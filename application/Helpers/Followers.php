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

class Followers extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getFollowers( $id, $following, $ajax = false ) {
        $data = self::cache()->getData( 'members_following' );
        $found = false;
        $total = 0;
        $isFollowing = false;
        $retVal = new \stdClass();
        $list = '';
        $display = false;

        if ( ! self::member()->signedIn() ) {
            $retVal->dialog = '';
            $retVal->followers = '';
            return $retVal;
        }

        foreach ( $data as $follow ) {
            switch ( $following ) {
                case 'forum':
                    if ( $follow->forumId == $id && $follow->followingForum == 1 && $follow->followingTopic == 0 ) {
                        $total++;

                        if ( self::member()->signedIn() && $follow->memberId == self::member()->memberId() ) $isFollowing = true;

                        if ( $follow->displayInList == 1 ) {
                            $list .= self::output()->getPartial(
                                'FollowersHelper',
                                'Follower',
                                'Item',
                                [
                                    'photo'     => self::member()->profilePhoto( $follow->memberId, true ),
                                    'link'      => self::member()->getLink( $follow->memberId ),
                                    'timestamp' => self::dateTime()->parse( $follow->timestamp, [ 'timeAgo' => true ] )
                                ]
                            );

                            $display = true;
                        }
                    }
                    break;

                case 'topic':
                    if ( $follow->topicId == $id && $follow->followingForum == 0 && $follow->followingTopic == 1 ) {
                        $total++;

                        if ( self::member()->signedIn() && $follow->memberId == self::member()->memberId() ) $isFollowing = true;

                        if ( $follow->displayInList == 1 ) {
                            $list .= self::output()->getPartial(
                                'FollowersHelper',
                                'Follower',
                                'Item',
                                [
                                    'photo'     => self::member()->profilePhoto( $follow->memberId, true ),
                                    'link'      => self::member()->getLink( $follow->memberId ),
                                    'timestamp' => self::dateTime()->parse( $follow->timestamp, [ 'timeAgo' => true ] )
                                ]
                            );

                            $display = true;
                        }
                    }
                    break;
            }
        }

        if ( $display ) {
            if ( self::settings()->followersDialogListingMaxItems == 0 ) {
                $viewMoreLink = '';
            } else {
                $viewMoreLink = self::output()->getPartial(
                    'FollowersHelper',
                    'ViewMore',
                    'Link',
                    [
                        'url' => self::seo()->seoUrl( 'followers', 'list', [ 'following' => $following, 'id' => $id ] )
                    ]
                );
            }

            $retVal->dialog = self::output()->getPartial(
                'FollowersHelper',
                'Followers',
                'Dialog',
                [
                    'listing' => $list,
                    'link'    => $viewMoreLink
                ]
            );

            $followingLink = self::output()->getPartial(
                'FollowersHelper',
                'Followers',
                'Link',
                [
                    'total' => self::math()->formatNumber( $total )
                ]
            );
        } else {
            $retVal->dialog = '';

            $followingLink = self::output()->getPartial(
                'FollowersHelper',
                'Followers',
                'NonLink',
                [
                    'total' => self::math()->formatNumber( $total )
                ]
            );
        }

        if ( $isFollowing ) {
            if ( self::settings()->followersAjaxEnabled ) {
                $toggleLink = self::output()->getPartial(
                    'FollowersHelper',
                    'Unfollow',
                    'LinkAjax',
                    [
                        'id'        => $id,
                        'following' => $following
                    ]
                );
            } else {
                $toggleLink = self::output()->getPartial(
                    'FollowersHelper',
                    'Unfollow',
                    'Link',
                    [
                        'url' => self::seo()->seoUrl( 'followers', 'unfollow', [ 'id' => $id, 'following' => $following ] )
                    ]
                );
            }
        } else {
            if ( self::settings()->followersAjaxEnabled ) {
                $toggleLink = self::output()->getPartial(
                    'FollowersHelper',
                    'Follow',
                    'LinkAjax',
                    [
                        'id'        => $id,
                        'following' => $following
                    ]
                );
            } else {
                $toggleLink = self::output()->getPartial(
                    'FollowersHelper',
                    'Follow',
                    'Link',
                    [
                        'url' => self::seo()->seoUrl( 'followers', 'follow', [ 'id' => $id, 'following' => $following ] )
                    ]
                );
            }

            if ( self::settings()->followersAjaxEnabled ) {
                $retVal->dialog .= self::output()->getPartial(
                    'FollowersHelper',
                    'Followers',
                    'FollowDialogAjax',
                    [
                        'id'        => $id,
                        'following' => $following
                    ]
                );
            } else {
                $retVal->dialog .= self::output()->getPartial(
                    'FollowersHelper',
                    'Followers',
                    'FollowDialog',
                    [
                        'id'        => $id,
                        'following' => $following
                    ]
                );
            }
        }

        if ( $ajax ) {
            $retVal->followers = self::output()->getPartial(
                'FollowersHelper',
                'Followers',
                'FollowersAjax',
                [
                    'followersLink' => $followingLink,
                    'toggleLink'    => $toggleLink
                ]
            );
        } else {
            $retVal->followers = self::output()->getPartial(
                'FollowersHelper',
                'Followers',
                'Followers',
                [
                    'followersLink' => $followingLink,
                    'toggleLink'    => $toggleLink
                ]
            );
        }

        return $retVal;
    }
}