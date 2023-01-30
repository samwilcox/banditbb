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

namespace BanditBB\Controllers;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class ForumsController extends \BanditBB\Controllers\BaseController {

    protected static $model;

    public function __construct() {
        self::$model = new \BanditBB\Models\ForumsModel();
    }

    public function index() {
        self::set( self::$model->generateIndex() );
        self::output( 'Forums', 'Index' );
    }

    public function view() {
        self::set( self::$model->viewForum() );
        self::output( 'Forums', 'View' ); 
    }

    public function verifyPassword() {
        self::$model->verifyForumPassword();
    }
}