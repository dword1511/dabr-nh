<?php
function desktop_theme_status_form($text = '', $in_reply_to_id = NULL) {
	if (user_is_authenticated()) {
		$output = '<form method="post" action="update">
  <fieldset><legend><img src="'.BASE_URL.'images/bird_16_blue.png" width="16" height="16" /> 发生了神马？</legend>
  <textarea id="status" name="status" rows="3" style="width:95%;max-width:400px;">'.$text.'</textarea>
  <div><input name="in_reply_to_id" value="'.$in_reply_to_id.'" type="hidden" /><input type="submit" value="推！" /> <span id="remaining">140</span>';
		$output .= geoloc($_COOKIE['geo']);
		$output .= '</div></fieldset></form>';
		$output .= js_counter('status');
		return $output;
	}
}

function desktop_theme_search_form($query) {
	$query = stripslashes(htmlentities($query,ENT_QUOTES,"UTF-8"));
	return "<form action='search' method='get'><input name='query' value=\"$query\" style='width:100%; max-width: 300px' /><input type='submit' value='给我搜！' /></form>";
}

function desktop_theme_avatar($url, $force_large = false) {
	return "<img src='".$url."' height='48' width='48' />";
}

function desktop_theme_css() {
	$out = theme_css();
	$out .= "<style type='text/css'>.avatar{display:block; height:50px; width:50px; left:5px; margin:0; overflow:hidden; position:absolute;}
.shift{margin-left:58px;min-height:72px;max-width:700px;width:95%}</style>";
	return $out;
}

?>
