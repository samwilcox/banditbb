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

namespace BanditBB\Url;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Seo extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function seoUrl( $controller, $action = null, $params = [], $includeCSRFToken = false ) {
        $retVal = '';

        switch ( self::settings()->seoEnabled ) {
            case true:
                $url = '';

                $url .= \sprintf( '/%s', $controller );

                if ( $action != null ) $url .= \sprintf( '/%s', $action );

                if ( isset( $params ) && \count( $params ) > 0 ) {
                    foreach ( $params as $k => $v ) {
                        $url .= \sprintf( '/%s/%s', $k, $v );
                    }
                }

                if ( $includeCSRFToken && self::settings()->CSRFProtectionEnabled ) {
                    $url .= \sprintf( '/token/%s', self::security()->getCSRFToken() );
                }

                $retVal = \sprintf( '%s?%s', self::vars()->wrapper, $url );
                break;
            
            case false:
                $url = '';

                $url .= \sprintf( '?controller=%s', $controller );

                if ( $action != null ) $url .= \sprintf( '&amp;action=%s', $action );

                if ( isset( $params ) && \count( $params ) > 0 ) {
                    foreach ( $params as $k => $v ) {
                        $url .= \sprintf( '&amp;%s=%s', $k, $v );
                    }
                }

                if ( $includeCSRFToken && self::settings()->CSRFProtectionEnabled ) {
                    $url .= \sprintf( '&amp;token=%s', self::security()->getCSRFToken() );
                }

                $retVal = \sprintf( '%s%s', self::vars()->wrapper, $url );
                break;
        }

        return $retVal;
    }
}