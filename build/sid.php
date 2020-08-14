<?php

/**
 * Plugin Name: Sid
 * Description: Named after the famous cookie monster from Sesame Street.
 * Version: 1.0.0
 * Text Domain: sid
 * Author: artcom venture GmbH
 * Author URI: http://www.artcom-venture.de/
 */

if ( ! defined( 'SID_PLUGIN_URL' ) ) {
	define( 'SID_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'SID_PLUGIN_DIR' ) ) {
	define( 'SID_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SID_COOKIE_NAME' ) ) {
	define( 'SID_COOKIE_NAME', 'sid' );
}

/**
 * T9n.
 */
add_action( 'after_setup_theme', 'sid_t9n' );
function sid_t9n() {
	load_plugin_textdomain( 'sid', false,  basename(SID_PLUGIN_DIR ) . '/languages' );
}

/**
 * Get available languages to (maybe) set cookie message to.
 *
 * @return array
 */
function sid_available_languages() {
	$languages = array();

	require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
	$translations = wp_get_available_translations();

	foreach ( get_available_languages() as $language ) {
		$languages[$language] = $translations[$language]['native_name'];
	}

	return apply_filters( 'sid_get_available_languages', $languages );
}

/**
 * Get message hash (by specific ´$locale´).
 *
 * @param null|string $locale
 * @return string
 */
function sid_get_cookie_hash( $locale = null ) {
	if ( !$locale ) $locale = get_locale();

	$settings = sid_settings();

	return md5( $settings['message'][$locale] );
}

/**
 * Get all Sid settings or specific ´$setting´.
 * Defaults included.
 *
 * @param null|string $setting
 * @return mixed
 */
function sid_settings( $setting = null ) {
	$settings = get_option( 'sid', array() );
	$settings += array(
		'message' => array(),
		'confirmation' => array(),
		'fontsize' => '14px',
		'bcolor' => '', 'tcolor' => '', 'lcolor' => '', 'bbcolor' => '',
		'indirect' => 0,
		'position' => 'top'
	);

	$settings['confirmation'] += array( 'type' => 'link' );

	$settings['bcolor'] = $settings['bcolor'] ?: '#ffffff';
	$settings['tcolor'] = $settings['tcolor'] ?: '#000000';
	$settings['lcolor'] = $settings['lcolor'] ?: '#000000';

	$settings['message'] += array_map( '__return_empty_string', sid_available_languages() );
	$settings['confirmation'] += array_map( '__return_empty_string', sid_available_languages() );

	if ( $setting ) {
		if ( isset($settings[$setting]) ) return $settings[$setting];
		return null;
	}

	return $settings;
}

/**
 * Get Sid's cookie value.
 *
 * @return array|null
 */
function sid_get_cookie() {
	if ( isset($_COOKIE[SID_COOKIE_NAME]) && $cookie = json_decode( urldecode( stripslashes( $_COOKIE['sid'] ) ) ) ) {
		return (array) $cookie;
	}

	return null;
}

/**
 * Returns whether Sid is accepted or not (yet).
 *
 * @return bool
 */
function sid_is_accepted() {
	return ($cookie = sid_get_cookie())
	       && isset($cookie[get_locale()]) && $cookie[get_locale()] == sid_get_cookie_hash();
}

// auto-include first level /inc/ files
if ( $inc = opendir( $path = dirname( __FILE__ ) . '/inc' ) ) {
	while ( ($file = readdir( $inc )) !== false ) {
		if ( !preg_match( '/\.php$/', $file ) ) continue;
		require $path . '/' . $file;
	}

	closedir( $inc );
}