<?php
require_once 'config/config.php';
$page_title = 'Trang chủ';
$sql_products = "SELECT sp.*, dm.ten_danh_muc FROM san_pham sp LEFT JOIN danh_muc dm ON sp.id_danh_muc = dm.id ORDER BY sp.ngay_tao DESC LIMIT 8";
$result_products = $conn->query($sql_products);
$sql_categories = "SELECT * FROM danh_muc";
$result_categories = $conn->query($sql_categories);
include 'includes/header.php';
?>
<section class="banner-slider">
    <div class="container">
        <div class="slider-wrapper">
            <div class="slide active">
                <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=1200&h=400&fit=crop" alt="Banner">
                <div class="slide-content">
                    <h2>iPhone 17 Pro Max</h2>
                    <p>Titanium. Mạnh mẽ. Nhẹ hơn bao giờ hết</p>
                    <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-primary">Xem ngay</a>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="flash-sale">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-bolt"></i> FLASH SALE</h2>
            <div class="countdown"><span>02</span>:<span>35</span>:<span>48</span></div>
        </div>
        <div class="product-grid">
            <?php $result_products->data_seek(0);
            $count = 0;
            while ($product = $result_products->fetch_assoc()): if ($count >= 4) break;
                $count++;
                $discount = 0;
                if ($product['gia'] > 0) {
                    $discount = round((($product['gia'] - $product['gia_khuyen_mai']) / $product['gia']) * 100);
                } ?>
                <div class="product-card" onclick="location.href='product-detail.php?id=<?php echo $product['id']; ?>'">
                    <?php if ($discount > 0): ?><div class="product-badge">-<?php echo $discount; ?>%</div><?php endif; ?>
                    <div class="product-image">
                        <img src="<?php echo SITE_URL; ?>/uploads/<?php echo $product['hinh_anh']; ?>" alt="<?php echo htmlspecialchars($product['ten_sp']); ?>" onerror="this.src='https://images.unsplash.com/photo-1592286927505-128d151c5874?w=300&h=300&fit=crop'">
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['ten_sp']); ?></h3>
                        <div class="product-rating"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i><span>(234)</span></div>
                        <div class="product-price">
                            <span class="price-new"><?php echo formatPrice($product['gia_khuyen_mai']); ?></span>
                            <?php if ($product['gia'] != $product['gia_khuyen_mai']): ?>
                                <span class="price-old"><?php echo formatPrice($product['gia']); ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-cart" onclick="event.stopPropagation();addToCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-shopping-cart"></i> Thêm vào giỏ</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<section class="hot-brands">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-fire"></i> THƯƠNG HIỆU NỔI BẬT</h2>
        </div>
        <div class="brands-grid">
            <?php while ($category = $result_categories->fetch_assoc()): ?>
                <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $category['id']; ?>" class="brand-card">
                    <span><?php echo htmlspecialchars($category['ten_danh_muc']); ?></span>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<script>
    function addToCart(productId) {
        fetch('<?php echo SITE_URL; ?>/api/cart-add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        }).then(response => response.json()).then(data => {
            if (data.success) {
                showToast('Đã thêm vào giỏ hàng!', 'success');
                updateCartCount();
            } else {
                showToast(data.message || 'Có lỗi xảy ra!', 'error');
            }
        }).catch(error => {
            console.error('Error:', error);
            showToast('Có lỗi xảy ra!', 'error');
        });
    }
</script>
<?php include 'includes/footer.php'; ?>