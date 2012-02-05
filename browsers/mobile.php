<?php
require_once ("common/menu.php");

function mobile_theme_menu_bottom() {
	return '';
}

function mobile_theme_menu_top() {
	$links = array();
	foreach (menu_visible_items() as $url => $page) {
		if (!$url) $url = BASE_URL;
		if (isset($page['accesskey'])) $links[] = "<a href='$url' accesskey='{$page['accesskey']}'>".$page['display']."</a> {$page['accesskey']}";
		else $links[] = "<a href='$url'>".$page['display']."</a>";
	}
	if (user_is_authenticated()) {
		$user = user_current_username();
		array_unshift($links, "<b><a href='user/$user'>$user</a></b>");
	}
	return "<div class='menu menu-$menu'>".implode(' | ', $links).'</div>';
}
?>
