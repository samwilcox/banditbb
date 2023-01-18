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

namespace BanditBB\Data\Queries;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class MySqliQueries implements \BanditBB\Data\QueriesStructure {

    protected static $instance;
    protected static $connInfo;
    protected $prefix = '';

    public function __construct() {
        require ( APP_PATH . 'Config.inc.php' );
        self::$connInfo = isset( $cfg ) ? $cfg : [];
        $this->prefix = self::$connInfo['dbPrefix'];
    }

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

public function selectStoredCache() {
return <<<QUERY
SELECT * FROM {$this->prefix}stored_cache
QUERY;
}

public function selectForumsWithDepth() {
return <<<QUERY
SELECT node.*, (COUNT(parent.title) - 1) AS depth FROM {$this->prefix}forums AS node, {$this->prefix}forums AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt GROUP BY node.title ORDER BY node.lft
QUERY;
}

public function selectForumsWithDepthCache() {
return <<<QUERY
/*qc=on*/SELECT node.*, (COUNT(parent.title) - 1) AS depth FROM {$this->prefix}forums AS node, {$this->prefix}forums AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt GROUP BY node.title ORDER BY node.lft
QUERY;
}

public function selectForCache( $data = [] ) {
return <<<QUERY
SELECT * FROM `{$this->prefix}{$data['table']}`{$data['sorting']}
QUERY;
}

public function selectForCacheCached( $data = [] ) {
return <<<QUERY
/*qc=on*/SELECT * FROM `{$this->prefix}{$data['table']}`{$data['sorting']}
QUERY;
}

public function updateCacheData() {
return <<<QUERY
UPDATE {$this->prefix}stored_cache SET data = '{{toCache}}' WHERE title = '{{table}}'
QUERY;
}

public function insertUserSession() {
return <<<QUERY
INSERT INTO {$this->prefix}sessions
VALUES (
'{{id}}',
'{{memberId}}',
'{{memberUsername}}',
'{{expires}}',
'{{lastClick}}',
'{{location}}',
'{{forumId}}',
'{{topicId}}',
'{{ipAddress}}',
'{{userAgent}}',
'{{hostname}}',
'{{displayOnList}}',
'{{isBot}}',
'{{botName}}',
'{{adminSession}}'
)
QUERY;
}

public function updateUserSession() {
return <<<QUERY
UPDATE {$this->prefix}sessions
SET
expires = '{{expires}}',
lastClick = '{{lastClick}}',
location = '{{location}}',
displayOnList = '{{displayOnList}}',
forumId = '{{forumId}}',
topicId = '{{topicId}}'
WHERE sessionId = '{{id}}'
QUERY;
}

public function deleteUserSession() {
return <<<QUERY
DELETE FROM {$this->prefix}sessions WHERE sessionId = '{{id}}'
QUERY;
}

public function deleteUserSessionGc() {
return <<<QUERY
DELETE FROM {$this->prefix}sessions WHERE expires < UNIX_TIMESTAMP();
QUERY;
}

public function selectSessionDataFromStore() {
return <<<QUERY
SELECT * FROM {$this->prefix}session_store WHERE storeId = '{{id}}' AND expires > '{{time}}'
QUERY;
}

public function selectSessionFromStore() {
return <<<QUERY
SELECT storeId FROM {$this->prefix}session_store WHERE storeId = '{{id}}'
QUERY;
}

public function insertSessionStoreNew() {
return <<<QUERY
INSERT INTO {$this->prefix}session_store
VALUES (
'{{id}}',
'{{data}}',
'{{lifetime}}'
)
QUERY;
}

public function updateSessionStoreData() {
return <<<QUERY
UPDATE {$this->prefix}session_store
SET
id = '{{id}}',
data = '{{data}}',
lifetime = '{{lifetime}}'
WHERE storeId = '{{id}}'
QUERY;
}

public function deleteFromSessionStore() {
return <<<QUERY
DELETE FROM {$this->prefix}session_store WHERE storeId = '{{id}}'
QUERY;
}

public function deleteFromSessionStoreGc() {
return <<<QUERY
DELETE FROM {$this->prefix}session_store WHERE lifetime < UNIX_TIMESTAMP();
QUERY;
}

public function insertIntoRegistry() {
return <<<QUERY
INSERT INTO {$this->prefix}registry
VALUES (
null,
'{{name}}',
'{{value}}',
'{{type}}'
)
QUERY;
}

public function updateRegistry() {
return <<<QUERY
UPDATE {$this->prefix}registry
SET
keyValue = '{{value}}',
valueType = '{{type}}'
WHERE registryId = '{{id}}'
QUERY;
}

public function updateMemberRemoveLockout() {
return <<<QUERY
UPDATE {$this->prefix}members
SET
signInAttempts = '0',
lockedOut = '0',
lockoutExpires = '0'
WHERE memberId = '{{id}}'
QUERY;
}

public function updateMemberLockout() {
return <<<QUERY
UPDATE {$this->prefix}members
SET
signInAttempts = '{{attempts}}',
lockedOut = '1',
lockoutExpires = '{{expires}}'
WHERE memebrId = '{{id}}'
QUERY;
}

public function updateMemberNewAttempt() {
return <<<QUERY
UPDATE {$this->prefix}members
SET
signInAttempts = '{{attempts}}'
WHERE memberId = '{{id}}'
QUERY;
}

public function updateMembersDevices() {
return <<<QUERY
UPDATE {$this->prefix}members_devices
SET
signInKey = '{{token}}',
lastUsed = '{{timestamp}}',
userAgent = '{{userAgent}}'
WHERE deviceId = '{{id}}'
QUERY;
}

public function insertMembersDevices() {
return <<<QUERY
INSERT INTO {$this->prefix}members_devices
VALUES (
'{{deviceId}}',
'{{memberId}}',
'{{token}}',
'{{userAgent}}',
'{{timestamp}}'
)
QUERY;
}

public function updateUserSessionIdAndName() {
return <<<QUERY
UPDATE {$this->prefix}sessions
SET
memberId = '0',
memberUsername = 'Guest'
WHERE memberId = '{{id}}'
QUERY;
}

public function updateMemberSession() {
return <<<QUERY
UPDATE {$this->prefix}sessions
SET
memberId = '{{memberId}}',
memberUsername = '{{username}}',
displayOnList = '{{displayOnList}}'
WHERE sessionId = '{{id}}'
QUERY;
}

public function updateMembersDevicesRemoveKey() {
return <<<QUERY
UPDATE {$this->prefix}members_devices
SET
signInKey = '',
lastUsed = '{{timestamp}}',
userAgent = '{{userAgent}}'
WHERE deviceId = '{{id}}'
QUERY;
}

public function updateMembersLastOnline() {
return <<<QUERY
UPDATE {$this->prefix}members
SET
lastOnlineTimestamp = '{{timestamp}}'
WHERE memberId = '{{id}}'
QUERY;
}

public function updateForumsRedirectClick() {
return <<<QUERY
UPDATE {$this->prefix}forums
SET
totalClicks = '{{totalClicks}}',
lastClick = '{{lastClick}}'
WHERE forumId = '{{id}}'
QUERY;
}

public function selectTopicsLimit() {
return <<<QUERY
SELECT * FROM {$this->prefix}topics
WHERE forumId = '{{forumId}}' AND pinned = '0'{{timeframe}}
ORDER BY {{sortBy}} {{sortOrder}}
LIMIT {{from}}, {{perPage}}
QUERY;
}

public function updateSessionBrowsing() {
return <<<QUERY
UPDATE {$this->prefix}sessions
SET
forumId = '{{forumId}}',
topicId = '{{topicId}}'
WHERE sessionId = '{{id}}'
QUERY;
}

public function insertNewFollowing() {
return <<<QUERY
INSERT INTO {$this->prefix}members_following
VALUES (
NULL,
'{{forumId}}',
'{{topicId}}',
'{{memberId}}',
'{{followingForum}}',
'{{followingTopic}}',
'{{timestamp}}',
'{{displayInList}}',
'{{periodic}}',
'0'
)
QUERY;
}

public function updateFollowingList() {
return <<<QUERY
UPDATE {$this->prefix}members_following
SET
displayInList = '{{display}}'
WHERE followingId = '{{id}}'
QUERY;
}

public function deleteFollowing() {
return <<<QUERY
DELETE FROM {$this->prefix}members_following
WHERE followingId = '{{id}}'
QUERY;
}

public function insertAttachment() {
return <<<QUERY
INSERT INTO {$this->prefix}members_attachments
VALUES (
NULL,
'{{fileName}}',
'{{fileSize}}',
'0',
'0',
'{{memberId}}'
)
QUERY;
}

public function insertProfilePhoto() {
return <<<QUERY
INSERT INTO {$this->prefix}members_photos
VALUES (
NULL,
'{{fileName}}',
'{{fileSize}}',
'{{timestamp}}'
)
QUERY;
}

public function deleteAttachment() {
return <<<QUERY
DELETE FROM {$this->prefix}members_attachments
WHERE attachmentId = '{{id}}'
QUERY;
}

public function deleteProfilePhoto() {
return <<<QUERY
DELETE FROM {$this->prefix}members_photos
WHERE photoId = '{{id}}'
QUERY;
}

public function updateMembersProfilePhoto() {
return <<<QUERY
UPDATE {$this->prefix}members
SET
photoType = '{{type}}',
photoId = '{{photoId}}'
WHERE memberId = '{{id}}'
QUERY;
}

public function updateTopicViews() {
return <<<QUERY
UPDATE {$this->prefix}topics
SET
totalViews = '{{totalViews}}'
WHERE topicId = '{{id}}'
QUERY;
}

public function insertForumsRead() {
return <<<QUERY
INSERT INTO {$this->prefix}forums_read
VALUES (
NULL,
'{{forumId}}',
'{{topicId}}',
'{{memberId}}',
'{{time}}'
)
QUERY;
}

public function updateForumsRead() {
return <<<QUERY
UPDATE {$this->prefix}forums_read
SET
lastReadTimestamp = '{{time}}'
WHERE readId = '{{id}}'
QUERY;
}

public function selectPostsLimit() {
return <<<QUERY
SELECT * FROM {$this->prefix}posts
WHERE topicId = '{{id}}'
ORDER BY 'postedTimestamp' ASC
LIMIT {{from}}, {{perPage}}
QUERY;
}

public function insertLike() {
return <<<QUERY
INSERT INTO {$this->prefix}members_likes
VALUES (
NULL,
'{{memberId}}',
'{{contentId}}',
'{{contentType}}',
'{{timestamp}}'
)
QUERY;
}

public function deleteLike() {
return <<<QUERY
DELETE FROM {$this->prefix}members_likes
WHERE likeId = '{{id}}'
QUERY;
}

public function insertNewPost() {
return <<<QUERY
INSERT INTO {$this->prefix}posts
VALUES (
NULL,
'{{topicId}}',
'{{time}}',
'{{memberId}}',
'{{message}}',
'{{attachments}}',
'{{showSignature}}',
'{{ipAddress}}'
)
QUERY;
}

public function updateTopicLastPost() {
return <<<QUERY
UPDATE {$this->prefix}topics
SET
lastPostMemberId = '{{memberId}}',
lastPostTimestamp = '{{timestamp}}',
totalReplies = '{{totalReplies}}'
WHERE topicId = '{{id}}'
QUERY;
}

public function insertNewPoll() {
return <<<QUERY
INSERT INTO {$this->prefix}polls
VALUES (
NULL,
'{{question}}',
'{{choices}}',
'{{startedTimestamp}}',
'{{startedMemberId}}',
'0',
'{{pollOnly}}',
'0',
'{{closeTimestamp}}',
''
)
QUERY;
}

public function insertNewTopic() {
return <<<QUERY
INSERT INTO {$this->prefix}topics
VALUES (
NULL,
'{{forumId}}',
'{{memberId}}',
'{{timestamp}}',
'{{title}}',
'{{memberId}}',
'{{timestamp}}',
'0',
'0',
'0',
'0',
'0',
'0',
'0',
'',
'0',
'0',
'0',
'0',
'{{pollId}}'
)
QUERY;
}

public function updatePollClosed() {
return <<<QUERY
UPDATE {$this->prefix}polls
SET
closed = '{{closed}}'
WHERE pollId = '{{id}}'
QUERY;
}

public function updatePoll() {
return <<<QUERY
UPDATE {$this->prefix}polls
SET
choices = '{{choices}}',
totalVotes = '{{totalVotes}}',
voters = '{{voters}}'
WHERE pollId = '{{id}}'
QUERY;
}

public function updatePollVoters() {
return <<<QUERY
UPDATE {$this->prefix}polls
SET
voters = '{{voters}}'
WHERE pollId = '{{id}}'
QUERY;
}

public function updateSessionAdminSession() {
return <<<QUERY
UPDATE {$this->prefix}sessions
SET
adminSession = '{{adminSession}}'
WHERE sessionId = '{{id}}'
QUERY;
}

public function updateFeaturePermissionState() {
return <<<QUERY
UPDATE {$this->prefix}feature_permissions
SET
enabled = '{{state}}'
WHERE permissionId = '{{id}}'
QUERY;
}

}