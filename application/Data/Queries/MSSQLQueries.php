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

namespace BanditBB\Data\Queries;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class MSSQLQueries implements \BanditBB\Data\QueriesStructure {

    protected static $instance;
    protected static $connInfo;
    protected $prefix = '';

    public function __construct() {
        require ( APP_PATH . 'Config.inc.php' );
        self::$connInfo = isset( $cfg ) ? $cfg : [];
        $this->prefix = self::$connInfo['dbPrefix'];
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }
}