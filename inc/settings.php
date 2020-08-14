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
			wp_enqueue_script( 'sid-settings', SID_PLUGIN_URL . 'js/settings.min.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-slider' ), $plugin_data['Version'] );
			wp_register_style( 'jquery-ui', SID_PLUGIN_URL . 'css/jquery-ui/base/jquery-ui.css', array(), '1.12.1' );
			wp_enqueue_style( 'sid-settings', SID_PLUGIN_URL . 'css/settings.min.css', array( 'wp-color-picker', 'jquery-ui' ), $plugin_data['Version'] ); ?>

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
                                <div class="nav-tab-wrapper hide-if-no-js">
                                    <?php foreach( $available_languages = sid_available_languages() as $locale => $name ): ?>
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

                                        <label style="vertical-align: baseline"><b><?php _e( 'Confirmation link text', 'sid' ); ?></b>:</label>
                                        <?php unload_textdomain( 'sid' );
                                        load_textdomain( 'sid', SID_PLUGIN_DIR . "languages/sid-{$locale}.mo" ); ?>
                                        <input type="text" name="sid[confirmation][<?php echo $locale; ?>]" value="<?php echo $settings['confirmation'][$locale]; ?>" placeholder="<?php _e( 'Accept', 'sid' ); ?>" />
                                        <?php sid_t9n();

                                        $confirmation_type = array_map( function( $type ) use ( $settings ) {
                                            return '<option' . selected(strtolower( $type ), $settings['confirmation']['type'], false ) . ' value="' . strtolower( $type ) . '">' . __( $type ) . '</option>';
                                        }, array( 'Link' , 'Button' ) );
                                        $confirmation_type = '<select name="sid[confirmation][type]">' . implode( '', $confirmation_type ) . '</select>';
                                        printf( __( 'show as %s', 'sid' ), $confirmation_type ); ?>

                                        <p class="description"><?php _e( 'The confirmation link will be added automatically at the end of the text.', 'sid' ); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
		                        <?php _e( 'Font Sizes' ); ?>:
                            </th>
                            <td>
                                <div id="font-size-slider"></div>
                                <input type="hidden" name="sid[fontsize]" value="<?php echo $settings['fontsize']; ?>" placeholder="14px" />
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">
		                        <?php _e( 'Colors' ); ?>:
                            </th>
                            <td>
                                <input type="text" name="sid[bcolor]" class="color-picker" value="<?php echo $settings['bcolor']; ?>" data-label="<?php _e( 'Background' ); ?>" />
                                <input type="text" name="sid[tcolor]" class="color-picker" value="<?php echo $settings['tcolor']; ?>" data-label="<?php _e( 'Text' ); ?>" />
                                <input type="text" name="sid[lcolor]" class="color-picker" value="<?php echo $settings['lcolor']; ?>" data-label="<?php _e( 'Links' ); ?>" />
                                <input type="text" name="sid[bbcolor]" class="color-picker" value="<?php echo $settings['bbcolor']; ?>" data-label="<?php _e( 'Button' ); ?>" />
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
		                        <?php _e( 'Indirect confirmation', 'sid' ); ?>:
                            </th>
                            <td>
                                <input type="checkbox" class="regular-checkbox"
                                       name="sid[indirect]" value="1"<?php checked( $settings['indirect'] ); ?> />

                                <p class="description">
			                        <?php _e( 'User confirms by further use (on every link click) of the website.', 'sid' ); ?>
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

/**
 * Define editor's first row mce buttons.
 */
add_filter( 'mce_buttons', 'sid_editor_buttons', 100, 2 );
function sid_editor_buttons( $buttons, $editor_id ) {
    if ( in_array( $editor_id, array_map( function( $locale ) {
        return "sid-message-{$locale}";
    }, array_keys( sid_available_languages() ) ) ) )
        return apply_filters( 'sid_editor_buttons', array( 'bold', 'italic', 'link', 'unlink', 'removeformat', 'undo', 'redo' ) );

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
		       'background-color' => $settings['bcolor'],
               'font-size' => $settings['fontsize'],
               'color' => $settings['tcolor'],
               'line-height' => 1.6,
               'text-align' => 'center',
               'max-width' => $GLOBALS['content_width'] . 'px',
               'margin' => '0 auto',
			   'padding' => '1em 2em 0',
               'box-sizing' => 'border-box',
            ),
            'a' => array(
                'color' => $settings['lcolor']
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
