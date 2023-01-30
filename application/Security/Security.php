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

namespace BanditBB\Security;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Security extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function validateCSRFToken() {
        if ( self::settings()->CSRFProtectionEnabled ) {
            if ( ! isset( $_SESSION['CSRF_Token'] ) ) {
                self::errors()->throwError( self::localization()->getWords( 'errors', 'csrfTokenMissing' ) );
            }

            if ( ! isset( self::request()->token ) ) {
                self::errors()->throwError( self::localization()->getWords( 'errors', 'csrfTokenMissing' ) );
            }

            $token = $_SESSION['CSRF_Token'];

            if ( self::settings()->CSRFProtectionOneTimeTokens ) {
                unset( $_SESSION['CSRF_Token'] );
            } else {
                unset( $_SESSION['CSRF_TokenExists'] );
            }

            if ( self::settings()->CSRFProtectionOriginCheck && \sha1( $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] ) != \substr( \base64_decode( $token ), 10, 40 ) ) {
                self::errors()->throwError( self::localization()->getWords( 'errors', 'csrfOriginError' ) );
            }

            if ( self::request()->token != $token ) {
                self::errors()->throwError( self::localization()->getWords( 'errors', 'csrfTokenDoesNotMatch' ) );
            }

            if ( self::settings()->CSRFProtectionTokenExpirationSeconds != 0 ) {
                if ( \intval( \substr( \base64_decode( $token ), 0, 10 ) ) + self::settings()->CSRFProtectionTokenExpirationSeconds < \time() ) {
                    self::errors()->throwError( self::localization()->getWords( 'errors', 'csrfTokenExpired' ) );
                }
            }
        }
    }

    public static function validateCSRFTokenAjax() {
        
    }

    public static function getCSRFTokenFormField() {
        if ( self::settings()->CSRFProtectionEnabled ) {
            return self::output()->getPartial( 'Security', 'CSRF', 'HiddenField', [ 'token' => self::getCSRFToken() ] );
        } else {
            return '';
        }
    }

    public static function getCSRFTokenFieldForAjax() {
        if ( self::settings()->CSRFProtectionEnabled ) {
            return self::output()->getPartial( 'Security', 'CSRF', 'AjaxField', [ 'token' => self::getCSRFTokenForAjax() ] );
        } else {
            return '';
        }
    }

    public static function getCSRFToken() {
        $extraProtection = self::settings()->CSRFProtectionOriginCheck ? \sha1( $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] ) : '';
        $token = '';

        if ( self::settings()->CSRFProtectionOneTimeTokens ) {
            $token = \base64_encode( \time() . $extraProtection . self::randomizeString( 32 ) );

            $_SESSION['CSRF_Token'] = $token;

            unset( $_SESSION['CSRF_TokenExists'] );
        } else {
            if ( isset( $_SESSION['CSRF_TokenExists'] ) ) {
                $token = $_SESSION['CSRF_Token'];
            } else {
                $token = \base64_encode( \time() . $extraProtection . self::randomizeString( 32 ) );

                $_SESSION['CSRF_Token'] = $token;
                $_SESSION['CSRF_TokenExists'] = true;
            }
        }

        return $token;
    }

    public static function getCSRFTokenForAjax() {
        $extraProtection = self::settings()->CSRFProtectionOriginCheck ? \sha1( $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] ) : '';
        $token = '';

        if ( isset( $_SESSION['CSRF_Token_Ajax'] ) ) {
            $token = $_SESSION['CSRF_Token_Ajax'];
        } else {
            $token = \base64_encode( \time() . $extraProtection . self::randomizeString( 32 ) );
            $_SESSION['CSRF_Token_Ajax'] = $token;
        }

        return $token;
    }

    private static function randomizeString( $length ) {
        $seed = \strlen( self::settings()->CSRFProtectionSeed ) > 0 ? self::settings()->CSRFProtectionSeed : 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijqlmnopqrtsuvwxyz0123456789';
        $max = \strlen( $seed ) - 1;
        $retVal = '';

        for ( $i = 0; $i < $length; $i++ ) {
            $retVal .= $seed[ \intval( \mt_rand( 0.0, $max ) ) ];
        }

        return $retVal;
    }
}