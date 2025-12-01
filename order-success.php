<?php
require_once 'config/config.php';

$page_title = 'Đặt hàng thành công';

// Kiểm tra có mã đơn hàng không
$order_code = isset($_GET['order']) ? $_GET['order'] : (isset($_SESSION['order_success']) ? $_SESSION['order_success'] : null);

if (!$order_code) {
    redirect(SITE_URL . '/');
}

// Lấy thông tin đơn hàng
$sql = "SELECT * FROM don_hang WHERE ma_don_hang = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $order_code);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    redirect(SITE_URL . '/');
}

// Xóa session order_success
if (isset($_SESSION['order_success'])) {
    unset($_SESSION['order_success']);
}

include 'includes/header.php';
?>

<style>
.success-page {
    padding: 60px 0;
    text-align: center;
}
.success-icon {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 30px;
    animation: scaleIn 0.5s ease;
}
.success-icon i {
    font-size: 60px;
    color: white;
}
@keyframes scaleIn {
    from {
        transform: scale(0);
    }
    to {
        transform: scale(1);
    }
}
.success-content {
    max-width: 600px;
    margin: 0 auto;
}
.order-info-box {
    background: var(--secondary-color);
    border-radius: 12px;
    padding: 30px;
    margin: 30px 0;
    text-align: left;
}
.order-info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
}
.order-info-row:last-child {
    border-bottom: none;
}
.order-info-row strong {
    color: var(--text-dark);
}
.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}
</style>

<section class="success-page">
    <div class="container">
        <div class="success-content">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 style="font-size: 32px; margin-bottom: 15px; color: var(--text-dark);">
                Đặt hàng thành công!
            </h1>
            
            <p style="font-size: 16px; color: var(--text-light); margin-bottom: 20px;">
                Cảm ơn bạn đã tin tưởng và đặt hàng tại PhoneShop. 
                Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất.
            </p>

            <div class="order-info-box">
                <h3 style="margin-bottom: 20px; color: var(--primary-color);">
                    <i class="fas fa-info-circle"></i> Thông tin đơn hàng
                </h3>
                
                <div class="order-info-row">
                    <span>Mã đơn hàng:</span>
                    <strong style="color: var(--primary-color);"><?php echo htmlspecialchars($order['ma_don_hang']); ?></strong>
                </div>
                
                <div class="order-info-row">
                    <span>Người nhận:</span>
                    <strong><?php echo htmlspecialchars($order['ho_ten']); ?></strong>
                </div>
                
                <div class="order-info-row">
                    <span>Số điện thoại:</span>
                    <strong><?php echo htmlspecialchars($order['dien_thoai']); ?></strong>
                </div>
                
                <div class="order-info-row">
                    <span>Địa chỉ giao hàng:</span>
                    <strong><?php echo htmlspecialchars($order['dia_chi']); ?></strong>
                </div>
                
                <div class="order-info-row">
                    <span>Tổng tiền:</span>
                    <strong style="color: var(--primary-color); font-size: 20px;">
                        <?php echo formatPrice($order['tong_thanh_toan']); ?>
                    </strong>
                </div>
                
                <div class="order-info-row">
                    <span>Phương thức thanh toán:</span>
                    <strong>
                        <?php 
                        $payment_methods = [
                            'cod' => 'Thanh toán khi nhận hàng (COD)',
                            'momo' => 'Ví MoMo',
                            'vnpay' => 'VNPay',
                            'bank' => 'Chuyển khoản ngân hàng'
                        ];
                        echo $payment_methods[$order['phuong_thuc_thanh_toan']] ?? 'COD';
                        ?>
                    </strong>
                </div>
                
                <div class="order-info-row">
                    <span>Trạng thái:</span>
                    <strong style="color: var(--warning-color);">Chờ xác nhận</strong>
                </div>
            </div>

            <div style="background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; text-align: left; margin-bottom: 30px;">
                <h4 style="margin-bottom: 10px; color: #856404;">
                    <i class="fas fa-bell"></i> Lưu ý
                </h4>
                <ul style="margin: 0; padding-left: 20px; color: #856404;">
                    <li>Đơn hàng của bạn đang được xử lý</li>
                    <li>Chúng tôi sẽ liên hệ để xác nhận đơn hàng trong vòng 24h</li>
                    <li>Thời gian giao hàng dự kiến: 2-3 ngày làm việc</li>
                    <li>Vui lòng giữ máy để nhận cuộc gọi xác nhận</li>
                </ul>
            </div>

            <div class="action-buttons">
                <a href="<?php echo SITE_URL; ?>/" class="btn btn-secondary btn-large">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>
                <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary btn-large">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua hàng
                </a>
                <?php if (isLoggedIn()): ?>
                <a href="<?php echo SITE_URL; ?>/account.php" class="btn btn-outline btn-large">
                    <i class="fas fa-receipt"></i> Xem đơn hàng
                </a>
                <?php endif; ?>
            </div>

            <div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--border-color);">
                <p style="color: var(--text-light); margin-bottom: 10px;">
                    <strong>Cần hỗ trợ?</strong>
                </p>
                <p style="color: var(--text-light);">
                    Liên hệ hotline: <a href="tel:0962371176" style="color: var(--primary-color); font-weight: 600;">0962371176</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
