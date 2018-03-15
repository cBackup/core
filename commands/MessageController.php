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

namespace app\commands;

use Yii;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\VarDumper;
use yii\helpers\FileHelper;
use yii\console\Exception;


/**
 * Extracts messages to be translated from source files and provides some validation as well
 * To generate message files, use command line `./yii message/extract messages/config.php`
 * To extract sources for en, use command line `./yii message/source`
 *
 * @package app\commands
 */
class MessageController extends \yii\console\controllers\MessageController
{

    /**
     * Generates en-US files as language source for Transifex
     *
     * @throws Exception
     * @return int
     */
    public function actionSource()
    {

        $cfgfile = Yii::getAlias('@app/messages/config.php');

        /** @noinspection PhpIncludeInspection */
        $cfgfile = require($cfgfile);
        $config  = array_merge(
            $this->getOptionValues($this->action->id),
            $cfgfile,
            $this->getPassedOptionValues()
        );

        $config['sourcePath']  = Yii::getAlias($config['sourcePath']);
        $config['messagePath'] = Yii::getAlias($config['messagePath']);

        $dir = $config['messagePath'] . DIRECTORY_SEPARATOR . Yii::$app->sourceLanguage;
        if (!is_dir($dir) && !@mkdir($dir)) {
            throw new Exception("Directory '{$dir}' can not be created.");
        }
        if (!isset($config['sourcePath'], $config['languages'])) {
            throw new Exception('The configuration file must specify "sourcePath" and "languages".');
        }
        if (!is_dir($config['sourcePath'])) {
            throw new Exception("The source path {$config['sourcePath']} is not a valid directory.");
        }
        if (empty($config['format']) || !in_array($config['format'], ['php', 'po', 'pot', 'db'])) {
            throw new Exception('Format should be either "php", "po", "pot" or "db".');
        }
        if (in_array($config['format'], ['php', 'po', 'pot'])) {
            if (!isset($config['messagePath'])) {
                throw new Exception('The configuration file must specify "messagePath".');
            }
            if (!is_dir($config['messagePath'])) {
                throw new Exception("The message path {$config['messagePath']} is not a valid directory.");
            }
        }
        if (empty($config['languages'])) {
            throw new Exception('Languages cannot be empty.');
        }

        $files    = FileHelper::findFiles(realpath($config['sourcePath']), $config);
        $messages = [];

        foreach ($files as $file) {
            $messages = array_merge_recursive($messages, $this->extractMessages($file, $config['translator'], $config['ignoreCategories']));
        }

        foreach ($messages as $file => $msgs) {

            $msgs = array_values(array_unique($msgs));
            $msgs = array_combine($msgs, $msgs);
            ksort($msgs);

            $array   = preg_replace('/^\[(.*)\]$/sim', '($1)', VarDumper::export($msgs));
            $array   = preg_replace("/\n/", "\r\n", $array);
            $content = "<?php\r\n/** @noinspection HtmlUnknownTarget */\r\n\$lang = array$array;\r\n\r\nreturn \$lang;\r\n";

            if (file_put_contents("$dir/{$file}.php", $content) === false) {
                $this->stdout("Translation was NOT saved.\n\n", Console::FG_RED);
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $this->stdout("Translation saved.\n\n", Console::FG_GREEN);

        }

        return ExitCode::OK;

    }


    /**
     * Find duplicated keys in $lang folder translation files
     *
     * @param  string $lang Language code (e.g. 'ru-RU')
     * @return int
     */
    public function actionFindDuplicates($lang = 'ru-RU')
    {

        $keys  = [];
        $dupes = [];

        /** @noinspection PhpIncludeInspection */
        $legit = require_once(\Yii::$app->basePath . DIRECTORY_SEPARATOR . 'messages' . DIRECTORY_SEPARATOR . 'duplicates.php');

        try {
            $messages = new \DirectoryIterator(\Yii::$app->basePath . DIRECTORY_SEPARATOR . 'messages' . DIRECTORY_SEPARATOR . $lang);
        }
        catch (\Exception $e) {
            $this->stdout($e->getMessage());
            return ExitCode::UNSPECIFIED_ERROR;
        }

        foreach ($messages as $message) {
            if ($message->getExtension() == 'php') {

                /** @noinspection PhpIncludeInspection */
                $file = require($message->getRealPath());

                foreach ($file as $key => $value) {

                    $filename = $message->getBasename();

                    if( array_key_exists($key, $keys) ) {

                        if( array_key_exists($key, $legit) && in_array($filename, $legit[$key]) ) {
                            continue;
                        }
                        else {
                            $dupes[$key][] = $filename;
                        }

                    }
                    else {
                        $keys[$key] = $filename;
                    }
                }

            }
        }

        if( !empty($dupes) ) {
            echo "Found duplicated keys:\n";
            foreach ($dupes as $k => $v) {
                echo "  '$k' in ".join(', ', $v)." and $keys[$k]\n";
            }
        }
        else {
            echo "No duplicated keys found\n";
        }

        return ExitCode::OK;

    }


    /**
     * Checks if all languages have identical keys, don't have '' empty nor @@ unused valus
     */
    public function actionValidate()
    {

        $messages = null;
        $folder   = Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'messages' . DIRECTORY_SEPARATOR;

        foreach (glob("{$folder}*", GLOB_ONLYDIR|GLOB_MARK) as $dir) {

            foreach (glob("{$dir}*.php") as $file) {

                $language = basename(dirname($file));
                $category = basename($file, '.php');

                /** @noinspection PhpIncludeInspection */
                $messages[$language][$category] = require_once($file);

            }

        }

        $empty      = null;
        $unused     = null;
        $mismatches = null;
        $languages  = array_keys($messages);

        foreach ($languages as $language) {

            foreach ($messages[$language] as $category => $values) {

                foreach ($values as $key => $value) {
                    if( empty($value) ) $empty[$language][$category][] = $key;
                    if( preg_match('/(?:^@@)|(?:@@$)/', $value) ) $unused[$language][$category][] = $key;
                }

            }

        }

        reset($languages);
        $init_lang = current($languages);

        foreach ($messages[$init_lang] as $cat => $msgs) {

            foreach (array_slice($messages, 1) as $lang_code => $lang_data) {

                $diff1 = array_diff_key($messages[$init_lang][$cat], $messages[$lang_code][$cat]);
                $diff2 = array_diff_key($messages[$lang_code][$cat], $messages[$init_lang][$cat]);

                if( !empty($diff1) ) {
                    $mismatches[$lang_code][$cat] = array_keys($diff1);
                }

                if( !empty($diff2) ) {
                    $mismatches[$lang_code][$cat] = array_keys($diff2);
                }

            }

        }

        if( !empty($empty) ) {

            $this->stdout("Empty translations", Console::BG_RED);
            echo "\n";

            foreach ($empty as $lang => $values) {
                foreach ($values as $cat => $data) {
                    $this->stdout("- $lang/$cat", Console::FG_RED);
                    echo "\n";
                    echo "  ".join("\n  ", $data);
                }
                echo "\n";
            }
        }

        if( !empty($unused) ) {

            $this->stdout("Unused translations", Console::BG_RED);
            echo "\n";

            foreach ($unused as $lang => $values) {
                foreach ($values as $cat => $data) {
                    $this->stdout("- $lang/$cat", Console::FG_RED);
                    echo "\n";
                    echo "  ".join("\n  ", $data);
                }
                echo "\n";
            }
        }

        if(!empty($mismatches)) {

            $this->stdout("Mismatches in translations' keys", Console::BG_RED);
            echo "\n";

            foreach ($mismatches as $language => $data) {

                echo "┌ ";
                $this->stdout($language, Console::FG_RED);
                echo "\n";

                foreach ($data as $c => $keys) {
                    echo "├ "; $this->stdout($c, Console::FG_YELLOW);
                    echo "\n";
                    echo "│─ ".join("\n│─ ", $keys);
                    echo "\n";
                }
            }
        }

        if(empty($unused) && empty($empty) && empty($mismatches)) {
            $this->stdout("Everything is ok", Console::BG_GREEN);
        }

    }


    /**
     * Writes category messages into PHP file
     *
     * @param array $messages
     * @param string $fileName name of the file to write to
     * @param bool $overwrite if existing file should be overwritten without backup
     * @param bool $removeUnused if obsolete translations should be removed
     * @param bool $sort if translations should be sorted
     * @param string $category message category
     * @param bool $markUnused if obsolete translations should be marked
     * @return int exit code
     */
    protected function saveMessagesCategoryToPHP($messages, $fileName, $overwrite, $removeUnused, $sort, $category, $markUnused)
    {
        if (is_file($fileName)) {

            /** @noinspection PhpIncludeInspection */
            $rawExistingMessages = require($fileName);
            $existingMessages    = $rawExistingMessages;

            sort($messages);
            ksort($existingMessages);

            if (array_keys($existingMessages) === $messages && (!$sort || array_keys($rawExistingMessages) === $messages)) {
                $this->stdout("Nothing new in \"$category\" category... Nothing to save.\n\n", Console::FG_GREEN);
                return ExitCode::OK;
            }

            unset($rawExistingMessages);

            $todo         = [];
            $merged       = [];
            $untranslated = [];

            foreach ($messages as $message) {
                if (array_key_exists($message, $existingMessages) && $existingMessages[$message] !== '') {
                    $merged[$message] = $existingMessages[$message];
                }
                else {
                    $untranslated[] = $message;
                }
            }

            ksort($merged);
            sort($untranslated);

            foreach ($untranslated as $message) {
                $todo[$message] = '';
            }

            ksort($existingMessages);

            foreach ($existingMessages as $message => $translation) {
                if (!$removeUnused && !isset($merged[$message]) && !isset($todo[$message])) {
                    if (!empty($translation) && (!$markUnused || (strncmp($translation, '@@', 2) === 0 && substr_compare($translation, '@@', -2, 2) === 0))) {
                        $todo[$message] = $translation;
                    }
                    else {
                        $todo[$message] = '@@' . $translation . '@@';
                    }
                }
            }

            $merged = array_merge($todo, $merged);

            if ($sort) {
                ksort($merged);
            }

            if (false === $overwrite) {
                $fileName .= '.merged';
            }

            $this->stdout("Translation merged.\n");

        }
        else {

            $merged = [];

            foreach ($messages as $message) {
                $merged[$message] = '';
            }

            ksort($merged);

        }

        $array   = preg_replace('/^\[(.*)\]$/sim', '($1)', VarDumper::export($merged));
        $content = "<?php\r\n/** @noinspection HtmlUnknownTarget */\r\n\$lang = array$array;\r\n\r\nreturn \$lang;\r\n";

        if (file_put_contents($fileName, $content) === false) {
            $this->stdout("Translation was NOT saved.\n\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Translation saved.\n\n", Console::FG_GREEN);
        return ExitCode::OK;

    }

}
