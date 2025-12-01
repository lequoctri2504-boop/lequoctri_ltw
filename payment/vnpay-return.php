<?php
require_once '../config/config.php';
$page_title = 'Kết quả thanh toán';

$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
$inputData = array();
foreach($_GET as $key => $value) {
    if(substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach($inputData as $key => $value) {
    if($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, VNPAY_HASH_SECRET);

$vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
$vnp_TxnRef = $_GET['vnp_TxnRef'] ?? '';
$vnp_TransactionNo = $_GET['vnp_TransactionNo'] ?? '';
$vnp_Amount = ($_GET['vnp_Amount'] ?? 0) / 100;

if($secureHash == $vnp_SecureHash) {
    if($vnp_ResponseCode == '00') {
        // Payment success
        $sql = "UPDATE don_hang SET trang_thai_thanh_toan = 'da_thanh_toan', ma_giao_dich = ? WHERE ma_don_hang = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $vnp_TransactionNo, $vnp_TxnRef);
        $stmt->execute();
        
        $success = true;
        $message = "Giao dịch thành công!";
    } else {
        $success = false;
        $message = "Giao dịch thất bại!";
    }
} else {
    $success = false;
    $message = "Chữ ký không hợp lệ!";
}

include '../includes/header.php';
?>

<section style="padding:80px 0;text-align:center;">
<div class="container">
    <?php if($success): ?>
        <i class="fas fa-check-circle" style="font-size:100px;color:#28a745;margin-bottom:30px;"></i>
        <h1>Thanh toán thành công!</h1>
        <p style="font-size:18px;margin:20px 0;">Mã đơn hàng: <strong><?php echo htmlspecialchars($vnp_TxnRef); ?></strong></p>
        <p style="font-size:18px;margin:20px 0;">Mã giao dịch: <strong><?php echo htmlspecialchars($vnp_TransactionNo); ?></strong></p>
        <p style="font-size:18px;margin:20px 0;">Số tiền: <strong><?php echo formatPrice($vnp_Amount); ?></strong></p>
    <?php else: ?>
        <i class="fas fa-times-circle" style="font-size:100px;color:#dc3545;margin-bottom:30px;"></i>
        <h1>Thanh toán thất bại!</h1>
        <p style="font-size:18px;color:#dc3545;margin:20px 0;"><?php echo htmlspecialchars($message); ?></p>
        <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn btn-primary" style="margin-top:20px;">Thử lại</a>
    <?php endif; ?>
    
    <div style="margin-top:40px;">
        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-secondary">Tiếp tục mua sắm</a>
    </div>
</div>
</section>

<?php include '../includes/footer.php'; ?>