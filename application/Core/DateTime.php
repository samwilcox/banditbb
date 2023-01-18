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

class DateTime extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    private static function getTimeAgo( $before, $now ) {
        $calculation = $now - $before;
        $exit = false;

        if ( $calculation == 0 ) {
            $type = 'justnow';
        } elseif ( $calculation < 60 && $calculation > 0 ) {
            $type = 'seconds';
        } elseif ( $calculation < ( 60 * 60 ) && $calculation > 60 ) {
            $type = 'minutes';
        } elseif ( $calculation < ( 60 * 60 * 24 ) && $calculation > ( 60 * 60 ) ) {
            $type = 'hours';
        } elseif ( $calculation < ( ( 60 * 60 * 24 ) * 7 ) && $calculation > ( 60 * 60 * 24 ) ) {
            $type = 'days';
        } else {
            $exit = true;
        }

        if ( $exit ) return null;

        switch ( $type ) {
            case 'justnow':
                $retVal = self::localization()->getWords( 'global', 'justNow' );
                break;

            case 'seconds':
                $retVal  = $calculation;
                $retVal .= ( $calculation > 1 || $calculation < 1 ) ? ' ' . self::localization()->getWords( 'global', 'seconds' ) : ' ' . self::localization()->getWords( 'global', 'second' );
                $retVal .= ' ' . self::localization()->getWords( 'global', 'ago' );
                break;

            case 'minutes':
                $retVal  = \round( $calculation / 60 );
                $retVal .= ( $retVal > 1 || $retVal < 1 ) ? ' ' . self::localization()->getWords( 'global', 'minutes' ) : ' ' . self::localization()->getWords( 'global', 'minute' );
                $retVal .= ' ' . self::localization()->getWords( 'global', 'ago' );
                break;

            case 'hours':
                $retVal  = \round( $calculation / 60 / 60 );
                $retVal .= ( $retVal > 1 || $retVal < 1 ) ? ' ' . self::localization()->getWords( 'global', 'hours' ) : ' ' . self::localization()->getWords( 'global', 'hour' );
                $retVal .= ' ' . self::localization()->getWords( 'global', 'ago' );
                break;

            case 'days':
                $retVal  = \round( $calculation / 60 / 60 / 24 );
                $retVal .= ( $retVal > 1 || $retVal < 1 ) ? ' ' . self::localization()->getWords( 'global', 'days' ) : ' ' . self::localization()->getWords( 'global', 'day' );
                $retVal .= ' ' . self::localization()->getWords( 'global', 'ago' );
                break;
        }

        return $retVal;
    }

    public static function convertStringToTimestamp( $string ) {
        $retVal = null;
        
        switch ( $string ) {
            case '1day':
            $retVal = \strtotime( '1 day ago' );
            break;
            
            case '2days':
            $retVal = \strtotime( '2 days ago' );
            break;
            
            case '3days':
            $retVal = \strtotime( '3 days ago' );
            break;
            
            case '4days':
            $retVal = \strtotime( '4 days ago' );
            break;
            
            case '5days':
            $retVal = \strtotime( '5 days ago' );
            break;
            
            case '6days':
            $retVal = \strtotime( '6 days ago' );
            break;
            
            case '1week':
            $retVal = \strtotime( '1 week ago' );
            break;
            
            case '2weeks':
            $retVal = \strtotime( '2 weeks ago' );
            break;
            
            case '3weeks':
            $retVal = \strtotime( '3 weeks ago' );
            break;
            
            case '1month':
            $retVal = \strtotime( '1 month ago' );
            break;
            
            case '3months':
            $retVal = \strtotime( '3 months ago' );
            break;
            
            case '6months':
            $retVal = \strtotime( '6 months ago' );
            break;
            
            case '9months':
            $retVal = \strtotime( '9 months ago' );
            break;
            
            case '1year':
            $retVal = \strtotime( '1 year ago' );
            break;
            
            case '2years':
            $retVal = \strtotime( '2 years ago' );
            break;
        }
        
        return $retVal;
    }

    public static function parse( $timestamp, $options = [] ) {
        $dateOnly = false;
        $timeOnly = false;
        $timeAgo = false;
        $specificMember = null;

        if ( \count( $options ) > 0 ) {
            foreach ( $options as $k => $v ) {
                if ( $k == 'dateOnly' && $v == true ) {
                    $dateOnly = true;
                } elseif ( $k == 'timeOnly' && $v == true ) {
                    $timeOnly = true;
                } elseif ( $k == 'timeAgo' && $v == true ) {
                    $timeAgo = true;
                } elseif ( $k == 'specificMember' ) {
                    $specificMember = $v;
                }
            }
        }

        if ( $dateOnly ) {
            $format = self::member()->getDateFormat( $specificMember );
        } elseif ( $timeOnly ) {
            $format = self::member()->getTimeFormat( $specificMember );
        } else {
            $format = self::member()->getDateTimeFormat( $specificMember );
        }

        $memberTimeAgo = $specificMember == null ? self::member()->timeAgo() : self::member()->getTimeAgo( $specificMember );

        if ( $timeAgo ) {
            if ( $memberTimeAgo ) {
                return self::getTimeAgo( $timestamp, \time() ) != null ? self::getTimeAgo( $timestamp, \time() ) : \date( $format, $timestamp );
            } else {
                return \date( $format, $timestamp );
            }
        } else {
            return \date( $format, $timestamp );
        }
    }
}