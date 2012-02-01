<?php
require_once ("common/menu.php");

function mobile_theme_menu_bottom() {
	return '';
}

function mobile_theme_menu_top() {
	return theme_menu_both('bottom');
}
?>
