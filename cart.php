<?php require_once 'config/config.php'; $page_title='Giỏ hàng'; $cart_items=isset($_SESSION['cart'])?$_SESSION['cart']:[]; $subtotal=0; $total_quantity=0; foreach($cart_items as $item){$subtotal+=$item['gia']*$item['quantity']; $total_quantity+=$item['quantity'];} $total=$subtotal; include 'includes/header.php';?>
<div class="breadcrumb"><div class="container"><a href="<?php echo SITE_URL;?>/">Trang chủ</a><i class="fas fa-chevron-right"></i><span>Giỏ hàng</span></div></div>
<section class="cart-page"><div class="container"><h1><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn</h1>
<?php if(count($cart_items)==0):?>
<div style="text-align:center;padding:60px 20px;"><i class="fas fa-shopping-cart" style="font-size:80px;color:#ddd;margin-bottom:20px;"></i>
<h3>Giỏ hàng của bạn đang trống</h3><p style="color:#999;margin-bottom:30px;">Hãy thêm sản phẩm vào giỏ hàng</p>
<a href="<?php echo SITE_URL;?>/products.php" class="btn btn-primary btn-large">Tiếp tục mua hàng</a></div>
<?php else:?>
<div class="cart-layout"><div class="cart-items">
<?php foreach($cart_items as $id=>$item):?>
<div class="cart-item" data-id="<?php echo $id;?>">
<div class="item-image"><img src="<?php echo SITE_URL;?>/uploads/<?php echo $item['hinh_anh'];?>" alt="<?php echo htmlspecialchars($item['ten_sp']);?>" onerror="this.src='https://images.unsplash.com/photo-1592286927505-128d151c5874?w=150&h=150&fit=crop'"></div>
<div class="item-info"><h3><?php echo htmlspecialchars($item['ten_sp']);?></h3>
<div class="item-actions"><button class="btn-link text-danger" onclick="removeFromCart(<?php echo $id;?>)"><i class="fas fa-trash"></i> Xóa</button></div></div>
<div class="item-quantity">
<button class="qty-btn" onclick="updateQuantity(<?php echo $id;?>,-1)"><i class="fas fa-minus"></i></button>
<input type="number" value="<?php echo $item['quantity'];?>" min="1" readonly>
<button class="qty-btn" onclick="updateQuantity(<?php echo $id;?>,1)"><i class="fas fa-plus"></i></button>
</div>
<div class="item-price"><span class="price-current"><?php echo formatPrice($item['gia']*$item['quantity']);?></span></div>
</div>
<?php endforeach;?>
</div>
<div class="cart-summary"><h3>Thông tin đơn hàng</h3>
<div class="summary-row"><span>Tạm tính (<?php echo $total_quantity;?> sản phẩm):</span><span><?php echo formatPrice($subtotal);?></span></div>
<div class="summary-row"><span>Phí vận chuyển:</span><span class="text-success">Miễn phí</span></div>
<div class="summary-total"><span>Tổng cộng:</span><span class="total-price"><?php echo formatPrice($total);?></span></div>
<a href="<?php echo SITE_URL;?>/checkout.php" class="btn btn-primary btn-block btn-large"><i class="fas fa-credit-card"></i> Tiến hành thanh toán</a>
<a href="<?php echo SITE_URL;?>/products.php" class="btn btn-secondary btn-block"><i class="fas fa-arrow-left"></i> Tiếp tục mua hàng</a>
</div></div>
<?php endif;?>
</div></section>
<script>
function updateQuantity(productId,change){fetch('<?php echo SITE_URL;?>/api/cart-update.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({product_id:productId,change:change})}).then(r=>r.json()).then(d=>{if(d.success){location.reload();}});}
function removeFromCart(productId){if(!confirm('Bạn có chắc muốn xóa sản phẩm này?'))return; fetch('<?php echo SITE_URL;?>/api/cart-remove.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({product_id:productId})}).then(r=>r.json()).then(d=>{if(d.success){location.reload();}});}
</script>
<?php include 'includes/footer.php';?>
