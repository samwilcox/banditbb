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

class Vars extends \BanditBB\Application {

    protected static $instance;
    protected static $vars = [];
    protected static $globalVars = [];

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public function __set( $key, $value ) {
        self::$vars[$key] = $value;
    }

    public function __get( $key ) {
        if ( \array_key_exists( $key, self::$vars ) ) return self::$vars[$key];
        return null;
    }

    public function __isset( $key ) {
        if ( \array_key_exists( $key, self::$vars ) ) {
            return true;
        } else {
            return false;
        }
    }
} 