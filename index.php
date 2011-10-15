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

menu_register(array (
	'logout' => array (
		'security' => true,
		'callback' => 'logout_page',
		'display' => '登出',
	),
));

function logout_page() {
	user_logout();
	$content = theme('logged_out');
	theme('page', '已登出', $content);
}

browser_detect();
menu_execute_active_handler();
?>
