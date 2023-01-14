var json;
var currentDropDown = null;
var triggeredElementList = null;

$( document ).ready( function() {
    $( this ).click( function( e ) {
        var found = false;

        for ( var i = 0; i < triggeredElementList.length; i++ ) {
            if ( e.target.id == triggeredElementList[i] ) {
                found = true;
                break;
            }
        }

        if ( ! found ) {
            $( "#" + currentDropDown ).slideUp();
            currentDropDown = null;
            triggeredElementList = null;
        }
    });

    parseJson();
});

function parseJson() {
    json = JSON.parse( $( "#json" ).html() );
}

function openDropDownMenu( e ) {
    var menu = $( "#" + $( e ).data( 'menu' ) );
    var ignored = $( e ).data( 'ignored' );
    var ignoredElements = ignored.split( ',' );
    var linkElement = $( "#" + $( e ).data( 'link' ) );

    if ( currentDropDown != null ) {
        $( "#" + currentDropDown ).slideUp();
        currentDropDown = null;
        triggeredElementList = null;
    }

    var difference = ( $( window ).width() - $( "#" + ignoredElements[0] ).offset().left );

    if ( menu.width() >= difference ) {
        menu.css( { "left":( linkElement.offset().left - menu.width() + linkElement.width() ) + "px" } );
    } else {
        menu.css( { "left":linkElement.offset().left + "px" } );
    }

    menu.css( { "top":( linkElement.offset().top + linkElement.height() + 5 ) + "px" } );
    menu.slideDown();

    ignoredElements.push( $( e ).data( 'link' ) );
    currentDropDown = $( e ).data( 'menu' );
    triggeredElementList = ignoredElements;
}