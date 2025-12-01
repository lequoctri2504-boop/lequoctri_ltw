<?php
// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ltw_dt');
define('DB_PORT', '3306');

// Cấu hình site
define('SITE_URL', 'http://localhost/lequoctri');
define('SITE_NAME', 'PhoneShop - Lê Quốc Trí');

// Session
session_start();

// Facebook OAuth
define('FB_APP_ID', '4105163333068967');
define('FB_APP_SECRET', 'd8e61851d208c6e1081cc1c29111d149');
define('FB_REDIRECT_URI', SITE_URL . '/oauth/facebook-callback.php');

// Google OAuth
define('GOOGLE_CLIENT_ID', '279302206006-1tgbm2s187ddutcnsi87sudmbkav2qeg.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', SITE_URL . '/oauth/google-callback.php');

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kết nối database
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $conn->set_charset("utf8mb4");
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Lỗi kết nối: " . $e->getMessage());
}

// Helper functions
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] == 'quantri';
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    global $conn;
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM nguoi_dung WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function formatPrice($price) {
    return number_format($price, 0, ',', '.') . '₫';
}

function escape($str) {
    global $conn;
    return $conn->real_escape_string($str);
}
?>
