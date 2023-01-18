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

namespace BanditBB\Types;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

abstract class Types {

    protected static $constCacheArray = null;

    public static function getConstants() {
        if ( self::$constCacheArray == null ) self::$constCacheArray = [];

        $calledClass = \get_called_class();

        if ( ! \array_key_exists( $calledClass, self::$constCacheArray ) ) {
            $reflect = new \ReflectionClass( $calledClass );
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }

        return self::$constCacheArray[$calledClass];
    }

    public static function isValidName( $name, $strict = false) {
        $constants = self::getConstants();

        if ( $strict ) return \array_key_exists( $name, $constants );

        $keys = \array_map( 'strtolower', \array_keys( $constants ) );

        return \in_array( \strtolower( $name ), $keys );
    }

    public static function isValidValue( $value, $strict = false ) {
        $values = \array_values( self::getConstants() );
        return \in_array( $value, $values, $strict );
    }
}