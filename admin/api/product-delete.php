<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if(!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$id = intval($_POST['id']);

// Lấy thông tin ảnh
$sql = "SELECT hinh_anh FROM san_pham WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    // Xóa khỏi database
    $sql_delete = "DELETE FROM san_pham WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id);
    
    if($stmt_delete->execute()) {
        // Xóa ảnh
        if($product['hinh_anh'] && file_exists('../../uploads/' . $product['hinh_anh'])) {
            unlink('../../uploads/' . $product['hinh_anh']);
        }
        echo json_encode(['success' => true, 'message' => 'Xóa thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
}
?>
