<?php

/******************************
* script control
******************************/

global $stylesheet;
$stylesheet = $adb_options['stylesheet'];

function adb_load_scripts()
{
	global $stylesheet;
	if($stylesheet=="") {
		$css="css/adb-style-standard.css";
	} else{
		$css="css/adb-style-".$stylesheet.".css";
	}
	wp_enqueue_style('adb-styles', plugin_dir_url( __FILE__ ) . $css);
}
add_action('wp_enqueue_scripts', 'adb_load_scripts');

function adb_goback($text='Gå tilbage')
{
	$goback = '<a href="" onclick="goBack()">'.$text.'</a>

				<script>
					function goBack() {
				    window.history.back();
				}
				</script>';
	return $goback;
}