<?php

function mobile_theme_action_icon($url, $image_url, $text) {
	if ($text == 'MAP')	{
		return "<a href='$url' target='_blank'><img src='$image_url' width='12' height='12' /></a>";
	}
	else if ($text == 'DM')	{
		return "<a href='$url'><img src='$image_url' width='16' height='11' /></a>";
	}
	else	{
		return "<a href='$url'><img src='$image_url' width='12' height='12' /></a>";
	}
}

?>
