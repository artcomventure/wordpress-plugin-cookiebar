(function ( $ ) {

    /**
     * T9n tabs.
     */

    $( 'div.nav-tab-wrapper' ).each( function () {
        const $this = $( this ),
            $tabs = $( 'a', $this ).on( 'click', function ( e ) {
                e.preventDefault();

                // deselect
                $tabs.removeClass( 'nav-tab-active' ).each( function () {
                    // hide all sections
                    $( $( this ).attr( 'href' ) ).hide();
                } );

                // activate clicked section
                $( $( this ).blur().addClass( 'nav-tab-active' ).attr( 'href' ) ).show();
            } );

        // activate first tab
        $this.find( 'a' ).first().trigger( 'click' );
    } );

    /**
     * Change editor styles.
     */

    const $messages = document.getElementsByClassName( 'wp-editor-area' );
    function setEditorStyle( css, selector ) {
        if ( typeof tinymce === 'undefined' ) return;
        if ( typeof selector === 'undefined' ) selector = 'body';

        // need <style>
        if ( selector !== 'body' ) {
            let _css = [];

            for ( let attribute in css ) {
                if ( !css.hasOwnProperty( attribute ) ) continue;
                _css.push( attribute + ': ' + css[attribute] );
            }

            css = '<style>' + selector + '{ ' + _css.join( '; ' ) + ' }</style>';
        }

        Array.prototype.forEach.call( $messages, function( $message ) {
            const editor = tinymce.get( $message.id ),
                $body = editor.getBody();

            if ( selector === 'body' ) editor.dom.setStyle( $body, css );
            else $($body).parent().find( 'head' ).append( css );
        } );
    }

    /**
     * Font size range slider.
     */

    const $fontSizeInput = $( 'input[name="sid[fontsize]"]' );
    let $handle;

    $( '#custom_fontsize' ).on( 'change', function() {
        $(this).parent()[this.checked ? 'addClass' : 'removeClass']( 'checked' );

        if ( this.checked ) setEditorStyle( { fontSize: $fontSizeInput.val()||$fontSizeInput.attr( 'placeholder' ) } );
        else setEditorStyle( { fontSize: '' } );
    } ).trigger( 'change' );

    $( '#font-size-slider' ).slider( {
        min: 8,
        max: 36,
        value: parseInt($fontSizeInput.val()||$fontSizeInput.attr( 'placeholder' ) ),
        create: function( event, ui ) {
            $handle = $(this).children().first().attr( 'data-value', $fontSizeInput.val()||$fontSizeInput.attr( 'placeholder' ) );
        },
        slide: function( event, ui, value ) {
            $fontSizeInput.val( value = ui.value + 'px' );
            $handle.attr( 'data-value', value );
            setEditorStyle( { fontSize: value } )
        }
    } );

    /**
     * Color picker UI.
     */

    $( 'input.color-picker' ).each( function() {
        $(this).wpColorPicker( {
            palettes: [],
            change: function( e, data ) {
                setTimeout( function() {
                    switch ( this.name ) {
                        case 'sid[color][box]':
                            setEditorStyle( { backgroundColor: this.value } );
                            break;

                        case 'sid[color][text]':
                            setEditorStyle( { color: this.value } );
                            break;

                        case 'sid[color][link]':
                            setEditorStyle( { color: this.value }, 'a' );
                            break;
                    }
                }.bind( this ), 10 );
            }
        } )
        // change label
        .closest( 'div.wp-picker-container' ).find( 'span.wp-color-result-text' ).text( $(this).data( 'label' ) );
    } );

    /**
     * Connect confirmation tag inputs.
     *
     * This field is in t9n section although it's an unique value.
     * Therefore: when one is changed all others are changed (for UX purpose).
     */

    const $buttonColorUI = $( 'input[name="sid[color][button]"]' ).closest( 'div.wp-picker-container' ),
        $tagInputs = $( 'select[name="sid[rejection][tag]"], select[name="sid[confirmation][tag]"]' ).on( 'change', function() {
            const name = this.name;
            const value = this.value;

            $tagInputs.filter( function() {
                return this.name === name;
            } ).each( function() { $(this).val( value ); } );

            // toggle button color UI
            // not needed for button `tag`.
            $buttonColorUI[$tagInputs.filter( function() { return this.value === 'button' } ).length  ? 'show' : 'hide']()
        } );

        // to toggle button color UI on page load
        $tagInputs.first().trigger( 'change' );

    /**
     * Set expiration.
     */

    (function() {

        const $expires = $( 'input[name="sid[expires]"]' );
        if ( !$expires.length ) return;

        const $display = $expires.find( '+ p.description span:first-of-type' );
        if ( !$display.length ) return;

        let request;

        function setDescription() {
            if ( request ) request = request.abort();

            request = $.get( ajaxurl, {
                action: 'sid-expiration',
                expires: $expires.val(),
                format: 'd.m.Y H:i:s'
            }, function ( response ) {
                $display.removeClass( 'error' );

                if ( response === false ) {
                    response = wp.i18n.__( 'ERROR. Please check format.', 'sid' );
                    $display.addClass( 'error' );
                }
                else if ( !response ) response = wp.i18n.__( 'until the end of the session', 'sid' );

                $display.html( response )
            } );
        }

        $expires.on( 'input', setDescription );

    })();

})( jQuery );