<?php
/**
 * File config cập nhật cho dự án PhoneShop
 * Chép nội dung file này và thêm vào config/config.php hiện tại
 */

// ============= VNPAY CONFIG (Thêm vào config.php) =============
// Đăng ký tài khoản tại: https://sandbox.vnpayment.vn/devreg/
// Sau khi đăng ký, lấy TMN Code và Hash Secret từ dashboard

define('VNPAY_TMN_CODE', 'YOUR_TMN_CODE_HERE');      // VD: 'ABCD1234'
define('VNPAY_HASH_SECRET', 'YOUR_HASH_SECRET_HERE'); // VD: 'ABCDEFGH1234567890'
define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
define('VNPAY_RETURN_URL', SITE_URL . '/payment/vnpay-return.php');

// ============= MOMO CONFIG (Optional - Thêm nếu dùng MoMo) =============
define('MOMO_PARTNER_CODE', 'YOUR_PARTNER_CODE');
define('MOMO_ACCESS_KEY', 'YOUR_ACCESS_KEY');
define('MOMO_SECRET_KEY', 'YOUR_SECRET_KEY');
define('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
define('MOMO_RETURN_URL', SITE_URL . '/payment/momo-return.php');
define('MOMO_NOTIFY_URL', SITE_URL . '/payment/momo-notify.php');

// ============= HELPER FUNCTIONS (Thêm vào cuối file config.php) =============

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Log hoạt động (optional)
 */
function logActivity($action, $description = '', $user_id = null) {
    global $conn;
    
    if ($user_id === null && isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
    }
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $sql = "INSERT INTO hoat_dong (id_nguoi_dung, hanh_dong, mo_ta, ip_address, user_agent, ngay_tao) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $user_id, $action, $description, $ip, $user_agent);
    $stmt->execute();
}

/**
 * Send email (cần cấu hình SMTP)
 */
function sendEmail($to, $subject, $body) {
    // Cấu hình PHPMailer hoặc mail() function
    // Ví dụ đơn giản:
    $headers = "From: " . SITE_NAME . " <noreply@phoneshop.com>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $body, $headers);
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Check admin role
 */
function requireAdmin() {
    if (!isAdmin()) {
        redirect(SITE_URL . '/');
        exit();
    }
}

/**
 * Upload file
 */
function uploadFile($file, $upload_dir = 'uploads/') {
    $target_dir = __DIR__ . '/../' . $upload_dir;
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return ['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (jpg, jpeg, png, gif, webp)'];
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File không được vượt quá 5MB'];
    }
    
    $new_filename = time() . '_' . generateRandomString(8) . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'filename' => $new_filename];
    }
    
    return ['success' => false, 'message' => 'Có lỗi khi upload file'];
}

/**
 * Delete file
 */
function deleteFile($filename, $upload_dir = 'uploads/') {
    $file_path = __DIR__ . '/../' . $upload_dir . $filename;
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return false;
}

/**
 * Debug function (only for development)
 */
function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * JSON response helper
 */
function jsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Pagination helper
 */
function paginate($total_items, $items_per_page = 12, $current_page = 1) {
    $total_pages = ceil($total_items / $items_per_page);
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'items_per_page' => $items_per_page,
        'offset' => $offset,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
}

// ============= SECURITY FUNCTIONS =============

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting (simple implementation)
 */
function checkRateLimit($action, $max_attempts = 5, $time_window = 60) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = "rate_limit_{$action}_{$ip}";
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
    }
    
    $data = $_SESSION[$key];
    
    // Reset if time window passed
    if (time() - $data['first_attempt'] > $time_window) {
        $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    // Check limit
    if ($data['count'] >= $max_attempts) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}

// ============= END OF CONFIG ADDITIONS =============
?>
