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
 * This is the model class for table "{{%mailer_events}}".
 *
 * @property string $name
 * @property string $subject
 * @property string $template
 * @property string $recipients
 * @property string $description
 *
 * @property MailerEventsTasks[] $mailerEventsTasks
 * @property ScheduleMail $scheduleMail
 */
class MailerEvents extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mailer_events}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'recipients', 'subject', 'description'], 'filter', 'filter' => 'trim'],
            [['template'], 'filter', 'filter' => function($value) { return htmlspecialchars_decode($value); }],
            [['name'], 'match', 'pattern' => '/^[a-z0-9_]+$/im', 'message' => Yii::t('app', 'Event name should contain only a-z, 0-9, underscore')],
            [['recipients'], 'validateMails', 'on' => 'set-recipients'],
            [['name'], 'unique'],
            [['template', 'recipients'], 'string'],
            [['name'], 'string', 'max' => 128],
            [['subject', 'description'], 'string', 'max' => 255],
            [['subject'],  'required', 'when' => function($model) { /** @var $model MailerEvents */ return !empty($model->template); }],
            [['template'], 'required', 'when' => function($model) { /** @var $model MailerEvents */ return !empty($model->subject);  }],
            [['subject', 'template', 'recipients', 'description'], 'default', 'value' => null]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'        => Yii::t('app', 'Name'),
            'subject'     => Yii::t('app', 'Subject'),
            'template'    => Yii::t('app', 'Template'),
            'recipients'  => Yii::t('app', 'Recipients'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    /**
     * Validate emails
     *
     * @param $attribute
     */
    public function validateMails($attribute)
    {

        $mails = explode(';', $this->$attribute);
        $incorrect_mails = [];

        foreach($mails as $mail) {
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $incorrect_mails[] = $mail;
            }
        }

        if( !empty(array_filter($incorrect_mails)) ) {
            $this->addError($attribute, Yii::t('app', 'Incorrect email address entered: <br> {0}', implode('<br>', $incorrect_mails)));
        }

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMailerEventsTasks()
    {
        return $this->hasMany(MailerEventsTasks::class, ['event_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScheduleMail()
    {
        return $this->hasOne(ScheduleMail::class, ['event_name' => 'name']);
    }

    /**
     * Render subject icon in gridview
     *
     * @return string
     */
    public function getEventHasSubject()
    {
        return static::prepareResult($this->subject, Yii::t('app', 'Event subject is set'), Yii::t('app', 'Event subject is missing'));
    }

    /**
     * Render template icon in gridview
     *
     * @return string
     */
    public function getEventHasTemplate()
    {
        return static::prepareResult($this->template, Yii::t('app', 'Event template is set'), Yii::t('app', 'Event template is missing'));
    }


    /**
     * Render template icon in gridview
     *
     * @return string
     */
    public function getEventHasRecipients()
    {
        return static::prepareResult($this->recipients, Yii::t('app', 'Event recipients are set'), Yii::t('app', 'Event recipients are missing'));
    }

    /**
     * Prepera result for gridview
     *
     * @param  string $has_param
     * @param  string $has_text
     * @param  string $has_not_text
     * @return string
     */
    private static function prepareResult($has_param, $has_text = '', $has_not_text = '')
    {

        $class = 'fa fa-times text-danger';
        $text  = $has_not_text;

        if (!is_null($has_param)) {
            $class = 'fa fa-check text-success';
            $text  = $has_text;
        }

        return Html::tag('i', '', [
            'class'               => $class,
            'style'               => 'cursor: help;',
            'data-toggle'         => 'tooltip',
            'data-placement'      => 'bottom',
            'data-original-title' => $text
        ]);

    }

}
