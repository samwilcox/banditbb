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

class Captcha extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getCaptcha() {
        if ( self::settings()->captchaEnabled ) {
            $out = new \stdClass();
            $out->captcha = '';
            $out->hidden = '';
            $out->onclick = '';
            $out->onsubmit = '';

            switch ( self::settings()->captchaType ) {
                case 'normal':
                    $out->captcha = self::output()->getPartial(
                        'Security',
                        'Captcha',
                        'Normal',
                        [
                            'url'  => self::seo()->seoUrl( 'ajax', 'captcha' ),
                            'hash' => self::utilities()->randomHash()
                        ]
                    );
                    break;

                case 'recaptcha':
                    $out->captcha = self::output()->getPartial(
                        'Security',
                        'Captcha',
                        'ReCaptcha2',
                        [
                            'siteKey' => self::settings()->recaptchaSiteKey
                        ]
                    );
                    break;

                case 'recaptcha3':
                    $hash = self::utilities()->randomHash();

                    $out->captcha = self::output()->getPartial(
                        'Security',
                        'Captcha',
                        'ReCaptcha3',
                        [
                            'siteKey' => self::settings()->recaptchaSiteKey,
                            'hash'    => $hash
                        ]
                    );

                    $out->hidden = self::output()->getPartial(
                        'Security',
                        'Captcha',
                        'ReCaptcha3HiddenField',
                        [
                            'hash' => $hash
                        ]
                    );

                    $out->onclick = self::output()->getPartial(
                        'Security',
                        'Captcha',
                        'ReCaptcha3OnClickAnd'
                    );

                    $out->onsubmit = self::output()->getPartial(
                        'Security',
                        'Captcha',
                        'ReCaptcha3OnClick'
                    );
                    break;
            }

            return $out;
        }
    }

    public static function generateCaptcha() {
        $image = \imagecreatetruecolor( self::settings()->captchaWidth, self::settings()->captchaHeight );
        \imageantialias( $image, true );
        $colors = [];
        $red = \rand( 125, 175 );
        $green = \rand( 125, 175 );
        $blue = \rand( 125, 175 );

        for ( $i = 0; $i < 5; $i++ ) {
            $colors[] = \imagecolorallocate( $image, $red - 20 * $i, $green - 20 * $i, $blue - 20 * $i );
        }

        \imagefill( $image, 0, 0, $colors[0] );

        for ( $i = 0; $i < 10; $i++ ) {
            \imagesetthickness( $image, \rand( 2, 10 ) );
            $lineColor = $colors[ \rand(1, 4 ) ];
            \imagerectangle( $image, \rand( -10, ( self::settings()->captchaWidth - 10 ) ), \rand( -10, 10 ), \rand( -10, ( self::settings()->captchaWidth - 10 ) ), \rand( 40, ( self::settings()->captchaWidth - 90 ) ), $lineColor ); 
        }


        $black = \imagecolorallocate( $image, 0, 0, 0 );
        $white = \imagecolorallocate( $image, 255, 255, 255 );
        $textColors = [$black, $white];

        $fontsList = self::settings()->captchaFonts;
        $fonts = [];

        if ( \count( $fontsList ) > 0 ) {
            foreach ( $fontsList as $font ) {
                $fonts[] = \sprintf( '%spublic/fonts/captcha/%s', ROOT_PATH, $font );
            }
        }

        $captchaStr = self::generateString();
        $_SESSION['BanditBB_Captcha_String'] = $captchaStr;

        for ( $i = 0; $i < \strlen( $captchaStr ); $i++ ) {
            $letterSpace = 170 / \strlen( $captchaStr );
            $initial = 15;

            \imagettftext( $image, 24, \rand( -15, 15 ), $initial + $i * $letterSpace, \rand( 25, ( self::settings()->captchaHeight - 10 ) ), $textColors[\rand( 0, 1)], $fonts[\array_rand( $fonts )], $captchaStr[$i] );
        }

        \header( 'Content-type: image/png' );
        \imagepng( $image );
        \imagedestroy( $image );
        exit;
    }

    public static function validateCaptcha() {
        if ( self::settings()->captchaEnabled ) {
            switch ( self::settings()->captchaType ) {
                case 'normal':
                    $captchaString = self::request()->captchacode;

                    if ( $captchaString != $_SESSION['BanditBB_Captcha_String'] ) {
                        self::errors()->throwError( self::localization()->getWords( 'errors', 'normalCaptchaWrongCode' ) );
                    }
                    break;

                case 'recaptcha':
                    $response = $_POST['g-recaptcha-response'];
                    $data = [ 'secret' => self::settings()->recaptchaSecretKey, 'response' => $response, 'remoteip' => self::agent()->getIpAddress() ];
                    $options = [
                        'http' => [
                            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method'  => "POST",
                            'content' => \http_build_query( $data )
                        ]
                    ];

                    $context = \stream_context_create( $options );
                    $verifyResponse = \file_get_contents( 'https://www.google.com/recaptcha/api/siteverify', false, $context );
                    $responseData = \json_decode( $verifyResponse );

                    if ( ! $responseData->success ) {
                        self::errors()->throwError( self::localization()->getWords( 'errors', 'recaptchaOriginalInvalid' ) );
                    }
                    break;

                case 'recaptcha3':
                    $token = self::request()->recaptchatoken;
                    $data = [ 'secret' => self::settings()->recaptchaSecretKey, 'response' => $token, 'remoteip' => self::agent()->getIpAddress() ];
                    $options = [
                        'http' => [
                            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method'  => "POST",
                            'content' => \http_build_query( $data )
                        ]
                    ];

                    $context = \stream_context_create( $options );
                    $verifyResponse = \file_get_contents( 'https://www.google.com/recaptcha/api/siteverify', false, $context );
                    $responseData = \json_decode( $verifyResponse );

                    if ( $responseData->success ) {
                        if ( $responseData->score < self::settings()->recaptchaMinScore ) {
                            switch ( self::settings()->recaptchaLowScoreAction ) {
                                case 'error':
                                    self::errors()->throwError( self::localization()->getWords( 'errors', 'recaptchaLowScore' ) );
                                    break;
                            }
                        }
                    } else {
                        self::errors()->throwError( self::localization()->getWords( 'errors', 'recaptchaFailure' ) );
                    }
                    break;
            }
        }
    }

    private static function generateString() {
        $input = self::settings()->captchaInputCharacters;
        $length = \strlen( $input );
        $randStr = '';

        for ( $i = 0; $i < self::settings()->captchaTotalCharacters; $i++ ) {
            $randChar = $input[\mt_rand( 0, $length - 1 ) ];
            $randStr .= $randChar;
        }

        return $randStr;
    }
}