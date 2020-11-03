<?php

/**
 * Set cookie by request (not javascript).
 */
add_action( 'template_redirect', 'set_sid_cookie');
function set_sid_cookie() {
	if ( isset($_GET[SID_COOKIE_NAME]) ) {
		setcookie( SID_COOKIE_NAME, urlencode( json_encode( $_GET[SID_COOKIE_NAME] + (sid_get_cookie() ?: array()) ) ) );

		global $wp;
		// reload current page
		wp_redirect( home_url( add_query_arg( $wp->query_vars, $wp->request ) ) );
		exit;
	}
}

/**
 * Enqueue scripts and styles.
 */
add_action( 'wp_enqueue_scripts', 'sid_scripts' );
function sid_scripts() {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	$plugin_data = get_plugin_data( SID_PLUGIN_DIR . 'sid.php' );

	$settings = sid_settings();
	if ( empty($settings['message'][get_locale()]) ) return;

	wp_enqueue_script( 'cookiebar', SID_PLUGIN_URL . 'js/cookiebar.min.js', array(), $plugin_data['Version'] );
	wp_localize_script( 'cookiebar', 'Sid', array(
	        'accepted' => sid_is_accepted(),
	        'settings' => $settings
    ) );

	wp_enqueue_style( 'cookiebar', SID_PLUGIN_URL . 'css/cookiebar.min.css', array(), $plugin_data['Version'] );

	// cookiebar colors
	$inline_style = "#cookiebar {
        background-color: " . $settings['bcolor'] . ";
        color: " . $settings['tcolor'] . ";
        font-size: " . $settings['fontsize'] . ";
    }
    
    #cookiebar .inner,
    #cookiebar[data-position=\"middle\"] {
        max-width: " . $GLOBALS['content_width'] . "px;
    }";

	// cookiebar link color
	if ( $settings['lcolor'] ) $inline_style .= "#cookiebar a {
        color: " . $settings['lcolor'] . ";
    }";

	// cookiebar button color
    $bbcolor = $settings['bbcolor'] ?: $settings['lcolor'];

    // #XYZ to #XXYYZZ
    if ( strlen($bbcolor) == 4 )
	    $bbcolor = '#' . $bbcolor[1] . $bbcolor[1] . $bbcolor[2] . $bbcolor[2] . $bbcolor[3] . $bbcolor[3];

	$r = hexdec($bbcolor[1].$bbcolor[2]);
	$g = hexdec($bbcolor[3].$bbcolor[4]);
	$b = hexdec($bbcolor[5].$bbcolor[6]);

	$btcolor = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255 > .5 ? '#000' : '#fff';

	$inline_style .= "#cookiebar a[data-type=\"button\"] {
        background-color: " . $bbcolor . ";
        color: " . $btcolor . ";
    } 
    
    #cookiebar a[data-type=\"button\"]:before {
        background-color: " . $btcolor . "; 
    }";

	// cookiebar button color
	$inline_style .= "";

	wp_add_inline_style( 'cookiebar', $inline_style );
}

/**
 * Add cookiebar markup to template.
 */
add_action( 'wp_footer', 'sid_render_cookiebar' );
function sid_render_cookiebar() {
    if ( sid_is_accepted() ) return;

	$settings = sid_settings();

	if ( $message = $settings['message'][get_locale()] ) {
		global $wp;
		if ( !$confirmation = $settings['confirmation'][get_locale()] ) $confirmation = __( 'Accept', 'sid' );
		$confirmation = '<a class="accept-cookies" data-type="' . $settings['confirmation']['type'] . '" href="' . home_url( add_query_arg( array( 'sid[' . get_locale() . ']' => sid_get_cookie_hash() ) + $wp->query_vars, $wp->request ) ) . '"><span>' . $confirmation . '</span></a>'; ?>

        <div id="cookiebar" data-position="<?php echo $settings['position']; ?>" data-locale="<?php echo get_locale(); ?>" data-hash="<?php echo sid_get_cookie_hash(); ?>">
            <div class="inner"><?php echo wpautop( $message . ' ' . $confirmation ); ?></div>
        </div>
	<?php }
}