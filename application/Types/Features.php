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

namespace BanditBB\Types;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Features extends \BanditBB\Types\Types {

    const MEMBERS = 'members';
    const CALENDAR = 'calendar';
    const SEARCH = 'search';
    const HELP = 'help';
    const EXPANDED_MENU = 'expandedMenu';
    const LATEST_CONTENT = 'latestContent';
    const COMMUNITY_LEADERS = 'communityLeaders';
    const WHOS_ONLINE = 'whosOnline';
    const DELETE_COOKIES = 'deleteCookies';
    const MARK_ALL_READ = 'markAllRead';
    const DEBUG_INFORMATION = 'debugInformation';
    const NOTIFICATIONS = 'notifications';
    const MESSAGES = 'messages';
    const TEXT_EDITOR_TOOLBAR = "textEditorToolbar";
    const TEXT_EDITOR_FONT_SELECTOR = "textEditorFontSelector";
    const TEXT_EDITOR_FONT_SIZE_SELECTOR = "textEditorFontSizeSelector";
    const TEXT_EDITOR_FONT_COLOR_SELECTOR = "textEditorFontColorSelector";
    const TEXT_EDITOR_BOLD = "textEditorBold";
    const TEXT_EDITOR_ITALIC = "textEditorItalic";
    const TEXT_EDITOR_UNDERLINE = "textEditorUnderline";
    const TEXT_EDITOR_STRIKETHROUGH = "textEditorStrikethrough";
    const TEXT_EDITOR_SUBSCRIPT = "textEditorSubscript";
    const TEXT_EDITOR_SUPERSCRIPT = "textEditorSuperscript";
    const TEXT_EDITOR_ALIGN_LEFT = "textEditorAlignLeft";
    const TEXT_EDITOR_ALIGN_CENTER = "textEditorAlignCenter";
    const TEXT_EDITOR_ALIGN_RIGHT = "textEditorAlignRight";
    const TEXT_EDITOR_JUSTIFY_FULL = "textEditorJustifyFull";
    const TEXT_EDITOR_DECREASE_INDENT = "textEditorDecreaseIndent";
    const TEXT_EDITOR_INCREASE_INDENT = "textEditorIncreaseIndent";
    const TEXT_EDITOR_ORDERED_LIST = "textEditorOrderedList";
    const TEXT_EDITOR_UNORDERED_LIST = "textEditorUnOrderedList";
    const TEXT_EDITOR_HORIZONTAL_RULE = "textEditorHorizontalRule";
    const TEXT_EDITOR_UNDO = "textEditorUndo";
    const TEXT_EDITOR_REDO = "textEditorRedo";
    const TEXT_EDITOR_INSERT_QUOTE = "textEditorInsertQuote";
    const TEXT_EDITOR_INSERT_LINK = "textEditorInsertLink";
    const TEXT_EDITOR_INSERT_IMAGE = "textEditorInsertImage";
    const TEXT_EDITOR_INSERT_VIDEO = "textEditorInsertVideo";
    const TEXT_EDITOR_INSERT_CODE = "textEditorInsertCode";
    const TEXT_EDITOR_INSERT_EMOTICON = "textEditorInserEmoji";
    const TEXT_EDITOR_INSERT_GIF = 'textEditorInsertGif';
    const TOPIC_RSS_FEED = 'topicRssFeed';
    const REPUTATION = 'reputation';
    const FRIENDS = 'friends';
}