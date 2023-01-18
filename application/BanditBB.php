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

class Application {

    public static function run() {
        $timer = \microtime( true );

        require_once ( 'Static.php' );

        \ignore_user_abort( true );
        \date_default_timezone_set( 'UTC' );
        \spl_autoload_register( 'self::autoloader', true, true );

        \BanditBB\Core\Settings::i();
        \BanditBB\Data\Database::i()->connect();
        \BanditBB\Data\Cache::i()->build();
        \BanditBB\Core\Settings::i()->populateSettings();
        \BanditBB\Core\Settings::i()->urlsToSettings();
        \BanditBB\Core\Request::i();
        \BanditBB\Core\Vars::i()->executionTimerStart = $timer;
        \BanditBB\Core\Session::i()->management();
        \BanditBB\Users\Member::i();
        \BanditBB\Localization\Localization::i();
        \BanditBB\Core\Registry::i();

        $controller = isset( \BanditBB\Core\Request::i()->controller ) ? \ucfirst( \BanditBB\Core\Request::i()->controller ) : 'Forums';
        $controller = $controller . 'Controller';
        $controllerNs = '\\BanditBB\\Controllers\\' . $controller;
        $action = isset( \BanditBB\Core\Request::i()->action ) ? \ucfirst( \BanditBB\Core\Request::i()->action ) : 'index';

        $obj = new $controllerNs();
        $obj->$action();

        \session_write_close();
        \BanditBB\Data\Database::i()->disconnect();
    }

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

    protected static function agent() { return \BanditBB\Core\Agent::i(); }
    protected static function cookies() { return \BanditBB\Core\Cookies::i(); }
    protected static function dateTime() { return \BanditBB\Core\DateTime::i(); }
    protected static function globals() { return \BanditBB\Core\Globals::i(); }
    protected static function registry() { return \BanditBB\Core\Registry::i(); }
    protected static function request() { return \BanditBB\Core\Request::i(); }
    protected static function session() { return \BanditBB\Core\Session::i(); }
    protected static function settings() { return \BanditBB\Core\Settings::i(); }
    protected static function vars() { return \BanditBB\Core\Vars::i(); }
    protected static function db() { return \BanditBB\Data\Database::i(); }
    protected static function queries() { return \BanditBB\Data\Queries::i(); }
    protected static function cache() { return \BanditBB\Data\Cache::i(); }
    protected static function file() { return \BanditBB\Files\File::i(); }
    protected static function localization() { return \BanditBB\Localization\Localization::i(); }
    protected static function math() { return \BanditBB\Math\Math::i(); }
    protected static function output() { return \BanditBB\Output\Output::i(); }
    protected static function theme() { return \BanditBB\Theme\Theme::i(); }
    protected static function redirect() { return \BanditBB\Url\Redirect::i(); }
    protected static function seo() { return \BanditBB\Url\Seo::i(); }
    protected static function url() { return \BanditBB\Url\Url::i(); }
    protected static function member() { return \BanditBB\Users\Member::i(); }
    protected static function barHelper() { return null; }

    /**
     * End of application references
     */
}