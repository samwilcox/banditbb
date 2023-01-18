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

class Agent {

    protected static $instance;
    protected static $agent;

    public function __construct() {
        self::$agent = new \stdClass();

        self::$agent->ipAddress = $_SERVER['REMOTE_ADDR'];
        self::$agent->hostname = \gethostbyaddr( self::$agent->ipAddress );
        self::$agent->agent = $_SERVER['HTTP_USER_AGENT'];
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getIpAddress() {
        return self::$agent->ipAddress;
    }

    public static function getHostname() {
        return self::$agent->hostname;
    }

    public static function getUserAgent() {
        return self::$agent->agent;
    }
}