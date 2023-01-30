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

class WhosOnline extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function usersBrowsing( $type, $id ) {
        $retVal = '';
        $data = self::cache()->getData( 'sessions' );
        $stats = new \stdClass();
        $stats->total = 0;
        $stats->members = 0;
        $stats->anonymous = 0;
        $stats->guests = 0;
        $stats->bots = 0;
        $memberList = '';
        $initial = true;

        switch ( $type ) {
            case 'forum':
                $field = 'forumId';
                break;

            case 'topic':
                $field = 'topicId';
                break;
        }

        foreach ( $data as $session ) {
            if ( $session->$field == $id ) {
                if ( $session->memberId == 0 && $session->memberUsername == 'Guest' && $session->isBot == 0 ) {
                    $stats->guests++;
                } elseif ( $session->memberId == 0 && $session->memberUsername == 'Guest' && $session->isBot == 1 && \strlen( $session->botName ) > 0 ) {
                    $stats->bots++;
                } elseif ( $session->memberId != 0 && $session->memberUsername != 'Guest' && $session->displayOnList == 0 && $session->isBot == 0 ) {
                    $stats->anonymous++;
                } else {
                    $stats->members++;

                    if ( $initial ) {
                        $seperator = '';
                        $initial = false;
                    } else {
                        $seperator = self::output()->getPartial( 'Global', 'List', 'Seperator' );
                    }

                    $memberList .= self::member()->getLink( $session->memberId, false, self::localization()->quickReplace( 'whosonlinehelper', 'lastClick', 'LastClick', self::dateTime()->parse( $session->lastClick, [ 'timeAgo' => false, 'timeOnly' => true ] ) ), $seperator );
                }
            }
        }

        $stats->total = ( $stats->members + $stats->anonymous + $stats->guests + $stats->bots );

        if ( $stats->members > 0 && \strlen( $memberList ) > 0 ) {
            $list = self::output()->getPartial(
                'WhosOnlineHelper',
                'Members',
                'List',
                [
                    'list' => $memberList
                ]
            );
        } else {
            $list = '';
        }

        return self::output()->getPartial(
            'WhosOnlineHelper',
            'Browsing',
            'List',
            [
                'browsing'  => self::localization()->quickMultiWordReplace( 'whosonlinehelper', 'usersBrowsing', [
                    'Total' => self::math()->formatNumber( $stats->total ),
                    'Type'  => self::localization()->getWords( 'whosonlinehelper', $type . 'Type' )
                ]),
                'guests'    => self::localization()->quickReplace( 'whosonlinehelper', 'totalGuests' . ( $stats->guests == 1 ? 'Singular' : 'Plural' ), 'Total', self::math()->formatNumber( $stats->guests ) ),
                'members'   => self::localization()->quickReplace( 'whosonlinehelper', 'totalMembers' . ( $stats->members == 1 ? 'Singular' : 'Plural' ), 'Total', self::math()->formatNumber( $stats->members ) ),
                'anonymous' => self::localization()->quickReplace( 'whosonlinehelper', 'totalAnonymous', 'Total', self::math()->formatNumber( $stats->anonymous ) ),
                'bots'      => self::localization()->quickReplace( 'whosonlinehelper', 'totalBots' . ( $stats->bots == 1 ? 'Singular' : 'Plural' ), 'Total', self::math()->formatNumber( $stats->bots ) ),
                'list'      => $list
            ]
        );
    }

    public static function recordForumTopicBrowsing( $forumId, $topicId ) {
        $data = self::cache()->getData( 'sessions' );
        $update = false;

        foreach ( $data as $session ) {
            if ( $session->sessionId == self::session()->getSessionId() ) {
                if ( $session->forumId != $forumId || $session->topicId != 0 ) {
                    $update = true;
                }
            }
        }

        if ( $update ) {
            self::db()->query( self::queries()->updateSessionBrowsing(), [ 'forumId' => $forumId, 'topicId' => $topicId, 'id' => self::session()->getSessionId() ] );
            self::cache()->update( 'sessions' );
        }
    }
}