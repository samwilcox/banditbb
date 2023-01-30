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

namespace BanditBB\Core;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Globals extends \BanditBB\Application {

    protected static $instance;
    protected static $vars = [];

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getGlobals() {
        self::$vars['baseUrl'] = self::vars()->baseUrl;
        self::$vars['themeUrl'] = self::member()->themeUrl();
        self::$vars['imagesetUrl'] = self::member()->imagesetUrl();
        self::$vars['forumsUrl'] = self::seo()->seoUrl( 'forums' );
        self::$vars['wrapper'] = self::vars()->wrapper;
        self::$vars['communityTitle'] = self::settings()->communityTitle;
        self::$vars['communityLogo'] = self::settings()->communityLogo;
        self::$vars['homeUrl'] = self::settings()->homeUrl;
        self::$vars['seoEnabled'] = self::settings()->seoEnabled ? 'true' : 'false';
        self::$vars['csrfTokenAjax'] = self::security()->getCSRFTokenFieldForAjax();

        switch ( self::settings()->communityLogoType ) {
            case 'text':
                self::$vars['logo'] = self::output()->getPartial(
                    'Global',
                    'Logo',
                    'Text',
                    [
                        'url'   => self::seo()->seoUrl( 'forums' ),
                        'title' => self::settings()->communityTitle
                    ]
                );
                break;

            case 'image':
                self::$vars['logo'] = self::output()->getPartial(
                    'Global',
                    'Logo',
                    'Image',
                    [
                        'url'         => self::seo()->seoUrl( 'forums'),
                        'title'       => self::settings()->communityTitle,
                        'logo'        => self::settings()->communityLogo,
                        'imagesetUrl' => self::member()->imagesetUrl()
                    ]
                );
                break;
        }

        if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::MEMBERS ) ) {
            self::$vars['membersMenuLink'] = self::output()->getPartial(
                'Global',
                'Link',
                'Members',
                [
                    'url' => self::seo()->seoUrl( 'members' )
                ]
            );
        } else {
            self::$vars['membersMenuLink'] = '';
        }

        if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::CALENDAR ) ) {
            self::$vars['calendarMenuLink'] = self::output()->getPartial(
                'Global',
                'Link',
                'Calendar',
                [
                    'url' => self::seo()->seoUrl( 'calendar' )
                ]
            );
        } else {
            self::$vars['calendarMenuLink'] = '';
        }

        if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::SEARCH ) ) {
            self::$vars['searchMenuLink'] = self::output()->getPartial(
                'Global',
                'Link',
                'Search',
                [
                    'url' => self::seo()->seoUrl( 'search' )
                ]
            );
        } else {
            self::$vars['searchMenuLink'] = '';
        }

        if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::HELP ) ) {
            self::$vars['helpMenuLink'] = self::output()->getPartial(
                'Global',
                'Link',
                'Help',
                [
                    'url' => self::seo()->seoUrl( 'help' )
                ]
            );
        } else {
            self::$vars['helpMenuLink'] = '';
        }

        if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::EXPANDED_MENU ) ) {
            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::LATEST_CONTENT ) ) {
                $latestContent = self::output()->getPartial(
                    'Global',
                    'Link',
                    'LatestContent',
                    [
                        'url' => self::seo()->seoUrl( 'content' )
                    ]
                );
            } else {
                $latestContent = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::COMMUNITY_LEADERS ) ) {
                $communityLeaders = self::output()->getPartial(
                    'Global',
                    'Link',
                    'CommunityLeaders',
                    [
                        'url' => self::seo()->seoUrl( 'members', 'leaders' )
                    ]
                );
            } else {
                $communityLeaders = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::WHOS_ONLINE ) ) {
                $whosOnline = self::output()->getPartial(
                    'Global',
                    'Link',
                    'WhosOnline',
                    [
                        'url' => self::seo()->seoUrl( 'online' )
                    ]
                );
            } else {
                $whosOnline = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::MARK_ALL_READ ) ) {
                $markAllRead = self::output()->getPartial(
                    'Global',
                    'Link',
                    'MarkAllRead',
                    [
                        'url' => self::seo()->seoUrl( 'content', 'markall' )
                    ]
                );
            } else {
                $markAllRead = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::DELETE_COOKIES ) ) {
                $deleteCookies = self::output()->getPartial(
                    'Global',
                    'Link',
                    'DeleteCookies',
                    [
                        'url' => self::seo()->seoUrl( 'forums', 'deletecookies' )
                    ]
                );
            } else {
                $deleteCookies = '';
            }

            self::$vars['expandedMenuDropDownMenu'] = self::output()->getPartial(
                'Global',
                'ExpandedMenu',
                'DropDownMenu',
                [
                    'latestContent'    => $latestContent,
                    'communityLeaders' => $communityLeaders,
                    'whosOnline'       => $whosOnline,
                    'markAllRead'      => $markAllRead,
                    'deleteCookies'    => $deleteCookies
                ]
            );

            self::$vars['expandedMenuLink'] = self::output()->getPartial( 'Global', 'ExpandedMenu', 'Link' );
        } else {
            self::$vars['expandedMenuLink'] = '';
            self::$vars['expandedMenuDropDownMenu'] = '';
        }

        self::$vars['breadcrumbs'] = '';

        if ( isset( self::vars()->breadcrumbs ) ) {
            if ( \count( self::vars()->breadcrumbs ) > 0 ) {
                foreach ( self::vars()->breadcrumbs as $crumb ) {
                    self::$vars['breadcrumbs'] .= self::output()->getPartial(
                        'Global',
                        'Breadcrumb',
                        'Item',
                        [
                            'title' => $crumb['title'],
                            'url'   => $crumb['url']
                        ]
                    );
                }
            }
        }

        if ( self::member()->signedIn() ) {
            self::$vars['signInDialog'] = '';

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::NOTIFICATIONS ) ) {
                if ( self::member()->notificationsEnabled() ) {
                    $notificationData = self::member()->notificationsData();
                    $notifications = self::member()->notificationsListForDropDown();

                    if ( $notifications == null ) $notifications = self::output()->getPartial( 'Global', 'Notifications', 'None' );

                    $notificationsLink = self::output()->getPartial(
                        'Global',
                        'Notifications',
                        'Icon',
                        [
                            'badge' => $notificationData->unread ? self::output()->getPartial( 'Global', 'Unread', 'Badge', [ 'total' => $notificationData->totalUnread ] ) : ''
                        ]
                    );
                } else {
                    $notifications = self::output()->getPartial( 'Global', 'Notifications', 'Disabled' );

                    $notificationsLink = self::output()->getPartial(
                        'Global',
                        'Notifications',
                        'Icon',
                        [
                            'badge' => ''
                        ]
                    );
                }

                self::$vars['notificationsMenu'] = self::output()->getPartial(
                    'Global',
                    'DropDownMenu',
                    'Notifications',
                    [
                        'content'  => $notifications,
                        'allUrl'   => self::seo()->seoUrl( 'notifications', null, [], true ),
                        'clearUrl' => self::seo()->seoUrl( 'notifications', 'clearall', [], true )
                    ]
                );
            } else {
                self::$vars['notificationsMenu'] = '';
                $notificationsLink = '';
            }

            if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::MESSAGES ) ) {
                if ( self::member()->messagesEnabled() ) {
                    $messageData = self::member()->messagesData();
                    $messages = self::member()->messagesListForDropDown();

                    if ( $messages == null ) $messages = self::output()->getPartial( 'Global', 'Messages', 'None' );

                    $messagesLink = self::output()->getPartial(
                        'Global',
                        'Messages',
                        'Icon',
                        [
                            'badge' => $messagesData->unread ? self::output()->getPartial( 'Global', 'Unread', 'Badge', [ 'total' => $messagesData->totalUnread ] ) : ''
                        ]
                    );
                } else {
                    $messages = self::output()->getPartial( 'Global', 'Messages', 'Disabled' );

                    $messagesLink = self::output()->getPartial(
                        'Global',
                        'Messages',
                        'Icon',
                        [
                            'badge' => ''
                        ]
                    );
                }

                self::$vars['messagesMenu'] = self::output()->getPartial(
                    'Global',
                    'DropDownMenu',
                    'Messages',
                    [
                        'content'  => $messages,
                        'inboxUrl' => self::seo()->seoUrl( 'messages', null, [], true ),
                        'newUrl'   => self::seo()->seoUrl( 'messages', 'newmessage', [], true )
                    ]
                );
            } else {
                self::$vars['messagesMenu'] = '';
                $messagesLink = '';
            }

            $elevatedOptions = false;

            if ( self::member()->isModerator() ) {
                $moderatorLink = self::output()->getPartial(
                    'Global',
                    'Link',
                    'GenericNoTip',
                    [
                        'seperator' => '',
                        'name'      => self::localization()->getWords( 'global', 'mmModeratorCenterLink' ),
                        'url'       => self::seo()->seoUrl( 'moderatorcenter', null, [], true )
                    ]
                );

                $elevatedOptions = true;
            } else {
                $moderatorLink = '';
            }

            if ( self::member()->adminCPAccess() ) {
                $adminCPLink = self::output()->getPartial(
                    'Global',
                    'Link',
                    'GenericNoTip',
                    [
                        'seperator' => '',
                        'name'      => self::localization()->getWords( 'global', 'mmAdminCPLink' ),
                        'url'       => self::seo()->seoUrl( 'admincp' )
                    ]
                );

                $elevatedOptions = true;
            } else {
                $adminCPLink = '';
            }

            if ( $elevatedOptions ) {
                $eOptions = self::output()->getPartial(
                    'Global',
                    'Member',
                    'ElevatedOptions',
                    [
                        'moderatorCenter' => $moderatorLink,
                        'adminCP'         => $adminCPLink
                    ]
                );
            } else {
                $eOptions = '';
            }

            self::$vars['userBar'] = self::output()->getPartial(
                'Global',
                'UserBar',
                'Member',
                [
                    'notificationsLink' => $notificationsLink,
                    'messagesLink'      => $messagesLink,
                    'displayName'       => self::member()->displayName(),
                    'photo'             => self::member()->profilePhoto( self::member()->memberId(), true, true, null, false, null, false )
                ]
            );

            self::$vars['memberMenu'] = self::output()->getPartial(
                'Global',
                'DropDownMenu',
                'MemberMenu',
                [
                    'elevatedOptions'   => $eOptions,
                    'notificationsUrl'  => self::seo()->seoUrl( 'notifications', null, [], true ),
                    'messagesUrl'       => self::seo()->seoUrl( 'messages', null, [], true ),
                    'friendsUrl'        => self::seo()->seoUrl( 'friends', null, [], true ),
                    'settingsUrl'       => self::seo()->seoUrl( 'settings', null, [], true ),
                    'profileUrl'        => self::seo()->seoUrl( 'profiles', 'view', [ 'id' => self::url()->getUrlWithIdAndTitle( self::member()->memberId(), self::member()->displayName() ) ] ),
                    'contentUrl'        => self::seo()->seoUrl( 'content' ),
                    'signOutUrl'        => self::seo()->seoUrl( 'authentication', 'signout', [], true )
                ]
            );
        } else {
            self::$vars['memberMenu'] = '';
            self::$vars['notificationsMenu'] = '';
            self::$vars['messagesMenu'] = '';
            self::$vars['signInDialog'] = self::authentication()->getAuthenticationForm( 'dialog' );

            if ( self::settings()->accountCreationEnabled ) {
                $createAccount = self::output()->getPartial(
                    'Global',
                    'Link',
                    'CreateAccountAlt',
                    [
                        'url' => self::seo()->seoUrl( 'createaccount' )
                    ]
                );
            } else {
                $createAccount = '';
            }

            self::$vars['userBar'] = self::output()->getPartial(
                'Global',
                'UserBar',
                'Guest',
                [
                    'createAccount' => $createAccount,
                    'signInUrl'     => self::seo()->seoUrl( 'authentication' )
                ]
            );
        }

        $data = self::cache()->massGetData( [ 'localizations' => 'installed_localizations', 'themes' => 'installed_themes' ] );
        self::$vars['localizationItems'] = '';
        self::$vars['themeItems'] = '';

        foreach ( $data->localizations as $local ) {
            if ( $local->localizationsId == self::member()->localizationId() ) {
                self::$vars['selectedLocalization'] = self::output()->getPartial(
                    'Global',
                    'Selector',
                    'Localization',
                    [
                        'title' => $local->title
                    ]
                );
            }

            self::$vars['localizationItems'] .= self::output()->getPartial(
                'Global',
                'Selector',
                'LocalizationItem',
                [
                    'url'   => self::seo()->seoUrl( 'selector', 'localization', [ 'id' => $local->localizationsId ] ),
                    'title' => $local->title
                ]
            );
        }

        foreach ( $data->themes as $theme ) {
            if ( $theme->themeId == self::member()->themeId() ) {
                self::$vars['selectedTheme'] = self::output()->getPartial(
                    'Global',
                    'Selector',
                    'Theme',
                    [
                        'title' => $theme->title
                    ]
                );
            }

            self::$vars['themeItems'] .= self::output()->getPartial(
                'Global',
                'Selector',
                'ThemeItem',
                [
                    'url'   => self::seo()->seoUrl( 'selector', 'theme', [ 'id' => $theme->themeId ] ),
                    'title' => $theme->title
                ]
            );
        }

        if ( self::settings()->privacyPolicyEnabled && \strlen( self::settings()->privacyPolicyUrl ) > 0 ) {
            self::$vars['privacyPolicy'] = self::output()->getPartial(
                'Global',
                'Link',
                'GenericNoTip',
                [
                    'url'       => self::settings()->privacyPolicyUrl,
                    'name'      => self::localization()->getWords( 'global', 'privacyPolicyLink' ),
                    'seperator' => ''
                ]
            );
        } else {
            self::$vars['privacyPolicy'] = '';
        }

        if ( self::settings()->contactUsEnabled && \strlen( self::settings()->contactUsUrl ) > 0 ) {
            self::$vars['contactUs'] = self::output()->getPartial(
                'Global',
                'Link',
                'GenericNoTip',
                [
                    'url'       => self::settings()->contactUsUrl,
                    'name'      => self::localization()->getWords( 'global', 'contactUsLink' ),
                    'seperator' => ''
                ]
            );
        } else {
            self::$vars['contactUs'] = '';
        }

        $timeZone = new \DateTimeZone( self::member()->timeZone() );
        $gmt = new \DateTime( 'now', $timeZone );

        self::$vars['allTimes'] = self::localization()->quickMultiWordReplace( 'global', 'allTimes', [
            'TimeZone' => self::member()->timeZone(),
            'GMT'      => \sprintf( 'GMT %s', $gmt->format( 'P' ) )
        ]);

        // ----- START OF POWERED BY SECTION ----- //

        // WARNING:
        // Please DO NOT remove this powered by section - keeping this allows the software to remain free
        // If you wish to remove the powered by section, please inquire about removing it for a fee
        // at sam@banditbb.com
        self::$vars['poweredBy'] = self::localization()->quickMultiWordReplace( 'global', 'poweredBy', [
            'Link'     => self::output()->getPartial( 'Global', 'Link', 'PoweredBy' ),
            'Version'  => APP_VERSION,
            'Years'    => self::utilities()->getCopyrightYears()
        ]);

        // ----- END OF POWERED BY SECTION ----- //

        if ( self::member()->hasFeaturePermissions( \BanditBB\Types\Features::DEBUG_INFORMATION ) ) {
            $executionTimer = ( \microtime( true ) - self::vars()->executionTimerStart );
            $percentages = self::math()->calculateDebugPercentages( $executionTimer, self::db()->executionTime() );

            self::$vars['debugInformation'] = self::localization()->quickMultiWordReplace( 'global', 'debugInfo', [
                'Seconds'   => \round( $executionTimer, 2 ),
                'PHP'       => $percentages->page,
                'SQL'       => $percentages->database,
                'Queries'   => \number_format( self::db()->totalQueries() ),
                'GZIP'      => self::vars()->gzip ? self::localization()->getWords( 'global', 'enabled' ) : self::localization()->getWords( 'global', 'disabled' ),
                'ClockIcon' => self::output()->getPartial( 'Global', 'Icon', 'Clock' ),
                'SQLIcon'   => self::output()->getPartial( 'Global', 'Icon', 'SQL' ),
                'GZIPIcon'  => self::output()->getPartial( 'Global', 'Icon', 'GZIP' )
            ]);
        } else {
            self::$vars['debugInformation'] = '';
        }

        self::$vars['subForumsMenus'] = self::vars()->subForumsMenus;
        self::$vars['editorResources'] = '';

        if ( isset( self::vars()->editorActive ) ) {
            if ( self::vars()->editorActive ) {
                self::$vars['editorResources'] = self::output()->getPartial( 'Editor', 'Editor', 'Resources', [ 'wrapper' => self::vars()->wrapper ] );
            }
        }

        return self::$vars;
    }
}