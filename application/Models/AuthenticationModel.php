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

class AuthenticationModel extends \BanditBB\Models\BaseModel {

    private static $vars = [];

    public function authenticateUser() {
        self::security()->validateCSRFToken();
        if ( self::settings()->signInCaptchaEnabled ) self::captcha()->validateCaptcha();

        $identity = self::request()->identity;
        $password = self::request()->password;
        $rememberMe = self::request()->rememberme == 1 ? true : false;
        $redirectUrl = self::request()->url;

        $result = self::member()->validateCredentials( $identity, $password );

        if ( ! $result->success ) {
            self::authenticationError( $result->code );
        }

        $data = self::cache()->getData( 'members' );

        foreach ( $data as $member ) {
            switch ( self::settings()->allowSignInWithUsername ) {
                case true:
                    if ( $member->username == $identity || $member->emailAddress == $identity ) {
                        $username = $member->username;
                        $memberId = $member->memberId;
                        $hidden = $member->displayInUsersOnlineList;
                        break;
                    }
                    break;

                case false:
                    if ( $member->emailAddress == $identity ) {
                        $username = $member->username;
                        $memberId = $member->memberId;
                        $hidden = $member->displayInUsersOnlineList;
                        break;
                    }
                    break;
            }
        }

        self::authentication()->completeMemberSignIn( $memberId, $username, $rememberMe, $hidden, $redirectUrl );
    }

    public function signOutUser() {
        self::security()->validateCSRFToken();

        if ( isset( $_COOKIE['BanditBB_Token'] ) ) {
            $devices = self::cache()->getData( 'members_devices' );
            $deviceFound = false;

            foreach ( $devices as $device ) {
                if ( $device->signInKey == $_COOKIE['BanditBB_Token'] ) {
                    $deviceFound = true;
                    $deviceId = $device->deviceId;
                    break;
                }
            }

            if ( $deviceFound ) {
                self::db()->query( self::queries()->updateMembersDevicesRemoveKey(), [ 'id' => $deviceId, 'timestamp' => \time(), 'userAgent' => self::agent()->getUserAgent() ] );
                self::cache()->update( 'members_devices' );
            }

            self::cookies()->deleteCookie( 'BanditBB_Token' );
        }

        self::db()->query( self::queries()->updateMembersLastOnline(), [ 'timestamp' => \time(), 'id' => self::member()->memberId() ] );
        self::db()->query( self::queries()->deleteUserSession(), [ 'id' => self::session()->getSessionId() ] );

        unset( $_SESSION['BanditBB_ID'] );

        self::cache()->massUpdate( [ 'sessions', 'members' ] );

        self::redirect()->normalRedirect( self::url()->getRedirectUrl() );
    }
}