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

namespace BanditBB\Data\Cache;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class RedisCache extends \BanditBB\Data\Cache\DataCache implements \BanditBB\Data\CacheStructure {

    protected static $instance;
    protected static $cache = [];
    protected static $redis = null;

    public function __construct() {
        try {
            self::$redis = new Redis();
            self::$redis->connect( self::settings()->redisServerHost, self::settings()->redisServerPort );
        } catch ( \Exception $e ) {
            throw new \BanditBB\Core\Exceptions\CacheException( \sprintf( 'Unable to connect to the configured Redis server at %s:%s', self::settings()->redisServerHost, self::settings()->redisServerPort ) );
        }
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    private static function sorting( $table ) {
        $sorting = null;

        foreach ( self::$sorting as $k => $v ) {
            if ( $k == $table ) {
                $sorting = $k;
                break;
            }
        }

        return $sorting;
    }

    private static function getCache( $table, $sorting ) {
        if ( $table == 'forums' ) {
            $sql = self::db()->query( self::queries()->selectForumsWithDepth() );
        } else {
            $sql = self::db()->query( self::queries()->selectForCache( [ 'table' => $table, 'sorting' => $sorting ] ) );
        }

        return $sql;
    }

    public static function build() {
        foreach ( self::$tables as $table ) {
            unset( $records );

            $sorting = self::sorting( $table );

            if ( self::$redis->get( $table ) != null ) {
                self::$cache[$table] = \json_decode( self::$redis->get( $table ) );
            } else {
                $sql = self::getCache( $table, $sorting );

                while ( $record = self::db()->fetchAssoc( $sql ) ) $records[] = $record;

                self::db()->freeResult( $sql );

                $toCache = \json_encode( $records );

                self::$redis->set( $table, $toCache );

                self::$cache[$table] = \json_decode( $toCache );
            }
        }
    }

    public static function update( $table ) {
        $sorting = self::sorting( $table );
        $sql = self::getCache( $table, $sorting );

        while ( $record = self::db()->fetchAssoc( $sql ) ) $records[] = $record;

        self::db()->freeResult( $sql );

        $toCache = \json_encode( $records );

        self::$redis->set( $table, $toCache );

        self::$cache[$table] = \json_decode( $toCache );
    }

    public static function massUpdate( $tables = [] ) {
        if ( \count( $tables ) > 0 ) {
            foreach ( $tables as $table ) {
                self::update( $table );
            }
        }
    }

    public static function getData( $table ) {
        return ( \count( self::$cache[$table] != null ? self::$cache[$table] : [] ) > 0 ) ? self::$cache[$table] : [];
    }

    public static function massGetData( $tables = [] ) {
        $retVal = new \stdClass();

        if ( \is_array( $tables ) && \count( $tables ) > 0 ) {
            foreach ( $tables as $name => $table ) {
                if ( \count( self::$cache[$table] != null ? self::$cache[$table] : [] ) > 0 ) {
                    $retVal->$name = self::$cache[$table];
                } else {
                    $retVal->$name = [];
                }
            }
        }

        return $retVal;
    }
}