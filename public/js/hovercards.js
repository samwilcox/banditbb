var currentHoverElement = null;
var isWaiting = false;
var linkElement = null;
var closeCard = false;
var hovercards = {};

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
            callback( true );
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
    $( "#hovercard" ).fadeOut();
    $( "#hovercard" ).html( '' );
}

function openHovercard( e ) {
    linkElement = $( e ).data( 'link' );
    isWaiting = true;
    var memberId = $( e ).data( 'memberid' );
    var card = $( "#hovercard" );
    var anchorElement = $( "#" + $( e ).data( 'link' ) );
    var difference = ( $( window ).width() - anchorElement.offset().left );

    if ( card.width() >= difference ) {
        card.css( { "left":( anchorElement.offset().left - card.width() + anchorElement.width() ) + "px" } );
    } else {
        card.css( { "left":( anchorElement.offset().left + "px" ) } );
    }

    card.css( { "top":( anchorElement.offset().top - card.height() ) + "px" } );

    if ( hovercards[memberId] === undefined ) {
        $.ajax({
            url: json.wrapper,
            type: 'get',
            data: {
                'controller':'ajax',
                'action':'gethovercard',
                'id':memberId
            },
            dataType: 'json',
            success: function( response ) {
                if ( response.success ) {
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

    delay( 500 ).then(() => stillOnLink( $( e ).data( 'link' ) ) ? card.fadeIn() : isWaiting = false );
}