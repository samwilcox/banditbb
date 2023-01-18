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

namespace BanditBB\Core;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Session extends \BanditBB\Application {

    protected static $instance;
    protected static $params;

    public function __construct() {
        self::$params = new \stdClass();
        self::$params->duration = 15;
        self::$params->ipMatch = false;
        self::$params->lifetime = 0;
        self::$params->session = null;

        self::constructSessionData();
        self::sessionGc();
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    private static function constructSessionData() {
        self::$params->session = new \stdClass();
        self::$params->session->id = 0;
        self::$params->session->expires = 0;
        self::$params->session->lastClick = 0;
        self::$params->session->location = "";
        self::$params->session->forumId = 0;
        self::$params->session->topicId = 0;
        self::$params->session->memberId = 0;
        self::$params->session->memberUsername = 'Guest';
        self::$params->session->memberDisplayName = 'Guest';
        self::$params->session->displayOnList = 0;
        self::$params->session->adminSession = false;
    }

    public static function management() {
        self::$params->duration = ( self::settings()->sessionTimeout * 60 );
        self::$params->ipMatch = self::settings()->sessionIpMatch;

        if ( self::settings()->sessionStoreMethod == 'dbstore' ) {
            self::$params->lifetime = \get_cfg_var( 'session.gc_maxlifetime' );

            \session_set_save_handler(
                [ &$this, 'session_open' ],
                [ &$this, 'session_close' ],
                [ &$this, 'session_read' ],
                [ &$this, 'session_write' ],
                [ &$this, 'session_delete' ],
                [ &$this, 'session_garbage_collection' ]
            );
        }

        \session_start();

        if ( isset( self::request()->controller ) && self::request()->controller == 'ajax' && isset( self::request()->sid ) ) {
            self::$params->session->id = \session_id( self::request()->sid );
        } else {
            self::$params->session->id = \session_id();
        }

        if ( isset( $_COOKIE['BanditBB_Token'] ) ) {
            $token = $_COOKIE['BanditBB_Token'];
            $found = false;
            $data = self::cache()->getData( 'members_devices' );

            foreach ( $data as $device ) {
                if ( $device->signInKey == $token ) {
                    $found = true;
                    $memberId = $device->memberId;
                }
            }

            switch ( $found ) {
                case true:
                    $data = self::cache()->massGetData( [ 'members' => 'members', 'sessions' => 'sessions' ] );

                    foreach ( $data->members as $member ) {
                        if ( $member->memberId == $memberId ) {
                            $displayName = $member->displayName;
                            $username = $member->username;
                            $displayOnList = $member->displayInUsersOnlineList;
                        }
                    }

                    $found = false;

                    foreach ( $data->sessions as $session ) {
                        if ( $session->memberId == $memberId ) {
                            $found = true;
                            $ipAddress = $session->ipAddress;
                            $userAgent = $session->userAgent;
                            $adminSess = $session->adminSession;
                        }
                    }

                    switch ( $found ) {
                        case true:
                            switch ( self::$params->ipMatch ) {
                                case true:
                                    if ( $ipAddress != self::agent()->getIpAddress() ||$userAgent != self::agent()->getUserAgent() ) {
                                        self::destroySession();
                                    } else {
                                        self::updateSession( true, [ 'id' => $memberId, 'username' => $username, 'displayName' => $displayName, 'displayOnList' => $displayOnList, 'adminSession' => $adminSess ] );
                                    }
                                    break;

                                case false:
                                    self::updateSession( true, [ 'id' => $memberId, 'username' => $username, 'displayName' => $displayName, 'displayOnList' => $displayOnList, 'adminSession' => $adminSess ] );
                                    break;
                            }
                            break;

                        case false:
                            self::createSession( true, [ 'id' => $memberId, 'username' => $username, 'displayName' => $displayName, 'displayOnList' => $displayOnList, 'adminSession' => 0 ] );
                            break;
                    }
                    break;

                case false:
                    self::destroySession();
                    break;
            }
        } else {
            $data = self::cache()->getData( 'sessions' );
            $found = false;

            foreach ( $data as $session ) {
                if ( $session->sessionId == self::$params->session->id ) {
                    $found = true;
                    $ipAddress = $session->ipAddress;
                    $userAgent = $session->userAgent;
                }
            }

            switch ( $found ) {
                case true:
                    switch ( self::$params->ipMatch ) {
                        case true:
                            if ( $ipAddress != self::agent()->getIpAddress() || $userAgent != self::agent()->getUserAgent() ) {
                                self::destroySession();
                            } else {
                                self::updateSession();
                            }
                            break;

                        case false:
                            self::updateSession();
                            break;
                    }
                    break;

                case false:
                    self::createSession();
                    break;
            }
        }
    }

    private static function destroySession() {
        self::cookies()->deleteCookie( 'BanditBB_Token' );

        \session_unset();
        \session_destroy();

        if ( isset( $_COOKIE[\session_name()] ) ) self::cookies()->deleteCookie( \session_name(), true );

        self::deleteUserSession();
        unset( $_SESSION['BanditBB_ID'] );
        self::redirect()->normalRedirect( self::seo()->seoUrl( 'forums' ) );
    }

    private static function updateSession( $memberSession = false, $memberData = [] ) {
        self::$params->session->expires = ( \time() + self::$params->duration );
        self::$params->session->lastClick = \time();
        self::$params->session->location = $_SERVER['REQUEST_URI'];
        self::$params->session->displayOnList = 0;

        if ( $memberSession ) {
            self::$params->session->memberId = $memberData['id'];
            self::$params->session->memberUsername = $memberData['username'];
            self::$params->session->memberDisplayName = $memberData['displayName'];
            self::$params->session->displayOnList = $memberData['displayOnList'];
            self::$params->session->adminSession = $memberData['adminSession'] == 1 ? true : false;
        } else {
            unset( $_SESSION['BanditBB_ID'] );
        }

        self::updateUserSession();
    }

    private static function createSession( $memberSession = false, $memberData = [] ) {
        self::$params->session->expires = ( \time() + self::$params->duration );
        self::$params->session->lastClick = \time();
        self::$params->session->location = $_SERVER['REQUEST_URI'];

        if ( $memberSession ) {
            self::$params->session->memberId = $memberData['id'];
            self::$params->session->memberUsername = $memberData['username'];
            self::$params->session->memberDisplayName = $memberData['displayName'];
            self::$params->session->displayOnList = $memberData['displayOnList'];
            self::$params->session->adminSession = $memberData['adminSession'] == 1 ? true : false;
        } else {
            self::$params->session->memberId = 0;
            self::$params->session->memberUsername = 'Guest';
            self::$params->session->memberDisplayName = 'Guest';
            self::$params->session->displayOnList = 0;
            self::$params->session->adminSession = false;

            unset( $_SESSION['BanditBB_ID'] );
        }

        self::createUserSession();
    }

    private static function createUserSession() {
        if ( self::request()->controller != 'source' ) {
            self::db()->query( self::queries()->insertUserSession(),
            [
                'id'             => self::$params->session->id,
                'memberId'       => self::$params->session->memberId,
                'memberUsername' => self::$params->session->memberUsername,
                'expires'        => self::$params->session->expires,
                'lastClick'      => self::$params->session->lastClick,
                'location'       => self::$params->session->location,
                'forumId'        => self::$params->session->forumId,
                'topicId'        => self::$params->session->topicId,
                'ipAddress'      => self::agent()->getIpAddress(),
                'userAgent'      => self::agent()->getUserAgent(),
                'hostname'       => self::agent()->getHostname(),
                'displayOnList'  => self::$params->session->displayOnList,
                'isBot'          => self::request()->isBot() ? 1 : 0,
                'botName'        => self::request()->botName(),
                'adminSession'   => self::$params->session->adminSession ? 1 : 0
            ]);

            self::cache()->update( 'sessions' );
        } 
    }

    private static function updateUserSession() {
        if ( self::request()->controller != 'source' ) {
            self::db()->query( self::queries()->updateUserSession(),
            [
                'expires'       => self::$params->session->expires,
                'lastClick'     => self::$params->session->lastClick,
                'location'      => self::$params->session->location,
                'displayOnList' => self::$params->session->displayOnList,
                'id'            => self::$params->session->id,
                'forumId'       => self::$params->session->forumId,
                'topicId'       => self::$params->session->topicId
            ]);

            self::cache()->update( 'sessions' );
        }
    }

    private static function deleteUserSession() {
        if ( self::request()->controller != 'source' ) {
            self::db()->query( self::queries()->deleteUserSession(), [ 'id' => self::$params->session->id ] );
            self::cache()->update( 'sessions' );
        }
    }

    private static function sessionGc() {
        self::db()->query( self::queries()->deleteUserSessionGc() );
        if ( self::db()->affectedRows() > 0 ) self::cache()->update( 'sessions' );
    }

    public function session_open() {
        // Left blank on purpose.
    }

    public function session_close() {
        // Left blank on purpose.
    }

    public function session_read( $id ) {
        $data = '';
        $time = \time();

        $sql = self::db()->query( self::queries()->selectSessionDataFromStore(), [ 'id' => $id, 'time' => $time ] );

        if ( self::db()->numRows( $sql ) > 0 ) {
            $row = self::db()->fetchArray( $sql );
            $data = $row['data'];
        }

        self::db()->freeResult( $sql );

        return $data;
    }

    public function session_write( $id, $data ) {
        $time = \time();
        $sql = self::db()->query( self::queries()->selectSessionFromStore(), [ 'id' => $id ] );
        $total = self::db()->numRows( $sql );
        self::db()->freeResult( $sql );

        if ( $total == 0 ) {
            self::db()->query( self::queries()->insertSessionStoreNew(), [ 'id' => $id, 'data' => $data, 'lifetime' => self::$params->lifetime ] );
        } else {
            self::db()->query( self::queries()->updateSessionStoreData(), [ 'id' => $id, 'data' => $data, 'lifetime' => self::$params->lifetime ] );
        }

        return true;
    }

    public function session_delete( $id ) {
        self::db()->query( self::queries()->deleteFromSessionStore(), [ 'id' => $id ] );
    }

    public function session_garbage_collection() {
        self::db()->query( self::queries()->deleteFromSessionStoreGc() );
    }

    public static function getSessionId() {
        return self::$params->session->id;
    }

    public static function setForumId( $id ) {
        self::$params->session->forumId = $id;
    }

    public static function setTopicId( $id ) {
        self::$params->session->topicId = $id;
    }

    public static function getAdminSession() {
        return self::$params->session->adminSession;
    }
}