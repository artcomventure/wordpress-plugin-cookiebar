(function ( $ ) {

    /**
     * T9n tabs.
     */

    $( 'div.nav-tab-wrapper' ).each( function () {
        var $this = $( this ),
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

    var $messages = document.getElementsByClassName( 'wp-editor-area' );
    function setEditorStyle( css, selector ) {
        if ( typeof selector === 'undefined' ) selector = 'body';

        // need <style>
        if ( selector !== 'body' ) {
            var _css = [];

            for ( var attribute in css ) {
                if ( !css.hasOwnProperty( attribute ) ) continue;
                _css.push( attribute + ': ' + css[attribute] );
            }

            css = '<style type="text/css">' + selector + '{ ' + _css.join( '; ' ) + ' }</style>';
        }

        Array.prototype.forEach.call( $messages, function( $message ) {
            var editor = tinymce.get( $message.id ),
                $body = editor.getBody();

            if ( selector === 'body' ) editor.dom.setStyle( $body, css );
            else $($body).parent().find( 'head' ).append( css );
        } );
    }

    /**
     * Font size range slider.
     */

    var $fontSizeInput = $( 'input[name="sid[fontsize]"]' ),
        $handle;

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
                        case 'sid[bcolor]':
                            setEditorStyle( { backgroundColor: this.value } );
                            break;

                        case 'sid[tcolor]':
                            setEditorStyle( { color: this.value } );
                            break;

                        case 'sid[lcolor]':
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
     * Connect confirmation type inputs.
     *
     * This field is in t9n section although it's an unique value.
     * Therefore: when one is changed all others are changed (for UX purpose).
     */

    var $bbColorUI = $( 'input[name="sid[bbcolor]"]' ).closest( 'div.wp-picker-container' ),
        $typeInputs = $( 'select[name="sid[confirmation][type]"]' ).on( 'change', function() {
            $typeInputs.val( this.value );

            // toggle button color UI
            // not needed for `type` 'link'.
            $bbColorUI[this.value === 'button' ? 'show' : 'hide']()
        } );

    // to toggle button color UI on page load
    $typeInputs.first().trigger( 'change' );

})( jQuery );