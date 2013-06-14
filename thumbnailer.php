<?

//////////////////////////////////////////////////////////////
// General Perpose Web Image Thumbnailer
// (C) dword1511 <zhangchi866@gmail.com>
// License: GNU GPLv3
//////////////////////////////////////////////////////////////

error_reporting(E_ALL ^ E_NOTICE);
require 'config.php';
// content arrangement in embdedly.php:
//<a href="link to full sized image via proxy"><img src="link to thumb images[here]"></a>
// expand to branch process instead of foreach for flexibility.

// TODO: Add page thumbnailer --> needs a lot of RAM
//       Add original image mode: translate URL and go_proxy().
//       Get the data all in once if URL is an image.

function no_thumb() {
  header('HTTP/1.1 301 Moved Permanently');
  header('Location: '.BASE_URL.'images/nothumb.png');
  die(0);
}

function go_proxy($url) {
  header('HTTP/1.1 301 Moved Permanently');
  header('Location: '.BASE_URL.'simpleproxy.php?url='.urlencode($url));
  die(0);
}

function curl_writefn($ch, $chunk) {
  global $curl_data, $curl_size;

  $curl_data .= $chunk;
  $curl_len   = strlen($chunk);
  $curl_size += $curl_len;

  // Avoiding process to much of it.
  if($curl_size > 65536) return -1;

  // Is it a image/otect-stream? stop early.
  if(preg_match('#Content\-Type\:\ image|Content\-Type\:\ application\/octet\-stream#', $curl_data) == 1) return -1;

  // Got what we need / End of header? Kill transfer.
  if(preg_match('#(property="og:image").*\/\>|\<\/head\>#i', $curl_data) == 1) return -1;

  return $curl_len;
}

function process_image($url) {
  // Fetch
  $c = curl_init();
  curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($c, CURLOPT_MAXREDIRS, 3);
  curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($c, CURLOPT_TIMEOUT, 10);
  curl_setopt($c, CURLOPT_URL, $url);
  curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($c, CURLOPT_HEADER, FALSE);
  $i = imagecreatefromstring(curl_exec($c));

  // If failed, call no_thumb();
  if($i === false) {
    error_log('Thumbnailer: failed to create an image from: '.$url);
    no_thumb();
  }

  // If resize needed(x | y > 150), crop(x | y < 200) & resize & output directly.
  $x = imagesx($i);
  $y = imagesy($i);

  if($x < 150 && $y < 150) go_proxy($url);

  // Pixels to scape. Will be doubled.
  // Then shrink with aspect ratio kept.
  $xs = 0;
  $ys = 0;
  $cr = 0.2;

  if($x < $y) {
    if($y > 300) $ys = $cr * $y;
    $ny = 150;
    if($x < 150) $nx = $x;
    else {
      $nx = 150;
      $xs = ($x - $y + 2 * $ys) / 2;
    }
  }
  else {
    if($x > 300) $xs = $cr * $x;
    $nx = 150;
    if($y < 150) $ny = $y;
    else {
      $ny = 150;
      $ys = ($y - $x + 2 * $xs) / 2;
    }
  }

  $ni = imagecreatetruecolor($nx, $ny);
  if($ni === false) no_thumb();

  imagecopyresampled($ni, $i, 0, 0, $xs, $ys, $nx, $ny, $x - 2 * $xs, $y - 2 * $ys);
  header('Content-Type: image/jpeg');
  imagejpeg($ni, NULL, 85);

  imagedestroy($i);
  imagedestroy($ni);
  die(0);
}

function get_meta($url) {
  global $curl_data, $curl_size;
  $curl_data = '';
  $curl_size = 0;

  $c = curl_init();
  curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
  // eg. nyti.ms produces 5 redirections.
  curl_setopt($c, CURLOPT_MAXREDIRS, 6);
  curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
  // do not waste time
  curl_setopt($c, CURLOPT_TIMEOUT, 10);
  // skip HTML + XMLNS + HEAD + META = 104 bytes. However, most server will ignore this.
  //curl_setopt($c, CURLOPT_RANGE, '104-');
  curl_setopt($c, CURLOPT_WRITEFUNCTION, 'curl_writefn');
  curl_setopt($c, CURLOPT_URL, $url);
  // we want to determine the content type.
  curl_setopt($c, CURLOPT_HEADER, TRUE);
  curl_exec($c);

  // will resize big ones later.
  if(preg_match('#Content\-Type\:\ image#', $curl_data) == 1) error_log('MATCH: Content-Type: image');
  preg_match('#property="og:image" content="([^\<\>]*?)"#', $curl_data, $matches);
  if($matches[1]) return $matches[1];
  preg_match('#content="([^\<\>]*)" property="og:image"#', $curl_data, $matches);
  if($matches[1]) return $matches[1];

  return '';
}

$url = !empty($_GET['url']) ? $_GET['url'] : null;
if($url == null) no_thumb();

/*
special cases that resize can be done by image providers
'#vines\.s3\.amazonaws\.com\/v\/thumbs\/([\w\-\.\?\=]+)#'
=> 'http://vines.s3.amazonaws.com/v/thumbs/%s', =============> this needs resize
'#news\.bbcimg\.co\.uk/media/images/([\w\/\.]+)#'
=> 'http://news.bbcimg.co.uk/media/images/%s', ===>need refine
'#graphics[\d]+\.nytimes\.com\/images\/([\w\/\-\.]+)#'
=> 'http://graphics8.nytimes.com/images/%s',=======================> analyse difference between og:image and twitter:image
*.png, *.jpg ,blah blah blah, or determine by mime. modify get_meta.
*/

// Get what we need
$link = get_meta($url);

// Let others do the resize-job themselves:
// Foursquare
$matches = '';
preg_match('#(irs[\d]\.4sqi\.net\/img\/general\/[\d]+x[\d]+\/[\w\.\-]+)#', $link, $matches);
if($matches) go_proxy(preg_replace('#\/[\d]+x[\d]+\/#', '/150x150/', $link));

// Gravatar
$matches = '';
preg_match('#gravatar.com/avatar/([\w]+)#', $link, $matches);
if($matches) go_proxy('http://gravatar.com/avatar/'.$matches[1].'?s=150');

// Do the actual job: $link == '' means images are unlikely included.
if($link != '') process_image($link);
else no_thumb();
