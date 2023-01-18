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

namespace BanditBB\Data;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Database {

    protected static $instance;
    protected static $connInfo;

    public static function i() {
        if ( ! self::$instance ) {
            require ( APP_PATH . 'Config.inc.php' );
            self::$connInfo = isset( $cfg ) ? $cfg : [];

            switch ( self::$connInfo['dbDriver'] ) {
                case 'mysqli':
                    self::$instance = \BanditBB\Data\Database\MySqliDatabase::i();
                    break;

                case 'mssql':
                    self::$instance = \BanditBB\Data\Database\MSSQLDatabase::i();
                    break;  
            }
        }

        return self::$instance;
    }
}