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

class AjaxController extends \BanditBB\Controllers\BaseController {

    protected static $model;

    public function __construct() {
        self::$model = new \BanditBB\Models\AjaxModel();
    }

    public function hovercard() {
        self::$model->getHovercard();
    }

    public function validateCredentials() {
        self::$model->validateCredentials();
    }

    public function captcha() {
        self::$model->getCaptchaImage();
    }

    public function validateForumPassword() {
        self::$model->validateForumPassword();
    }

    public function uploadFile() {
        self::$model->uploadFile();
    }

    public function removeFile() {
        self::$model->removeFile();
    }
}