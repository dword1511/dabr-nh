<?php
$geocode = '
<span id="geo" style="display: none;"><input onclick="goGeo()" type="checkbox" id="geoloc" name="location"/><label for="geoloc" id="lblGeo"/></span>
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
 if(started) return;
 started = true;
 geoStatus("正在定位…");
 navigator.geolocation.getCurrentPosition(geoSuccess, geoError);
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
 geoStatus("包含<a href=\'http://maps.google.com.hk/m?q=" + position.coords.latitude + "," + position.coords.longitude + "\' target=\'blank\'>地理位置信息</a>");
 chkbox.value = position.coords.latitude + "," + position.coords.longitude;
}</script>'
?>

