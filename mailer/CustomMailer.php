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

namespace app\mailer;

use yii\helpers\Inflector;
use app\models\MailerEventsTasks;
use app\models\MailerEvents;
use toriphes\console\Runner;
use cbackup\console\ConsoleRunner;


/**
 * @package app\mailer
 */
class CustomMailer
{

    /**
     * Class which contains mailer methods
     *
     * @var string
     */
    private $mailer_methods = 'app\mailer\MailerMethods';

    /**
     * @var array
     */
    private $template_params = [];


    /**
     * Create and send new Mail
     *
     * @param  string $task_name
     * @param  bool $background
     * @return bool|string
     * @throws \yii\db\Exception
     */
    public function sendMail($task_name, $background = false)
    {

        /** Stop processing script if mailer is disabled in system settings */
        if (\Y::param('mailer') == 0) {
            \Yii::error(["Mailer is disabled. To use mailer please enable it in 'System settings'", null, 'SEND MAIL'], 'mailer.writeLog');
            return false;
        }

        /** Create new event task */
        $task_id = $this->createEventTask($task_name);

        /** Run event task */
        if ($task_id != false) {

            $command = "mailer/send {$task_id}";

            if(!$background) {
                $output = '';
                $runner = new Runner();
                /** @noinspection PhpParamsInspection */
                $runner->run($command, $output);
                return $output;
            } else {
                $console = new ConsoleRunner(['file' => '@app/yii']);
                $console->run($command);
                return null;
            }

        }

        return false;

    }


    /**
     * Send test mail
     *
     * @return bool|string
     */
    public function sendTestMail()
    {
        $output = '';
        $runner = new Runner();
        /** @noinspection PhpParamsInspection */
        $runner->run('mailer/send-test-mail', $output);
        return $output;
    }


    /**
     * Create event task
     * Return event task id on success, false on error
     *
     * @param  string $task_name
     * @return bool|int
     * @throws \yii\db\Exception
     */
    private function createEventTask($task_name)
    {

        $event = MailerEvents::findOne($task_name);

        if (!is_null($event)) {

            $event_task = new MailerEventsTasks();

             $transaction = \Yii::$app->db->beginTransaction();

             try {

                /** Set event task attributes*/
                $event_task->event_name = $event->name;
                $event_task->subject    = $event->subject;
                $event_task->body       = $this->generateMailBody($event->template);

                /** Save event task */
                if ($event_task->save()) {
                    $transaction->commit();
                    \Yii::info(["Event task with ID {$event_task->id} created", $event_task->id, 'CREATE'], 'mailer.writeLog');
                    \Yii::getLogger()->flush(true);
                    return $event_task->id;
                } else {
                    $transaction->rollBack();
                    \Yii::error(["An error occurred while creating new event task", null, 'CREATE'], 'mailer.writeLog');
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
                \Yii::error(["An error occurred while creating new event task.\nException:\n{$e->getMessage()}", null, 'CREATE'], 'mailer.writeLog');
            }

        } else {
            \Yii::error(["Event with name `{$task_name}` does not exist", null, 'CREATE'], 'mailer.writeLog');
        }

        return false;

    }


    /**
     * Generate email body
     *
     * @param  string $event_template
     * @return string
     */
    public function generateMailBody($event_template)
    {
        $result = '';

        if (!empty($event_template) && !is_null($event_template)) {
            $templ_vars = $this->parseTemplateParams($event_template)->prepareVariables();
            $result     = strtr($event_template, $templ_vars);
        }

        return $result;
    }


    /**
     * Parse user created mail template
     *
     * @param  string $template
     * @return $this
     */
    private function parseTemplateParams($template)
    {

        if (!empty($template) && !is_null($template)) {

            /** Find all template params */
            preg_match_all('/{{.*?}}/i', $template, $variables);

            foreach ($variables[0] as $template_var) {

                preg_match_all('/(?<={{)\w+|:.*?(?=>)|>.*?(?=}})/', $template_var, $var_params);

                foreach ($var_params[0] as $key => $value) {

                    /** Get method name in camelcase */
                    $this->template_params[$template_var]['method'] = 'get' . Inflector::camelize(strtolower($var_params[0][0]));

                    /** Get all necessary arguments from string */
                    if (strpos($var_params[0][$key], ':') !== false) {
                        $this->template_params[$template_var]['args'] = array_filter(explode(':', $var_params[0][$key]));
                    }

                    /** Get all output params from string */
                    if (strpos($var_params[0][$key], '>') !== false) {
                        $this->template_params[$template_var]['output'] = array_filter(explode('>', strtolower($var_params[0][$key])));
                    }

                }

            }

        }

        return $this;

    }


    /**
     * Prepare all variables based on user template
     *
     * @return array|string
     */
    private function prepareVariables()
    {

        $result = [];

        try {
            foreach ($this->template_params as $key => $value) {
                if (method_exists($this->mailer_methods, $value['method'])) {
                    $reflection   = new \ReflectionMethod($this->mailer_methods, $value['method']);
                    $args         = (array_key_exists('args', $value))   ? $value['args']   : [];
                    $output       = (array_key_exists('output', $value)) ? $value['output'] : [];
                    $result[$key] = $reflection->invoke(new MailerMethods($args, $output));
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $result;

    }


}
