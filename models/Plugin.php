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
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use app\modules\rbac\models\AuthItem;


/**
 * This is the model class for table "{{%plugin}}".
 *
 * @property string $name
 * @property string $author
 * @property string $version
 * @property string $access
 * @property integer $enabled
 * @property string $widget
 * @property string $metadata
 * @property string $params
 * @property string $description
 *
 * @property AuthItem $pluginAccess
 */
class Plugin extends ActiveRecord
{

    /**
     * @var UploadedFile
     */
    public $file;

    /**
     * Access to plugin module methods
     *
     * @var object
     */
    public $plugin;

    /**
     * @var array
     */
    public $plugin_params = [];

    /**
     * @var string
     */
    private $plugin_path = '';

    /**
     * @var string
     */
    private $plugin_full_name = '';

    /**
     * @var string
     */
    private $plugin_name = '';

    /**
     * @var string
     */
    private $dst_path = '';

    /**
     * @var array
     */
    private $plugin_config = [];

    /**
     * @var null
     */
    private $previous_version = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%plugin}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'author', 'version', 'metadata'], 'required'],
            [['enabled'], 'integer'],
            [['metadata', 'params'], 'string'],
            [['name', 'access'], 'string', 'max' => 64],
            [['author', 'widget', 'description'], 'string', 'max' => 255],
            [['version'], 'string', 'max' => 32],
            [['name'], 'unique'],
            [['access'], 'exist', 'skipOnError' => true, 'targetClass' => AuthItem::class, 'targetAttribute' => ['access' => 'name']],
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'zip', 'on' => 'validate_file'],
            [['params', 'widget', 'description'], 'default', 'value' => null]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'        => Yii::t('app', 'Name'),
            'author'      => Yii::t('plugin', 'Author'),
            'version'     => Yii::t('app', 'Version'),
            'access'      => Yii::t('plugin', 'Access'),
            'enabled'     => Yii::t('app', 'Status'),
            'widget'      => Yii::t('plugin', 'Widget'),
            'metadata'    => Yii::t('plugin', 'Metadata'),
            'params'      => Yii::t('plugin', 'Params'),
            'description' => Yii::t('app', 'Description'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPluginAccess()
    {
        return $this->hasOne(AuthItem::class, ['name' => 'access']);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->plugin_params = Json::decode($this->params);
        $this->plugin        = $this->initPluginTranslation();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->cache->delete('pluginmenu');
        Yii::$app->cache->delete('pluginmodules');
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        Yii::$app->cache->delete('pluginmenu');
        Yii::$app->cache->delete('pluginmodules');
        parent::afterDelete();
    }

    /**
     * Run installation steps
     *
     * @return array
     */
    public function installPlugin()
    {

        $steps     = ['uploadPlugin', 'unZipPlugin', 'validateJson', 'addPlugin', 'processSql', 'cleanUp'];
        $counter   = 0;
        $has_error = false;
        $result    = [];
        $message   = '';

        try {

            foreach ($steps as $step) {
                if ($this->$step() == true) {
                    $counter++;
                } else {
                    $has_error = true;
                    break;
                }
            }

            $result = ['status' => true, 'message' => Yii::t('app', 'Action successfully finished')];

        } catch (\Exception $e) {
            $has_error = true;
            $message   = preg_replace('/^\h*\v+/m', '', $e->getMessage());
        }

        if ($has_error == true) {

            $result = [
                'status'  => false,
                'message' => Yii::t('plugin', 'Step: {0}<br>Exception: {1}', [Inflector::camel2words($steps[$counter]), $message])
            ];

            if ($steps[$counter] != end($steps)) {
                try {
                    $this->cleanUp();
                } catch (\Exception $e) {
                    $result['message'] .= "\n" . Yii::t('plugin', 'File clean up failed: {0}', $e->getMessage());
                }
            }

            Yii::error([$result['message'], strtoupper(Inflector::camel2words($steps[$counter]))], 'system.writeLog');

        }

        return $result;

    }

    /**
     * Get plugin params by plugin name
     *
     * @param  string $name
     * @return mixed
     */
    public function getPluginParams($name)
    {
        $params = static::find()->select('params')->where(['name' => $name])->scalar();
        return Json::decode($params);
    }


    /**
     * Get plugin form from plugin json file
     *
     * @return array
     */
    public function getPluginForm()
    {

        $plugin_file = Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'plugins' .
            DIRECTORY_SEPARATOR . $this->prepareName($this->name) . DIRECTORY_SEPARATOR . "{$this->name}.json";
        $plugin_data = Json::decode(file_get_contents($plugin_file));
        $result      = [];

        if (array_key_exists('form', $plugin_data) && !empty($plugin_data['form'])) {
            $result = $plugin_data['form'];
        }

        return $result;

    }

    /**
     * Remove plugin from system
     *
     * @param  string $name
     * @throws \Exception
     */
    public function removePlugin($name)
    {

        try {

            $location    = Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $this->prepareName($name);
            $tables_file = $location . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . $this->prepareName($name, false) . 'Tables.php';

            /** Run table remove process if table installation file exists */
            if (file_exists($tables_file)) {
                $class = 'app\\modules\\plugins\\' . $this->prepareName($name) . '\\sql\\' . $this->prepareName($name, false) . 'Tables';
                $object = (new \ReflectionClass($class))->newInstance();
                $object->remove();
            }

            /** Remove plugin table entry and files */
            static::find()->where(['name' => $name])->one()->delete();
            FileHelper::removeDirectory($location);

        }
        /** @noinspection PhpUndefinedClassInspection */
        catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }

    }

    /** @noinspection PhpUnusedPrivateMethodInspection
     *
     * Upload file to runtime directory
     *
     * Method is called dynamically in @see installPlugin() method
     *
     * @return  bool
     * @throws \Exception
     */
    private function uploadPlugin()
    {

        try {

            $this->setScenario('validate_file');

            if ($this->validate(['file'])) {

                $this->plugin_path      = \Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'plugin_' . $this->file->baseName . DIRECTORY_SEPARATOR;
                $this->plugin_full_name = $this->file->baseName . '.' . $this->file->extension;
                $this->plugin_name      = $this->prepareName($this->file->baseName);

                /** Create dir if does not exist */
                if (!file_exists($this->plugin_path) && !@mkdir($this->plugin_path)) {
                    throw new \Exception("Could not create plugin directory {$this->plugin_path}");
                }

                $this->file->saveAs($this->plugin_path . DIRECTORY_SEPARATOR . $this->plugin_full_name);

                return true;
            } else {
                throw new \Exception($this->errors['file'][0]);
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

    }

    /** @noinspection PhpUnusedPrivateMethodInspection
     *
     * Unzip plugin to runtime directory
     *
     * Method is called dynamically in @see installPlugin() method
     *
     * @return  bool
     * @throws \Exception
     */
    private function unZipPlugin()
    {

        $zip = new \ZipArchive;

        try {

            $status  = false;
            $archive = $zip->open($this->plugin_path . DIRECTORY_SEPARATOR . $this->plugin_full_name);

            if ($archive === true) {
                $zip->extractTo($this->plugin_path . DIRECTORY_SEPARATOR . $this->plugin_name);
                $zip->close();
                $status = true;
            }

            return $status;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

    }

    /** @noinspection PhpUnusedPrivateMethodInspection
     *
     * Validate plugin .json file
     *
     * Method is called dynamically in @see installPlugin() method
     *
     * @return  bool
     * @throws \Exception
     */
    private function validateJson()
    {

        $required_fields = ['name', 'author', 'version', 'compatibility', 'description'];
        $plugin_json_file = $this->plugin_path . DIRECTORY_SEPARATOR . $this->plugin_name . DIRECTORY_SEPARATOR . "{$this->file->baseName}.json";

        if (file_exists($plugin_json_file)) {

            $plugin_json         = file_get_contents($plugin_json_file);
            $this->plugin_config = Json::decode($plugin_json);

            if (!is_array($this->plugin_config) && empty($this->plugin_config)) {
                throw new \Exception("Plugin config file {$this->file->baseName}.json is empty.");
            }

            foreach ($required_fields as $field) {
                if (!array_key_exists($field, $this->plugin_config['metadata']) || empty($this->plugin_config['metadata'][$field])) {
                    $errors[] = $field;
                }
            }

            if (!empty($errors)) {
                throw new \Exception("Required fields:\n<b>" . implode("\n", $errors) . "</b>\n do not exist or fields are empty.");
            }

            if ($this->plugin_config['metadata']['name'] != $this->file->baseName) {
                throw new \Exception("Plugin name in config file {$this->file->baseName}.json does not match directory name.");
            }

            $min_version = $this->plugin_config['metadata']['compatibility'];
            if (version_compare($min_version, Yii::$app->version, '>')) {
                throw new \Exception("Plugin requires cBackup version {$min_version} or later");
            }

            return true;

        } else {
            throw new \Exception("Plugin config file {$this->file->baseName}.json does not exist.");
        }

    }

    /** @noinspection PhpUnusedPrivateMethodInspection, PhpUndefinedClassInspection
     *
     * Move plugin to plugins directory and add entry to database
     * Method is called dynamically in @see installPlugin() method
     *
     * @return  bool
     * @throws \Exception
     * @throws \Throwable
     */
    private function addPlugin()
    {

        if (is_array($this->plugin_config) && !empty($this->plugin_config)) {

            $metadata = $this->plugin_config['metadata'];

            /** Set new or existing model */
            $plugin = static::find()->where(['name' => $metadata['name']]);
            $model  = (!$plugin->exists()) ? new Plugin() : $plugin->one();

            /** Store previous version during installation */
            $this->previous_version = $model->version;

            /** Set plugin params */
            $model->name        = $metadata['name'];
            $model->author      = $metadata['author'];
            $model->version     = $metadata['version'];
            $model->description = $metadata['description'];
            $model->metadata    = Json::encode($metadata);
            $model->params      = $this->parseFormParams();

            /** Set widget type if exists */
            if (array_key_exists('widget', $this->plugin_config['metadata']) && !empty($this->plugin_config['metadata']['widget'])) {
                $model->widget = $this->plugin_config['metadata']['widget'];
            }

            if ($model->save()) {

                try {
                    $src_path       = $this->plugin_path . DIRECTORY_SEPARATOR . $this->plugin_name;
                    $this->dst_path = Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $this->plugin_name;
                    FileHelper::removeDirectory($this->dst_path);
                    FileHelper::copyDirectory($src_path, $this->dst_path);
                } catch (\Exception $e) {
                    $model->delete();
                    throw new \Exception($e->getMessage());
                }

                return true;

            } else {
                throw new \Exception('Unable to save plugin to database.');
            }

        } else {
            throw new \Exception('Plugin config file is corrupted.');
        }

    }

    /** @noinspection PhpUnusedPrivateMethodInspection, PhpUndefinedClassInspection
     *
     * Process SQL if sql query found in plugin directory
     * Method is called dynamically in @see installPlugin() method
     *
     * @return  bool
     * @throws \Exception
     * @throws \Throwable
     */
    private function processSql()
    {

        $sql = $this->dst_path . DIRECTORY_SEPARATOR . 'sql';

        if (file_exists($sql) && is_dir($sql) && (new \FilesystemIterator($sql))->valid()) {

            $tables_file = $this->dst_path . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . $this->prepareName($this->file->baseName, false) . 'Tables.php';

            /** Check if table instalation file exists */
            if (file_exists($tables_file)) {

                $status      = true;
                $plugin_data = static::find()->where(['name' => $this->file->baseName]);

                try {

                    $class  = 'app\\modules\\plugins\\' . $this->plugin_name . '\\sql\\' . $this->prepareName($this->file->baseName, false) . 'Tables';
                    $object = (new \ReflectionClass($class))->newInstance();

                    /** If plugin does not exist run installation method */
                    if ($this->previous_version == null) {
                        $status = $object->install();
                    }

                    /** If plugin exists and plugin versions do not match run update method */
                    if ($this->previous_version != null && version_compare($this->previous_version, $plugin_data->one()->version, '!=')) {
                        $status = $object->update();
                    }

                    return $status;

                } catch (\Exception $e) {
                    /** If exception while installing or updating database occurs delete plugin */
                    $plugin_data->one()->delete();
                    FileHelper::removeDirectory($this->dst_path);
                    throw new \Exception($e->getMessage());
                }

            }
        }

        return true;

    }

    /**
     * Remove plugin temporary data from runtime folder
     *
     * @return  bool
     * @throws \Exception
     */
    private function cleanUp()
    {

        $status = false;

        if (file_exists($this->plugin_path) && is_dir($this->plugin_path)) {
            FileHelper::removeDirectory($this->plugin_path);
            $status = true;
        }

        return $status;

    }

    /**
     * Get form default params from json fieldset
     *
     * @return string
     */
    private function parseFormParams()
    {

        $result = [];
        $form = $this->plugin_config['form'];

        foreach ($form as $fieldset) {
            foreach ($fieldset['fields'] as $field) {
                $result[$field['name']] = $field['default'];
            }
        }

        return Json::encode($result);

    }

    /**
     * Get translation from specific plugin
     *
     * @return object
     * @throws \ReflectionException
     */
    private function initPluginTranslation()
    {
        $class  = 'app\\modules\\plugins\\' . $this->prepareName($this->name) . '\\' . $this->prepareName($this->name, false);
        $object = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        $files  = (property_exists($class, 'translations')) ? $object->translations : [];

        $object->registerTranslations($this->name, $files);
        return $object;
    }

    /**
     * Prepare plugin name
     *
     * @param  string $name
     * @param  bool $lower
     * @return string
     */
    public function prepareName($name, $lower = true)
    {
        $name = Inflector::camelize($name);
        return ($lower) ? strtolower($name) : $name;
    }

}
