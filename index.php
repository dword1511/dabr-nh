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

menu_register(array (
	'about' => array (
		'callback' => '关于',
	),
	'logout' => array (
		'security' => true,
		'callback' => '登出',
	),
));

function logout_page() {
	user_logout();
	$content = theme('logged_out');
	theme('page', 'Logged out', $content);
}

function about_page() {
	$content = file_get_contents('about.html');
	theme('page', 'About', $content);
}

browser_detect();
menu_execute_active_handler();
?>
