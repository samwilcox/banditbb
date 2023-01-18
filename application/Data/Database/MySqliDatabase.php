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

namespace BanditBB\Data\Database;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class MySqliDatabase implements \BanditBB\Data\DatabaseStructure {

    protected static $instance;
    protected static $params;

    public function __construct() {
        self::$params = new \stdClass();

        require ( APP_PATH . 'Config.inc.php' );
        self::$params->connInfo = isset( $cfg ) ? $cfg : [];
        self::$params->dbPrefix = self::$params->connInfo['dbPrefix'];

        self::$params->totalQueries = 0;
        self::$params->handle = null;
        self::$params->time = new \stdClass();
        self::$params->time->start = null;
        self::$params->time->result = null;
        self::$params->lastQuery = '';
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function connect() {
        self::$params->handle = new \mysqli(
            self::$params->connInfo['dbHost'],
            self::$params->connInfo['dbUsername'],
            self::$params->connInfo['dbPassword'],
            self::$params->connInfo['dbName'],
            self::$params->connInfo['dbPort']
        );

        if ( self::$params->handle->connect_error ) {
            self::fatalError();
        }

        return self::$params->handle;
    }

    public static function query( $query, $params = null ) {
        self::startStopExecutionTimer( true );
        self::$params->lastQuery = $query;

        if ( $params != null ) {
            foreach ( $params as $k => $v ) {
                $query = \str_replace( '{{' . $k . '}}', self::escapeString( $v ), $query );
            }
        }

        if ( ! $statement = self::$params->handle->query( $query ) ) {
            self::fatalError();
        }

        self::$params->totalQueries++;

        self::startStopExecutionTimer( false );

        return $statement;
    }

    public static function multiQuery( $query, $params = null ) {
        self::startStopExecutionTimer( true );
        self::$params->lastQuery = $query;

        if ( $params != null ) {
            foreach ( $params as $k => $v ) {
                $query = \str_replace( '{{' . $k . '}}', self::escapeString( $v ), $query );
            }
        }

        if ( ! $statement = self::$params->handle->multi_query( $query ) ) {
            self::fatalError();
        }

        self::$params->totalQueries++;

        self::startStopExecutionTimer( false );

        return $statement;
    }

    public static function executeTransaction( $queries = [] ) {
        self::startStopExecutionTimer( true );

        try {
            self::$params->handle->autocommit( false );

            foreach ( $queries as $item ) {
                $query = $item['query'];

                if ( $item['params'] != null ) {
                    if ( \count( $item['params'] ) > 0 ) {
                        foreach ( $item['params'] as $k => $v ) {
                            $query = \str_replace( '{{' . $k . '}}', self::escapeString( $v ), $query );
                        }
                    } 
                }

                if ( ! $statement = self::$params->handle->query( $query ) ) {
                    throw new Exception();
                }
            }

            self::$params->totalQueries = ( self::$params->totalQueries + \count( $queries ) );
        } catch ( \Exception $e ) {
            self::$params->handle->rollBack();
            self::$params->totalQueries = ( self::$params->totalQueries - \count( $queries ) );
            self::fatalError();
        } finally {
            self::$params->handle->autocommit( true );
        }

        self::startStopExecutionTimer( false );
    }

    public static function fetchObject( $resource ) {
        return $resource->fetch_object();
    }

    public static function fetchArray( $resource ) {
        return $resource->fetch_array();
    }

    public static function fetchAssoc( $resource ) {
        return $resource->fetch_assoc();
    }

    public static function numRows( $resource ) {
        return $resource->num_rows;
    }

    public static function freeResult( $resource ) {
        return $resource->free_result();
    }

    public static function insertId() {
        return self::$params->handle->insert_id;
    }

    public static function affectedRows() {
        return self::$params->handle->affected_rows;
    }

    public static function escapeString( $string ) {
        return self::$params->handle->real_escape_string( $string );
    }

    public static function disconnect() {
        if ( self::$instance ) {
            \mysqli_close( self::$params->handle );
            self::$instance = null;
            return;
        }
    }

    public static function dbPrefix() {
        return self::$params->dbPrefix;
    }

    public static function totalQueries() {
        return self::$params->totalQueries;
    }

    public static function executionTime() {
        return self::$params->time->result;
    }

    public static function databaseVersion() {
        return \mysqli_get_server_version( self::$params->handle );
    }

    private static function startStopExecutionTimer( $mode ) {
        switch ( $mode ) {
            case true:
                self::$params->time->start = \microtime( true );
                break;

            case false:
                self::$params->time->result = self::$params->time->result + ( \microtime( true ) - self::$params->time->start );
                break;
        }
    }

    private static function fatalError() {
        throw new \BanditBB\Core\Exceptions\DatabaseException( self::$params->handle->error, self::$params->handle->errno );
    }
}