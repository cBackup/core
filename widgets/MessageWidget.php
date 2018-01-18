<?php

namespace app\widgets;

use yii\base\Widget;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class Message
 *
 * @package app\widgets
 */
class MessageWidget extends Widget
{

    /**
     * @var array
     */
    public $data = [
        'count'    => '',
        'messages' => []
    ];

    /**
     * Prepare dataset
     *
     * @return void
     */
    public function init()
    {

        parent::init();

        $messages = (new Query())
            ->select(['created', 'message'])
            ->from([new Expression('{{%messages}} FORCE INDEX (ix_time)')])
            ->where(['approved' => null])
            ->orderBy(['created' => SORT_DESC])
        ;

        $this->data = [
            'count'    => $messages->count(),
            'messages' => $messages->limit(5)->all(),
        ];

    }

    /**
     * Render messages view
     *
     * @return string
     */
    public function run()
    {
        return $this->render('message_widget', [
            'data' => $this->data
        ]);
    }

}
