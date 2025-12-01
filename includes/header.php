<?php if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/config.php';
} ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : '';
            echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="header-top-content">
                    <div class="header-left">
                        <a href="#"><i class="fas fa-phone"></i> hotline: 0962371176</a>
                        <!-- <a href="#"><i class="fas fa-map-marker-alt"></i> Tìm cửa hàng</a> -->
                    </div>
                    <div class="header-right">
                        <?php if (isLoggedIn()): $user = getCurrentUser(); ?>
                            <a href="<?php echo SITE_URL; ?>/account.php"><i class="fas fa-user"></i> <?php echo htmlspecialchars($user['ho_ten']); ?></a>
                            <a href="<?php echo SITE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/login.php"><i class="fas fa-user"></i> Đăng nhập</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-main">
            <div class="container">
                <div class="header-main-content">
                    <div class="logo">
                        <a href="<?php echo SITE_URL; ?>/"><span><img src="<?php echo SITE_URL; ?>/assets/images/logo_LQT1.png" alt="Logo" width="70px"></span></a>
                    </div>
                    <div class="search-box">
                        <form action="<?php echo SITE_URL; ?>/search.php" method="GET">
                            <input type="text" name="q" placeholder="Tìm kiếm điện thoại, phụ kiện..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    <div class="header-actions">
                        <a href="<?php echo SITE_URL; ?>/cart.php" class="cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge" id="cart-count">0</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <nav class="navbar">
            <div class="container">
                <ul class="nav-menu">
                    <li><a href="<?php echo SITE_URL; ?>/products.php"><i class="fas fa-mobile-alt"></i> Điện thoại</a></li>
                    <!-- <li><a href="<?php echo SITE_URL; ?>/products.php?category=2"><i class="fas fa-headphones"></i> Phụ kiện</a></li> -->
                    <li class="hot"><a href="<?php echo SITE_URL; ?>/products.php"><i class="fas fa-fire"></i> Khuyến mãi</a></li>
                </ul>
            </div>
        </nav>
    </header>