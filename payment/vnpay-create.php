<?php
require_once '../config/config.php';

if(!isset($_GET['order_id'])) {
    redirect(SITE_URL . '/cart.php');
}

$order_id = intval($_GET['order_id']);

// Get order info
$sql = "SELECT * FROM don_hang WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if(!$order) {
    die('Đơn hàng không tồn tại');
}

// Check if VNPay is configured
if(empty(VNPAY_TMN_CODE) || empty(VNPAY_HASH_SECRET)) {
    die('VNPay chưa được cấu hình. Vui lòng kiểm tra config.php');
}

$vnp_TmnCode = VNPAY_TMN_CODE;
$vnp_HashSecret = VNPAY_HASH_SECRET;
$vnp_Url = VNPAY_URL;
$vnp_Returnurl = VNPAY_RETURN_URL;

$vnp_TxnRef = $order['ma_don_hang'];
$vnp_OrderInfo = "Thanh toan don hang " . $order['ma_don_hang'];
$vnp_OrderType = 'billpayment';
$vnp_Amount = $order['tong_thanh_toan'] * 100; // VNPay yêu cầu số tiền * 100
$vnp_Locale = 'vn';
$vnp_BankCode = '';
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => $vnp_OrderType,
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef
);

if(isset($vnp_BankCode) && $vnp_BankCode != "") {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}

ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";
foreach($inputData as $key => $value) {
    if($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . "?" . $query;
$vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
$vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

header('Location: ' . $vnp_Url);
exit();
?>