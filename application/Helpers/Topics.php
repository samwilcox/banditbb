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

class Topics extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getTotalViewingTopic( $topicId ) {
        $data = self::cache()->getData( 'sessions' );
        $total = 0;

        foreach ( $data as $session ) {
            if ( $session->topicId == $topicId ) $total++;
        }

        return $total;
    }

    public static function getLastPostData( $topicId ) {
        $retVal = new \stdClass();
        $list = [];
        $data = self::cache()->getData( 'posts' );

        foreach ( $data as $post ) {
            if ( $post->topicId == $topicId ) {
                $list[$post->postId] = $post->postedTimestamp;
            }
        }

        if ( \count( $list ) < 1 ) {
            $retVal->dataAvailable = false;
            $retVal->authorId = null;
            $retVal->timestamp = null;
            return $retVal;
        }

        \arsort( $list );

        foreach ( $data as $post ) {
            if ( $post->postId == \key( $list ) ) {
                $retVal->dataAvailable = true;
                $retVal->authorId = $post->authorId;
                $retVal->timestamp = $post->postedTimestamp;
                break;
            }
        }

        return $retVal;
    }

    public static function haveUnread( $topicId ) {
        if ( ! self::member()->signedIn() ) return false;

        $unread = true;
        $data = self::cache()->massGetData( [ 'read' => 'forums_read', 'topics' => 'topics' ] );

        foreach ( $data->read as $obj ) {
            if ( $obj->topicId == $topicId && $obj->memberId == self::member()->memberId() ) {
                foreach ( $data->topics as $topic ) {
                    if ( $topic->topicId == $obj->topicId ) $lastPost = self::getLastPostData( $topic->topicId );
                }

                if ( ! $lastPost->dataAvailable ) {
                    $unread = false;
                } else {
                    if ( $lastPost->timestamp <= $obj->lastReadTimestamp ) $unread = false;
                }
            }
        }

        return $unread;
    }

    public static function hasSolution( $topicId ) {
        $retVal = new \stdClass();
        $data = self::cache()->massGetData( [ 'forums' => 'forums', 'topics' => 'topics' ] );

        foreach ( $data->topics as $topic ) {
            foreach ( $data->forums as $forum ) {
                if ( $forum->forumId == $topic->forumId ) {
                    if ( $forum->forumType != 'QA' ) {
                        $retVal->hasSolution = false;
                    } else {
                        if ( $topic->hasSolution == 1 && $topic->solutionPostId != 0 && $topic->solutionTimestamp != 0 ) {
                            $retVal->hasSolution = true;
                            $retVal->link = self::utilities()->whichPageIsPostOn( $topic->solutionPostId );
                        } else {
                            $retVal->hasSolution = false;
                        }
                    }
                }
            }
        }

        return $retVal;
    }

    public static function hasAttachments( $topicId ) {
        $retVal = false;
        $data = self::cache()->getData( 'posts' );

        foreach ( $data as $post ) {
            if ( $post->topicId == $topicId ) {
                if ( $post->attachments != NULL ) {
                    $retVal = true;
                }
            }
        }

        return $retVal;
    }

    public static function isHot( $topicId ) {
        $retVal = new \stdClass();
        $retVal->hot = false;
        $data = self::cache()->massGetData( [ 'forums' => 'forums', 'topics' => 'topics' ] );

        foreach ( $data->topics as $topic ) {
            if ( $topic->topicId == $topicId ) {
                $totalReplies = $topic->totalReplies;
                break;
            }
        }

        foreach ( $data->forums as $forum ) {
            if ( $forum->forumId == $topic->forumId ) {
                $retVal->threshold = $forum->hotThreshold;
                break;
            }
        }

        if ( $totalReplies >= $retVal->threshold ) $retVal->hot = true;

        return $retVal;
    }

    public static function listing( $topicId ) {
        $data = self::cache()->massGetData( [ 'topics' => 'topics', 'forums' => 'forums' ] );

        foreach ( $data->topics as $topic ) {
            if ( $topic->topicId == $topicId ) {
                $forumId = $topic->forumId;
                $authorId = $topic->createdMemberId;
                $topicTitle = $topic->title;
                $timestamp = $topic->createdTimestamp;
                $totalReplies = $topic->totalReplies;
                $totalViews = $topic->totalViews;
                $pinned = $topic->pinned == 1 ? true : false;
                $locked = $topic->locked == 1 ? true : false;
            }
        }

        if ( self::haveUnread( $topicId ) ) {
            $topicLink = self::output()->getPartial(
                'TopicsHelper',
                'Link',
                'Unread',
                [
                    'url'          => self::seo()->seoUrl( 'topics', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $topicId, $topicTitle ) ] ),
                    'title'        => $topicTitle,
                    'totalViewing' => self::localization()->quickReplace( 'topicshelper', 'totalViewingTopic' . ( self::getTotalViewingTopic( $topicId ) == 1 ? 'Singular' : 'Plural' ), 'Total', self::math()->formatNumber( self::getTotalViewingTopic( $topicId ) ) )
                ]
            );
        } else {
            $topicLink = self::output()->getPartial(
                'TopicsHelper',
                'Link',
                'Read',
                [
                    'url'          => self::seo()->seoUrl( 'topics', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $topicId, $topicTitle ) ] ),
                    'title'        => $topicTitle,
                    'totalViewing' => self::localization()->quickReplace( 'topicshelper', 'totalViewingTopic' . ( self::getTotalViewingTopic( $topicId ) == 1 ? 'Singular' : 'Plural' ), 'Total', self::math()->formatNumber( self::getTotalViewingTopic( $topicId ) ) )
                ]
            );
        }

        $lastPostData = self::getLastPostData( $topicId );
        $isHot = self::isHot( $topicId );
        $solution = self::hasSolution( $topicId );

        return self::output()->getPartial(
            'TopicsHelper',
            'Topics',
            'Item',
            [
                'link'             => $topicLink,
                'startedBy'        => self::localization()->quickMultiWordReplace( 'topicshelper', 'startedBy', [
                    'Author'    => self::member()->getLink( $authorId ),
                    'Timestamp' => self::output()->getPartial( 'TopicsHelper', 'Topics', 'Timestamp', [ 'timestamp' => self::dateTime()->parse( $timestamp, [ 'timeAgo' => true ] ) ] )
                ]),
                'pinned'            => $pinned ? self::output()->getPartial( 'TopicsHelper', 'Topics', 'Icon', [ 'icon' => self::output()->getPartial( 'TopicsHelper', 'Icon', 'Pinned' ), 'tooltip' => self::localization()->getWords( 'topicshelper', 'topicPinned' ) ] ) : '',
                'locked'            => $locked ? self::output()->getPartial( 'TopicsHelper', 'Topics', 'Icon', [ 'icon' => self::output()->getPartial( 'TopicsHelper', 'Icon', 'Locked' ), 'tooltip' => self::localization()->getWords( 'topicshelper'. 'topicLocked' ) ] ) : '',
                'attachments'       => self::hasAttachments( $topicId ) ? self::output()->getPartial( 'TopicsHelper', 'Topics', 'Icon', [ 'icon' => self::output()->getPartial( 'TopicsHelper', 'Icon', 'Attachments' ), 'tooltip' => self::localization()->getWords( 'topicshelper', 'hasAttachments' ) ] ) : '',
                'solution'          => $solution->hasSolution ? self::output()->getPartial( 'TopicsHelper', 'Topics', 'SolutionIcon', [ 'url' => $solution->link ] ) : '',
                'hot'               => $isHot->hot ? self::output()->getPartial( 'TopicsHelper', 'Topics', 'Icon', [ 'icon' => self::output()->getPartial( 'TopicsHelper', 'Icon', 'Hot' ), 'tooltip' => self::localization()->quickReplace( 'topicshelper', 'topicHot', 'Threshold', $isHot->threshold ) ] ) : '',
                'totalReplies'      => self::math()->formatNumber( $totalReplies ),
                'totalViews'        => self::math()->formatNumber( $totalViews ),
                'id'                => $topicId,
                'lastPostPhoto'     => self::member()->profilePhoto( $lastPostData->authorId, true ),
                'lastPostLink'      => self::member()->getLink( $lastPostData->authorId ),
                'lastPostTimestamp' => self::dateTime()->parse( $lastPostData->timestamp, [ 'timeAgo' => true ] )
            ]
        );
    }

    public static function incrementViews( $topicId ) {
        $data = self::cache()->massGetData( [ 'topics' => 'topics', 'forums' => 'forums' ] );
        $update = false;

        foreach ( $data->topics as $topic ) {
            if ( $topic->topicId == $topicId ) {
                $forumId = $topic->forumId;
                $totalViews = $topic->totalViews;
                break;
            }
        }

        foreach ( $data->forums as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $viewsLocked = $forum->totalViewsLocked == 1 ? true : false;
                break;
            }
        }

        if ( $viewsLocked ) {
            if ( ! isset( $_COOKIE['BanditBB_Topic_Viewed'] ) ) {
                $update = true;
                self::cookies()->newCookie( 'BanditBB_Topic_Viewed', \serialize( [ $topicId ] ), \strtotime( '+10 years', \time() ) );
            } else {
                $cookieData = \unserialize( $_COOKIE['BanditBB_Topic_Viewed'] );
                $topicFound = false;

                if ( \count( $cookieData ) > 0 ) {
                    foreach ( $cookieData as $cookieId ) {
                        if ( $cookieId == $topicId ) {
                            $topicFound = true;
                            break;
                        }
                    }
                }

                if ( ! $topicFound ) {
                    $update = true;
                    \array_push( $topicId );
                    self::cookies()->newCookie( 'BanditBB_Topic_Viewed', \serialize( $cookieData ), \strtotime( '+10 years', \time() ) );
                }
            }
        } else {
            $update = true;
        }

        if ( $update ) {
            $totalViews++;

            self::db()->query( self::queries()->updateTopicViews(), [ 'totalViews' => $totalViews, 'id' => $topicId ] );
            self::cache()->update( 'topics' );
        }
    }

    public static function updateForumsRead( $forumId, $topicId ) {
        if ( self::member()->signedIn() ) {
            $data = self::cache()->getData( 'forums_read' );
            $found = false;

            foreach ( $data as $read ) {
                if ( $read->memberId == self::member()->memberId() && $read->forumId == $forumId && $read->topicId == $topicId ) {
                    $found = true;
                    $id = $read->readId;
                    break;
                }
            }

            if ( $found ) {
                self::db()->query( self::queries()->updateForumsRead(), [ 'id' => $id, 'time' => \time() ] );
            } else {
                self::db()->query( self::queries()->insertForumsRead(), [ 'forumId' => $forumId, 'topicId' => $topicId, 'memberId' => self::member()->memberId(), 'time' => \time() ] );
            }

            self::cache()->update( 'forums_read' );
        }
    }
}