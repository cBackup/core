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

namespace app\modules\v2\controllers;

use app\helpers\ApiHelper;
use app\models\Node;
use app\models\OutStp;
use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\ContentNegotiator;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\db\Expression;
use app\models\User;
use dautkom\ipv4\IPv4;


/**
 * @package app\modules\v2\controllers
 */
class NodeController extends Controller
{

    /**
     * @return array
     */
    public function behaviors()
    {

        $behaviors = parent::behaviors();

        /*
         * To return JSON format
         */
        $behaviors['contentNegotiator'] = [
            'class'   => ContentNegotiator::class,
            'formats' => [
                'application/json'  => Response::FORMAT_JSON,
                'charset'           => 'UTF-8',
            ],
        ];

        /*
         * Authentication
         */
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBearerAuth::class,
                [
                    'class' => QueryParamAuth::class,
                    'tokenParam' => 'token'
                ],
                [
                    'class' => HttpBasicAuth::class,
                    'auth'  => function($username, $password) {

                        $user = User::findOne(['userid' => $username, 'enabled' => 1]);

                        if(!empty($user) && $user->validatePassword($password)) {
                            return $user;
                        }

                        return null;
                    }
                ]
            ],
        ];

        /*
         * Access rules
         */
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['search', 'list', 'get', 'get-nodes', 'lookup', 'get-stp', 'interim'],
                    'roles'   => ['APIReader', 'admin'],
                ],
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'list'      => ['get'],
                'get'       => ['get'],
                'get-nodes' => ['get'],
                'get-stp'   => ['get'],
                'interim'   => ['post'],
                'search'    => ['post'],
                'lookup'    => ['post'],
            ],
        ];

        return $behaviors;

    }


    /**
     * Get node list
     *
     * Method returns array but the response is translated to JSON string
     * via contentNegotiator behavior
     *
     * @param  int $limit
     * @param  int $offset
     * @return array translated to json string via contentNegotiator behavior
     */
    public function actionList($limit = null, $offset = null): array
    {

        $nodes = Node::find()->limit($limit)->offset($offset)->asArray()->all();

        if(!$nodes) {
            return ApiHelper::getResponseBodyByCode(404);
        }
        else {
            return $nodes;
        }

    }


    /**
     * Get node
     *
     * @param  int $id
     * @return array translated to json string via contentNegotiator behavior
     */
    public function actionGet($id): array
    {

        $node = Node::find()->where('id=:id', [':id' => intval($id)])->asArray()->one();

        if(!$node) {
            return ApiHelper::getResponseBodyByCode(404);
        }
        else {
            return $node;
        }

    }


    /**
     * Get full list of nodes
     *
     * @return array translated to json string via contentNegotiator behavior
     */
    public function actionGetNodes(): array
    {

        $query = Node::find()
            ->select([
                'node.*',
                'CONCAT_WS(" ", prepend_location, location) AS full_location',
                'c.name AS node_credentials',
                'CONCAT_WS(" ", d.vendor, d.model) AS device_name',
                'n.network AS network_address',
                'n.description AS network_description'])
            ->joinWith(['device d', 'credential c', 'network n'], false);

        $nodes = $query->asArray()->all();

        if(empty($nodes)) {
            return ApiHelper::getResponseBodyByCode(404);
        }
        else {
            return $nodes;
        }

    }


    /**
     * @return array translated to json string via contentNegotiator behavior
     */
    public function actionSearch(): array
    {

        if( empty(Yii::$app->request->post()) ) {
            return ApiHelper::getResponseBodyByCode(400, 'Empty criteria');
        }

        $query = Node::find()->where('id');
        $model = new Node();

        foreach (Yii::$app->request->post() as $arg => $val) {

            if( !empty($val) && $model->hasAttribute($arg) ) {

                $val = json_decode($val);

                if(is_array($val)) {
                    $query->andWhere(['IN', $arg, $val]);
                }
                else {
                    $query->andWhere(['LIKE', $arg, $val]);
                }

            }

        }

        return $query->asArray()->all();

    }


    /**
     * @return array translated to json string via contentNegotiator behavior
     */
    public function actionLookup(): array
    {

        if( empty(Yii::$app->request->post()) ) {
            return ApiHelper::getResponseBodyByCode(400, 'Empty criteria');
        }

        $query = Node::find();
        $model = new Node();
        $where = [];
        $likes = 0;

        foreach (Yii::$app->request->post() as $arg => $val) {

            if( !empty($val) && $model->hasAttribute($arg) ) {

                $val = json_decode($val);

                if(is_array($val)) {
                    foreach ($val as $criteria) {
                        $where[] = [$arg, $criteria];
                    }
                }
                else {
                    $where[] = [$arg, $val];
                }

            }

        }

        if( !empty($where) ) {

            foreach ($where as $like) {

                if( $likes === 0 ) {
                    $query->where(['LIKE', $like[0], $like[1]]);
                    $likes++;
                }
                else {
                    $query->orWhere(['LIKE', $like[0], $like[1]]);
                }

            }

        }

        return $query->asArray()->all();

    }


    /**
     * Get STP map
     *
     * Method returns array but the response is translated to JSON string
     * via contentNegotiator behavior
     *
     * @param $criteria
     * @return array translated to json string via contentNegotiator behavior
     */
    public function actionGetStp($criteria) :array
    {

        $stp = (new OutStp())->createStpTree($criteria);

        if (empty($stp)) {
            return ApiHelper::getResponseBodyByCode(404);
        }
        else {
            return $stp;
        }

    }


    /**
     * Get nodes list between specific ip range
     *
     * @return array translated to json string via contentNegotiator behavior
     */
    public function actionInterim(): array
    {

        $range = Yii::$app->request->post();
        $net   = new IPv4();

        /** Check if post is not empty */
        if (empty($range)) {
            return ApiHelper::getResponseBodyByCode(400, 'Empty criteria');
        }

        /** Check if correct count of parameters are passed */
        if (count($range) != 2) {
            return ApiHelper::getResponseBodyByCode(400, 'Incorrect parameter count');
        }

        /** Check if given ip addresses are valid */
        if (!$net->address($range[0])->isValid() || !$net->address($range[1])->isValid()) {
            return ApiHelper::getResponseBodyByCode(400, 'Invalid IP address specified');
        }

        $query = Node::find()
            ->select([
                'node.*',
                'CONCAT_WS(" ", prepend_location, location) AS full_location',
                'c.name AS node_credentials',
                'CONCAT_WS(" ", d.vendor, d.model) AS device_name',
                'n.network AS network_address',
                'n.description AS network_description'])
            ->andFilterWhere([
                'between',
                'INET_ATON(ip)',
                new Expression('INET_ATON(:ip1)', [':ip1' => $range[0]]),
                new Expression('INET_ATON(:ip2)', [':ip2' => $range[1]])
            ])
            ->joinWith(['device d', 'credential c', 'network n'], false)
        ;

        $nodes = $query->asArray()->all();

        return $nodes;

    }

}
