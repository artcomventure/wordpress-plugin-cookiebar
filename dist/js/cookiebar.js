document.addEventListener( 'DOMContentLoaded', function() {

    [].forEach.call( document.getElementsByClassName( 'cookiebar' ), function( $cookiebar ) {
        // is main cookiebar
        if ( $cookiebar.id === 'cookiebar' ) {
            document.body.classList.add( 'cookie-bar' );

            const position = $cookiebar.getAttribute( 'data-position' );

            // set gap on ´position´ of page
            (function gap() {
                if ( !$cookiebar || position === 'middle' ) return;
                window.requestAnimationFrame( gap );
                if ( $cookiebar.offsetHeight === parseInt( document.body.style['padding-' + position] ) ) return;
                document.body.style['padding-' + position] = $cookiebar.offsetHeight + 'px';
            })();

            $cookiebar.addEventListener( 'click', function( e ) {
                e.stopPropagation();

                this.classList.add( 'shake' );
                setTimeout( function() {
                    $cookiebar.classList.remove( 'shake' );
                }, 600 );
            }, false );

            $cookiebar.firstElementChild.addEventListener( 'click', function( e ) {
                e.stopPropagation();
            }, false );
        }
    } );

    // accept cookie
    [].forEach.call( document.querySelectorAll( '.accept-essential-cookies, .accept-all-cookies' ), function( $action ) {
        if ( $action.tagName === 'BUTTON' ) $action.removeAttribute( 'onclick' );

        $action.addEventListener( 'click', function( e ) {
            e.preventDefault();

            setCookie( this.classList.contains( 'accept-all-cookies' ) );

            // remove cookiebar
            const $cookiebar = document.getElementById( 'cookiebar' );
            if ( $cookiebar ) {
                const position = $cookiebar.getAttribute( 'data-position' );
                if ( position !== 'middle' ) document.body.style['padding-' + position] = '';
                $cookiebar.parentNode.removeChild( $cookiebar );
            }
        }, false );
    } );

    // set cookie on accept action
    function setCookie( accept ) {
        Sid.accepted = accept ? 1 : 0;
        Sid.declined = accept ? 0 : 1;
        Sid.status = accept ? 1 : 2;

        let cookie = (function() {
            let value = '';
            document.cookie.split( ';' ).forEach( function ( cookie ) {
                if ( !value && cookie.trim().indexOf( 'sid=' ) == 0 ) {
                    value = decodeURIComponent( cookie.replace( new RegExp( '^\\s*sid=' ), '' ) );
                }
            } );

            return JSON.parse( value||"{}" );
        })();

        cookie[Sid.locale] = Sid.accepted + Sid.hash;
        cookie = ['sid=' + encodeURIComponent( JSON.stringify( cookie ) ), 'path=/'];
        if ( Sid.settings.expires ) {
            let expires = new Date();
            expires.setTime( expires.getTime() + Sid.settings.expires * 1000);
            cookie.push( 'expires=' + expires.toGMTString() )
        }
        document.cookie = cookie.join( ';' );

        document.body.classList.remove( 'cookie-bar' );

        // let 'others' know _cookies_ are accepted/declined
        document.body.dispatchEvent( new Event( (Sid.accepted === 1 ? 'sid-accepted' : 'sid-declined'), { bubbles: true } ) );
        // backwards compatibility prior 1.3.0
        if ( Sid.accepted ) document.body.dispatchEvent( new Event( 'sid_accepted' , { bubbles: true } ) );
    }

}, false );
