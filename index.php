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

// error_reporting( E_ALL );
// ini_set( 'display_errors', true );
error_reporting( 0 );

define( 'ROOT_PATH', dirname( __FILE__ ) . '/' );

require_once ( ROOT_PATH . 'application/BandBB.php' );
\BanditBB\Application::run();