<?php

$em_curl_writefn = function($ch, $chunk) {
	global $em_curl;
	$em_curl .= $chunk;

	// End of html header? Kill transfer.
	if(strpos('</head>', $em_curl) == 1) return -1;

	return strlen($chunk);
};

function get_og_image($url) {
	// Really needs speed here, better find a way to do this in parallel.
	global $em_curl;
	$em_curl = '';

	$c   = curl_init();
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt($c, CURLOPT_TIMEOUT, 2);
	// skip: '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://opengraphprotocol.org/schema/"><head><meta '
	//curl_setopt($c, CURLOPT_RANGE, '104-'); // most server will not support this.
	curl_setopt($c, CURLOPT_WRITEFUNCTION, $em_curl_writefn);
	curl_setopt($c, CURLOPT_URL, 'http://localhost/admin/');
	curl_exec($c);

	preg_match('/property="og:image" content="(.*?)"/', $em_curl, $matches);
	if($matches[1]) return ($matches[1]);
	preg_match('/content="(.*?)" property="og:image"/', $em_curl, $matches);
	if($matches[1]) return ($matches[1]);
	return $url;
}


function embedly_embed_thumbnails(&$feed) {
	if(setting_fetch('hide_inline')) return $text;

	$services = array(
		'#youtube\.com\/watch\?v=([_-\d\w]+)#i'		=> 'http://i.ytimg.com/vi/%s/1.jpg',
		'#youtu\.be\/([_-\d\w]+)#i'			=> 'http://i.ytimg.com/vi/%s/1.jpg',

		'#instagr\.am\/p\/([_-\d\w]+)#i'		=> 'http://instagr.am/p/%s/media/?size=t',
		'#instagram\.com\/p\/([_-\d\w]+)#i'		=> 'http://instagr.am/p/%s/media/?size=t',

		'#imgur\.com\/([\w]+)[\s\.ls][\.\w]*#i'		=> 'http://imgur.com/%ss.png',
		'#imgur\.com\/gallery\/([\w]+)#i'		=> 'http://imgur.com/%ss.png',

		'#twitpic\.com\/([\d\w]+)#i'			=> 'http://twitpic.com/show/thumb/%s',

		'#yfrog\.com\/([\d\w]+)#'			=> 'http://yfrog.com/%s:small',

		'#img\.ly\/([\w\d]+)#i'				=> 'http://img.ly/show/thumb/%s',

		'#qik\.ly\/([_-\d\w]+)#i'			=> 'http://qik.ly/%s.jpg',
		'#twitgoo\.com\/([\d\w]+)#i'			=> 'http://twitgoo.com/show/thumb/%s',
		'#hellotxt\.com\/i\/([\d\w]+)#i'		=> 'http://hellotxt.com/image/%s.s.jpg',
		'#ts1\.in\/(\d+)#i'				=> 'http://ts1.in/mini/%s',
		'#moby\.to\/\?([\w\d]+)#i'			=> 'http://moby.to/%s:square',
		'#mobypicture\.com\/\?([\w\d]+)#i'		=> 'http://mobypicture.com/?%s:square',
		'#twic\.li\/photo\/([\w]+)#i'			=> 'http://twic.li/userimg/thumb_%s.jpg',
		'#tweetphoto\.com\/(\d+)#'			=> 'http://api.plixi.com/api/tpapi.svc/imagefromurl?url=http://tweetphoto.com/%s',
		'#plixi\.com\/p\/(\d+)#'			=> 'http://api.plixi.com/api/tpapi.svc/imagefromurl?url=http://plixi.com/p/%s&size=small',
		'#phz\.in\/([\d\w]+)#'				=> 'http://api.phreadz.com/thumb/%s?t=code',
		'#brizzly\.com\/pic\/([\w]+)#i'			=> 'http://pics.brizzly.com/thumb_sm_%s.jpg',
		'#pk\.gd\/([\d\w]+)#i'				=> 'http://img.pikchur.com/pic_%s_s.jpg',
		'#pikchur\.com\/([\d\w]+)#i'			=> 'http://img.pikchur.com/pic_%s_s.jpg',
		'#znl\.me\/([\d\w]+)#'				=> 'http://www.zannel.com/webservices/content/%s/Image-164x123-JPG.jpg',
		'#twitrpix\.com\/([\d\w]+)#i'			=> 'http://img.twitrpix.com/thumb/%s',
		// provided within og:image meta
		'#irs[\d]\.4sqi\.net\/img\/general\/[\d]+x[\d]+\/([\w.]+)#'
								=> 'http://irs3.4sqi.net/img/general/150x150/%s',
		'#vines\.s3\.amazonaws\.com\/v\/thumbs\/([\w\-\.\?\=]+)#i'
								=> 'http://vines.s3.amazonaws.com/v/thumbs/%s',
		'#news\.bbcimg\.co\.uk/media/images/([\w]+/[\w]+/[\w]+\.[\w]+)#i'
								=> 'http://news.bbcimg.co.uk/media/images/%s',
		// direct image urls that are allowed to proxy
		'#pbs\.twimg\.com\/media\/([\w\-]+\.[\w]*)#'	=> 'http://pbs.twimg.com/media/%s:thumb',
		'#si[\d]\.twimg\.com\/profile_images\/([\d]+/[\w]+.[\w]+)#i'
								=> 'http://si0.twimg.com/profile_images/%s',
		'#www\.speedtest\.net\/(result|iphone\/[\d]+)\.png#'
								=> 'http://www.speedtest.net/%s.png',
		);

	// Loop through the feed
	foreach($feed as &$status) {
		// If there are entities
		if($status->entities) {
			$entities = $status->entities;
			if($entities->urls) {
				// Loop through the URL entities
				foreach($entities->urls as $urls) {
					// Use the expanded URL, if it exists
					// If there is no expanded URL, use the regular URL
					if($urls->expanded_url != "") {
						foreach($services as $pattern => $thumbnail_url) {
							$real_url = $urls->expanded_url;
							if(preg_match_all($pattern, $real_url, $matches, PREG_PATTERN_ORDER) < 1) $real_url = get_og_image($real_url);
							if(preg_match_all($pattern, $real_url, $matches, PREG_PATTERN_ORDER) > 0) {
								foreach($matches[1] as $key => $match) {
									$html = theme('external_link', $urls->expanded_url, "<img src=\"" . BASE_URL . "simpleproxy.php?url=" . sprintf($thumbnail_url, $match) . "\" />");
									$feed[$status->id]->text = $html . '<br />' . $feed[$status->id]->text;
									// shall we add a link here allowing access with internal proxy?
								}
							}
						}
					}
					else {
						foreach($services as $pattern => $thumbnail_url) {
							$real_url = $urls->url;
							if(preg_match_all($pattern, $real_url, $matches, PREG_PATTERN_ORDER) < 1) $real_url = get_og_image($real_url);
							if(preg_match_all($pattern, $real_url, $matches, PREG_PATTERN_ORDER) > 0) {
								foreach($matches[1] as $key => $match) {
									$html = theme('external_link', $urls->url, "<img src=\"" . BASE_URL . "simpleproxy.php?url=" . sprintf($thumbnail_url, $match) . "\" />");
									$feed[$status->id]->text = $html . '<br />' . $feed[$status->id]->text;
								}
							}
						}
					}
				}
			}
		}
	}
}
