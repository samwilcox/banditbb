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

class Forums extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getLastPostData( $forumId ) {
        $retVal = new \stdClass();
        $list = [];
        $data = self::cache()->massGetData( [ 'posts' => 'posts', 'topics' => 'topics' ] );

        foreach ( $data->posts as $post ) {
            foreach ( $data->topics as $topic ) {
                if ( $topic->topicId == $post->topicId ) {
                    if ( $topic->forumId == $forumId ) {
                        $list[$post->postId] = $post->postedTimestamp;
                    }
                }
            }
        }

        if ( \count( $list ) < 1 ) {
            $retVal->dataAvailable = false;
            return $retVal;
        }

        \arsort( $list );

        foreach ( $data->posts as $post ) {
            if ( $post->postId == \key( $list ) ) {
                $topicId = $post->topicId;
                $timestamp = $post->postedTimestamp;
                $authorId = $post->authorId;
                $postId = $post->postId;
                break;
            }
        }

        foreach ( $data->topics as $topic ) {
            if ( $topic->topicId == $topicId ) {
                $title = $topic->title;
            }
        }

        $retVal->dataAvailable = true;
        $retVal->postId = $postId;
        $retVal->topicId = $topicId;
        $retVal->authorId = $authorId;
        $retVal->timestamp = $timestamp;
        $retVal->title = $title;

        return $retVal;
    }

    public static function getTotalTopicsReplies( $forumId ) {
        $retVal = new \stdClass();
        $total = new \stdClass();
        $total->topics = 0;
        $total->replies = 0;
        $total->ignored = 0;
        $data = self::cache()->massGetData( [ 'topics' => 'topics', 'posts' => 'posts' ] );

        foreach ( $data->topics as $topic ) {
            if ( $topic->forumId == $forumId ) {
                $total->topics++;

                foreach ( $data->posts as $post ) {
                    if ( $post->topicId == $topic->topicId ) {
                        if ( $topic->createdTimestamp == $post->postedTimestamp ) $total->ignored++;
                        $total->replies++;
                    }
                }
            }
        }

        $total->replies = ( ( $total->replies - $total->ignored ) < 0 ) ? 0 : ( $total->replies - $total->ignored );

        $retVal->topics = $total->topics;
        $retVal->replies = $total->replies;
        $retVal->total = $total->topics + $total->replies;
        $retVal->topicsFormatted = self::math()->formatNumber( $total->topics );
        $retVal->repliesFormatted = self::math()->formatNumber( $total->replies );
        $retVal->totalFormatted = self::math()->formatNumber( $total->topics + $total->replies );

        return $retVal;
    }

    public static function haveUnread( $forumId ) {
        $stats = self::getTotalTopicsReplies( $forumId );

        if ( $stats->topics == 0 && $stats->replies == 0 || ! self::member()->signedIn() ) {
            return false;
        }

        $unread = false;
        $data = self::cache()->massGetData( [ 'read' => 'forums_read', 'topics' => 'topics' ] );

        foreach ( $data->read as $read ) {
            if ( $read->forumId == $forumId && $read->memberId == self::member()->memberId() ) {
                foreach ( $data->topics as $topic ) {
                    if ( $topic->topicId == $read->topicId ) $lastPost = self::getLastPostData( $topic->forumId );
                }

                if ( ! $lastPost->dataAvailable ) {
                    $unread = false;
                } else {
                    if ( $lastPost->timestamp <= $read->lastReadTimestamp ) $unread = false;
                }
            }
        }

        return $unread;
    }

    public static function getTotalViewingForum( $forumId ) {
        $data = self::cache()->getData( 'sessions' );
        $total = 0;

        foreach ( $data as $session ) {
            if ( $session->forumId == $forumId ) $total++;
        }

        return $total;
    }

    public static function haveSubForums( $forumId ) {
        $data = self::cache()->getData( 'forums' );
        $retVal = false;

        foreach ( $data as $forum ) {
            if ( $forum->parentId == $forumId && $forum->hidden == 0 ) {
                $retVal = true;
                break;
            }
        }

        return $retVal;
    }

    public static function getSubForums( $forumId ) {
        $data = self::cache()->getData( 'forums' );
        $list = '';

        foreach ( $data as $forum ) {
            if ( $forum->parentId == $forumId && $forum->hidden == 0 ) {
                $list .= self::output()->getPartial(
                    'Global',
                    'Link',
                    'Generic',
                    [
                        'seperator' => '',
                        'url'       => self::seo()->seoUrl( 'forums', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $forum->forumId, $forum->title ) ] ),
                        'name'      => $forum->title,
                        'tooltip'   => self::localization()->quickReplace( 'forumshelper', 'totalViewingForum' . ( self::getTotalViewingForum( $forum->forumId ) == 1 ? 'Singular' : 'Plural' ), 'Total', self::math()->formatNumber( self::getTotalViewingForum( $forum->forumId ) ) )
                    ]
                );
            }
        }

        return $list;
    }

    public static function getSubForumsMenus() {
        $data = self::cache()->getData( 'forums' );
        $list = '';

        foreach ( $data as $forum ) {
            if ( $forum->hidden == 0 ) {
                if ( $forum->depth == 1 ) {
                    if ( self::haveSubForums( $forum->forumId ) && $forum->displaySubForums == 1 ) {
                        $list .= self::output()->getPartial(
                            'ForumsHelper',
                            'SubForums',
                            'Menu',
                            [
                                'list' => self::getSubForums( $forum->forumId ),
                                'id'   => $forum->forumId
                            ]
                        );
                    }
                }
            }
        }

        return $list;
    }

    public static function getSubForumsLink( $forumId ) {
        $data = self::cache()->getData( 'forums' );
        $link = '';

        foreach ( $data as $forum ) {
            if ( $forum->forumId == $forumId && $forum->hidden == 0 ) {
                if ( self::haveSubForums( $forumId ) && $forum->displaySubForums == 1 ) {
                    $link = self::output()->getPartial(
                        'ForumsHelper',
                        'SubForums',
                        'Link',
                        [
                            'id' => $forum->forumId
                        ]
                    );
                }
            }
        }

        return $link;
    }

    public static function getForum( $forumId, $initial = false ) {
        $data = self::cache()->getData( 'forums' );
        $found = false;

        foreach ( $data as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $found = true;
                $image = $forum->imageUrl;
                $cardBgType = $forum->cardBackgroundType;
                $cardBg = $forum->cardBackground;
                $redirect = $forum->redirect == 1 ? true : false;
                $title = $forum->title;
                $description = \stripslashes( $forum->description );
                $archived = $forum->archived == 1 ? true : false;
                $clicks = $forum->totalClicks;
                $totalClicks = self::math()->formatNumber( $forum->totalClicks );
                $lastClick = $forum->lastClick != 0 ? self::dateTime()->parse( $forum->lastClick, [ 'timeAgo' => true ] ) : '---';
                $forumType = $forum->forumType;
            }
        }

        if ( ! $found ) return '';

        $description = \nl2br( $description );
        // TO-DO: Add BB tag replacement here

        if ( \strlen( $image ) > 0 ) {
            $forumImage = self::output()->getPartial( 'ForumsHelper', 'Forum', 'Image', [ 'url' => $image ] );
        } else {
            $forumImage = '';
        }

        switch ( $cardBgType ) {
            case 'color':
                $forumStyle = '';
                break;

            case 'image':
                $forumStyle = self::output()->getPartial( 'ForumsHelper', 'Forum', 'Style', [ 'url' => $cardBg ] );
                break;
        }

        if ( $redirect ) {
            return self::output()->getPartial(
                'ForumsHelper',
                'Forum',
                'RedirectForum',
                [
                    'statusIcon'    => self::output()->getPartial( 'ForumsHelper', 'StatusIcon', 'Redirect' ),
                    'url'           => self::seo()->seoUrl( 'forums', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $forumId, $title ) ] ),
                    'title'         => $title,
                    'description'   => $description,
                    'subForumsLink' => self::getSubForumsLink( $forumId ),
                    'totalClicks'   => self::localization()->quickReplace( 'forumshelper', 'totalClicks' . ( $clicks == 1 ? 'Singular' : 'Plural' ) , 'Total', $totalClicks ),
                    'lastClick'     => $lastClick,
                    'forumImage'    => $forumImage,
                    'forumStyle'    => $forumStyle
                ]
            );
        }

        if ( $archived ) {
            $statusIcon = self::output()->getPartial( 'ForumsHelper', 'StatusIcon', 'Archived' );
            $statusMessage = self::localization()->quickReplace( 'forumshelper', 'statusMessageArchived', 'Type', $forumType == 'normal' ? '' : \sprintf( ' (%s)', self::localization()->getWords( 'forumshelper', 'questionAnswerForum' ) ) );
        } else {
            if ( self::haveUnread( $forumId ) ) {
                $statusIcon = self::output()->getPartial( 'ForumsHelper', 'StatusIcon', 'Unread' . ( $forumType == 'normal' ? '' : 'QA' ) );
                $statusMessage = self::localization()->quickReplace( 'forumshelper', 'statusMessageUnread', 'Type', $forumType == 'normal' ? '' : \sprintf( ' (%s)', self::localization()->getWords( 'forumshelper', 'questionAnswerForum' ) ) );
            } else {
                $statusIcon = self::output()->getPartial( 'ForumsHelper', 'StatusIcon', 'Read' . ( $forumType == 'normal' ? '' : 'QA' ) );
                $statusMessage = self::localization()->quickReplace( 'forumshelper','statusMessageRead', 'Type', $forumType == 'normal' ? '' : \sprintf( ' (%s)', self::localization()->getWords( 'forumshelper', 'questionAnswerForum' ) ) );
            }
        }

        $lastPostData = self::getLastPostData( $forumId );

        if ( $lastPostData->dataAvailable ) {
            if ( $lastPostData->authorId == 0 ) {
                $authorName = self::localization()->getWords( 'global', 'guest' );
                $authorPhoto = self::member()->profilePhoto( 0, true );
            } else {
                $authorName = self::member()->getLink( $lastPostData->authorId );
                $authorPhoto = self::member()->profilePhoto( $lastPostData->authorId, true );
            }

            $totalViewing = self::topics()->getTotalViewingTopic( $lastPostData->topicId );

            $lastPost = self::output()->getPartial(
                'ForumsHelper',
                'Forum',
                'LastPost',
                [
                    'photo'        => $authorPhoto,
                    'url'          => self::utilities()->whichPageIsPostOn( $lastPostData->postId ),
                    'topicTitle'   => self::settings()->forumsLastPostTopicPortionTotalCharacters == 0 ? $lastPostData->title : self::textParsing()->getPortionOfString( $lastPostData->title, self::settings()->forumsLastPostTopicPortionTotalCharacters ),
                    'link'         => $authorName,
                    'timestamp'    => self::dateTime()->parse( $lastPostData->timestamp, [ 'timeAgo' => true ] ),
                    'totalViewing' => self::localization()->quickReplace( 'forumshelper', 'totalViewingTopic' . ( $totalViewing == 1 ? 'Singular' : 'Plural' ), 'Total', self::math()->formatNumber( $totalViewing ) )
                ]
            );
        } else {
            $lastPost = self::output()->getPartial( 'ForumsHelper', 'Forum', 'LastPostNone' );
        }

        $topicsReplies = self::getTotalTopicsReplies( $forumId );

        return self::output()->getPartial(
            'ForumsHelper',
            'Forum',
            'ForumItem',
            [
                'statusIcon'    => $statusIcon,
                'forumImage'    => $forumImage,
                'url'           => self::seo()->seoUrl( 'forums', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $forumId, $title ) ] ),
                'title'         => $title,
                'description'   => $description,
                'totalViewing'  => self::localization()->quickReplace( 'forumshelper', 'totalViewing', 'Total', self::math()->formatNumber( self::getTotalViewingForum( $forumId ) ) ),
                'subForumsLink' => self::getSubForumsLink( $forumId ),
                'totalTopics'   => self::localization()->quickReplace( 'forumshelper', 'totalTopics' . ( $topicsReplies->topics == 1 ? 'Singular' : 'Plural' ), 'Total', $topicsReplies->topicsFormatted ),
                'totalReplies'  => self::localization()->quickReplace( 'forumshelper', 'totalReplies' . ( $topicsReplies->replies == 1 ? 'Singular' : 'Plural' ), 'Total', $topicsReplies->repliesFormatted ),
                'lastPost'      => $lastPost,
                'forumStyle'    => $forumStyle,
                'statusMessage' => $statusMessage
            ]
        );
    }

    public static function listing( $forumId = null, $categoryId = null ) {
        $data = self::cache()->getData( 'forums' );
        $depth = 0;
        $initial = false;
        $found = false;
        $output = new \stdClass();

        if ( $categoryId == null ) {
            if ( $forumId != null ) {
                foreach ( $data as $forum ) {
                    if ( $forum->parentId == $forumId ) {
                        $found = true;
                        break;
                    }
                }

                if ( $found ) {
                    $displaySubForums = false;

                    foreach ( $data as $forum ) {
                        if ( $forum->forumId == $forumId && $forum->displaySubForums == 1 ) {
                            $displaySubForums = true;
                            break;
                        }
                    }

                    if ( $displaySubForums ) {
                        $output->forums .= self::output()->getPartial( 'ForumsHelper', 'Forum', 'SubForumsListBegin' );

                        foreach ( $data as $forum ) {
                            if ( $forum->parentId == $forumId && $forum->hidden == 0 ) {
                                $output->forums .= self::getForum( $forum->forumId, $initial );
                                $initial = $initial ? false : true;
                            }
                        }

                        $output->forums .= self::output()->getPartial( 'ForumsHelper', 'Forum', 'SubForumsListEnd' );
                        $output->forumsExist = true;
                        return $output;
                    }
                } else {
                    $output->forumsExist = false;
                    return $output;
                }
            } else {
                foreach ( $data as $forum ) {
                    if ( $forum->hidden == 0 ) {
                        if ( $forum->depth == 0 ) {
                            if ( $depth > 0 ) {
                                $output->forums .= self::output()->getPartial( 'ForumsHelper', 'Forum', 'CategoryEnd' );
                                $initial = true;
                            }

                            $output->forums .= self::output()->getPartial(
                                'ForumsHelper',
                                'Forum',
                                'CategoryBegin',
                                [
                                    'title' => $forum->title,
                                    'id'    => $forum->forumId,
                                    'url'   => self::seo()->seoUrl( 'forums', 'category', [ 'id' => self::url()->getUrlWithIdAndTitle( $forum->forumId, $forum->title ) ] )
                                ]
                            );

                            $depth++;
                        } elseif ( $forum->depth == 1 ) {
                            $output->forums .= self::getForum( $forum->forumId, $initial );
                            $initial = $initial ? false : true;
                        }
                    }
                }

                $output->forums .= self::output()->getPartial( 'ForumsHelper', 'Forum', 'CategoryEnd' );
                $output->subForumsMenus = self::getSubForumsMenus();
                $output->forumsExist = true;

                return $output;
            }
        }
    }

    public static function getForumBreadcrumbs( $forumId ) {
        $data = self::cache()->getData( 'forums' );
        $crumbs = [];
        $found = false;

        foreach ( $data as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $found = true;
                $depth = $forum->depth;
                $title = $forum->title;
                $parentId = $forum->parentId;
            }
        }

        $crumbs[] = [
            'title' => self::localization()->getWords( 'forumshelper', 'forumsBreadcrumb' ),
            'url'   => self::seo()->seoUrl( 'forums' )
        ];

        if ( $found ) {
            if ( $depth == 1 ) {
                $crumbs[] = [
                    'title' => $title,
                    'url'   => self::seo()->seoUrl( 'forums', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $forumId, $title ) ] )
                ];
            } elseif ( $depth > 1 ) {
                for ( $i = $depth; $i > 0; $i-- ) {
                    foreach ( $data as $forum ) {
                        if ( $forum->forumId == $parentId ) {
                            $crumbs[] = [
                                'title' => $forum->title,
                                'url'   => self::seo()->seoUrl( 'forums', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $forum->forumId, $forum->title ) ] )
                            ];

                            $parentId = $forum->parentId;
                        }
                    }
                }

                if ( $parentId == 0 ) {
                    $crumbs[] = [
                        'title' => $title,
                        'url'   => self::seo()->seoUrl( 'forums', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $forumId, $title ) ] )
                    ];
                }
            }
        }

        return $crumbs;
    }

    public static function permissionsError( $forumId, $type ) {
        $data = self::cache()->getData( 'forums' );

        foreach ( $data as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $permissionsViewForum = $forum->permissionsViewForum == NULL ? false : $forum->permissionsViewForum;
                break;
            }
        }

        switch ( $type ) {
            case 'viewForum':
                if ( $permissionsViewForum != false ) {
                    $permissionsViewForum = \nl2br( $permissionsViewForum );
                    $permissionsViewForum = self::textParsing()->bbTagReplacement( $permissionsViewForum, false, true );

                    self::errors()->throwError( $permissionsViewForum );
                } else {
                    self::errors()->throwError( self::localization()->getWords( 'errors', 'invalidViewForumPermissions' ) );
                }
                break;
        }
    }

    private static function forumPasswordCheck( $forumId, $topicId = null ) {
        $data = self::cache()->getData( 'forums' );

        foreach ( $data as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $passwordProtected = $forum->passwordProtected == 1 ? true : false;
                $password = $forum->password;
                $exemptGroups = \strlen( $forum->passwordExemptGroups ) > 4 ? \unserialize( $forum->passwordExemptGroups ) : null;
                $title = $forum->title;
                break;
            }
        }

        if ( ! $passwordProtected ) {
            return true;
        }

        $exempt = false;

        if ( $exemptGroups != null ) {
            foreach ( $exemptGroups as $group ) {
                if ( $group == self::member()->primaryGroup() ) {
                    $exempt = true;
                }
            }

            if ( ! $exempt ) {
                $secondaryGroups = self::member()->secondaryGroups();

                if ( $secondaryGroups != false ) {
                    foreach ( $exemptGroups as $group ) {
                        foreach ( $secondaryGroups as $groupId ) {
                            if ( $group == $groupId ) {
                                $exempt = true;
                            }
                        }
                    }
                }
            }
        }

        if ( $exempt ) {
            return true;
        }

        if ( ! isset( $_COOKIE['BanditBB_Authorized_Forums'] ) ) {
            return false;
        } else {
            $forumList = \unserialize( $_COOKIE['BanditBB_Authorized_Forums'] );
            
            if ( \count( $forumList ) > 0 ) {
                foreach ( $forumList as $id ) {
                    if ( $id == $forumId ) {
                        return true;
                    }
                }
            }

            return false;
        }
    }

    public static function checkForumPassword( $forumId, $url = null, $error = false ) {
        $data = self::cache()->getData( 'forums' );

        foreach ( $data as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $forumTitle = $forum->title;
            }
        }

        if ( $url == null ) {
            $url = self::seo()->seoUrl( 'forums', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( $forumId, $forumTitle ) ] );
        }

        $captcha = self::captcha()->getCaptcha();

        if ( self::settings()->forumPasswordUseAjax ) {
            $onSubmit = self::output()->getPartial(
                'ForumsHelper',
                'Forum',
                'OnSubmit',
                [
                    'id' => $forumId,
                    'onClick' => self::settings()->forumPasswordCaptchaEnabled ? $captcha->onclick : ''
                ]
            );
        } else {
            $onSubmit = self::settings()->forumPasswordCaptchaEnabled ? $captcha->onsubmit : '';
        }

        if ( ! self::forumPasswordCheck( $forumId ) ) {
            self::output()->renderAlt(
                'ForumsHelper',
                'Password',
                'Form',
                [
                    'errorBox'      => self::utilities()->getErrorBox( $error, $error != false ? true : false, 'forumpassworderrorbox' ),
                    'csrfToken'     => self::security()->getCSRFTokenFormField(),
                    'data'          => \base64_encode( \json_encode( [ 'url' => $url, 'forumId' => $forumId ] ) ),
                    'id'            => self::request()->id,
                    'captcha'       => self::settings()->forumPasswordCaptchaEnabled ? $captcha->captcha : '',
                    'captchaHidden' => self::settings()->forumPasswordCaptchaEnabled ? $captcha->hidden : '',
                    'onSubmit'      => $onSubmit,
                    'pageInfo'      => self::localization()->quickReplace( 'forumshelper', 'forumPasswordPageInfo', 'ForumTitle', $forumTitle )
                ]
            );

            exit;
        }
    }

    public static function handleRedirectForum( $forumId ) {
        $data = self::cache()->getData( 'forums' );
        $update = false;

        foreach ( $data as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $redirect = $forum->redirect == 1 ? true : false;
                $url = \strlen( $forum->redirectUrl ) > 0 ? $forum->redirectUrl : null;
                $useCookies = $forum->redirectUseCookies == 1 ? true : false;
                $totalClicks = $forum->totalClicks;
                break;
            }
        }

        if ( $redirect && $url != null ) {
            if ( $useCookies ) {
                if ( isset( $_COOKIE['BanditBB_Forum_Clicked'] ) ) {
                    $totalClicks++;
                    self::cookies()->newCookie( 'BanditBB_Forum_Clicked', \serialize( [ $forumId ] ), \strtotime( '+10 years', \time() ) );
                    $update = true;
                } else {
                    $cookieData = \unserialize( $_COOKIE['BanditBB_Forum_Clicked'] );
                    $forumFound = false;

                    foreach ( $cookieData as $cookieItem ) {
                        if ( $cookieItem == $forumId ) {
                            $forumFound = true;
                            break;
                        }
                    }

                    if ( ! $forumFound ) {
                        $totalClicks++;
                        $cookieData = \array_push( $cookieData, [ $forumId ] );
                        self::cookies()->newCookie( 'BanditBB_Forum_Clicked', \serialize( $cookieData ), \strtotime( '+10 years', \time() ) );
                        $update = true;
                    }
                }
            } else {
                $totalClicks++;
                $update = true;
            }

            if ( $update ) {
                self::db()->query( self::queries()->updateForumsRedirectClick(), [ 'id' => $forumId, 'totalClicks' => $totalClicks, 'lastClick' => \time() ] );
                self::cache()->update( 'forums' );
            }

            self::redirect()->normalRedirect( $url );
        }
    }

    public static function getForumFilter( $forumId ) {
        $retVal = [];

        if ( isset( self::request()->started ) ) {
            $retVal['started'] = self::request()->started;
            $_SESSION['BanditBB_Filter_Started_ID_' . $forumId] = $retVal['started'];
        } elseif ( isset( $_SESSION['BanditBB_Filter_Started_ID_' . $forumId] ) ) {
            $retVal['started'] = $_SESSION['BanditBB_Filter_Started_ID_' . $forumId];
        } else {
            $retVal['started'] = self::member()->getFilter( 'topics' )->started;
        }

        if ( isset( self::request()->sortby ) ) {
            $retVal['sortBy'] = self::request()->sortby;
            $_SESSION['BanditBB_Filter_SortBy_ID_' . $forumId] = $retVal['sortBy'];
        } elseif ( isset( $_SESSION['BanditBB_Filter_SortBy_ID_' . $forumId] ) ) {
            $retVal['sortBy'] = $_SESSION['BanditBB_Filter_SortBy_ID_' . $forumId];
        } else {
            $retVal['sortBy'] = self::member()->getFilter( 'topics' )->sortBy;
        }

        if ( isset( self::request()->sortorder ) ) {
            $retVal['sortOrder'] = self::request()->sortorder;
            $_SESSION['BanditBB_Filter_SortOrder_ID_' . $forumId] = $retVal['sortOrder'];
        } elseif ( isset( $_SESSION['BanditBB_SortOrder_ID_' . $forumId] ) ) {
            $retVal['sortOrder'] = $_SESSION['BanditBB_SortOrder_ID_' . $forumId];
        } else {
            $retVal['sortOrder'] = self::member()->getFilter( 'topics' )->sortOrder;
        }

        $startedList = [ 'anytime', '1day', '2days', '3days', '4days', '5days', '6days', '1week', '2weeks', '3weeks', '1month', '3months', '6months', '9months', '1year', '2years' ];
        $sortByList = [ 'lastPostTimestamp', 'createdTimestamp', 'title', 'totalReplies', 'totalViews' ];
        $sortOrderList = [ 'ASC', 'DESC' ];

        $retVal['startedVars'] = [
            'anytime' => '',
            '1day'    => '',
            '2days'   => '',
            '3days'   => '',
            '4days'   => '',
            '5days'   => '',
            '6days'   => '',
            '1week'   => '',
            '2weeks'  => '',
            '3weeks'  => '',
            '1month'  => '',
            '3months' => '',
            '6months' => '',
            '9months' => '',
            '1year'   => '',
            '2years'  => ''
        ];

        $retVal['sortByVars'] = [
            'lastPostTimestamp' => '',
            'createdTimestamp'  => '',
            'title'             => '',
            'totalReplies'      => '',
            'totalViews'        => ''
        ];

        $retVal['sortOrderVars'] = [
            'ASC'  => '',
            'DESC' => ''
        ];

        foreach ( $startedList as $item ) {
            if ( $retVal['started'] == $item ) $retVal['startedVars'][$item] = ' selected';
        }

        foreach ( $sortByList as $item ) {
            if ( $retVal['sortBy'] == $item ) $retVal['sortByVars'][$item] = ' selected';
        }

        foreach ( $sortOrderList as $item ) {
            if ( $retVal['sortOrder'] == $item ) $retVal['sortOrderVars'][$item] = ' selected';
        }

        return $retVal;
    }

    private static function filterMapping( $type ) {
        $retVal = '';

        switch ( $type ) {
            case 'sortby':
                $retVal = [
                    'lastPostTimestamp' => self::localization()->getWords( 'forumshelper', 'filterLastPostTimestamp' ),
                    'createdTimestamp'  => self::localization()->getWords( 'forumshelper', 'filterCreatedTimestamp' ),
                    'title'             => self::localization()->getWords( 'forumshelper', 'filterTitle' ),
                    'totalReplies'      => self::localization()->getWords( 'forumshelper', 'filterTotalReplies' ),
                    'totalViews'        => self::localization()->getWords( 'forumshelper', 'filterTotalViews' )
                ];
                break;

            case 'sortorder':
                $retVal = [
                    'ASC'  => self::localization()->getWords( 'forumshelper', 'filterAscending' ),
                    'DESC' => self::localization()->getWords( 'forumshelper', 'filterDescending' )
                ];
                break;
        }

        return $retVal;
    }

    public static function getStartedFilterWords() {
        $retVal = [];

        $retVal['anytime'] = self::localization()->getWords( 'forumshelper', 'filterAnytime' );
        $retVal['1day'] = self::localization()->quickReplace( 'forumshelper', 'filterDay', 'Total', '1' );
        $retVal['2days'] = self::localization()->quickReplace( 'forumshelper', 'filterDays', 'Total', '2' );
        $retVal['3days'] = self::localization()->quickReplace( 'forumshelper', 'filterDays', 'Total', '3' );
        $retVal['4days'] = self::localization()->quickReplace( 'forumshelper', 'filterDays', 'Total', '4' );
        $retVal['5days'] = self::localization()->quickReplace( 'forumshelper', 'filterDays', 'Total', '5' );
        $retVal['6days'] = self::localization()->quickReplace( 'forumshelper', 'filterDays', 'Total', '6' );
        $retVal['1week'] = self::localization()->quickReplace( 'forumshelper', 'filterWeek', 'Total', '1' );
        $retVal['2weeks'] = self::localization()->quickReplace( 'forumshelper', 'filterWeeks', 'Total', '2' );
        $retVal['3weeks'] = self::localization()->quickReplace( 'forumshelper', 'filterWeeks', 'Total', '3' );
        $retVal['1month'] = self::localization()->quickReplace( 'forumshelper', 'filterMonth', 'Total', '1' );
        $retVal['3months'] = self::localization()->quickReplace( 'forumshelper', 'filterMonths', 'Total', '3' );
        $retVal['6months'] = self::localization()->quickReplace( 'forumshelper', 'filterMonths', 'Total', '6' );
        $retVal['9months'] = self::localization()->quickReplace( 'forumshelper', 'filterMonths', 'Total', '9' );
        $retVal['1year'] = self::localization()->quickReplace( 'forumshelper', 'filterYear', 'Total', '1' );
        $retVal['2years'] = self::localization()->quickReplace( 'forumshelper', 'filterYears', 'Total', '2' );

        return $retVal;
    }

    public static function getFilterDropDown( $forumId ) {
        $data = self::cache()->getData( 'forums' );
        $filter = self::getForumFilter( $forumId );
        $words = self::getStartedFilterWords();
        $options = new \stdClass();
        $options->timeframe = '';
        $options->sortBy = '';
        $options->sortOrder = '';

        foreach ( $data as $forum ) {
            if ( $forum->forumId == $forumId ) {
                $forumTitle = $forum->title;
                break;
            }
        }

        foreach ( $filter['startedVars'] as $k => $v ) {
            $options->timeframe .= self::output()->getPartial(
                'Global',
                'Select',
                'Option',
                [
                    'selected' => $v,
                    'name'     => $words[$k],
                    'value'    => $k
                ]
            );
        }

        foreach ( $filter['sortByVars'] as $k => $v ) {
            $options->sortBy .= self::output()->getPartial(
                'Global',
                'Select',
                'Option',
                [
                    'selected' => $v,
                    'name'     => self::filterMapping( 'sortby' )[$k],
                    'value'    => $k
                ]
            );
        }

        foreach ( $filter['sortOrderVars'] as $k => $v ) {
            $options->sortOrder .= self::output()->getPartial(
                'Global',
                'Select',
                'Option',
                [
                    'selected' => $v,
                    'name'     => self::filterMapping( 'sortorder' )[$k],
                    'value'    => $k
                ]
            );
        }

        return self::output()->getPartial(
            'ForumsHelper',
            'Filter',
            'DropDownMenu',
            [
                'timeframe' => $options->timeframe,
                'sortBy'    => $options->sortBy,
                'sortOrder' => $options->sortOrder,
                'forumId'   => self::url()->getUrlWithIdAndTitle( $forumId, $forumTitle ),
                'page'      => isset( self::request()->page ) ? self::request()->page : 1
            ]
        );
    } 
}