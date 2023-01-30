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

namespace BanditBB\Output;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Output extends \BanditBB\Application {

    protected static $instance;
    protected static $httpStatusLegend = [];

    public function __construct() {
        self::populateStatusCodes();
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    private static function populateStatusCodes() {
        self::$httpStatusLegend = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        ];
    }

    public static function pageOutput( $output = '', $vars = [], $contentType = 'text/html', $httpStatusCode = 200, $httpHeaders = [] ) {
        self::localization()->outputWordsReplacement( $output );

        if ( \count( $vars ) > 0 ) {
            foreach ( $vars as $k => $v ) {
                $output = \str_replace( '{{' . $k . '}}', $v, $output );
            }
        }

        @ob_end_clean();
        $output = \ltrim( $output );

        \header( 'HTTP/1.0 ' . $httpStatusCode . ' ' . self::$httpStatusLegend[$httpStatusCode] );
        \header( 'Access-Control-Allow-Origin: *' );
        \header( 'X-BanditBB-SignIn: ' . self::member()->memberId() );

        if ( self::settings()->gzipCompressionEnabled ) {
            if ( \substr_count( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) ) {
                \ob_start( 'ob_gzhandler' );
                self::vars()->gzip = true;
            } else {
                \ob_start();
                self::vars()->gzip = false;
            }
        } else {
            \ob_start();
            self::vars()->gzip = false;
        }

        if ( ! self::settings()->pageCachingEnabled ) {
            \header( 'Cache-Control: no-store, no-cache, must-revalidate' );
            \header( 'Cache-Control: post-check=0, pre-check=0', false );
            \header( 'Pragma: no-cache' );
        }

        print $output;

        \header( 'Content-type: ' . $contentType . ';charset=UTF-8' );

        foreach ( $httpHeaders as $k => $v ) {
            \header( $k . ': ' . $v );
        }

        \header( 'Connection: close' );

        @ob_end_flush();
        @flush();

        if ( \function_exists( 'fastcgi_finish_reguest' ) ) \fastcfg_finish_request();
    }

    public static function render( $controller, $action, $vars = [] ) {
        $base = self::theme()->getThemeBase();
        $output = self::theme()->getTheme( $controller, $action );
        $base = \str_replace( '{{body}}', $output, $base );
        $globals = self::globals()->getGlobals();

        if ( \count( $globals ) > 0 ) $vars = \array_merge( $vars, $globals );

        $vars = \array_merge( $vars, self::widgetsHelper()->getWidgetOutputVars( \strtolower( $controller ), \strtolower( $action ) ) );

        self::pageOutput( $base, $vars );
    }

    public static function renderAlt( $controller, $action, $partial, $vars = [] ) {
        $base = self::theme()->getThemeBase();
        $output = self::theme()->getThemePartial( $controller, $action, $partial );
        $base = \str_replace( '{{body}}', $output, $base );
        $globals = self::globals()->getGlobals();

        if ( \count( $globals ) > 0 ) $vars = \array_merge( $vars, $globals );

        $vars = \array_merge( $vars, self::widgetsHelper()->getWidgetOutputVars( \strtolower( $controller ), \strtolower( $action ) ) );

        self::pageOutput( $base, $vars );
    }

    public static function renderError( $vars = [] ) {
        $base = self::theme()->getThemeBase();
        $output = self::theme()->getThemePartial( 'Error', 'Error', 'Message' );
        $base = \str_replace( '{{body}}', $output, $base );
        $globals = self::globals()->getGlobals();

        if ( \count( $globals ) > 0 ) $vars = \array_merge( $vars, $globals );

        $vars = \array_merge( $vars, self::widgetsHelper()->getWidgetOutputVars( \strtolower( $controller ), \strtolower( $action ) ) );

        self::pageOutput( $base, $vars );
    }

    public static function renderPartial( $controller, $action, $partial, $vars = [] ) {
        $output = self::theme()->getThemePartial( $controller, $action, $partial );
        $globals = self::globals()->getGlobals();

        if ( \count( $globals ) > 0 ) $vars = \array_merge( $vars, $globals );

        $vars = \array_merge( $vars, self::widgetsHelper()->getWidgetOutputVars( \strtolower( $controller ), \strtolower( $action ) ) );

        self::pageOutput( $base, $vars );
    } 

    public static function renderPrint( $controller, $action, $partial, $vars = [] ) {
        $base = self::theme()->getThemePrint();
        $output = self::theme()->getThemePartial( $controller, $action, $partial );
        $base = \str_replace( '{{body}}', $output, $base );
        $globals = self::globals()->getGlobals();

        if ( \count( $globals ) > 0 ) $vars = \array_merge( $vars, $globals );

        self::pageOutput( $base, $vars );
    }

    public static function renderSource( $source, $contentType ) {
        self::pageOutput( $source, [], $contentType );
    }

    public static function getPartial( $controller, $action, $partial, $vars = [] ) {
        $output = self::theme()->getThemePartial( $controller, $action, $partial );

        if ( \count( $vars ) > 0 ) {
            foreach ( $vars as $k => $v ) {
                $output = \str_replace( '{{' . $k . '}}', $v, $output );
            }
        }

        self::localization()->outputWordsReplacement( $output );

        return $output;
    }
}