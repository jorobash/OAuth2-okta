<?php
session_start();

$fp = fopen(dirname(__FILE__).'/errorlog.txt', 'w');
$params = [];
function http($url, $params=false) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  if($params)
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
  return json_decode(curl_exec($ch));

//   $ch = curl_init();
// // Will return the response, if false it print the response
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// // Set the url
//   curl_setopt($ch, CURLOPT_VERBOSE, 1);
//    curl_setopt($ch, CURLOPT_STDERR, $fp);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// curl_setopt($ch, CURLOPT_URL,$url);
// // Execute
// $result=curl_exec($ch);
// if($result === false){
// 	echo 'Curl error: ' .curl_error($ch);
// }
// // Closing
// curl_close($ch);

// return json_decode($result);

}
$metadata = http("https://dev-458743.okta.com/oauth2/default/.well-known/oauth-authorization-server");
// var_dump($metadata);die;
// $result = json_decode(file_get_contents("https://dev-458743.okta.com/oauth2/default/.well-known/oauth-authorization-server");
// var_dump($metadata->authorization_endpoint);die;

$client_id = '0oaku9eowSABFkUct356';
$client_secret = 'MbEt-ahgzfcBuYF9_7uHQ4_jDTXZTcgOF9ii5jCI';
$redirect_url = 'http://localhost:83/oktaOauth2-autentication/';
var_dump($_SESSION);

if(isset($_GET['logout'])){
	unset($_SESSION['username']);
	header('Location: '.$_SERVER['PHP_SELF']);

}

if(isset($_SESSION['username'])){
	echo '<p>Logged in as </p>';
	echo '<p>' . $_SESSION['username']. '</p>';
	echo '<p><a href="'. $_SERVER['PHP_SELF'].'/?logout">Log out</a></p>';
	die();
}

if(isset($_GET['code'])){
	if(isset($_SESSION['state']) != $_GET['state']){
		die('bad state');	
	}

	$response = http($metadata->token_endpoint, [
		'code' => $_GET['code'],
		'grant_type' => 'authorization_code',
		'client_id' => $client_id,
		'client_secret' => $client_secret,
		'redirect_uri'  => $redirect_url
	]);
	
	$token = http($metadata->introspection_endpoint, [
		'client_id' => $client_id,
		'client_secret' => $client_secret,
		'token' => $response->access_token	
	]);
	var_dump($token);
	if($token->active){
		$_SESSION['username'] = $token->username;
		header('Location: ' .$_SERVER['PHP_SELF']);
	}
}

if(!isset($_SESSION['username'])){
	$_SESSION['state'] = bin2hex(random_bytes(7));
	$authorize_url = $metadata->authorization_endpoint . '?' . http_build_query([
		'response_type' => 'code',
		'client_id' => $client_id,
		'redirect_uri' => $redirect_url,
		'state' => $_SESSION['state']	
	]);

	// var_dump($authorize_url);

	echo '<p><a href="'. $authorize_url.'">Log In</a></p>';
}