<?php

function embedly_embed_thumbnails(&$feed) {
	if(setting_fetch('hide_inline')) return $text;

	$services = array(
		'#youtube\.com\/watch\?v=([_-\d\w]+)#i'		=> 'http://i.ytimg.com/vi/%s/1.jpg',
		'#youtu\.be\/([_-\d\w]+)#i'			=> 'http://i.ytimg.com/vi/%s/1.jpg',
		'#qik\.ly\/([_-\d\w]+)#i'			=> 'http://qik.ly/%s.jpg',
		'#twitpic\.com\/([\d\w]+)#i'			=> 'http://twitpic.com/show/thumb/%s',
		'#twitgoo\.com\/([\d\w]+)#i'			=> 'http://twitgoo.com/show/thumb/%s',
		'#hellotxt\.com\/i\/([\d\w]+)#i'		=> 'http://hellotxt.com/image/%s.s.jpg',
		'#ts1\.in\/(\d+)#i'				=> 'http://ts1.in/mini/%s',
		'#moby\.to\/\?([\w\d]+)#i'			=> 'http://moby.to/%s:square',
		'#mobypicture\.com\/\?([\w\d]+)#i'		=> 'http://mobypicture.com/?%s:square',
		'#twic\.li\/photo\/([\w]+)#i'			=> 'http://twic.li/userimg/thumb_%s.jpg',
		'#tweetphoto\.com\/(\d+)#'			=> 'http://api.plixi.com/api/tpapi.svc/imagefromurl?url=http://tweetphoto.com/%s',
		'#plixi\.com\/p\/(\d+)#'			=> 'http://api.plixi.com/api/tpapi.svc/imagefromurl?url=http://plixi.com/p/%s&size=small',
		'#phz\.in\/([\d\w]+)#'				=> 'http://api.phreadz.com/thumb/%s?t=code',
		'#imgur\.com\/([\w]+)[\s\.ls][\.\w]*#i'		=> 'http://imgur.com/%sm.png',
		'#imgur\.com\/gallery\/([\w]+)#i'		=> 'http://imgur.com/%sm.png',
		'#brizzly\.com\/pic\/([\w]+)#i'			=> 'http://pics.brizzly.com/thumb_sm_%s.jpg',
		'#img\.ly\/([\w\d]+)#i'				=> 'http://img.ly/show/medium/%s',
		'#pk\.gd\/([\d\w]+)#i'				=> 'http://img.pikchur.com/pic_%s_s.jpg',
		'#pikchur\.com\/([\d\w]+)#i'			=> 'http://img.pikchur.com/pic_%s_s.jpg',
		'#znl\.me\/([\d\w]+)#'				=> 'http://www.zannel.com/webservices/content/%s/Image-164x123-JPG.jpg',
		'#yfrog\.com\/([\d\w]+)#'			=> 'http://yfrog.com/%s:small',
		'#instagr\.am\/p\/([_-\d\w]+)#i'		=> 'http://instagr.am/p/%s/media/?size=t',
		'#instagram\.com\/p\/([_-\d\w]+)#i'		=> 'http://instagr.am/p/%s/media/?size=t',
		'#twitrpix\.com\/([\d\w]+)#i'			=> 'http://img.twitrpix.com/thumb/%s',
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
							if(preg_match_all($pattern, $urls->expanded_url, $matches, PREG_PATTERN_ORDER) > 0) {
								foreach($matches[1] as $key => $match) {
									$html = theme('external_link', $urls->expanded_url, "<img src=\"" . BASE_URL . "simpleproxy.php?url=" . sprintf($thumbnail_url, $match) . "\" />");
									$feed[$status->id]->text = $html . '<br />' . $feed[$status->id]->text;
								}
							}
						}
					}
					else {
						foreach($services as $pattern => $thumbnail_url) {
							if(preg_match_all($pattern, $urls->url, $matches, PREG_PATTERN_ORDER) > 0) {
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
