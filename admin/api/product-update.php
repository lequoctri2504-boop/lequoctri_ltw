<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if(!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$id = intval($_POST['id']);
$ten_sp = trim($_POST['ten_sp']);
$gia = floatval($_POST['gia']);
$gia_khuyen_mai = floatval($_POST['gia_khuyen_mai']);
$id_danh_muc = intval($_POST['id_danh_muc']);
$ton_kho = intval($_POST['ton_kho']);
$mo_ta = trim($_POST['mo_ta']);

// Lấy ảnh cũ
$sql = "SELECT hinh_anh FROM san_pham WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$old_image = $stmt->get_result()->fetch_assoc()['hinh_anh'];

$hinh_anh = $old_image;

// Nếu có upload ảnh mới
if(isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $filename = $_FILES['hinh_anh']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if(in_array($ext, $allowed)) {
        $new_filename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $upload_path = '../../uploads/' . $new_filename;
        
        if(move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $upload_path)) {
            // Xóa ảnh cũ
            if($old_image && file_exists('../../uploads/' . $old_image)) {
                unlink('../../uploads/' . $old_image);
            }
            $hinh_anh = $new_filename;
        }
    }
}

// Update database
$sql = "UPDATE san_pham SET ten_sp=?, gia=?, gia_khuyen_mai=?, id_danh_muc=?, ton_kho=?, mo_ta=?, hinh_anh=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sddissi", $ten_sp, $gia, $gia_khuyen_mai, $id_danh_muc, $ton_kho, $mo_ta, $hinh_anh, $id);

if($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $conn->error]);
}
?>
