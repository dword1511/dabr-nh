<?php

function text_theme_avatar($url, $force_large = false) {
	return '';
}

function text_theme_action_icon($url, $image_url, $text) {
	if($text == 'MAP' || $text == 'LINK') return "<a href='$url' target='" . get_target() . "'>$text</a>";
	return "<a href='$url'>$text</a>";
}
