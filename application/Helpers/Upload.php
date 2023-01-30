<?php

/**
 * Bandit Bulletin Board
 * 
 * By Sam Wilcox <sam@banditbb.com>
 * https://www.banditbb.com
 * 
 * You are bound by the terms of the user-end license agreement.
 * View the user-end license agreement at:
 * https://license.banditbb.com
 */

namespace BanditBB\Helpers;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Upload extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    private static function findFilenameNotTaken( $path, $filename ) {
        $count = 0;
        $ext = \pathinfo( $filename, PATHINFO_EXTENSION );
        $base = \basename( $filename, '.' . $ext );

        while ( \file_exists( "$path/$filename" ) ) {
            $count++;
            $filename = "$base($count).$ext";
        }

        return $filename;
    }

    private static function calculateGuestsSpaceUsed() {
        $data = self::cache()->getData( 'members_attachments' );
        $spaceUsed = 0;

        foreach ( $data as $attachment ) {
            if ( $attachment->memberId == 0 ) {
                $spaceUsed = ( $attachment->fileSize + $spaceUsed );
            }
        }

        return $spaceUsed;
    }

    public static function uploadFile( $fieldName, $viaAjax = false, $type = 'attachments' ) {
        if ( ! self::member()->signedIn() ) {
            if ( ! self::settings()->guestFileUploads ) {
                if ( $viaAjax ) {
                    return \json_encode( [ 'status' => false, 'message' => self::getAjaxErrorTemplate( self::localization()->getWords( 'ajaxerrors', 'uploadErrorGuestsRestricted' ) ) ] );
                } else {
                    self::errors()->throwError( self::localization()->getWords( 'errors', 'uploadErrorGuestsRestricted' ) );
                }
            }
        }

        if ( isset( $_FILES[$fieldName] ) ) {
            $tmpName = $_FILES[$fieldName]['tmp_name'];
            $fileName = $_FILES[$fieldName]['name'];
            $fileSize = $_FILES[$fieldName]['size'];
            $fileExtension = \pathinfo( $fileName, PATHINFO_EXTENSION );
            $extList = '';
            $initial = true;
            $allowedExts = self::settings()->uploadAllowedFileExtensions;

            if ( \count( $allowedExts ) > 0 ) {
                foreach ( $allowedExts as $ext ) {
                    if ( $initial ) {
                        $extList .= $ext;
                        $initial = false;
                    } else {
                        $extList .= ', ' . $ext;
                    }
                }

                if ( ! \in_array( $fileExtension, $allowedExts ) ) {
                    if ( $viaAjax ) {
                        return \json_encode( [ 'status' => false, 'message' => self::getAjaxErrorTemplate( self::localization()->getWords( 'ajaxerrors', 'uploadErrorInvalidExtension' ) ) ] );
                    } else {
                        self::errors()->throwError( self::localization()->quickMultiWordReplace( 'errors', 'uploadErrorInvalidExtension', [ 'Extension' => $fileExtension, 'AllowedExtensions' => $extList ] ) );
                    }
                }
            }

            if ( $type == 'attachment' && self::member()->signedIn() ) {
                $totalSpaceUsed = self::member()->getTotalAttachmentSpaceUsed();

                if ( self::settings()->attachmentMaxSpaceAllowed != 0 && ( $totalSpaceUsed + $fileSize ) > self::settings()->attachmentMaxSpaceAllowed ) {
                    if ( $viaAjax ) {
                        return \json_encode( [ 'status' => false, 'message' => self::getAjaxErrorTemplate( self::localization()->getWords( 'ajaxerrors', 'uploadErrorNoSpaceLeft' ) ) ] );
                    } else {
                        self::errors()->throwError( self::localization()->quickMultiWordReplace( 'errors', 'uploadErrorNoSpaceLeft', [
                            'Quota' => self::file()->getReadableFileSize( self::settings()->attachmentMaxSpaceAllowed ),
                            'Size'  => self::file()->getReadableFileSize( $fileSize )
                        ]));
                    }
                }
            } elseif ( $type == 'attachment' && ! self::member()->signedIn() ) {
                $totalSpaceUsed = self::calculateGuestsSpaceUsed();

                if ( self::settings()->guestsMaxSpaceAllowed != 0 && ( $totalSpaceUsed + $fileSize ) > self::settings()->guestsMaxSpaceAllowed ) {
                    if ( $viaAjax ) {
                        return \json_encode( [ 'status' => false, 'message' => self::getAjaxErrorTemplate( self::localization()->getWords( 'ajaxerrors', 'uploadErrorGuestsNoSpaceLeft' ) ) ] );
                    } else {
                        self::errors()->throwError( self::localization()->quickMultiWordReplace( 'errors', 'uploadErrorGuestsNoSpaceLeft', [
                            'Quota' => self::file()->getReadableFileSize( self::settings()->guestsMaxSpaceAllowed ),
                            'Size'  => self::file()->getReadableFileSize( $fileSize )
                        ]));
                    }
                }
            }
            
            $uploadDir = self::settings()->uploadDir;
            $attachmentsUploadDir = self::settings()->uploadAttachmentsDir;
            $profilePhotosUploadDir = self::settings()->uploadProfilePhotosDir;

            if ( self::member()->signedIn() ) {
                $userDir = self::member()->memberId();
            } else {
                $userDir = 'guest';
            }

            if ( $type == 'attachment' ) {
                $attachmentsDir = ROOT_PATH . $uploadDir . '/' . $attachmentsUploadDir . '/' . $userDir;

                if ( ! \file_exists( $attachmentsDir ) ) {
                    @mkdir( $attachmentsDir, 0777, true );
                }
                
                $currentAttachments = @scandir( $attachmentsDir );
                $fileName = self::findFilenameNotTaken( $currentAttachments, $fileName );

                if ( ! \move_uploaded_file( $tmpName, $attachmentsDir . '/' . $fileName ) ) {
                    if ( $viaAjax ) {
                        return \json_encode( [ 'status' => false, 'message' => self::getAjaxErrorTemplate( self::localization()->getWords( 'ajaxerrors', 'uploadErrorUploadFailed' ) ) ] );
                    } else {
                        self::errors()->throwError( self::localization()->getWords( 'errors', 'uploadErrorUploadFailed' ) );
                    }
                }

                self::db()->query( self::queries()->insertAttachment(), [ 'fileName' => $fileName, 'fileSize' => $fileSize, 'memberId' => self::member()->signedIn() ? self::member()->memberId() : 0 ] );

                $newAttachmentId = self::db()->insertId();
                $isImage = false;

                self::cache()->update( 'members_attachments' );

                if ( \count( self::settings()->knownImageExtensions ) > 0 ) {
                    foreach ( self::settings()->knownImageExtensions as $extension ) {
                        if ( \strtolower( $fileExtension ) == \strtolower( $extension ) ) $isImage = true;
                    }
                }

                if ( $isImage ) {
                    $flex = self::output()->getPartial(
                        'UploadHelper',
                        'Template',
                        'Photo',
                        [
                            'imgUrl'   => self::vars()->baseUrl . '/' . $uploadDir . '/' . $attachmentsUploadDir . '/' . $userDir . '/' . $fileName,
                            'fileName' => $fileName,
                            'fileSize' => self::file()->getReadableFileSize( $fileSize ),
                            'id'       => $newAttachmentId,
                            'type'     => 'attachment'
                        ]
                    );
                } else {
                    $flex = self::output()->getPartial(
                        'UploadHelper',
                        'Template',
                        'File',
                        [
                            'fileName' => $fileName,
                            'fileSize' => $fileSize,
                            'id'       => $newAttachmentId,
                            'type'     => $attachment
                        ]
                    );
                }

                if ( $viaAjax ) {
                    return \json_encode( [ 'status' => true, 'id' => $newAttachmentId, 'flex' => $flex ] );
                } else {
                    return $newAttachmentId;
                }
            } else {
                if ( ! self::member()->signedIn() ) {
                    if ( $viaAjax ) {
                        return \json_encode( [ 'status' => false, 'message' => self::getAjaxErrorTemplate( self::localization()->getWords( 'ajaxerrors', 'uploadErrorNotSignedIn' ) ) ] );
                    } else {
                        self::errors()->throwError( self::localization()->getWords( 'errors', 'uploadErrorNotSignedIn' ) );
                    }
                }

                $profilePhotosDir = ROOT_PATH . $uploadDir . '/' . $profilePhotosUploadDir . '/' . self::member()->memberId();

                if ( ! \file_exists( $profilePhotosDir ) ) @mkdir( $profilePhotosDir, 0777, true );

                $currentFiles = @scandir( $profilePhotosDir );

                foreach ( $currentFiles as $file ) {
                    if ( $file != '.' && $file != '..' ) {
                        @unlink( $file );
                    }
                }

                if ( ! \move_uploaded_file( $tmpName, $profilePhotosDir . 'profile-photo.' . $fileExtension ) ) {
                    if ( $viaAjax ) {
                        return \json_encode( [ 'status' => false, 'message' => self::getAjaxErrorTemplate( self::localization()->getWords( 'ajaxerrors', 'uploadErrorUploadFailed' ) ) ] );
                    } else {
                        self::errors()->throwError( self::localization()->getWords( 'errors', 'uploadErrorUploadFailed' ) );
                    }
                }

                $photoData = self::member()->haveProfilePhoto();

                if ( $photoData->exists ) {
                    self::db()->query( self::queries()->deleteProfilePhoto(), [ 'id' => $photoData->id ] );
                }

                self::db()->query( self::queries()->insertProfilePhoto(), [ 'fileName' => $fileName, 'fileSize' => $fileSize, 'timestamp' => \time() ] );

                $newPhotoId = self::db()->insertId();

                self::db()->query( self::queries()->updateMembersProfilePhoto(), [ 'type' => 'uploaded', 'photoId' => $newPhotoId, 'id' => self::member()->memberId() ] );

                self::cache()->massUpdate( [ 'members', 'members_photos' ] );

                if ( $viaAjax ) {
                    return \json_encode( [ 'status' => true, 'id' => $newPhotoId ] );
                } else {
                    return $newPhotoId;
                }
            }
        }
    }

    private static function getAjaxErrorTemplate( $error ) {
        return self::output()->getPartial( 'UploadHelper', 'Template', 'Error', [ 'error' => $error ] );
    }
}