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

namespace BanditBB\Files;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class File extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function createFile( $filename ) {
        if ( ! \touch( $filename ) ) self::fatalError( \sprintf( 'Could not create file %s', $filename ) );
    }

    public static function applyPermissions( $filename, $permissions ) {
        if ( ! \chmod( $filename, $permissions ) ) self::fatalError( \sprintf( 'Could not set permissions [%s] for file %s', $permissions, $filename ) );
    }

    public static function deleteFile( $filename ) {
        if ( ! \unlink( $filename ) ) self::fatalError( \sprintf( 'Could not delete file %s', $filename ) );
    }

    public static function readFile( $filename ) {
        $retVal = null;

        if ( \file_exists( $filename ) ) {
            if ( \filesize( $filename ) > 0 ) {
                $handle = @fopen( $filename, 'r' );

                if ( \flock( $handle, LOCK_SH ) ) {
                    $retVal = @fread( $handle, \filesize( $filename ) );
                    \flock( $handle, LOCK_UN );
                } else {
                    self::fatalError( \sprintf( 'Could not read file [%s] as a lock could not be obtained on the file', $filename ) );
                }
            }
        }

        return $retVal;
    }

    public static function writeFile( $filename, $data ) {
        $handle = @fopen( $filename, 'w' );

        if ( \flock( $handle, LOCK_EX ) ) {
            @ftruncate( $handle, 0 );
            @fwrite( $handle, $data );
            @fflush( $handle );
            \flock( $handle, LOCK_UN );
        } else {
            self::fatalError( \sprintf( 'Could not write data to file [%s] as a lock could not be obtained on the file', $filename ) );
        }
    }

    public static function getReadableFileSize( $bytes, $decimals = 2 ) {
        $size = [ 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' ];
        $factor = \floor( \log( $bytes, 1024 ) );

        return \sprintf( "%.{$decimals}f", $bytes / \pow( 1024, $factor ) ) . @$size[$factor];
    }

    public static function fatalError( $error ) {
        throw new \BanditBB\Core\Exceptions\FileException( $error );
    }
}