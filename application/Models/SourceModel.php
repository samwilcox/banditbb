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

namespace BanditBB\Models;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class SourceModel extends \BanditBB\Models\BaseModel {

    private static $vars = [];

    public function getCssSource() {
        $cssFile = self::member()->themePath() . 'css/' . \strtolower( self::request()->file ) . '.css';

        if ( \file_exists( $cssFile ) ) {
            return self::file()->readFile( $cssFile );
        }
    }

    public function getJsSource() {
        $jsFile = ROOT_PATH . 'public/js/' . ( isset( self::request()->thirdparty ) ? '3rdparty/' : '' ) . \strtolower( self::request()->file ) . '.js';

        if ( \file_exists( $jsFile ) ) {
            return self::file()->readFile( $jsFile );
        }
    }
}