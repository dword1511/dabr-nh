<?php

function em_curl_writefn($ch, $chunk) {
	global $em_curl;
	$em_curl .= $chunk;

	// Got what we need / End of header? Kill transfer.
	if(preg_match('#(property="og:image"|name="twitter:image").*\/\>|\<\/head\>#i', $em_curl) == 1) return -1;

	return strlen($chunk);
}

function get_og_image($url) {
	// Really needs speed here, better find a way to do this in parallel.
	global $em_curl;
	$em_curl = '';

	$c = curl_init();
	curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($c, CURLOPT_MAXREDIRS, 5); // nyti.ms produces 5 redirections.
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($c, CURLOPT_TIMEOUT, 3);
	// skip: '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://opengraphprotocol.org/schema/"><head><meta '
	//curl_setopt($c, CURLOPT_RANGE, '104-'); // most server will ignore this.
	curl_setopt($c, CURLOPT_WRITEFUNCTION, 'em_curl_writefn');
	curl_setopt($c, CURLOPT_URL, $url);
	curl_exec($c);

	// twitter:image meta is used first, then og:image, since sometimes og:image can be full-sized images
	preg_match('#content="(.*?)" name="twitter:image"#', $em_curl, $matches);
	if($matches[1]) return $matches[1];
	preg_match('#name="twitter:image" content="(.*?)"#', $em_curl, $matches);
	if($matches[1]) return $matches[1];
	preg_match('#property="og:image" content="(.*?)"#', $em_curl, $matches);
	if($matches[1]) return $matches[1];
	preg_match('#content="(.*?)" property="og:image"#', $em_curl, $matches);
	if($matches[1]) return $matches[1];
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
		// provided within og:image or twitter:image meta
		'#irs[\d]\.4sqi\.net\/img\/general\/[\d]+x[\d]+\/([\w.]+)#'
								=> 'http://irs3.4sqi.net/img/general/150x150/%s',
		'#vines\.s3\.amazonaws\.com\/v\/thumbs\/([\w\-\.\?\=]+)#'
								=> 'http://vines.s3.amazonaws.com/v/thumbs/%s',
		'#news\.bbcimg\.co\.uk/media/images/([\w\/\.]+)#'
								=> 'http://news.bbcimg.co.uk/media/images/%s',
		'#graphics[\d]+\.nytimes\.com\/images\/([\w\/\-\.]+)#'
								=> 'http://graphics8.nytimes.com/images/%s',
		'#gravatar.com/avatar/([\w]+)#'			=> 'http://gravatar.com/avatar/%s?s=150',
		// direct image urls that are allowed to proxy
		'#pbs\.twimg\.com\/media\/([\w\-]+\.[\w]*)#'	=> 'http://pbs.twimg.com/media/%s:thumb',
		'#si[\d]\.twimg\.com\/profile_images\/([\d]+/[\w]+.[\w]+)#'
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
					if($urls->expanded_url != "") $real_url = $urls->expanded_url;
					else $real_url = $urls->url;
					$matched = false;
					foreach($services as $pattern => $thumbnail_url) {
						if(preg_match_all($pattern, $real_url, $matches, PREG_PATTERN_ORDER) > 0) {
							foreach($matches[1] as $key => $match) {
								$html = theme('external_link', $real_url, '<img src="'.BASE_URL.'simpleproxy.php?url='.sprintf($thumbnail_url, $match).'" />');
								$feed[$status->id]->text = $html . '<br />' . $feed[$status->id]->text;
								// shall we add a link here allowing access with internal proxy?
							}
							$matched = true;
						}
					}

					if($matched == false) {
						$real_url = get_og_image($real_url);
						foreach($services as $pattern => $thumbnail_url) {
							if(preg_match_all($pattern, $real_url, $matches, PREG_PATTERN_ORDER) > 0) {
								foreach($matches[1] as $key => $match) {
									$html = '<img src="'.BASE_URL.'simpleproxy.php?url='.sprintf($thumbnail_url, $match).'" />';
									$feed[$status->id]->text = $html.'<br />'.$feed[$status->id]->text;
								}
							}
						}
					}
				}
			}
		}
	}
}
