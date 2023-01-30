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

class Posts extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getPostNumber( $topicId ) {
        $data = self::cache()->getData( 'posts' );
        $found = false;
        $listing = [];
        $final = [];

        foreach ( $data as $post ) {
            if ( $post->topicId == $topicId ) {
                $listing[] = $post->postId;
            }
        }

        \sort( $listing );

        $x = 1;

        foreach ( $listing as $item ) {
            $final[$item] = $x;
            $x++;
        }

        return $final;
    }

    public static function getPosting( $postId ) {
        $data = self::cache()->massGetData( [ 'posts' => 'posts', 'topics' => 'topics', 'forums' => 'forums' ] );

        foreach ( $data->posts as $post ) {
            if ( $post->postId == $postId ) {
                $topicId = $post->topicId;
                $memberId = $post->authorId;
                $timestamp = $post->postedTimestamp;
                $message = \stripslashes( $post->message );
                break;
            }
        }

        foreach ( $data->topics as $topic ) {
            if ( $topic->topicId == $topicId ) {
                $isSolutionPost = ( $topic->hasSolution == 1 && $topic->solutionPostId == $postId ) ? true : false;
                $forumId = $topic->forumId;
                $topicTitle = $topic->title;
                break;
            }
        }

        foreach ( $data->forums as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $reportPostEnabled = $forum->reportPostEnabled == 1 ? true : false;
                $sharePostEnabled = $forum->sharePostEnabled == 1 ? true : false;
                break;
            }
        }

        $postData = self::member()->getPostData( $memberId );
        $sharing = self::utilities()->getContentSharing( $postId, self::utilities()->whichPageIsPostOn( $postId ) );

        return self::output()->getPartial(
            'PostsHelper',
            'Posts',
            'Item',
            [
                'photo'      => self::member()->profilePhoto( $memberId ),
                'link'       => self::member()->getLink( $memberId ),
                'group'      => self::member()->getPrimaryGroupLink( $memberId ),
                'timestamp'  => self::localization()->quickReplace( 'postshelper', 'postTimestamp', 'Timestamp', self::dateTime()->parse( $timestamp, [ 'timeAgo' => true ] ) ),
                'message'    => $message,
                'postId'     => self::localization()->quickReplace( 'postshelper', 'postId', 'Number', self::getPostNumber( $topicId )[$postId] ),
                'reportPost' => $reportPostEnabled ? self::output()->getPartial( 'PostsHelper', 'Link', 'ReportPost', [ 'id' => $postId ] ) : '',
                'sharePost'  => $sharePostEnabled ? $sharing->iconLink : '',
                'shareMenu'  => $sharePostEnabled ? $sharing->menu : '',
                'quotePost'  => self::member()->haveForumPermission( $forumId, 'postReply' ) ? self::output()->getPartial( 'PostsHelper', 'Link', 'QuotePost', [ 'id' => $postId ] ) : '',
                'signature'  => self::member()->showSignatures() ? self::member()->getSignatureBox( $memberId ) : '',
                'postData'   => $postData->guest ? '' : self::output()->getPartial( 'PostsHelper', 'Posts', 'PostData', [ 'data' => $postData->data ] )
            ]
        );
    }
}