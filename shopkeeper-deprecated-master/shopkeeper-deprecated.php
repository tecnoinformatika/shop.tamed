<?php

/**
 * Plugin Name:       		Shopkeeper Deprecated Features
 * Plugin URI:        		https://shopkeeper.wp-theme.design/
 * Description:       		Old features of Shopkeeper theme that are no longer used.
 * Version:           		1.1
 * Author:            		GetBowtied
 * Author URI:				https://getbowtied.com
 * Text Domain:				shopkeeper-deprecated
 * Domain Path:				/languages/
 * Requires at least: 		5.0
 * Tested up to: 			5.2.1
 *
 * @package  Shopkeeper Deprecated
 * @author   GetBowtied
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// Plugin Updater
require 'core/updater/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://raw.githubusercontent.com/getbowtied/shopkeeper-deprecated/master/core/updater/assets/plugin.json',
	__FILE__,
	'shopkeeper-deprecated'
);

global $theme;
$theme = wp_get_theme();
$parent_theme = $theme->parent();

if ( $theme->template == 'shopkeeper') {

	include_once('includes/shortcodes/icon-box.php');
	
	// Add new WP shortcodes to VC
	add_action( 'plugins_loaded', function() {
		global $theme, $parent_theme;

		if ( defined(  'WPB_VC_VERSION' ) ) {

			// Icon Box VC Element
			include_once('includes/shortcodes/vc/icon-box.php');

			if( $theme->version >= '2.8.4' || ( !empty($parent_theme) && $parent_theme->version >= '2.8.4' ) ) {

				// Modify and remove existing shortcodes from VC
				include_once('includes/wpbakery/custom_vc.php');
				
				// VC Templates
				$vc_templates_dir = dirname(__FILE__) . '/includes/wpbakery/vc_templates/';
				vc_set_shortcodes_templates_dir($vc_templates_dir);
			}
		}
	});
}
