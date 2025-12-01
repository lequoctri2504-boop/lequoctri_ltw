<?php
// includes/functions.php
require_once __DIR__ . '/../config/database.php';

function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . '₫';
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'quantri';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

// Lấy tất cả sản phẩm
function getAllProducts($limit = null) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT sp.*, dm.ten_danh_muc 
            FROM san_pham sp 
            LEFT JOIN danh_muc dm ON sp.id_danh_muc = dm.id 
            WHERE sp.ton_kho > 0";
    
    if ($limit) {
        $sql .= " LIMIT :limit";
    }
    
    $stmt = $conn->prepare($sql);
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

// Lấy sản phẩm theo ID
function getProductById($id) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT sp.*, dm.ten_danh_muc 
            FROM san_pham sp 
            LEFT JOIN danh_muc dm ON sp.id_danh_muc = dm.id 
            WHERE sp.id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch();
}

// Thêm sản phẩm vào giỏ hàng
function addToCart($userId, $productId, $quantity = 1) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $conn->beginTransaction();
        
        // Kiểm tra giỏ hàng của user
        $sql = "SELECT id FROM gio_hang WHERE id_nguoi_dung = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $cart = $stmt->fetch();
        
        if (!$cart) {
            // Tạo giỏ hàng mới
            $sql = "INSERT INTO gio_hang (id_nguoi_dung) VALUES (:user_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $cartId = $conn->lastInsertId();
        } else {
            $cartId = $cart['id'];
        }
        
        // Kiểm tra sản phẩm đã có trong giỏ chưa
        $sql = "SELECT id, so_luong FROM gio_hang_chi_tiet 
                WHERE id_gio_hang = :cart_id AND id_san_pham = :product_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        $item = $stmt->fetch();
        
        if ($item) {
            // Cập nhật số lượng
            $newQty = $item['so_luong'] + $quantity;
            $sql = "UPDATE gio_hang_chi_tiet SET so_luong = :qty WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':qty', $newQty);
            $stmt->bindParam(':id', $item['id']);
            $stmt->execute();
        } else {
            // Thêm mới
            $sql = "INSERT INTO gio_hang_chi_tiet (id_gio_hang, id_san_pham, so_luong) 
                    VALUES (:cart_id, :product_id, :qty)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':cart_id', $cartId);
            $stmt->bindParam(':product_id', $productId);
            $stmt->bindParam(':qty', $quantity);
            $stmt->execute();
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        return false;
    }
}

// Lấy giỏ hàng của user
function getCartItems($userId) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT ghct.*, sp.ten_sp, sp.gia, sp.gia_khuyen_mai, sp.hinh_anh,
            (COALESCE(sp.gia_khuyen_mai, sp.gia) * ghct.so_luong) as thanh_tien
            FROM gio_hang_chi_tiet ghct
            INNER JOIN gio_hang gh ON ghct.id_gio_hang = gh.id
            INNER JOIN san_pham sp ON ghct.id_san_pham = sp.id
            WHERE gh.id_nguoi_dung = :user_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Tính tổng giỏ hàng
function getCartTotal($userId) {
    $items = getCartItems($userId);
    $total = 0;
    foreach ($items as $item) {
        $total += $item['thanh_tien'];
    }
    return $total;
}

// Tạo đơn hàng
function createOrder($userId, $shippingInfo) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $conn->beginTransaction();
        
        // Lấy giỏ hàng
        $cartItems = getCartItems($userId);
        if (empty($cartItems)) {
            throw new Exception("Giỏ hàng trống");
        }
        
        $total = getCartTotal($userId);
        
        // Tạo đơn hàng
        $sql = "INSERT INTO don_hang (id_nguoi_dung, tong_tien, trang_thai) 
                VALUES (:user_id, :total, 'choxuly')";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':total', $total);
        $stmt->execute();
        $orderId = $conn->lastInsertId();
        
        // Thêm chi tiết đơn hàng
        foreach ($cartItems as $item) {
            $price = $item['gia_khuyen_mai'] ?? $item['gia'];
            $sql = "INSERT INTO don_hang_chi_tiet (id_don_hang, id_san_pham, gia, so_luong) 
                    VALUES (:order_id, :product_id, :price, :qty)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindParam(':product_id', $item['id_san_pham']);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':qty', $item['so_luong']);
            $stmt->execute();
        }
        
        // Xóa giỏ hàng
        $sql = "DELETE ghct FROM gio_hang_chi_tiet ghct
                INNER JOIN gio_hang gh ON ghct.id_gio_hang = gh.id
                WHERE gh.id_nguoi_dung = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        $conn->commit();
        return $orderId;
    } catch (Exception $e) {
        $conn->rollBack();
        return false;
    }
}
?>