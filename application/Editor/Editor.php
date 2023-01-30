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

namespace BanditBB\Editor;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Editor extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getEditor() {
        self::vars()->editorActive = true;

        if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_TOOLBAR ) ) {
            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_FONT_SELECTOR ) ) {
                $fontSelectorDropDown = self::output()->getPartial( 'Editor', 'Toolbar', 'FontSelectorDropDown' );
                $fontSelector = self::output()->getPartial( 'Editor', 'Toolbar', 'FontSelector' );  
            } else {
                $fontSelectorDropDown = '';
                $fontSelector = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_FONT_SIZE_SELECTOR ) ) {
                $fontSizeSelectorDropDown = self::output()->getPartial( 'Editor', 'Toolbar', 'FontSizeSelectorDropDown' );
                $fontSizeSelector = self::output()->getPartial( 'Editor', 'Toolbar', 'FontSizeSelector' );
            } else {
                $fontSizeSelectorDropDown = '';
                $fontSizeSelector = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_FONT_COLOR_SELECTOR ) ) {
                $fontColorSelector = self::output()->getPartial( 'Editor', 'Toolbar', 'FontColorSelector' );
            } else {
                $fontColorSelector = '';
            }

            $bold = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_BOLD ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'Bold' ) : '';
            $italic = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_ITALIC ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'Italic' ) : '';
            $underline = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_UNDERLINE ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'Underline' ) : '';
            $strikethrough = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_STRIKETHROUGH ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'Strikethrough' ) : '';
            $subscript = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_SUBSCRIPT ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'Subscript' ) : '';
            $superscript = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_SUPERSCRIPT ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'Superscript' ) : '';
            $alignLeft = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_ALIGN_LEFT ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'AlignLeft' ) : '';
            $alignCenter = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_ALIGN_CENTER ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'AlignCenter' ) : '';
            $alignRight = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_ALIGN_RIGHT ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'AlignRight' ) : '';
            $outdent = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_DECREASE_INDENT ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'Outdent' ) : '';
            $indent = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_INCREASE_INDENT ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'Indent' ) : '';
            $orderedList = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_ORDERED_LIST ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'OrderedList' ) : '';
            $unorderedList = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_UNORDERED_LIST ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'UnorderedList' ) : '';
            $horizontalRule = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_HORIZONTAL_RULE ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'HorizontalRule' ) : '';
            $undo = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_UNDO ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'Undo' ) : '';
            $redo = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_REDO ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'Redo' ) : '';
            $quote = self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_INSERT_QUOTE ) ? self::output()->getPartial( 'Editor', 'Toolbar', 'Quote' ) : '';

            if ( \strlen( $alignLeft ) > 0 || \strlen( $alignCenter ) > 0 || \strlen( $alignRight ) > 0 ) {
                $align = self::output()->getPartial( 'Editor', 'Toolbar', 'Align' );
                $alignDropDown = self::output()->getPartial(
                    'Editor',
                    'Toolbar',
                    'AlignDropDown',
                    [
                        'left'   => $alignLeft,
                        'center' => $alignCenter,
                        'right'  => $alignRight
                    ]
                );

                $showAlign = true;
            } else {
                $align = '';
                $alignDropDown = '';
                $showAlign = false;
            }

            $bar = self::output()->getPartial( 'Editor', 'Toolbar', 'Bar' );

            if ( \strlen( $bold ) > 0 || \strlen( $italic ) > 0 || \strlen( $underline ) > 0 || \strlen( $strikethrough ) > 0 || \strlen( $subscript ) > 0 || \strlen( $superscript ) > 0 ) {
                $textModificationBar = $bar;
            } else {
                $textModificationBar = '';
            }

            if ( \strlen( $indent ) > 0 || \strlen( $outdent ) > 0 || $showAlign ) {
                $alignmentBar = $bar;
            } else {
                $alignmentBar = '';
            }

            if ( \strlen( $unorderedList ) > 0 || \strlen( $orderedList ) > 0 || \strlen( $horizontalRule ) > 0 ) {
                $listsBar = $bar;
            } else {
                $listsBar = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_INSERT_LINK ) ) {
                $insertLinkDropDown = self::output()->getPartial( 'Editor', 'Toolbar', 'InsertLinkDropDown' );
                $insertLink = self::output()->getPartial( 'Editor', 'Toolbar', 'InsertLink' );
            } else {
                $insertLinkDropDown = '';
                $insertLink = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_INSERT_IMAGE ) ) {
                $insertImageDropDown = self::output()->getPartial( 'Editor', 'Toolbar', 'InsertImageDropDown' );
                $insertImage = self::output()->getPartial( 'Editor', 'Toolbar', 'InsertImage' );
            } else {
                $insertImageDropDown = '';
                $insertImage = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_INSERT_VIDEO ) ) {
                $insertVideoDropDown = self::output()->getPartial( 'Editor', 'Toolbar', 'InsertVideoDropDown' );
                $insertVideo = self::output()->getPartial( 'Editor', 'Toolbar', 'InsertVideo' );
            } else {
                $insertVideoDropDown = '';
                $insertVideo = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_INSERT_CODE ) ) {
                $options = '';
                $initial = true;

                if ( \count( self::settings()->codeLanguages ) > 0 ) {
                    foreach ( self::settings()->codeLanguages as $lang ) {
                        $options .= self::output()->getPartial(
                            'Global',
                            'Select',
                            'Options',
                            [
                                'name'     =>  $lang,
                                'value'    => \strtolower( $lang ),
                                'selected' => $initial ? ' selected' : ''
                            ]
                        );

                        if ( $initial ) $initial = false;
                    }
                }

                $insertCodeDialog = self::output()->getPartial( 'Editor', 'Toolbar', 'InsertCodeDialog', [ 'options' => $options ] );
                $insertCode = self::output()->getPartial( 'Editor', 'Toolbar', 'InsertCode' );
            } else {
                $insertCodeDialog = '';
                $insertCode = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::TEXT_EDITOR_INSERT_EMOTICON ) ) {
                $emoticonList = @scandir( ROOT_PATH . 'public/emoticons' );
                $emoticons = '';
                $count = 1;

                foreach ( $emoticonList as $icon ) {
                    if ( $icon != '.' && $icon != '..' ) {
                        $count++;
                        $newLine = $count % 8 == 1 ? self::output()->getPartial( 'Editor', 'Toolbar', 'NewLine' ) : '';
                        $emoticons .= self::output()->getPartial( 'Editor', 'Toolbar', 'Emoticon', [ 'src' => self::vars()->baseUrl . '/public/emoticons/' . $icon, 'newLine' => $newLine ] );
                    }
                }

                $insertEmoticonDropDown = self::output()->getPartial( 'Editor', 'Toolbar', 'InsertEmoticonDropDown', [ 'emoticons' => $emoticons ] );
                $insertEmoticon = self::output()->getPartial( 'Editor', 'Toolbar', 'InsertEmoticon' );
            } else {
                $insertEmoticonDropDown = '';
                $insertEmoticon = '';
            }

            if ( \strlen( $insertLink ) > 0 || \strlen( $insertImage ) > 0 || \strlen( $insertVideo ) > 0 || \strlen( $insertEmoticon ) > 0 ) {
                $insertContentBar = $bar;
            } else {
                $insertContentBar = '';
            }

            if ( \strlen( $quote ) > 0 || \strlen( $insertCode ) > 0 ) {
                $quoteCodeBar = $bar;
            } else {
                $quoteCodeBar = '';
            }

            $toolbar = self::output()->getPartial(
                'Editor',
                'Toolbar',
                'Base',
                [
                    'fontSelector'      => $fontSelector,
                    'fontSizeSelector'  => $fontSizeSelector,
                    'fontColorSelector' => $fontColorSelector,
                    'bold'              => $bold,
                    'italic'            => $italic,
                    'underline'         => $underline,
                    'strikethrough'     => $strikethrough,
                    'subscript'         => $subscript,
                    'superscript'       => $superscript,
                    'textModBar'        => $textModificationBar,
                    'outdent'           => $outdent,
                    'indent'            => $indent,
                    'align'             => $align,
                    'alignBar'          => $alignmentBar,
                    'unorderedList'     => $unorderedList,
                    'orderedList'       => $orderedList,
                    'horizontalRule'    => $horizontalRule,
                    'listsBar'          => $listsBar,
                    'insertLink'        => $insertLink,
                    'insertImage'       => $insertImage,
                    'insertVideo'       => $insertVideo,
                    'insertEmoticon'    => $insertEmoticon,
                    'insertContentBar'  => $insertContentBar,
                    'quote'             => $quote,
                    'insertCode'        => $insertCode,
                    'quoteCodeBar'      => $quoteCodeBar,
                    'undo'              => $undo,
                    'redo'              => $redo
                ]
            );
        } else {
            $toolbar = '';
        }

        return self::output()->getPartial(
            'Editor',
            'Editor',
            'Base',
            [
                'fontSelectorDropDown'     => $fontSelectorDropDown,
                'fontSizeSelectorDropDown' => $fontSizeSelectorDropDown,
                'insertLinkDropDown'       => $insertLinkDropDown,
                'insertImageDropDown'      => $insertImageDropDown,
                'insertVideoDropDown'      => $insertVideoDropDown,
                'insertCodeDialog'         => $insertCodeDialog,
                'insertEmoticonDropDown'   => $insertEmoticonDropDown,
                'toolbar'                  => $toolbar,
                'alignDropDown'            => $alignDropDown
            ]
        );
    }

    public static function getUploader( $marginTop = false ) {
        self::vars()->editorActive = true;

        return self::output()->getPartial(
            'Editor',
            'Uploader',
            'Base',
            [
                'maxFileSize'  => self::localization()->quickReplace( 'uploader', 'maxFileSize', 'FileSize', self::file()->getReadableFileSize( self::settings()->maxFileUploadSize ) ),
                'uploadOrDrag' => self::localization()->quickReplace( 'uploader', 'uploadFilesOrDrag', 'Link', self::output()->getPartial( 'Editor', 'Uploader', 'ChooseFiles' ) ),
                'marginTop'    => $marginTop ? self::output()->getPartial( 'Global', 'Margin', 'Top' ) : '',
                'imagesetUrl'  => self::member()->imagesetUrl()
            ]
        );
    }

    public static function getQuickEditor( $options = [] ) {
        $hiddenFields = '';
        $optionsForm = false;
        $signature = '';
        $follow = '';
        $total = 0;
        $followingArr = [];
        $spacer = '';

        if ( isset( $options['fields'] ) && \is_array( $options['fields'] ) && \count( $options['fields'] ) > 0 ) {
            foreach ( $options['fields'] as $k => $v ) {
                $hiddenFields .= self::output()->getPartial( 'Global', 'Fields', 'Hidden', [ 'name' => $k, 'value' => $v ] );
            }
        }

        if ( self::member()->signedIn() && ( ( isset( $options['signature'] ) && $options['signature'] ) || ( isset( $options['follow'] ) && $options['follow'] ) ) ) {
            $optionsForm = true;
            $oneAlready = false;

            if ( isset( $options['signature'] ) && $options['signature'] ) {
                $oneAlready = true;
                $signature = self::output()->getPartial( 'Global', 'Checkbox', 'IncludeSignature', [ 'checked' => self::member()->signatureEnabled() ? ' checked' : '' ] );
            }

            if ( $oneAlready ) {
                $spacer = self::output()->getPartial( 'Editor', 'QuickReply', 'Spacer' );
            }

            if ( isset( $options['follow'] ) && $options['follow'] ) {
                if ( isset( $options['topicId'] ) ) {
                    $data = self::cache()->getData( 'members_following' );
                    $following = false;
                    $include = false;
                    $optionsForm = true;

                    foreach ( $data as $follower ) {
                        if ( $follower->memberId == self::member()->memberId() && $follower->topicId == $options['topicId'] && $follower->followingTopic == 1 && $follower->followingForum == 1 ) {
                            $following = true;

                            if ( $follower->displayInList == 1 ) {
                                $include = true;
                            }
                        }
                    }

                    if ( $following ) {
                        $followingArr['followChecked'] = ' checked';
                        $followingArr['display'] = '';

                        if ( $include ) {
                            $followingArr['followIncludeChecked'] = ' checked';
                        } else {
                            $followingArr['followIncludeChecked'] = '';
                        }
                    } else {
                        $followingArr['followChecked'] = '';
                        $followingArr['display'] = self::output()->getPartial( 'Global', 'Style', 'DisplayNone' );
                        $followingArr['followIncludeChecked'] = '';
                    }
                } else {
                    $followingArr['followChecked'] = '';
                    $followingArr['display'] = self::output()->getPartial( 'Global', 'Style', 'DisplayNone' );
                    $followingArr['followIncludeChecked'] = '';
                }

                $follow = $spacer . self::member()->notificationsEnabled() ? self::output()->getPartial( 'Global', 'Checkbox', 'Follow', $followingArr ) : '';
            }
        }

        if ( $optionsForm ) {
            $formOptions = self::output()->getPartial(
                'Editor',
                'QuickReply',
                'Options',
                [
                    'spacer'           => $spacer,
                    'follow'           => $follow,
                    'includeSignature' => $signature
                ]
            );
        } else {
            $formOptions = '';
        }

        return self::output()->getPartial(
            'Editor',
            'QuickReply',
            'Base',
            [
                'editor'    => self::getEditor(),
                'uploader'  => self::getUploader(),
                'hidden'    => $hiddenFields,
                'message'   => ( isset( $options['message'] ) && \strlen( $options['message'] ) > 0 ) ? $options['message'] : self::localization()->getWords( 'editor', 'quickReplyLinkText' ),
                'options'   => $formOptions,
                'marginTop' => ( isset( $options['marginTop'] ) && $options['marginTop'] ) ? self::output()->getPartial( 'Global', 'Margin', 'Top' ) : '',
                'csrfToken' => self::security()->getCSRFTokenFormField()
            ]
        );
    }
} 