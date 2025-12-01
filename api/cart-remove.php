<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
    exit();
}

$product_id = intval($input['product_id']);

// Nếu chưa đăng nhập
if (!isLoggedIn()) {
    if (!isset($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        exit();
    }
    
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id_san_pham'] == $product_id) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã xóa sản phẩm',
                'cart_count' => isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0
            ]);
            exit();
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không có trong giỏ']);
    exit();
}

// Nếu đã đăng nhập
$user_id = $_SESSION['user_id'];

// Tìm giỏ hàng
$sql = "SELECT id FROM gio_hang WHERE id_nguoi_dung = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

if (!$cart) {
    echo json_encode(['success' => false, 'message' => 'Giỏ hàng không tồn tại']);
    exit();
}

$cart_id = $cart['id'];

// Xóa sản phẩm khỏi giỏ
$sql = "DELETE FROM gio_hang_chi_tiet WHERE id_gio_hang = ? AND id_san_pham = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cart_id, $product_id);

if ($stmt->execute()) {
    // Đếm tổng số sản phẩm còn lại
    $sql = "SELECT SUM(so_luong) as total FROM gio_hang_chi_tiet WHERE id_gio_hang = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã xóa sản phẩm',
        'cart_count' => $total['total'] ?? 0
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
