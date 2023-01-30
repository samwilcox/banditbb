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

namespace BanditBB\Widgets;

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

    public static function getWidgets( $controller = 'forums', $action = 'index', $section = 'top' ) {
        $data = self::cache()->massGetData( [ 'widgets' => 'widgets', 'placement' => 'widget_placement' ] );
        $found = false;
        $placements = [];
        $widgetPlacement = '';

        foreach ( $data->placement as $place ) {
            if ( $place->controller == $controller && $place->action == $action && $place->section == $section ) {
                $placements[$place->sortOrder] = $place->placementId;
            }
        }

        foreach ( $data->placement as $place ) {
            if ( $place->controller == 'all' && $place->action == 'all' && $place->section == $section ) {
                $placements[$place->sortOrder] = $place->placementId;
            }
        }

        \ksort( $placements );

        foreach ( $placements as $k => $v ) {
            foreach ( $data->placement as $place ) {
                if ( $place->placementId == $v ) {
                    foreach ( $data->widgets as $widget ) {
                        if ( $widget->widgetId == $place->widgetId ) {
                            $widgetTitle = $widget->title;
                        }
                    }

                    $widgetOutput .= self::getWidget( $widgetTitle );
                }
            }
        }

        if ( \strlen( $widgetOutput ) < 1 ) {
            return null;
        }

        return $widgetOutput;
    }

    public static function haveWidgets( $controller = 'forums', $action = 'index', $section = 'top' ) {
        $data = self::cache()->massGetData( [ 'widgets' => 'widgets', 'placement' => 'widget_placement' ] );
        $found = false;
        $totals = new \stdClass();
        $totals->top = 0;
        $totals->bottom = 0;
        $totals->left = 0;
        $totals->right = 0;
        $lists = new \stdClass();
        $lists->top = [];
        $lists->bottom = [];
        $lists->left = [];
        $lists->right = [];

        foreach ( $data->placement as $place ) {
            if ( ( $controller == $place->controller || $place->controller == 'all' ) && ( $action == $place->action || $place->action == 'all' ) && $section == $place->section ) {
                if ( $place->section == 'top' ) {
                    $totals->top++;

                    foreach ( $data->widgets as $widget ) {
                        if ( $widget->widgetId == $place->widgetId ) {
                            $lists->top[] = $widget->title;
                        }
                    }
                }

                if ( $place->section == 'bottom' ) {
                    $totals->bottom++;

                    foreach ( $data->widgets as $widget ) {
                        if ( $widget->widgetId == $place->widgetId ) {
                            $lists->bottom[] = $widget->title;
                        }
                    }
                }

                if ( $place->section == 'left' ) {
                    $totals->left++;

                    foreach ( $data->widgets as $widget ) {
                        if ( $widget->widgetId == $place->widgetId ) {
                            $lists->left[] = $widget->title;
                        }
                    }
                }

                if ( $place->section == 'right' ) {
                    $totals->right++;

                    foreach ( $data->widgets as $widget ) {
                        if ( $widget->widgetId == $place->widgetId ) {
                            $lists->right[] = $widget->title;
                        }
                    }
                }
            }
        }

        if ( $section == 'top' && $totals->top == 1 && $lists->top[0] == 'welcomeBanner' && self::member()->signedIn() ) {
            return false;
        }

        if ( $section == 'bottom' && $totals->bottom == 1 && $lists->bottom[0] == 'welcomeBanner' && self::member()->signedIn() ) {
            return false;
        }

        if ( $section == 'left' && $totals->left == 1 && $lists->left[0] == 'welcomeBanner' && self::member()->signedIn() ) {
            return false;
        }

        if ( $section == 'right' && $totals->right == 1 && $lists->right[0] == 'welcomeBanner' && self::member()->signedIn() ) {
            return false;
        }

            foreach ( $data->placement as $place ) {
                if ( $controller == $place->controller && $action == $place->action && $section == $place->section ) {
                    $found = true;
                }
            }

        if ( ! $found ) {
            foreach ( $data->placement as $place ) {
                if ( $place->controller == 'all' && $place->action == 'all' && $section == $place->section ) {
                    $found = true;
                }
            }
        }

        return $found;
    }

    public static function getWidget( $widget ) {
        return self::widgetsHelper()->getRequestedWidget( $widget );
    }
}