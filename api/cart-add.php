<?php
require_once '../config/config.php';

header('Content-Type: application/json');

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Lấy dữ liệu JSON từ request body
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
    exit();
}

$product_id = intval($input['product_id']);
$quantity = intval($input['quantity']);

// Validate
if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit();
}

// Kiểm tra sản phẩm có tồn tại
$sql = "SELECT * FROM san_pham WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit();
}

// Kiểm tra tồn kho
if ($product['ton_kho'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không đủ hàng']);
    exit();
}

// Nếu chưa đăng nhập, lưu vào session
if (!isLoggedIn()) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id_san_pham'] == $product_id) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['cart'][] = [
            'id_san_pham' => $product_id,
            'ten_sp' => $product['ten_sp'],
            'gia' => $product['gia_khuyen_mai'] ?? $product['gia'],
            'hinh_anh' => $product['hinh_anh'],
            'quantity' => $quantity
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã thêm vào giỏ hàng',
        'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
    ]);
    exit();
}

// Nếu đã đăng nhập, lưu vào database
$user_id = $_SESSION['user_id'];

// Tìm hoặc tạo giỏ hàng
$sql = "SELECT id FROM gio_hang WHERE id_nguoi_dung = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();

if (!$cart) {
    // Tạo giỏ hàng mới
    $sql = "INSERT INTO gio_hang (id_nguoi_dung, ngay_tao) VALUES (?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_id = $conn->insert_id;
} else {
    $cart_id = $cart['id'];
}

// Kiểm tra sản phẩm đã có trong giỏ chưa
$sql = "SELECT id, so_luong FROM gio_hang_chi_tiet WHERE id_gio_hang = ? AND id_san_pham = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $cart_id, $product_id);
$stmt->execute();
$cart_item = $stmt->get_result()->fetch_assoc();

if ($cart_item) {
    // Cập nhật số lượng
    $new_quantity = $cart_item['so_luong'] + $quantity;
    
    // Kiểm tra tồn kho
    if ($new_quantity > $product['ton_kho']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Không đủ hàng trong kho'
        ]);
        exit();
    }
    
    $sql = "UPDATE gio_hang_chi_tiet SET so_luong = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
    $stmt->execute();
} else {
    // Thêm mới
    $sql = "INSERT INTO gio_hang_chi_tiet (id_gio_hang, id_san_pham, so_luong) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $cart_id, $product_id, $quantity);
    $stmt->execute();
}

// Đếm tổng số sản phẩm trong giỏ
$sql = "SELECT SUM(so_luong) as total FROM gio_hang_chi_tiet WHERE id_gio_hang = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'success' => true,
    'message' => 'Đã thêm vào giỏ hàng',
    'cart_count' => $total['total'] ?? 0
]);
?>
