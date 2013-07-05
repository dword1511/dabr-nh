<?php

//////////////////////////////////////////////////////////////
// This is a simple HTTP port 80 (SSL forced off) proxy for bypassing raw data.
// (C) dword1511 <zhangchi866@gmail.com>
// License: GNU GPLv3
//////////////////////////////////////////////////////////////

error_reporting(E_ALL ^ E_NOTICE);

// This workaround should solve 4sqi.net's bug
header('Content-Type:');

// CGI getallheaders workaround
if(!function_exists('getallheaders')) {
  function getallheaders() {
    $headers = '';
    foreach($_SERVER as $name => $value)
      if(substr($name, 0, 5) == 'HTTP_')
        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
    return $headers;
  }
}

// Parse the query
$url = !empty($_GET['url']) ? $_GET['url'] : null;
if($url == null) {
  echo '<html><head><title>Error</title></head><body><h1>URL is not specified or method not supported.</h1></body></html>';
  return;
}

// Check URL and extract destination host
$url = str_replace('https://', 'http://', $url);
preg_match('#http\:\/\/([\w\.\_\-]+)\/#i', $url, $matches);
if(!$matches) preg_match('#http\:\/\/([\w\.\_\-]+)$#i', $url, $matches);

if(!$matches) {
  echo '<html><head><title>Error</title></head><body><h1>Bad URL.</h1></body></html>';
  return;
}

// Compose the request
$req         = getallheaders();
$req['Host'] = $matches[1];
$request     = "GET $url HTTP/1.1\r\n";
foreach($req as $key => $value)
  if($key != 'Cookie') $request .= "$key: $value\r\n"; // Do not leak clients' cookie.
$request    .= "\r\n";
$ip          = gethostbyname($req['Host']);
$socket      = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// Open the socket
if($socket === false) {
  echo '<html><head><title>Error</title></head><body><h1>Socket creation failed.</h1></body></html>';
  return;
}

if(socket_connect($socket, $ip, 80) === false) {
  echo '<html><head><title>Error</title></head><body><h1>Connection failed.</h1></body></html>';
  return;
}

// Send request
socket_write($socket, $request, strlen($request));

// Wait for response header
$res = '';
while(strpos($res, "\r\n\r\n") === false) $res .= socket_read($socket, 2048);

// Send header and get content length if provided.
list($resp, $body) = explode("\r\n\r\n", $res, 2);
$resp = explode("\r\n", $resp);
$len  = 0;
$h20x = strpos($resp[0], '200') || strpos($resp[0], '206');
foreach($resp as $value) {
  list($k, $v) = explode(':', $value, 2);
  if($k == 'Content-Length') $len = $v;
  // Do not proxy other site's cookie.
  if($k != 'Set-Cookie') header($value);
}

// Send content, end connection if finished.
echo $body;

if($h20x && $len == 0) {
  // stream without size
  while($out = socket_read($socket, 2048)) echo $out;
  socket_close($socket);
  exit;
}

$len -= strlen($body);
while($len > 0) {
  $out = socket_read($socket, 2048);
  if($out === false) {
    // Early finish, maybe a time-out or something.
    socket_close($socket);
    exit;
  }
  if($out != '') {
    echo $out;
    $len -= strlen($out);
  }
}

socket_close($socket);
