var json;
var currentDropDown = null;
var triggeredElementList = null;
var currentDialogElement = null;
var selRange;

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
    hljs.highlightAll();
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
    selRange = saveSelection();
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
        dialog.css( { "width" : dialogWidth + "px", "margin-left" : "-" + ( dialog.width() / 2 ) + "px" } );
    }

    toggleBackgroundDisabler( true );
    dialog.fadeIn( { queue: false, duration: 'slow' } );
    dialog.animate( { 'marginTop' : '+=30px' }, 400, 'easeInQuad' );
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
        $( "#" + currentDialogElement ).fadeOut( { queue: false, duration: 'slow' } );
        $( "#" + currentDialogElement ).animate( { 'marginTop' : '-=30px' }, 400, 'easeOutQuad' );
        toggleBackgroundDisabler( false );
        currentDialogElement = null;
    }
}

function validateCredentials( e ) {
    event.preventDefault();

    var submitButton = $( "#" + $( e ).data( 'button' ) );
    var errorBox = $( "#" + $( e ).data( 'errorbox' ) );
    var errorBoxContent = $( "#" + $( e ).data( 'errorboxcontent' ) );
    var identity = $( "#" + $( e ).data( 'identity' ) );
    var password = $( "#" + $( e ).data( 'password' ) );
    var form = $( e ).data( 'form' );
    var originalButtonText = submitButton.val();
    var valid = false;
    var completed = false;

    submitButton.val( json.lang_validating );
    submitButton.attr( "disabled", true );

    $.ajax({
        url: json.wrapper,
        type: 'get',
        data: {
            'controller':'ajax',
            'action':'validatecredentials',
            'identity':identity.val(),
            'password':password.val()
        },
        dataType: 'json',
        success: function( response ) {
            if ( response.status ) {
                errorBoxContent.html();
                errorBox.fadeOut();
                submitButton.val( originalButtonText );
                submitButton.removeAttr( 'disabled' );
                valid = true;
                completed = true;
            } else {
                errorBoxContent.html( response.data );
                errorBox.fadeIn();
                submitButton.val( originalButtonText );
                submitButton.removeAttr( 'disabled' );
                valid = false;
                completed = true;
            }
        }
    });

    ( async() => {
        while ( completed == false ) {
            await new Promise( resolve => setTimeout( resolve, 1000 ) );
        }

        if ( valid == true ) {
            document.getElementsByName( form )[0].submit();
        }
    })();
}

function validateForumPassword( e ) {
    event.preventDefault();

    var submitButton = $( "#" + $( e ).data( 'button' ) );
    var errorBox = $( "#" + $( e ).data( 'errorbox' ) );
    var errorBoxContent = $( "#" + $( e ).data( 'errorboxcontent' ) );
    var forumId = $( e ).data( 'forumid' );
    var password = $( "#" + $( e ).data( 'password' ) );
    var form = $( e ).data( 'form' );
    var originalButtonText = submitButton.val();
    var valid = false;
    var completed = false;

    submitButton.val( json.lang_validating );
    submitButton.attr( "disabled", true );

    $.ajax({
        url: json.wrapper,
        type: 'get',
        data: {
            'controller':'ajax',
            'action':'validateforumpassword',
            'password':password.val(),
            'forumid':forumId
        },
        dataType: 'json',
        success: function( response ) {
            if ( response.status ) {
                errorBoxContent.html();
                errorBox.fadeOut();
                submitButton.val( originalButtonText );
                submitButton.removeAttr( "disabled" );
                valid = true;
                completed = true;
            } else {
                errorBoxContent.html( response.data );
                errorBox.fadeIn();
                submitButton.val( originalButtonText );
                submitButton.removeAttr( "disabled" );
                valid = false;
                completed = true;
            }
        }
    });

    ( async() => {
        while ( completed == false ) {
            await new Promise( resolve => setTimeout( resolve, 1000 ) );
        }

        if ( valid == true ) {
            document.getElementsByName( form )[0].submit();
        }
    })();
}

function closeErrorBox( e ) {
    $( "#" + $( e ).data( 'errorbox' ) ) .fadeOut();
}

function refreshCaptcha( e ) {
    var catchaImage = $( "#" + $( e ).data( 'image' ) );
    var captchaTextBox = $( "#" + $( e ).data( 'field' ) );
    catchaImage.attr( "src", json.wrapper + "?controller=ajax&action=captcha&date=" + Date.now() );
    captchaTextBox.val( '' );
}

function onDivClick( e ) {
    var address = $( e ).data( 'url' );
    location.href = address;
}