<?php
/**
 * @package MFBJOBSAPI
 * @version 1.0
 */
/*
Plugin Name: MFBJOBSAPI
Plugin URI: follows
Description: A Plugin, to share jobs automatically with Bundesagentur für Arbeit.
Author: Bjoern Zschernack
Version: 1.0
Author URI: http://madeforbrowser.com
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'class-mfbjobsapi-plugin-setup.php';
//require_once 'includes/mfbjobsapi-plugin.php';
$pluginHolder = new MFBJOBSAPI();

// Run Plugin.
//$pluginHolder->shortcoder();

?>
