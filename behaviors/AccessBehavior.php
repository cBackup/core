<?php
/**
 * Yii2 behavior to redirect all users to login (or any) page if not
 * authenticated, but allow access to some pages
 *
 * @copyright Artem Voitko <r3verser@gmail.com>
 */

namespace app\behaviors;

use yii\base\Behavior;
use yii\console\Controller;
use yii\helpers\Url;


/**
 * Redirects all users to defined page if they are not logged in.
 * Adjust configuration as following:
 *
 * 'as AccessBehavior' => [
 *      'class'         => 'app\components\AccessBehavior',
 *      'allowedRoutes' => [
 *          '/',
 *          ['/user/registration/register'],
 *          ['/user/registration/resend'],
 *          ['/user/registration/confirm'],
 *          ['/user/recovery/request'],
 *          ['/user/recovery/reset']
 *      ],
 *      'redirectUri' => ['site/login']
 *  ],
 *
 * @package app\components
 * @author  Artem Voitko <r3verser@gmail.com>
 */
class AccessBehavior extends Behavior
{

    /**
     * @var string Yii route format string
     */
    protected $redirectUri;

    /**
     * @var array Routes which are allowed to access for none logged in users
     */
    protected $allowedRoutes = [];


    /**
     * @param $uri string Yii route format string
     */
    public function setRedirectUri($uri)
    {
        $this->redirectUri = $uri;
    }


    /**
     * Sets allowedRoutes param and generates urls from defined routes
     * @param array $routes Array of allowed routes
     */
    public function setAllowedRoutes(array $routes)
    {
        $this->allowedRoutes = $routes;
    }


    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->redirectUri)) {
            $this->redirectUri = \Yii::$app->getUser()->loginUrl;
        }
    }


    /**
     * Subscribe for event
     * @return array
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }


    /**
     * On event callback
     * @throws \yii\base\ExitException
     */
    public function beforeAction()
    {
        if ( \Yii::$app->getUser()->isGuest && \Yii::$app->getRequest()->url !== Url::to($this->redirectUri) ) {
            foreach ($this->allowedRoutes as $allowedUrl) {
                if( preg_match($allowedUrl, \Yii::$app->getRequest()->url) ) {
                    return;
                }
            }
            \Yii::$app->getResponse()->redirect($this->redirectUri)->send();
            \Yii::$app->end();
        }
    }

}
