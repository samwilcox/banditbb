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

namespace BanditBB\Math;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Math extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function calculateAge( $month, $day, $year ) {
        $time = \mktime( 0, 0, 0, $month, $day, $year );
        $calc = ( $time < 0 ) ? ( \time() + ( $time * - 1 ) ) : \time() - $time;
        $year = 60 * 60 * 24 * 365;

        return \floor( $calc / $year );
    }

    public static function formatNumber( $number ) {
        if ( $number > 1000 )
        {
            $x = \round( $number );
            $xNumberFormat = \number_format( $x );
            $xArray = \explode( ',', $xNumberFormat );
            $xParts = [ 'K', 'M', 'B', 'T' ];
            $xCountParts = \count( $xArray ) - 1;
            $xDisplay = $x;
            $xDisplay = $xArray[0] . ( ( int ) $xArray[1][0] !== 0 ? '.' . $xArray[1][0] : '' );
            $xDisplay = $xDisplay . $xParts[$xCountParts - 1];

            return $xDisplay;
        }

        return $number;
    }

    public static function calculateDebugPercentage( $executionTimer, $databaseTimer ) {
        $retVal = new \stdClass();

        $total = $executionTimer;
        $pageTime = $executionTimer - $databaseTimer;

        $retVal->database = \round( ( $databaseTimer / $total ) * 100, 1 ) . '%';
        $retVal->page = \round( ( $pageTime / $total ) * 100, 1 ) . '%';

        return $retVal;
    }
}