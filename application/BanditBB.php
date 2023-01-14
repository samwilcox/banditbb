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

namespace BanditBB;

/**
 * BanditBB base class.
 * 
 * @package BanditBB
 * @author  Sam Wilcox <sam@banditbb.com>
 * @access  public
 */
class Application {

    /**
     * Initial application execution point. 
     * 
     * @return void
     */
    public static function run() {
        require_once ( 'Static.php' );

        \ignore_user_abort( true );
        \date_default_timezone_set( 'UTC' );
        \spl_autoload_register( 'self::autoloader', true, true );


    }

    /**
     * Application class auto-loading feature.
     * 
     * @param string $className name of the class to load
     * @return void
     */
    public static function autoloader( $className ) {
        $bits = \explode( '\\', $className );
        $class = \array_pop( $bits );

        if ( $bits[0] != 'BanditBB' ) return;

        \array_shift( $bits );

        $path = \realpath( \dirname( __FILE__ ) . '/' . \str_replace( '\\', '/', \implode( '\\', $bits ) ) . '/' . $class . '.php' );

        if ( \strlen( $path ) > 0 ) {
            require_once ( $path );
        }
    }

    /**
     * Begin of application references
     */

    

    /**
     * End of application references
     */
}