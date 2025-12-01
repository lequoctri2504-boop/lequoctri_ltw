<?php
require_once 'config/config.php';

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    redirect(SITE_URL . '/products.php');
}

// Lấy thông tin sản phẩm
$sql = "SELECT sp.*, dm.ten_danh_muc 
        FROM san_pham sp 
        LEFT JOIN danh_muc dm ON sp.id_danh_muc = dm.id 
        WHERE sp.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    redirect(SITE_URL . '/products.php');
}

$page_title = $product['ten_sp'];

// Tính discount
$discount = 0;
if ($product['gia'] > 0 && $product['gia_khuyen_mai'] < $product['gia']) {
    $discount = round((($product['gia'] - $product['gia_khuyen_mai']) / $product['gia']) * 100);
}

// Lấy sản phẩm liên quan
$sql_related = "SELECT * FROM san_pham 
                WHERE id_danh_muc = ? AND id != ? 
                ORDER BY RAND() 
                LIMIT 4";
$stmt = $conn->prepare($sql_related);
$stmt->bind_param("ii", $product['id_danh_muc'], $product_id);
$stmt->execute();
$related_products = $stmt->get_result();

include 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/">Trang chủ</a>
        <i class="fas fa-chevron-right"></i>
        <a href="<?php echo SITE_URL; ?>/products.php">Sản phẩm</a>
        <i class="fas fa-chevron-right"></i>
        <span><?php echo htmlspecialchars($product['ten_sp']); ?></span>
    </div>
</div>

<section class="product-detail">
    <div class="container">
        <div class="detail-layout">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <div class="main-image">
                    <img id="mainImg" src="<?php echo SITE_URL; ?>/uploads/<?php echo $product['hinh_anh']; ?>" 
                         alt="<?php echo htmlspecialchars($product['ten_sp']); ?>"
                         onerror="this.src='https://images.unsplash.com/photo-1592286927505-128d151c5874?w=600&h=600&fit=crop'">
                </div>
                <div class="thumbnail-list">
                    <img class="active" src="<?php echo SITE_URL; ?>/uploads/<?php echo $product['hinh_anh']; ?>" 
                         alt="Thumb 1"
                         onerror="this.src='https://images.unsplash.com/photo-1592286927505-128d151c5874?w=100&h=100&fit=crop'">
                    <img src="<?php echo SITE_URL; ?>/uploads/<?php echo $product['hinh_anh']; ?>" 
                         alt="Thumb 2"
                         onerror="this.src='https://images.unsplash.com/photo-1592286927505-128d151c5874?w=100&h=100&fit=crop'">
                    <img src="<?php echo SITE_URL; ?>/uploads/<?php echo $product['hinh_anh']; ?>" 
                         alt="Thumb 3"
                         onerror="this.src='https://images.unsplash.com/photo-1592286927505-128d151c5874?w=100&h=100&fit=crop'">
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-detail-info">
                <h1><?php echo htmlspecialchars($product['ten_sp']); ?></h1>
                
                <div class="product-meta">
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>(234 đánh giá)</span>
                    </div>
                    <span>|</span>
                    <span><strong>Danh mục:</strong> <?php echo htmlspecialchars($product['ten_danh_muc']); ?></span>
                    <span>|</span>
                    <span><strong>Tình trạng:</strong> 
                        <?php if ($product['ton_kho'] > 0): ?>
                            <span style="color: var(--success-color);">Còn hàng</span>
                        <?php else: ?>
                            <span style="color: var(--danger-color);">Hết hàng</span>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="price-section">
                    <div class="main-price">
                        <span class="price-new"><?php echo formatPrice($product['gia_khuyen_mai']); ?></span>
                        <?php if ($discount > 0): ?>
                            <span class="price-old"><?php echo formatPrice($product['gia']); ?></span>
                            <span class="discount-badge">-<?php echo $discount; ?>%</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($product['mo_ta'])): ?>
                <div class="promotions-box">
                    <h3><i class="fas fa-gift"></i> Thông tin sản phẩm</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['mo_ta'])); ?></p>
                </div>
                <?php endif; ?>

                <?php if ($product['ton_kho'] > 0): ?>
                <div class="purchase-actions">
                    <div class="quantity-selector">
                        <button class="qty-btn" onclick="changeQuantity(-1)"><i class="fas fa-minus"></i></button>
                        <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['ton_kho']; ?>" readonly>
                        <button class="qty-btn" onclick="changeQuantity(1)"><i class="fas fa-plus"></i></button>
                    </div>
                    <button class="btn btn-primary btn-large" style="flex:1;" onclick="addToCartDetail(<?php echo $product['id']; ?>)">
                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
                    </button>
                </div>
                <?php else: ?>
                <div class="alert" style="background:#fee;color:#c33;padding:15px;border-radius:8px;">
                    <i class="fas fa-exclamation-circle"></i> Sản phẩm tạm hết hàng
                </div>
                <?php endif; ?>

                <div class="policies">
                    <div class="policy-item">
                        <i class="fas fa-truck"></i>
                        <div>
                            <strong>Giao hàng nhanh</strong>
                            <p>Giao hàng trong 2h</p>
                        </div>
                    </div>
                    <div class="policy-item">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>Bảo hành chính hãng</strong>
                            <p>12 tháng toàn quốc</p>
                        </div>
                    </div>
                    <div class="policy-item">
                        <i class="fas fa-sync-alt"></i>
                        <div>
                            <strong>Đổi trả dễ dàng</strong>
                            <p>Trong 7 ngày</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if ($related_products->num_rows > 0): ?>
        <div style="margin-top:60px;">
            <div class="section-header">
                <h2><i class="fas fa-box"></i> SẢN PHẨM LIÊN QUAN</h2>
            </div>
            <div class="product-grid">
                <?php while ($related = $related_products->fetch_assoc()): 
                    $rel_discount = 0;
                    if ($related['gia'] > 0) {
                        $rel_discount = round((($related['gia'] - $related['gia_khuyen_mai']) / $related['gia']) * 100);
                    }
                ?>
                <div class="product-card" onclick="location.href='product-detail.php?id=<?php echo $related['id']; ?>'">
                    <?php if ($rel_discount > 0): ?>
                    <div class="product-badge">-<?php echo $rel_discount; ?>%</div>
                    <?php endif; ?>
                    <div class="product-image">
                        <img src="<?php echo SITE_URL; ?>/uploads/<?php echo $related['hinh_anh']; ?>" 
                             alt="<?php echo htmlspecialchars($related['ten_sp']); ?>"
                             onerror="this.src='https://images.unsplash.com/photo-1592286927505-128d151c5874?w=300&h=300&fit=crop'">
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($related['ten_sp']); ?></h3>
                        <div class="product-price">
                            <span class="price-new"><?php echo formatPrice($related['gia_khuyen_mai']); ?></span>
                            <?php if ($related['gia'] != $related['gia_khuyen_mai']): ?>
                            <span class="price-old"><?php echo formatPrice($related['gia']); ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-cart" onclick="event.stopPropagation();addToCart(<?php echo $related['id']; ?>)">
                            <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function changeQuantity(change) {
    const input = document.getElementById('quantity');
    let value = parseInt(input.value) || 1;
    const max = parseInt(input.max);
    
    value += change;
    if (value < 1) value = 1;
    if (value > max) value = max;
    
    input.value = value;
}

function addToCartDetail(productId) {
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    
    fetch('<?php echo SITE_URL; ?>/api/cart-add.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Đã thêm vào giỏ hàng!', 'success');
            // Update cart count
            if (data.cart_count !== undefined) {
                document.getElementById('cart-count').textContent = data.cart_count;
            }
        } else {
            showToast(data.message || 'Có lỗi xảy ra!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra!', 'error');
    });
}

function addToCart(productId) {
    fetch('<?php echo SITE_URL; ?>/api/cart-add.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Đã thêm vào giỏ!', 'success');
            if (data.cart_count !== undefined) {
                document.getElementById('cart-count').textContent = data.cart_count;
            }
        } else {
            showToast(data.message || 'Lỗi!', 'error');
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
