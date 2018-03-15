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

namespace app\modules\rbac\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\models\User;


/**
 * This is the model class for table "{{%auth_assignment}}".
 *
 * @property string $item_name
 * @property string $user_id
 * @property integer $created_at
 *
 * @property AuthItem $itemName
 * @property User $user
 */
class AuthAssignment extends ActiveRecord
{

    /**
     * @var string
     */
    public $name_search;

    /**
     * @var int
     */
    public $type;

    /**
     * @var array
     */
    public $roles = [];

    /**
     * @var array
     */
    public $permissions = [];

    /**
     * @var array
     */
    public $_assignments = [];

    /**
     * @var \yii\rbac\ManagerInterface
     */
    protected $manager;

    /**
     * AuthAssignment constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->manager = Yii::$app->authManager;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_assignment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['created_at'], 'integer'],
            [['item_name', 'user_id'], 'string', 'max' => 64],
            [['item_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::class, 'targetAttribute' => ['item_name' => 'name']],
            [['roles', 'permissions'], 'default', 'value' => []],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'item_name'   => Yii::t('rbac', 'Access level'),
            'user_id'     => Yii::t('app', 'User'),
            'created_at'  => Yii::t('rbac', 'Created At'),
            'name_search' => Yii::t('user', 'Full name'),
            'type'        => Yii::t('app', 'Type'),
            'roles'       => Yii::t('rbac', 'Roles'),
            'permissions' => Yii::t('rbac', 'Permissions')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['userid' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemName()
    {
        return $this->hasOne(AuthItem::class, ['name' => 'item_name']);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->_assignments = $this->getUserAssignments();
        $this->roles        = $this->_assignments;
        $this->permissions  = $this->_assignments;

        parent::afterFind();
    }

    /**
     * Returns all role and permissions assigned to the specific user.
     *
     * @return array
     */
    public function getUserAssignments()
    {
        return ArrayHelper::map($this->manager->getAssignments($this->user_id), 'roleName', 'roleName');
    }

    /**
     * Update user assignments
     *
     * @param  array $items
     * @return bool
     * @throws \Exception
     */
    public function updateUserAssignment($items)
    {

        $updated = false;
        $status  = ['delete' => false, 'update' => false];

        /**
         * Delete will be executed only if user already has assigned auth items
         * if not delete status true will be returned
         */
        if (!empty($this->getUserAssignments())) {
            $status['delete'] = $this->manager->revokeAll($this->user_id);
        }
        else {
            $status['delete'] = true;
        }

        /** Update user access assignments */
        if (!empty($items) && $status['delete'] == true) {

            foreach ($items as $name) {

                $auth_item = $this->manager->getRole($name);

                if (empty($auth_item)) {
                    $auth_item = $this->manager->getPermission($name);
                }

                $this->manager->assign($auth_item, $this->user_id);
                $status['update'] = true;
            }

        }
        else {
            $status['update'] = true;
        }

        if ($status['delete'] == true && $status['update'] == true) {
            $updated = true;
        }

        return $updated;

    }

    /**
     * Delete specific user assignment
     *
     * @param  string $user_id
     * @param  string $item_name
     * @return bool
     */
    public function deleteUserAssignment($user_id, $item_name)
    {

        $deleted = false;

        if (!empty($item_name)) {

            $auth_item = $this->manager->getRole($item_name);

            if (empty($auth_item)) {
                $auth_item = $this->manager->getPermission($item_name);
            }

            $this->manager->revoke($auth_item, $user_id);
            $deleted = true;
        }

        return $deleted;

    }

}
