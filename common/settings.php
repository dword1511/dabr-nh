<?php

/*
Syntax is
'Name|links,bodybg,bodyt,small,odd,even,replyodd,replyeven,menubg,menut,menua',

Assembled in theme_css()
*/

$GLOBALS['colour_schemes'] = array(
	0 => '傲娇粉|c06,fcd,623,c8a,fee,fde,ffa,dd9,c06,fee,fee',
	1 => '扎眼橙|b50,ddd,111,555,fff,eee,ffa,dd9,e81,c40,fff',
	2 => '清新蓝|138,ddd,111,555,fff,eee,ffa,dd9,138,fff,fff',
	3 => '鸭屎绿|293C03,ccc,000,555,fff,eee,CCE691,ACC671,495C23,919C35,fff',
	4 => '阴暗紫|d5d,000,ddd,999,222,111,202,101,909,222,000,000',
	5 => '猪血红|d12,ddd,111,555,fff,eee,ffa,dd9,c12,fff,fff',
);

menu_register(array(
	'settings' => array(
		'callback' => 'settings_page',
		//'display' => '设置',
		'hidden' => true,
	),
	'reset' => array(
		'hidden' => true,
		'callback' => 'cookie_monster',
	),
));

function cookie_monster() {
	$cookies = array(
		'browser',
		'settings',
		'utc_offset',
		'search_favourite',
		'perPage',
		'USER_AUTH',
	);
	$duration = time() - 3600;
	foreach ($cookies as $cookie) {
		setcookie($cookie, NULL, $duration, COOKIE_PREFIX);
		setcookie($cookie, NULL, $duration);
	}
	return theme('page', 'Cookies 已经被吃掉了', '<p>储存在您机器上有关 dabr 的 Cookie 已经被清除，所有设置已复位，请重新登陆。</p>');
}

function setting_fetch($setting, $default = NULL) {
	$settings = (array) unserialize(base64_decode($_COOKIE['settings']));
	if (array_key_exists($setting, $settings)) return $settings[$setting];
	else return $default;
}

function setcookie_year($name, $value) {
	$duration = time() + (3600 * 24 * 365);
	setcookie($name, $value, $duration, COOKIE_PREFIX);
}

function settings_page($args) {
	if ($args[1] == 'save') {
		$settings['browser']     = $_POST['browser'];
		$settings['gwt']         = $_POST['gwt'];
		$settings['perPage']     = $_POST['perPage'];
		$settings['colours']     = $_POST['colours'];
		$settings['timestamp']   = $_POST['timestamp'];
		$settings['hide_inline'] = $_POST['hide_inline'];
		$settings['utc_offset']  = (float)$_POST['utc_offset'];
		$settings['emoticons']   = $_POST['emoticons'];
		
		if (MYSQL_USERS == 'ON' && $newpass = $_POST['newpassword']) {
			user_is_authenticated();
			list($key, $secret) = explode('|', $GLOBALS['user']['password']);
			$sql = sprintf("REPLACE INTO user (username, oauth_key, oauth_secret, password) VALUES ('%s', '%s', '%s', MD5('%s'))",  mysql_escape_string(user_current_username()), mysql_escape_string($key), mysql_escape_string($secret), mysql_escape_string($newpass));
			mysql_query($sql);
		}

		setcookie_year('settings', base64_encode(serialize($settings)));
		twitter_refresh('');
	}

	$modes = array(
		'mobile' => '手机',
		'desktop' => '电脑',
		'text' => '文本',
		'worksafe' => '工作',
	);

	$perPage = array(
		'5' => '每页显示 5 推',
		'10' => '每页显示 10 推',
		'20' => '每页显示 20 推',
		'30' => '每页显示 30 推',
		'40' => '每页显示 40 推',
		'50' => '每页显示 50 推',
		'100' => '每页显示 100 推',
		'150' => '每页显示 150 推',
		'200' => '每页显示 200 推',
	);

	$gwt = array(
		'off' => '直接',
		'on' => '通过 GWT',
	);

	$emoticons = array(
		'on' => '打开',
		'off' => '关掉（推荐）',
	);

	$colour_schemes = array();
	foreach ($GLOBALS['colour_schemes'] as $id => $info) {
		list($name, $colours) = explode('|', $info);
		$colour_schemes[$id] = $name;
	}

	$utc_offset = setting_fetch('utc_offset', 0);
	if ($utc_offset > 0) $utc_offset = '+' . $utc_offset;

	$content .= '<form action="settings/save" method="post">';
	$content .= '<p>配色方案：<br /><select name="colours" style="width:60%;max-width:200px;">';
	$content .= theme('options', $colour_schemes, setting_fetch('colours', 2)).'</select></p>'; // should be the same default value as used in theme.php
	$content .= '<p>情景模式：<br /><select name="browser" style="width:60%;max-width:200px;">';
	$content .= theme('options', $modes, $GLOBALS['current_theme']).'</select></p>';
	$content .= '<p>每页推数:<br /><select name="perPage" style="width:60%;max-width:200px;">';
	$content .= theme('options', $perPage, setting_fetch('perPage', 20)).'</select></p>';
	$content .= '<p>蛋疼的表情符转换：<br /><select name="emoticons" style="width:60%;max-width:200px;">';
	$content .= theme('options', $emoticons, setting_fetch('emoticons', $GLOBALS['current_theme'] == 'text' ? 'on' : 'off')).'</select></p>';
	$content .= '<p>外链方式：<br /><select name="gwt" style="width:60%;max-width:200px;">';
	$content .= theme('options', $gwt, setting_fetch('gwt', $GLOBALS['current_theme'] == 'text' ? 'on' : 'off'));
	$content .= '</select><small><br />Google Web Transcoder (GWT) 会把网页转换成适合不靠谱的山寨机浏览的格式。</small></p>';
	$content .= '<p><label><input type="checkbox" name="timestamp" value="yes" '. (setting_fetch('timestamp') == 'yes' ? ' checked="checked" ' : '') .' /> 使用 ' . twitter_date('H:i') . ' 而不是“25 秒之前”来显示时间。</label></p>';
	$content .= '<p><label><input type="checkbox" name="hide_inline" value="yes" '. (setting_fetch('hide_inline') == 'yes' ? ' checked="checked" ' : '') .' /> 不要显示内嵌的媒体（Twitpic 啊 Youtube 啊啥的）。</label></p>';
	$content .= '<p><label>现在的 UTC 时间是 ' . gmdate('H:i') . ' ，考虑到存在 <input type="text" name="utc_offset" value="'. $utc_offset .'" size="3" /> 的时差，我会把时间显示成 ' . twitter_date('H:i') . ' 这样。<br />呐，如果你觉得时间不对就调调时差吧。</label></p>';
	if (MYSQL_USERS == 'ON' && user_is_authenticated()) $content .= '<fieldset><legend>Dabr 账户</legend><small>如果你被墙了，你可以通过 Dabr 帐号和密码登录（当然 OAuth 还得翻墙的）。</small></p><p>修改 Dabr 密码<br /><input type="password" name="newpassword" /><br /><small>不想改的话就甭填好了。</small></fieldset>';
	$content .= '<p><input type="submit" value="存起来吧" style="width:60%;max-width:200px;"/></p></form>';
	$content .= '<hr /><p>访问<a href="reset">重置</a>页面，如果事情变得不太对头的话 —— 你会被登出，所有的设置也会被清空。</p><p><strong><a href="about">关于 Dabr-nh →</a></strong></p>';
	return theme('page', '设置', $content);
}
