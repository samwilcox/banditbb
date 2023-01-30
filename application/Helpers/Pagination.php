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

class Pagination extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function generate( $totalItems, $perPage, $page, $preUrl = null, $seoUrls = true ) {
        if ( $preUrl != null ) {
            $preUrl = ( self::settings()->seoEnabled && $seoUrls ) ? $preUrl . '/page/' : $preUrl . '&amp;page=';
        } else {
            $preUrl = ( self::settings()->seoEnabled && $seoUrls ) ? self::vars()->wrapper . '?/page/' : self::vars()->wrapper . '?page=';
        }

        $totalPages = \ceil( $totalItems / $perPage );
        $max = self::settings()->maxPageLinks;
        
        if ( $totalPages == 0 ) $totalPages = 1; 

        if ( $totalPages > 1 ) {
            $goFirst = $page > 1 ? self::output()->getPartial( 'PaginationHelper', 'Link', 'First', [ 'url' => $preUrl . '1' ] ) : '';
            $goLast = $page != $totalPages ? self::output()->getPartial( 'PaginationHelper', 'Link', 'Last', [ 'url' => $preUrl . $totalPages ] ) : '';

            if ( $page > 1 ) $previous =  self::output()->getPartial( 'PaginationHelper', 'Link', 'Previous', [ 'url' => $preUrl . ( $page - 1 ) ] );
            if ( $page < $totalPages ) $next = self::output()->getPartial( 'PaginationHelper', 'Link', 'Next', [ 'url' => $preUrl . ( $page + 1 ) ] );
            
            if ( $totalPages <= $max ) {
                for ( $i = 1; $i <= $totalPages; $i++ ) $pages .= self::getPageLink( $i, $page, $preUrl );
            } else {
                if ( $totalPages - $page > $max ) {
                    for ( $i = $page; $i <= ( $page + $max ); $i++ ) $pages .= self::getPageLink( $i, $page, $preUrl );
                } else {
                    for ( $i = ( $totalPages - $max ); $i <= $totalPages; $i++ ) $pages .= self::getPageLink( $i, $page, $preUrl );
                }
            }

            return $goFirst . $previous . $pages . $next . $goLast;
        } else {
            return self::output()->getPartial( 'PaginationHelper', 'Page', 'NoneLink', [ 'page' => 1 ] );
        }
    }

    public static function prePagination( $query, $prePage, $params = null, $preUrl = null, $seoUrls = true ) {
        $page = isset( self::request()->page ) ? self::request()->page : 1;
        $sql = $params != null ? self::db()->query( $query, $params ) : self::db()->query( $query );
        $total = self::db()->numRows( $sql );

        self::db()->freeResult( $sql );

        $page = $page != '' ? \ctype_digit( $page ) ? $page : 1 : 1;

        return [
            'pagination' => self::generate( $total, $perPage, $page, $preUrl, $seoUrls ),
            'from'       => ( ( $page * $perPage ) - $perPage ),
            'totalPages' => \ceil( $total / $perPage ) == 0 ? 1 : \ceil( $total / $perPage ),
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'displaying' => self::localization()->quickMultiWordReplace( 'paginationhelper', 'displayingPagination', [
                'Start' => $total > 0 ? ( ( $page * $perPage ) - $perPage ) + 1 : 0,
                'End'   => ( $page * $perPage ) < $total ? ( $page * $perPage ) : $total,
                'Total' => self::math()->formatNumber( $total )
            ]),
            'full' => self::output()->getPartial(
                'PaginationHelper',
                'Pagination',
                'Full',
                [
                    'pageOfPage' => self::localization()->quickMultiWordReplace( 'paginationhelper', 'pageOfPage', [
                        'Page'       => $page,
                        'TotalPages' => \ceil( $total / $perPage ) == 0 ? 1 : \ceil( $total / $perPage )
                    ]),
                    'pagination' => self::generate( $total, $perPage, $page, $preUrl, $seoUrls ),
                    'displaying' => self::localization()->quickMultiWordReplace( 'paginationhelper', 'displayingPagination', [
                        'Start' => $total > 0 ? ( ( $page * $perPage ) - $perPage ) + 1 : 0,
                        'End'   => ( $page * $perPage ) < $total ? ( $page * $perPage ) : $total,
                        'Total' => self::math()->formatNumber( $total )
                    ]),
                    'page'       => $page,
                    'totalPages' => \ceil( $total / $perPage ) == 0 ? 1 : \ceil( $total / $perPage )
                ]
            )
        ];
    }

    public static function prePaginationWithTotal( $totalItems, $perPage, $preUrl = null, $seoUrls = true ) {
        $page = isset( self::request()->page ) ? self::request()->page : 1;
        $page = $page != '' ? \ctype_digit( $page ) ? $page : 1 : 1;

        return [
            'pagination' => self::generate( $totalItems, $perPage, $page, $preUrl, $seoUrls ),
            'from'       => ( ( $page * $perPage ) - $perPage ),
            'totalPages' => \ceil( $totalItems / $perPage ) == 0 ? 1 : \ceil( $totalItems / $perPage ),
            'total'      => $totalItems,
            'page'       => $page,
            'perPage'    => $perPage,
            'displaying' => self::localization()->quickMultiWordReplace( 'paginationhelper', 'displayingPagination', [
                'Start' => $totalItems > 0 ? ( ( $page * $perPage ) - $perPage ) + 1 : 0,
                'End'   => ( $page * $perPage ) < $totalItems ? ( $page * $perPage ) : $totalItems,
                'Total' => self::math()->formatNumber( $totalItems )
            ]),
            'full' => self::output()->getPartial(
                'PaginationHelper',
                'Pagination',
                'Full',
                [
                    'pageOfPage' => self::localization()->quickMultiWordReplace( 'paginationhelper', 'pageOfPage', [
                        'Page'       => $page,
                        'TotalPages' => \ceil( $totalItems / $perPage ) == 0 ? 1 : \ceil( $totalItems / $perPage )
                    ]),
                    'pagination' => self::generate( $totalItems, $perPage, $page, $preUrl, $seoUrls ),
                    'displaying' => self::localization()->quickMultiWordReplace( 'paginationhelper', 'displayingPagination', [
                        'Start' => $totalItems > 0 ? ( ( $page * $perPage ) - $perPage ) + 1 : 0,
                        'End'   => ( $page * $perPage ) < $totalItems ? ( $page * $perPage ) : $totalItems,
                        'Total' => self::math()->formatNumber( $totalItems )
                    ]),
                    'page'       => $page,
                    'totalPages' => \ceil( $totalItems / $perPage ) == 0 ? 1 : \ceil( $totalItems / $perPage )
                ]
            )
        ];
    }

    private static function getPageLink( $iterator, $page, $preUrl ) {
        if ( $iterator == $page ) {
            return self::output()->getPartial( 'PaginationHelper', 'Page', 'NoneLink', [ 'page' => $iterator ] );
        } else {
            return self::output()->getPartial( 'PaginationHelper', 'Page', 'Link', [ 'url' => $preUrl . $iterator, 'page' => $iterator ] );
        }
    }
}