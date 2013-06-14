<?php
$dabr_start = microtime(1);

if($_SERVER['HTTPS']!="on") {
    $redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("Location:$redirect");
}

header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . date('r'));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

require 'config.php';
require 'common/browser.php';
require 'common/menu.php';
require 'common/user.php';
require 'common/theme.php';
require 'common/twitter.php';
require 'common/lists.php';
require 'common/settings.php';
require 'common/about.php';

// Twitter's API URL.
define('API_NEW','https://api.twitter.com/1.1/');
define('API_OLD','https://api.twitter.com/1/');

menu_register(array (
	'logout' => array (
		'security' => true,
		'callback' => 'logout_page',
		//'display' => '登出',
		'hidden' => true,
	),
));

function logout_page() {
	user_logout();
	header("Location: " . BASE_URL); /* Redirect browser */
	exit;
}

browser_detect();
menu_execute_active_handler();
