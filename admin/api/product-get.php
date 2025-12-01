<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if(!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM san_pham WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    echo json_encode(['success' => true, 'product' => $product]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
}
?>
