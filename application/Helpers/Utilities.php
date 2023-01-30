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

class Utilities extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function authenticationTokenHash() {
        return \sha1( \md5( \time() . self::agent()->getIpAddress() . self::agent()->getUserAgent() . \rand( 100000000, 900000000 ) ) );
    }

    public static function generateDeviceIdHash() {
        return \sha1( \md5( \time() . self::agent()->getIpAddress() . self::agent()->getHostname() . self::agent()->getUserAgent() ) );
    }

    public static function randomHash() {
        return \sha1( \md5( \time() . \rand( 1000000000, 9000000000 ) ) );
    }

    public static function addBreadcrumb( $name, $url ) {
        if ( isset( self::vars()->breadcrumbs ) ) {
            self::vars()->breadcrumbs = \array_merge( self::vars()->breadcrumbs, [ [ 'title' => $name, 'url' => $url ] ] );
        } else {
            self::vars()->breadcrumbs = [];
            self::vars()->breadcrumbs = \array_merge( self::vars()->breadcrumbs, [ [ 'title' => $name, 'url' => $url ] ] );
        }
    }

    public static function getErrorBox( $error, $display = false, $id = null ) {
        return self::output()->getPartial(
            'Global',
            'Error',
            'Box',
            [
                'error' => $error,
                'id'    => $id == null ? self::randomHash() : $id,
                'style' => $display ? '' : self::output()->getPartial( 'Global', 'Style', 'DisplayNone' )
            ]
        );
    }

    // WARNING:
    // Please DO NOT remove this powered by section - keeping this allows the software to remain free
    // If you wish to remove the powered by section, please inquire about removing it for a fee
    // at sam@banditbb.com
    public static function getCopyrightYears() {
        $retVal = '';
        $years = [];

        for ( $i = 2022; $i <= \date( 'Y', \time() ); $i++ ) {
            $years[] = $i;
        }

        if ( $years[0] != $years[\count( $years ) - 1] ) {
            $retVal = \sprintf( '%s-%s', $years[0], $years[\count( $years ) - 1] );
        } else {
            $retVal = $years[0];
        }

        return $retVal;
    }

    public static function whichPageIsPostOn( $postId, $memberId = null, $vars = null ) {
        $perPage = $memberId != null ? self::member()->postsPerPageSpecific( $memberId ) : self::member()->postsPerPage();
        $data = self::cache()->massGetData( [ 'topics' => 'topics', 'posts' => 'posts' ] );
        $collection = [];
        $itorator = 0;
        $page = 1;
        
        foreach ( $data->posts as $post ) {
            if ( $post->postId == $postId ) {
                $topicId = $post->topicId;
            }
        }

        foreach ( $data->posts as $post ) {
            if ( $post->topicId == $topicId ) {
                $collection[] = $post->postId;
            }
        }

        $total = \count( $collection );
        \sort( $collection );

        foreach ( $collection as $item ) {
            if ( $item == $postId ) {
                $itorator++;
                break;
            } else {
                $itorator++;
            }
        }

        foreach ( $data->topics as $topic ) {
            if ( $topic->topicId == $topicId ) {
                $topicTitle = $topic->title;
            }
        }

        $totalPages = \ceil( $total / $perPage );

        for ( $i = 1; $i <= $totalPages; $i++ ) {
            if ( $itorator >= ( ( $i * $perPage ) - $perPage ) && $itorator <= ( $i * $perPage ) ) {
                $page = $i;
            } else {
                $page = 1;
            }
        }

        $params = [ 'id' => self::url()->getUrlWithIdAndTitle( $topicId, $topicTitle ), 'page' => $page ];

        if ( $vars != null && \count( $vars ) > 0 ) {
            $params = \array_merge( $params, $vars );
        }

        return self::seo()->seoUrl( 'topics', 'view', $params ) . '/#post-' . $postId;
    }

    public static function getContentSharing( $contentId, $contentUrl, $contentTitle = null ) {
        $sharingServices = self::settings()->contentSharingServices;
        $content = new \stdClass();
        $content->items = '';
        $content->dropDown = '';

        foreach ( $sharingServices as $service ) {
            if ( $contentTitle == null ) {
                $contentTitle = \ucfirst( $service->title );
            }

            $content->items .= self::output()->getPartial(
                'UtilitiesHelper',
                'Share',
                'DropDownItem',
                [
                    'url'   => \sprintf( $service['url'], \urlencode( $contentUrl ), \urlencode( $contentTitle ) ),
                    'title' => self::localization()->quickReplace( 'utilitieshelper', 'shareOn', 'Service', \ucfirst( $service['title'] ) ),
                    'icon'  => self::output()->getPartial( 'UtilitiesHelper', 'Share', 'Icon' . \ucfirst( $service['title'] ), [ 'color' => $service['color'] ] )
                ]
            );
        }

        $hash = self::randomHash();

        $content->menu = self::output()->getPartial(
            'UtilitiesHelper',
            'Share',
            'DropDownMenu',
            [
                'list' => $content->items,
                'hash'  => $hash
            ]
        );

        $content->link = self::output()->getPartial(
            'UtilitiesHelper',
            'Share',
            'Link',
            [
                'hash' => $hash
            ]
        );

        $content->iconLink = self::output()->getPartial(
            'UtilitiesHelper',
            'Share',
            'IconLink',
            [
                'hash' => $hash
            ]
        );

        return $content;
    }
}