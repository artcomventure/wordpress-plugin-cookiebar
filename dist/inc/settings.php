<?php

/**
 * Register 'sid' settings option.
 */
add_action( 'admin_init', 'sid_register_setting' );
function sid_register_setting() {
	register_setting( 'sid', 'sid' );
}

/**
 * Add and render settings page.
 */
add_action( 'admin_menu', 'sid_settings_page' );
function sid_settings_page() {
	add_options_page(
		__( 'Cookiebar', 'sid' ),
		__( 'Cookiebar', 'sid' ),
		'manage_options',
		'sid-settings',
		function() {
		    // enqueue styles and scripts for admin page
			$plugin_data = get_plugin_data( SID_PLUGIN_DIR . 'sid.php' );
			wp_enqueue_script( 'sid-settings', SID_PLUGIN_URL . 'js/settings.min.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-slider', 'wp-i18n' ), $plugin_data['Version'] );
			wp_set_script_translations( 'sid-settings', 'sid', SID_PLUGIN_DIR . '/languages' );
			wp_register_style( 'jquery-ui', SID_PLUGIN_URL . 'css/jquery-ui/base/jquery-ui.css', array(), '1.12.1' );
			wp_enqueue_style( 'sid-settings', SID_PLUGIN_URL . 'css/settings.css', array( 'wp-color-picker', 'jquery-ui' ), $plugin_data['Version'] ); ?>

		    <div class="wrap">
                <h2><?php printf( __( '%s Settings', 'sid' ), __( 'Cookiebar', 'sid' ) ); ?></h2>

                <form id="cookiebar-settings-form" method="post" action="options.php">
                    <?php wp_nonce_field( 'sid_nonce', '_sid_nonce' );
                    settings_fields( 'sid' );

                    $settings = sid_settings(); ?>

                    <table class="form-table">
                        <tbody>

                        <tr valign="top">
                            <th scope="row">
				                <?php _e( 'Message', 'sid' ); ?>
                            </th>
                            <td>
                                <?php $available_languages = sid_available_languages(); ?>
                                <div class="nav-tab-wrapper hide-if-no-js"<?php echo count($available_languages) == 1 ? ' style="display: none;"' : ''; ?>>
                                    <?php foreach( $available_languages as $locale => $name ): ?>
                                        <a href="#message-<?php echo $locale ?>-wrap" class="nav-tab">
                                            <?php echo $name; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>

                                <?php foreach( $available_languages as $locale => $name ) : ?>
                                    <div id="message-<?php echo $locale ?>-wrap">
                                        <?php wp_editor( $settings['message'][$locale], "sid-message-{$locale}", array(
                                            'textarea_name' => 'sid[message][' . $locale . ']',
                                            'media_buttons' => apply_filters( 'sid_editor_media_button', false ),
                                            'tinymce' => array(
                                                'autoresize_min_height' => 100,
                                                'wp_autoresize_on' => true,
                                                'content_css' => ''
                                            ),
                                            'quicktags' => false
                                        ) ); ?>

                                        <p class="description">
                                            <?php _e( 'Leave blank to not show any message.', 'sid' ); ?>
                                            <?php _e( 'As soon as the message is changed the cookiebar will appear again.', 'sid' ); ?>
                                            <br />&nbsp;
                                        </p>

                                        <div style="margin-bottom: .5em;">
                                            <label style="vertical-align: baseline"><b><?php _e( 'Rejection link text', 'sid' ); ?></b>:</label>
		                                    <?php unload_textdomain( 'sid' );
		                                    load_textdomain( 'sid', SID_PLUGIN_DIR . "languages/sid-{$locale}.mo" ); ?>
                                            <input type="text" name="sid[rejection][<?php echo $locale; ?>]" value="<?php echo $settings['rejection'][$locale]; ?>" placeholder="<?php _e( 'Essential only', 'sid' ); ?>" />
		                                    <?php unload_textdomain( 'sid' );
		                                    sid_t9n();

		                                    $rejection_tag = array_map( function( $tag, $label ) use ( $settings ) {
			                                    return '<option' . selected( $tag, $settings['rejection']['tag'], false ) . ' value="' . $tag . '">' . $label . '</option>';
		                                    }, array( 'a', 'button' ), array( __( 'Link' ), __( 'Button' ) ) );
		                                    $rejection_tag = '<select name="sid[rejection][tag]">' . implode( '', $rejection_tag ) . '</select>';
		                                    printf( __( 'show as %s', 'sid' ), $rejection_tag ); ?>
                                        </div>

                                        <div>
                                            <label style="vertical-align: baseline"><b><?php _e( 'Confirmation link text', 'sid' ); ?></b>:</label>
                                            <?php unload_textdomain( 'sid' );
                                            load_textdomain( 'sid', SID_PLUGIN_DIR . "languages/sid-{$locale}.mo" ); ?>
                                            <input type="text" name="sid[confirmation][<?php echo $locale; ?>]" value="<?php echo $settings['confirmation'][$locale]; ?>" placeholder="<?php _e( 'Accept', 'sid' ); ?>" />
                                            <?php unload_textdomain( 'sid' );
                                            sid_t9n();

                                            $confirmation_tag = array_map( function( $tag, $label ) use ( $settings ) {
                                                return '<option' . selected( $tag, $settings['confirmation']['tag'], false ) . ' value="' . $tag . '">' . $label . '</option>';
                                            }, array( 'a', 'button' ), array( __( 'Link' ), __( 'Button' ) ) );
                                            $confirmation_tag = '<select name="sid[confirmation][tag]">' . implode( '', $confirmation_tag ) . '</select>';
                                            printf( __( 'show as %s', 'sid' ), $confirmation_tag ); ?>
                                        </div>

                                        <p class="description"><?php _e( 'The confirmation/rejection links will be added automatically at the end of the text.', 'sid' ); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
		                        <?php _e( 'Scripts', 'sid' ); ?>
                            </th>
                            <td>
                                <div class="nav-tab-wrapper hide-if-no-js">
			                        <?php foreach( ['script', 'noscript'] as $script ) : ?>
                                        <a href="#<?php echo strtolower( $script ) ?>-wrap" class="nav-tab">
					                        <?php echo $script; ?>
                                        </a>
			                        <?php endforeach; ?>
                                </div>

		                        <?php foreach( ['script', 'noscript'] as $script ) : ?>
                                    <div id="<?php echo $script ?>-wrap">
                                        <textarea name="sid[<?php echo $script ?>]" rows="10" cols="50" class="large-text code"><?php
                                            echo $settings[$script]
                                        ?></textarea>

                                        <p class="description">
					                        <?php _e( 'Enter scripts that should be executed once the cookiebar is accepted.', 'sid' ); ?>
                                        </p>
                                    </div>
		                        <?php endforeach; ?>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
		                        <?php _e( 'Font Sizes' ); ?>:
                            </th>
                            <td style="display: flex; align-items: center;">
                                <label for="custom_fontsize">
                                    <?php _e( 'Custom font size', 'sid' ) ?>
                                    <input id="custom_fontsize" name="sid[custom_fontsize]" type="checkbox"<?php checked( 1, $settings['custom_fontsize'] ) ?> value="1" />
                                </label>
                                <div id="font-size-slider"></div>
                                <input type="hidden" name="sid[fontsize]" value="<?php echo $settings['fontsize']; ?>" placeholder="14px" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
		                        <?php _e( 'Colors' ); ?>:
                            </th>
                            <td>
                                <input type="text" name="sid[color][background]" class="color-picker" value="<?php echo $settings['color']['background']; ?>" data-label="<?php _e( 'Background' ); ?>" />
                                <input type="text" name="sid[color][box]" class="color-picker" value="<?php echo $settings['color']['box']; ?>" data-label="<?php _e( 'Box' ); ?>" />
                                <input type="text" name="sid[color][text]" class="color-picker" value="<?php echo $settings['color']['text']; ?>" data-label="<?php _e( 'Text' ); ?>" />
                                <input type="text" name="sid[color][link]" class="color-picker" value="<?php echo $settings['color']['link']; ?>" data-label="<?php _e( 'Links' ); ?>" />
                                <input type="text" name="sid[color][button]" class="color-picker" value="<?php echo $settings['color']['button']; ?>" data-label="<?php _e( 'Button' ); ?>" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
		                        <?php _e( 'Position', 'sid' ); ?>:
                            </th>
                            <td>
		                        <?php $select = '<select name="sid[position]">';
		                        foreach ( array( 'Top', 'Middle', 'Bottom' ) as $position ) {
			                        $select .= '<option value="' . strtolower( $position ) . '"' . selected( $settings['position'], strtolower( $position ), false ) . '>' . __( $position, 'sid' ) . '</option>';
		                        }
		                        $select .= '</select>';

		                        printf( __( '%s of page', 'sid' ), $select ); ?>

                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
		                        <?php _e( 'Validity', 'sid' ); ?>:
                            </th>
                            <td>
                                <input type="text" class="regular-textfield"
                                       name="sid[expires]" value="<?php echo $settings['expires'] ?>" placeholder="<?php _e( 'E.g.', 'sid' ) ?>: 1y 2M 3w 4d 5h 6m 7s" />

                                <p class="description">
                                    <?php printf(
                                        __( "Users' selection will be invalid in (all optional): 1y (%s) 2M (%s) 3w (%s) 4d (%s) 5h (%s) 6m (%s) 7s (%s)", 'sid' ),
                                        __( 'years', 'sid' ),
                                        __( 'months', 'sid' ),
                                        __( 'weeks', 'sid' ),
                                        __( 'days', 'sid' ),
                                        __( 'hours', 'sid' ),
                                        __( 'minutes', 'sid' ),
                                        __( 'seconds', 'sid' )
                                    ) ?>
                                    <br /><?php printf( _x( 'Current expiration: %s', 'cookie selection expiration', 'sid' ), '<span>' . (sid_expiration() ?: __( 'until the end of the session', 'sid' )) . '</span>' ) ?>
                                    <span><?php _e( '(varies from actual time the user make his selection)', 'sid' ) ?></span>
                                </p>
                            </td>
                        </tr>

                        </tbody>
                    </table>

	                <?php submit_button(); ?>

                </form>
            </div>
        <?php }
	);
}

add_filter( 'pre_update_option_sid', function( $value, $old_value, $option ) {
	return $value;
}, 10, 3 );

/**
 * Define editor's first row mce buttons.
 */
add_filter( 'mce_buttons', 'sid_editor_buttons', 100, 2 );
function sid_editor_buttons( $buttons, $editor_id ) {
    if ( in_array( $editor_id, array_map( function( $locale ) {
        return "sid-message-{$locale}";
    }, array_keys( sid_available_languages() ) ) ) )
        return apply_filters( 'sid_editor_buttons', array( 'alignleft', 'alignright', 'bold', 'italic', 'link', 'unlink', 'removeformat', 'undo', 'redo' ) );

    return $buttons;
}

/**
 * Editor config.
 */
add_filter( 'tiny_mce_before_init', 'sid_editor_config', 10, 2 );
function sid_editor_config( $init, $editor_id ) {
	if ( in_array( $editor_id, array_map( function( $locale ) {
		return "sid-message-{$locale}";
	}, array_keys( sid_available_languages() ) ) ) ) {
		$settings = sid_settings();

		$init['content_style'] = '';
		foreach( array(
		   'body' => array(
			   'font-family' => 'sans-serif',
		       'font-weight' => '400',
		       'background-color' => $settings['color']['box'],
               'font-size' => $settings['custom_fontsize'] ? $settings['fontsize'] : '',
               'color' => $settings['color']['text'],
               'line-height' => 1.6,
               'text-align' => 'center',
               'max-width' => $GLOBALS['content_width'] . 'px',
               'margin' => '0 auto',
			   'padding' => '1em 2em 0',
               'box-sizing' => 'border-box',
            ),
            'a' => array(
                'color' => $settings['color']['link']
            )
        ) as $selector => $styles ) {
		    if ( !$styles = array_filter( $styles, function( $value ) {
		        return $value !== '';
            } ) ) continue;

			$init['content_style'] .= "{$selector} { ";
			$styles = array_map( function( $attribute, $value ) {
			    return "{$attribute}: {$value}";
            }, array_keys( $styles ), $styles );
			$init['content_style'] .= implode( '; ', $styles );
			$init['content_style'] .= " } ";
        }
    }

    return apply_filters( 'sid_editor_config', $init );
}
