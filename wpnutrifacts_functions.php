<?php

/**
 * @package WP Nutrition Facts
 * @link http://www.kilukrumedia.com
 * @copyright Copyright &copy; 2014, Kilukru Media
 * @version: 1.0.2
 */

if (!function_exists('wpnutrifacts_activate')) {
	function wpnutrifacts_activate() {
	  global $wpnutrifacts_activation;
	  $wpnutrifacts_activation = true;
	}
}

if (!function_exists('_print_r')) {
	function _print_r( $var ) {
		echo '<pre>';
		print_r( $var );
		echo '</pre>';
	}
}

if (!function_exists('wpnutrifacts_update_settings_check')) {
	function wpnutrifacts_update_settings_check() {

		//Set migrate function @todo
		//if(isset($_POST['wpnutrifacts_migrate'])) wpnutrifacts_migrate();

		//Set migrate options function @todo
		//if ( ( isset( $_POST['wpnutrifacts_migrate_options'] ) )  ||
		//	 ( !get_option('wpnutrifacts_options') ) ) {
		//}
	}
}

if (!function_exists('wpnutrifacts_class_defined_error')) {
	function wpnutrifacts_class_defined_error() {
		$wpnutrifacts_class_error = "The WP Nutrition Facts class is already defined";
		if ( class_exists( 'ReflectionClass' ) ) {
			$r = new ReflectionClass( 'WP_Nutrition_Facts' );
			$wpnutrifacts_class_error .= " in " . $r->getFileName();
		}
		$wpnutrifacts_class_error .= ", preventing WP Nutrition Facts from loading.";
		echo wpnutrifacts_show_essage($wpnutrifacts_class_error, true);
	}
}

/**
 * Generic function to show a message to the user using WP's
 * standard CSS classes to make use of the already-defined
 * message colour scheme.
 *
 * @param $message The message you want to tell the user.
 * @param $errormsg If true, the message is an error, so use
 * the red message style. If false, the message is a status
  * message, so use the yellow information message style.
 */
if (!function_exists('wpnutrifacts_show_essage')) {
	function wpnutrifacts_show_essage($message, $errormsg = false)
	{
		$html = '';
		if ($errormsg) {
			$html .= '<div id="message" class="error">';
		}
		else {
			$html .= '<div id="message" class="updated fade">'; //highlight
		}

		$html .= "<p><strong>$message</strong></p></div>";

		return $html;
	}
}

if (!function_exists('wpnutrifacts_get_version')) {
	function wpnutrifacts_get_version(){
		return WPNUTRIFACTS_VERSION;
	}
}


if (!function_exists('wpnutrifacts_option_isset')) {
	function wpnutrifacts_option_isset( $option ) {
		global $wpnutrifacts_options;
		return ( ( isset( $wpnutrifacts_options[$option] ) ) && $wpnutrifacts_options[$option] );
	}
}


if ( ! function_exists( 'shortcode_exists' ) ){
/**
 * Check if a shortcode is registered in WordPress.
 *
 * Examples: shortcode_exists( 'caption' ) - will return true.
 *           shortcode_exists( 'blah' )    - will return false.
 */
function shortcode_exists( $shortcode = false ) {
	global $shortcode_tags;

	if ( ! $shortcode )
		return false;

	if ( array_key_exists( $shortcode, $shortcode_tags ) )
		return true;

	return false;
}
}



if ( ! function_exists( 'wpnutrifacts_get_option' ) ){function wpnutrifacts_get_option( $option, $default = 'yes', $args = array() ){
	$default_args = array(
		'value_type' 				=> 'option',
		'post_id' 					=> null,
		'option_array_value' 		=> 0,
	);
	// Set default value
	$args = array_merge( $default_args, $args );
	
	// Switch value type default name
	switch( $args['value_type'] ){
		case 'post_meta':
		case 'postmeta':
		case 'meta':
			$args['value_type'] = 'post_meta';
			break;
			
		default:
			$args['value_type'] = $default_args['value_type'];
		
	}
	
	// If global element already exist;
	if( isset($GLOBALS['wpnutrifacts_get_' . $args['value_type'] ]) && isset($GLOBALS['wpnutrifacts_get_' . $args['value_type'] ][$option]) ){
		return $GLOBALS['wpnutrifacts_get_' . $args['value_type'] ][$option];
	}
	
	// Return value depend of type of value
	if( $args['value_type'] == 'post_meta' && is_numeric($args['post_id']) ){
		$option_value = get_post_meta( $args['post_id'], $option, $default );
	}else{
		$option_value = get_option($option, $default );
	}
	
	// If return is an array
	if( is_array($option_value) ){
		$option_value = $option_value[$args['option_array_value']];
	}

	// If global return doesn't exist create an array
	if( !isset($GLOBALS['wpnutrifacts_get_' . $args['value_type'] ]) ){
		$GLOBALS['wpnutrifacts_get_' . $args['value_type']] = array();
	}
	
	// Set value to global return
	$GLOBALS['wpnutrifacts_get_' . $args['value_type']][$option] = $option_value;
	
	return $option_value;
}}
if ( ! function_exists( 'wpnutrifacts_get_post_meta' ) ){function wpnutrifacts_get_post_meta( $option, $default = 'yes', $args = array() ){
	return wpnutrifacts_get_option( $option, $default, $args );
}}

if ( ! function_exists( 'wpnutrifacts_get_option_on_off' ) ){function wpnutrifacts_get_option_on_off( $option, $default = 'yes', $args = array() ){
	$option_value = wpnutrifacts_get_option($option, $default, $args );
	$option_value = (  ( $option_value == 'yes' || $option_value == 'oui' ) ? true : false );
	return $option_value;
}}


