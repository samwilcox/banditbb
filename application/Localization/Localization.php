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

namespace BanditBB\Localization;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Localization extends \BanditBB\Application {

    protected static $instance;
    protected static $lang = [];

    public function __construct() {
        self::load();
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    private static function load() {
        $data = self::cache()->getData( 'localization' );

        foreach ( $data as $localization ) {
            if ( $localization->packageId == self::member()->localizationId() ) {
                self::$lang[$localization->category][$localization->stringId] = $localization->stringData;
            }
        }
    }

    public static function getFullCategory( $category ) {
        return self::$lang[$category];
    }

    public static function getWords( $category, $stringId ) {
        return self::$lang[$category][$stringId];
    }

    public static function wordReplace( $words, $toReplace, $replacement ) {
        return \str_replace( '{{' . $toReplace . '}}', $replacement, $words );
    }

    public static function quickReplace( $category, $stringId, $toReplace, $replacement ) {
        return self::wordReplace( self::$lang[$category][$stringId], $toReplace, $replacement );
    }

    public static function quickReplaceSpecificId( $localizationId, $category, $stringId, $toReplace, $replacement ) {
        return self::wordReplace( self::getWordsSpecificId( $category, $stringId, $localizationId ), $toReplace, $replacement );
    }

    public static function quickMultiWordReplaceSpecificId( $localizationId, $category, $stringId, $replacementList = [] ) {
        $retVal = self::getWordsSpecificId( $category, $stringId, $localizationId );

        foreach ( $replacementList as $k => $v ) {
            $retVal = self::wordReplace( $retVal, $k, $v );
        }

        return $retVal;
    }

    public static function quickMultiWordReplace( $category, $stringId, $replacementList = [] ) {
        $retVal = self::$lang[$category][$stringId];

        foreach ( $replacementList as $k => $v ) {
            $retVal = self::wordReplace( $retVal, $k, $v );
        }

        return $retVal;
    }

    public static function multiWordReplace( $words, $replacementList = [] ) {
        $retVal = $words;

        foreach ( $replacementList as $k => $v ) {
            $retVal = self::wordReplace( $retVal, $k, $v );
        }

        return $retVal;
    }

    public static function getWordsSpecificId( $category, $stringId, $localizationId ) {
        $data = self::cache()->getData( 'localization' );
        $local = [];

        foreach ( $data as $localization ) {
            if ( $localization->packageId == $localizationId ) {
                $local[$localization->category][$localization->stringId] = $localization->stringData;
            }
        }

        return $local[$category][$stringId];
    }

    public static function outputWordsReplacement( &$output ) {
        if ( ! $output ) return;

        \preg_match_all( '/{([a-zA-Z0-9]+?)\.([a-zA-Z0-9]+?)\.([a-zA-Z0-9]+?)}/is', $output, $matches );

        $replacementList = [];

        for ( $i = 0; $i < \count( $matches[0] ); $i++ ) {
            $replacementList[] = $matches[0][$i];
        }

        foreach ( $replacementList as $bit ) {
            \preg_match( '/{([a-zA-Z0-9]+?)\.([a-zA-Z0-9]+?)\.([a-zA-Z0-9]+?)}/is', $bit, $matches );

            $output = \str_replace( $bit, self::getWords( $matches[2], $matches[3] ), $output );
        }
    }

    public static function wordsReplacement( $content, $localizationId = null ) {
        if ( ! $content ) return;

        \preg_match_all( '/{([a-zA-Z0-9]+?)\.([a-zA-Z0-9]+?)\.([a-zA-Z0-9]+?)}/is', $content, $matches );

        $replacementList = [];

        for ( $i = 0; $i < count( $matches[0] ); $i++ ) {
            $replacementList[] = $matches[0][$i];
        }

        foreach ( $replacementList as $bit ) {
            \preg_match( '/{([a-zA-Z0-9]+?)\.([a-zA-Z0-9]+?)\.([a-zA-Z0-9]+?)}/is', $bit, $matches );

            $content = \str_replace( $bit, $localizationId == null ? self::getWords( $matches[2], $matches[3] ) : self::getWordsSpecificId( $matches[2], $matches[3], $localizationId ), $content );
        }

        return $content;
    }
}