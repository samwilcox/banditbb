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

namespace BanditBB\Models;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class AjaxModel extends \BanditBB\Models\BaseModel {

    private static $vars = [];

    public function getHovercard() {
        $memberId = self::request()->id;
        $hash = self::member()->uniqueHash( $memberId );
        $data = self::cache()->getData( 'members' );
        $found = false;

        foreach ( $data as $member ) {
            if ( $member->memberId == $memberId ) {
                $found = true;
            }
        }

        if ( ! $found ) {
            self::output()->renderSource( \json_encode( [ 'status' => false, 'data' => self::localization()->getWords( 'ajaxerrors', 'hovercardMemberNotFound') ] ), \BanditBB\Types\ContentType::JSON );
        }

        $tooltip = self::localization()->quickReplace( 'ajax', 'viewMemberProfile', 'DisplayName', self::member()->displayName( $memberId ) );
        $onlineTooltip = self::localization()->quickReplace( 'ajax', 'onlineStatusTooltip', 'DisplayName', self::member()->displayName( $memberId ) );
        $offlineTooltip = self::localization()->quickReplace( 'ajax', 'offlineStatusTooltip', 'DisplayName', self::member()->displayName( $memberId ) );

        if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::FRIENDS ) && self::member()->signedIn() ) {
            if ( self::member()->isAFriend( $memberId ) ) {
                if ( self::settings()->addRemoveFriendUseAjax ) {
                    $friends = self::output()->getPartial(
                        'Ajax',
                        'Hovercard',
                        'RemoveFriendAjax',
                        [
                            'id'   => $memberId,
                            'hash' => $hash
                        ]
                    );
                } else {
                    $friends = self::output()->getPartial(
                        'Ajax',
                        'Hovercard',
                        'RemoveFriend',
                        [
                            'url'  => self::seo()->seoUrl( 'friends', 'remove', [ 'id' => $memberId ] ),
                            'id'   => $memberId,
                            'hash' => $hash
                        ]
                    );
                }
            } else {
                if ( self::settings()->addRemoveFriendUseAjax ) {
                    $friends = self::output()->getPartial(
                        'Ajax',
                        'Hovercard',
                        'AddFriendAjax',
                        [
                            'id'   => $memberId,
                            'hash' => $hash
                        ]
                    );
                } else {
                    $friends = self::output()->getPartial(
                        'Ajax',
                        'Hovercard',
                        'AddFriend',
                        [
                            'url'  => self::seo()->seoUrl( 'friends', 'add', [ 'id' => $memberId ] ),
                            'id'   => $memberId,
                            'hash' => $hash
                        ]
                    );
                }
            }
        } else {
            $friends = '';
        }

        self::output()->renderSource( \json_encode( [ 'status' => true, 'data' => self::output()->getPartial(
            'Ajax',
            'Member',
            'Hovercard',
            [
                'coverStyle'   => self::member()->hasCoverPhoto( $memberId ) ? self::output()->getPartial( 'Global', 'CoverPhoto', 'Style', [ 'url' => self::member()->getCoverPhoto( $memberId ) ] ) : '',
                'photo'        => self::member()->profilePhoto( $memberId, false, false, 'photoHovercard', false ),
                'url'          => self::seo()->seoUrl( 'profiles', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $memberId, self::member()->displayName( $memberId ) ) ] ),
                'tooltip'      => $tooltip,
                'displayName'  => self::member()->displayName( $memberId ),
                'groupLink'    => self::member()->getPrimaryGroupLink( $memberId, $hash ),
                'onlineStatus' => self::member()->online( $memberId ) ? self::output()->getPartial( 'Ajax', 'Hovercard', 'Online', [ 'tooltip' => $onlineTooltip, 'id' => $hash ] ) : self::output()->getPartial( 'Ajax', 'Hovercard', 'Offline', [ 'tooltip' => $offlineTooltip, 'id' => $hash ] ),
                'joined'       => self::localization()->quickReplace( 'ajax', 'joined', 'Timestamp', self::output()->getPartial( 'Global', 'Text', 'Timestamp', [ 'timestamp' => self::member()->joined( $memberId ), 'hash' => 'timestamptext-joined-' . $hash ] ) ),
                'lastOnline'   => self::localization()->quickReplace( 'ajax', 'lastOnline', 'Timestamp', self::output()->getPartial( 'Global', 'Text', 'Timestamp', [ 'timestamp' => self::member()->lastOnline( $memberId ), 'hash' => 'timestamptext-lastonline-' . $hash ] ) ),
                'posts'        => self::member()->showAbout( 'posts', $memberId ) ? self::output()->getPartial( 'Ajax', 'Hovercard', 'StatBox', [ 'title' => self::localization()->getWords( 'ajax', 'posts' ), 'stat' => self::member()->totalPosts( $memberId ), 'name' => 'posts', 'id' => $hash ] ) : '',
                'reputation'   => self::output()->getPartial( 'Ajax', 'Hovercard', 'StatBox', [ 'title' => self::localization()->getWords( 'ajax', 'reputation' ), 'stat' => self::member()->getReputation( $memberId ), 'name' => 'reputation', 'id' => $hash ] ),
                'gender'       => self::member()->showAbout( 'gender', $memberId ) ? self::output()->getPartial( 'Ajax', 'Hovercard', 'StatBox', [ 'title' => self::localization()->getWords( 'ajax', 'gender' ), 'stat' => self::member()->getGender( $memberId ), 'name' => 'gender', 'id' => $hash ] ) : '',
                'location'     => self::member()->showAbout( 'location', $memberId ) ? self::output()->getPartial( 'Ajax', 'Hovercard', 'StatBox', [ 'title' => self::localization()->getWords( 'ajax', 'location' ), 'stat' => self::member()->getLocationLink( $memberId, $hash ), 'name' => 'location', 'id' => $hash ] ) : '',
                'contentUrl'   => self::seo()->seoUrl( 'search', 'findcontent', [ 'id' => $memberId ] ),
                'messageLink'  => self::member()->canSendMemberMessage( $memberId ) ? self::output()->getPartial( 'Ajax', 'Hovercard', 'Message', [ 'url' => self::seo()->seoUrl( 'messages', 'compose', [ 'rid' => $memberId ] ), 'id' => $memberId ] ) : '',
                'friendsLink'  => $friends,
                'blockLink'    => self::member()->signedIn() ? self::output()->getPartial( 'Ajax', 'Hovercard', 'Block', [ 'url' => self::seoUrl( 'settings', 'blockmember', [ 'id' => $memberId] ), 'hash' => $hash ] ) : '',
                'id'           => $hash
            ]
        )]), \BanditBB\Types\ContentType::JSON );
    }

    public function validateCredentials() {
        self::output()->renderSource( self::member()->validateCredentials( self::request()->identity, self::request()->password )->ajaxOutput, \BanditBB\Types\ContentType::JSON );
    }

    public function getCaptchaImage() {
        self::captcha()->generateCaptcha();
    }

    public function validateForumPassword() {
        $password = self::request()->password;
        $forumId = self::request()->forumid;
        $data = self::cache()->getData( 'forums' );
        $found = false;

        foreach ( $data as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $found = true;
                $forumPassword = $forum->password;
                break;
            }
        }

        if ( ! $found ) {
            self::output()->renderSource( \json_encode( [ 'status' => false, 'data' => self::localization()->getWords( 'ajaxerrors', 'forumNotFound' ) ] ), \BanditBB\Types\ContentType::JSON );
        }

        if ( ! \password_verify( $password, $forumPassword ) ) {
            self::output()->renderSource( \json_encode( [ 'status' => false, 'data' => self::localization()->getWords( 'ajaxerrors', 'forumPasswordInvalid' ) ] ), \BanditBB\Types\ContentType::JSON );
        } else {
            self::output()->renderSource( \json_encode( [ 'status' => true ] ), \BanditBB\Types\ContentType::JSON );
        }
    }

    public function uploadFile() {
        $result = self::upload()->uploadFile( 'file', true, self::request()->type );
        self::output()->renderSource( $result, \BanditBB\Types\ContentType::JSON );
    }

    public function removeFile() {
        $fileId = self::request()->fileid;
        $fileType = self::request()->filetype;

        switch ( $fileType ) {
            case 'attachment':
                $data = self::cache()->getData( 'members_attachments' );
                $found = false;

                foreach ( $data as $attachment ) {
                    if ( $attachment->attachmentId == $fileId ) {
                        $found = true;
                        $fileName = $attachment->fileName;
                        break;
                    }
                }

                if ( $found ) {
                    self::db()->query( self::queries()->deleteAttachment(), [ 'id' => $fileId ] );
                    self::cache()->update( 'members_attachments' );
                    @unlink( ROOT_PATH . self::settings()->uploadDir . '/' . self::settings()->uploadAttachmentsDir . '/' . ( self::member()->signedIn() ? self::member()->memberId() : 'guest' ) . '/' . $fileName );
                }
                break;

            case 'profile-photo':
                $data = self::cache()->getData( 'members_photos' );
                $found = false;

                foreach ( $data as $photo ) {
                    if ( $photo->photoId == $fileId ) {
                        $found = true;
                        $fileName = $photo->fileName;
                        break;
                    }
                }

                if ( $found ) {
                    self::db()->query( self::queries()->deletePhoto(), [ 'id' => $fileId ] );
                    self:cache()->update( 'members_photos' );
                    @unlink( ROOT_PATH . self::settings()->uploadDir . '/' . self::settings()->profilePhotosUploadDir . '/' . self::member()->memberId() . '/' . $fileName );
                }
                break;
        }

        self::output()->renderSource( \json_encode( [ 'status' => true ] ), \BanditBB\Types\ContentType::JSON );
    }
}