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

class Registry extends \BanditBB\Application {

    protected static $instance;
    protected static $vars = [];
    protected static $currentType;

    public function __construct() {
        self::load();
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    private static function load() {
        $data = self::cache()->getData( 'registry' );

        foreach ( $data as $reg ) {
            if ( $reg->valueType == 'bool' ) {
                self::$vars[$reg->keyName] = $reg->keyValue == 'true' ? true : false;
            } elseif ( $reg->valueType == 'serialized' ) {
                self::$vars[$reg->keyName] = \strlen( $reg->keyValue ) > 0 ? \unserialize( $reg->keyValue ) : null;
            } elseif ( $reg->valueType == 'json' ) {
                self::$vars[$reg->keyName] = \srlen( $reg->keyValue ) > 0 ? \json_decode( $reg->keyValue ) : null;
            } elseif ( $reg->valueType == 'int' ) {
                self::$vars[$reg->keyName] = (int) $reg->keyValue;
            } else {
                self::$vars[$reg->keyName] = $reg->keyValue;
            }
        }

        self::$currentType = 'string';
    }

    public static function saveKeyValuePair( $key, $value, $type = 'string' ) {
        $data = self::cache()->getData( 'registry' );
        $exists = false;
        $id = null;

        foreach ( $data as $reg ) {
            if ( $reg->keyName == $key ) {
                $exists = true;
                $id = $reg->registryId;
                break;
            }
        }

        if ( $exists ) {
            self::db()->query( self::queries()->updateRegistry(), [ 'id' => $id, 'value' => $value, 'type' => $type ] );
        } else {
            self::db()->query( self::queries()->insertIntoRegistry(), [ 'name' => $key, 'value' => $value, 'type' => $type ] );
        }

        self::cache()->update( 'registry' );
    }

    public function __set( $key, $value ) {
        self::saveKeyValuePair( $key, $value, self::$currentType );
    }

    public function __get( $key ) {
        if ( \array_key_exists( $key, self::$vars ) ) {
            return self::$vars[$key];
        }

        return null;
    }

    public function __isset( $key ) {
        if ( \array_key_exists( $key, self::$vars ) ) {
            return true;
        } else {
            return false;
        }
    }

    public static function setType( $type ) {
        self::$currentType = $type;
    }
}