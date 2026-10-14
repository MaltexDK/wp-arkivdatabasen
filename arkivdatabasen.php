<?php
/*
Plugin Name: Arkivdatabasen
Plugin URI:  http://www.arkivdatabasen.dk/plugin
Description: Dette plugin henter data fra en tilknyttet arkivbase. Det kr&aelig;ves at arkivdatabasens API er aktiveret og at man kender API-n&oslash;glen.
Version:     1.2
Author:      Maltexgruppen
Author URI:  http://www.maltex.dk/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/******************************
* global variables
******************************/

$adb_prefix = 'adb_';
$adb_plugin_name = 'Arkivdatabasen';
$adb_shortcode = '[adb]';

// retrieve our plugin settings from the options table
$adb_options = get_option('adb_settings');

/******************************
* includes
******************************/

include('includes/scripts.php'); // this controls all JS / CSS
include('includes/data-processing.php'); // this controls all saving of data
include('includes/display-functions.php'); // display content functions
include('includes/admin-page.php'); // the plugin options page HTML and save functions
