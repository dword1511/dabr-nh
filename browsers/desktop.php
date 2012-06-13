<?php
function desktop_theme_status_form($text = '', $in_reply_to_id = NULL) {
	if (user_is_authenticated()) {
		if ($_GET['status']) $text = $_GET['status'];
		$output = '<script type="text/javascript">document.onkeydown = function (){if(event.ctrlKey && window.event.keyCode == 13) document.user_tweet.submit();}</script>
<form method="post" action="update" name="user_tweet"><fieldset>
<legend><img src="'.BASE_URL.'images/twitter-bird-16x16.png" width="16" height="16"/> 发生了神马？</legend>
<textarea id="status" name="status" rows="4" style="width:95%;max-width:400px;">'.$text.'</textarea>
<div><input name="in_reply_to_id" value="'.$in_reply_to_id.'" type="hidden"/>
<input type="submit" value="推！"/>
<span id="remaining">140</span>';
		$output .= geoloc($_COOKIE['geo']);
		$output .= ' <a href="picture">发图片</a></div></fieldset></form>';
		$output .= js_counter('status');
		return $output;
	}
}

function desktop_theme_search_form($query) {
	$query = stripslashes(htmlentities($query,ENT_QUOTES,"UTF-8"));
	return '<form action="search" method="get"><input name="query" value="'.$query.'" style="width:60%; max-width: 300px"/><input type="submit" value="给我搜"/>'.geoloc($_COOKIE['geo'],1).'<p><strong><a href="trends">趋势 →</a></strong></p>';
}

function desktop_theme_avatar($url, $force_large = false) {
	return "<img src='".$url."' height='48' width='48' />";
}

function desktop_theme_css() {
	$out = theme_css();
	$out .= "<style type='text/css'>.avatar{display:block; height:50px; width:50px; left:5px; margin:0; overflow:hidden; position:absolute;}
.shift{margin-left:58px;min-height:72px;max-width:700px;}</style>";
	return $out;
}

function desktop_theme_page($title, $content) {
	$body = theme('menu_top');
	$body .= $content;
	$body .= theme('menu_bottom');
	if (DEBUG_MODE == 'ON') {
		global $dabr_start, $api_time, $services_time, $rate_limit;
		$time = microtime(1) - $dabr_start;
		$body .= '<p>总计磨蹭了 '.round($time, 4).' 秒。（ Dabr ：'.round(($time - $api_time - $services_time) / $time * 100).'% ，Twitter ：'.round($api_time / $time * 100).'% ，其他服务：'.round($services_time / $time * 100).'% ）<br/>'.$rate_limit.'</p>';
	}
	if ($title == 'Login') {
		$title = 'Dabr - 登录到 Twitter';
		$meta = '<meta name="description" content="免费而且不太河蟹的移动版 Twitter 替代品，为挪鸡鸭量身打造。" />';
	}
	ob_start('ob_gzhandler');
	header('Content-Type: text/html; charset=utf-8');
	echo '<html><head>
<title>Dabr - ',$title,'</title><base href="',BASE_URL,'" />'.$meta.theme('css').'</head><body id="thepage"><a name="top">';
	echo $body;
	// If the cookies haven't been set, remind the user that they can set how Dabr looks
	if (setting_fetch('colours') == null) echo '<p>觉得 Dabr 很难看？（其实就是嘛！） <a href="settings">更改配色方案吧！</a>（有毛线用。。。）</p>';
	global $GA_ACCOUNT;
	if ($GA_ACCOUNT) echo '<img src="' . googleAnalyticsGetImageUrl() . '"/>';
	echo '</body></html>';
	exit();
}

?>
