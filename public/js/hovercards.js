var currentHoverElement = null;
var isWaiting = false;
var linkElement = null;
var closeCard = false;
var hovercards = {};
var ignoredElements = null;

$( document ).ready( function() {
    $( document ).mousemove( function( e ) {
        currentHoverElement = e.target.id;
        watchMouse();
    });
});

function delay( time ) {
    return new Promise( resolve => setTimeout( resolve, time ) );
}

function stillOnLink( anchorElement ) {
    if ( currentHoverElement == anchorElement ) {
        return true;
    }

    return false;
}

function waitingForChange( callback ) {
    setTimeout( function() {
        if ( currentHoverElement == linkElement || currentHoverElement == 'hovercard' ) {
            callback( false );
        } else {
            var found = false;

            if ( ignoredElements != null ) {
                for ( var i = 0; i < ignoredElements.length; i++ ) {
                    if ( currentHoverElement == ignoredElements[i] ) {
                        found = true;
                        break;
                    }
                }
            } 

            if ( found ) {
                callback( false );
            } else {
                callback( true );
            }
        }
    }, 2000);
}

function watchMouse() {
    waitingForChange( function( close ) {
        if ( close ) {
            closeHovercard();
        }
    });
}

function closeHovercard() {
    linkElement = null;
    isWaiting = false;
    ignoredElements = null;
    $( "#hovercard" ).fadeOut();
    $( "#hovercard" ).html( '' );
}

function openHovercard( e ) {
    linkElement = $( e ).data( 'link' );
    isWaiting = true;
    var hash = $( e ).data( 'hash' );
    var memberId = $( e ).data( 'memberid' );
    var card = $( "#hovercard" );
    var anchorElement = $( "#" + $( e ).data( 'link' ) );
    var difference = ( $( window ).width() - anchorElement.offset().left );
    var ignored = $( e ).data( 'ignored' );
    ignoredElements = ignored.split( ',' );

    card.css( { "height" : "260px" } );

    if ( hovercards[memberId] === undefined ) {
        $.ajax({
            url: json.wrapper,
            type: 'get',
            data: {
                'controller':'ajax',
                'action':'hovercard',
                'id':memberId,
                'hash':hash
            },
            dataType: 'json',
            success: function( response ) {
                if ( response.status ) {
                    card.css( { "height" : "fit-content" } );
                    hovercards[memberId] = response.data;
                    card.html( response.data );
                } else {
                    card.html( response.data );
                }
            }
        });
    } else {
        card.html( hovercards[memberId] );
    }

    if ( card.width() >= difference ) {
        card.css( { "left":( anchorElement.offset().left - card.width() + anchorElement.width() ) + "px" } );
    } else {
        card.css( { "left":( anchorElement.offset().left + "px" ) } );
    }

    card.css( { "top":( anchorElement.offset().top - card.height() ) + "px" } );

    delay( 500 ).then(() => stillOnLink( $( e ).data( 'link' ) ) ? card.fadeIn() : isWaiting = false );
}