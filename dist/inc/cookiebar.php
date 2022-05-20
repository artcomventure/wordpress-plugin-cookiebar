<?php

/**
 * Set cookie by request (not javascript).
 */
add_action( 'template_redirect', 'set_sid_cookie');
function set_sid_cookie() {
	if ( isset($_GET[SID_COOKIE_NAME]) ) {
		setcookie( SID_COOKIE_NAME, urlencode( json_encode( $_GET[SID_COOKIE_NAME] + (sid_get_cookie() ?: array()) ) ), sid_expiration( 'seconds' ), '/' );

		global $wp;
		// reload current page
		wp_redirect( home_url( add_query_arg( $wp->query_vars, $wp->request ) ) );
		exit;
	}
}

/**
 * @param string $format
 *
 * @return false|int|string
 */
add_action( 'wp_ajax_sid-expiration', 'sid_expiration' );
function sid_expiration( $format = '' ) {
	$expires = trim( $_GET['expires'] ?? sid_settings( 'expires' ) );

	$time_replaces = array(
	    '/(\d+) *y( |$)/' => "$1 years",
        '/(\d+) *M( |$)/' => "$1 months",
        '/(\d+) *w( |$)/' => "$1 weeks",
        '/(\d+) *d( |$)/' => "$1 days",
        '/(\d+) *h( |$)/' => "$1 hours",
        '/(\d+) *m( |$)/' => "$1 minutes",
        '/(\d+) *s( |$)/' => "$1 seconds"
    );

	$now = current_time( 'timestamp' );
	$expires = preg_replace( array_keys( $time_replaces ), array_values( $time_replaces ), $expires );

	if ( $expires && $expires = strtotime( $expires, $now ) )
	    switch ( $format = $_GET['format'] ?? $format ?: get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) {
            case 'seconds':
                $expires = $expires - $now + (int) get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
                break;

            default:
                $expires = date_i18n( $format, $expires );
                break;
        }

	if ( wp_doing_ajax() ) wp_send_json( $expires );
    else return $expires;
}

/**
 * Enqueue scripts and styles.
 */
add_action( 'wp_enqueue_scripts', 'sid_scripts' );
function sid_scripts() {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	$plugin_data = get_plugin_data( SID_PLUGIN_DIR . 'sid.php' );

	$settings = sid_settings();
	// convert to seconds
	$settings['expires'] = sid_expiration( 'seconds' );

	wp_enqueue_script( 'cookiebar', SID_PLUGIN_URL . 'js/cookiebar.min.js', array(), $plugin_data['Version'] );
	wp_localize_script( 'cookiebar', 'Sid', array(
		'status' => sid_status(),
		'accepted' => sid_is_accepted(),
        'declined' => sid_is_declined(),
        'locale' => get_locale(),
        'hash' => sid_get_cookie_hash(),
        'settings' => $settings
    ) );

	// no need for CSS
    // cookiebar is already accepted/rejected
	if ( sid_status() !== '' ) return;

	wp_enqueue_style( 'cookiebar', SID_PLUGIN_URL . 'css/cookiebar.css', array(), $plugin_data['Version'] );

	$CSS = '';

	if ( !empty($GLOBALS['content_width']) ) {
		$CSS .= "#cookiebar .inner {
            max-width: " . $GLOBALS['content_width'] . "px;
        }";
    }

	if ( $settings['color']['background'] ) $CSS .= "#cookiebar:before {
        background-color: " . $settings['color']['background'] . ";
    }";

	// cookiebar colors
    if ( $settings['color']['text'] || $settings['custom_fontsize'] ) $CSS .= "#cookiebar .inner {
        " . ($settings['color']['text'] ? "color: " . $settings['color']['text'] : '') . ";
        " . ($settings['custom_fontsize'] ? "font-size: " . $settings['fontsize'] : '') . ";
    }";

	// overlay color
	$CSS .= "#cookiebar, #cookiebar .inner {
	    background-color: " . $settings['color']['box'] . ";
	}";

	// cookiebar link color
	if ( $settings['color']['link'] ) $CSS .= "#cookiebar a {
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

	    $CSS .= "#cookiebar button {
            background-color: " . $button_color . ";
            color: " . $button_text_color . ";
        }";
    }

	// minify css
	$CSS = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $CSS ); // negative look ahead
	$CSS = preg_replace('/\s{2,}/', ' ', $CSS );
	$CSS = preg_replace('/\s*([:;{}])\s*/', '$1', $CSS );
	$CSS = preg_replace('/;}/', '}', $CSS );

	if ( $CSS ) wp_add_inline_style( 'cookiebar', $CSS );
}

/**
 * Add scripts to header.
 */
add_action( 'wp_head', 'sid_script', 9 );
function sid_script() {
	if ( !$script = sid_settings( 'script' ) ) return; ?>

    <!-- Sid script -->
    <script>
        (function() {
            function sidLoadScript() {
		        <?php echo $script; ?>

            }

	        <?php echo ( sid_do_scripts() )
	        ? 'sidLoadScript();'
            : "document.addEventListener( 'DOMContentLoaded', function() {
                document.body.addEventListener( 'sid-accepted', sidLoadScript, false );
            }, false );" ?>
        })();
    </script>
    <!-- End Sid script -->
<?php }

/**
 * Add noscripts to top of body.
 */
add_action( 'wp_body_open', 'sid_noscript', -1 );
function sid_noscript() {
    if ( sid_do_scripts() && !$noscript = sid_settings( 'noscript' ) ) return; ?>

    <!-- Sid noscript -->
    <noscript><?php echo $noscript; ?></noscript>
    <!-- End Sid noscript -->
<?php }

/**
 * Retrieve cookiebar message.
 *
 * @return string
 */
function sid_get_message() {
	$settings = sid_settings();

	if ( $message = $settings['message'][get_locale()] ) {
		global $wp;

		// rejection

		if ( ! $rejection = $settings['rejection'][ get_locale() ] ) {
			$rejection = __( 'Essential only', 'sid' );
		}

		$deeplink = home_url( add_query_arg( array( 'sid[' . get_locale() . ']' => '0' . sid_get_cookie_hash() ) + $wp->query_vars, $wp->request ) );
		$deeplink  = $settings['rejection']['tag'] != 'button' ? 'href="' . $deeplink . '"' : 'onclick="window.location.href=\'' . $deeplink . '\'"';
		$rejection = '<' . $settings['rejection']['tag'] . ' class="accept-essential-cookies" ' . $deeplink . '><span>' . $rejection . '</span></' . $settings['rejection']['tag'] . '>';

		// confirmation

		if ( ! $confirmation = $settings['confirmation'][ get_locale() ] ) {
			$confirmation = __( 'Accept', 'sid' );
		}

		$deeplink = home_url( add_query_arg( array( 'sid[' . get_locale() . ']' => '1' . sid_get_cookie_hash() ) + $wp->query_vars, $wp->request ) );
		$deeplink = $settings['confirmation']['tag'] != 'button' ? 'href="' . $deeplink . '"' : 'onclick="window.location.href=\'' . $deeplink . '\'"';
		$confirmation = '<' . $settings['confirmation']['tag'] . ' class="accept-all-cookies" ' . $deeplink . '><span>' . $confirmation . '</span></' . $settings['confirmation']['tag'] . '>';

		if ( preg_match( '/<p[^>]*>\S*<\/p>$/', $message ) ) {
			$message = preg_replace( '/<p[^>](.*)>\S*<\/p>$/',
                "<p $1 class='wp-block-buttons'>${rejection} ${confirmation}</p>",
                $message );
		} else {
			$message = wpautop( $message . ' ' . $rejection . ' ' . $confirmation );
		}
	}

	return $message;
}

/**
 * Add cookiebar markup to template.
 */
add_action( 'wp_footer', 'sid_render_cookiebar' );
function sid_render_cookiebar() {
    if ( sid_status() !== '' || !$message = sid_get_message() ) return; ?>

    <div id="cookiebar" class="cookiebar" data-position="<?php echo sid_settings( 'position' ); ?>">
        <div class="inner"><?php echo $message; ?></div>
    </div>
<?php }