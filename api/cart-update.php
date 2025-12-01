<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id']) || !isset($input['change'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
    exit();
}

$product_id = intval($input['product_id']);
$change = intval($input['change']); // +1 hoặc -1

// Nếu chưa đăng nhập
if (!isLoggedIn()) {
    if (!isset($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
        exit();
    }
    
    foreach ($_SESSION['cart'] as $key => &$item) {
        if ($item['id_san_pham'] == $product_id) {
            $item['quantity'] += $change;
            
            // Xóa nếu số lượng <= 0
            if ($item['quantity'] <= 0) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index
            }
            
            echo json_encode([
                'success' => true,
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

// Lấy thông tin sản phẩm trong giỏ
$sql = "SELECT id, so_luong FROM gio_hang_chi_tiet WHERE id_gio_hang = ? AND id_san_pham = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cart_id, $product_id);
$stmt->execute();
$cart_item = $stmt->get_result()->fetch_assoc();

if (!$cart_item) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không có trong giỏ']);
    exit();
}

$new_quantity = $cart_item['so_luong'] + $change;

if ($new_quantity <= 0) {
    // Xóa sản phẩm khỏi giỏ
    $sql = "DELETE FROM gio_hang_chi_tiet WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_item['id']);
    $stmt->execute();
} else {
    // Kiểm tra tồn kho
    $sql = "SELECT ton_kho FROM san_pham WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if ($new_quantity > $product['ton_kho']) {
        echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho']);
        exit();
    }
    
    // Cập nhật số lượng
    $sql = "UPDATE gio_hang_chi_tiet SET so_luong = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
    $stmt->execute();
}

// Đếm tổng số sản phẩm
$sql = "SELECT SUM(so_luong) as total FROM gio_hang_chi_tiet WHERE id_gio_hang = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'success' => true,
    'cart_count' => $total['total'] ?? 0
]);
?>
