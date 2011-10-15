<?php
function link_user($name) {
	if(user_is_authenticated()) return ' <a href="user/'.$name.'">@'.$name.'</a> ';
	else return ' <a href="https://twitter.com/'.$name.'">@'.$name.'</a> ';
}

function link_site($url, $title) {
	if (setting_fetch('gwt') == 'on') {
		$encoded = urlencode($url);
		$link = "http://google.com/gwt/n?u={$encoded}";
	}
	else $link = $url;
	return ' <a href="'.$link.'">'.$title.'</a> ';
}

function theme_about() {
	$content = '<div id="about" style="margin:0.5em; padding:0.1em 1em;"><h3>嘛是 Dabr?</h3>
<ul><li>使用 Twitter API 的基于 Web 的客户端。</li><li>一个';
	$content .= link_site('http://code.google.com/p/dabr/','开源的');
	$content .= '项目，原始版本是';
	$content .= link_user('davidcarrington');
	$content .= '带着';
	$content .= link_user('whatleydude');
	$content .= '的灵感，在牛x的'
	$content .= link_site('http://shkspr.mobi/blog/index.php/tag/dabr/','Terence Eden');
	$content .= '的帮助下完成的。</li><li>比较安全，因为所有登录信息都作为一个加密的 cookie 存在你的机器上。（服务器从来就不屑于存储这些玩意儿嘛！）</li></ul>
<p>如果有啥建议或者意见就直说吧 =。=</p><p><strong>这个站点使用的是一个修改版的 Dabr 。<br /> 修改版的项目主页在伟大的';
	$content .= link_site('https://github.com/dword1511/dabr-nh','github');
	$content .= '上。</strong></p><p>修改版的作者';
	$content .= link_user('dword1511');
	$content .= '感谢<ul><li>';
	$content .= link_user('yegle');
	$content .= '</li><li>';
	$content .= link_user('tifan');
	$content .= '</li><li>';
	$content .= link_user('shellexy');
	$content .= '</li></ul>等人提供的各种 patch 。</p></div>';
	return $content;
}
?>
