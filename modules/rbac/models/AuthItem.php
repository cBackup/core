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

use app\models\Plugin;
use app\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "{{%auth_item}}".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property AuthAssignment[] $authAssignments
 * @property User[] $users
 * @property AuthRule $ruleName
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItemChild[] $authItemChildren0
 * @property AuthItem[] $children
 * @property AuthItem[] $parents
 * @property Plugin[] $plugins
 */
class AuthItem extends ActiveRecord
{

    /**
     * Role constant
     */
    const TYPE_ROLE = 1;

    /**
     * Permissions constant
     */
    const TYPE_PERMISSION = 2;

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
    public $_children;

    /**
     * @var \yii\rbac\ManagerInterface
     */
    protected $manager;

    /**
     * @var \yii\rbac\Item
     */
    private $_name;

    /**
     * AuthItem constructor.
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
        return '{{%auth_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['name'], 'unique'],
            [['name', 'description'], 'filter', 'filter' => 'trim'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 64],
            [['rule_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthRule::class, 'targetAttribute' => ['rule_name' => 'name']],
            [['description'], 'default', 'value' => null],
            [['roles', 'permissions'], 'default', 'value' => []],
            [['roles', 'permissions'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'        => Yii::t('app', 'Name'),
            'type'        => Yii::t('app', 'Type'),
            'description' => Yii::t('app', 'Description'),
            'rule_name'   => Yii::t('rbac', 'Rule Name'),
            'data'        => Yii::t('rbac', 'Data'),
            'created_at'  => Yii::t('rbac', 'Created At'),
            'updated_at'  => Yii::t('rbac', 'Updated At'),
            'roles'       => Yii::t('rbac', 'Roles'),
            'permissions' => Yii::t('rbac', 'Permissions'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => function() { return date('U'); },
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::class, ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['userid' => 'user_id'])->viaTable('{{%auth_assignment}}', ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(AuthRule::class, ['name' => 'rule_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren()
    {
        return $this->hasMany(AuthItemChild::class, ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren0()
    {
        return $this->hasMany(AuthItemChild::class, ['child' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(AuthItem::class, ['name' => 'child'])->viaTable('{{%auth_item_child}}', ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(AuthItem::class, ['name' => 'parent'])->viaTable('{{%auth_item_child}}', ['child' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlugins()
    {
        return $this->hasMany(Plugin::class, ['access' => 'name']);
    }


    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->setNameType();
        $this->_children   = $this->getAuthChildren();
        $this->roles       = $this->_children['roles'];
        $this->permissions = $this->_children['permissions'];

        parent::afterFind();
    }


    /**
     * Set auth item type
     *
     * @return null|\yii\rbac\Item
     */
    public function setNameType()
    {
        if ($this->type == self::TYPE_ROLE) {
            $this->_name = $this->manager->getRole($this->name);
        } else {
            $this->_name = $this->manager->getPermission($this->name);
        }

        return $this->_name;
    }


    /**
     * Access item types
     *
     * @return array
     */
    public function getTypeDefinition()
    {
        return [
            self::TYPE_ROLE       => Yii::t('rbac', 'Role'),
            self::TYPE_PERMISSION => Yii::t('rbac', 'Permission'),
        ];
    }


    /**
     * Make item type human readable
     *
     * @return string
     */
    public function getAuthItemReadable()
    {

        $types = $this->getTypeDefinition();

        if (array_key_exists($this->type, $types)) {
            return $types[$this->type];
        }

        return Yii::t('rbac', 'Unknown item type');

    }


    /**
     * Get all system roles
     *
     * @return array
     */
    public function getAllRoles()
    {

        $all_roles = $this->manager->getRoles();

        if (!empty($this->name)) {
            ArrayHelper::remove($all_roles, $this->name);
        }

        return ArrayHelper::map($all_roles, 'name', 'name');

    }


    /**
     * Get all system permissions
     *
     * @return array
     */
    public function getAllPermissions()
    {

        $all_permissions = $this->manager->getPermissions();

        if (!empty($this->name)) {
            ArrayHelper::remove($all_permissions, $this->name);
        }

        return ArrayHelper::map($all_permissions, 'name', 'name');

    }

    /**
     * Get auth item children
     *
     * @return array
     */
    public function getAuthChildren()
    {
        $children = $this->manager->getChildren($this->name);

        $result = ['roles' => [], 'permissions' => []];

        foreach ($children as $child) {
            switch ($child->type) {
                case 1: $result['roles'][] = $child->name; break;
                case 2: $result['permissions'][] = $child->name; break;
            }
        }

        return $result;
    }

    /**
     * Update auth item
     *
     * @param  array $items
     * @return bool
     * @throws \yii\base\Exception
     */
    public function updateElement($items)
    {

        $updated = false;
        $status  = ['delete' => false, 'update' => false];

        if ($this->manager->removeChildren($this->_name)) {
            $status['delete'] = true;
        }

        if (!empty($items)) {

            foreach ($items as $name) {

                $child = $this->manager->getPermission($name);

                if (empty($child) && $this->type == self::TYPE_ROLE) {
                    $child = $this->manager->getRole($name);
                }

                $this->manager->addChild($this->_name, $child);
                $status['update'] = true;
            }
        }

        if ($status['delete'] == true && $status['update'] == true) {
            $updated = true;
        }

        return $updated;

    }

}
