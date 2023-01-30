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

class Authentication extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getAuthenticationForm( $formType, $error = null ) {
        $optionsPresent = false;

        if ( self::settings()->accountCreationEnabled ) {
            $optionsPresent = true;

            $createAccount = self::output()->getPartial(
                'AuthenticationHelper',
                'Link',
                'CreateAccount',
                [
                    'url' => self::seo()->seoUrl( 'createaccount' )
                ]
            );
        } else {
            $createAccount = '';
        }

        if ( self::settings()->forgotPasswordEnabled ) {
            $optionsPresent = true;

            $forgotPassword = self::output()->getPartial(
                'AuthenticationHelper',
                'Link',
                'ForgotPassword',
                [
                    'url' => self::seo()->seoUrl( 'authentication', 'forgotpassword' )
                ]
            );
        } else {
            $forgotPassword = '';
        }

        if ( $optionsPresent ) {
            $options = self::output()->getPartial(
                'AuthenticationHelper',
                'Form',
                'Options',
                [
                    'forgotPassword' => $forgotPassword,
                    'createAccount'  => $createAccount
                ]
            );
        } else {
            $options = '';
        }

        $captcha = self::captcha()->getCaptcha();

        if ( self::settings()->signInFormValidateAjax ) {
            $onSubmit = self::output()->getPartial(
                'AuthenticationHelper',
                'Form',
                'OnSubmit',
                [
                    'id'       => $formType == 'normal' ? 'normal' : 'dialog',
                    'formName' => $formType == 'normal' ? 'signinnormalform' : 'signindialogform',
                    'onClick'  => self::settings()->signInCaptchaEnabled ? $captcha->onclick : ''
                ]
            );
        } else {
            $onSubmit = self::settings()->signInCaptchaEnabled ? $captcha->onsubmit : '';
        }

        switch ( $formType ) {
            case 'normal':
                return self::output()->getPartial(
                    'AuthenticationHelper',
                    'Form',
                    'Normal',
                    [
                        'wrapper'             => self::vars()->wrapper,
                        'type'                => 'normal',
                        'errorBox'            => self::utilities()->getErrorBox( $error, false, 'normal' ),
                        'identityPlaceholder' => self::localization()->getWords( 'authenticationhelper', 'identityPlaceholder' . self::settings()->allowSignInWithUsername ? 'Username' : '' ),
                        'options'             => $options,
                        'url'                 => self::url()->getRedirectUrl(),
                        'onSubmit'            => $onSubmit,
                        'csrfToken'           => self::security()->getCSRFTokenFormField(),
                        'captcha'             => self::settings()->signInCaptchaEnabled ? $captcha->captcha : '',
                        'captchaHidden'       => self::settings()->signInCaptchaEnabled ? $captcha->hidden : ''
                    ]
                );
                break;

            case 'dialog':
                return self::output()->getPartial(
                    'AuthenticationHelper',
                    'Form',
                    'Dialog',
                    [
                        'wrapper'             => self::vars()->wrapper,
                        'type'                => 'dialog',
                        'errorBox'            => self::utilities()->getErrorBox( $error, false, 'dialog' ),
                        'identityPlaceholder' => self::localization()->getWords( 'authenticationhelper', 'identityPlaceholder' . ( self::settings()->allowSignInWithUsername ? 'Username' : '' ) ),
                        'options'             => $options,
                        'url'                 => self::url()->getCurrentPageUrl(),
                        'onSubmit'            => $onSubmit,
                        'id'                  => $formType == 'normal' ? 'normal' : 'dialog',
                        'csrfToken'           => self::security()->getCSRFTokenFormField(),
                        'captcha'             => self::settings()->signInCaptchaEnabled ? $captcha->captcha : '',
                        'captchaHidden'       => self::settings()->signInCaptchaEnabled ? $captcha->hidden : ''
                    ]
                );
                break;
        }
    }

    public static function invalidSignInAttempt( $memberId ) {
        $retVal = new \stdClass();

        if ( self::settings()->authenticationSignInAttemptsEnabled ) {
            $data = self::cache()->getData( 'members' );

            foreach ( $data as $member ) {
                if ( $member->memberId == $memberId ) {
                    $attempts = $member->signInAttempts;
                    $lockedOut = $member->lockedOut == 1 ? true : false;
                    $lockoutExpires = $member->lockoutExpires;
                    break;
                }
            }

            if ( $lockedOut ) {
                if ( $lockoutExpires <= \time() ) {
                    self::db()->query( self::queries()->updateMemberRemoveLockout(), [ 'id' => $memberId ] );
                    self::cache()->update( 'members' );

                    $retVal->lockedOut = false;
                    $retVal->attempts = 0;
                    $retVal->timeLeft = null;
                    $retVal->enabled = true;
                } else {
                    $retVal->lockedOut = true;
                    $retVal->attempts = $attempts;
                    $retVal->timeLeft = ( $lockoutExpires - \time() ) / 60;
                    $retVal->enabled = true;
                }
            }

            $attempts++;

            if ( ! $lockedOut ) {
                if ( $attempts >= self::settings()->authenticationSignInAttemptsMax ) {
                    $expires = \time() + ( self::settings()->authenticationSignInAttemptsLockoutPeriodMinutes * 60 );

                    self::db()->query( self::queries()->updateMemberLockout(), [ 'attempts' => $attempts, 'expires' => $expires, 'id' => $memberId ] );
                    self::cache()->update( 'members' );

                    $retVal->lockedOut = true;
                    $retVal->attempts = $attempts;
                    $retVal->timeLeft = ( $expires - \time() ) / 60;
                    $retVal->enabled = true;
                } else {
                    self::db()->query( self::queries()->updateMemberNewAttempt(), [ 'attempts' => $attempts, 'id' => $memberId ] );
                    self::cache()->update( 'members' );

                    $retVal->lockedOut = false;
                    $retVal->attempts = $attempts;
                    $retVal->timeLeft = null;
                    $retVal->enabled = true;
                }
            }
        } else {
            $retVal->lockedOut = false;
            $retVal->attempts = null;
            $retVal->timeLeft = null;
            $retVal->enabled = false;
        }

        return $retVal;
    }

    public static function isLockedOut( $memberId ) {
        $retVal = new \stdClass();

        if ( self::settings()->authenticationSignInAttemptsEnabled ) {
            $data = self::cache()->getData( 'members' );

            foreach ( $data as $member ) {
                if ( $member->memberId == $memberId ) {
                    $lockedOut = $member->lockedOut == 1 ? true : false;
                    $attempts = $member->signInAttempts;
                    $lockoutExpires = $member->lockoutExpires;
                    break;
                }
            }

            if ( $lockedOut ) {
                if ( $lockoutExpires <= \time() ) {
                    self::db()->query( self::queries()->updateMemberRemoveLockout(), [ 'id' => $memberId ] );
                    self::cache()->update( 'members' );

                    $retVal->lockedOut = false;
                    $retVal->attempts = null;
                    $retVal->timeLeft = null;
                } else {
                    $retVal->lockedOut = true;
                    $retVal->attempts = $attempts;
                    $retVal->timeLeft = ( $lockoutExpires - \time() ) / 60;
                }
            } else {
                $retVal->lockedOut = false;
                $retVal->attempts = null;
                $retVal->timeLeft = null;
            }
        } else {
            $retVal->lockedOut = false;
            $retVal->attempts = null;
            $retVal->timeLeft = null;
        }

        return $retVal;
    }

    public static function completeMemberSignIn( $memberId, $username, $rememberMe, $hidden, $redirectUrl ) {
        $token = self::utilities()->authenticationTokenHash();
        $devices = self::cache()->getData( 'members_devices' );
        $deviceFound = false;

        if ( isset( $_COOKIE['BanditBB_Device_ID' ] ) ) {
            $deviceId = $_COOKIE['BanditBB_Device_ID'];
        } else {
            $deviceId = null;
        }

        if ( $deviceId != null ) {
            foreach ( $devices as $device ) {
                if ( $device->memberId == $memberId && $device->deviceId == $deviceId ) {
                    $deviceFound = true;
                    break;
                }
            }
        }

        if ( $deviceFound ) {
            self::db()->query( self::queries()->updateMembersDevices(), [ 'token' => $token, 'timestamp' => \time(), 'id' => $deviceId, 'userAgent' => self::agent()->getUserAgent() ] );
        } else {
            $deviceHash = self::utilities()->generateDeviceIdHash();
            self::db()->query( self::queries()->insertMembersDevices(), [ 'memberId' => $memberId, 'token' => $token, 'userAgent' => self::agent()->getUserAgent(), 'timestamp' => \time(), 'deviceId' => $deviceHash ] );
            self::cookies()->newCookie( 'BanditBB_Device_ID', $deviceHash, \strtotime( '+10 years', \time() ) );
        }

        if ( $rememberMe ) {
            $expiration = \strtotime( '+10 years', \time() );
        } else {
            $expiration = ( \time() + ( self::settings()->sessionTimeout * 60 ) );
        }

        self::cookies()->newCookie( 'BanditBB_Token', $token, $expiration );

        $_SESSION['BanditBB_ID'] = $memberId;

        $members = self::cache()->getData( 'members' );
        $update = false;

        foreach ( $members as $member ) {
            if ( $member->memberId == $memberId ) {
                if ( $member->signInAttempts != 0 ) $update = true;
            }
        }

        if ( $update ) {
            self::db()->query( self::queries()->updateMemberRemoveLockout(), [ 'id' => $memberId ] );
            self::cache()->update( 'members' );
        }

        $sessions = self::cache()->getData( 'sessions' );
        $sessionExists = false;

        foreach ( $sessions as $session ) {
            if ( $session->memberId == $memberId && $session->memberUsername == $username ) $sessionExists = true;
        }

        if ( $sessionExists ) {
            self::db()->query( self::queries()->updateUserSessionIdAndName(), [ 'id' => $memberId ] );
        }

        self::db()->query( self::queries()->updateMemberSession(), [ 'memberId' => $memberId, 'username' => $username, 'displayOnList' => $hidden, 'id' => self::session()->getSessionId() ] );

        self::cache()->massUpdate( [ 'sessions', 'members_devices' ] );

        self::redirect()->normalRedirect( $redirectUrl );
    }

    private static function authenticationError( $code ) {
        $_SESSION['BanditBB_Error_Code'] = $code;
        self::redirect()->normalRedirect( self::seo()->seoUrl( 'authentication' ) );
    }
}