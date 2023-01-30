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

class Request extends \BanditBB\Application {

    protected static $instance;
    protected static $incoming = [];
    protected static $bot;

    public function __construct() {
        self::$bot = new \stdClass();
        self::parseRequest();
        self::detectBots();
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function parseRequest() {
        foreach ( $_GET as $k => $v ) self::$incoming[$k] = \filter_var( $v, FILTER_UNSAFE_RAW );
        foreach ( $_POST as $k => $v ) self::$incoming[$k] = \filter_var( $v, FILTER_UNSAFE_RAW );

        $bits = null;

        if ( self::settings()->seoEnabled ) {
            if ( \strlen( $_SERVER['QUERY_STRING'] ) > 0 ) {
                if ( ! \stripos( $_SERVER['QUERY_STRING'], '&' ) ) {
                    if ( \count( $_POST ) < 1 ) {
                        $bits = \explode( '/', $_SERVER['QUERY_STRING'] );
                    }
                }
            }
        }

        if ( $bits != null ) {
            \array_shift( $bits );

            if ( isset( $bits[0] ) ) self::$incoming['controller'] = $bits[0];
            if ( isset( $bits[1] ) ) self::$incoming['action'] = $bits[1];

            \array_slice( $bits, 2 );

            if ( \count( $bits ) > 0 ) {
                for ( $i = 0; $i < \count( $bits ); $i++ ) {
                    if ( isset( $bits[$i + 1] ) ) {
                        self::$incoming[$bits[$i]] = $bits[$i + 1];
                    }
                }
            }
        }
    }

    public static function detectBots() {
        self::$bot->name;
        self::$bot->present = false;
        $bots = \explode( ',', self::settings()->searchBotList ?? '' );

        for ( $i = 0; $i < \count( $bots ); $i++ ) {
            if ( \strpos( ' ' . \strtolower( self::agent()->getUserAgent() ), \strtolower( $bots[$i] ) ) != false ) self::$bot->name = $bots[$i];
        }

        self::$bot->present = \strlen( self::$bot->name ) > 0 ? true : false;
    }

    public static function isBot() {
        return self::$bot->present;
    }

    public static function botName() {
        return self::$bot->name;
    }

    public function __set( $key, $value ) {
        self::$incoming[$key] = $value;
    }

    public function __get( $key ) {
        if ( \array_key_exists( $key, self::$incoming ) ) return self::$incoming[$key];
        return NULL;
    }

    public function __isset( $key ) {
        if ( \array_key_exists( $key, \count( self::$incoming ) < 1 ? [] : self::$incoming ) ) {
            return true;
        } else {
            return false;
        }
    }
}