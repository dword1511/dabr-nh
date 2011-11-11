<?php

menu_register(array(
	'oauth' => array(
		'callback' => 'user_oauth',
		'hidden' => 'true',
	),
	'login' => array(
		'callback' => 'user_login',
		'hidden' => 'true',
	),
));

function user_oauth() {
	require_once 'OAuth.php';

	// Session used to keep track of secret token during authorisation step
	session_start();

	// Flag forces twitter_process() to use OAuth signing
	$GLOBALS['user']['type'] = 'oauth';

	if ($oauth_token = $_GET['oauth_token']) {
		// Generate ACCESS token request
		$params = array('oauth_verifier' => $_GET['oauth_verifier']);
		$response = twitter_process('https://api.twitter.com/oauth/access_token', $params);
		parse_str($response, $token);

		// Store ACCESS tokens in COOKIE
		$GLOBALS['user']['password'] = $token['oauth_token'] .'|'.$token['oauth_token_secret'];

		// Fetch the user's screen name with a quick API call
		unset($_SESSION['oauth_request_token_secret']);
		$user = twitter_process('https://api.twitter.com/account/verify_credentials.json');
		$GLOBALS['user']['username'] = $user->screen_name;

		_user_save_cookie(1);
		header('Location: '. BASE_URL);
		exit();

	} else {
		// Generate AUTH token request
		$params = array('oauth_callback' => BASE_URL.'oauth');
		$response = twitter_process('https://api.twitter.com/oauth/request_token', $params);
		parse_str($response, $token);

		// Save secret token to session to validate the result that comes back from Twitter
		$_SESSION['oauth_request_token_secret'] = $token['oauth_token_secret'];

		// redirect user to authorisation URL
		$authorise_url = 'https://api.twitter.com/oauth/authorize?oauth_token='.$token['oauth_token'];
        //header("Location: $authorise_url");
        if($_POST){
            header('Location: ' . BASE_URL.'oauth_proxy.php?p='.base64_encode($_POST['password']).'&u='.base64_encode($_POST['username']).'&g='.urlencode($authorise_url));                                                                                                                                 
        }
        else{
            header("Location: $authorise_url");
        }
	}
}

function user_oauth_sign(&$url, &$args = false) {
	require_once 'OAuth.php';

	$method = $args !== false ? 'POST' : 'GET';

	// Move GET parameters out of $url and into $args
	if (preg_match_all('#[?&]([^=]+)=([^&]+)#', $url, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$args[$match[1]] = $match[2];
		}
		$url = substr($url, 0, strpos($url, '?'));
	}

	$sig_method = new OAuthSignatureMethod_HMAC_SHA1();
	$consumer = new OAuthConsumer(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET);
	$token = NULL;

	if (($oauth_token = $_GET['oauth_token']) && $_SESSION['oauth_request_token_secret']) {
		$oauth_token_secret = $_SESSION['oauth_request_token_secret'];
	} else {
		list($oauth_token, $oauth_token_secret) = explode('|', $GLOBALS['user']['password']);
	}
	if ($oauth_token && $oauth_token_secret) {
		$token = new OAuthConsumer($oauth_token, $oauth_token_secret);
	}

	$request = OAuthRequest::from_consumer_and_token($consumer, $token, $method, $url, $args);
	$request->sign_request($sig_method, $consumer, $token);

	switch ($method) {
		case 'GET':
			$url = $request->to_url();
			$args = false;
			return;
		case 'POST':
			$url = $request->get_normalized_http_url();
			$args = $request->to_postdata();
			return;
	}
}

function user_ensure_authenticated() {
	require_once 'about.php';
	if (!user_is_authenticated()) {
		$content = theme('login');
		$content .= theme('about');
		theme('page', 'Login', $content);
	}
}

function user_logout() {
	unset($GLOBALS['user']);
	setcookie('USER_AUTH', '', time() - 3600, '/');
}

function user_is_authenticated() {
	if (!isset($GLOBALS['user'])) {
		if(array_key_exists('USER_AUTH', $_COOKIE)) {
			_user_decrypt_cookie($_COOKIE['USER_AUTH']);
		} else {
			$GLOBALS['user'] = array();
		}
	}
	
	// Auto-logout any users that aren't correctly using OAuth
	if (user_current_username() && user_type() !== 'oauth') {
		user_logout();
		twitter_refresh('logout');
	}

	if (!user_current_username()) {
		if ($_POST['username'] && $_POST['password']) {
			$GLOBALS['user']['username'] = trim($_POST['username']);
			$GLOBALS['user']['password'] = $_POST['password'];
			$GLOBALS['user']['type'] = 'oauth';
			
			$sql = sprintf("SELECT * FROM user WHERE username='%s' AND password=MD5('%s') LIMIT 1", mysql_escape_string($GLOBALS['user']['username']), mysql_escape_string($GLOBALS['user']['password']));
			$rs = mysql_query($sql);
			if ($rs && $user = mysql_fetch_object($rs)) {
				$GLOBALS['user']['password'] = $user->oauth_key . '|' . $user->oauth_secret;
			} else {
				theme('error', 'Invalid username or password.');
			}
			
			_user_save_cookie($_POST['stay-logged-in'] == 'yes');
			header('Location: '. BASE_URL);
			exit();
		} else {
			return false;
		}
	}
	return true;
}

function user_current_username() {
	return $GLOBALS['user']['username'];
}

function user_is_current_user($username) {
	return (strcasecmp($username, user_current_username()) == 0);
}

function user_type() {
	return $GLOBALS['user']['type'];
}

function _user_save_cookie($stay_logged_in = 0) {
	$cookie = _user_encrypt_cookie();
	$duration = 0;
	if ($stay_logged_in) {
		$duration = time() + (3600 * 24 * 365);
	}
	setcookie('USER_AUTH', $cookie, $duration, '/');
}

function _user_encryption_key() {
	return ENCRYPTION_KEY;
}

function _user_encrypt_cookie() {
	$plain_text = $GLOBALS['user']['username'] . ':' . $GLOBALS['user']['password'] . ':' . $GLOBALS['user']['type'];

	$td = mcrypt_module_open('blowfish', '', 'cfb', '');
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	mcrypt_generic_init($td, _user_encryption_key(), $iv);
	$crypt_text = mcrypt_generic($td, $plain_text);
	mcrypt_generic_deinit($td);
	return base64_encode($iv.$crypt_text);
}

function _user_decrypt_cookie($crypt_text) {
	$crypt_text = base64_decode($crypt_text);
	$td = mcrypt_module_open('blowfish', '', 'cfb', '');
	$ivsize = mcrypt_enc_get_iv_size($td);
	$iv = substr($crypt_text, 0, $ivsize);
	$crypt_text = substr($crypt_text, $ivsize);
	mcrypt_generic_init($td, _user_encryption_key(), $iv);
	$plain_text = mdecrypt_generic($td, $crypt_text);
	mcrypt_generic_deinit($td);

	list($GLOBALS['user']['username'], $GLOBALS['user']['password'], $GLOBALS['user']['type']) = explode(':', $plain_text);
}

function user_login() {
	return theme('page', '登录','
<form method="post" action="'.$_GET['q'].'"><p>
用户名：<input name="username" size="15" /><br />
密　码：<input name="password" type="password" size="15" /><br />
<label><input type="checkbox" checked="checked" value="yes" name="stay-logged-in" /> 记住我 </label><br />
<input type="submit" value="登录" /></p>
</form>

<p><b>注册步骤：</b></p>

<ol>
	<li>在墙外通过 <a href="oauth">Twitter/OAuth</a> 登录</li>
	<li>访问 Dabr 的“设置”页面来设置一个密码。</li>
	<li>现在你可以通过 Dabr 从包括兲朝的任何地方访问 Twitter 了。</li>
</ol>
');
}

function theme_login() {
	$content = '<div style="margin:1em; font-size: 1.2em">
<table><tr>
	<td><a href="oauth"><img src="'.BASE_URL.'images/twitter_button_2_lo.gif" alt="通过 Twitter/OAuth 登录" width="165" height="28" /></a></td>
</tr><tr>
	<th>小贴士：上面的玩意墙内的用户可不要乱点哦亲！被跨省了本人概不负责哦亲！</th>
</tr></table>
<p>直接通过用户名和密码登录是邪恶的，参见 <a href="http://blog.dabr.co.uk/">Dabr blog</a></p>';
	$content .='<hr /><h2>大家好！我是桑不起的 OAuth 黑中介</h2>
<em>（对，你最好表相信我，否则……）</em>
<form action="'.BASE_URL.'oauth" method="post">
<table>
	<tr><th>用户名：</th></tr>
	<tr><td><input type="text" name="username"/></td></tr>
	<tr><th>密　码：</th></tr>
	<tr><td><input type="password" name="password"/></td></tr>
</table>
<p><input type="submit" value="无节操登录"/></p>
</form>';

	if (MYSQL_USERS == 'ON') $content .= '<p>人在兲朝身不由己？ <a href="login">试试用 Dabr 账户登录！</a></p>';
	$content .= '</div>';
	return $content;
}

function theme_logged_out() {
	return '<p>已经登出了。<a href="">再登录玩玩？</a></p>';
}

?>
