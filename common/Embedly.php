<?php

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
		'#pbs\.twimg\.com\/media\/([\w\-]+\.[\w]*)#'	=> 'http://pbs.twimg.com/media/%s:thumb',
		'#si[\d]\.twimg\.com\/profile_images\/([\d]+\/[\w]+.[\w]+)#'
								=> 'http://si0.twimg.com/profile_images/%s',
		'#www\.speedtest\.net\/(result\/[\d]+|iphone\/[\d]+)\.png#'
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
								$html = theme('external_link', $real_url, '<img src="'.simple_proxy_url(sprintf($thumbnail_url, $match)).'" />');
								$feed[$status->id]->text = $html . '<br />' . $feed[$status->id]->text;
								// shall we add a link here allowing access with internal proxy?
							}
							$matched = true;
						}
					}
					// Local thumbnailer
					if($matched == false) {
						$html = theme('external_link', $real_url, '<img src="thumbnailer.php?url='.urlencode($real_url).'" />');
						$feed[$status->id]->text = $html . '<br />' . $feed[$status->id]->text;
					}
				}
			}
		}
	}
}
