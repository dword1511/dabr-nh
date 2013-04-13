<?php

// Usage: $out .= geoloc($_COOKIE['geo']);
function geoloc($checked, $raden = 0) {
	$msga = '包含';
	$msgb = '地理位置信息';
	$msgc = '';
	if($raden) {
		$msga = '搜索';
		$msgb = '附近';
		$msgc = '的内容';
	}
	$content = '
<span id="geo" style="display: none;">
 <input onclick="goGeo()" type="checkbox" id="geoloc" name="location"/>
 <label for="geoloc" id="lblGeo"/></span>';
	if($raden) $content .= '
<select name="radius">
 <option value="1km"> 1 公里</option>
 <option value="5km"> 5 公里</option>
 <option value="10km">10 公里</option>
 <option value="50km">50 公里</option>
</select>';
	$content .= '
<script type="text/javascript">
started = false;
chkbox = document.getElementById("geoloc");
if (navigator.geolocation) {
 geoStatus("'.$msga.$msgb.$msgc.'");';
	// Do not automatically enable geoloc in searches.
	if($checked == "Y" && $raden == 0) $content .= '
 chkbox.checked = true;
 goGeo();';
	$content .= '}
function goGeo(node) {
 if(started) return;
 started = true;
 geoStatus("正在定位…");
 navigator.geolocation.getCurrentPosition(geoSuccess, geoError, {enableHighAccuracy:true, maximumAge:600000, timeout:10000});
}

function geoStatus(msg) {
 document.getElementById("geo").style.display = "inline";
 document.getElementById("lblGeo").innerHTML = msg;
}

function geoError(error) {
 switch(error.code) {
  case error.TIMEOUT:
   geoStatus(error + "：定位服务超时鸟。问问方校长是肿么回事。");
   break;
  case error.PERMISSION_DENIED:
   geoStatus(error + "：定位请求被拒鸟。请瞅瞅您的浏览器设置。");
   break;
  case error.POSITION_UNAVAILABLE:
   geoStatus(error + "：抱歉，貌似我们找不到你在哪。");
   break;
  default:
   geoStatus(error + "未知错误。");
 };
}

function geoSuccess(position) {
 if(typeof position.address !== "undefined")
  geoStatus("'.$msga.'<a href=\'https://maps.google.com/maps?q=" + position.coords.latitude + "," + position.coords.longitude + "\' target=\''.get_target().'\' title=\'您的位置：" + position.address.country + position.address.region + "省" + position.address.city + "市，精确到" + position.coords.accuracy + "米\'>'.$msgb.'</a>'.$msgc.'");
 else
  geoStatus("'.$msga.'<a href=\'https://maps.google.com/maps?q=" + position.coords.latitude + "," + position.coords.longitude + "\' target=\''.get_target().'\' title=\'您的位置精确到" + position.coords.accuracy + "米\'>'.$msgb.'</a>'.$msgc.'");
 chkbox.value = position.coords.latitude + "," + position.coords.longitude;
}
</script>';
	return $content;
}
