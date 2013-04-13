<?php

menu_register(array(
	'lists' => array(
		'security' => true,
		'callback' => 'lists_controller',
		'display' => '列表',
	),
));

function lists_paginated_process($url) {
	// Adds cursor/pagination parameters to a query
	$cursor = $_GET['cursor'];
	if (!is_numeric($cursor)) $cursor = -1;
	$url .= '&cursor='.$cursor;
	return twitter_process($url);
}

function twitter_lists_tweets($user, $list) {
	// Tweets belonging to a list
	$url = API_NEW."lists/statuses.json?owner_screen_name={$user}&slug={$list}";
	if($_GET['max_id']) $url .= '&max_id=' . $_GET['max_id'];
	return twitter_process($url);
}

function twitter_lists_user_lists($user) {
	// Lists a user has created
	return twitter_process(API_NEW."lists/list.json?screen_name={$user}");
}

function twitter_lists_user_memberships($user) {
	// Lists a user belongs to
	return lists_paginated_process(API_NEW."lists/memberships.json?screen_name={$user}");
}

function twitter_lists_list_members($user, $list) {
	// Members of a list
	return lists_paginated_process(API_NEW."lists/members.json?owner_screen_name={$user}&slug={$list}");
}

function twitter_lists_list_subscribers($user, $list) {
	// Subscribers of a list
	return lists_paginated_process(API_NEW."lists/subscribers.json?owner_screen_name={$user}&slug={$list}");
}

/* Front controller for the new pages

List URLS:
lists -- current user's lists
lists/$user -- chosen user's lists
lists/$user/lists -- alias of the above
lists/$user/memberships -- lists user is in
lists/$user/$list -- tweets
lists/$user/$list/members
lists/$user/$list/subscribers
lists/$user/$list/edit -- rename a list (no member editting)
*/

function lists_controller($query) {
	// Pick off $user from $query or default to the current user
	$user = $query[1];
	if (!$user) $user = user_current_username();

	// Fiddle with the $query to find which part identifies the page they want
	if ($query[3]) {
		// URL in form: lists/$user/$list/$method
		$method = $query[3];
		$list = $query[2];
	} else {
		// URL in form: lists/$user/$method
		$method = $query[2];
	}

	// Attempt to call the correct page based on $method
	switch ($method) {
		case '':
		case 'lists':
			// Show which lists a user has created
			return lists_lists_page($user);
		case 'memberships':
			// Show which lists a user belongs to
			return lists_membership_page($user);
		case 'members':
			// Show members of a list
			return lists_list_members_page($user, $list);
		case 'subscribers':
			// Show subscribers of a list
			return lists_list_subscribers_page($user, $list);
		case 'edit':
			// TODO: List editting page (name and availability)
			break;
		default:
			// Show tweets in a particular list
			$list = $method;
			return lists_list_tweets_page($user, $list);
	}

	// Error to be shown for any incomplete pages (breaks above)
	return theme('error', 'List page not found');
}

/* Pages */

function lists_lists_page($user) {
	// Show a user's lists
	$lists = twitter_lists_user_lists($user);
	$content = "<p><a href='lists/{$user}/memberships'>包含了 {$user} 的列表</a> | <strong>{$user} 订阅的列表</strong></p>";
	$content .= theme('lists', $lists);
	theme('page', "{$user} 创建的列表", $content);
}

function lists_membership_page($user) {
	// Show lists a user belongs to
	$lists = twitter_lists_user_memberships($user);
	$content = "<p><strong>包含了 {$user} 的列表</strong> | <a href='lists/{$user}'>{$user} 订阅的列表</a></p>";
	$content .= theme('lists', $lists);
	theme('page', '列表与用户', $content);
}

function lists_list_tweets_page($user, $list) {
	// Show tweets in a list
	$tweets = twitter_lists_tweets($user, $list);
	$tl = twitter_standard_timeline($tweets, 'user');
	$content = theme('status_form');
	$list_url = "lists/{$user}/{$list}";
	$content .= "<p><a href='user/{$user}'>@{$user}</a>/<strong>{$list}</strong> 里的消息 | <a href='{$list_url}/members'>查看成员</a> | <a href='{$list_url}/subscribers'>查看订阅者</a></p>";
	$content .= theme('timeline', $tl);
	theme('page', "List {$user}/{$list}", $content);
}

function lists_list_members_page($user, $list) {
	// Show members of a list
	// TODO: add logic to CREATE and REMOVE members
	$p = twitter_lists_list_members($user, $list);

	// TODO: use a different theme() function? Add a "delete member" link for each member
	$content  = "<div class='heading'><a href='user/{$user}'>@{$user}</a>/<a href='lists/{$user}/{$list}'>{$list}</a> 里的成员：</div>\n";
	$content .= theme('followers_list', $p);
	theme('page', "{$user}/{$list} 的成员", $content);
}

function theme_listmembers($feed) {
	$rows = array();
	if (count($feed) == 0 || $feed == '[]') return '<p>这个列表目前还是空的。</p>';
	foreach ($feed->users->user as $user) {
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
	return theme('table', array(), $rows, array('class' => 'followers'));
}

function lists_list_subscribers_page($user, $list) {
	// Show subscribers of a list
	$p = twitter_lists_list_subscribers($user, $list);
	$content  = "<div class='heading'><a href='user/{$user}'>@{$user}</a>/<a href='lists/{$user}/{$list}'>{$list}</a> 的订阅者：</div>\n";
	$content .= theme('followers_list', $p);
	theme('page', "{$user}/{$list} 的订阅者", $content);
}

/* Theme functions */

function theme_lists($json) {
	if(isset($json->lists)) $lists = $json->lists;
	else             $lists = $json;
	if(sizeof($lists) == 0 || $lists == '[]') return "<p>木有列表可供显示。</p><pre>" . print_r($json,1) . "</pre>";

	$rows    = array();
	$headers = array('列表 ', '成员数 ', '订阅者数');
	foreach($lists as $list) {
		$url = "lists/{$list->user->screen_name}/{$list->slug}";
		$rows[] = array(
			"<a href='user/{$list->user->screen_name}'>@{$list->user->screen_name}</a>/<a href='{$url}'><strong>{$list->slug}</strong></a> ",
			"<a href='{$url}/members'>{$list->member_count}</a> ",
			"<a href='{$url}/subscribers'>{$list->subscriber_count}</a>",
		);
	}
	$content = theme('table', $headers, $rows);
	$content .= theme('list_pagination', $json);
	return $content;
}

function theme_list_pagination($json) {
	if ($cursor = (string) $json->next_cursor) {
		$links[] = "<a href='{$_GET['q']}?cursor={$cursor}'>下一页</a>";
	}
	if ($cursor = (string) $json->previous_cursor) {
		$links[] = "<a href='{$_GET['q']}?cursor={$cursor}'>上一页</a>";
	}
	if (count($links) > 0) return '<p>'.implode(' | ', $links).'</p>';
}
