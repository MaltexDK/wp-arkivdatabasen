<?php
if (!defined('ABSPATH')) { exit; }
function adb_load_scripts() {
    global $adb_options;
    $style = adb_choice($adb_options['stylesheet'], ['standard','valby'], 'standard');
    wp_enqueue_style('adb-styles', plugins_url('includes/css/adb-style-'.$style.'.css', ADB_FILE), [], ADB_VERSION);
    wp_enqueue_style('adb-ui', plugins_url('includes/css/adb-ui.css', ADB_FILE), [], ADB_VERSION);
    wp_enqueue_script('adb-sorttable', plugins_url('includes/sorttable.js', ADB_FILE), [], ADB_VERSION, true);
    wp_enqueue_script('adb-ui', plugins_url('includes/adb-ui.js', ADB_FILE), ['adb-sorttable'], ADB_VERSION, true);
}
add_action('wp_enqueue_scripts', 'adb_load_scripts');
