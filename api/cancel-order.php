<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đơn hàng']);
    exit();
}

$order_id = intval($input['order_id']);
$user_id = $_SESSION['user_id'];

// Kiểm tra đơn hàng có tồn tại và thuộc về user không
$sql = "SELECT * FROM don_hang WHERE id = ? AND id_nguoi_dung = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại']);
    exit();
}

// Chỉ cho phép hủy đơn hàng đang chờ xác nhận
if ($order['trang_thai'] != 'cho_xac_nhan') {
    echo json_encode([
        'success' => false, 
        'message' => 'Không thể hủy đơn hàng ở trạng thái này'
    ]);
    exit();
}

// Cập nhật trạng thái đơn hàng
$sql = "UPDATE don_hang SET trang_thai = 'da_huy' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    // Hoàn trả số lượng sản phẩm vào kho
    $sql = "SELECT id_san_pham, so_luong FROM don_hang_chi_tiet WHERE id_don_hang = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result();
    
    while ($item = $items->fetch_assoc()) {
        $sql_update = "UPDATE san_pham SET ton_kho = ton_kho + ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $item['so_luong'], $item['id_san_pham']);
        $stmt_update->execute();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã hủy đơn hàng thành công'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}
?>
