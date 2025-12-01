<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if(!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$ten_sp = trim($_POST['ten_sp']);
$gia = floatval($_POST['gia']);
$gia_khuyen_mai = floatval($_POST['gia_khuyen_mai']);
$id_danh_muc = intval($_POST['id_danh_muc']);
$ton_kho = intval($_POST['ton_kho']);
$mo_ta = trim($_POST['mo_ta']);

// Validate
if(empty($ten_sp) || $gia <= 0 || $gia_khuyen_mai <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
    exit;
}

// Upload ảnh
$hinh_anh = '';
if(isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $filename = $_FILES['hinh_anh']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if(!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Định dạng ảnh không hợp lệ']);
        exit;
    }
    
    $new_filename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    $upload_path = '../../uploads/' . $new_filename;
    
    if(move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $upload_path)) {
        $hinh_anh = $new_filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể upload ảnh']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ảnh sản phẩm']);
    exit;
}

// Insert database
$sql = "INSERT INTO san_pham (id_danh_muc, ten_sp, gia, gia_khuyen_mai, ton_kho, mo_ta, hinh_anh) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isddiis", $id_danh_muc, $ten_sp, $gia, $gia_khuyen_mai, $ton_kho, $mo_ta, $hinh_anh);

if($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm thành công', 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $conn->error]);
}
?>
