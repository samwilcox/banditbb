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

class Settings extends \BanditBB\Application {

    protected static $instance;
    protected static $vars = [];

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function populateSettings() {
        $data = self::cache()->getData( 'application_settings' );

        foreach ( $data as $setting ) {
            if ( $setting->valueType == 'bool' ) {
                self::$vars[$setting->settingKey] = ( \strtolower( $setting->settingValue ) == 'true' ) ? true : false;
            } elseif ( $setting->valueType == 'int' ) {
                self::$vars[$setting->settingKey] = (int) $setting->settingValue;
            } elseif ( $setting->valueType == 'json' ) {
                self::$vars[$setting->settingKey] = \strlen( $setting->settingValue ) > 0 ? \json_decode( $setting->settingValue ) : '';
            } else if ( $setting->valueType == 'serialized' ) {
                self::$vars[$setting->settingKey] = \strlen( $setting->settingValue ) > 0 ? \unserialize( $setting->settingValue ) : '';
            } else {
                self::$vars[$setting->settingKey] = $setting->settingValue;
            }
        }
    }

    public static function urlsToSettings() {
        $appUrl = APP_URL;

        if ( \substr( $appUrl, \strlen( $appUrl ) - 1, \strlen( $appUrl ) ) == '/' ) {
            self::vars()->baseUrl = \substr( $appUrl, 0, \strlen( $appUrl ) - 1 );
        } else {
            self::vars()->baseUrl = $appUrl;
        }
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