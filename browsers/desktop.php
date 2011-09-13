<?php
function desktop_theme_status_form($text = '', $in_reply_to_id = NULL) {
	if (user_is_authenticated()) {
		$output = '<form method="post" action="update">
  <fieldset><legend><img src="'.BASE_URL.'images/bird_16_blue.png" width="16" height="16" /> 发生了神马？</legend>
  <textarea id="status" name="status" rows="3" style="width:95%;max-width:400px;">'.$text.'</textarea>
  <div><input name="in_reply_to_id" value="'.$in_reply_to_id.'" type="hidden" /><input type="submit" value="推！" /> <span id="remaining">140</span> 
  <span id="geo" style="display: none;"><input onclick="goGeo()" type="checkbox" id="geoloc" name="location" /> <label for="geoloc" id="lblGeo"></label></span></div>
  </fieldset>
  <script type="text/javascript">
started = false;
chkbox = document.getElementById("geoloc");
if (navigator.geolocation) {
	geoStatus("包含地理位置信息");
	if ("'.$_COOKIE['geo'].'"=="Y") {
		chkbox.checked = true;
		goGeo();
	}
}
function goGeo(node) {
	if (started) return;
	started = true;
	geoStatus("正在定位……");
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
			document.getElementById("lblGeo").innerHTML += "：定位服务超时鸟。问问方校长是肿么回事。";
		break;
		case error.PERMISSION_DENIED:
			document.getElementById("lblGeo").innerHTML += "：定位请求被拒绝鸟。请看看您的浏览器设置。";
		break;
		case error.POSITION_UNAVAILABLE:
			document.getElementById("lblGeo").innerHTML += "：抱歉，也许您已经被开除球籍。";
		break;
	};
}
function geoSuccess(position) {
	geoStatus("包含<a href=\'http://maps.google.com.hk/m?q=" + position.coords.latitude + "," + position.coords.longitude + "\' target=\'blank\'>地理位置信息</a>");
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
	return "<img src='".$url."' height='48' width='48' />";
}

function desktop_theme_css() {
	$out = theme_css();
	$out .= "<style type='text/css'>.avatar{display:block; height:50px; width:50px; left:5px; margin:0; overflow:hidden; position:absolute;}
.shift{margin-left:58px;min-height:72px;max-width:700px;width:95%}</style>";
	return $out;
}

?>
