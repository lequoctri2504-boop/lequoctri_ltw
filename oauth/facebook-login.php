<?php
require_once '../config/config.php';
$facebook_auth_url='https://www.facebook.com/v18.0/dialog/oauth';
$params=['client_id'=>FB_APP_ID,'redirect_uri'=>FB_REDIRECT_URI,'scope'=>'email,public_profile','response_type'=>'code','state'=>bin2hex(random_bytes(16))];
$_SESSION['oauth_state']=$params['state'];
$url=$facebook_auth_url.'?'.http_build_query($params);
header('Location:'.$url);
exit();
?>
