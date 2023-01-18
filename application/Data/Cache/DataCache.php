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

namespace BanditBB\Data\Cache;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class DataCache extends \BanditBB\Application {

    protected static $instance;
    protected static $tables = [];
    protected static $sorting = [];

    public function __construct() {
        self::$tables = [
            'application_settings',
            'feature_permissions',
            'forums',
            'forums_read',
            'forum_permissions',
            'groups',
            'installed_localizations',
            'installed_themes',
            'localization',
            'members',
            'members_attachments',
            'members_devices',
            'members_following',
            'members_likes',
            'members_notifications',
            'members_photos',
            'members_reputation',
            'messages_map',
            'messages_posts',
            'messages_topics',
            'polls',
            'posts',
            'registry',
            'sessions',
            'session_store',
            'topics'
        ];

        self::$sorting = [
            'application_settings'    => ' ORDER BY settingId ASC',
            'forums'                  => null,
            'forums_read'             => ' ORDER BY readId ASC',
            'groups'                  => ' ORDER BY sorting ASC',
            'installed_localizations' => ' ORDER BY localizationsId ASC',
            'installed_themes'        => ' ORDER BY themeId ASC',
            'localization'            => ' ORDER BY localizationId ASC',
            'members'                 => ' ORDER BY memberId ASC',
            'members_devices'         => ' ORDER BY memberId ASC',
            'members_photos'          => ' ORDER BY photoId ASC',
            'sessions'                => ' ORDER BY memberId ASC',
            'registry'                => ' ORDER BY registryId ASC',
            'topics'                  => ' ORDER BY topicId ASC',
            'posts'                   => ' ORDER BY postId ASC',
            'members_notifications'   => ' ORDER BY notificationId ASC',
            'messages_map'            => ' ORDER BY mapId ASC',
            'messages_posts'          => ' ORDER BY postId ASC',
            'messages_topics'         => ' ORDER BY topicId ASC',
            'forum_permissions'       => ' ORDER BY permissionId ASC',
            'members_following'       => ' ORDER BY followingId ASC',
            'members_attachments'     => ' ORDER BY attachmentId ASC',
            'members_reputation'      => ' ORDER BY reputationId ASC',
            'members_likes'           => ' ORDER BY likeId ASC',
            'polls'                   => ' ORDER BY pollId ASC',
            'feature_permissions'     => ' ORDER BY permissionId ASC'
        ];
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }
}