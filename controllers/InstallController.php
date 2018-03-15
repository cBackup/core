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

namespace app\controllers;

use app\components\NetSsh;
use app\helpers\SystemHelper;
use app\models\Config;
use app\models\Install;
use Yii;
use yii\db\Connection;
use yii\db\Exception;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


/**
 * @package app\controllers
 */
class InstallController extends Controller
{

    /**
     * @var array
     */
    private $installer = [
        'index',
        'requirements',
        'database',
        'integrity',
        'finalize'
    ];

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws NotFoundHttpException
     * @throws \yii\base\ExitException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {

        if( file_exists(Yii::$app->basePath.DIRECTORY_SEPARATOR.'install.lock') ) {
            throw new NotFoundHttpException();
        }

        if($action->id != 'restart') {

            $complete = intval(Yii::$app->session->get('complete'));
            $search   = array_search($action->id, $this->installer);

            if( $search > $complete+1 ) {
                $this->redirect([$this->installer[$complete]]);
                Yii::$app->end();
            }

        }

        Yii::$app->language = Yii::$app->session->get('language');
        return parent::beforeAction($action);

    }

    /**
     * @param  string $lang
     * @return string
     */
    public function actionIndex($lang = 'en-US')
    {

        if(Yii::$app->request->isPost) {
            $lang = Yii::$app->request->post('language') ?? $lang;
            Yii::$app->session->set('language', $lang);
            Yii::$app->session->set('complete', 0);
            return $this->redirect(['requirements']);
        }

        Yii::$app->session->removeAllFlashes();
        Yii::$app->session->removeAll();
        Yii::$app->session->set('language', $lang);
        Yii::$app->language = $lang;
        return $this->render('index');

    }

    /**
     * @return string
     */
    public function actionRequirements()
    {

        if(Yii::$app->request->isPost) {
            Yii::$app->session->set('complete', 1);
            return $this->redirect(['database']);
        }

        return $this->render('requirements', [
            'extensions' => Install::getPhpExtensions(),
        ]);

    }

    /**
     * @return string
     */
    public function actionDatabase()
    {

        $model = new Install();

        if (isset($_SESSION)) {
            foreach ($_SESSION as $key => $value) {
                if (is_string($key) && array_key_exists($key, $model->attributes)) {
                    $model->{$key} = $value;
                }
            }
        }

        if(Yii::$app->request->isPost) {

            $model->load(Yii::$app->request->post());

            Yii::$app->session->set('syspassword', $model->syspassword);
            Yii::$app->session->set('timezone', $model->timezone);
            Yii::$app->session->set('username', $model->username);
            Yii::$app->session->set('password', $model->password);
            Yii::$app->session->set('gitpath', $model->gitpath);
            Yii::$app->session->set('schema', $model->schema);
            Yii::$app->session->set('email', $model->email);
            Yii::$app->session->set('host', $model->host);
            Yii::$app->session->set('port', $model->port);
            Yii::$app->session->set('path', $model->path);
            Yii::$app->session->set('threads', $model->threads);
            Yii::$app->session->set('java_port', $model->java_port);
            Yii::$app->session->set('java_username', $model->java_username);
            Yii::$app->session->set('java_password', $model->java_password);
            Yii::$app->session->set('server_password', $model->server_password);
            Yii::$app->session->set('server_login', $model->server_login);
            Yii::$app->session->set('server_port', $model->server_port);
            Yii::$app->session->set('systeminit', $model->systeminit);

            if( $model->validate() ) {
                Yii::$app->session->set('complete', 2);
                return $this->redirect(['integrity']);
            }

        }

        return $this->render('database', [
            'model' => $model,
        ]);

    }

    /**
     * @return string
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function actionIntegrity()
    {

        if(Yii::$app->request->isPost) {

            $tz    = Yii::$app->session->get('timezone');
            $dbn   = Yii::$app->session->get('schema');
            $cfg   = Yii::$app->basePath.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR;
            $key   = Yii::$app->security->generateRandomString(8).'-';
            $key  .= Yii::$app->security->generateRandomString(24);
            $jkey  = SystemHelper::generateToken();                  /** @var string $jkey  javacore user token        */
            $dsn   = 'mysql:host='.Yii::$app->session->get('host');  /** @var string $dsn   database connection string */
            $dsn  .= ";dbname=$dbn";
            $dsn  .= ';port='.Yii::$app->session->get('port');
            $pass1 = Yii::$app->session->get('password');            /** @var string $pass1 database password          */
            $user1 = Yii::$app->session->get('username');            /** @var string $user1 database username          */
            $pass2 = Yii::$app->session->get('syspassword');         /** @var string $pass2 system   password          */
            $path  = Yii::$app->session->get('path');                /** @var string $path  storage folder path        */
            $email = Yii::$app->session->get('email');               /** @var string $email administrator's email      */
            $lang  = Yii::$app->session->get('language');            /** @var string $lang  system language            */
            $git   = Yii::$app->session->get('gitpath');             /** @var string $git   path to git executable     */
            $thrds = Yii::$app->session->get('threads');
            $init  = Yii::$app->session->get('systeminit');
            $ssh   = [
                'port' => Yii::$app->session->get('java_port'),
                'user' => Yii::$app->session->get('java_username'),
                'pass' => Yii::$app->session->get('java_password'),
            ];
            $srv    = [
                'port' => Yii::$app->session->get('server_port'),
                'user' => Yii::$app->session->get('server_login'),
                'pass' => Yii::$app->session->get('server_password'),
            ];

            $file_db  = file_get_contents("{$cfg}db.php");
            $sql_1    = file_get_contents(Yii::$app->basePath.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'schema.sql');
            $sql_2    = file_get_contents(Yii::$app->basePath.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'data.sql');
            $internet = intval(Install::checkInternet());
            $uri      = Install::getDocumentUri();
            $uri      = str_replace('install/', '', $uri);

            try {

                $db = new Connection(['dsn' => $dsn, 'username' => $user1, 'password' => $pass1]);
                $db->open();

                $f1 = file_put_contents("{$cfg}db.php", /** @lang PHP */
                    "<?php\n\nreturn [\n\t'class' => 'yii\db\Connection',\n\t'dsn' => '$dsn',\n\t'username' => '$user1',\n\t'password' => '$pass1',\n\t'charset' => 'utf8',\n];\n"
                );

                $f2 = file_put_contents("{$cfg}settings.ini", "cookieValidationKey = \"$key\"\ndefaultTimeZone = \"$tz\"\nserviceType = \"$init\"\n");

                if( empty($sql_1) || empty($sql_2) ) {
                    throw new \UnexpectedValueException('Unable to find import database structure');
                }
                else {
                    // Prevent duplicate deployment if user pressed 'back' button
                    if( Yii::$app->session->get('complete') < 3 ) {
                        $db->createCommand($sql_1)->execute();
                        sleep(1);
                        $db->createCommand("ALTER DATABASE $dbn CHARACTER SET utf8 COLLATE utf8_general_ci;")->execute();
                        sleep(1);
                        $db->createCommand($sql_2)->execute();
                    }
                }

                if( $f1 < 1 ) {
                    throw new \UnexpectedValueException('Database config file was not saved');
                }
                if( $f2 < 1 ) {
                    throw new \UnexpectedValueException('settings.ini file was not saved');
                }
                else {
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        '`key`'   => 'adminEmail',
                        '`value`' => $email,
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        '`key`'   => 'dataPath',
                        '`value`' => $path,
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        '`key`'   => 'isolated',
                        '`value`' => !$internet,
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        '`key`'   => 'gitEmail',
                        '`value`' => $email,
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('user', [
                        'userid'        => 'ADMIN',
                        'auth_key'      => Yii::$app->security->generateRandomString(),
                        'password_hash' => Yii::$app->security->generatePasswordHash($pass2),
                        'access_token'  => null,
                        'fullname'      => 'Admin',
                        'email'         => $email,
                        'enabled'       => 1
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('user', [
                        'userid'        => 'JAVACORE',
                        'auth_key'      => Yii::$app->security->generateRandomString(),
                        'password_hash' => Yii::$app->security->generatePasswordHash(Yii::$app->security->generateRandomString()),
                        'access_token'  => $jkey,
                        'fullname'      => 'cBackup Service',
                        'email'         => null,
                        'enabled'       => 1
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('user', [
                        'userid'        => 'CONSOLE_APP',
                        'auth_key'      => Yii::$app->security->generateRandomString(),
                        'password_hash' => Yii::$app->security->generatePasswordHash(Yii::$app->security->generateRandomString()),
                        'access_token'  => null,
                        'fullname'      => 'cBackup Console',
                        'email'         => null,
                        'enabled'       => 1
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('auth_assignment', [
                        'item_name'  => 'admin',
                        'user_id'    => 'ADMIN',
                        'created_at' => time()
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('auth_assignment', [
                        'item_name'  => 'APICore',
                        'user_id'    => 'JAVACORE',
                        'created_at' => time()
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        'key'   => 'gitPath',
                        'value' => $git
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        'key'   => 'threadCount',
                        'value' => $thrds
                    ])->execute();
                    if( $lang != 'en-US' ) {
                        /** @noinspection MissedFieldInspection */
                        $db->createCommand()->insert('setting_override', [
                            '`key`'  => 'language',
                            'userid' => 'ADMIN',
                            'value'  => $lang
                        ])->execute();
                    }
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        'key'   => 'javaSchedulerUsername',
                        'value' => $ssh['user']
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        'key'   => 'javaSchedulerPassword',
                        'value' => $ssh['pass']
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        'key'   => 'javaSchedulerPort',
                        'value' => $ssh['port']
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        'key'   => 'javaServerPort',
                        'value' => $srv['port']
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        'key'   => 'javaServerUsername',
                        'value' => $srv['user']
                    ])->execute();
                    /** @noinspection MissedFieldInspection */
                    $db->createCommand()->insert('config', [
                        'key'   => 'javaServerPassword',
                        'value' => $srv['pass']
                    ])->execute();

                    $props = "#sshd\n";
                    $props.= "sshd.shell.port={$ssh['port']}\n";
                    $props.= "sshd.shell.enabled=true\n";
                    $props.= "sshd.shell.username={$ssh['user']}\n";
                    $props.= "sshd.shell.password={$ssh['pass']}\n";
                    $props.= "sshd.shell.host=localhost\n";
                    $props.= "sshd.shell.auth.authType=SIMPLE\n";
                    $props.= "sshd.shell.prompt.title=cbackup\n";
                    $props.= "\n";
                    $props.= "#spring\n";
                    $props.= "spring.main.banner-mode=off\n";
                    $props.= "\n";
                    $props.= "#cbackup\n";
                    $props.= "cbackup.scheme=".( (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) ? 'https' : 'http' )."\n";
                    $props.= "cbackup.site=localhost{$uri}\n";
                    $props.= "cbackup.token=$jkey\n";

                    $f3 = file_put_contents(Yii::$app->basePath.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'application.properties', $props);

                    if( $f3 < 1 ) {
                        throw new \UnexpectedValueException('application.properties file was not written');
                    }

                    // init backup data directory as git repository
                    Config::runRepositoryInit('cBackup Service', $email, $git, $path);

                    Yii::$app->session->set('complete', 3);
                    return $this->redirect(['finalize']);

                }

            }
            /** @noinspection PhpUndefinedClassInspection */
            catch (\Throwable $e) {

                // Rollback
                file_put_contents("{$cfg}db.php", $file_db);
                FileHelper::removeDirectory($path.DIRECTORY_SEPARATOR.'backup'.DIRECTORY_SEPARATOR.'.git');
                Yii::$app->session->addFlash('danger', $e->getMessage());
                return $this->redirect(['integrity']);

            }

        }
        else {

            $ssh       = '';
            $database  = '';
            $locations = Install::checkPermissions();

            try {

                $db = new Connection([
                    'dsn'      => 'mysql:host='.Yii::$app->session->get('host').';dbname='.Yii::$app->session->get('schema').';port='.Yii::$app->session->get('port'),
                    'username' => Yii::$app->session->get('username'),
                    'password' => Yii::$app->session->get('password'),
                ]);
                $db->open();

                // Prevent duplicate deployment if user pressed 'back' button
                $tables = $db->createCommand('SHOW TABLES')->queryAll();

                if (!empty($tables)) {
                    if( Yii::$app->session->get('complete') < 3 ) {
                        throw new Exception('Database `' . Yii::$app->session->get('schema') . '` is not empty');
                    }
                }

            }
            /** @noinspection PhpUndefinedClassInspection */
            catch (\Throwable $e) {
                $database = $e->getMessage();
            }

            try {
                (new NetSsh())->init([
                    'ip'       => '127.0.0.1',
                    'port'     => Yii::$app->session->get('server_port'),
                    'username' => Yii::$app->session->get('server_login'),
                    'password' => Yii::$app->session->get('server_password'),
                ]);
            }
            catch(\Exception $e) {
                $ssh = $e->getMessage();
            }

        }

        return $this->render('integrity', [
            'database'  => $database,
            'locations' => $locations,
            'ssh'       => $ssh,
        ]);

    }

    /**
     * @return string
     */
    public function actionFinalize()
    {

        if(Yii::$app->request->isPost) {
            file_put_contents(Yii::$app->basePath.DIRECTORY_SEPARATOR.'install.lock', date('d.m.Y H:i:s'));
            Yii::$app->session->removeAllFlashes();
            Yii::$app->session->removeAll();
            return $this->redirect('./deleter.php');
        }

        return $this->render('finalize');

    }

}
