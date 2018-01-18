<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 *
 * Changed to reflect requirements for cBackup
 * Removed unnecessary extensions and requisites
 */

$frameworkPath = dirname(__FILE__) . '/../vendor/yiisoft/yii2';

if (!is_dir($frameworkPath)) {
    echo '<h1>Error</h1>';
    echo '<p><strong>The path to yii framework seems to be incorrect.</strong></p>';
    echo '<p>You need to install Yii framework via composer or adjust the framework path in file <abbr title="' . __FILE__ . '">' . basename(__FILE__) . '</abbr>.</p>';
    echo '<p>Please refer to the <abbr title="' . dirname(__FILE__) . '/README.md">README</abbr> on how to install Yii.</p>';
}

require_once($frameworkPath . '/requirements/YiiRequirementChecker.php');
$requirementsChecker = new YiiRequirementChecker();

/**
 * Adjust requirements according to your application specifics.
 */
$requirements = array(
    // Database :
    array(
        'name' => 'PDO extension',
        'mandatory' => true,
        'condition' => extension_loaded('pdo'),
        'by' => 'All DB-related classes',
    ),
    array(
        'name' => 'PDO MySQL extension',
        'mandatory' => false,
        'condition' => extension_loaded('pdo_mysql'),
        'by' => 'All DB-related classes',
        'memo' => 'Required for MySQL database.',
    ),
    // cBackup
    array(
        'name' => 'Execute functions',
        'mandatory' => true,
        'condition' => function_exists('exec'),
        'by' => 'cBackup',
        'memo' => 'Required for support bundle and backup processing.',
    ),
    array(
        'name' => 'SNMP extension',
        'mandatory' => true,
        'condition' => extension_loaded('snmp'),
        'by' => 'cBackup',
        'memo' => 'Required for equipment interaction.',
    ),
    array(
        'name' => 'GMP extension',
        'mandatory' => true,
        'condition' => extension_loaded('gmp'),
        'by' => 'cBackup',
        'memo' => 'Required for equipment interaction.',
    ),
    array(
        'name' => 'cURL extension',
        'mandatory' => true,
        'condition' => extension_loaded('curl'),
        'by' => 'cBackup',
        'memo' => 'Required for fetching external resources.',
    ),
    array(
        'name' => 'ZIP extension',
        'mandatory' => true,
        'condition' => extension_loaded('zip'),
        'by' => 'cBackup',
        'memo' => 'Required for obtaining and extracting updates and content.',
    ),
    array(
        'name' => 'SSH2 extension',
        'mandatory' => true,
        'condition' => extension_loaded('ssh2'),
        'by' => 'cBackup',
        'memo' => 'Required for equipment interaction.',
    ),
    // PHP ini :
    'phpExposePhp' => array(
        'name' => 'Expose PHP',
        'mandatory' => false,
        'condition' => $requirementsChecker->checkPhpIniOff("expose_php"),
        'by' => 'Security reasons',
        'memo' => '"expose_php" should be disabled at php.ini',
    ),
    'phpAllowUrlInclude' => array(
        'name' => 'PHP allow url include',
        'mandatory' => false,
        'condition' => $requirementsChecker->checkPhpIniOff("allow_url_include"),
        'by' => 'Security reasons',
        'memo' => '"allow_url_include" should be disabled at php.ini',
    ),
    'phpSmtp' => array(
        'name' => 'PHP mail SMTP',
        'mandatory' => false,
        'condition' => strlen(ini_get('SMTP')) > 0,
        'by' => 'Email sending',
        'memo' => 'PHP mail SMTP server required',
    ),
);

// OPcache check
if (!version_compare(phpversion(), '7.0', '>=')) {
    $requirements[] = array(
        'name'      => 'APC extension',
        'mandatory' => false,
        'condition' => extension_loaded('apc'),
        'by'        => '<a href="http://www.yiiframework.com/doc-2.0/yii-caching-apccache.html">ApcCache</a>',
    );
}

$requirementsChecker->checkYii()->check($requirements)->render();
