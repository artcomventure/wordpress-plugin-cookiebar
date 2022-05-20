<?php

/**
 * Plugin Name: Sid
 * Description: Named after the famous cookie monster from Sesame Street.
 * Version: 1.4.0
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

add_filter( 'load_script_translation_file', function( $file, $handle, $domain ) {
	if ( $file && $domain == 'sid' ) {
		if ( $handle == 'sid-settings' ) {
			if ( $md5 = md5('js/settings.js') )
				$file = preg_replace( '/\/' . $domain . '-([^-]+)-.+\.json$/', "/$1-{$md5}.json", $file );
		}
	}

	return $file;
}, 10, 3 );

/**
 * Get available languages to (maybe) set cookie message to.
 *
 * @return array
 */
function sid_available_languages() {
	$languages = array();

	require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
	$translations = wp_get_available_translations() + array( 'en_US' => array( 'native_name' => 'English (United States)' ) );

	if ( function_exists( 'bogo_available_locales' ) )
		$available_locales = bogo_available_locales();
	elseif ( function_exists( 'pll_languages_list' ) )
		$available_locales = pll_languages_list( array( 'fields' => 'locale' ) );
//	elseif ( function_exists( 'wpml_active_languages' ) )
//		$available_locales = wpml_active_languages();
	elseif ( function_exists( 'wpm_get_languages' ) )
		$available_locales = array_column( wpm_get_languages(), 'locale' );
	else $available_locales = array( get_locale() );

	foreach ( $available_locales as $locale ) {
		$languages[$locale] = $translations[$locale]['native_name'];
	}

	return apply_filters( 'sid_get_available_languages', $languages );
}

/**
 * Get message hash (by specific ´$locale´).
 *
 * @param null|string $locale
 * @return string|null
 */
function sid_get_cookie_hash( $locale = null ) {
	$message = sid_settings()['message'][$locale ?? get_locale()];

	return $message ? md5( $message ) : null;
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
		'rejection' => array(),
		'custom_fontsize' => '',
		'fontsize' => '14px',
		'color' => array(), // @since 1.1.3
		'indirect' => 0, // deprecated @since 1.4.0
		'position' => 'top',
		'expires' => '',
		'script' => '', // @since 1.4.0
		'noscript' => '' // @since 1.4.0
	);

	// default confirmation tag with former `type` fallback (@prior 1.2.0)
	$settings['confirmation'] += array( 'tag' => $settings['confirmation']['type'] ?? 'a' );
	if ( $settings['confirmation']['tag'] == 'link' ) $settings['confirmation']['tag'] = 'a';

	$settings['rejection'] += array( 'tag' => 'a' ); // @since 1.3.0

	// to keep backward compatibility @since 1.2.0
	$settings['color'] += array(
		'background' => '',
		'box' => empty($settings['bcolor']) ? '#ffffff' : $settings['bcolor'],
		'text' => empty($settings['tcolor']) ? '#000000' : $settings['tcolor'],
		'link' => empty($settings['lcolor']) ? '#000000' : $settings['lcolor'],
		'button' => empty($settings['bbcolor']) ? '' : $settings['bbcolor']
	);

	// remove old entries
	unset( $settings['bcolor'], $settings['tcolor'], $settings['lcolor'], $settings['bbcolor'] );

	$settings['message'] += array_map( '__return_empty_string', sid_available_languages() );
	$settings['confirmation'] += array_map( '__return_empty_string', sid_available_languages() );
	$settings['rejection'] += array_map( '__return_empty_string', sid_available_languages() );

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
 * Returns status of Sid.
 * 0: inactive
 * 1: accepted
 * 2: declined
 * '': active but not set yet
 *
 * @return int|string
 */
function sid_status() {
	if ( is_null( $hash = sid_get_cookie_hash() ) ) return 0; // no cookie banner at all

	$locale = get_locale();

	if ( ($cookie = sid_get_cookie()) && isset($cookie[$locale]) ) {
		$accepted = true; // backwards compatibility prior 1.3.0

		if ( strlen( $cookie[ $locale ] ) > 32 ) {
			$accepted = $cookie[ $locale ][0];
			$cookie[ $locale ] = substr( $cookie[ $locale ], 1 );
		}

		if ( $cookie[$locale] == $hash ) {
			// 1: accepted
			// 2: declined
			return $accepted ? 1 : 2;
		}
	}

	return ''; // no status yet
}

/**
 * Returns whether Sid is active and accepted.
 *
 * @return bool
 */
function sid_is_accepted() {
	return sid_status() === 1;
}

/**
 * Returns whether Sid is active and declined.
 *
 * @return bool
 */
function sid_is_declined() {
	return sid_status() === 2;
}

/**
 * Whether to execute scripts immediately.
 * ... in case if no cookiebar or cookiebar accepted
 *
 * @return bool
 */
function sid_do_scripts() {
	return in_array( sid_status(), array( 0, 1 ), true );
}

// auto-include first level /inc/ files
if ( $inc = opendir( $path = dirname( __FILE__ ) . '/inc' ) ) {
	while ( ($file = readdir( $inc )) !== false ) {
		if ( !preg_match( '/\.php$/', $file ) ) continue;
		require $path . '/' . $file;
	}

	closedir( $inc );
}
