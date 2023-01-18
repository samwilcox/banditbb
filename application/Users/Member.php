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

namespace BanditBB\Users;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Member extends \BanditBB\Application {

    protected static $instance;
    protected static $member;

    public function __construct() {
        self::constructData();
        self::checkIfMember();
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    private static function constructData() {
        self::$member = new \stdClass();

        self::$member->id = 0;
        self::$member->signedIn = false;
        self::$member->username = 'Guest';
        self::$member->displayName = 'Guest';
        self::$member->emailAddress = null;
        self::$member->localizationId = null;
        self::$member->themeId = null;
        self::$member->themePath = null;
        self::$member->themeUrl = null;
        self::$member->imagesetUrl = null;
        self::$member->primaryGroup = null;
        self::$member->secondaryGroups = [];
        self::$member->timeZone = null;
        self::$member->dateFormat = null;
        self::$member->timeFormat = null;
        self::$member->dateTimeFormat = null;
        self::$member->timeAgo = true;
        self::$member->perPage = new \stdClass();
        self::$member->perPage->topics = 15;
        self::$member->perPage->posts = 15;
        self::$member->perPage->results = 15;
        self::$member->perPage->members = 15;
        self::$member->perPage->messages = 15;
        self::$member->classes = new \stdClass();
        self::$member->classes->noPhotoThumbnail = null;
        self::$member->classes->photo = null;
        self::$member->classes->photoThumbnail = null;
        self::$member->classes->photoThumbnailTiny = null;
        self::$member->messagesEnabled = null;
        self::$member->notificationsEnabled = null;
        self::$member->notificationsPeriodicSetting = null;
        self::$member->attachments = [];
        self::$member->signatureEnabled = false;
        self::$member->showSignatures = true;
    }

    private static function checkIfMember() {
        if ( isset( $_SESSION['BanditBB_ID'] ) ) {
            self::$member->signedIn = true;
            self::$member->id = $_SESSION['BanditBB_ID'];
        } else {
            self::$member->signedIn = false;
            self::$member->id = 0;
        }

        $data = self::cache()->getData( 'members' );
        $found = false;

        foreach ( $data as $member ) {
            if ( $member->memberId == self::$member->id ) {
                $found = true;
                break;
            }
        }

        if ( ! $found ) {
            self::$member->signedIn = false;
            self::$member->id = 0;
        }

        self::loadMemberData();
    }

    private static function loadMemberData() {
        if ( self::$member->signedIn ) {
            $data = self::cache()->getData( 'members' );

            foreach ( $data as $member ) {
                if ( $member->memberId == self::$member->id ) {
                    self::$member->primaryGroup = $member->primaryGroup;
                    self::$member->secondaryGroups = \strlen( $member->secondaryGroups ) > 4 ? \unserialize( $member->secondaryGroups ) : '';
                    self::$member->timeZone = $member->timeZone;
                    self::$member->dateFormat = $member->dateFormat;
                    self::$member->timeFormat = $member->timeFormat;
                    self::$member->dateTimeFormat = $member->dateTimeFormat;
                    self::$member->timeAgo = $member->timeAgo == 1 ? true : false;
                    self::$member->perPage->topics = \intval( $member->topicsPerPage );
                    self::$member->perPage->posts = \intval( $member->postsPerPage );
                    self::$member->perPage->results = \intval( $member->resultsPerPage );
                    self::$member->perPage->members = \intval( $member->membersPerPage );
                    self::$member->perPage->messages = \intval( $member->messagesPerPage );
                    self::$member->notificationsEnabled = $member->notificationsEnabled == 1 ? true : false;
                    self::$member->messagesEnabled = $member->messagesEnabled == 1 ? true : false;
                    self::$member->themeId = $member->themeId;
                    self::$member->localizationId = $member->localizatioId;
                    self::$member->notificationPeriodicSetting = $member->notifcationPeriodicSetting;
                    self::$member->attachments = self::getAttachments();
                    self::$member->signatureEnabled = $member->signatureEnabled == 1 ? true : false;
                    self::$member->showSignatures = $member->showSignatures == 1 ? true : false;
                    break;
                }
            } 
        } else {
            if ( isset( $_COOKIE['BanditBB_Localization_ID'] ) ) {
                self::$member->localizationId = $_COOKIE['BanditBB_Localization_ID'];
            } else {
                self::$member->localizationId = self::settings()->defaultLocalizationId;
            }

            if ( isset( $_COOKIE['BanditBB_Theme_ID'] ) ) {
                self::$member->themeId = $_COOKIE['BanditBB_Theme_ID'];
            } else {
                self::$member->themeId = self::settings()->defaultThemeId;
            }

            self::$member->primaryGroup = self::settings()->guestGroupId;
            self::$member->secondaryGroups = [];
            self::$member->timeZone = self::settings()->timeZone;
            self::$member->dateFormat = self::settings()->dateFormat;
            self::$member->timeFormat = self::settings()->timeFormat;
            self::$member->dateTimeFormat = self::settings()->dateTimeFormat;
            self::$member->timeAgo = self::settings()->timeAgo;
            self::$member->perPage->topics = self::settings()->topicsPerPage;
            self::$member->perPage->posts = self::settings()->postsPerPage;
            self::$member->perPage->results = self::settings()->resultsPerPage;
            self::$member->perPage->members = self::settings()->membersPerPage;
            self::$member->perPage->messages = self::settings()->messagesPerPage;
            self::$member->notificationsEnabled = false;
            self::$member->messagesEnabled = false;
        }

        $data = self::cache()->getData( 'installed_themes' );

        foreach ( $data as $theme ) {
            if ( $theme->themeId == self::$member->themeId ) {
                $folder = $theme->folder;
                $imagesetFolder = $theme->imageset;
                break;
            }
        }

        self::$member->themePath = ROOT_PATH . 'themes/' . $folder . '/';
        self::$member->themeUrl = self::vars()->baseUrl . '/themes/' . $folder;
        self::$member->imagesetUrl = self::vars()->baseUrl . '/public/imagesets/' . $imagesetFolder;

        \date_default_timezone_set( self::$member->timeZone );
    }

    public static function populateVarsUsingOutput( $arr ) {
        self::$member->classes->noPhoto = $arr['noPhotoClass'];
        self::$member->classes->noPhotoThumbnail = $arr['noPhotoThumbnailClass'];
        self::$member->classes->photo = $arr['photoClass'];
        self::$member->classes->photoThumbnail = $arr['photoThumbnailClass'];
        self::$member->classes->photoThumbnailTiny = $arr['photoThumbnailTinyClass'];
    }

    public static function getMemberData( $field, $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::cache()->getData( 'members' );
        $found = false;

        foreach ( $data as $member ) {
            if ( $member->memberId == $id ) {
                $found = true;
                $value = $member->$field;
                break;
            }
        }

        if ( ! $found ) return null;

        return $value;
    }

    public static function getMemberDataCollection( $fields = [], $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $retVal = new \stdClass();
        $data = self::cache()->getData( 'members' );
        $found = false;

        foreach ( $data as $member ) {
            if ( $member->memberId == $id ) {
                $found = true;

                if ( \count( $fields ) > 0 ) {
                    foreach ( $fields as $field ) {
                        $retVal->$field = $member->$field;
                    }
                }

                break;
            }
        }

        if ( ! $found ) return null;

        return $retVal;
    }

    public static function age( $id = null ) {
        $data = self::getMemberDataCollection( [ 'dobMonth', 'dobDay', 'dobYear', 'displayAge' ], $id );

        if ( $data == null || $data->displayAge == 0 ) return null;
        
        if ( $data->dobMonth != 0 && $data->dobDay != 0 && $data->dobYear != 0 ) {
            return self::math()->calculateAge( $data->dobMonth, $data->dobDay, $data->dobYear );
        }
    }

    public static function displayName( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'displayName', $id );

        if ( $data == null || \strlen( $data ) < 1 ) return self::localization()->getWords( 'global', 'unknown' );

        return $data;
    }

    public static function primaryGroup( $id = null ) {
        $data = self::getMemberData( 'primaryGroupId', $id );

        if ( $data == null || $data == 0 ) return self::settings()->guestGroupId;

        return $data;
    }

    public static function secondaryGroups( $id = null ) {
        $data = self::getMemberData( 'secondaryGroups', $id );

        if ( $data == null || \stren( $data ) < 1 ) return false;

        return \strlen( $data ) > 0 ? \unserialize( $data ) : null;
    }

    public static function hasFeaturePermissions( $feature ) {
        $data = self::cache()->getData( 'feature_permissions' );
        $found = false;

        foreach ( $data as $permission ) {
            if ( \strtolower( $permission->systemIdentifier ) == \strtolower( $feature ) ) {
                $found = true;
                $enabled = $permission->enabled == 1 ? true : false;
                $allowedUsers = \strlen( $permission->allowedUsers ) > 4 ? \unserialize( $permission->allowedUsers ) : null;
                $allowedGroups = \strlen( $permission->allowedGroups ) > 4 ? \unserialize( $permission->allowedGroups ) : null;
                break;
            }
        }

        if ( ! $found || ! $enabled ) return false;
        if ( self::primaryGroup() == self::settings()->administratorGroupId ) return true;

        if ( $allowedGroups != null && \count( $allowedGroups ) > 0 ) {
            foreach ( $allowedGroups as $groupId ) {
                if ( self::primaryGroup() == $groupId ) {
                    return true;
                }

                $secondaryGroups = self::secondaryGroups();

                if ( $secondaryGroups != null && \count( $secondaryGroups ) > 0 ) {
                    foreach ( $secondaryGroups as $id ) {
                        if ( $id == $groupId ) {
                            return true;
                        }
                    }
                }
            }
        }

        if ( $allowedUsers != null && \count( $allowedUsers ) > 0 ) {
            foreach ( $allowedUsers as $id ) {
                if ( $id == self::$member->id ) {
                    return true;
                }
            }
        }

        return true; // TEMP SET TO TRUE !!!! RESTORE TO FALSE ONCE TESTING IS COMPLETE
    }

    public static function online( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::cache()->getData( 'sessions' );
        $found = false;

        foreach ( $data as $session ) {
            if ( $session->memberId == $id ) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    public static function lastClick( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::cache()->getData( 'sessions' );
        $found = false;

        foreach ( $data as $session ) {
            if ( $session->memberId == $id ) {
                $found = true;
                $lastClick = $session->lastClick;
                break;
            }
        }

        if ( ! $found || $lastClick == 0 ) return self::localization()->getWords( 'global', 'unknown' );

        return self::dateTime()->parse( $lastClick, [ 'timeOnly' => true ] );
    }

    public static function lastOnline( $id = null ) {
        $data = self::getMemberData( 'lastOnlineTimestamp', $id );

        if ( $data == null || $data == 0 ) return self::localization()->getWords( 'global', 'unknown' );

        return self::dateTime()->parse( $data );
    }

    public static function joined( $id = null, $full = false ) {
        $data = self::getMemberData( 'joinedTimestamp', $id );

        if ( $data == null || $data == 0 ) return self::localization()->getWords( 'global', 'unknown' );

        if ( $full ) {
            return self::dateTime()->parse( $data, [ 'timeAgo' => true ] );
        } else {
            return self::dateTime()->parse( $data, [ 'dateOnly' => true, 'timeAgo' => true ] );
        }
    }

    public static function getTimeZone( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'timeZone', $id );

        if ( $data == null ) return self::timeZone();

        return $data;
    }

    public static function getDateFormat( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'dateFormat', $id );

        if ( $data == null ) return self::dateFormat();

        return $data;
    }

    public static function getTimeFormat( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'timeFormat', $id );

        if ( $data == null ) return self::timeFormat();

        return $data;
    }

    public static function getDateTimeFormat( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'dateTimeFormat', $id );

        if ( $data == null ) return self::dateTimeFormat();

        return $data;
    }

    public static function getTimeAgo( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'timeAgo', $id );

        if ( $data == null ) return self::timeAgo();

        return $data == 1 ? true : false;
    }

    public static function localizationId( $id = null ) {
        $data = self::getMemberData( 'localizationId', $id );

        if ( $data == null ) return self::$member->localizationId;

        return $data;
    }

    public static function signedIn() {
        return self::$member->signedIn;
    }

    public static function themeId() {
        return self::$member->themeId;
    }

    public static function memberId() {
        return self::$member->id;
    }

    public static function imagesetUrl() {
        return self::$member->imagesetUrl;
    }

    public static function timeZone() {
        return self::$member->timeZone;
    }

    public static function dateFormat() {
        return self::$member->dateFormat;
    }

    public static function timeFormat() {
        return self::$member->timeFormat;
    }

    public static function dateTimeFormat() {
        return self::$member->dateTimeFormat;
    }

    public static function timeAgo() {
        return self::$member->timeAgo;
    }

    public static function topicsPerPage() {
        return self::$member->perPage->topics;
    }

    public static function postsPerPage() {
        return self::$member->perPage->posts;
    }

    public static function resultsPerPage() {
        return self::$member->perPage->results;
    }

    public static function membersPerPage() {
        return self::$member->perPage->members;
    }

    public static function messagesPerPage() {
        return self::$member->perPage->messages;
    }

    public static function themeUrl() {
        return self::$member->themeUrl;
    }

    public static function themePath() {
        return self::$member->themePath;
    }

    public static function messagesEnabled() {
        return self::$member->messagesEnabled;
    }

    public static function notificationsEnabled() {
        return self::$member->notificationsEnabled;
    }

    public static function signatureEnabled() {
        return self::$member->signatureEnabled;
    }

    public static function showSignatures() {
        return self::$member->showSignatures;
    }
} 