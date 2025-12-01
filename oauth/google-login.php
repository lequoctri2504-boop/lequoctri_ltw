<?php
require_once '../config/config.php';
$google_auth_url='https://accounts.google.com/o/oauth2/v2/auth';
$params=['client_id'=>GOOGLE_CLIENT_ID,'redirect_uri'=>GOOGLE_REDIRECT_URI,'scope'=>'email profile','response_type'=>'code','state'=>bin2hex(random_bytes(16)),'access_type'=>'online'];
$_SESSION['oauth_state']=$params['state'];
$url=$google_auth_url.'?'.http_build_query($params);
header('Location:'.$url);
exit();
?>
