<?php

session_start();
$client_id = '0oa11olndmbJx5HW54x6';
$client_secret = 'w2b1N-LeORE99c9LItfuNN4777BMrRhHPRD7Dswq';
$redirect_uri = 'http://localhost:8080/';

if (isset($_SESSION['username'])) {
  echo '<p>Logged in as </p>';
  echo "<p>{$_SESSION['username']}</p>";
  echo '<p><a href="/?logout">Log Out</a></p>';
  die();
} else {
  $authorize_url = 'TODO';
  echo '<p>Not logged in</p>';
  echo '<p><a href="'.$authorize_url.'">Log In</a></p>';
}

function http($url, $params=false) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  if ($params) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
  return json_decode(curl_exec($ch));
}

?>
