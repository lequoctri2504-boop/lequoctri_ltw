<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$count = 0;

if (isLoggedIn()) {
    // Lấy từ database
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT SUM(ghct.so_luong) as total
            FROM gio_hang_chi_tiet ghct
            INNER JOIN gio_hang gh ON ghct.id_gio_hang = gh.id
            WHERE gh.id_nguoi_dung = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    $count = $result['total'] ?? 0;
} else {
    // Lấy từ session
    if (isset($_SESSION['cart'])) {
        $count = array_sum(array_column($_SESSION['cart'], 'quantity'));
    }
}

echo json_encode([
    'success' => true,
    'count' => (int)$count
]);
?>
