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

class Cache {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) {
            switch ( CACHE ) {
                case true:
                    switch ( CACHE_METHOD ) {
                        case 'filecache':
                            self::$instance = \BanditBB\Data\Cache\FileCache::i();
                            break;

                        case 'dbcache':
                            self::$instance = \BanditBB\Data\Cache\DbCache::i();
                            break;

                        case 'sqlcache':
                            require ( APP_PATH . 'Config.inc.php' );
                            $connInfo = isset( $cfg ) ? $cfg : [];

                            if ( $connInfo['dbDriver'] == 'mysqli' ) {
                                if ( \extension_loaded( 'mysqlnd' ) ) {
                                    self::$instance = \BanditBB\Data\Cache\SqlCache::i();
                                } else {
                                    self::$instance = \BanditBB\Data\Cache\NoCache::i();
                                }
                            } else {
                                self::$instance = \BanditBB\Data\Cache\NoCache::i();
                            }
                            break;

                        case 'memcache':
                            if ( \class_exists( 'Memcache' ) ) {
                                self::$instance = \BanditBB\Data\Cache\MemCache::i();
                            } else {
                                self::$instance = \BanditBB\Data\Cache\NoCache::i();
                            }
                            break;

                        case 'rediscache':
                            if ( \class_exists( 'Redis' ) ) {
                                self::$instance = \BanditBB\Data\Cache\RedisCache::i();
                            } else {
                                self::$instance = \BanditBB\Data\Cache\NoCache::i();
                            }
                            break;
                    }
                    break;

                case false:
                    self::$instance = \BanditBB\Data\Cache\NoCache::i();
                    break;
            }
        }

        return self::$instance;
    }
}