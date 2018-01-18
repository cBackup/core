<?php

namespace app\models;

use Y;
use Yii;
use yii\base\Model;
use yii\db\Query;
use app\modules\rbac\models\AuthItem;


/**
 * @package app\models
 */
class SupportBundle extends Model
{

    /**
     * @return string
     */
    public static function getData()
    {
        return serialize([
            'php'      => (new Sysinfo())->getPhpInfo(),
            's_server' => $_SERVER,
            'files'    => Install::checkPermissions(),
            'internet' => intval(Install::checkInternet()),
            'worldacc' => intval(Install::checkWorldAccess()),
            'system'   => [
                'cbackup_version'            => Yii::$app->version,
                'cbackup_env'                => YII_ENV,
                'cbackup_debug'              => YII_DEBUG,
                'server_platform'            => php_uname("s") . ' ' . php_uname("r"),
                'yii_version'                => Yii::getVersion(),
                'yii_db_driver'              => Yii::$app->db->driverName,
                'db_server_version'          => (new Query())->select('version()')->scalar(),
                'db_client_version'          => mysqli_get_client_info(),
                'php_version'                => phpversion(),
                'php_interface'              => php_sapi_name(),
                'java_version'               => Sysinfo::getJavaVersion(),
                'git_version'                => Sysinfo::getGitVersion(),
            ],
            'cbackup' => [
                'db_size'                    => self::getDatabaseSize(),
                'nodes_count'                => Node::find()->count(),
                'networks_count'             => Network::find()->count(),
                'devices_count'              => Device::find()->count(),
                'credentials_count'          => Credential::find()->count(),
                'tasks_count'                => Task::find()->count(),
                'tasks_has_devices_count'    => TasksHasDevices::find()->count(),
                'tasks_has_nodes_count'      => TasksHasNodes::find()->count(),
                'schedules_count'            => Schedule::find()->count(),
                'workers_count'              => Worker::find()->count(),
                'worker_task_distinct_count' => Worker::find()->select('task_name')->distinct()->count(),
                'users_count'                => User::find()->count(),
                'vendors_count'              => Vendor::find()->count(),
                'authitem_count'             => AuthItem::find()->count(),
                'messages_count'             => Messages::find()->count(),
                'default_settings_dump'      => Setting::find()->asArray()->all(),
                'user_settings_dump'         => SettingOverride::find()->asArray()->all(),
                'system_config_dump'         => self::getSystemConfig(),
                'dataPath_size'              => self::getDataDirectorySize(Y::param('dataPath')),
                'dataPath_files_amount'      => self::getDataDirectoryBackupAmount(),
                'configs_in_database'        => OutBackup::find()->count(),
                'plugins'                    => Plugin::find()->asArray()->all(),
            ]
        ]);
    }


    /**
     * Calculate current database size in bytes
     *
     * @return int
     */
    private static function getDatabaseSize()
    {

        $database = (new Query())->select("DATABASE()")->scalar();
        $db_size  = (new Query())->select('SUM(DATA_LENGTH + INDEX_LENGTH)')->from("information_schema.TABLES")->where("TABLE_SCHEMA = '$database'")->scalar();

        return intval($db_size);

    }


    /**
     * Get system configuration values dump with filtered out
     * sensitive data, such as logins and passwords
     *
     * @return array
     */
    private static function getSystemConfig()
    {
        return array_map(
            function($s){
                if( preg_match('/login|username|passw/i', $s['key']) ) {
                    $s['value'] = '*******';
                }
                return $s;
            },
            Config::find()->asArray()->all()
        );
    }


    /**
     * Get dataPath directory size in bytes
     *
     * @param  string $dir
     * @return int
     */
    private static function getDataDirectorySize($dir)
    {

        $size = 0;
        $dir  = rtrim($dir, '/\\').DIRECTORY_SEPARATOR.'{,.}*';

        $list = glob($dir, GLOB_BRACE | GLOB_NOSORT);
        $list = array_filter($list, function($v){
            return preg_match('%(\\\\|/)\.{1,2}$%im', $v) ? false : true;
        });

        foreach ($list as $element) {
            $size += is_file($element) ? filesize($element) : self::getDataDirectorySize($element);
        }

        return $size;

    }


    /**
     * Count files in $dataPath/backup directory
     *
     * @return int
     */
    private static function getDataDirectoryBackupAmount()
    {

        $f = 0;
        $d = new \DirectoryIterator(Y::param('dataPath').DIRECTORY_SEPARATOR.'backup');

        foreach ($d as $item) {

            if( $item->isFile() && !$item->isDot() ) {

                $name = $item->getBasename();

                if( $name[0] != '.' ) {
                    $f++;
                }

            }

        }

        return $f;

    }

}
