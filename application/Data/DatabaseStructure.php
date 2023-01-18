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

interface DatabaseStructure {

    public static function connect();
    public static function query( $query, $params = null );
    public static function multiQuery( $query, $params = null );
    public static function executeTransaction( $queries = [] );
    public static function fetchObject( $resource );
    public static function fetchArray( $resource );
    public static function fetchAssoc( $resource );
    public static function numRows( $resource );
    public static function freeResult( $resource );
    public static function insertId();
    public static function affectedRows();
    public static function escapeString( $string );
    public static function disconnect();
    public static function dbPrefix();
    public static function totalQueries();
    public static function executionTime();
    public static function databaseVersion();
}