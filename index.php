<?php

session_start();

function http($url, $params=false) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  if ($params) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
  return json_decode(curl_exec($ch));
}

if (isset($_GET['logout'])) {
  unset($_SESSION['username']);
  header('Location: /');
  die();
}

if (isset($_SESSION['username'])) {
  echo '<p>Logged in as </p>';
  echo "<p>{$_SESSION['username']}</p>";
  echo '<p><a href="/?logout">Log Out</a></p>';
  die();
}

$client_id = '0oa11olndmbJx5HW54x6';
$client_secret = 'w2b1N-LeORE99c9LItfuNN4777BMrRhHPRD7Dswq';
$redirect_uri = 'http://localhost:8080';
$metadata_url = 'https://dev-741731.okta.com/oauth2/default/.well-known/oauth-authorization-server';
$metadata = http($metadata_url);

if (isset($_GET['code'])) { // Error Check
  if ($_SESSION['state'] != $_GET['state']) die('Authorization server returned invalid state parameter'); // Mitigate CSRF
  if (isset($_GET['error'])) die('Authorization server returned an error: ' . htmlspecialchars($_GET['error']));

  $response = http($metadata->token_endpoint, [
  'grant_type' => 'authorization_code',
  'code' => $_GET['code'],
  'redirect_uri' => $redirect_uri,
  'client_id' => $client_id,
  'client_secret' => $client_secret,
]);

  if (!isset($response->access_token)) die('Error fetching access token');

  $token = http($metadata->introspection_endpoint, [ // Introspection endpoint tells us the username of the person who logged in.
  'token' => $response->access_token,
  'client_id' => $client_id,
  'client_secret' => $client_secret,
]);

  if ($token->active == 1) {
    $_SESSION['username'] = $token->username;
    header('Location: /');
    die();
  }
}

if (!isset($_SESSION['username'])) {
  $_SESSION['state'] = hash('sha256', session_id());
  // bin2hex(random_bytes(5));
  $authorize_url = $metadata->authorization_endpoint.'?'.http_build_query([
    'response_type' => 'code',
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'state' => $_SESSION['state'],
    'scope' => 'openid',
  ]);
  echo '<p>Not logged in</p>';
  echo '<p><a href="'.$authorize_url.'">Log In</a></p>';
}

?>
