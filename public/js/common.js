var json;
var currentDropDown = null;
var triggeredElementList = null;
var currentDialogElement = null;

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

function toggle( e ) {
    var elementHeader = $( "#" + $( e ).data( 'header' ) );
    var elementContent = $( "#" + $( e ).data( 'content' ) );
    var elementIcon = $( "#" + $( e ).data( 'icon' ) );
    var collapse = $( e ).data( 'collapse' );
    var expand = $( e ).data( 'expand' );

    if ( elementContent.is( ":visible" ) ) {
        elementContent.slideUp();
        elementIcon.removeClass( collapse );
        elementIcon.addClass( expand );
        elementHeader.css( { "opacity":0.5 } );
    } else {
        elementContent.slideDown();
        elementIcon.removeClass( expand );
        elementIcon.addClass( collapse );
        elementHeader.css( { "opacity":1.0 } );
    }
}

function togglePasswordField( e ) {
    var passwordField = $( "#" + $( e ).data( 'field' ) );
    var icon          = $( "#" + $( e ).data( 'icon' ) );

    if ( passwordField.attr( 'type' ) == 'password' ) {
        passwordField.attr( 'type', 'text' );
        icon.removeClass( json.eyeSlash );
        icon.addClass( json.eyeSlashNone );
        passwordField.focus();
    } else {
        passwordField.attr( 'type', 'password' );
        icon.removeClass( json.eyeSlashNone );
        icon.addClass( json.eyeSlash );
        passwordField.focus();
    }
}

function toggleBackgroundDisabler( mode ) {
    if ( mode ) {
        $( "#background-disabler" ).fadeIn();
    } else {
        $( "#background-disabler" ).fadeOut();
    }
}

function openDialog( e ) {
    var dialog = $( "#" + $( e ).data( 'dialog' ) );
    var dialogWidth = $( e ).data( 'width' );
    var modifyMargin = $( e ).data( 'margin' );

    dialog.css( { "width" : dialogWidth + "px" } );

    if ( currentDialogElement != null ) {
        $( "#" + currentDialogElement ).fadeOut();
        toggleBackgroundDisabler( false );
        currentDialogElement = null;
    }

    if ( modifyMargin != true ) {
        dialog.css( { "width" : dialogWidth + "px", "margin-top" : "-" + ( dialog.height() / 2 ) + "px", "margin-left" : "-" + ( dialog.width() / 2 ) + "px" } );
    }

    toggleBackgroundDisabler( true );
    dialog.fadeIn();
    currentDialogElement = dialog.attr( 'id' );
}

function openDialogAlt( dialog, width ) {
    var dialog = $( "#" + dialog );
    var dialogWidth = width;
    var modifyMargin = false;

    dialog.css( { "width" : dialogWidth + "px" } );

    if ( currentDialogElement != null ) {
        $( "#" + currentDialogElement ).fadeOut();
        toggleBackgroundDisabler( false );
        currentDialogElement = null;
    }

    if ( modifyMargin != true ) {
        dialog.css( { "width" : dialogWidth + "px", "margin-top" : "-" + ( dialog.height() / 2 ) + "px", "margin-left" : "-" + ( dialog.width() / 2 ) + "px" } );
    }

    toggleBackgroundDisabler( true );
    dialog.fadeIn();
    currentDialogElement = dialog.attr( 'id' );
}

function closeDialog() {
    if ( currentDialogElement != null ) {
        $( "#" + currentDialogElement ).fadeOut();
        toggleBackgroundDisabler( false );
        currentDialogElement = null;
    }
}