<?php
/*
Plugin Name: WP Nutrition Facts
Plugin URI: http://www.kilukrumedia.com
Description: Insert a Nutrition Facts Table to pages, posts and custom post type.
Version: 1.0.2
Author: Kilukru Media
Author URI: http://www.kilukrumedia.com
*/


/*
Copyright (C) 2012-2014 Kilukru Media, kilukrumedia.com (info AT kilukrumedia DOT com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( !session_id() ){ session_start(); } // Start session just in case

if ( ! defined( 'WPNUTRIFACTS_VERSION' ) )
{	define( 'WPNUTRIFACTS_VERSION', '1.0.2' ); }

if ( ! defined( 'WPNUTRIFACTS_VERSION_NUMERIC' ) )
{	define( 'WPNUTRIFACTS_VERSION_NUMERIC', '1000200' ); }

if ( ! defined( 'WPNUTRIFACTS_VERSION_FILETIME' ) )
{	define( 'WPNUTRIFACTS_VERSION_FILETIME', '1390505570' ); } //Set by echo time();

if ( ! defined( 'WPNUTRIFACTS_PLUGIN_DIR' ) )
{	define( 'WPNUTRIFACTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ); }

if ( ! defined( 'WPNUTRIFACTS_PLUGIN_BASENAME' ) )
{	define( 'WPNUTRIFACTS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); }

if ( ! defined( 'WPNUTRIFACTS_PLUGIN_DIRNAME' ) )
{	define( 'WPNUTRIFACTS_PLUGIN_DIRNAME', dirname( WPNUTRIFACTS_PLUGIN_BASENAME ) ); }

if ( ! defined( 'WPNUTRIFACTS_PLUGIN_URL' ) )
{	define( 'WPNUTRIFACTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); }

if ( ! defined( 'WPNUTRIFACTS_PLUGIN_CSS_URL' ) )
{	define( 'WPNUTRIFACTS_PLUGIN_CSS_URL', WPNUTRIFACTS_PLUGIN_URL . 'css/' ); }
if ( ! defined( 'WPNUTRIFACTS_PLUGIN_IMAGES_URL' ) )
{	define( 'WPNUTRIFACTS_PLUGIN_IMAGES_URL', WPNUTRIFACTS_PLUGIN_URL . 'images/' ); }
if ( ! defined( 'WPNUTRIFACTS_PLUGIN_JS_URL' ) )
{	define( 'WPNUTRIFACTS_PLUGIN_JS_URL', WPNUTRIFACTS_PLUGIN_URL . 'js/' ); }

if ( ! defined( 'WP_CONTENT_URL' ) )
{	define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' ); }
if ( ! defined( 'WP_ADMIN_URL' ) )
{	define( 'WP_ADMIN_URL', get_option( 'siteurl' ) . '/wp-admin' ); }
if ( ! defined( 'WP_CONTENT_DIR' ) )
{	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); }
if ( ! defined( 'WP_PLUGIN_URL' ) )
{	define( 'WP_PLUGIN_URL', WP_CONTENT_URL. DIRECTORY_SEPARATOR . 'plugins' ); }
if ( ! defined( 'WP_PLUGIN_DIR' ) )
{	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' ); }

define('WPNUTRIFACTS_DEFAULT_LANGUAGE', substr(get_bloginfo('language'), 0, 2) );
if( !defined('ICL_LANGUAGE_CODE') ){
	define('WPNF_ICL_LANGUAGE_CODE', WPNUTRIFACTS_DEFAULT_LANGUAGE );
}else{
	define('WPNF_ICL_LANGUAGE_CODE', ICL_LANGUAGE_CODE );
}

/**
 * Options to disabled elements
 */
//WPNUTRIFACTS_DISABLED_FRONTEND_FOOTER_LINK
//WPNUTRIFACTS_DISABLED_FRONTEND_CSS


if ( class_exists( 'WP_Nutrition_Facts' ) ) {
	add_action( 'activation_notice', 'wpnutrifacts_class_defined_error' );
	return;
}

// Require functions before Class
require_once( WPNUTRIFACTS_PLUGIN_DIR . 'wpnutrifacts_functions.php');
require_once( WPNUTRIFACTS_PLUGIN_DIR . 'wpnutrifacts_class.php');

global $mblzr, $wpnutrifacts_options, $wpnutrifacts_activation;

$wpnutrifacts_activation = false;
$wpnutrifacts = new WP_Nutrition_Facts();

////checking to see if things need to be updated

register_activation_hook( __FILE__, 'wpnutrifacts_activate' );

add_action( 'init', 'wpnutrifacts_update_settings_check' );

////end checking to see if things need to be updated


