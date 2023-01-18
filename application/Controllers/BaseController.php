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

namespace BanditBB\Controllers;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class BaseController {

    protected static $vars = [];

    protected static function set( $arr ) {
        self::$vars = \array_merge( self::$vars, $arr );
    }

    protected static function output( $controller, $action ) {
        \BanditBB\Output\Output::i()->render( $controller, $action, self::$vars );
    }

    protected static function outputSource( $source, $contentType ) {
        \BanditBB\Output\Output::i()->renderSource( $source, $contentType );
    }

    protected static function outputPartial( $controller, $action, $partial ) {
        \BanditBB\Output\Output::i()->renderPartial( $controller, $action, $partial, self::$vars );
    }

    protected static function outputRaw( $source ) {
        print $source;
    }
}