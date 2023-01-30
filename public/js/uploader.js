let formData = new FormData();
var initial = true;
var initialId = true;
var fileIdCollection = new Object();
var existingOpened = false;

document.addEventListener( 'DOMContentLoaded', function() {
    const ele = document.getElementById( 'drop-zone' );

    ele.addEventListener( 'dragenter', function( e ) {
        e.preventDefault();
        e.target.classList.add( 'dragging' );
    });

    ele.addEventListener( 'dragover', function( e ) {
        e.preventDefault();
    });

    ele.addEventListener( 'dragleave', function( e ) {
        e.preventDefault();
        e.target.classList.remove( 'dragging' );
    });

    ele.addEventListener( 'drop', function( e ) {
        e.preventDefault();
        e.target.classList.remove( 'dragging' );

        if ( e.dataTransfer.items ) {
            for ( var i = 0; i < e.dataTransfer.items.length; i++ ) {
                if ( e.dataTransfer.items[i].kind == 'file' ) {
                    var file = e.dataTransfer.items[i].getAsFile();
                    formData.append( 'file', file );
                    fileUploadViaAjax();
                }
            }
        } else {
            for ( var i = 0; i < e.dataTransfer.files.length; i++ ) {
                var thisFile = e.originalEvent.dataTransfer.files;
                formData.append( 'file', thisFile[0] );
                fileUploadViaAjax();
            }
        }
    });
});

function showLoadingIcon() {
    $( "#uploader-preloader" ).fadeIn();
}

function hideLoadingIcon() {
    $( "#uploader-preloader" ).fadeOut();
}

function showExistingIcon() {
    $( "#uploader-existing-preloader" ).fadeIn();
}

function hideExistingIcon() {
    $( "#uploader-existing-preloader" ).fadeOut();
}

function openFileBrowser() {
    document.getElementById( 'file-upload' ).click();
}

function fileUploadViaAjax() {
    formData.append( 'controller', 'ajax' );
    formData.append( 'action', 'uploadfile' );
    formData.append( 'type', 'attachment' );

    showLoadingIcon();

    $.ajax({
        url: json.wrapper,
        dataType: 'json',
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        type: 'post',
        success: function( response ) {
            console.log(response);
            if ( response.status ) {
                addFlexToContainer( response.flex );
                fileIdCollection[response.id] = response.id;
            } else {
                addFlexToContainer( response.message );
            }

            formData = new FormData();
            hideLoadingIcon();
        },
        error: function( request, status, error ) {;
            hideLoadingIcon();
        }
    });
}

function onFileSelect( e ) {
    formData.append( 'file', e.files[0] );
    fileUploadViaAjax();
}

function addFlexToContainer( flex ) {
    if ( initial ) {
        initial = false;
        $( "#uploader-container" ).css( { "display":"inline-flex" } );
    }

    $( "#uploader-container" ).append( flex );
}

function removeAttachment( e ) {
    var attachmentId = $( e ).data( 'attachmentid' );
    var fileType = $( e ).data( 'type' );

    $.ajax({
        url: json.wrapper,
        type: 'get',
        data: {
            'controller':'ajax',
            'action':'removefile',
            'fileid':attachmentId,
            'filetype':fileType
        },
        dataType: 'json',
        success: function( response ) {

        },
        error: function( request, status, error ) {

        }
    });

    delete fileIdCollection[attachmentId];
    var element = document.getElementById( 'uploader-file-id-' + attachmentId );
    element.parentNode.removeChild( element );
}

function removeError() {
    var element = document.getElementById( 'uploader-error-item' );
    element.parentNode.removeChild( element );
}

function loadExistingAttachments() {
    if ( ! existingOpened ) {
        showExistingIcon();

        $.ajax({
            url: json.wrapper,
            type: 'get',
            data: {
                'controller':'ajax',
                'action':'getattachments'
            },
            dataType: 'html',
            success: function( response ) {
                $( "#uploader-existing-container" ).append( response );
            },
            error: function( request, status, error ) {

            }
        });

        hideExistingIcon();
    }

    existingOpened = true;
}

function insertImageToPost( e ) {
    var imageSrc = $( e ).data( 'imgsrc' );

    if ( editorType == 'Full' ) {
        $( "#editor-fullbox" ).focus();
        document.execCommand( "insertImage", false, imageSrc );
    } else {
        bbTag( '[image]' + imageSrc, '[/image]' );
    }
}

function populateHiddenWithFileIds() {
    var collection = '';
    var initial = true;

    for ( var property in fileIdCollection ) {
        if ( initial ) {
            initial = false;
            collection = fileIdCollection[property];
        } else {
            collection += ',' + fileIdCollection[property];
        }
    }

    $( "#uploader-fileidcollection" ).val( collection );
}