<?php
require_once '../config/config.php';

// Kiểm tra state để chống CSRF
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die('State mismatch. Possible CSRF attack.');
}

// Kiểm tra có code không
if (!isset($_GET['code'])) {
    redirect(SITE_URL . '/login.php?error=oauth_failed');
}

$code = $_GET['code'];

// Exchange code for access token
$token_url = 'https://graph.facebook.com/v18.0/oauth/access_token';
$params = [
    'client_id' => FB_APP_ID,
    'client_secret' => FB_APP_SECRET,
    'redirect_uri' => FB_REDIRECT_URI,
    'code' => $code
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url . '?' . http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Cho localhost
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    redirect(SITE_URL . '/login.php?error=token_failed');
}

$access_token = $token_data['access_token'];

// Lấy thông tin user từ Facebook
$user_url = 'https://graph.facebook.com/v18.0/me?fields=id,name,email&access_token=' . $access_token;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $user_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$user_response = curl_exec($ch);
curl_close($ch);

$user_data = json_decode($user_response, true);

if (!isset($user_data['id'])) {
    redirect(SITE_URL . '/login.php?error=user_data_failed');
}

$facebook_id = $user_data['id'];
$name = $user_data['name'] ?? 'Facebook User';
$email = $user_data['email'] ?? $facebook_id . '@facebook.com';

// Kiểm tra user đã tồn tại chưa
$sql = "SELECT * FROM nguoi_dung WHERE provider = 'facebook' AND provider_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $facebook_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user) {
    // User đã tồn tại, đăng nhập
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['ho_ten'] = $user['ho_ten'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['vai_tro'] = $user['vai_tro'];
} else {
    // Tạo user mới
    $sql = "INSERT INTO nguoi_dung (ho_ten, email, provider, provider_id, vai_tro, ngay_tao) 
            VALUES (?, ?, 'facebook', ?, 'khachhang', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $facebook_id);
    
    if ($stmt->execute()) {
        $new_user_id = $conn->insert_id;
        
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['ho_ten'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['vai_tro'] = 'khachhang';
    } else {
        redirect(SITE_URL . '/login.php?error=register_failed');
    }
}

// Xóa oauth_state
unset($_SESSION['oauth_state']);

// Redirect về trang chủ
redirect(SITE_URL . '/');
?>
