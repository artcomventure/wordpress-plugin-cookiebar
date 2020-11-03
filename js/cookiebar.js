document.addEventListener( 'DOMContentLoaded', function() {

    var $cookiebar = document.getElementById( 'cookiebar' );
    if ( !$cookiebar ) return;

    var position = $cookiebar.getAttribute( 'data-position' );

    // set gap on ´position´ of page
    (function gap() {
        if ( !$cookiebar || position === 'middle' ) return;
        window.requestAnimationFrame( gap );
        if ( $cookiebar.offsetHeight === parseInt( document.body.style['padding-' + position] ) ) return;
        document.body.style['padding-' + position] = $cookiebar.offsetHeight + 'px';
    })();

    // accept cookie
    $cookiebar.querySelector( '.accept-cookies' ).addEventListener( 'click', function( e ) {
        e.preventDefault();

        setCookie();

        // remove cookiebar
        $cookiebar.parentNode.removeChild( $cookiebar );
        if ( position !== 'middle' ) document.body.style['padding-' + position] = '';
    }, false ); 

    $cookiebar.addEventListener( 'click', function( e ) {
        e.stopPropagation();
    }, false );

    // set cookie on accept action
    function setCookie() {
        var cookie = (function() {
            var value = '';
            document.cookie.split( ';' ).forEach( function ( cookie ) {
                if ( !value && cookie.trim().indexOf( 'sid=' ) == 0 ) {
                    value = decodeURIComponent( cookie.replace( new RegExp( '^\\s*sid=' ), '' ) );
                }
            } );

            return JSON.parse( value||"{}" );
        })();

        cookie[$cookiebar.getAttribute( 'data-locale' )] = $cookiebar.getAttribute( 'data-hash' );
        cookie = ['sid=' + encodeURIComponent( JSON.stringify( cookie ) ), 'path=/'];
        document.cookie = cookie.join( ';' );

        Sid.accepted = "1";

        // let 'others' know cookies are accepted
        document.body.dispatchEvent( new Event( 'sid_accepted', { bubbles: true } ) );
    }

    // set cookie on EVERY link click
    if ( parseInt( Sid.settings.indirect ) ) Array.prototype.forEach.call( document.querySelectorAll( 'a, form button, form input[type="submit"]' ), function( $link ) {
        $link.addEventListener( 'click', setCookie, false);
    });

}, false );
