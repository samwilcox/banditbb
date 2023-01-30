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

namespace BanditBB\Helpers;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Widgets extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getRequestedWidget( $widget ) {
        $data = self::cache()->getData( 'widgets' );
        $found = false;

        switch ( $widget ) {
            case 'welcome':
                return self::welcomeWidget();
                break;
        }
    }

    public static function getWidgetOutputVars( $controller, $action ) {
        $retVal = [];

        $retVal['blockBegin']       = ( self::widgets()->haveWidgets( $controller, $action, 'left' ) || self::widgets()->haveWidgets( $controller, $action, 'right' ) ) ? self::output()->getPartial( 'WidgetsHelper', 'Blocks', 'Begin' ) : '';
        $retVal['blockEnd']         = ( self::widgets()->haveWidgets( $controller, $action, 'left' ) || self::widgets()->haveWidgets( $controller, $action, 'right' ) ) ? self::output()->getPartial( 'WidgetsHelper', 'Blocks', 'End' ) : '';
        $retVal['blockLeftBegin']   = self::widgets()->haveWidgets( $controller, $action, 'left' ) ? self::output()->getPartial( 'WidgetsHelper', 'Blocks', 'LeftBegin' ) : '';
        $retVal['blockLeftEnd']     = self::widgets()->haveWidgets( $controller, $action, 'left' ) ? self::output()->getPartial( 'WidgetsHelper', 'Blocks', 'LeftEnd' ) : '';
        $retVal['blockCenterBegin'] = ( self::widgets()->haveWidgets( $controller, $action, 'left' ) || self::widgets()->haveWidgets( $controller, $action, 'right' ) ) ? self::output()->getPartial( 'WidgetsHelper', 'Blocks', 'CenterBegin' ) : '';
        $retVal['blockCenterEnd']   = ( self::widgets()->haveWidgets( $controller, $action, 'left' ) || self::widgets()->haveWidgets( $controller, $action, 'right' ) ) ? self::output()->getPartial( 'WidgetsHelper', 'Blocks', 'CenterEnd' ) : '';
        $retVal['blockRightBegin']  = self::widgets()->haveWidgets( $controller, $action, 'right' ) ? self::output()->getPartial( 'WidgetsHelper', 'Blocks', 'RightBegin' ) : '';
        $retVal['blockRightEnd']    = self::widgets()->haveWidgets( $controller, $action, 'right' ) ? self::output()->getPartial( 'WidgetsHelper', 'Blocks', 'RightEnd' ) : '';
        $retVal['leftWidgets']      = self::widgets()->haveWidgets( $controller, $action, 'left' ) ? self::widgets()->getWidgets( $controller, $action, 'left' ) : '';
        $retVal['rightWidgets']     = self::widgets()->haveWidgets( $controller, $action, 'right' ) ? self::widgets()->getWidgets( $controller, $action, 'right' ) : '';
        $retVal['bottomWidgets']    = self::widgets()->haveWidgets( $controller, $action, 'bottom' ) ? self::widgets()->getWidgets( $controller, $action, 'bottom' ) : '';
        $retVal['topWidgets']       = self::widgets()->haveWidgets( $controller, $action, 'top' ) ? self::widgets()->getWidgets( $controller, $action, 'top' ) : '';
        $retVal['bottomSeperator']  = self::widgets()->haveWidgets( $controller, $action, 'bottom' ) ? self::output()->getPartial( 'WidgetsHelper', 'Widget', 'Spacer' ) : '';
        $retVal['topSeperator']     = self::widgets()->haveWidgets( $controller, $action, 'top' ) ? self::output()->getPartial( 'WidgetsHelper', 'Widget', 'Spacer' ) : '';

        return $retVal;
    }

    private static function welcomeWidget() {
        return self::output()->getPartial(
            'WidgetsHelper',
            'Welcome',
            'Widget',
            [
                'message' => self::localization()->quickMultiWordReplace( 'widgetshelper', 'welcomeMessage', [
                    'SignInLink'        => self::output()->getPartial(
                        'Global',
                        'Link',
                        'SignIn',
                        [
                            'url' => self::seo()->seoUrl( 'authentication' )
                        ]
                    ),
                    'CreateAccountLink' => self::settings()->accountCreationEnabled ? self::output()->getPartial( 'Global', 'Link', 'CreateAccount', [ 'url' => self::seo()->seoUrl( 'createaccount' ) ] ) : self::localization()->getWords( 'global', 'accountCreationDisabledSmall' )
                ])
            ]
        );
    }
}