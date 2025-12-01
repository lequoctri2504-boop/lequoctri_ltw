<?php
require_once 'config/config.php';

$page_title = 'Thanh toán';

// Lấy giỏ hàng
$cart_items = [];
$subtotal = 0;

if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT ghct.*, sp.ten_sp, sp.gia, sp.gia_khuyen_mai, sp.hinh_anh, sp.ton_kho,
            (COALESCE(sp.gia_khuyen_mai, sp.gia) * ghct.so_luong) as thanh_tien
            FROM gio_hang_chi_tiet ghct
            INNER JOIN gio_hang gh ON ghct.id_gio_hang = gh.id
            INNER JOIN san_pham sp ON ghct.id_san_pham = sp.id
            WHERE gh.id_nguoi_dung = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $subtotal += $row['thanh_tien'];
    }
} else {
    $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    foreach ($cart_items as $item) {
        $subtotal += $item['gia'] * $item['quantity'];
    }
}

// Redirect nếu giỏ hàng trống
if (count($cart_items) == 0) {
    redirect(SITE_URL . '/cart.php');
}

$shipping = 0; // Miễn phí ship
$total = $subtotal + $shipping;

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $ho_ten = trim($_POST['ho_ten']);
    $dien_thoai = trim($_POST['dien_thoai']);
    $email = trim($_POST['email']);
    $tinh_thanh = trim($_POST['tinh_thanh']);
    $quan_huyen = trim($_POST['quan_huyen']);
    $phuong_xa = trim($_POST['phuong_xa']);
    $dia_chi_cu_the = trim($_POST['dia_chi_cu_the']);
    $ghi_chu = trim($_POST['ghi_chu']);
    $phuong_thuc_thanh_toan = $_POST['payment_method'];
    
    // Tạo địa chỉ đầy đủ
    $dia_chi = $dia_chi_cu_the . ', ' . $phuong_xa . ', ' . $quan_huyen . ', ' . $tinh_thanh;
    
    // Validate
    $errors = [];
    if (empty($ho_ten)) $errors[] = 'Vui lòng nhập họ tên';
    if (empty($dien_thoai)) $errors[] = 'Vui lòng nhập số điện thoại';
    if (empty($tinh_thanh) || empty($quan_huyen) || empty($phuong_xa)) {
        $errors[] = 'Vui lòng chọn đầy đủ địa chỉ';
    }
    if (empty($dia_chi_cu_the)) $errors[] = 'Vui lòng nhập địa chỉ cụ thể';
    
    if (count($errors) == 0) {
        // Bắt đầu transaction
        $conn->begin_transaction();
        
        try {
            // Tạo mã đơn hàng
            $ma_don_hang = 'DH' . time();
            
            // Tạo đơn hàng
            $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
            $trang_thai = 'cho_xac_nhan';
            $trang_thai_thanh_toan = ($phuong_thuc_thanh_toan == 'cod') ? 'chua_thanh_toan' : 'chua_thanh_toan';
            
            $sql = "INSERT INTO don_hang (
                        ma_don_hang, id_nguoi_dung, ho_ten, email, dien_thoai, 
                        dia_chi, ghi_chu, tong_san_pham, tong_tien, phi_ship, 
                        tong_thanh_toan, phuong_thuc_thanh_toan, trang_thai, 
                        trang_thai_thanh_toan, ngay_dat
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $total_items = array_sum(array_column($cart_items, isLoggedIn() ? 'so_luong' : 'quantity'));
            
            $stmt->bind_param(
                "sisssssiiddsss",
                $ma_don_hang,
                $user_id,
                $ho_ten,
                $email,
                $dien_thoai,
                $dia_chi,
                $ghi_chu,
                $total_items,
                $subtotal,
                $shipping,
                $total,
                $phuong_thuc_thanh_toan,
                $trang_thai,
                $trang_thai_thanh_toan
            );
            
            $stmt->execute();
            $order_id = $conn->insert_id;
            
            // Thêm chi tiết đơn hàng
            foreach ($cart_items as $item) {
                if (isLoggedIn()) {
                    $product_id = $item['id_san_pham'];
                    $quantity = $item['so_luong'];
                    $price = $item['gia_khuyen_mai'] ?? $item['gia'];
                } else {
                    $product_id = $item['id_san_pham'];
                    $quantity = $item['quantity'];
                    $price = $item['gia'];
                }
                
                $sql = "INSERT INTO don_hang_chi_tiet (id_don_hang, id_san_pham, gia, so_luong) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iidi", $order_id, $product_id, $price, $quantity);
                $stmt->execute();
                
                // Trừ tồn kho
                $sql = "UPDATE san_pham SET ton_kho = ton_kho - ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $quantity, $product_id);
                $stmt->execute();
            }
            
            // Xóa giỏ hàng
            if (isLoggedIn()) {
                $sql = "DELETE ghct FROM gio_hang_chi_tiet ghct
                        INNER JOIN gio_hang gh ON ghct.id_gio_hang = gh.id
                        WHERE gh.id_nguoi_dung = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            } else {
                unset($_SESSION['cart']);
            }
            
            $conn->commit();
            
            // Redirect theo phương thức thanh toán
            if ($phuong_thuc_thanh_toan == 'vnpay') {
                redirect(SITE_URL . '/payment/vnpay-create.php?order_id=' . $order_id);
            } else {
                $_SESSION['order_success'] = $ma_don_hang;
                redirect(SITE_URL . '/order-success.php?order=' . $ma_don_hang);
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}

// Lấy thông tin user nếu đã đăng nhập
$user = isLoggedIn() ? getCurrentUser() : null;

include 'includes/header.php';
?>

<header class="header checkout-header">
    <div class="container">
        <div class="checkout-logo">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>/">
                    <span><img src="<?php echo SITE_URL; ?>/assets/images/logo_LQT1.png" alt="" width="70px"></span>
                </a>
            </div>
        </div>
        <div class="checkout-steps">
            <div class="step active">
                <span class="step-number">1</span>
                <span class="step-text">Giỏ hàng</span>
            </div>
            <div class="step active">
                <span class="step-number">2</span>
                <span class="step-text">Thanh toán</span>
            </div>
            <div class="step">
                <span class="step-number">3</span>
                <span class="step-text">Hoàn thành</span>
            </div>
        </div>
    </div>
</header>

<section class="checkout-page">
    <div class="container">
        <?php if (isset($errors) && count($errors) > 0): ?>
        <div class="alert" style="background:#fee;color:#c33;padding:15px;border-radius:8px;margin-bottom:20px;">
            <?php foreach ($errors as $error): ?>
                <p><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="checkout-layout">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <div class="form-section">
                    <h2><i class="fas fa-user"></i> Thông tin khách hàng</h2>
                    
                    <div class="form-group">
                        <label>Họ và tên <span class="required">*</span></label>
                        <input type="text" name="ho_ten" class="form-control" 
                               value="<?php echo $user ? htmlspecialchars($user['ho_ten']) : ''; ?>" 
                               placeholder="Nhập họ và tên" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Số điện thoại <span class="required">*</span></label>
                            <input type="tel" name="dien_thoai" class="form-control" 
                                   value="<?php echo $user ? htmlspecialchars($user['dien_thoai'] ?? '') : ''; ?>"
                                   placeholder="0xxx xxx xxx" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>"
                                   placeholder="email@example.com">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2><i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng</h2>
                    
                    <div class="form-group">
                        <label>Tỉnh/Thành phố <span class="required">*</span></label>
                        <select name="tinh_thanh" class="form-control" required>
                            <option value="">Chọn Tỉnh/Thành phố</option>
                            <option value="Hà Nội">Hà Nội</option>
                            <option value="TP. Hồ Chí Minh">TP. Hồ Chí Minh</option>
                            <option value="Đà Nẵng">Đà Nẵng</option>
                            <option value="Hải Phòng">Hải Phòng</option>
                            <option value="Cần Thơ">Cần Thơ</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Quận/Huyện <span class="required">*</span></label>
                            <input type="text" name="quan_huyen" class="form-control" 
                                   placeholder="Nhập Quận/Huyện" required>
                        </div>
                        <div class="form-group">
                            <label>Phường/Xã <span class="required">*</span></label>
                            <input type="text" name="phuong_xa" class="form-control" 
                                   placeholder="Nhập Phường/Xã" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Địa chỉ cụ thể <span class="required">*</span></label>
                        <input type="text" name="dia_chi_cu_the" class="form-control" 
                               placeholder="Số nhà, tên đường..." required>
                    </div>
                    
                    <div class="form-group">
                        <label>Ghi chú đơn hàng</label>
                        <textarea name="ghi_chu" class="form-control" rows="3" 
                                  placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h2><i class="fas fa-credit-card"></i> Phương thức thanh toán</h2>
                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cod" checked>
                            <div class="payment-content">
                                <div class="payment-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="payment-info">
                                    <strong>Thanh toán khi nhận hàng (COD)</strong>
                                    <p>Thanh toán bằng tiền mặt khi nhận hàng</p>
                                </div>
                            </div>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="momo">
                            <div class="payment-content">
                                <div class="payment-icon">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div class="payment-info">
                                    <strong>Ví MoMo</strong>
                                    <p>Thanh toán qua ví điện tử MoMo</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-section">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" required>
                        <span>Tôi đã đọc và đồng ý với <a href="#">Điều khoản sử dụng</a> và <a href="#">Chính sách bảo mật</a></span>
                    </label>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="cart-summary">
                    <h3>Đơn hàng của bạn</h3>
                    
                    <div class="order-items">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo SITE_URL; ?>/uploads/<?php echo isLoggedIn() ? $item['hinh_anh'] : $item['hinh_anh']; ?>" 
                                 alt="<?php echo isLoggedIn() ? htmlspecialchars($item['ten_sp']) : htmlspecialchars($item['ten_sp']); ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1592286927505-128d151c5874?w=80&h=80&fit=crop'">
                            <div class="order-item-info">
                                <h4><?php echo isLoggedIn() ? htmlspecialchars($item['ten_sp']) : htmlspecialchars($item['ten_sp']); ?></h4>
                                <p>Số lượng: <?php echo isLoggedIn() ? $item['so_luong'] : $item['quantity']; ?></p>
                            </div>
                            <div class="order-item-price">
                                <?php echo formatPrice(isLoggedIn() ? $item['thanh_tien'] : ($item['gia'] * $item['quantity'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-totals">
                        <div class="total-row">
                            <span>Tạm tính:</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="total-row">
                            <span>Phí vận chuyển:</span>
                            <span class="text-success">Miễn phí</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Tổng cộng:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>

                    <button type="submit" name="place_order" class="btn btn-primary btn-block btn-large">
                        <i class="fas fa-check-circle"></i> Hoàn tất đặt hàng
                    </button>

                    <div class="security-badges">
                        <div class="badge-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Thanh toán an toàn</span>
                        </div>
                        <div class="badge-item">
                            <i class="fas fa-lock"></i>
                            <span>Bảo mật thông tin</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
