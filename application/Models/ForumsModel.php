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

class ForumsModel extends \BanditBB\Models\BaseModel {

    private static $vars = [];

    public function generateIndex() {
        self::utilities()->addBreadcrumb( self::localization()->getWords( 'forums', 'forumsBreadcrumbLink' ), self::seo()->seoUrl( 'forums' ) );

        $forums = self::forums()->listing();

        if ( $forums->forumsExist ) {
            self::$vars['forums'] = $forums->forums;
            self::vars()->subForumsMenus = self::forums()->getSubForumsMenus();
        } else {
            self::$vars['forums'] = '';
        }

        return self::$vars;
    }

    public function viewForum() {
        $forumId = self::textParsing()->getValueBeforeSlash( self::request()->id );
        $enteredPassword = isset( self::request()->password ) ? self::request()->password : null;
        $data = self::cache()->massGetData( [ 'forums' => 'forums', 'topics' => 'topics' ] );
        $found = false;

        foreach ( $data->forums as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $found = true;
                $forumTitle = $forum->title;
                $forumDescription = $forum->description;
                break;
            }
        }

        if ( ! $found ) {
            self::errors()->throwError( self::localization()->getWords( 'errors', 'forumNotFound' ) );
        }

        if ( ! self::member()->haveForumPermission( $forumId, 'viewForum' ) ) {
            self::forums()->permissionsError( $forumId, 'viewForum' );
        }

        self::forums()->checkForumPassword( $forumId );
        self::vars()->breadcrumbs = self::forums()->getForumBreadcrumbs( $forumId );
        self::forums()->handleRedirectForum( $forumId );

        $totalTopics = 0;

        foreach ( $data->topics as $topic ) {
            if ( $topic->forumId == $forumId ) $totalTopics++;
        }

        self::$vars['subForums'] = self::forums()->listing( $forumId )->forums;

        $pageData = self::pagination()->prePaginationWithTotal( $totalTopics, self::member()->topicsPerPage(), self::seo()->seoUrl( 'forums', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $forumId, $forumTitle ) ] ) );
        $filter = self::forums()->getForumFilter( $forumId );
        self::$vars = \array_merge( self::$vars, self::forums()->getStartedFilterWords() );
        $followers = self::followers()->getFollowers( $forumId, 'forum' );

        self::$vars['header'] = self::output()->getPartial(
            'Forums',
            'View',
            'Header',
            [
                'forumTitle'       => $forumTitle,
                'forumDescription' => $forumDescription,
                'filterDropDown'   => self::forums()->getFilterDropDown( $forumId ),
                'pagination'       => $pageData['full'],
                'newTopicButton'   => self::buttons()->getButtonUsingPermissions( \BanditBB\Types\Buttons::NEW_TOPIC, $forumId ),
                'followers'        => $followers->followers,
                'followersDialog'  => $followers->dialog,
                'markAllRead'      => self::member()->signedIn() ? self::output()->getPartial( 'Forums', 'View', 'MarkAllRead', [ 'url' => self::seo()->seoUrl( 'forums', 'markall', [ 'id' => $forumId ] ) ] ) : ''
            ]
        );

        $timeFrameSeconds = self::dateTime()->convertStringToTimestamp( $filter['started'] );
        $pins = false;
        self::$vars['topics'] = '';

        foreach ( $data->topics as $topic ) {
            if ( $topic->forumId == $forumId && $topic->pinned == 1 ) $pins = true; 
        }

        $thisPage = ( isset( self::request()->page ) && \ctype_digit( self::request()->page ) ) ? self::request()->page : 1;
        $pinsData = [];

        if ( $pageData ['total'] > 0 || $pins ) {
            if ( $pins && $thisPage == 1 ) {
                foreach ( $data->topics as $topic ) {
                    if ( $topic->forumId == $forumId && $topic->pinned == 1 ) {
                        self::$vars['topics'] .= self::topics()->listing( $topic->topicId );
                    }
                }
            }

            $sql = self::db()->query( self::queries()->selectTopicsLimit(), [
                'forumId'   => $forumId,
                'timeframe' => $filter['started'] != 'anytime' ? " AND createdTimestamp < " . $timeFrameSeconds . "" : '',
                'sortBy'    => $filter['sortBy'],
                'sortOrder' => $filter['sortOrder'],
                'from'      => $pageData['from'],
                'perPage'   => $pageData['perPage']
            ]);

            $totalReturned = self::db()->numRows( $sql );

            if ( $totalReturned > 0 || $pins ) {
                while ( $row = self::db()->fetchObject( $sql ) ) self::$vars['topics'] .= self::topics()->listing( $row->topicId );
            } else {
                self::$vars['topics'] = self::output()->getPartial( 'Forums', 'Filter', 'Empty' );
            }

            self::db()->freeResult( $sql );
        } else {
            self::$vars['topics'] = self::output()->getPartial( 'Forums', 'Topics', 'None' );
        }

        self::vars()->forumId = $forumId;
        self::vars()->topicId = 0;
        self::whosOnline()->recordForumTopicBrowsing( $forumId, 0 );

        self::$vars['browsing'] = self::whosOnline()->usersBrowsing( 'forum', $forumId );

        return self::$vars;
    }

    public function verifyForumPassword() {
        self::security()->validateCSRFToken();
        if ( self::settings()->forumPasswordCaptchaEnabled ) self::captcha()->validateCaptcha();

        $data = self::cache()->getData( 'forums' );
        $password = self::request()->password;
        $info = \json_decode( \base64_decode( self::request()->data ) );
        $forumId = $info->forumId;
        $url = $info->url;
        $found = false;

        foreach ( $data as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $found = true;
                $forumPassword = $forum->password;
            }
        }

        if ( ! $found ) {
            self::forums()->checkForumPassword( $forumId, $url, self::localization()->getWords( 'errors', 'forumNotFound' ) );
        }

        if ( ! password_verify( $password, $forumPassword ) ) {
            self::forums()->checkForumPassword( $forumId, $url, self::localization()->getWords( 'errors', 'forumPasswordInvalid' ) );
        }

        if ( isset( $_COOKIE['BanditBB_Authorized_Forums'] ) ) {
            $cookieData = \unserialize( $_COOKIE['BanditBB_Authorized_Forums'] );
            $exists = false;

            foreach ( $cookieData as $cookieItem ) {
                if ( $cookieItem == $forumId ) $exists = true;
            }

            if ( ! $exists ) {
                \array_push( $forumId );
                self::cookies()->newCookie( 'BanditBB_Authorized_Forums', \serialize( $cookieData ), \strtotime( self::settings()->forumPasswordCookieExpirationPeriod, \time() ) );
            }
        } else {
            self::cookies()->newCookie( 'BanditBB_Authorized_Forums', \serialize( [ $forumId ] ), \strtotime( self::settings()->forumPasswordCookieExpirationPeriod, \time() ) );
        }

        self::redirect()->normalRedirect( $url );
    }
}