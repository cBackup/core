<?php
/**
 * This file is part of cBackup, network equipment configuration backup tool
 * Copyright (C) 2017, Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
 *
 * cBackup is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace app\commands;

use app\models\MailerEventsTasks;
use yii\console\Controller;
use yii\swiftmailer\Mailer;

/**
 * @package app\commands
 */
class MailerController extends Controller
{

    /**
     * @var \yii\swiftmailer\Mailer
     */
    private $mailer;


    /**
     * @inheritdoc
     */
    public function init()
    {

        parent::init();

        if (\Y::param('mailerType') == 'local') {
            $transport = [
                'class'   => 'Swift_SendmailTransport',
                'command' => \Y::param('mailerSendMailPath') . ' -bs'
            ];
        } else {

            $transport = [
                'class' => 'Swift_SmtpTransport',
                'host'  => \Y::param('mailerSmtpHost'),
                'port'  => \Y::param('mailerSmtpPort'),
            ];

            /** Set SMTP auth when it's necessary */
            if (\Y::param('mailerSmtpAuth') == 1) {
                $transport['username'] = \Y::param('mailerSmtpUsername');
                $transport['password'] = \Y::param('mailerSmtpPassword');
            }

            /** Set SMTP encryption when it's necessary */
            if (\Y::param('mailerSmtpSecurity') != 'none') {
                $transport['encryption'] = \Y::param('mailerSmtpSecurity');
            }

            /** Workaround for OpenSSL error:14090086:SSL routines:ssl3_get_server_certificate:certificate verify failed' */
            if (\Y::param('mailerSmtpSslVerify') == 1) {
                $transport['streamOptions']['ssl']['verify_peer'] = false;
                $transport['streamOptions']['ssl']['verify_peer_name'] = false;
            }
        }

        $this->mailer = new Mailer();
        $this->mailer->transport = $transport;

        $this->mailer->messageConfig = [
            'charset' => 'UTF-8',
            'from'    => [\Y::param('mailerFromEmail') => \Y::param('mailerFromName')],
        ];

    }


    /**
     * Send mail
     *
     * 0 - ok
     * 1 - task not found
     * 2 - task status update error
     * 3 - mail send error
     * 4 - wrong task status
     * 5 - mailer is disabled
     *
     * @param int $task_id
     * @return void
     */
    public function actionSend($task_id)
    {

        /** Stop processing script if mailer is disabled in system settings */
        if (\Y::param('mailer') == 0) {
            \Yii::error(["Mailer is disabled. To use mailer please enable it in 'System settings'", null, 'SEND MAIL'], 'mailer.writeLog');
            $this->stdout(5);
            exit();
        }

        $errors     = false;
        $event_task = MailerEventsTasks::findOne($task_id);

        /** Check if event task exists */
        if (is_null($event_task)) {
            \Yii::error(["Error while sending mail (Event: {$event_task->event_name}, Task: $event_task->id). Task not found.", $event_task->id, 'SEND MAIL'], 'mailer.writeLog');
            $this->stdout(1);
            exit();
        }

        /** Task is new?*/
        if ($event_task->status != 'new') {
            \Yii::error(["Error while sending mail (Event: {$event_task->event_name}, Task: $event_task->id). Incorrect task status - {$event_task->status}.", $event_task->id, 'SEND MAIL'], 'mailer.writeLog');
            $this->stdout(4);
            exit();
        }

        /** Set new event status to outgoing */
        $event_task->status = 'outgoing';

        /** Save new task status */
        if ($event_task->save()) {
            \Yii::info(["Task status changed to {$event_task->status}", $event_task->id, 'UPDATE'], 'mailer.writeLog');
        } else {
            \Yii::error(["Error while sending mail (Event: {$event_task->event_name}, Task: $event_task->id). Cannot change task status.", $event_task->id, 'UPDATE'], 'mailer.writeLog');
            $this->stdout(2);
            exit();
        }

        /** Get full list of event recipients */
        $recipients = $event_task->eventName->recipients;

        /** Create recipients array */
        if (!empty($recipients)) {
            $recipients_emails = array_filter(explode(';', $recipients));
        } else {
            \Yii::error(["Error while sending mail (Event: {$event_task->event_name}, Task: $event_task->id). No email recipients supplied", $event_task->id, 'SEND MAIL'], 'mailer.writeLog');
            $this->stdout(3);
            exit();
        }

        try {

            /** Creates a new message instance */
            $mailer = $this->mailer->compose();

            /** Set Subject and body */
            $mailer->setSubject($event_task->subject);
            $mailer->setHtmlBody($event_task->body);

            /** Max execution time */
            set_time_limit(count($recipients_emails) * 7 + 60);

            $sent_mail = 0;
            foreach ($recipients_emails as $email) {

                $mailer->setTo($email);

                /** Make delay between sent mails */
                if ($sent_mail >= 1) {
                    sleep(7);
                }

                /** Send mail */
                if ($mailer->send()) {
                    \Yii::info(["Email successfully sent (Event: {$event_task->event_name}, Task: $event_task->id). To: $email", $event_task->id, 'SEND MAIL'], 'mailer.writeLog');
                } else {
                    \Yii::error(["Error while sending mail (Event: {$event_task->event_name}, Task: $event_task->id). To: $email", $event_task->id, 'SEND MAIL'], 'mailer.writeLog');
                    $errors = true;
                }

                $sent_mail++;

                /** Flush yii logger */
                \Yii::getLogger()->flush(true);

            }
        } catch (\Exception $e) {
            \Yii::error(["Error while sending mail (Event: {$event_task->event_name}, Task: $event_task->id).\nException:\n{$e->getMessage()}", null, 'CREATE'], 'mailer.writeLog');
            $this->stdout(3);
            exit();
        }

        /** Set status to sent if no error occured */
        if (!$errors) {

            $event_task->status = 'sent';

            if ($event_task->save()) {
                \Yii::info(["Task status changed to {$event_task->status}", $event_task->id, 'UPDATE'], 'mailer.writeLog');
            } else {
                \Yii::error(["Error while sending mail (Event: {$event_task->event_name}, Task: $event_task->id). Cannot change task status.", $event_task->id, 'SEND MAIL'], 'mailer.writeLog');
                $this->stdout(2);
                exit();
            }

        } else {
            \Yii::error(["Error while sending mail (Event: {$event_task->event_name}, Task: $event_task->id). Some recipients don't receive email.", $event_task->id, 'SEND MAIL'], 'mailer.writeLog');
            $this->stdout(3);
            exit();
        }

        /** Mails sent  without errors*/
        $this->stdout(0);

    }


    /**
     * Send test mail to check if SMTP configuration is correct
     *
     * 0 - ok
     * 1 - mail send error
     * 2 - mailer is disabled
     *
     * If exception occurs error message will be returned
     *
     * @return void
     */
    public function actionSendTestMail()
    {

        /** Stop processing script if mailer is disabled in system settings */
        if (\Y::param('mailer') == 0) {
            $this->stdout(2);
            exit();
        }

        try {

            /** Creates a new message instance */
            $mailer      = $this->mailer->compose();
            $mailer_type = (\Y::param('mailerType') == 'local') ? 'Sendmail' : 'SMTP';

            /** Set all params */
            $mailer->setTo(\Y::param('mailerFromEmail'));
            $mailer->setSubject('Test cBackup mailer');
            $mailer->setTextBody("This is a test mail sent using '{$mailer_type}'. Your email settings are correct!");

            /** Try to send mail */
            if ($mailer->send()) {
                $this->stdout(0);
            } else {
                $this->stdout(1);
            }

        } catch (\Exception $e) {
            $this->stdout($e->getMessage());
        }

    }

}
