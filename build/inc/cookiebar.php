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
	$inline_style = "#cookiebar .inner {
        color: " . $settings['color']['text'] . ";
        font-size: " . $settings['fontsize'] . ";
    }
    
    #cookiebar .inner {
        max-width: " . $GLOBALS['content_width'] . "px;
    }";

	// overlay color
    if ( $settings['position'] == 'middle' ) {
        $inline_style .= "#cookiebar .inner {
            background-color: " . $settings['color']['box'] . ";
        }";

        if ( $settings['color']['background'] ) $inline_style .= "#cookiebar:before {
            background-color: " . $settings['color']['background'] . ";
        }";
    }
    else $inline_style .= "#cookiebar {
            background-color: " . $settings['color']['box'] . ";
        }";

	// cookiebar link color
	if ( $settings['color']['link'] ) $inline_style .= "#cookiebar a {
        color: " . $settings['color']['link'] . ";
    }";

	// cookiebar button color
    if ( $button_color = $settings['color']['button'] ?? $settings['button'] ) {
	    // #XYZ to #XXYYZZ
	    if ( strlen($button_color) == 4 )
		    $button_color = '#' . $button_color[1] . $button_color[1] . $button_color[2] . $button_color[2] . $button_color[3] . $button_color[3];

	    $r = hexdec($button_color[1].$button_color[2]);
	    $g = hexdec($button_color[3].$button_color[4]);
	    $b = hexdec($button_color[5].$button_color[6]);

	    $button_text_color = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255 > .5 ? '#000' : '#fff';

	    $inline_style .= "#cookiebar a[data-type=\"button\"] {
        background-color: " . $button_color . ";
        color: " . $button_text_color . ";
    } 
    
    #cookiebar a[data-type=\"button\"]:before {
        background-color: " . $button_text_color . "; 
    }";
    }

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
		$confirmation = '<a class="accept-cookies" data-type="' . $settings['confirmation']['type'] . '" href="' . home_url( add_query_arg( array( 'sid[' . get_locale() . ']' => sid_get_cookie_hash() ) + $wp->query_vars, $wp->request ) ) . '"><span>' . $confirmation . '</span></a>';

		if ( preg_match( '/(<p[^>]*>)\S*<\/p>$/', $message ) )
            $message = preg_replace( '/(<p[^>]*>)\S*<\/p>$/', "$1${confirmation}</p>", $message );
        else $message = wpautop( $message . ' ' . $confirmation ); ?>

        <div id="cookiebar" data-position="<?php echo $settings['position']; ?>" data-locale="<?php echo get_locale(); ?>" data-hash="<?php echo sid_get_cookie_hash(); ?>">
            <div class="inner"><?php echo $message; ?></div>
        </div>
	<?php }
}