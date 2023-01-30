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
        self::$member->classes->photoThumbnailUserBar = null;
        self::$member->classes->photoThumbnailNotifyItem = null;
        self::$member->messagesEnabled = null;
        self::$member->notificationsEnabled = null;
        self::$member->notificationsPeriodicSetting = null;
        self::$member->attachments = [];
        self::$member->signatureEnabled = false;
        self::$member->showSignatures = true;
        self::$member->uniqueHash = null;
        self::$member->filters = new \stdClass();
        self::$member->filters->topics = new \stdClass();
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
                    self::$member->uniqueHash = $member->uniqueHash;
                    self::$member->filters->topics->started = $member->topicFilterStarted;
                    self::$member->filters->topics->sortBy = $member->topicFilterSortBy;
                    self::$member->filters->topics->sortOrder = $member->topicFilterSortOrder;
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
            self::$member->filters->topics->started = self::settings()->topicFilterDefaultStarted;
            self::$member->filters->topics->sortBy = self::settings()->topicFilterDefaultSortBy;
            self::$member->filters->topics->sortOrder = self::settings()->topicFilterDefaultSortOrder;
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
        self::$member->classes->photoThumbnailUserBar = $arr['photoThumbnailUserBarClass'];
        self::$member->classes->photoThumbnailNotifyItem = $arr['photoThumbnailNotifyItem'];
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

    public static function getAttachments() {
        return null;
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

    public static function getLink( $id = null, $includeHovercard = true, $title = null, $seperator = null, $age = null ) {
        if ( $id == null ) $id = self::$member->id;

        $displayName = self::displayName( $id );
        $groupId = self::primaryGroup( $id );

        if ( $id == 0 ) return self::localization()->getWords( 'global', 'guest' );

        $data = self::cache()->getData( 'groups' );

        foreach ( $data as $group ) {
            if ( $group->groupId == $groupId ) {
                $color = $group->color;
                $important = $group->important == 1 ? true : false;
                break;
            }
        }

        $randomHash = self::utilities()->randomHash();

        return self::output()->getPartial(
            'Global',
            'Member',
            'Link',
            [
                'url'       => self::seo()->seoUrl( 'profiles', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $id, $displayName ) ] ),
                'name'      => $displayName,
                'title'     => $title != null ? $title : self::localization()->quickReplace( 'global', 'viewMembersProfile', 'DisplayName', $displayName ),
                'seperator' => $seperator == null ? '' : $seperator,
                'age'       => $age != null ? self::output()->getPartial( 'Global', 'Member', 'Age', [ 'age' => $age ] ) : '',
                'color'     => $color,
                'important' => $important ? self::output()->getPartial( 'Global', 'Style', 'Bold' ) : '',
                'hovercard' => $includeHovercard ? self::output()->getPartial( 'Global', 'Member', 'HovercardMarkup', [ 'id' => $id, 'hash' => self::uniqueHash( $id ), 'randomHash' => $randomHash, 'linkId' => 'member-link-' . $randomHash . '-' . self::uniqueHash( $id ) ] ) : ''
            ]
        );
    }

    public static function getPrimaryGroupLink( $id = null, $hash = null ) {
        if ( $id == null ) $id = self::$member->id;
        $data = self::cache()->getData( 'groups' );
        $found = false;

        foreach ( $data as $group ) {
            if ( $group->groupId == self::primaryGroup( $id ) ) {
                $found = true;
                $color = $group->color;
                $title = $group->title;
                $important = $group->important == 1 ? true : false;
                $groupId = $group->groupId;
                break;
            }
        }

        if ( ! $found ) return self::localization()->getWords( 'global', 'unknown' );

        return self::output()->getPartial(
            'Global',
            'Member',
            'GroupLink',
            [
                'color'       => $color,
                'title'       => $title,
                'url'         => self::seo()->seoUrl( 'groups', 'view', [ 'id' => $groupId ] ),
                'important'   => $important ? self::output()->getPartial( 'Global', 'Style', 'GroupImportant' ) : self::output()->getPartial( 'Global', 'Style', 'GroupNormal' ),
                'hash'        => $hash != null ? 'groupl-' . self::uniqueHash( $id ) : ''
            ]
        );
    }

    public static function hasFeaturePermissions( $feature ) {
        return true; // TEMP

        // $data = self::cache()->getData( 'feature_permissions' );
        // $found = false;

        // foreach ( $data as $permission ) {
        //     if ( \strtolower( $permission->systemIdentifier ) == \strtolower( $feature ) ) {
        //         $found = true;
        //         $enabled = $permission->enabled == 1 ? true : false;
        //         $allowedUsers = \strlen( $permission->allowedUsers ) > 4 ? \unserialize( $permission->allowedUsers ) : null;
        //         $allowedGroups = \strlen( $permission->allowedGroups ) > 4 ? \unserialize( $permission->allowedGroups ) : null;
        //         break;
        //     }
        // }

        // if ( ! $found || ! $enabled ) return false;
        // if ( self::primaryGroup() == self::settings()->administratorGroupId ) return true;

        // if ( $allowedGroups != null && \count( $allowedGroups ) > 0 ) {
        //     foreach ( $allowedGroups as $groupId ) {
        //         if ( self::primaryGroup() == $groupId ) {
        //             return true;
        //         }

        //         $secondaryGroups = self::secondaryGroups();

        //         if ( $secondaryGroups != null && \count( $secondaryGroups ) > 0 ) {
        //             foreach ( $secondaryGroups as $id ) {
        //                 if ( $id == $groupId ) {
        //                     return true;
        //                 }
        //             }
        //         }
        //     }
        // }

        // if ( $allowedUsers != null && \count( $allowedUsers ) > 0 ) {
        //     foreach ( $allowedUsers as $id ) {
        //         if ( $id == self::$member->id ) {
        //             return true;
        //         }
        //     }
        // }

        // return true; // TEMP SET TO TRUE !!!! RESTORE TO FALSE ONCE TESTING IS COMPLETE
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

    public static function haveProfilePhoto( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $retVal = new \stdClass();
        $data = self::getMemberDataCollection( [ 'photoType', 'photoId' ], $id );

        if ( \strlen( $data->photoType ) > 0 && $data->photoId != 0 ) {
            $retVal->present = true;
        } else {
            $retVal->present = false;
        }

        $retVal->type = $data->photoType;
        $retVal->id = $data->photoId;

        return $retVal;
    }

    public static function profilePhoto( $id = 0, $thumbnail = false, $userBar = false, $customClass = null, $hovercard = true, $title = null, $link = true ) {
        if ( $id != 0 && $link ) {
            $randomHash = self::utilities()->randomHash();

            $linkBegin = self::output()->getPartial(
                'Global',
                'Link',
                'ProfilePhotoBegin',
                [
                    'url'       => self::seo()->seoUrl( 'profiles', 'index', [ 'id' => self::url()->getUrlWithIdAndTitle( $id, self::displayName( $id ) ) ] ),
                    'title'     => $title == null ? self::localization()->quickReplace( 'global', 'viewMembersProfile', 'DisplayName', self::displayName( $id ) ) : $title,
                    'hash'      => 'hc-profilephotol-' . self::uniqueHash( $id ),
                    'hovercard' => $hovercard ? self::output()->getPartial( 'Global', 'Member', 'HovercardMarkup', [ 'id' => $id, 'hash' => self::uniqueHash( $id ), 'randomHash' => $randomHash, 'linkId' => 'hc-profilephotol-' . self::uniqueHash( $id ) ] ) : ''
                ]
            );

            $linkEnd = self::output()->getPartial( 'Global', 'Link', 'ProfilePhotoEnd' );
        } else {
            $linkBegin = '';
            $linkEnd = '';
        }

        if ( $id == 0 ) {
            $class = $thumbnail ? self::$member->classes->noPhotoThumbnail : self::$member->classes->noPhoto;
            if ( $userBar ) $class = self::$member->classes->photoThumbnailUserBar;
            if ( $customClass ) $class = $customClass;

            return self::output()->getPartial(
                'Global',
                'Photo',
                'NoPhoto',
                [
                    'class'           => $class,
                    'letter'          => 'G',
                    'backgroundColor' => self::settings()->noPhotoColors['G']['background'],
                    'textColor'       => self::settings()->noPhotoColors['G']['text'],
                    'linkBegin'       => $linkBegin,
                    'linkEnd'         => $linkEnd,
                    'hash'            => 'hc-nophoto-' . self::uniqueHash( $id )
                ]
            );
        } else {
            $memberData = self::getMemberDataCollection( [ 'displayName', 'photoType', 'photoId' ], $id );

            if ( $memberData == null ) {
                $class = $thumbnail ? self::$member->classes->noPhotoThumbnail : self::$member->classes->noPhoto;
                if ( $userBar ) $class = self::$member->classes->photoThumbnailUserBar;
                if ( $customClass ) $class = $customClass;

                return self::output()->getPartial(
                    'Global',
                    'Photo',
                    'NoPhoto',
                    [
                        'class'           => $class,
                        'letter'          => 'G',
                        'backgroundColor' => self::settings()->noPhotoColors['G']['background'],
                        'textColor'       => self::settings()->noPhotoColors['G']['text'],
                        'linkBegin'       => $linkBegin,
                        'linkEnd'         => $linkEnd,
                        'hash'            => 'hc-nophoto-' . self::uniqueHash( $id )
                    ]
                );
            }

            $firstChar = \strtoupper( \substr( $memberData->displayName, 0, 1 ) );

            if ( $memberData->photoId == 0 ) {
                $class = $thumbnail ? self::$member->classes->noPhotoThumbnail : self::$member->classes->noPhoto;
                if ( $userBar ) $class = self::$member->classes->photoThumbnailUserbar;
                if ( $customClass ) $class = $customClass;

                return self::output()->getPartial(
                    'Global',
                    'Photo',
                    'NoPhoto',
                    [
                        'class'           => $class,
                        'letter'          => $firstChar,
                        'backgroundColor' => self::settings()->noPhotoColors['G']['background'],
                        'textColor'       => self::settings()->noPhotoColors['G']['text'],
                        'linkBegin'       => $linkBegin,
                        'linkEnd'         => $linkEnd,
                        'hash'            => 'hc-nophoto-' . self::uniqueHash( $id )
                    ]
                );
            }

            switch ( $memberData->photoType ) {
                case 'uploaded':
                    $photo = '';
                    $photos = self::cache()->getData( 'members_photos' );

                    foreach ( $photos as $obj ) {
                        if ( $obj->photoId == $memberData->photoId ) $photo = $obj->fileName;
                    }

                    $photoUrl = self::vars()->baseUrl . '/' . self::settings()->uploadDir . '/' . self::settings()->profilePhotosDir . '/' . $id . '/' . $photo;
                    
                    if ( ! @file_get_contents( $photoUrl ) ) {
                        $class = $thumbnail ? self::$member->classes->noPhotoThumbnail : self::$member->classes->noPhoto;
                        if ( $userBar ) $class = self::$member->classes->photoThumbnailUserBar;
                        if ( $customClass ) $class = $customClass;

                        return self::output()->getPartial(
                            'Global',
                            'Photo',
                            'NoPhoto',
                            [
                                'class'           => $class,
                                'letter'          => $firstChar,
                                'backgroundColor' => self::settings()->noPhotoColors['G']['background'],
                                'textColor'       => self::settings()->noPhotoColors['G']['text'],
                                'linkBegin'       => $linkBegin,
                                'linkEnd'         => $linkEnd,
                                'hash'            => 'hc-nophoto-' . self::uniqueHash( $id )
                            ]
                        );
                    }

                    $class = $thumbnail ? self::$member->classes->photoThumbnail : self::$member->classes->photo;
                    if ( $userBar ) $class = self::$member->classes->photoThumbnailUserBar;
                    if ( $customClass ) $class = $customClass;

                    return self::output()->getPartial(
                        'Global',
                        'Photo',
                        'Photo',
                        [
                            'image'     => $photoUrl,
                            'class'     => $class,
                            'linkBegin' => $linkBegin,
                            'linkEnd'   => $linkEnd,
                            'hash'      => 'hc-photo-' . self::uniqueHash( $id )
                        ]
                    );
                    break;

                case 'gallery':
                    $gallery = self::cache()->getData( 'photo_gallery' );
                    $found = false;

                    foreach ( $gallery as $obj ) {
                        if ( $obj->galleryId == $memberData->photoId ) {
                            $found = true;
                            $photo = $obj->fileName;
                        }
                    }

                    $photoUrl = self::vars()->baseUrl . '/public/gallery/' . $photo;

                    if ( ! $found || ! \file_exists( $photoUrl ) ) {
                        $class = $thumbnail ? self::$member->classes->noPhotoThumbnail : self::$member->classes->noPhoto;
                        if ( $userBar ) $class = self::$member->classes->photoThumbnailUserBar;
                        if ( $customClass ) $class = $customClass;

                        return self::output()->getPartial(
                            'Global',
                            'Photo',
                            'NoPhoto',
                            [
                                'class'           => $class,
                                'letter'          => $firstChar,
                                'backgroundColor' => self::settings()->noPhotoColors['G']['background'],
                                'textColor'       => self::settings()->noPhotoColors['G']['text'],
                                'linkBegin'       => $linkBegin,
                                'linkEnd'         => $linkEnd,
                                'hash'            => 'hc-nophoto-' . self::uniqueHash( $id )
                            ]
                        );
                    }

                    $class = $thumbnail ? self::$member->classes->photoThumbnail : self::$member->classes->photo;
                    if ( $userBar ) $class = self::$member->classes->photoThumbnailUserBar;
                    if ( $customClass ) $class = $customClass;

                    return self::output()->getPartial(
                        'Global',
                        'Photo',
                        'Photo',
                        [
                            'image'     => $photoUrl,
                            'class'     => $class,
                            'linkBegin' => $linkBegin,
                            'linkEnd'   => $linkEnd,
                            'hash'      => 'hc-photo-' . self::uniqueHash( $id )
                        ]
                    );
                    break;
            }
        }
    }

    public static function hasCoverPhoto( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'coverPhoto', $id );

        if ( $data == NULL ) {
            return false;
        }

        return true;
    } 

    public static function getCoverPhoto( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'coverPhoto', $id );

        if ( $data == NULL ) {
            return '';
        }

        $d = self::cache()->getData( 'members_photos' );
        $found = false;

        foreach ( $d as $item ) {
            if ( $item->photoId == $data ) {
                $found = true;
                $filename = $item->fileName;
                break;
            }
        }

        if ( ! $found ) return '';

        $photo = self::vars()->baseUrl . '/' . self::settings()->uploadDir . '/' . self::settings()->profilePhotosDir . '/' . $id . '/cover/' . $filename;
        
        return $photo;
    }

    public static function calculateTotalPosts( $id ) {
        $data = self::cache()->massGetData( [ 'posts' => 'posts', 'topics' => 'topics', 'forums' => 'forums' ] );
        $total = 0;

        foreach ( $data->posts as $post ) {
            if ( $post->authorId == $id ) {
                foreach ( $data->topics as $topic ) {
                    if ( $topic->topicId == $post->topicId ) {
                        foreach ( $data->forums as $forum ) {
                            if ( $forum->forumId == $topic->forumId ) {
                                if ( $forum->incrementPostCount == 1 ) $total++;
                            }
                        }
                    }
                }
            }
        }

        return $total;
    }

    public static function totalPosts( $id = null ) {
        $totalPosts = 0;

        if ( $id == null ) {
            if ( self::$member->signedIn ) {
                $totalPosts = self::calculateTotalPosts( self::$member->id );
            } else {
                return 0;
            }
        } else {
            $data = self::cache()->getData( 'members' );
            $found = false;

            foreach ( $data as $member ) {
                if ( $member->memberId == $id ) {
                    $found = true;
                    break;
                }
            }

            if ( ! $found ) return 0;

            $totalPosts = self::calculateTotalPosts( $id );
        }

        return $totalPosts;
    }

    public static function getReputation( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::cache()->getData( 'members_reputation' );
        $reputation = 0;

        foreach ( $data as $rep ) {
            if ( $rep->memberId == $id ) $reputation++;
        }

        return self::math()->formatNumber( $reputation );
    }

    public static function getLocationLink( $id = null, $hash = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'location', $id );

        if ( $data == null ) return self::localization()->getWords( 'global', 'unknown' );

        return self::output()->getPartial(
            'Global',
            'Member',
            'LocationLink',
            [
                'url'   => \sprintf( 'https://maps.google.com/maps?q=%s', \urlencode( $data ) ),
                'title' => $data,
                'hash'  => $hash != null ? $hash : ''
            ]
        );
    }

    public static function getGender( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'gender', $id );
        $genderList = self::settings()->genderList;

        if ( \count( $genderList ) > 0 && \is_array( $genderList ) ) {
            foreach ( $genderList as $k => $v ) {
                if ( \strtolower( $k ) == \strtolower( $data ) ) {
                    return self::output()->getPartial(
                        'Global',
                        'Member',
                        'Gender',
                        [
                            'gender' => $v,
                            'icon'   => self::output()->getPartial( 'Global', 'Gender', $k )
                        ]
                    );
                }
            }
        }
    }

    public static function showAbout( $type, $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        switch ( $type ) {
            case 'posts':
                $field = 'postsShowTotalPosts';
                $parent = null;
                break;

            case 'age':
                $field = 'postsShowAge';
                $parent = [ 'dobMonth', 'dobDay', 'dobYear' ];
                break;

            case 'location':
                $field = 'postsShowLocation';
                $parent = [ 'location' ];
                break;

            case 'gender':
                $field = 'postsShowGender';
                $parent = [ 'gender' ];
                break;

            case 'joined':
                $field = 'postsShowJoined';
                $parent = null;
                break;
        }

        $data = self::getMemberData( $field, $id );
        $dataExists = false;

        if ( $parent != null ) {
            $list = [];

            foreach ( $parent as $item ) {
                $d = self::getMemberData( $item, $id );

                if ( \strlen( $d ) > 0 || $d != 0 ) {
                    $list[] = true;
                } else {
                    $list[] = false;
                }
            }

            $exists = true;

            foreach ( $list as $item ) {
                if ( ! $item ) $exists = false;
            }

            $dataExists = true;
        } else {
            $dataExists = true;
        }

        if ( $data == null || ! $dataExists ) return false;
        
        if ( $data == 1 ) {
            return true;
        } else {
            return false;
        }
    }

    public static function canSendMemberMessage( $id ) {
        $canSendMessage = false;
        $data = self::getMemberData( 'messagesEnabled', $id );

        if ( self::hasFeaturePermissions( \BanditBB\Types\Features::MESSAGES ) && $data == 1 && self::$member->messagesEnabled ) {
            $canSendMessage = true;
        }

        return $canSendMessage;
    }

    public static function isAFriend( $id ) {
        $isFriend = false;
        $data = self::cache()->getData( 'members_friends' );

        foreach ( $data as $friend ) {
            if ( $friend->memberId == self::$member->id && $friend->friendMemberId == $id ) {
                $isFriend = true;
            }
        }

        return $isFriend;
    }

    public static function uniqueHash( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'uniqueHash', $id );

        if ( $data == null ) return '';

        return $data;
    }

    public static function validateCredentials( $username, $password ) {
        $data = self::cache()->getData( 'members' );
        $found = false;
        $retVal = new \stdClass();

        foreach ( $data as $member ) {
            switch ( self::settings()->allowSignInWithUsername ) {
                case true:
                    if ( $member->username == $username || $member->emailAddress == $username ) {
                        $found = true;
                        $hashedPassword = $member->password;
                        $memberId = $member->memberId;
                        break;
                    }
                    break;

                case false:
                    if ( $member->emailAddress == $username ) {
                        $found = true;
                        $hashedPassword = $member->password;
                        $memberId = $member->memberId;
                        break;
                    }
                    break;
            }
        }

        if ( ! $found ) {
            $retVal->success = false;
            $retVal->reason = 'signInFailed';
            $retVal->ajaxOutput = \json_encode( [ 'status' => false, 'data' => self::localization()->getWords( 'ajaxerrors', 'signInFailed' . ( self::settings()->allowSignInWithUsername ? 'Both' : 'Single' ) ) ] );
            $retVal->code = 1;
            return $retVal;
        }

        if ( ! \password_verify( $password, $hashedPassword ) ) {
            $lockedInfo = self::authentication()->invalidSignInAttempt( $memberId );

            if ( $lockedInfo->lockedOut ) {
                $retVal->success = false;
                $retVal->reason = 'lockedOut';
                $retVal->ajaxOutput = \json_encode( [ 'status' => false, 'data' => self::localization()->quickReplace( 'ajaxerrors', 'lockedOutWithTimeLeft', 'Total', \round( $lockedInfo->timeLeft, $lockedInfo->timeLeft > 1 ? 0 : 2 ) ) ] );
                $retVal->code = 2;
                return $retVal;
            } else {
                if ( $lockedInfo->enabled ) {
                    $retVal->success = false;
                    $retVal->reason = 'signInFailed';
                    $retVal->ajaxOutput = \json_encode( [ 'status' => false, 'data' => self::localization()->quickMultiWordReplace( 'ajaxerrors', 'failedSignInAttemptsLeft' . ( self::settings()->allowSignInWithUsername ? 'Both' : 'Single' ) , [
                        'Attempts' => self::settings()->authenticationSignInAttemptsMax - $lockedInfo->attempts,
                        'Total'    => self::settings()->authenticationSignInAttemptsMax
                    ])]);
                    $retVal->code = 3;
                    return $retVal;
                } else {
                    $retVal->success = false;
                    $retVal->reason = 'signInFailed';
                    $retVal->ajaxOutput = \json_encode( [ 'status' => false, 'data' => self::localization()->getWords( 'ajaxerrors', 'signInFailed' . ( self::settings()->allowSignInWithUsername ? 'Both' : 'Single' ) ) ] );
                    $retVal->code = 1;
                    return $retVal;
                }
            }
        }

        $lockedInfo = self::authentication()->isLockedOut( $memberId );

        if ( $lockedInfo->lockedOut ) {
            $retVal->success = false;
            $retVal->reason = 'lockedOut';
            $retVal->ajaxOutput = \json_encode( [ 'status' => false, 'data' => self::localization()->quickReplace( 'ajaxerrors', 'lockedOutWithTimeLeft', 'Total', \round( $lockedInfo->timeLeft, $lockedInfo->timeLeft > 1 ? 0 : 2 ) ) ] );
            $retVal->code = 2;
            return $retVal;
        } else {
            $retVal->success = true;
            $retVal->reason = null;
            $retVal->ajaxOutput = \json_encode( [ 'status' => true ] );
            $retVal->code = 0;
        }

        return $retVal;
    }

    public static function notificationsData( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $retVal = new \stdClass();
        $data = self::cache()->getData( 'members_notifications' );
        $unread = false;
        $total = 0;

        foreach ( $data as $notification ) {
            if ( $notification->memberId == $id && $notification->isRead == 0 ) {
                $total++;
                $unread = true;
            }
        }

        $retVal->unread = $unread;
        $retVal->totalUnread = $total;

        return $retVal;
    }

    public static function messagesData( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $retVal = new \stdClass();
        $data = self::cache()->massGetData( [ 'map' => 'messages_map', 'posts' => 'messages_posts' ] );
        $total = 0;
        $unread = false;

        foreach ( $data->map as $map ) {
            if ( $map->memberId == $id ) {
                $timestamps = [];

                foreach ( $data->posts as $post ) {
                    if ( $post->topicId == $map->topicId ) {
                        $timestamps[] = $post->postedTimestamp;
                    }
                }

                foreach ( $timestamps as $timestamp ) {
                    if ( $timestamp > $map->lastReadTimestamp ) {
                        $total++;
                        $unread = true;
                    }
                }
            }
        }

        $retVal->unread = $unread;
        $retVal->totalUnread = $total;

        return $retVal;
    }

    public static function notificationsListForDropDown( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::cache()->getData( 'members_notifications' );
        $unread = false;
        $retVal = '';

        foreach ( $data as $notification ) {
            if ( $notification->memberId == $id && $notification->isRead == 0 ) {
                $unread = true;
                $notificationData = \json_decode( $notification->data );

                switch ( $notification->type ) {
                    case 'newpost':
                        $topics = self::cache()->getData( 'topics' );

                        foreach ( $topics as $topic ) {
                            if ( $topic->topicId == $notificationData->topicId ) {
                                $topicTitle = $topic->title;
                            }
                        }

                        $retVal .= self::output()->getPartial(
                            'Global',
                            'Member',
                            'NotificationItemNewPost',
                            [
                                'title' => self::localization()->quickReplace(
                                    'global',
                                    'notificationNewPostTitle',
                                    'TopicName',
                                    self::output()->getPartial(
                                        'Global',
                                        'Link',
                                        'Generic',
                                        [
                                            'tooltip'   => self::math()->formatNumber( self::topics()->getTotalViewingTopic( $notificationData->topicId ) ),
                                            'seperator' => '',
                                            'name'      => $topicTitle,
                                            'url'       => self::seo()->seoUrl( 'forwarder', 'notification', [ 'id' => $notification->notificationId ] )
                                        ]
                                    )
                                ),
                                'timestamp' => self::dateTime()->parse( $notification->timestamp, [ 'timeAgo' => true ] ),
                                'by'        => self::getLink( $notificationData->authorId )
                            ]
                        );
                        break;
                }
            }
        }

        if ( $unread ) {
            return $retVal;
        } else {
            return null;
        }
    }

    public static function messagesListForDropDown( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::cache()->massGetData( [ 'map' => 'messages_map', 'posts' => 'messages_posts', 'topics' => 'messages_topics' ] );
        $unread = false;

        foreach ( $data->map as $map ) {
            if ( $map->memberId == $id ) {
                $timestamps = [];

                foreach ( $data->posts as $post ) {
                    if ( $post->topicId == $map->topicId ) {
                        $timestamps[$post->postId] = $post->postedTimestamp;
                    }
                }

                $haveUnread = false;

                foreach ( $timestamps as $postId => $postedTimestamp ) {
                    if ( $postedTimestamp > $map->lastReadTimestamp ) {
                        $haveUnread = true;
                        $unread = true;
                    }
                }

                if ( $haveUnread ) {
                    foreach ( $data->topics as $topic ) {
                        if ( $topic->topicId == $map->topicId ) {
                            $title = $topic->title;
                        }
                    }

                    \arsort( $timestamps );

                    foreach ( $data->posts as $post ) {
                        if ( $post->postId == \key( $timestamps ) ) {
                            $retVal .= self::output()->getPartial(
                                'Global',
                                'Member',
                                'MessagesItem',
                                [
                                    'from'      => self::getLink( $post->postedMemberId ),
                                    'title'     => $title,
                                    'timestamp' => self::dateTime()->parse( $post->postedTimestamp, [ 'timeAgo' => true ] ),
                                    'url'       => self::seo()->seoUrl( 'forwarder', 'message', [ 'id' => $post->postId ] ),
                                    'photo'     => self::profilePhoto( $post->postedMemberId, true, false, self::$member->classes->photoThumbnailNotifyItem )
                                ]
                            );
                        }
                    }
                }
            }
        }

        if ( $unread ) {
            return $retVal;
        } else {
            return null;
        }
    }

    public static function isModerator( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::cache()->getData( 'groups' );
        $isModerator = false;

        foreach ( $data as $group ) {
            if ( $group->groupId == self::primaryGroup( $id ) ) {
                if ( $group->isModerator == 1 ) $isModerator = true;
            }
        }

        if ( ! $isModerator ) {
            $secondaryGroups = self::secondaryGroups( $id );

            if ( $secondaryGroups != null ) {
                if ( \count( $secondaryGroups ) > 0 && \is_array( $secondaryGroups ) ) {
                    foreach ($secondaryGroups as $groupId ) {
                        foreach ($data as $group ) {
                            if ( $group->groupId == $groupId ) {
                                if ( $group->isModerator == 1 ) $isModerator = true;
                            }
                        }
                    }
                }
            }
        }

        return $isModerator;
    }

    public static function adminCPAccess( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::cache()->getData( 'groups' );
        $adminCPAccess = false;

        foreach ( $data as $group ) {
            if ( $group->groupId == self::primaryGroup( $id ) ) {
                if ( $group->adminCPAccess == 1 ) $adminCPAccess = true;
            }
        }

        if ( ! $adminCPAccess ) {
            $secondaryGroups = self::secondaryGroups( $id );

            if ( $secondaryGroups != null ) {
                if ( \count( $secondaryGroups ) > 0 && \is_array( $secondaryGroups ) ) {
                    foreach ( $secondaryGroups as $groupId ) {
                        foreach ( $data as $group ) {
                            if ( $group->groupId == $groupId ) {
                                if ( $group->adminCPAccess == 1 ) $adminCPAccess = true;
                            }
                        }
                    }
                }
            }
        }

        return $adminCPAccess;
    }

    public static function isInGroup( $groupId, $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        if ( $id == 0 ) {
            if ( $groupId == self::settings()->guestGroupId ) {
                return true;
            }
        }

        if ( self::primaryGroup( $id ) == $groupId ) {
            return true;
        }

        $secondaryGroups = self::secondaryGroups( $id );

        if ( \is_array( $secondaryGroups ) && \count( $secondaryGroups ) > 0 ) {
            foreach ( $secondaryGroups as $group ) {
                if ( $group == $groupId ) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function haveForumPermissions( $forumId, $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::cache()->getData( 'forum_permissions' );
        $permissions = new \stdClass();

        // TO-DO: SET TO TRUE FOR DEVELOPMENT ONLY!!!
        $permissions->viewForum = true;
        $permissions->readTopics = true;
        $permissions->postTopics = true;
        $permissions->postReply = true;
        $permissions->uploadAttachments = true;
        $permissions->downloadAttachments = true;
        $permissions->createPoll = true;
        $permissions->castPoll = true;
        
        foreach ( $data as $permission ) {
            if ( $permission->forumId == $forumId ) {
                $viewForum = \strlen( $permission->viewForumList ) > 4 ? \unserialize( $permission->viewForumList ) : null;
                $readTopics = \strlen( $permission->readTopicsList ) > 4 ? \unserialize( $permission->readTopicsList ) : null;
                $postTopics = \strlen( $permission->postTopicsList ) > 4 ? \unserialize( $permission->postTopicsList ) : null;
                $postReply = \strlen( $permission->postReplyList ) > 4 ? \unserialize( $permission->postReplyList ) : null;
                $downloadAttachments = \strlen( $permission->downloadAttachmentsList ) > 4 ? \unserialize( $permission->downloadAttachmentsList ) : null;
                $uploadAttachments = \strlen( $permission->uploadAttachmentsList ) > 4 ? \unserialize( $permission->uploadAttachmentsList ) : null;
                $createPoll = \strlen( $permission->createPollList ) > 4 ? \unserialize( $permission->createPollList ) : null;
                $castPoll = \strlen( $permission->castPollList ) > 4 ? \unserialize( $permission->castPollList ) : null;
                break;
            }
        }

        if ( $viewForum != null && \count( $viewForum ) > 0 ) {
            foreach ( $viewForum as $groupId ) {
                if ( self::isInGroup( $groupId, $id ) ) $permissions->viewForum = true;
            }
        }

        if ( $readTopics != null && \count( $readTopics ) > 0 ) {
            foreach ( $readTopics as $groupId ) {
                if ( self::isInGroup( $groupId, $id ) ) $permissions->readTopics = true;
            }
        }

        if ( $postTopics != null && \count( $postTopics ) > 0 ) {
            foreach ( $postTopics as $groupId ) {
                if ( self::isInGroup( $groupId, $id ) ) $permissions->postTopics = true;
            }
        }

        if ( $postReply != null && \count( $postReply ) > 0 ) {
            foreach ( $postReply as $groupId ) {
                if ( self::isInGroup( $groupId, $id ) ) $permissions->postReply = true;
            }
        }

        if ( $downloadAttachments != null && \count( $downloadAttachments ) > 0 ) {
            foreach ( $downloadAttachments as $groupId ) {
                if ( self::isInGroup( $groupId, $id ) ) $permissions->downloadAttachments = true;
            }
        }

        if ( $uploadAttachments != null && \count( $uploadAttachments ) > 0 ) {
            foreach ( $uploadAttachments as $groupId ) {
                if ( self::isInGroup( $groupId, $id ) ) $permissions->uploadAttachments = true;
            }
        }

        if ( $createPoll != null && \count( $createPoll ) > 0 ) {
            foreach ( $createPoll as $groupId ) {
                if ( self::isInGroup( $groupId, $id ) ) $permissions->createPoll = true;
            }
        }

        if ( $castPoll != null && \count( $castPoll ) > 0 ) {
            foreach ( $castPoll as $groupId ) {
                if ( self::isInGroup( $groupId, $id ) ) $permissions->castPoll = true;
            }
        }

        return $permissions;
    }

    public static function haveForumPermission( $forumId, $permission, $id = null ) {
        return self::haveForumPermissions( $forumId, $id )->$permission;
    }

    public static function getSignature( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'signature', $id );

        if ( $data == null ) return null;

        return \stripslashes( $data );
    }

    public static function getSignatureBox( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::getMemberData( 'signatureEnabled', $id );

        if ( $data == null || $data == 0 ) {
            return '';
        }

        $signature = self::getSignature( $id );

        if ( \strlen( $signature ) < 1 ) return '';

        $signature = \nl2br( $signature );

        if ( self::settings()->signaturesAllowBBCode ) {
            $signature = self::textParsing()->bbTagReplacement( $signature, false, true );
        }

        if ( self::settings()->signaturesWordCensoring ) {
            $signature = self::textParsing()->wordCensoring( $signature, false, true );
        }

        return self::output()->getPartial( 'Global', 'Member', 'SignatureBox', [ 'signature' => $signature ] );
    }

    public static function getPostData( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::cache()->getData( 'members' );
        $found = false;
        $retVal = new \stdClass();

        foreach ( $data as $member ) {
            if ( $member->memberId == $id ) {
                $found = true;
                $showTotalPosts = $member->postsShowTotalPosts == 1 ? true : false;
                $showAge = $member->postsShowAge == 1 ? true : false;
                $showLocation = $member->postsShowLocation == 1 ? true : false;
                $showGender = $member->postsShowGender == 1 ? true : false;
                $showJoined = $member->postsShowJoined == 1 ? true : false;
                $location = $member->location;
                $gender = $member->gender;
                $dobDay = $member->dobDay;
                $dobMonth = $member->dobMonth;
                $dobYear = $member->dobYear;
                $displayAge = $member->displayAge == 1 ? true : false;
            }
        }

        if ( ! $found ) {
            $retVal->guest = true;
            $retVal->data = null;
            return $retVal;
        }

        $postData = '';

        if ( $showTotalPosts ) {
            $postData .= self::output()->getPartial(
                'Global',
                'Member',
                'PostItem',
                [
                    'data' => self::localization()->quickMultiWordReplace( 'global', 'authorTotalPosts', [ 'Icon' => self::output()->getPartial( 'Global', 'Icon', 'TotalPosts' ), 'Total' => self::math()->formatNumber( self::totalPosts( $id ) ) ] )
                ]
            );
        }

        if ( $showJoined ) {
            $postData .= self::output()->getPartial(
                'Global',
                'Member',
                'PostItem',
                [
                    'data' => self::localization()->quickMultiWordReplace( 'global', 'authorJoined', [ 'Icon' => self::output()->getPartial( 'Global', 'Icon', 'Joined' ), 'Joined' => self::joined( $id ) ] )
                ]
            );
        }

        if ( $showLocation && \strlen( $location ) > 0 ) {
            $postData .= self::output()->getPartial(
                'Global',
                'Member',
                'PostItem',
                [
                    'data' => self::localization()->quickMultiWordReplace( 'global', 'authorLocation', [ 'Icon' => self::output()->getPartial( 'Global', 'Icon', 'Location' ), 'Location' => self::output()->getPartial( 'Global', 'PostData', 'Location', [ 'location' => $location, 'locationCleaned' => \urlencode( $location ) ] ) ] )
                ]
            );
        }

        if ( $showGender && \strlen( $gender ) > 0 ) {
            $postData .= self::output()->getPartial(
                'Global',
                'Member',
                'PostItem',
                [
                    'data' => self::localization()->quickMultiWordReplace( 'global', 'authorGender', [ 'Icon' => self::output()->getPartial( 'Global', 'Icon', 'Gender' ), 'Gender' => self::getGender( $id ) ] )
                ]
            );
        }

        if ( $showAge && $dobMonth != 0 && $dobDay != 0 && $dobYear != 0 && $displayAge ) {
            $postData .= self::output()->getPartial(
                'Global',
                'Member',
                'PostItem',
                [
                    'data' => self::localization()->quickMultiWordReplace( 'global', 'authorAge', [ 'Icon' => self::output()->getPartial( 'Global', 'Icon', 'Age' ), 'Age' => self::age( $id ) ] )
                ]
            );
        }

        if ( self::hasFeaturePermissions( \BanditBB\Types\Features::REPUTATION ) ) {
            $postData .= self::output()->getPartial(
                'Global',
                'Member',
                'PostItem',
                [
                    'data' => self::localization()->quickMultiWordReplace( 'global', 'authorReputation', [ 'Icon' => self::output()->getPartial( 'Global', 'Icon', 'Reputation' ), 'Reputation' => self::getReputation( $id ) ] )
                ]
            );
        }

        $retVal->guest = false;
        $retVal->data = $postData;

        return $retVal;
    }

    public static function getTotalAttachmentSpaceUsed( $id = null ) {
        if ( $id == null ) $id = self::$member->id;

        $data = self::cache()->getData( 'members_attachments' );
        $spaceUsed = 0;

        if ( \count( self::$member->attachments ) > 0 ) {
            foreach ( self::$member->attachments as $attachmentId ) {
                foreach ( $data as $attachment ) {
                    if ( $attachment->attachmentId == $attachmentId ) {
                        $spaceUsed = ( $attachment->fileSize + $spaceUsed );
                    }
                }
            }
        }

        return $spaceUsed;
    }

    public static function getFilter( $filter ) {
        return self::$member->filters->$filter;
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