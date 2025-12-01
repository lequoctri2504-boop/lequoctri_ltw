<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if(!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$order_id = intval($_POST['order_id']);
$status = trim($_POST['status']);

$allowed_status = ['cho_xac_nhan', 'da_xac_nhan', 'dang_giao', 'hoan_thanh', 'da_huy'];

if(!in_array($status, $allowed_status)) {
    echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
    exit;
}

$sql = "UPDATE don_hang SET trang_thai = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $order_id);

if($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $conn->error]);
}
?>
