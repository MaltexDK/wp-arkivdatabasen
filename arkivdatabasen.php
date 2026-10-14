<?php
/*
Plugin Name: Arkivdatabasen
Plugin URI: https://github.com/MaltexDK/wp-arkivdatabasen
Description: Søgning og visning af data fra en tilknyttet Arkivdatabase.
Version: 1.3.1
Requires at least: 6.0
Requires PHP: 7.4
Author: Maltex Gruppen
Author URI: https://www.maltex.dk/
License: GPL-2.0-or-later
Update URI: https://github.com/MaltexDK/wp-arkivdatabasen
Text Domain: adb_domain
*/
if (!defined('ABSPATH')) { exit; }
define('ADB_VERSION', '1.3.1');
define('ADB_FILE', __FILE__);
$adb_plugin_name = 'Arkivdatabasen';
$adb_shortcode = '[adb]';
require_once __DIR__ . '/includes/data-processing.php';
$adb_options = adb_get_options();
require_once __DIR__ . '/includes/scripts.php';
require_once __DIR__ . '/includes/display-functions.php';
require_once __DIR__ . '/includes/admin-page.php';
require_once __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';
$adb_update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
    'https://github.com/MaltexDK/wp-arkivdatabasen', __FILE__, 'arkivdatabasen'
);
$adb_update_checker->setBranch('main');
// Never offer an unbuilt branch/tag archive as an automatic update.
$adb_update_checker->getVcsApi()->enableReleaseAssets('/^arkivdatabasen\.zip$/', YahnisElsts\PluginUpdateChecker\v5p6\Vcs\Api::REQUIRE_RELEASE_ASSETS);
$adb_update_checker->addResultFilter(function ($info) {
    if (!$info || !preg_match('~^https://github\.com/MaltexDK/wp-arkivdatabasen/releases/download/[^/]+/arkivdatabasen\.zip$~', $info->download_url ?? '')) { return null; }
    return $info;
});
