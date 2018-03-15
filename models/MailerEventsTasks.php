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

namespace app\models;

use Yii;
use \yii\db\ActiveRecord;
use \yii\helpers\Html;


/**
 * This is the model class for table "{{%mailer_events_tasks}}".
 *
 * @property integer $id
 * @property string $event_name
 * @property string $status
 * @property string $subject
 * @property string $body
 * @property string $created
 *
 * @property LogMailer[] $logMailers
 * @property MailerEvents $eventName
 * @property MailerEventsTasksStatuses $eventStatus
 */
class MailerEventsTasks extends ActiveRecord
{

    /**
     * @var string
     */
    public $date_from;

    /**
     * @var string
     */
    public $date_to;

    /**
     * Default page size
     * @var int
     */
    public $page_size = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mailer_events_tasks}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['event_name', 'subject', 'body'], 'required'],
            [['body'], 'string'],
            [['created'], 'safe'],
            [['event_name'], 'string', 'max' => 128],
            [['status'], 'string', 'max' => 64],
            [['subject'], 'string', 'max' => 255],
            [['event_name'], 'exist', 'skipOnError' => true, 'targetClass' => MailerEvents::class, 'targetAttribute' => ['event_name' => 'name']],
            [['status'], 'exist', 'skipOnError' => true, 'targetClass' => MailerEventsTasksStatuses::class, 'targetAttribute' => ['status' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'event_name' => Yii::t('app', 'Name'),
            'status'     => Yii::t('app', 'Status'),
            'subject'    => Yii::t('app', 'Subject'),
            'body'       => Yii::t('app', 'Body'),
            'created'    => Yii::t('app', 'Created'),
            'date_from'  => Yii::t('log', 'Date/time from'),
            'date_to'    => Yii::t('log', 'Date/time to'),
            'page_size'  => Yii::t('app', 'Page size')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogMailers()
    {
        return $this->hasMany(LogMailer::class, ['event_task_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEventName()
    {
        return $this->hasOne(MailerEvents::class, ['name' => 'event_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEventStatus()
    {
        return $this->hasOne(MailerEventsTasksStatuses::class, ['name' => 'status']);
    }

    /**
     * Render styled event task statuses
     *
     * @return string
     */
    public function getEventStyledStatus()
    {
        $statuses = ['new' => '#3C763D;', 'outgoing' => '#8A6D3B;', 'sent'=> '#31708F;'];
        $color    = (array_key_exists($this->status, $statuses)) ? $statuses[$this->status] : '#909090;';
        return Html::tag('span', Yii::t('mail', $this->status), ['class' => 'text-bolder', 'style' => 'color: ' . $color ]);
    }

    /**
     * Render event edit links in popover
     *
     * @return string
     */
    public function getEventLinks()
    {

        $edit_recipients = Html::a('<i class="fa fa-address-book-o"></i> ' . Yii::t('app', 'Edit recipients'), [
            '/mail/events/edit-event-recipients', 'name' => $this->event_name], ['target' => '_blank']
        );

        $edit_tempplate = Html::a('<i class="fa fa-file-text-o"></i> ' . Yii::t('app', 'Edit template'), [
            '/mail/events/edit-event-template', 'name' => $this->event_name], ['target' => '_blank']
        );

        $edit_event = Html::a('<i class="fa fa-pencil-square-o"></i> ' . Yii::t('app', 'Edit event'), [
            '/mail/events/edit-event', 'name' => $this->event_name], ['target' => '_blank']
        );

        $popup_links = '
            <ul class="nav nav-list">
                <li>'. $edit_recipients .'</li>
                <li>'. $edit_tempplate  .'</li>
                <li>'. $edit_event      .'</li>
            </ul>
        ';

        $popup_template = '
            <div class="popover" role="tooltip" style="font-family: inherit;">
                <div class="arrow"></div>
                <div class="popover-content" style="padding: 0;">
                </div>
            </div>
        ';

        $result = Html::a($this->event_name, 'javascript:void(0);', [
            'class'               => 'popup',
            'data-container'      => 'body',
            'data-toggle'         => 'popover',
            'data-click-handler'  => 'custom',
            'data-html'           => 'true',
            'data-placement'      => 'right',
            'data-trigger'        => 'manual',
            'data-template'       => $popup_template,
            'data-original-title' => $this->event_name,
            'data-content'        => $popup_links
        ]);

        return $result;

    }

    /**
     * Render list of event recipients
     *
     * @return null|string
     */
    public function getEventRecipients()
    {

        $recipients = array_filter(explode(';', $this->eventName->recipients));
        $content    = null;

        if (!empty($recipients)) {

            $count   = count($recipients);
            $content = $recipients[0];

            if ($count > 1) {
                $link_text = Yii::t('mail', 'View list of recipients {0}', Html::tag('span', $count, ['class' => 'link-label-small label label-success']));
                $content   = Html::a($link_text, 'javascript:void(0);', [
                    'class'               => 'popup',
                    'data-container'      => 'body',
                    'data-toggle'         => 'popover',
                    'data-click-handler'  => 'custom',
                    'data-html'           => 'true',
                    'data-placement'      => 'right',
                    'data-trigger'        => 'manual',
                    'data-original-title' => Yii::t('mail', 'List of recipients'),
                    'data-content'        => implode('<br>', $recipients)
                ]);
            }
        }

        return $content;

    }

}
