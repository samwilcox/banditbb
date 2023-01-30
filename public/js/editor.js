var editorType = 'Lite';
var editorFull;
var editorLite;
var defaultFontSize;
var quoteX = 0;
var codeX = 0;

document.execCommand( "styleWithCSS", false, true );

$( document ).ready( function() {
    defaultFontSize = 3;
    editorFull = $( "#editor-fullbox" );
    editorLite = editorLite;
});

function isWYSIWYGEditorSupported() {
    if ( 'isContentEditable' in document.createElement( 'div' ) ) return true; else return false;
}

function chooseCorrectEditor() {
    if ( isWYSIWYGEditorSupported() ) {
        $( "#editor-fullbox" ).show();
        $( "#editor-litebox" ).hide();
        $( "#editor-type-string" ).val( 'full' );
        editorType = 'Full';
    } else {
        $( "#editor-fullbox" ).hide();
        $( "#editor-litebox" ).show();
        $( "#editor-undo" ).hide();
        $( "#editor-redo" ).hide();
        $( "#editor-type-string" ).val( 'lite' );
        editorType = 'Lite';
    }
}

function restoreSelection( range ) {
    if ( range ) {
        if ( window.getSelection ) {
            sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange( range );
        } else if ( document.selection && range.select ) {
            range.select();
        }
    }
}

function bbTag( begin, end ) {
    if ( ! begin ) begin = '';
    if ( ! end ) end = '';

    var bbObj = document.getElementById( 'editor-litebox' );
    $( "#editor-litebox" ).focus();

    if ( typeof document.selection != 'undefined' ) {
        var range = document.selection.createRange();
        var rangeText = range.text;
        if ( rangeText.length != 0 ) range.moveStart( 'character', bbObj, rangeText );
        range.Select();
    } else if ( $( "#editor-litebox" ).prop( "selectionStart" ) || $( "#editor-litebox" ).prop( "selectionStart" ) == '0' ) {
        var selStart = $( "#editor-litebox" ).prop( "selectionStart" ), selEnd = $( "#editor-litebox" ).prop( "selectionEnd" );
        var selStartEnd = bbObj.value.substring( selStart, selEnd );
        bbObj.value = bbObj.value.substr( 0, selStart ) + begin + selStartEnd + end + bbObj.value.substr( selEnd );
        var p = selStart + begin.length + selStartEnd.length + end.length;
        bbObj.focus();
        $( "#editor-litebox" ).prop( "selectionStart", p );
        $( "#editor-litebox" ).prop( "selectionEnd", p );
        bbObj.focus();
    } else {
        bbObj.value += begin + end;
        bbObj.focus();
    }
}

function editorCommand( e ) {
    var cmd = $( e ).data( 'command' );

    if ( editorType == 'Full' ) {
        editorFull.focus();
        document.execCommand( cmd );
    } else {
        switch ( cmd ) {
            case 'bold':
                bbTag( '[b]', '[/b]' );
                break;

            case 'italic':
                bbTag( '[i]', '[/i]' );
                break;

            case 'underline':
                bbTag( '[u]', '[/u]' );
                break;
                
            case 'strikethrough':
                bbTag( '[strikethrough]', '[/strikethrough]' );
                break;

            case 'subscript':
                bbTag( '[subscript]', '[/subscript]' );
                break;

            case 'superscript':
                bbTag( '[superscript]', '[/superscript]' );
                break;

            case 'justifyLeft':
                bbTag( '[AlignLeft]', '[/AlignLeft]' );
                break;

            case 'justifyCenter':
                bbTag( '[AlignCenter]', '[/AlignCenter]' );
                break;

            case 'justifyRight':
                bbTag( '[AlignRight]', '[/AlignRight]' );
                break;

            case 'outdent':
                bbTag( '[outdent]', '[/outdent]' );
                break;

            case 'indent':
                bbTag( '[indent]', '[/indent]' );
                break;

            case 'insertOrderedList':
                bbTag( '[ol]\n[*]', '[/*]\n[/ol]' );
                break;

            case 'insertUnorderedList':
                bbTag( '[ul]\n[*]', '[/*]\n[/ul]' );
                break;

            case 'insertHorizontalRule':
                bbTag( '[hr]', '[/hr]' );
                break;
        }
    }
}

function changeFontName( e ) {
    var fontName = $( e ).data( 'font' );

    if ( editorType == 'Full' ) {
        editorFull.focus();
        document.execCommand( "fontName", false, fontName );
    } else {
        bbTag( '[font="' + fontName + '"]', '[/font]' );
    }
}

function changeFontSize( e ) {
    var fontSize = $( e ).data( 'size' );

    if (fontSize == 'default' ) {
        fontSize = defaultFontSize;
    }

    if ( editorType == 'Full' ) {
        editorFull.focus();
        document.execCommand( "fontSize", false, fontSize );
    } else {
        bbTag( '[fontsize="' + fontSize + '"]', '[/fontsize]' );
    }
}

function changeFontColor( e ) {
    var colorBox = $( "#" + $( e ).data( 'box' ) ).val();

    if ( editorType == 'Full' ) {
        restoreSelection( selRange );
        editorFull.focus();
        document.execCommand( "foreColor", false, colorBox );
    } else {
        bbTag( '[fontcolor="' + colorBox + '"]', '[/fontcolor]' );
    }
}

function insertQuote() {
    quoteX++;

    if ( editorType == 'Full' ) {
        editorFull.focus();
        document.execCommand( "insertHTML", false, '<div class="postQuoteBox" contenteditable="false"><div class="postQuoteBoxHeader" contenteditable="false">Quote</div><div class="postQuoteBoxContent" contenteditable="false"><p contenteditable="true" id="quote-' + quoteX + '"></p></div></div><br><br>' );
        $( "#quote-" + quoteX ).focus();
    } else {
        bbTag( '[quote]', '[/quote]' );
    }
}

function insertHyperlink( e ) {
    var linkTitle = $( "#" + $( e ).data( 'title' ) ).val();
    var linkUrl = $( "#" + $( e ).data( 'url' ) ).val();

    if ( editorType == 'Full' ) {
        restoreSelection( selRange );
        editorFull.focus();

        if ( linkTitle.length < 1 ) {
            document.execCommand( "createLink", false, linkUrl );
        } else {
            document.execCommand( "insertHTML", false, '<a href="' + linkUrl + '">' + linkTitle + '</a>' );
        }
    } else {
        if ( linkTitle.length < 1 ) {
            bbTag( '[link]' + linkUrl + '[/link]' );
        } else {
            bbTag( '[link="' + linkUrl + '"]' + linkTitle + '[/link]' );
        }
    }

    $( "#" + $( e ).data( 'title' ) ).val( '' );
    $( "#" + $( e ).data( 'url' ) ).val( '' );
}

function insertImage( e ) {
    var imageUrl = $( "#" + $( e ).data( 'url' ) ).val();

    if ( editorType == 'Full' ) {
        restoreSelection( selRange );
        editorFull.focus();

        document.execCommand( "insertImage", false, imageUrl );
    } else {
        bbTag( '[image]' + imageUrl + '[/image]' );
    }

    $( "#" + $( e ).data( 'url' ) ).val( '' );
}

function insertVideo( e ) {
    var videoUrl = $( "#" + $( e ).data( 'url' ) ).val();

    if ( editorType == 'Full' ) {
        restoreSelection( selRange );
        editorFull.focus();
        document.execCommand( "insertHTML", false, createVideoFrame( videoUrl, 560, 315 ) );
    } else {
        bbTag( '[video]' + videoUrl + '[/video]' );
    }

    $( "#" + $( e ).data( 'url' ) ).val( '' );
}

function parseVideo( url ) {
    url.match(/(https?:\/\/|\/\/|)(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com)|dailymotion\.com)\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);

    if (RegExp.$3.indexOf('youtu') > -1) {
        var type = 'youtube';
    } else if (RegExp.$3.indexOf('vimeo') > -1) {
        var type = 'vimeo';
    }
	else if (RegExp.$3.indexOf('dailymotion') > -1) {
		var type = 'dailymotion';
	}

    return {
        type: type,
        id: RegExp.$6
    };
}

function createVideoFrame( url, width, height ) {
    var mediaObj = parseVideo( url );
	var iframe = '';
	
	if ( mediaObj.type == 'youtube' )
	{
		iframe = '<iframe width="' + width + '" height="' + height + '" src="https://www.youtube.com/embed/' + mediaObj.id + '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
	}
	else if ( mediaObj.type == 'vimeo' )
	{
		iframe = '<iframe src="https://player.vimeo.com/video/' + mediaObj.id + '" width="' + width + '" height="' + height + '" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>';
	}
	else if ( mediaObj.type == 'dailymotion' )
	{
		iframe = '<iframe frameborder="0" width="' + width + '" height="' + height + '" src="https://www.dailymotion.com/embed/video/' + mediaObj.id + '" allowfullscreen allow="autoplay"></iframe>';
	}
	
	return iframe;
}

function codeEditorTab( e, ev ) {
    if ( ev.key == "Tab" ) {
        ev.preventDefault();
        var start = e.selectionStart;
        var end = e.selectionEnd;
        e.value = e.value.substring( 0, start ) + "\t" + e.value.substring( end );
        e.selectionStart = e.selectionEnd = start + 1;
    }
}

function checkKey( e, ev ) {
    if ( ev.key == "Tab" ) {
        ev.preventDefault();
        e.focus();
        document.execCommand( "insertHTML", false, '&#9;' );
    }
}

function insertCode( e ) {
    var codeBody = $( "#" + $( e ).data( 'bodyfield' ) ).val();
    var codeSelect = $( "#" + $( e ).data( 'codeselect' ) ).val();

    codeX++;

    if ( editorType == 'Full' ) {
        restoreSelection( selRange );
        editorFull.focus();
        document.execCommand( "insertHTML", false, '<div class="postCodeBox" contenteditable="false"><div class="postCodeBoxHeader" contenteditable="false"><i class="fa-solid fa-code"></i> Code</div><div class="postCodeBoxContent" contenteditable="false"><p contenteditable="false"><pre contenteditable="false"><code id="code-block-' + codeX + '" class="language-' + codeSelect + '" contenteditable="true" spellcheck="false" onkeydown="checkKey(this, event);">' + codeBody + '</code></pre></p></div></div><br><br>' );
        hljs.highlightElement( document.getElementById( 'code-block-' + codeX ) );
        closeDialog();
    } else {
        bbTag( '[code="' + codeSelect + '"]' + codeBody + '[/code]' );
    }
}

function insertEmoticon( e ) {
    var imgSrc = $( e ).data( 'emoticon' );

    if ( editorType == 'Full' ) {
        editorFull.focus();
        document.execCommand( "insertImage", false, imgSrc );
    } else {
        bbTag( '[emoticon]' + imgSrc + '[/emoticon]' );
    }
}

function showQuickReply() {
    var linkElement = $( "#quick-reply-link" );
    var quickReply = $( "#editor-container" );
    var ele = document.getElementById( 'editor-container' );

    linkElement.hide();
    quickReply.show();

    ele.scrollIntoView();
    
    if ( editorType == 'Full' ) {
        editorFull.focus();
    } else {
        editorLite.focus();
    }
}

function toggleFollowBox( e ) {
    var follow = $( "#" + $( e ).data( 'follow' ) );
    var include = $( "#" + $( e ).data( 'include' ) );

    if ( follow.is( ":checked" ) ) {
        include.show();
    } else {
        include.hide();
    }
}