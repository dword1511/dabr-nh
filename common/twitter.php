<?php

require 'Autolink.php';
require 'Extractor.php';
require 'Embedly.php';
require 'Emoticons.php';
require 'geoloc.php';

menu_register(array(
	'' => array(
		'callback' => 'twitter_home_page',
		'accesskey' => '0',
		'display' => '主页',
	),
	'status' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_status_page',
	),
	'update' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_update',
	),
	'twitter-retweet' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_retweet',
	),
	'replies' => array(
		'security' => true,
		'callback' => 'twitter_replies_page',
		'accesskey' => '1',
		'display' => '提及',
	),
	'favourite' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_mark_favourite_page',
	),
	'unfavourite' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_mark_favourite_page',
	),
	'directs' => array(
		'security' => true,
		'callback' => 'twitter_directs_page',
		'accesskey' => '2',
		'display' => '私信',
	),
	'search' => array(
		'security' => true,
		'callback' => 'twitter_search_page',
		'accesskey' => '3',
		'display' => '搜索',
	),
	'user' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_user_page',
	),
	'follow' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_follow_page',
	),
	'unfollow' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_follow_page',
	),
	'confirm' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_confirmation_page',
	),
	'confirmed' => array(
                'hidden' => true,
                'security' => true,
                'callback' => 'twitter_confirmed_page',
        ),
	'block' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_block_page',
	),
	'unblock' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_block_page',
	),
	'spam' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_spam_page',
	),
	'favourites' => array(
		'hidden' => true,
		'security' => true,
		'callback' =>  'twitter_favourites_page',
//		'display' => '收藏',
	),
	'followers' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_followers_page',
//		'display' => '粉丝',
	),
	'friends' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_friends_page',
//		'display' => '偶像',
	),
	'delete' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_delete_page',
	),
	'deleteDM' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_deleteDM_page',
	),
	'retweet' => array(
		'hidden' => true,
		'security' => true,
		'callback' => 'twitter_retweet_page',
	),
	'hash' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_hashtag_page',
	),
	'picture' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_media_page',
//		'display' => '上图',
	),
	'trends' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_trends_page',
//		'display' => '趋势',
	),
	'retweets' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_retweets_page',
//		'display' => '转发',
	),
	'retweeted_by' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_retweeters_page',
	),
	'editbio' => array(
		'security' => true,
		'hidden' => true,
		'callback' => 'twitter_profile_page',
//		'display' => '自传',
	)
));

function get_target() {
	if (stristr($_SERVER['HTTP_USER_AGENT'], "Kindle/")) return "_self";
	else return "_blank";
}

function twitter_profile_page() {
	if ($_POST['name']){
		$post_data = array(
			"name"	=> stripslashes($_POST['name']),
			"url"	=> stripslashes($_POST['url']),
			"location"	=> stripslashes($_POST['location']),
			"description"	=> stripslashes($_POST['description']),
		);
		$url = API_URL."account/update_profile.json";
		$user = twitter_process($url, $post_data);
		$content = "<h2>个人资料已更新。新的资料会在一分钟内生效。</h2>";
	}

	// http://api.twitter.com/1/account/update_profile_image.format
	if ($_FILES['image']['tmp_name']){
		require 'tmhOAuth.php';

		list($oauth_token, $oauth_token_secret) = explode('|', $GLOBALS['user']['password']);

		$tmhOAuth = new tmhOAuth(array(
			'consumer_key'    => OAUTH_CONSUMER_KEY,
			'consumer_secret' => OAUTH_CONSUMER_SECRET,
			'user_token'      => $oauth_token,
			'user_secret'     => $oauth_token_secret,
		));

		// note the type and filename are set here as well
		$params = array(
			'image' => "@{$_FILES['image']['tmp_name']};type={$_FILES['image']['type']};filename={$_FILES['image']['name']}",
		);

		$code = $tmhOAuth->request('POST',
			$tmhOAuth->url("1/account/update_profile_image"),
			$params,
			true, // use auth
			true // multipart
		);

		if ($code == 200)  $content .= "<h2>换头成功。</h2>";
		else {
			$content = "Damn! Something went wrong. Sorry :-("."<br/> code=".$code."<br/> status=".$status."<br/> image=".$image
		//."<br /> response=<pre>"
		//. print_r($tmhOAuth->response['response'], TRUE)
		. "</pre><br /> info=<pre>"
		. print_r($tmhOAuth->response['info'], TRUE)
		. "</pre><br /> code=<pre>"
		. print_r($tmhOAuth->response['code'], TRUE) . "</pre>";
		}
	}

	$user = twitter_user_info(user_current_username());
	$content .= theme('user_header', $user);
	$content .= theme('profile_form', $user);
	theme('page', "编辑个人资料", $content);
}

function theme_profile_form($user) {
	$out .= "<form name='profile' action='editbio' method='post' enctype='multipart/form-data'>
<hr/>名字：<input name='name' size=40 maxlength='20' value='". htmlspecialchars($user->name, ENT_QUOTES)."'/>
<br/>头像：<img src='".theme_get_avatar($user)."' /> <input type='file' name='image' />
<br/>简介：<input name='description' size=40 maxlength='160' value='". htmlspecialchars($user->description, ENT_QUOTES)."'/>
<br/>链接：<input name='url' maxlength='100' size=40 value='". htmlspecialchars($user->url, ENT_QUOTES)."'/>
<br/>地址：<input name='location' maxlength='30' size=40 value='". htmlspecialchars($user->location, ENT_QUOTES)."'/>
<br/>　　　<input type='submit' value='更新个人资料'/></form>";
	if(setting_fetch('browser') == 'desktop') $out .= "<script type='text/javascript'>document.onkeydown = function (){if(event.ctrlKey && window.event.keyCode == 13) document.profile.submit();}</script>";
	return $out;
}

function long_url($shortURL) {
	if (!defined('LONGURL_KEY')) return $shortURL;
	$url = "http://www.longurlplease.com/api/v1.1?q=" . $shortURL;
	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($curl_handle,CURLOPT_URL,$url);
	$url_json = curl_exec($curl_handle);
	curl_close($curl_handle);
	$url_array = json_decode($url_json,true);
	$url_long = $url_array["$shortURL"];
	if ($url_long == null) return $shortURL;
	return $url_long;
}

function friendship_exists($user_a) {
	$request = API_URL.'friendships/show.json?target_screen_name=' . $user_a;
	$following = twitter_process($request);
	if ($following->relationship->target->following == 1) return true;
	else return false;
}

function friendship($user_a) {
	$request = API_URL.'friendships/show.json?target_screen_name=' . $user_a;
	return twitter_process($request);
}

function twitter_block_exists($query) {
	$request = API_URL.'blocks/blocking/ids.json';
	$blocked = (array) twitter_process($request);
	return in_array($query,$blocked);
}

function twitter_trends_page($query) {
	$woeid = $_GET['woeid'];
	if($woeid == '') $woeid = '1';

	$request = API_URL.'trends/available.json';
	$local = twitter_process($request);
	$header = '<form method="get" action="trends"><select name="woeid">';
	$header .= '<option value="1"' . (($woeid == 1) ? ' selected="selected"' : '') . '>Worldwide</option>';

	foreach($local as $key => $row) {
		$c[$key] = $row->country;
		$t[$key] = $row->placeType->code;
		$n[$key] = $row->name;
	}
	array_multisort($c, SORT_ASC, $t, SORT_DESC, $n, SORT_ASC, $local);

	foreach($local as $l) {
		if($l->woeid != 1) {
			$n = $l->name;
			if($l->placeType->code != 12) $n = '-' . $n;
			$header .= '<option value="' . $l->woeid . '"' . (($l->woeid == $woeid) ? ' selected="selected"' : '') . '>' . $n . '</option>';
		}
	}
	$header .= '</select> <input type="submit" value="应用" /></form>';

	$request = API_URL.'trends/' . $woeid . '.json';
	$trends = twitter_process($request);
	$search_url = 'search?query=';
	foreach($trends[0]->trends as $trend) {
		$row = array('<strong><a href="' . str_replace('http://twitter.com/search/', $search_url, $trend->url) . '">' . $trend->name . '</a></strong>');
		$rows[] = array('data' => $row,  'class' => 'tweet');
	}
	$headers = array($header);
	$content = theme('table', $headers, $rows, array('class' => 'timeline'));
	theme('page', '趋势', $content);
}

function js_counter($name, $length='140') {
	if(setting_fetch('browser') == 'mobile') return '';
	$script = '<script type="text/javascript">
function updateCount() {
var remaining = ' . $length . ' - document.getElementById("' . $name . '").value.length;
document.getElementById("remaining").innerHTML = remaining;
if(remaining < 0) {
 var colour = "#FF0000";
 var weight = "bold";
} else {
 var colour = "";
 var weight = "";
}
document.getElementById("remaining").style.color = colour;
document.getElementById("remaining").style.fontWeight = weight;
setTimeout(updateCount, 400);
}
updateCount();
</script>';
	return $script;
}

function twitter_media_page($query) {
	$content = "";
	$status = stripslashes($_POST['message']);
	if ($_POST['message'] && $_FILES['image']['tmp_name']) {
		require 'tmhOAuth.php';
		list($lat, $long) = explode(',', $_POST['location']);
		$geo = 'N';
		if (is_numeric($lat) && is_numeric($long)) {
			$geo = 'Y';
			$post_data['lat'] = $lat;
			$post_data['long'] = $long;
		}
		setcookie_year('geo', $geo);
		list($oauth_token, $oauth_token_secret) = explode('|', $GLOBALS['user']['password']);
		$tmhOAuth = new tmhOAuth(array(
			'consumer_key'    => OAUTH_CONSUMER_KEY,
			'consumer_secret' => OAUTH_CONSUMER_SECRET,
			'user_token'      => $oauth_token,
			'user_secret'     => $oauth_token_secret,
		));
		$image = "{$_FILES['image']['tmp_name']};type={$_FILES['image']['type']};filename={$_FILES['image']['name']}";
		$code = $tmhOAuth->request('POST', 'https://upload.twitter.com/1/statuses/update_with_media.json',
							array(
								'media[]' => "@{$image}",
								'status' => " " . $status,
								'lat' => $lat,
								'long' => $long,
							),true,	true);
		if ($code == 200) {
			$json = json_decode($tmhOAuth->response['response']);
			$image_url = $json->entities->media[0]->media_url_https;
			$text = $json->text;
			$content = "<p>上传成功，撒花！</p><p><a href=\"".BASE_URL."simpleproxy.php?url=".$image_url.":large\" target='".get_target()."'><img src=\"".BASE_URL."simpleproxy.php?url=".$image_url.":thumb\" alt='' /></p></a><p>".twitter_parse_tags($text)."</p>";
		}
		else $content = "擦！上传失败鸟！<br/>代码：".$code."<br/>状态：".$status;
	}
	if($_POST) {
		if (!$_POST['message']) $content .= "<p>为这张图添加点说明文字吧。</p>";
		if (!$_FILES['image']['tmp_name']) $content .= "<p>请选择一幅图片来上传。</p>";
	}	
	$content .= "<form method='post' action='picture' enctype='multipart/form-data' name='upload_pict'>图片：<input type='file' name='image'/><br/>消息（可选）：<br/><textarea name='message' style='width:90%; max-width: 400px;' rows='3' id='message'>".$status."</textarea><br><input type='submit' value='发送'>";
	if(setting_fetch('browser') != 'mobile') $content .= "<span id='remaining'>120</span>";
	if(setting_fetch('browser') == 'desktop') $content .= "<script type='text/javascript'>document.onkeydown = function (){if(event.ctrlKey && window.event.keyCode == 13) document.upload_pict.submit();}</script>".geoloc($_COOKIE['geo']);
	$content .= '</form>';
	if(setting_fetch('browser') != 'mobile') $content .= js_counter("message", "120");
	return theme('page', '上传图片', $content);
}

function twitter_process($url, $post_data = false) {
	if ($post_data === true) $post_data = array();
	$status = $post_data['status'];
	//if (user_type() == 'oauth' && ( strpos($url, '/twitter.com') !== false || strpos($url, 'api.twitter.com') !== false || strpos($url, 'upload.twitter.com') !== false)) 
	user_oauth_sign($url, $post_data);
	//else if (strpos($url, 'api.twitter.com') !== false && is_array($post_data)) {
	/* if (strpos($url, 'api.twitter.com') !== false && is_array($post_data)) {
		$s = array();
		foreach ($post_data as $name => $value)
		$s[] = $name.'='.urlencode($value);
		$post_data = implode('&', $s);
	} */
	$api_start = microtime(1);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	if($post_data !== false && !$_GET['page']) {
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
	curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	$response = curl_exec($ch);
	$response_info=curl_getinfo($ch);
	$erno = curl_errno($ch);
	$er = curl_error($ch);
	curl_close($ch);
	global $api_time;
	global $rate_limit;

	// Split that headers and the body
	list($headers, $body) = explode("\n\n", $response, 2);

	// Place the headers into an array
	$headers = explode("\n", $headers);
	$headers_array;
	foreach($headers as $header) {
		list($key, $value) = explode(':', $header, 2);
		$headers_array[$key] = $value;
	}

	// Not ever request is rate limited
	if ($headers_array['X-RateLimit-Limit']) {
		$current_time = time();
		$ratelimit_time = $headers_array['X-RateLimit-Reset'];
		$time_until_reset = $ratelimit_time - $current_time;
		$minutes_until_reset = round($time_until_reset / 60);
		$currentdate = strtotime("now");
		$rate_limit = "Rate Limit: " . $headers_array['X-RateLimit-Remaining'] . " / " . $headers_array['X-RateLimit-Limit'] . " for the next $minutes_until_reset minutes";
	}

	//	The body of the request is at the end of the headers
	$body = end($headers);

	$api_time += microtime(1) - $api_start;
	switch( intval( $response_info['http_code'] ) ) {
		case 200:
		case 201:
			$json = json_decode($body);
			if ($json) return $json;
			return $body;
		case 401:
			user_logout();
			theme('error', "<p>错误：您的登录信息有问题。也许您被管理员耍了。</p><p>{$response_info['http_code']}: {$result}</p><hr><p>$url</p>");
		case 0:
			$result = $erno . ":" . $er . "<br/>";
			theme('error', '<h2>Twitter 它它它……超时了！</h2><p>Dabr 决定不再等待 Twitter 的回应了。管理员会找 ISP 扯皮的。过会再试试吧。<br />'. $result . ' </p>');
		default:
			$result = json_decode($body);
			$result = $result->error ? $result->error : $body;
			if (strlen($result) > 500) $result = "Twitter 抽风了，甭见怪。也许你现在有机会去围观鲸鱼图。过会再试试吧。<pre>┈┈╭━━━━━━╮┏╮╭┓┈┈\n┈┈┃╰╯┈┈┈┈┃╰╮╭╯┈┈\n┈┈┣━╯┈┈┈┈╰━╯┃┈┈┈\n┈┈╰━━━━━━━━━╯┈┈┈\n╭┳╭┳╭┳╭┳╭┳╭┳╭┳╭┳\n╯╰╯╰╯╰╯╰╯╰╯╰╯╰╯╰</pre>";
			else if ($result == "Status is over 140 characters.") {
			  theme('error', "<h2>您太啰嗦了，在 140 字里面把话说清楚吧……</h2><p>原文：<br/>{$status}</p><hr>");
			  //theme('status_form',$status);
			}
			theme('error', "<h2>调用 Twitter API 时粗线了一个错误。</h2><p>{$response_info['http_code']}: {$result}</p><hr>");
	}
}

function twitter_fetch($url) {
	global $services_time;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$user_agent = "Mozilla/5.0 (compatible; dabr-nh; " . BASE_URL . ")";
	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$fetch_start = microtime(1);
	$response = curl_exec($ch);
	curl_close($ch);
	$services_time += microtime(1) - $fetch_start;
	return $response;
}

function twitter_get_media($status) {
	if($status->entities->media && setting_fetch('hide_inline') != 'yes') {
		$image = $status->entities->media[0]->media_url_https;
		$media_html = "<a href=\"" . BASE_URL . "simpleproxy.php?url=" . $image . ":large" . "\" target='" . get_target() . "'>";
		$media_html .= "<img src=\"" . BASE_URL . "simpleproxy.php?url=" . $image . ":thumb\"/>";
		$media_html .= "</a><br />";
		return $media_html;
	}
}

function twitter_parse_tags($input, $entities = false) {
	$out = $input;
	$out = nl2br($out);
	if($entities) {
		if($entities->urls) {
			foreach($entities->urls as $urls) {
				if($urls->expanded_url != "") $display_url = $urls->expanded_url;
				else $display_url = $urls->url;
				$url = $urls->url;
				$parsed_url = parse_url($url);
				if (empty($parsed_url['scheme'])) $url = 'http://' . $url;
				if (setting_fetch('gwt') == 'on') {
					$encoded = urlencode($urls->url);
					$link = "http://google.com/gwt/n?u={$encoded}";
				}
				else $link = $display_url;
				$link_html = '<a href="' . $link . '" target="' . get_target() . '">' . $display_url . '</a>';
				$url = $urls->url;
				$pattern = '#((?<!href\=(\'|\"))'.preg_quote($url,'#').')#i';
				$out = preg_replace($pattern,  $link_html, $out);
			}
		}
		if($entities->hashtags) {
			foreach($entities->hashtags as $hashtag) {
				$text = $hashtag->text;
				$pattern = '/(^|\s)([#＃]+)('. $text .')/iu';
				$link_html = ' <a href="hash/' . $text . '">#' . $text . '</a> ';
				$out = preg_replace($pattern,  $link_html, $out, 1);
			}
		}
	}
	else {
		$urls = Twitter_Extractor::create($input)->extractURLs();

		if (setting_fetch('gwt') == 'on') {
			foreach($urls as $url) {
				$encoded = urlencode($url);
				$out = str_replace($url, "<a href='http://google.com/gwt/n?u={$encoded}' target='" . get_target() . "'>{$url}</a>", $out);
			}	
		}
		else $out = Twitter_Autolink::create($out)->addLinksToURLs();	
	
		$out = Twitter_Autolink::create($out)->setTarget('')->addLinksToHashtags();
	}

	$out = Twitter_Autolink::create($out)->setTarget('')->addLinksToUsernamesAndLists();

	if (setting_fetch('emoticons') != 'off') $out = emoticons($out);

	return $out;
}

function format_interval($timestamp, $granularity = 2) {
	$units = array(
	'年' => 31536000,
	'天'  => 86400,
	'小时' => 3600,
	'分钟'  => 60,
	'秒'  => 1
	);
	$output = '';
	foreach ($units as $key => $value) {
		if ($timestamp >= $value) {
			$output .= ($output ? ' ' : ''). floor($timestamp / $value) . $key;
			$timestamp %= $value;
			$granularity--;
		}
		if ($granularity == 0) break;
	}
	return $output ? $output : '0 秒';
}

function twitter_status_page($query) {
	$id = (string) $query[1];
	if (is_numeric($id)) {
		$request = API_URL."statuses/show/{$id}.json?include_entities=true";
		$status = twitter_process($request);
		$content = theme('status', $status);
		$content .= '<a href="http://translate.google.com/m?hl=zh-CN&tl=zh-CN&sl=auto&ie=UTF-8&q='.urlencode($status->text).'" target="'.get_target().'">请 Google 翻译一下这货</a></p>';
		$thread_id = $status->id_str;
		$request = API_URL."related_results/show/{$thread_id}.json";
		$threadstatus = twitter_process($request);
		if ($threadstatus[0]->results) {
			$array = $threadstatus[0]->results;
			$tl = array();
			foreach ($array as $key=>$value) {
				array_push($tl, $value->value);
				if ($value->value->in_reply_to_status_id_str == $thread_id && $array[key]->value->screen_name != "") array_push($tl, $status);
			}
			$tl = twitter_standard_timeline($tl, 'replies');
			$content .= '<p>对话素酱紫滴：</p>'.theme('timeline', $tl);
		}
		else $content .= '<p>木有对话可供显示。</p>';
		theme('page', "消息 $id", $content);
	}
}

function twitter_retweet_page($query) {
	$id = (string) $query[1];
	if (is_numeric($id)) {
		$request = API_URL."statuses/show/{$id}.json?include_entities=true";
		$tl = twitter_process($request);
		$content = theme('retweet', $tl);
		theme('page', '转发', $content);
	}
}

function twitter_refresh($page = NULL) {
	if (isset($page)) $page = BASE_URL . $page;
	else $page = $_SERVER['HTTP_REFERER'];
	header('Location: '. $page);
	exit();
}

function twitter_delete_page($query) {
	twitter_ensure_post_action();
	$id = (string) $query[1];
	if (is_numeric($id)) {
		$request = API_URL."statuses/destroy/{$id}.json?page=".intval($_GET['page']);
		$tl = twitter_process($request, true);
		twitter_refresh('user/'.user_current_username());
	}
}

function twitter_deleteDM_page($query) {
	twitter_ensure_post_action();
	$id = (string) $query[1];
	if (is_numeric($id)) {
		$request = API_URL."direct_messages/destroy/$id.json";
		twitter_process($request, true);
		twitter_refresh('directs/');
	}
}

function twitter_ensure_post_action() {
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') die('Error: Invalid HTTP request method for this action.');
}

function twitter_follow_page($query) {
	$user = $query[1];
	if ($user) {
		if($query[0] == 'follow') $request = API_URL."friendships/create/{$user}.json";
		else $request = API_URL."friendships/destroy/{$user}.json";
		twitter_process($request, true);
		twitter_refresh('friends');
	}
}

function twitter_block_page($query) {
	twitter_ensure_post_action();
	$user = $query[1];
	if ($user) {
		if($query[0] == 'block'){
			$request = API_URL."blocks/create/create.json?screen_name={$user}";
			twitter_process($request, true);
	                twitter_refresh("confirmed/block/{$user}");
		}
		else {
			$request = API_URL."blocks/destroy/destroy.json?screen_name={$user}";
			twitter_process($request, true);
	                twitter_refresh("confirmed/unblock/{$user}");
		}
	}
}

function twitter_spam_page($query) {
	twitter_ensure_post_action();
	$user = $query[1];
	$post_data = array("screen_name" => $user);
	$request = API_URL."report_spam.json";
	twitter_process($request, $post_data);
	twitter_refresh("confirmed/spam/{$user}");
}

function twitter_confirmation_page($query) {
	$action = $query[1];
	$target = $query[2]; //The name of the user we are doing this action on
	$target_id = $query[3]; //The targets's ID.  Needed to check if they are being blocked.
	switch ($action) {
		case 'block':
			if (twitter_block_exists($target_id)) {
				$action = 'unblock';
				$content  = "<p>你真的要<strong>解除对 $target 的屏蔽</strong>么？</p>";
				$content .= '<ul><li>如果他们又fo了你的话他们就又可以看到你的活动了。</li><li>你<em>可以</em>再次B掉他们，如果需要的话。</li></ul>';
			}
			else {
				$content = "<p>你真的要<strong>B掉 $target </strong>么？</p>";
				$content .= "<ul><li>你不会再出现在他们的朋友名单里了。</li><li>他们在你的主页也看不到你发送的消息了。</li><li>他们会没法fo你。</li><li>你<em>可以</em>以后解封但是会需要重新互fo一下。</li></ul>";
			}
			break;
		case 'delete':
			$content = '<p>你真的要把自己的推文删掉么？</p>';
			$content .= "<ul><li>消息：<strong>$target</strong></li><li>世上木有后悔药哦亲！</li></ul>";
			break;
		case 'deleteDM':
			$content = '<p>你真的要删掉那条私信么？</p>';
			$content .= "<ul><li>消息：<strong>$target</strong></li><li>世上木有后悔药哦亲！</li><li>而且这条私信会被从<em>接收和发送双方</em>的账户里删掉。</li></ul>";
			break;
		case 'spam':
			$content  = "<p>你确定要举报 <strong>$target</strong> 发布垃圾信息？</p>";
			$content .= "<p>他们同时会被B掉所以你会掉fo哦。</p>";
			break;
	}
	$content .= "<form action='$action/$target' method='post'><input type='submit' value='是的，表罗嗦。'/></form>";
	theme('Page', '确认', $content);
}

function twitter_confirmed_page($query) {
        $action = $query[1]; // The action. block, unblock, spam
        $target = $query[2]; // The username of the target
	
	switch ($action) {
                case 'block':
			$content  = "<p><span class='avatar'><img src='".BASE_URL."images/dabr.png' width='48' height='48' /></span><span class='status shift'>再见鸟<strong>被B掉的</strong> @$target 。</span></p>";
                        break;
                case 'unblock':
                        $content  = "<p><span class='avatar'><img src='".BASE_URL."images/dabr.png' width='48' height='48' /></span><span class='status shift'>欢迎回来，<strong>被解封的</strong> @$target 。</span></p>";
                        break;
                case 'spam':
                        $content = "<p><span class='avatar'><img src='".BASE_URL."images/dabr.png' width='48' height='48' /></span><span class='status shift'>哼！再见鸟可恶滴 @$target 。</span></p>";
                        break;
	}
 	theme ('Page', '已确认！', $content);
}

function twitter_friends_page($query) {
	// Which user's friends are we looking for?
	$user = $query[1];
	if(!$user) {
	  user_ensure_authenticated();
	  $user = user_current_username();
	}
	$title_user = $user;

	// How many users to show	
	$perPage = setting_fetch('perPage', 20);

	// Bug in Twitter (?) can't feth more than 100 users at a time
	if($perPage >= 100) $perPage = 100;

	// Get all the user ID of the friends	
	$request_ids = API_URL."friends/ids.json?screen_name={$user}";
	$json = twitter_process($request_ids);
	$ids = $json->ids;	

	// Poor man's pagination to fix broken Twitter API
	// friends/edent/30
	if($query[2]) $nextPage = $query[2];
	else $nextPage = 0;

	$nextPageURL = "friends/" . $user . "/";
	if(count($ids) < ($nextPage + $perPage)) $nextPageURL = null;
	else $nextPageURL .= ($nextPage + $perPage);

	// Paginate through the user IDs and build a API query
	$user_ids = "";
	for($i=$nextPage;$i<($nextPage+$perPage);$i++) $user_ids .= $ids[$i] . ",";
	// Twitter requests that we POST these User IDs
	$user_id_array = array();
	$user_id_array["user_id"] = $user_ids;

	// Construct the request
	$request = API_URL."users/lookup.xml";

	// Get the XML
	$xml = twitter_process($request, $user_id_array);
	$tl = simplexml_load_string($xml);

	// Place the users into an array
	$sortedUsers = array();
	
	foreach($tl as $user) {
	  $user_id = $user->id;
	  // $tl is *unsorted* - but $ids is *sorted*. So we place the users from $tl into a new array based on how they're sorted in $ids
	  $key = array_search($user_id, $ids);
	  $sortedUsers[$key] = $user;
	}

	// Sort the array by key so the most recent is at the top
	ksort($sortedUsers);

	// Format the output
	$content = theme('followers', $sortedUsers, $nextPageURL);
	theme('page', $title_user.' 关注的人', $content);
}

function twitter_followers_page($query) {
	// Which user's friends are we looking for?
	$user = $query[1];
	if(!$user) {
	  user_ensure_authenticated();
	  $user = user_current_username();
	}
	$title_user = $user;

	// How many users to show	
	$perPage = setting_fetch('perPage', 20);

	// Bug in Twitter (?) can't feth more than 100 users at a time
	if($perPage >= 100) $perPage = 100;

	// Get all the user ID of the friends	
	$request_ids = API_URL."followers/ids.json?screen_name={$user}";
	$json = twitter_process($request_ids);
	$ids = $json->ids;	

	// Poor man's pagination to fix broken Twitter API
	// followers/edent/30
	if($query[2]) $nextPage = $query[2];
	else $nextPage = 0;

	$nextPageURL = "followers/" . $user . "/";
	if(count($ids) < ($nextPage + $perPage)) $nextPageURL = null;
	else $nextPageURL .= ($nextPage + $perPage);

	// Paginate through the user IDs and build a API query
	$user_ids = "";
	for($i=$nextPage;$i<($nextPage+$perPage);$i++) $user_ids .= $ids[$i] . ",";

	// Twitter requests that we POST these User IDs
	$user_id_array = array();
	$user_id_array["user_id"] = $user_ids;

	// Construct the request
	$request = API_URL."users/lookup.xml";

	// Get the XML
	$xml = twitter_process($request, $user_id_array);
	$tl = simplexml_load_string($xml);

	// Place the users into an array
	$sortedUsers = array();
	
	foreach($tl as $user) {
	  $user_id = $user->id;
	  // $tl is *unsorted* - but $ids is *sorted*. So we place the users from $tl into a new array based on how they're sorted in $ids
	  $key = array_search($user_id, $ids);
	  $sortedUsers[$key] = $user;
	}

	// Sort the array by key so the most recent is at the top
	ksort($sortedUsers);

	// Format the output
	$content = theme('followers', $sortedUsers, $nextPageURL);
	theme('page', $title_user.' 的粉丝', $content);
}

//  Shows every user who retweeted a specific status
function twitter_retweeters_page($query) {
	// Which tweet are we looking for?
	$id = $query[1];

	// How many users to show	
	$perPage = setting_fetch('perPage', 20);

	// Bug in Twitter (?) can't feth more than 100 users at a time
	if ($perPage >= 100) $perPage = 100;

	// Get all the user ID of the friends	
	$request_ids = API_URL."statuses/{$id}/retweeted_by/ids.json?count=100";
	$json = twitter_process($request_ids);
	$ids = $json;	

	// Poor man's pagination to fix broken Twitter API
	// retweeted_by/1234567980/20
	$nextPage = $query[2];
	$nextPageURL = "retweeted_by/" . $id . "/";
	if (count($ids) < $nextPage + $perPage) $nextPageURL = null;
	else $nextPageURL .= ($nextPage + $perPage);

	// Paginate through the user IDs and build a API query
	$user_ids = "";
	for($i=$nextPage;$i<($nextPage+$perPage);$i++) $user_ids .= $ids[$i] . ",";

	// Twitter requests that we POST these User IDs
	$user_id_array = array();
	$user_id_array["user_id"] = $user_ids;

	// Construct the request
	$request = API_URL."users/lookup.xml";

	// Get the XML
	$xml = twitter_process($request, $user_id_array);
	$tl = simplexml_load_string($xml);

	// lace the users into an array
	$sortedUsers = array();

	foreach($tl as $user) {
	  $user_id = $user->id;
	  // $tl is *unsorted* - but $ids is *sorted*. So we place the users from $tl into a new array based on how they're sorted in $ids
	  $key = array_search($user_id, $ids);
	  $sortedUsers[$key] = $user;
	}

	// Sort the array by key so the most recent is at the top
	ksort($sortedUsers);

	// Format the output
	$content = theme('followers', $sortedUsers, $nextPageURL);
	theme('page', "目力所及范围内转发了 {$id} 的家伙", $content);
}

function twitter_update() {
	twitter_ensure_post_action();
	$status = stripslashes(trim($_POST['status']));
	if ($status) {
		$request = API_URL.'statuses/update.json';
		$post_data = array('source' => 'dabr', 'status' => $status);
		$in_reply_to_id = (string) $_POST['in_reply_to_id'];
		if (is_numeric($in_reply_to_id)) $post_data['in_reply_to_status_id'] = $in_reply_to_id;
		list($lat, $long) = explode(',', $_POST['location']);
		$geo = 'N';
		if (is_numeric($lat) && is_numeric($long)) {
			$geo = 'Y';
			$post_data['lat'] = $lat;
			$post_data['long'] = $long;
		}
		setcookie_year('geo', $geo);
		$b = twitter_process($request, $post_data);
	}
	twitter_refresh($_POST['from'] ? $_POST['from'] : '');
}

function twitter_get_place($lat, $long) {
	$request = API_URL.'geo/reverse_geocode.json';
	$request .= '?lat='.$lat.'&long='.$long.'&max_results=1';
	$locations = twitter_process($request);
	$places = $locations->result->places;
	foreach($places as $place) if ($place->id) return $place->id;
	return false;
}

function twitter_retweet($query) {
	twitter_ensure_post_action();
	$id = $query[1];
	if (is_numeric($id)) {
		$request = API_URL.'statuses/retweet/'.$id.'.xml';
		twitter_process($request, true);
	}
	twitter_refresh($_POST['from'] ? $_POST['from'] : '');
}

function twitter_replies_page() {
	$perPage = setting_fetch('perPage', 20);
	$request = API_URL.'statuses/mentions.json?page='.intval($_GET['page']).'&include_entities=true&count='.$perPage;
	$tl = twitter_process($request);
	$tl = twitter_standard_timeline($tl, 'replies');
	$content = theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', '提到我的', $content);
}

function twitter_retweets_page() {
	$perPage = setting_fetch('perPage', 20);
	$request = API_URL.'statuses/retweets_of_me.json?page='.intval($_GET['page']).'&include_entities=true&count='.$perPage;
	$tl = twitter_process($request);
	$tl = twitter_standard_timeline($tl, 'retweets');
	$content = theme('status_form');
	$content .= theme('timeline',$tl);
	theme('page', '转发', $content);
}

function twitter_directs_page($query) {
	$perPage = setting_fetch('perPage', 20);
	$action = strtolower(trim($query[1]));
	switch ($action) {
		case 'create':
			$to = $query[2];
			$content = theme('directs_form', $to);
			theme('page', '写私信', $content);

		case 'send':
			twitter_ensure_post_action();
			$to = trim(stripslashes($_POST['to']));
			$message = trim(stripslashes($_POST['message']));
			$request = API_URL.'direct_messages/new.json';
			twitter_process($request, array('user' => $to, 'text' => $message));
			twitter_refresh('directs/sent');

		case 'sent':
			$request = API_URL.'direct_messages/sent.json?page='.intval($_GET['page']).'&include_entities=true&count='.$perPage;
			$tl = twitter_standard_timeline(twitter_process($request), 'directs_sent');
			$content = theme_directs_menu();
			$content .= theme('timeline', $tl);
			theme('page', '已送出', $content);

		case 'inbox':
		default:
			$request = API_URL.'direct_messages.json?page='.intval($_GET['page']).'&include_entities=true&count='.$perPage;
			$tl = twitter_standard_timeline(twitter_process($request), 'directs_inbox');
			$content = theme_directs_menu();
			$content .= theme('timeline', $tl);
			theme('page', '邮筒', $content);
	}
}

function theme_directs_menu() {
	return '<p><a href="directs/create">写信</a> | <a href="directs/inbox">邮筒</a> | <a href="directs/sent">已送出</a></p>';
}

function theme_directs_form($to) {
	if ($to) {
		if (friendship_exists($to) != 1) $html_to = "<h3>注意！</h3> <b>".$to."</b> 并没有关注你。你没办法发私信给这个帐号。-__-||<br/>";
		$html_to .= "发私信给 <b>$to</b><input name='to' value='$to' type='hidden'>";
	}
	else $html_to .= "发送给：<br/><input name='to'><br/>消息：<br/>";
	$content = "<form action='directs/send' method='post' name='dmsg'>$html_to<br><textarea name='message' style='width:90%; max-width: 400px;' rows='3' id='message'></textarea><br><input type='submit' value='发送'>";
	if(setting_fetch('browser') != 'mobile') $content .= "<span id='remaining'>140</span>";
	$content .= "</form>";
	if(setting_fetch('browser') == 'desktop') $content .= "<script type='text/javascript'>document.onkeydown = function (){if(event.ctrlKey && window.event.keyCode == 13) document.dmsg.submit();}</script>";
	if(setting_fetch('browser') != 'mobile') $content .= js_counter("message");
	return $content;
}

function twitter_search_page() {
	$search_query = $_GET['query'];

	list($lat, $long) = explode(',', $_GET['location']);
	$loc = $_GET['location'];
	$radius = $_GET['radius'];
	$content = theme('search_form', $search_query);
	if (isset($_POST['query'])) {
		$duration = time() + (3600 * 24 * 365);
		setcookie('search_favourite', $_POST['query'], $duration, COOKIE_PREFIX);
		twitter_refresh('search');
	}
	if (!isset($search_query) && array_key_exists('search_favourite', $_COOKIE)) $search_query = $_COOKIE['search_favourite'];
	if ($search_query) {
		$tl = twitter_search($search_query, $lat, $long, $radius);
		if ($search_query !== $_COOKIE['search_favourite'])  $content .= '<form action="search/bookmark" method="post"><input type="hidden" name="query" value="'.$search_query.'" /><input type="submit" value="存为默认搜索" /></form>';
		$content .= theme('timeline', $tl);
	}

	theme('page', '搜索', $content);
}

function twitter_search($search_query, $lat = NULL, $long = NULL, $radius = NULL) {
	$perPage = setting_fetch('perPage', 20);

	$page = (int) $_GET['page'];
	if ($page == 0) $page = 1;
	$request = 'https://search.twitter.com/search.json?rpp='.$perPage.'&result_type=recent&q=' . urlencode($search_query).'&page='.$page.'&include_entities=true';
	
	if ($lat && $long) {
		$request .= "&geocode=$lat,$long,";
		if ($radius) $request .="$radius";
		else $request .="1km";
	}

	$tl = twitter_process($request);
	$tl = twitter_standard_timeline($tl->results, 'search');
	return $tl;
}

function twitter_find_tweet_in_timeline($tweet_id, $tl) {
	if (!is_numeric($tweet_id) || !$tl) return;

	if (array_key_exists($tweet_id, $tl)) $tweet = $tl[$tweet_id];
	else {
		$request = API_URL."statuses/show/{$tweet_id}.json?include_entities=true";
		$tweet = twitter_process($request);
	}
	return $tweet;
}

function twitter_user_page($query) {
	$screen_name = $query[1];
	$subaction = $query[2];
	$in_reply_to_id = (string) $query[3];
	$content = '';

	if (!$screen_name) theme('error', '木有用户名啊！');

	$user = twitter_user_info($screen_name);

	if (isset($user->status)) {
		$request = API_URL."statuses/user_timeline.json?screen_name={$screen_name}&include_rts=true&include_entities=true&page=".intval($_GET['page']);
		$tl = twitter_process($request);
		$tl = twitter_standard_timeline($tl, 'user');
	}

	$to_users = array($user->screen_name);
	$hashtags = array();

	if (is_numeric($in_reply_to_id)) {
		$tweet = twitter_find_tweet_in_timeline($in_reply_to_id, $tl);
		$out = twitter_parse_tags($tweet->text);
		$content .= "<p>此回复针对下列消息：<br />{$out}</p>";
		if ($subaction == 'replyall') {
			$found = Twitter_Extractor::create($tweet->text)->extractMentionedUsernames();
			$to_users = array_unique(array_merge($to_users, $found));
		}
		if ($tweet->entities->hashtags) $hashtags = $tweet->entities->hashtags;	
	}

	$status = '';
	foreach ($to_users as $username) if (!user_is_current_user($username)) $status .= "@{$username} ";

	foreach ($hashtags as $hashtag) $status .= "#{$hashtag->text} ";
	$content .= theme('status_form', $status, $in_reply_to_id);
	$content .= theme('user_header', $user);
	$content .= theme('timeline', $tl);
	theme('page', "用户 {$screen_name}", $content);
}

function twitter_favourites_page($query) {
	$screen_name = $query[1];
	if (!$screen_name) {
		user_ensure_authenticated();
		$screen_name = user_current_username();
	}
	$request = API_URL."favorites/{$screen_name}.json?page=".intval($_GET['page']).'&include_entities=true';
	$tl = twitter_process($request);
	$tl = twitter_standard_timeline($tl, 'favourites');
	$content = theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', '收藏', $content);
}

function twitter_mark_favourite_page($query) {
	$id = (string) $query[1];
	if (!is_numeric($id)) return;
	if ($query[0] == 'unfavourite') $request = API_URL."favorites/destroy/$id.json";
	else $request = API_URL."favorites/create/$id.json";
	twitter_process($request, true);
	twitter_refresh();
}

function twitter_home_page() {
	user_ensure_authenticated();
	$perPage = setting_fetch('perPage', 20);
	$request = API_URL.'statuses/home_timeline.json?include_rts=true&include_entities=true&count='.$perPage;
	if ($_GET['max_id']) $request .= '&max_id='.$_GET['max_id'];
	if ($_GET['since_id']) $request .= '&since_id='.$_GET['since_id'];
	$tl = twitter_process($request);
	$tl = twitter_standard_timeline($tl, 'friends');
	$content = theme('status_form');
	$content .= theme('timeline', $tl);
	theme('page', '主页', $content);
}

function twitter_hashtag_page($query) {
	$hashtag = '#'.$query[1];
	$content = theme('status_form', $hashtag.' ');
	$tl = twitter_search($hashtag);
	$content .= theme('timeline', $tl);
	theme('page', $hashtag, $content);
}

function theme_status_form($text = '', $in_reply_to_id = NULL) {
	if (user_is_authenticated()) {
		if ($_GET['status']) $text = $_GET['status'];
		return "<fieldset><legend><img src='".BASE_URL."images/bird_16_blue.png' width='16' height='16'/>发生了神马？</legend><form method='post' action='update'><input name='status' value='{$text}' maxlength='140' /><input name='in_reply_to_id' value='{$in_reply_to_id}' type='hidden'/><input type='submit' value='推！'/></form><a href='picture'>发图片</a></fieldset>";
	}
}

function theme_status($status) {
	if($status->id_str) $status->id = $status->id_str;
	$feed[] = $status;
	$tl = twitter_standard_timeline($feed, 'status');
	$content = theme('timeline', $tl);
	return $content;
}

function theme_retweet($status) {
	$text = "RT @{$status->user->screen_name}: {$status->text}";
	$length = function_exists('mb_strlen') ? mb_strlen($text,'UTF-8') : strlen($text);
	$from = substr($_SERVER['HTTP_REFERER'], strlen(BASE_URL));
	if($status->user->protected == 0) $content.="<p>直接转发：</p><form action='twitter-retweet/{$status->id_str}' method='post'><input type='hidden' name='from' value='$from'/><input type='submit' value='直接转发'/></form><hr/>";
	else $content.="<p>@{$status->user->screen_name} 不让你转发这条消息。但是你可以假装编辑下再发啊。</p>";
	$content .= "<p>编辑后转发：</p><form action='update' method='post' name='rt_edited'><input type='hidden' name='from' value='$from'/><textarea name='status' style='width:90%; max-width: 400px;' rows='3' id='status'>$text</textarea><br/><input type='submit' value='转发'/>";
	if(setting_fetch('browser') != 'mobile') $content .= "<span id='remaining'>".(140-$length)."</span>";
	if(setting_fetch('browser') == 'desktop') $content .= geoloc($_COOKIE['geo']);
	$content .= "</form>";
	if(setting_fetch('browser') == 'desktop') $content .= "<script type='text/javascript'>document.onkeydown = function (){if(event.ctrlKey && window.event.keyCode == 13) document.rt_edited.submit();}</script>";
	if(setting_fetch('browser') != 'mobile') $content .= js_counter("status");
	return $content;
}

function twitter_tweets_per_day($user, $rounding = 1) {
	$days_on_twitter = (time() - strtotime($user->created_at)) / 86400;
	return round($user->statuses_count / $days_on_twitter, $rounding);
}

function theme_user_header($user) {
	$following = friendship($user->screen_name);
	$followed_by = $following->relationship->target->followed_by; //The $user is followed by the authenticating
	$following = $following->relationship->target->following;
	$name = theme('full_name', $user);
	$full_avatar = theme_get_full_avatar($user);
	$link = theme('external_link', $user->url);
	$cleanLocation = str_replace(array("iPhone: ","ÜT: "),"",$user->location);
	$raw_date_joined = strtotime($user->created_at);
	$date_joined = date('Y 年 m 月 d 日', $raw_date_joined);
	$tweets_per_day = twitter_tweets_per_day($user, 1);
	$bio = twitter_parse_tags($user->description);
	$out = "<div class='profile'>";
	$out .= "<span class='avatar'>".theme('external_link', $full_avatar, theme('avatar', theme_get_avatar($user)))."</span>";
	$out .= "<span class='status shift'><b>{$name}</b><br/>";
	$out .= "<span class='about'>";
	if ($user->verified == true) $out .= '<strong>已认证账户</strong><br/>';
	if ($user->protected == true) $out .= '<strong>保密的消息</strong><br/>';
	$out .= "简介：{$bio}<br/>";
	$out .= "链接：{$link}<br/>";
	$out .= "地址：<a href=\"http://maps.google.com.hk/m?q={$cleanLocation}\" target=\"" . get_target() . "\">{$user->location}</a><br/>";
	$out .= "加入时间：{$date_joined} （每天约 ".$tweets_per_day." 条消息）<br/>";
	if (user_is_current_user($user->screen_name)) $out .= "<strong><a href='editbio'>编辑个人资料 →</a></strong>";
	$out .= "</span></span>";
	$out .= "<div class='features'>";
	$out .= $user->statuses_count.' 条消息';

	if ($user->protected == true && $followed_by == false) {
		$out .= " | " . $user->followers_count . ' 个粉丝';
		$out .= " | " . $user->friends_count . ' 个偶像';
		$out .= " | " . $user->favourites_count . ' 条收藏';
	}
	else {
		$out .= " | <a href='followers/{$user->screen_name}'>" . $user->followers_count . " 个粉丝</a>";
		$out .= " | <a href='friends/{$user->screen_name}'>" . $user->friends_count . " 个偶像</a>";
		$out .= " | <a href='favourites/{$user->screen_name}'>" . $user->favourites_count . " 条收藏</a>";
	}

	$out .= " | <a href='lists/{$user->screen_name}/memberships'>" . "在 " . $user->listed_count . " 个列表中</a>";
	$out .=	" | <a href='directs/create/{$user->screen_name}'>发私信</a>";

	if (strtolower($user->screen_name) !== strtolower(user_current_username())) {
		if ($followed_by == false) $out .= " | <a href='follow/{$user->screen_name}'>关注</a>";
		else $out .= " | <a href='unfollow/{$user->screen_name}'>取消关注</a>";
		$out.= " | <a href='confirm/block/{$user->screen_name}/{$user->id}'>屏蔽/取消屏蔽</a>";
		$out .= " | <a href='confirm/spam/{$user->screen_name}/{$user->id}'>报告为垃圾信息</a>";
	}
	$out .= " | <a href='search?query=%40{$user->screen_name}'>搜索 @{$user->screen_name}</a>";
	if (user_is_current_user($user->screen_name)) $out .= " | <a href='retweets'>被转发的消息</a>";
	$out .= "</div></div>";
	return $out;
}

function theme_avatar($url, $force_large = false) {
	$size = $force_large ? 48 : 24;
	return "<img src='$url' height='$size' width='$size' />";
}

function theme_status_time_link($status, $is_link = true) {
	$time = strtotime($status->created_at);
	if ($time > 0) {
		if (twitter_date('dmy') == twitter_date('dmy', $time) && !setting_fetch('timestamp'))  $out = format_interval(time() - $time, 1). '前';
		else $out = twitter_date('H:i', $time);
	}
	else $out = $status->created_at;
	if ($is_link) $out = "<a href='status/{$status->id}' class='time'>$out</a>";
	return $out;
}

function twitter_date($format, $timestamp = null) {
	$offset = setting_fetch('utc_offset', 0) * 3600;
	if (!isset($timestamp)) $timestamp = time();
	return gmdate($format, $timestamp + $offset);
}

function twitter_standard_timeline($feed, $source) {
	$output = array();
	if (!is_array($feed)) return $output;

	foreach($feed as $key => $status) {
		if($status->id_str) $feed[$key]->id = $status->id_str;
		if($status->in_reply_to_status_id_str) $feed[$key]->in_reply_to_status_id = $status->in_reply_to_status_id_str;
		if($status->retweeted_status->id_str) $feed[$key]->retweeted_status->id = $status->retweeted_status->id_str;
	}

	switch ($source) {
		case 'status':
		case 'favourites':
		case 'friends':
		case 'replies':
		case 'retweets':
		case 'user':
			foreach ($feed as $status) {
				$new = $status;
				if ($new->retweeted_status) {
					$retweet = $new->retweeted_status;
					unset($new->retweeted_status);
					$retweet->retweeted_by = $new;
					$retweet->original_id = $new->id;
					$new = $retweet;
				}
				$new->from = $new->user;
				unset($new->user);
				$output[(string) $new->id] = $new;
			}
			return $output;
		case 'search':
			foreach ($feed as $status) {
				$output[(string) $status->id] = (object) array(
					'id' => $status->id,
					'text' => $status->text,
					'source' => strpos($status->source, '&lt;') !== false ? html_entity_decode($status->source) : $status->source,
					'from' => (object) array(
						'id' => $status->from_user_id,
						'screen_name' => $status->from_user,
						'profile_image_url' => $status->profile_image_url,
						'profile_image_url_https' => $status->profile_image_url,
					),
					'to' => (object) array(
						'id' => $status->to_user_id,
						'screen_name' => $status->to_user,
					),
					'created_at' => $status->created_at,
					'geo' => $status->geo,
				);
			}
			return $output;
		case 'directs_sent':
		case 'directs_inbox':
			foreach ($feed as $status) {
				$new = $status;
				if ($source == 'directs_inbox') {
					$new->from = $new->sender;
					$new->to = $new->recipient;
				}
				else {
					$new->from = $new->recipient;
					$new->to = $new->sender;
				}
				unset($new->sender, $new->recipient);
				$new->is_direct = true;
				$output[$new->id_str] = $new;
			}
			return $output;
		default:
			echo "<h1>$source</h1><pre>";
			print_r($feed); die();
	}
}

function preg_match_one($pattern, $subject, $flags = NULL) {
	preg_match($pattern, $subject, $matches, $flags);
	return trim($matches[1]);
}

function twitter_user_info($username = null) {
	if (!$username)
	$username = user_current_username();
	$request = API_URL."users/show.json?screen_name=$username&include_entities=true";
	$user = twitter_process($request);
	return $user;
}

function theme_timeline($feed) {
	if (count($feed) == 0) return theme('no_tweets');
	if (count($feed) == 1) $hide_pagination = true;
	$rows = array();
	$page = menu_current_page();
	$date_heading = false;
	$first=0;

	foreach ($feed as &$status) $status->text = twitter_parse_tags($status->text, $status->entities);
	unset($status);

	if (!in_array(setting_fetch('browser'), array('text', 'worksafe'))&&EMBEDLY_KEY != '') embedly_embed_thumbnails($feed);
	foreach ($feed as $status) {
		if ($first==0) {
			$since_id = $status->id;
			$first++;
		}
		else {
			$max_id =  $status->id;
			if ($status->original_id) $max_id =  $status->original_id;
		}
		$time = strtotime($status->created_at);
		if ($time > 0) {
			$date = twitter_date('l jS F Y', strtotime($status->created_at));
			if ($date_heading !== $date) {
				$date_heading = $date;
				$rows[] = array('data' => array(twitter_date('↓↓↓ Y 年 m 月 d 日 ↓↓↓', strtotime($status->created_at))), 'class' => 'date');
			}
		}
		else $date = $status->created_at;
		$text = $status->text;
		if (!in_array(setting_fetch('browser'), array('text', 'worksafe'))) $media = twitter_get_media($status);
		$link = theme('status_time_link', $status, !$status->is_direct);
		$actions = theme('action_icons', $status);
		$avatar = theme('avatar', theme_get_avatar($status->from));
		$source = $status->source ? "来自 ".str_replace('rel="nofollow"', 'rel="nofollow" target="' . get_target() . '"', preg_replace('/&(?![a-z][a-z0-9]*;|#[0-9]+;|#x[0-9a-f]+;)/i', '&amp;', $status->source)." 部门") : ''; //need to replace & in links with &amps and force new window on links
		if ($status->place->name) $source .= " " . $status->place->name . ", " . $status->place->country;
		if ($status->in_reply_to_status_id) $source .= " <a href='status/{$status->in_reply_to_status_id_str}'>对 {$status->in_reply_to_screen_name} 的回复</a>";
		if ($status->retweet_count) {
			$source .= " <a href='retweeted_by/{$status->id}'>被转发了 ";
			$source .= $status->retweet_count . " 次</a>";
		}
		if ($status->retweeted_by) {
			$retweeted_by = $status->retweeted_by->user->screen_name;
			$source .= "<br /><a href='retweeted_by/{$status->id}'>被下列用户转发：</a> <a href='user/{$retweeted_by}'>{$retweeted_by}</a>";
		}
		$html = "<b><a href='user/{$status->from->screen_name}'>{$status->from->screen_name}</a></b> $actions $link<br />{$text}<br />$media<small>$source</small>";
		unset($row);
		$class = 'status';
		if ($page != 'user' && $avatar) {
			$row[] = array('data' => $avatar, 'class' => 'avatar');
			$class .= ' shift';
		}
		$row[] = array('data' => $html, 'class' => $class);
		$class = 'tweet';
		if ($page != 'replies' && twitter_is_reply($status)) $class .= ' reply';
		$row = array('data' => $row, 'class' => $class);
		$rows[] = $row;
	}
	$content = theme('table', array(), $rows, array('class' => 'timeline'));

	if ($page != '' && !$hide_pagination) $content .= theme('pagination');
	else if (!$hide_pagination) {
		if(is_64bit()) $max_id = intval($max_id) - 1; //stops last tweet appearing as first tweet on next page
		$links[] = "<a href='{$_GET['q']}?max_id=$max_id' accesskey='9'>更早</a> 9";
		$content .= '<p>'.implode(' | ', $links).'</p>';
	}
	return $content;
}

function twitter_is_reply($status) {
	if (!user_is_authenticated()) return false;
	$user = user_current_username();

	if ($status->entities) if ($status->entities->user_mentions) {
		$entities = $status->entities;
		foreach($entities->user_mentions as $mentions) if ($mentions->screen_name == $user) return true;
	}

	$found = Twitter_Extractor::create($status->text)->extractMentionedUsernames();
	foreach($found as $mentions) if (strcasecmp($mentions, $user) == 0) return true;
	return false;
}

function theme_followers($feed, $nextPageURL) {
	$rows = array();
	if(count($feed) == 0 || $feed == '[]') return '<p>兄弟，你混得贼惨了吧。</p>';
	foreach ($feed as $user) {
		$name = theme('full_name', $user);
		$tweets_per_day = twitter_tweets_per_day($user);
		$last_tweet = strtotime($user->status->created_at);
		$content = "{$name}<br/><span class='about'>";
		if($user->description != "") $content .= "简介：" . twitter_parse_tags($user->description) . "<br/>";
		if($user->location != "") $content .= "地址：{$user->location}<br/>";
		$content .= "统计：".$user->statuses_count." 条消息，".$user->friends_count." 个偶像，".$user->followers_count." 个粉丝，每天约 ".$tweets_per_day." 条消息<br/>";
		if($user->protected == 'true' && $last_tweet == 0) $content .= "保密的消息";
		else if($last_tweet == 0) $content .= "该用户从未发过推";
		else $content .= "上一条消息于 ".twitter_date('Y 年 m 月 d 日 H:i:s', $last_tweet)." 发出";
		$content .= "</span>";
		$rows[] = array('data' => array(array('data' => theme('avatar', theme_get_avatar($user)), 'class' => 'avatar'),array('data' => $content, 'class' => 'status shift')),'class' => 'tweet');
	}
	$content = theme('table', array(), $rows, array('class' => 'followers'));
	if($nextPageURL) $content .= "<a href='{$nextPageURL}'>下一页</a>";
	return $content;
}

function theme_retweeters($feed, $hide_pagination = false) {
	$rows = array();
	if (count($feed) == 0 || $feed == '[]') return '<p>木有人转发过这条消息。</p>';
	foreach ($feed->user as $user) {
		$name = theme('full_name', $user);
		$tweets_per_day = twitter_tweets_per_day($user);
		$last_tweet = strtotime($user->status->created_at);
		$content = "{$name}<br/><span class='about'>";
		if($user->description != "") $content .= "简介：" . twitter_parse_tags($user->description) . "<br/>";
		if($user->location != "") $content .= "地址：{$user->location}<br/>";
		$content .= "统计：".$user->statuses_count." 条消息，".$user->friends_count." 个偶像，".$user->followers_count." 个粉丝，"."每天约 ".$tweets_per_day." 条消息<br/></span>";
		$rows[] = array('data' => array(array('data' => theme('avatar', theme_get_avatar($user)), 'class' => 'avatar'),array('data' => $content, 'class' => 'status shift')),'class' => 'tweet');
	}
	$content = theme('table', array(), $rows, array('class' => 'followers'));
	if (!$hide_pagination) $content .= theme('list_pagination', $feed);
	return $content;
}

function theme_full_name($user) {
	$name = "<a href='user/{$user->screen_name}'>{$user->screen_name}</a>";
	if($user->name != "") $name .= " ({$user->name})";
	return $name;
}

function theme_get_avatar($object) {
	return BASE_URL . "simpleproxy.php?url=" . $object->profile_image_url_https;
}

function theme_get_full_avatar($object) {
	return BASE_URL . "simpleproxy.php?url=" . str_replace('_normal.', '.', $object->profile_image_url_https);
}

function theme_no_tweets() {
	return '<p>木有任何消息可供显示。</p>';
}

function theme_search_form($query) {
	$query = stripslashes(htmlentities($query,ENT_QUOTES,"UTF-8"));
	return '<form action="search" method="get"><input name="query" value="'. $query .'"/><input type="submit" value="给我搜"/></form><p><strong><a href="trends">趋势 →</a></strong></p>';
}

function theme_external_link($url, $content = null) {
	if (!$content) return "<a href='".long_url($url)."' target='" . get_target() . "'>". long_url($url) ."</a>";
	else return "<a href='$url' target='" . get_target() . "'>$content</a>";
}

function theme_pagination() {
	$page = intval($_GET['page']);
	if (preg_match('#&q(.*)#', $_SERVER['QUERY_STRING'], $matches)) $query = $matches[0];
	if ($page == 0) $page = 1;
	$links[] = "<a href='{$_GET['q']}?page=".($page+1)."$query' accesskey='9'>更早</a> 9";
	if ($page > 1) $links[] = "<a href='{$_GET['q']}?page=".($page-1)."$query' accesskey='8'>更晚</a> 8";
	return '<p>'.implode(' | ', $links).'</p>';
}

function theme_action_icons($status) {
	$from = $status->from->screen_name;
	$retweeted_by = $status->retweeted_by->user->screen_name;
	$retweeted_id = $status->retweeted_by->id;
	$geo = $status->geo;
	$actions = array();
	if (!$status->is_direct) $actions[] = theme('action_icon', "user/{$from}/reply/{$status->id}", BASE_URL.'images/reply.png', '@');
	if( $status->entities->user_mentions ) $actions[] = theme('action_icon', "user/{$from}/replyall/{$status->id}", BASE_URL.'images/replyall.png', 'REPLY ALL');
	if (!$status->is_direct) {
		if ($status->favorited == '1') $actions[] = theme('action_icon', "unfavourite/{$status->id}", BASE_URL.'images/star.png','UNFAV');
		else $actions[] = theme('action_icon', "favourite/{$status->id}", BASE_URL.'images/star_grey.png','FAV');
		if ($retweeted_by) $actions[] = theme('action_icon', "retweet/{$status->id}", BASE_URL.'images/retweeted.png','RT');
		else $actions[] = theme('action_icon', "retweet/{$status->id}", BASE_URL.'images/retweet.png','RT');
		if (user_is_current_user($from)) $actions[] = theme('action_icon', "confirm/delete/{$status->id}", BASE_URL.'images/trash.png','DEL');
		if ($retweeted_by && user_is_current_user($retweeted_by)) $actions[] = theme('action_icon',"confirm/delete/{$retweeted_id}", BASE_URL.'images/trash.png', 'DEL');
	}
	else $actions[] = theme('action_icon', "confirm/deleteDM/{$status->id}", BASE_URL.'images/trash.png','DEL');
	if ($geo !== null) {
		$latlong = $geo->coordinates;
		$lat = $latlong[0];
		$long = $latlong[1];
		$actions[] = theme('action_icon', "http://maps.google.com.hk/m?q={$lat},{$long}", BASE_URL.'images/map.png','MAP');
	}
	if(setting_fetch('browser') == 'desktop') {
		if (!user_is_current_user($from)) $actions[] = theme('action_icon', "directs/create/{$from}", BASE_URL.'images/dm.png','DM');
		$actions[] = theme('action_icon',"search?query=%40{$from}",BASE_URL.'images/q.png','?');
		$actions[] = theme('action_icon',"http://twitter.com/{$from}/statuses/{$status->id}",BASE_URL.'images/lnk.png','LINK');
		$actions[] = theme('action_icon',"http://twitter.com/statuses/user_timeline/{$from}.rss",BASE_URL.'images/rss.png','RSS');
		$actions[] = theme('action_icon',"http://zh-tw.whotwi.com/user/{$from}",BASE_URL.'images/pie.png','ANAL');
		//TODO: add embed tweet links.
	}
	return implode(' ', $actions);
}

function theme_action_icon($url, $image_url, $text) {
	if ($text == 'MAP' || $text == 'LINK' || $text == 'RSS' || $text == 'ANAL') return "<a href='$url' target='".get_target()."'><img src='$image_url' alt='$text' width='16' height='16'/></a>";
	return "<a href='$url'><img src='$image_url' alt='$text' width='16' height='16'/></a>";
}

function is_64bit() {
	$int = "9223372036854775807";
	$int = intval($int);
	return ($int == 9223372036854775807);
}
?>
