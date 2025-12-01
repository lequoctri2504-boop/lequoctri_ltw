<?php require_once 'config/config.php';
$page_title = 'Danh sách sản phẩm';
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$where = [];
$params = [];
$types = '';
if ($category > 0) {
    $where[] = 'sp.id_danh_muc=?';
    $params[] = $category;
    $types .= 'i';
}
if ($search) {
    $where[] = 'sp.ten_sp LIKE ?';
    $params[] = '%' . $search . '%';
    $types .= 's';
}
$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT sp.*,dm.ten_danh_muc FROM san_pham sp LEFT JOIN danh_muc dm ON sp.id_danh_muc=dm.id $where_sql ORDER BY sp.ngay_tao DESC";
if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
$categories = $conn->query('SELECT * FROM danh_muc');
include 'includes/header.php'; ?>
<div class="breadcrumb">
    <div class="container"><a href="<?php echo SITE_URL; ?>/">Trang chủ</a><i class="fas fa-chevron-right"></i><span>Sản phẩm</span></div>
</div>
<section class="products-page">
    <div class="container">
        <div class="products-layout">
            <div class="filter-sidebar">
                <div class="filter-box">
                    <h3>Danh mục</h3>
                    <div class="filter-group">
                        <label><input type="radio" name="category" value="" <?php echo $category == 0 ? 'checked' : ''; ?> onchange="filterProducts()"> Tất cả</label>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <label><input type="radio" name="category" value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'checked' : ''; ?> onchange="filterProducts()"> <?php echo htmlspecialchars($cat['ten_danh_muc']); ?></label>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <div class="products-main">
                <div class="product-grid">
                    <?php while ($product = $result->fetch_assoc()): $discount = 0;
                        if ($product['gia'] > 0) {
                            $discount = round((($product['gia'] - $product['gia_khuyen_mai']) / $product['gia']) * 100);
                        } ?>
                        <div class="product-card" onclick="location.href='product-detail.php?id=<?php echo $product['id']; ?>'">
                            <?php if ($discount > 0): ?><div class="product-badge">-<?php echo $discount; ?>%</div><?php endif; ?>
                            <div class="product-image"><img src="<?php echo SITE_URL; ?>/uploads/<?php echo $product['hinh_anh']; ?>" alt="<?php echo htmlspecialchars($product['ten_sp']); ?>" onerror="this.src='https://images.unsplash.com/photo-1592286927505-128d151c5874?w=300&h=300&fit=crop'"></div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['ten_sp']); ?></h3>
                                <div class="product-price"><span class="price-new"><?php echo formatPrice($product['gia_khuyen_mai']); ?></span>
                                    <?php if ($product['gia'] != $product['gia_khuyen_mai']): ?><span class="price-old"><?php echo formatPrice($product['gia']); ?></span><?php endif; ?></div>
                                <button class="btn btn-cart" onclick="event.stopPropagation();addToCart(<?php echo $product['id']; ?>)"><i class="fas fa-shopping-cart"></i> Thêm vào giỏ</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    function filterProducts() {
        const category = document.querySelector('input[name="category"]:checked').value;
        let url = 'products.php?';
        if (category) url += 'category=' + category;
        window.location.href = url;
    }

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
        }).then(r => r.json()).then(d => {
            if (d.success) {
                showToast('Đã thêm vào giỏ!', 'success');
            } else {
                showToast(d.message || 'Lỗi!', 'error');
            }
        });
    }
</script>
<?php include 'includes/footer.php'; ?>