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

namespace app\components;

use Yii;
use GitWrapper\GitWrapper;
use toriphes\console\Runner;
use Ifsnop\Mysqldump as IMysqldump;


/**
 * @package app\components
 */
class Updater
{

    /**
     * Path to git repo
     *
     * @var string
     */
    private static $git_url = 'https://github.com/cBackup/updates.git';

    /**
     * @var string
     */
    private $base_path;

    /**
     * @var string
     */
    private $export_path;

    /**
     * @var string
     */
    private $file_name = 'dump.sql';

    /**
     * @var string
     */
    private $version_file;

    /**
     * @var \GitWrapper\GitWrapper
     */
    private $workingCopy;

    /**
     * Updater constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {

        if(YII_ENV_DEV) {
            throw new \Exception(Yii::t('update', 'Unable to proceed with live update in "development" environment, only "production" and "test" environments can run updater'));
        }

        $this->base_path    = Yii::$app->basePath;
        $this->export_path  = Yii::getAlias('@runtime/backup');
        $this->version_file = Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'old_version.txt';
        $this->workingCopy  = (new GitWrapper(\Y::param('gitPath')))->workingCopy($this->base_path);

    }


    /**
     * @return string
     */
    public static function getGitUrl(): string
    {
        return self::$git_url;
    }


    /**
     * Init git repo
     * Run once during installation
     *
     * @param  string $branch
     * @return void
     */
    public function initGitRepo($branch = 'master')
    {

        $wrapper = new GitWrapper(\Y::param('gitPath'));

        /** Get working copy */
        $git = $wrapper->init($this->base_path);

        /** Create config file */
        $git->config('remote.origin.url', static::$git_url)
            ->config('remote.origin.fetch', "+refs/heads/{$branch}:refs/remotes/origin/{$branch}")
            ->config("branch.{$branch}.remote", 'origin')
            ->config("core.filemode", 'false')
            ->config("branch.{$branch}.merge", "refs/heads/{$branch}");

        /** Fetch selected branch from remote origin */
        $git->fetch('origin');

        /** Create/Change local branch */
        $git->clearOutput();
        $git->branch($branch, ['list' => true]);
        if (empty($git->getOutput())) {
            $git->checkout("{$branch}", ['b' => true]); // Create new branch
        } else {
            $git->checkout("{$branch}"); // Checkout existing branch
        }

        /** Set local branch hash to remote origin branch hash */
        $git->reset("origin/{$branch}", ['mixed' => true]);

    }


    /**
     * Check for updates on origin branch
     *
     * @return bool
     * @throws \Exception
     */
    public function checkForUpdates()
    {

        try {

            $wrapper = new GitWrapper(\Y::param('gitPath'));

            /** Get working copy */
            $git    = $wrapper->workingCopy($this->base_path);
            $branch = $this->getWorkingBranch();

            /** Fetch changes from remote origin */
            $git->fetch('origin');

            /** Get diff */
            $git->clearOutput();
            $git->diff($branch, "origin/{$branch}");

            return (!empty($git->getOutput())) ? true : false;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

    }


    /**
     * Get origin version and release description
     *
     * @return array|string
     * @throws \Exception
     */
    public function getOriginVersion()
    {

        /** Get working copy */
        $git    = $this->workingCopy;
        $branch = $this->getWorkingBranch();
        $info   = [];

        /**
         * Get release info
         *   for git v2.8+ additional argument can be
         *   'format' => "%(refname:short)|%(subject)",
         *   and you want to explode("|", trim($git->getOutput())); then
         */
        $git->tag("origin/{$branch}", [
            'n'         => true,
            'points-at' => true
        ]);

        preg_match('/(\d+\.\d+\.\d+)(.+)/', $git->getOutput(), $info);

        if( !array_key_exists(1, $info) ) {
            throw new \Exception(Yii::t('update', 'Unable to retrieve valid version from origin'));
        }

        return [
            'version' => $info[1],
            'message' => $info[2] ?? null,
        ];

    }


    /**
     * Get APP previous version
     *
     * @return string
     */
    public function getPreviousVersion()
    {
        return trim(file_get_contents($this->version_file));
    }


    /**
     * Update cBackup files
     *
     * @return  bool
     * @throws \Exception
     */
    public function updateFiles()
    {

        try {

            $update_status = true;

            if ($this->checkForUpdates()) {

                /** Get working copy */
                $git    = $this->workingCopy;
                $branch = $this->getWorkingBranch();

                /** Store app previous version */
                $this->storeAppVersion();

                /** Remove all user created changes */
                $git->reset($branch, ['hard' => true]);

                /** Pull changes to master branch */
                $git->pull();

                $update_status = true;
            }

            return $update_status;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

    }


    /**
     * Update cBackup database
     *
     * @return bool
     * @throws \Exception
     */
    public function updateDatabase()
    {
        try {

            $update_status = true;

            if (!preg_match('/up-to-date./i', $this->exec('migrate/new'))) {
                if ($this->createDump()) {
                    $run_migration = $this->exec('migrate/up --interactive=0');
                    if (preg_match('/Migrated up successfully./i', $run_migration)) {
                        unlink($this->export_path . DIRECTORY_SEPARATOR . $this->file_name);
                        $update_status = true;
                    } else {
                        $this->revertFileChanges();
                        $this->revertDatabseChanges();
                        unlink($this->export_path . DIRECTORY_SEPARATOR . $this->file_name);
                        throw new \Exception("Migration failed! \n" . $run_migration);
                    }
                } else {
                    $this->revertFileChanges();
                    throw new \Exception(Yii::t('update', 'Error while creating SQL dump'));
                }
            }

            return $update_status;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    /**
     * Get Java service status
     *
     * @return array
     */
    public function getServiceStatus()
    {
        $response = ['init' => true, 'status' => false];

        try {
            $response['status'] = (new Service())->init()->isServiceActive();
        } catch (\Exception $e) {
            $response['init']  = false;
        }

        return $response;
    }


    /**
     * Get working repo branch
     *
     * @return  string
     * @throws \Exception
     */
    private function getWorkingBranch()
    {
        try {
            return trim($this->workingCopy->getWrapper()->git("--git-dir=" . $this->base_path . DIRECTORY_SEPARATOR . ".git rev-parse --abbrev-ref HEAD"));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    /**
     * Create database dump
     *
     * @return bool
     */
    private function createDump()
    {
        try {

            $db = Yii::$app->db;

            /** Create dir if not exists */
            if (!file_exists($this->export_path) && !@mkdir($this->export_path)) {
                throw new \Exception(Yii::t('update', 'Could not create backup directory {0}', $this->export_path));
            }

            /** Create database dump */
            $dump = new IMysqldump\Mysqldump($db->dsn, $db->username, $db->password, ['add-drop-table' => true]);
            $dump->start($this->export_path . DIRECTORY_SEPARATOR . $this->file_name);

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * Revert files changes to previous version
     *
     * @return bool
     * @throws \Exception
     */
    private function revertFileChanges()
    {
        try {

            /** Get working copy */
            $git     = $this->workingCopy;
            $version = $this->getPreviousVersion();

            /** Revert file changes to previous version */
            $git->reset($version, ['hard' => true]);

            return true;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    /**
     * Revert database changes to previous version
     *
     * @return bool
     * @throws \Exception
     */
    private function revertDatabseChanges()
    {

        $dump_file = $this->export_path . DIRECTORY_SEPARATOR . $this->file_name;

        if (file_exists($dump_file)) {

            $dump = file_get_contents($dump_file);

            try {

                /** Truncate all tables */
                Yii::$app->db->createCommand("SET foreign_key_checks = 0")->execute();
                $tables = Yii::$app->db->schema->getTableNames();
                foreach ($tables as $table) {
                    Yii::$app->db->createCommand()->dropTable($table)->execute();
                }
                Yii::$app->db->createCommand("SET foreign_key_checks = 1")->execute();

                /** Execute SQL dump */
                $cmd = Yii::$app->db->createCommand($dump);
                $cmd->execute();

                return true;

            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        } else {
            throw new \Exception(Yii::t('update', 'SQL dump file not found'));
        }

    }


    /**
     * Store previous app version for rollback purpose
     *
     * @return bool
     */
    private function storeAppVersion()
    {
        return (file_put_contents($this->version_file, Yii::$app->version)) ? true : false;
    }


    /**
     * Execute console command
     *
     * @param  string $cmd
     * @return string
     */
    private function exec($cmd)
    {

        $output = '';
        $runner = new Runner();

        /** error in phpdoc for run() method  */
        /** @noinspection PhpParamsInspection */
        $runner->run($cmd, $output);
        return $output;

    }

}
