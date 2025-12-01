<?php
require_once 'config/config.php';

if(!isLoggedIn()) {
    redirect(SITE_URL . '/login.php');
}

$page_title = 'Tài khoản của tôi';
$user = getCurrentUser();

// Get user orders
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM don_hang WHERE id_nguoi_dung = ? ORDER BY ngay_dat DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

// Update profile
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $ho_ten = sanitize($_POST['ho_ten']);
    $dien_thoai = sanitize($_POST['dien_thoai']);
    $dia_chi = sanitize($_POST['dia_chi']);
    
    $sql = "UPDATE nguoi_dung SET ho_ten = ?, dien_thoai = ?, dia_chi = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $ho_ten, $dien_thoai, $dia_chi, $user_id);
    
    if($stmt->execute()) {
        $_SESSION['ho_ten'] = $ho_ten;
        $success = "Cập nhật thành công!";
        $user = getCurrentUser();
    }
}

// Change password
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(md5($current_password) != $user['mat_khau']) {
        $error = "Mật khẩu hiện tại không đúng!";
    } elseif($new_password != $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } elseif(strlen($new_password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự!";
    } else {
        $password_hash = md5($new_password);
        $sql = "UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $user_id);
        
        if($stmt->execute()) {
            $success_password = "Đổi mật khẩu thành công!";
        }
    }
}

include 'includes/header.php';
?>

<style>
.account-page{padding:40px 0;background:#f8f8f8;}
.account-layout{display:grid;grid-template-columns:250px 1fr;gap:30px;}
.account-sidebar{background:white;border-radius:12px;padding:20px;height:fit-content;}
.account-menu{display:flex;flex-direction:column;gap:5px;}
.menu-item{padding:12px 15px;border-radius:8px;color:#333;display:flex;align-items:center;gap:10px;transition:all 0.3s;}
.menu-item:hover,.menu-item.active{background:var(--primary-color);color:white;}
.account-content{background:white;border-radius:12px;padding:30px;}
.section{display:none;}.section.active{display:block;}
.form-group{margin-bottom:20px;}
.form-group label{display:block;font-weight:600;margin-bottom:8px;}
.form-control{width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;}
.order-card{border:1px solid #eee;border-radius:12px;padding:20px;margin-bottom:20px;}
.order-header{display:flex;justify-content:space-between;padding-bottom:15px;border-bottom:1px solid #eee;margin-bottom:15px;}
.order-status{padding:5px 15px;border-radius:20px;font-size:13px;font-weight:600;}
.status-cho_xac_nhan{background:#fff3cd;color:#856404;}
.status-da_xac_nhan{background:#d1ecf1;color:#0c5460;}
.status-dang_giao{background:#cfe2ff;color:#084298;}
.status-hoan_thanh{background:#d1e7dd;color:#0f5132;}
.status-da_huy{background:#f8d7da;color:#842029;}
.alert{padding:15px;border-radius:8px;margin-bottom:20px;}
.alert-success{background:#d1e7dd;color:#0f5132;border:1px solid #badbcc;}
.alert-error{background:#f8d7da;color:#842029;border:1px solid #f5c2c7;}
</style>

<div class="breadcrumb">
<div class="container">
<a href="<?php echo SITE_URL;?>/">Trang chủ</a>
<i class="fas fa-chevron-right"></i>
<span>Tài khoản</span>
</div>
</div>

<section class="account-page">
<div class="container">
<div class="account-layout">

<!-- Sidebar -->
<div class="account-sidebar">
<div style="text-align:center;padding-bottom:20px;border-bottom:1px solid #eee;margin-bottom:20px;">
<i class="fas fa-user-circle" style="font-size:60px;color:var(--primary-color);"></i>
<h3 style="margin-top:10px;font-size:18px;"><?php echo htmlspecialchars($user['ho_ten']); ?></h3>
<p style="font-size:13px;color:#666;"><?php echo htmlspecialchars($user['email']); ?></p>
</div>
<div class="account-menu">
<a href="#" class="menu-item active" onclick="showSection('profile');return false;">
<i class="fas fa-user"></i> Thông tin cá nhân
</a>
<a href="#" class="menu-item" onclick="showSection('orders');return false;">
<i class="fas fa-box"></i> Đơn hàng của tôi
</a>
<a href="#" class="menu-item" onclick="showSection('password');return false;">
<i class="fas fa-lock"></i> Đổi mật khẩu
</a>
<a href="<?php echo SITE_URL;?>/logout.php" class="menu-item">
<i class="fas fa-sign-out-alt"></i> Đăng xuất
</a>
</div>
</div>

<!-- Content -->
<div class="account-content">

<!-- Profile Section -->
<div id="profile" class="section active">
<h2><i class="fas fa-user"></i> Thông tin cá nhân</h2>
<?php if(isset($success)): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" style="max-width:600px;margin-top:30px;">
<div class="form-group">
<label>Họ và tên *</label>
<input type="text" name="ho_ten" class="form-control" value="<?php echo htmlspecialchars($user['ho_ten']); ?>" required>
</div>
<div class="form-group">
<label>Email</label>
<input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background:#f5f5f5;">
<small style="color:#999;">Email không thể thay đổi</small>
</div>
<div class="form-group">
<label>Số điện thoại</label>
<input type="tel" name="dien_thoai" class="form-control" value="<?php echo htmlspecialchars($user['dien_thoai'] ?? ''); ?>">
</div>
<div class="form-group">
<label>Địa chỉ</label>
<textarea name="dia_chi" class="form-control" rows="3"><?php echo htmlspecialchars($user['dia_chi'] ?? ''); ?></textarea>
</div>
<button type="submit" name="update_profile" class="btn btn-primary">
<i class="fas fa-save"></i> Cập nhật thông tin
</button>
</form>
</div>

<!-- Orders Section -->
<div id="orders" class="section">
<h2><i class="fas fa-box"></i> Đơn hàng của tôi</h2>

<?php if($orders->num_rows > 0): ?>
<div style="margin-top:30px;">
<?php while($order = $orders->fetch_assoc()): ?>
<div class="order-card">
<div class="order-header">
<div>
<strong style="font-size:16px;">Đơn hàng #<?php echo $order['ma_don_hang']; ?></strong>
<p style="color:#666;font-size:13px;margin-top:5px;">
<i class="far fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($order['ngay_dat'])); ?>
</p>
</div>
<span class="order-status status-<?php echo $order['trang_thai']; ?>">
<?php 
$status_text = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'da_xac_nhan' => 'Đã xác nhận',
    'dang_giao' => 'Đang giao',
    'hoan_thanh' => 'Hoàn thành',
    'da_huy' => 'Đã hủy'
];
echo $status_text[$order['trang_thai']];
?>
</span>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:15px;background:#f8f8f8;border-radius:8px;">
<div><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['ho_ten']); ?></div>
<div><strong>SĐT:</strong> <?php echo htmlspecialchars($order['dien_thoai']); ?></div>
<div style="grid-column:1/-1;"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['dia_chi']); ?></div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-top:15px;padding-top:15px;border-top:1px solid #eee;">
<div>
<span style="color:#666;">Tổng tiền:</span>
<strong style="color:var(--primary-color);font-size:20px;margin-left:10px;"><?php echo formatPrice($order['tong_thanh_toan']); ?></strong>
</div>
<div style="display:flex;gap:10px;">
<a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-outline" style="padding:8px 15px;">
<i class="fas fa-eye"></i> Chi tiết
</a>
<?php if($order['trang_thai'] == 'cho_xac_nhan'): ?>
<button class="btn btn-danger" style="padding:8px 15px;" onclick="cancelOrder(<?php echo $order['id']; ?>)">
<i class="fas fa-times"></i> Hủy đơn
</button>
<?php endif; ?>
</div>
</div>
</div>
<?php endwhile; ?>
</div>
<?php else: ?>
<div style="text-align:center;padding:60px;">
<i class="fas fa-box-open" style="font-size:80px;color:#ddd;margin-bottom:20px;"></i>
<h3>Chưa có đơn hàng nào</h3>
<p style="color:#999;">Hãy mua sắm ngay để trải nghiệm dịch vụ tốt nhất!</p>
<a href="<?php echo SITE_URL;?>/products.php" class="btn btn-primary" style="margin-top:20px;">Mua sắm ngay</a>
</div>
<?php endif; ?>
</div>

<!-- Password Section -->
<div id="password" class="section">
<h2><i class="fas fa-lock"></i> Đổi mật khẩu</h2>

<?php if(isset($success_password)): ?>
<div class="alert alert-success"><?php echo $success_password; ?></div>
<?php endif; ?>
<?php if(isset($error)): ?>
<div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if(!empty($user['mat_khau'])): ?>
<form method="POST" style="max-width:600px;margin-top:30px;">
<div class="form-group">
<label>Mật khẩu hiện tại *</label>
<input type="password" name="current_password" class="form-control" required>
</div>
<div class="form-group">
<label>Mật khẩu mới *</label>
<input type="password" name="new_password" class="form-control" required minlength="6">
<small style="color:#999;">Ít nhất 6 ký tự</small>
</div>
<div class="form-group">
<label>Xác nhận mật khẩu mới *</label>
<input type="password" name="confirm_password" class="form-control" required minlength="6">
</div>
<button type="submit" name="change_password" class="btn btn-primary">
<i class="fas fa-key"></i> Đổi mật khẩu
</button>
</form>
<?php else: ?>
<div class="alert alert-error">
<i class="fas fa-info-circle"></i> Tài khoản của bạn đăng nhập qua <?php echo $user['provider']; ?>, không thể đổi mật khẩu.
</div>
<?php endif; ?>
</div>

</div>

</div>
</div>
</section>

<script>
function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
    document.getElementById(sectionId).classList.add('active');
    event.target.classList.add('active');
}

function cancelOrder(orderId) {
    if(!confirm('Bạn có chắc muốn hủy đơn hàng này?')) return;
    
    fetch('<?php echo SITE_URL;?>/api/cancel-order.php', {
        m