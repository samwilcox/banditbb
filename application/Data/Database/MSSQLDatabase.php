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

class MSSQLDatabase implements \BanditBB\Data\DatabaseStructure {

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
        self::$params->handle = \sqlsrv_connect(
            self::$params->connInfo['dbHost'], [
                'UID'      => self::$params->connInfo['dbUsername'],
                'PWD'      => self::$params->connInfo['dbPassword'],
                'Database' => self::$params->connInfo['dbName'],
                'Port'     => self::$params->connInfo['dbPort']
            ]
        );

        if ( ! self::$params->handle ) {
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

        if ( ! $statement = \sqlsrv_query( self::$params->handle, $query ) ) {
            self::fatalError();
        }

        self::$params->totalQueries++;

        self::startStopExecutionTimer( false );

        return $statement;
    }

    public static function executeTransaction( $queries = [] ) {
        self::startStopExecutionTimer( true );

        if ( ! \sqlsrv_begin_transaction( self::$params->handle ) ) {
            self::fatalError();
        }

        try {
            foreach ( $queries as $item ) {
                $query = $item['query'];

                if ( $item['params'] != null ) {
                    if ( \count( $item['params'] ) > 0 ) {
                        foreach ( $item['params'] as $k => $v ) {
                            $query = \str_replace( '{{' . $k . '}}', self::escapeString( $v ), $query );
                        }
                    } 
                }

                if ( ! $statement = \sqlsrv_query( self::$params->handle, $query ) ) {
                    throw new Exception();
                }
            }

            self::$params->totalQueries = ( self::$params->totalQueries + \count( $queries ) );
            \sqlsrv_commit( self::$params->handle );
        } catch ( \Exception $e ) {
            \sqlsrv_rollback( self::$params->handle );
            self::$params->totalQueries = ( self::$params->totalQueries - \count( $queries ) );
            self::fatalError();
        }

        self::startStopExecutionTimer( false );
    }

    public static function fetchObject( $resource ) {
        return \sqlsrv_fetch_object( $resource );
    }

    public static function fetchArray( $resource ) {
        return \sqlsrv_fetch_array( $resource );
    }

    public static function fetchAssoc( $resource ) {
        return \sqlsrv_fetch_array( $resource, SQLSRV_FETCH_ASSOC );
    }

    public static function numRows( $resource ) {
        return \sqlsrv_num_rows( $resource );
    }

    public static function freeResult( $resource ) {
        return \sqlsrv_free_stmt( $resource );
    }

    public static function insertId() {
        $sql = self::query( "SELECT SCOPE_IDENTITY() AS ins_id" );
        $row = self::fetchObject( $sql );
        $id = $row->ins_id;
        self::freeResult( $sql );

        return $id;
    }

    public static function affectedRows() {
        return \sqlsrv_rows_affected( self::$params->handle );
    }

    public static function escapeString( $string ) {
        return \mysqli_real_escape_string( $string );
    }

    public static function disconnect() {
        if ( self::$instance ) {
            \sqlsrv_close( self::$params->handle );
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

    public static function dbVersion() {
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