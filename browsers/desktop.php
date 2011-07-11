<?php
function desktop_theme_status_form($text = '', $in_reply_to_id = NULL) {
	if (user_is_authenticated()) {
		$output = '<form method="post" action="update">
  <fieldset><legend><img src="'.BASE_URL.'twimg/bird_16_blue.png" width="16" height="16" /> What\'s Happening?</legend>
  <textarea id="status" name="status" rows="3" style="width:95%;max-width:400px;">'.$text.'</textarea>
  <div><input name="in_reply_to_id" value="'.$in_reply_to_id.'" type="hidden" /><input type="submit" value="Tweet" /> <span id="remaining">140</span> 
  <span id="geo" style="display: none;"><input onclick="goGeo()" type="checkbox" id="geoloc" name="location" /> <label for="geoloc" id="lblGeo"></label></span></div>
  </fieldset>
  <script type="text/javascript">
started = false;
chkbox = document.getElementById("geoloc");
if (navigator.geolocation) {
	geoStatus("Tweet my location");
	if ("'.$_COOKIE['geo'].'"=="Y") {
		chkbox.checked = true;
		goGeo();
	}
}
function goGeo(node) {
	if (started) return;
	started = true;
	geoStatus("Locating...");
	navigator.geolocation.getCurrentPosition(geoSuccess, geoError);
}
function geoStatus(msg) {
	document.getElementById("geo").style.display = "inline";
	document.getElementById("lblGeo").innerHTML = msg;
}
function geoError(error) {
	geoStatus(error);
	switch(error.code) {
		case error.TIMEOUT:
			document.getElementById("lblGeo").innerHTML += ": Location timed out. Please check your network.";
		break;
		case error.PERMISSION_DENIED:
			document.getElementById("lblGeo").innerHTML += ": Permission denied. Please check your browser\'s ettings.";
		break;
		case error.POSITION_UNAVAILABLE:
			document.getElementById("lblGeo").innerHTML += ": We are sorry. But you are on Mars.";
		break;
	};
}
function geoSuccess(position) {
	geoStatus("Tweet my <a href=\'http://maps.google.com.hk/m?q=" + position.coords.latitude + "," + position.coords.longitude + "\' target=\'blank\'>location</a>");
	chkbox.value = position.coords.latitude + "," + position.coords.longitude;
}
  </script>
</form>';
		$output .= js_counter('status');
		return $output;
	}
}

function desktop_theme_search_form($query) {
	$query = stripslashes(htmlentities($query,ENT_QUOTES,"UTF-8"));
	return "<form action='search' method='get'><input name='query' value=\"$query\" style='width:100%; max-width: 300px' /><input type='submit' value='Search' /></form>";
}

function desktop_theme_avatar($url, $force_large = false) {
	return "<img src='".BASE_URL."simpleproxy.php?url=".$url."' height='48' width='48' />";
}

function desktop_theme_css() {
	$out = theme_css();
	$out .= "<style type='text/css'>.avatar{display:block; height:50px; width:50px; left:5px; margin:0; overflow:hidden; position:absolute;}
.shift{margin-left:58px;min-height:72px;max-width:700px;width:95%}</style>";
	return $out;
}

?>
