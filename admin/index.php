<?php
require_once '../config/config.php';

// Kiểm tra đăng nhập admin
if(!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login.php');
}

$user = getCurrentUser();

// Lấy thống kê
$stats_products = $conn->query("SELECT COUNT(*) as total FROM san_pham")->fetch_assoc()['total'];
$stats_orders = $conn->query("SELECT COUNT(*) as total FROM don_hang")->fetch_assoc()['total'];
$stats_customers = $conn->query("SELECT COUNT(*) as total FROM nguoi_dung WHERE vai_tro='khachhang'")->fetch_assoc()['total'];
$stats_revenue = $conn->query("SELECT COALESCE(SUM(tong_thanh_toan), 0) as total FROM don_hang WHERE trang_thai='hoan_thanh'")->fetch_assoc()['total'];

// Lấy danh sách sản phẩm
$sql_products = "SELECT sp.*, dm.ten_danh_muc FROM san_pham sp LEFT JOIN danh_muc dm ON sp.id_danh_muc = dm.id ORDER BY sp.id DESC";
$products = $conn->query($sql_products);

// Lấy danh sách đơn hàng
$sql_orders = "SELECT dh.*, nd.ho_ten FROM don_hang dh LEFT JOIN nguoi_dung nd ON dh.id_nguoi_dung = nd.id ORDER BY dh.id DESC";
$orders = $conn->query($sql_orders);

// Lấy danh sách khách hàng
$sql_customers = "SELECT * FROM nguoi_dung WHERE vai_tro='khachhang' ORDER BY id DESC";
$customers = $conn->query($sql_customers);

// Lấy danh mục
$categories = $conn->query("SELECT * FROM danh_muc ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f4f6f9;
            font-family: Arial;
        }
        #sidebar {
            width: 240px;
            height: 100vh;
            background: #212529;
            position: fixed;
            left: 0;
            top: 0;
            color: white;
            padding-top: 20px;
        }
        #sidebar a {
            display: block;
            padding: 12px 20px;
            color: #adb5bd;
            text-decoration: none;
        }
        #sidebar a:hover, #sidebar a.active {
            background: #343a40;
            color: white;
        }
        #content {
            margin-left: 250px;
            padding: 20px;
        }
        .header-bar {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div id="sidebar">
    <h4 class="text-center mb-4">⚙️ Admin Panel</h4>
    <a href="#" onclick="showSection('dashboard')" class="active"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="#" onclick="showSection('products')"><i class="fas fa-box"></i> Sản phẩm</a>
    <a href="#" onclick="showSection('orders')"><i class="fas fa-shopping-cart"></i> Đơn hàng</a>
    <a href="#" onclick="showSection('customers')"><i class="fas fa-users"></i> Khách hàng</a>
    <hr style="border-color:#444;">
    <a href="<?php echo SITE_URL; ?>/"><i class="fas fa-home"></i> Về trang chủ</a>
    <a href="<?php echo SITE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
</div>

<div id="content">
    
    <div class="header-bar d-flex justify-content-between align-items-center">
        <h3>Trang quản trị</h3>
        <div>
            <span class="badge bg-danger me-2">Admin</span>
            <strong><?php echo htmlspecialchars($user['ho_ten']); ?></strong>
        </div>
    </div>

    <!-- DASHBOARD -->
    <div id="dashboard" class="section active">
        <h3>Dashboard</h3>
        <div class="row mt-3">
            <div class="col-md-3">
                <div class="card text-bg-primary">
                    <div class="card-body">
                        <h5><i class="fas fa-box"></i> Sản phẩm</h5>
                        <h3><?php echo $stats_products; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-success">
                    <div class="card-body">
                        <h5><i class="fas fa-shopping-cart"></i> Đơn hàng</h5>
                        <h3><?php echo $stats_orders; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-warning">
                    <div class="card-body">
                        <h5><i class="fas fa-users"></i> Khách hàng</h5>
                        <h3><?php echo $stats_customers; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-danger">
                    <div class="card-body">
                        <h5><i class="fas fa-money-bill"></i> Doanh thu</h5>
                        <h3><?php echo number_format($stats_revenue/1000000, 1); ?>M</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PRODUCTS -->
    <div id="products" class="section">
        <h3>Quản lý sản phẩm</h3>
        <button class="btn btn-primary mb-3" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm sản phẩm</button>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên</th>
                        <th>Giá</th>
                        <th>Giá KM</th>
                        <th>Tồn kho</th>
                        <th>Danh mục</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="products-tbody">
                    <?php while($p = $products->fetch_assoc()): ?>
                    <tr id="product-<?php echo $p['id']; ?>">
                        <td><?php echo $p['id']; ?></td>
                        <td><img src="<?php echo SITE_URL; ?>/uploads/<?php echo $p['hinh_anh']; ?>" class="product-img" onerror="this.src='https://via.placeholder.com/50'"></td>
                        <td><?php echo htmlspecialchars($p['ten_sp']); ?></td>
                        <td><?php echo number_format($p['gia']); ?>₫</td>
                        <td><?php echo number_format($p['gia_khuyen_mai']); ?>₫</td>
                        <td><?php echo $p['ton_kho']; ?></td>
                        <td><?php echo htmlspecialchars($p['ten_danh_muc']); ?></td>
                        <td>
                            <button onclick="editProduct(<?php echo $p['id']; ?>)" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteProduct(<?php echo $p['id']; ?>)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ORDERS -->
    <div id="orders" class="section">
        <h3>Quản lý đơn hàng</h3>
        <div class="table-responsive">
            <table class="table table-bordered mt-3 bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Mã đơn</th>
                        <th>Khách</th>
                        <th>SĐT</th>
                        <th>Tổng tiền</th>
                        <th>Ngày</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <?php while($o = $orders->fetch_assoc()): 
                        $status_class = [
                            'cho_xac_nhan' => 'warning',
                            'da_xac_nhan' => 'info',
                            'dang_giao' => 'primary',
                            'hoan_thanh' => 'success',
                            'da_huy' => 'danger'
                        ];
                        $status_text = [
                            'cho_xac_nhan' => 'Chờ xác nhận',
                            'da_xac_nhan' => 'Đã xác nhận',
                            'dang_giao' => 'Đang giao',
                            'hoan_thanh' => 'Hoàn thành',
                            'da_huy' => 'Đã hủy'
                        ];
                    ?>
                    <tr id="order-<?php echo $o['id']; ?>">
                        <td><?php echo $o['id']; ?></td>
                        <td><?php echo $o['ma_don_hang'] ?: '#DH'.$o['id']; ?></td>
                        <td><?php echo htmlspecialchars($o['ho_ten'] ?: ($o['ho_ten'] ?: 'N/A')); ?></td>
                        <td><?php echo htmlspecialchars($o['dien_thoai'] ?: 'N/A'); ?></td>
                        <td><?php echo number_format($o['tong_thanh_toan'] ?: $o['tong_tien']); ?>₫</td>
                        <td><?php echo date('d/m/Y', strtotime($o['ngay_dat'] ?: $o['ngay_tao'])); ?></td>
                        <td><span class="badge bg-<?php echo $status_class[$o['trang_thai']] ?? 'secondary'; ?>"><?php echo $status_text[$o['trang_thai']] ?? $o['trang_thai']; ?></span></td>
                        <td>
                            <select class="form-select form-select-sm" onchange="updateOrderStatus(<?php echo $o['id']; ?>, this.value)">
                                <option value="cho_xac_nhan" <?php echo $o['trang_thai']=='cho_xac_nhan'?'selected':''; ?>>Chờ xác nhận</option>
                                <option value="da_xac_nhan" <?php echo $o['trang_thai']=='da_xac_nhan'?'selected':''; ?>>Đã xác nhận</option>
                                <option value="dang_giao" <?php echo $o['trang_thai']=='dang_giao'?'selected':''; ?>>Đang giao</option>
                                <option value="hoan_thanh" <?php echo $o['trang_thai']=='hoan_thanh'?'selected':''; ?>>Hoàn thành</option>
                                <option value="da_huy" <?php echo $o['trang_thai']=='da_huy'?'selected':''; ?>>Đã hủy</option>
                            </select>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- CUSTOMERS -->
    <div id="customers" class="section">
        <h3>Danh sách khách hàng</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered mt-3 bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Địa chỉ</th>
                        <th>Ngày đăng ký</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($c = $customers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['ho_ten']); ?></td>
                        <td><?php echo htmlspecialchars($c['email']); ?></td>
                        <td><?php echo htmlspecialchars($c['dien_thoai'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($c['dia_chi'] ?: 'N/A'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($c['ngay_tao'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL ADD PRODUCT -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Thêm sản phẩm mới</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Tên sản phẩm *</label>
                        <input type="text" name="ten_sp" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Giá *</label>
                            <input type="number" name="gia" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Giá khuyến mãi *</label>
                            <input type="number" name="gia_khuyen_mai" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Danh mục *</label>
                            <select name="id_danh_muc" class="form-select" required>
                                <?php 
                                $categories->data_seek(0);
                                while($cat = $categories->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['ten_danh_muc']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Tồn kho *</label>
                            <input type="number" name="ton_kho" class="form-control" value="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Mô tả</label>
                        <textarea name="mo_ta" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Ảnh sản phẩm *</label>
                        <input type="file" name="hinh_anh" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT PRODUCT -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Sửa sản phẩm</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Tên sản phẩm *</label>
                        <input type="text" name="ten_sp" id="edit_ten_sp" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Giá *</label>
                            <input type="number" name="gia" id="edit_gia" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Giá khuyến mãi *</label>
                            <input type="number" name="gia_khuyen_mai" id="edit_gia_khuyen_mai" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Danh mục *</label>
                            <select name="id_danh_muc" id="edit_id_danh_muc" class="form-select" required>
                                <?php 
                                $categories->data_seek(0);
                                while($cat = $categories->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['ten_danh_muc']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Tồn kho *</label>
                            <input type="number" name="ton_kho" id="edit_ton_kho" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Mô tả</label>
                        <textarea name="mo_ta" id="edit_mo_ta" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Ảnh hiện tại:</label><br>
                        <img id="edit_current_image" src="" style="max-width:150px;margin-bottom:10px;">
                    </div>
                    <div class="mb-3">
                        <label>Đổi ảnh mới (để trống nếu không đổi)</label>
                        <input type="file" name="hinh_anh" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
const SITE_URL = '<?php echo SITE_URL; ?>';

function showSection(id) {
    document.querySelectorAll(".section").forEach(s => s.classList.remove("active"));
    document.getElementById(id).classList.add("active");
    document.querySelectorAll("#sidebar a").forEach(a => a.classList.remove("active"));
    event.target.classList.add("active");
}

function openAddModal() {
    new bootstrap.Modal(document.getElementById('addModal')).show();
}

// Submit thêm sản phẩm
$('#addForm').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    $.ajax({
        url: SITE_URL + '/admin/api/product-add.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if(response.success) {
                alert('Thêm sản phẩm thành công!');
                location.reload();
            } else {
                alert('Lỗi: ' + response.message);
            }
        },
        error: function() {
            alert('Có lỗi xảy ra!');
        }
    });
});

// Load thông tin sản phẩm để edit
function editProduct(id) {
    $.get(SITE_URL + '/admin/api/product-get.php?id=' + id, function(data) {
        if(data.success) {
            const p = data.product;
            $('#edit_id').val(p.id);
            $('#edit_ten_sp').val(p.ten_sp);
            $('#edit_gia').val(p.gia);
            $('#edit_gia_khuyen_mai').val(p.gia_khuyen_mai);
            $('#edit_id_danh_muc').val(p.id_danh_muc);
            $('#edit_ton_kho').val(p.ton_kho);
            $('#edit_mo_ta').val(p.mo_ta);
            $('#edit_current_image').attr('src', SITE_URL + '/uploads/' + p.hinh_anh);
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    });
}

// Submit sửa sản phẩm
$('#editForm').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    $.ajax({
        url: SITE_URL + '/admin/api/product-update.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if(response.success) {
                alert('Cập nhật thành công!');
                location.reload();
            } else {
                alert('Lỗi: ' + response.message);
            }
        }
    });
});

// Xóa sản phẩm
function deleteProduct(id) {
    if(!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
    
    $.post(SITE_URL + '/admin/api/product-delete.php', {id: id}, function(response) {
        if(response.success) {
            alert('Đã xóa!');
            $('#product-' + id).remove();
        } else {
            alert('Lỗi: ' + response.message);
        }
    });
}

// Cập nhật trạng thái đơn hàng
function updateOrderStatus(orderId, status) {
    $.post(SITE_URL + '/admin/api/order-update-status.php', {
        order_id: orderId,
        status: status
    }, function(response) {
        if(response.success) {
            alert('Đã cập nhật trạng thái!');
            location.reload();
        } else {
            alert('Lỗi: ' + response.message);
        }
    });
}
</script>

</body>
</html>
