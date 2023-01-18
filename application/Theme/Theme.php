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

namespace BanditBB\Theme;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Theme extends \BanditBB\Application {

    protected static $instance;

    public function __construct() {
        self::member()->populateVarsUsingOutput([
            'noPhotoClass'               => self::getThemePartial( 'Global', 'Class', 'NoPhoto' ),
            'noPhotoThumbnailClass'      => self::getThemePartial( 'Global', 'Class', 'NoPhotoThumbnail' ),
            'photoClass'                 => self::getThemePartial( 'Global', 'Class', 'Photo' ),
            'photoThumbnailClass'        => self::getThemePartial( 'Global', 'Class', 'PhotoThumbnail' ),
            'photoThumbnailTinyClass'    => self::getThemePartial( 'Global', 'Class', 'PhotoThumbnailTiny' )
        ]);
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getThemeBase() {
        return self::file()->readFile( self::member()->themePath() . 'html/Base.html' );
    }

    public static function getThemePrint() {
        return self::file()->readFile( self::member()->themePath() . 'html/PrintBase.html' );
    }

    public static function getTheme( $controller, $action ) {
        return self::file()->readFile( self::member()->themePath() . 'html/' . $controller . '/' . $controller . '-' . $action . '.html' );
    }

    public static function getThemePartial( $controller, $action, $partial ) {
        return self::file()->readFile( self::member()->themePath() . 'html/' . $controller . '/' . $controller . '-' . $action . '-' . $partial . '.html' );
    }
}