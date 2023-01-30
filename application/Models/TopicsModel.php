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

namespace BanditBB\Models;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class TopicsModel extends \BanditBB\Models\BaseModel {

    private static $vars = [];

    public function viewTopic() {
        $topicId = self::textParsing()->getValueBeforeSlash( self::request()->id );
        $data = self::cache()->massGetData( [ 'topics' => 'topics', 'forums' => 'forums', 'posts' => 'posts', 'polls' => 'polls' ] );
        $found = false;

        foreach ( $data->topics as $topic ) {
            if ( $topic->topicId == $topicId ) {
                $found = true;
                $forumId = $topic->forumId;
                $topicTitle = $topic->title;
                $creatorId = $topic->createdMemberId;
                $creatorTimestamp = $topic->createdTimestamp;
                $hasSolution = $topic->hasSolution == 1 ? true : false;
                $solutionTimestamp = $topic->solutionTimestamp;
                $solutionMemberId = $topic->solutionMemberId;
                $solutionPostId = $topic->solutionPostId;
                $pollId = $topic->pollId;
                $link = $topic->link == 1 ? true : false;
                $linkTopicId = $topic->linkTopicId;
            }
        }

        if ( ! $found ) {
            self::errors()->throwError( self::localization()->getWords( 'errors', 'topicNotFound' ) );
        }

        if ( ! self::member()->haveForumPermission( $forumId, 'viewForum' ) ) {
            self::forums()->permissionsError( $forumId, 'viewForum' );
        }

        foreach ( $data->forums as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $forumTitle = $forum->title;
                $shareTopic = $forum->shareTopicEnabled == 1 ? true : false;
            }
        }

        self::forums()->checkForumPassword( $forumId, self::seo()->seoUrl( 'topics', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $topicId, $topicTitle ) ] ) );

        // POLL STUFF LATER
        $pollOnly = false;

        self::vars()->breadcrumbs = self::forums()->getForumBreadcrumbs( $forumId );
        self::utilities()->addBreadcrumb( $topicTitle, self::seo()->seoUrl( 'topics', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $topicId, $topicTitle ) ] ) );

        self::topics()->incrementViews( $topicId );
        self::topics()->updateForumsRead( $forumId, $topicId );

        $totalPosts = 0;

        foreach ( $data->posts as $post ) {
            if ( $post->topicId == $topicId ) $totalPosts++;
        }

        $pageData = self::pagination()->prePaginationWithTotal( $totalPosts, self::member()->postsPerPage(), self::seo()->seoUrl( 'topics', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $topicId, $topicTitle ) ] ) );
        $followers = self::followers()->getFollowers( $topicId, 'topic' );

        if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TOPIC_RSS_FEED ) ) {
            $rssFeed = self::output()->getPartial(
                'Topics',
                'RSS',
                'Feed',
                [
                    'url' => self::seo()->seoUrl( 'rss', 'topicfeed', [ 'id' => $topicId ] )
                ]
            );
        } else {
            $rssFeed = '';
        }

        if ( $shareTopic ) {
            $sharing = self::utilities()->getContentSharing( $topicId, \sprintf( '%s?controller=topics&action=view&id=%s', self::vars()->wrapper, self::url()->getUrlWithIdAndTitle( $topicId, $topicTitle ) ), $topicTitle );
            $sharingDropDownMenu = $sharing->menu;
            $sharingLink = $sharing->link;
        } else {
            $sharingDropDownMenu = '';
            $sharingLink ='';
        }

        self::$vars['header'] = self::output()->getPartial(
            'Topics',
            'View',
            'Header',
            [
                'pagination'      => $pageData['full'],
                'topicTitle'      => $topicTitle,
                'newTopicButton'  => self::buttons()->getButtonUsingPermissions( \BanditBB\Types\Buttons::NEW_TOPIC, $forumId, $topicId ),
                'postReplyButton' => self::buttons()->getButtonUsingPermissions( \BanditBB\Types\Buttons::POST_REPLY, $forumId, $topicId ),
                'photo'           => self::member()->profilePhoto( $creatorId, true ),
                'startedBy'       => self::localization()->quickReplace( 'topics', 'startedBy', 'Author', self::member()->getLink( $creatorId ) ),
                'timestamp'       => self::dateTime()->parse( $creatorTimestamp, [ 'timeAgo' => true ] ),
                'forumLink'       => self::output()->getPartial( 'Global', 'Link', 'GenericNoTip', [ 'seperator' => '', 'name' => $forumTitle, 'url' => self::seo()->seoUrl( 'forums', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $forumId, $forumTitle ) ] ) ] ),
                'rss'             => $rssFeed,
                'followers'       => $followers->followers,
                'followersDialog' => $followers->dialog,
                'shareLink'       => $sharingLink,
                'shareDropDown'   => $sharingDropDownMenu
            ]
        );

        $sql = self::db()->query( self::queries()->selectPostsLimit(), [
            'id'      => $topicId,
            'from'    => $pageData['from'],
            'perPage' => $pageData['perPage']
        ]);

        while ( $row = self::db()->fetchObject( $sql ) ) self::$vars['posts'] .= self::posts()->getPosting( $row->postId );

        self::db()->freeResult( $sql );

        if ( self::member()->haveForumPermission( $forumId, 'postReply' ) && ! $pollOnly ) {
            self::$vars['quickReply'] = self::editor()->getQuickEditor([
                'fields' => [
                    'controller' => 'post',
                    'action'     => 'processreply',
                    'topicid'    => $topicId
                ],
                'signature' => true,
                'follow'    => true,
                'topicId'   => $topicId,
                'uploader'  => self::member()->haveForumPermission( $forumId, 'uploadAttachments' ) ? true : false,
                'marginTop' => true
            ]);
        } else {
            self::$vars['quickReply'] = '';
        }

        self::vars()->forumId = $forumId;
        self::vars()->topicId = $topicId;
        self::whosOnline()->recordForumTopicBrowsing( $forumId, $topicId );

        self::$vars['browsing'] = self::whosOnline()->usersBrowsing( 'topic', $topicId );

        return self::$vars;
    }
}