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
		'callback' => 'about_page',
		'display' => '关于',
	),
	'logout' => array (
		'security' => true,
		'callback' => 'logout_page',
		'display' => '登出',
	),
));

function logout_page() {
	user_logout();
	$content = theme('logged_out');
	theme('page', 'Logged out', $content);
}

function about_page() {
	$content = '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>Dabr - 关于</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>';
	$content .= file_get_contents('about.html');
	$content .= "</body></html>";
	theme('page', 'About', $content);
}

browser_detect();
menu_execute_active_handler();
?>
