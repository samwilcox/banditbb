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

class Url extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getRedirectUrl() {
        $url = $_SERVER['HTTP_REFERER'];

        if ( self::settings()->seoEnabled ) {
            if ( \stristr( $url, self::vars()->baseUrl ) && ! \stristr( $url, 'signin' ) ) {
                return $url;
            } else {
                return self::seo()->seoUrl( 'forums' );
            }
        } else {
            if ( \stristr( $url, self::vars()->wrapper ) && ! \stristr( $url, 'signin' ) ) {
                return $url;
            } else {
                return self::vars()->wrapper;
            }
        }
    }

    public static function getCurrentPageUrl() {
        $uri = $_SERVER['REQUEST_URI'];
        $url = '';

        if ( \strlen( $uri ) == 1 ) {
            $url = self::seo()->seoUrl( 'forums' );
        } else {
            $url = self::vars()->baseUrl . $uri;
        }

        return $url;
    }

    public static function getUrlWithIdAndTitle( $id, $title ) {
        return \urlencode( \sprintf( '%s-%s', $id, \strtolower( \str_replace( ' ', '-', $title ) ) ) );
    }
}