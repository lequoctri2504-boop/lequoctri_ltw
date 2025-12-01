<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel Full</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial;
        }

        /* SIDEBAR */
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

        /* CONTENT */
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

    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div id="sidebar">
        <h4 class="text-center mb-4">⚙️ Admin Panel</h4>

        <a href="#" onclick="showSection('dashboard')" class="active">Dashboard</a>
        <a href="#" onclick="showSection('products')">Sản phẩm</a>
        <a href="#" onclick="showSection('orders')">Đơn hàng</a>
        <a href="#" onclick="showSection('customers')">Khách hàng</a>
    </div>

    <!-- CONTENT -->
    <div id="content">

        <!-- HEADER -->
        <div class="header-bar d-flex justify-content-between align-items-center">
            <h3>Trang quản trị</h3>
            <button class="btn btn-danger btn-sm">Đăng xuất</button>
        </div>

        <!-- DASHBOARD -->
        <div id="dashboard" class="section active">
            <h3>Dashboard</h3>
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="card text-bg-primary">
                        <div class="card-body">
                            <h5>Sản phẩm</h5>
                            <h3>120</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-bg-success">
                        <div class="card-body">
                            <h5>Đơn hàng</h5>
                            <h3>45</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-bg-warning">
                        <div class="card-body">
                            <h5>Khách hàng</h5>
                            <h3>350</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card text-bg-danger">
                        <div class="card-body">
                            <h5>Doanh thu</h5>
                            <h3>12.5M</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRODUCTS -->
        <div id="products" class="section">
            <h3>Quản lý sản phẩm</h3>

            <button class="btn btn-primary mb-3" onclick="openAddModal()">+ Thêm sản phẩm</button>

            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Giá</th>
                        <th>Hãng</th>
                        <th>Ảnh</th>
                        <th>Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>1</td>
                        <td>iPhone 15 Pro Max</td>
                        <td>29.990.000</td>
                        <td>Apple</td>
                        <td><img src="https://via.placeholder.com/40"></td>
                        <td>
                            <button onclick="openEditModal()" class="btn btn-warning btn-sm">Sửa</button>
                            <button class="btn btn-danger btn-sm">Xoá</button>
                        </td>
                    </tr>

                    <tr>
                        <td>2</td>
                        <td>Samsung S23 Ultra</td>
                        <td>24.990.000</td>
                        <td>Samsung</td>
                        <td><img src="https://via.placeholder.com/40"></td>
                        <td>
                            <button onclick="openEditModal()" class="btn btn-warning btn-sm">Sửa</button>
                            <button class="btn btn-danger btn-sm">Xoá</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ORDERS -->
        <div id="orders" class="section">
            <h3>Quản lý đơn hàng</h3>

            <table class="table table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách</th>
                        <th>Tổng tiền</th>
                        <th>Ngày</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>#DH001</td>
                        <td>Nguyễn Văn A</td>
                        <td>15.500.000</td>
                        <td>12/10/2024</td>
                        <td><span class="badge bg-success">Hoàn thành</span></td>
                    </tr>

                    <tr>
                        <td>#DH002</td>
                        <td>Trần B</td>
                        <td>12.300.000</td>
                        <td>11/09/2024</td>
                        <td><span class="badge bg-warning">Đang giao</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- CUSTOMERS -->
        <div id="customers" class="section">
            <h3>Danh sách khách hàng</h3>

            <table class="table table-striped table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>SĐT</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Nguyễn Văn A</td>
                        <td>a@gmail.com</td>
                        <td>0900000000</td>
                    </tr>

                    <tr>
                        <td>2</td>
                        <td>Trần B</td>
                        <td>b@gmail.com</td>
                        <td>0911111111</td>
                    </tr>
                </tbody>
            </table>
        </div>


    </div>




    <!-- MODAL ADD PRODUCT -->
    <div class="modal fade" id="addModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5>Thêm sản phẩm</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Tên sản phẩm</label>
                    <input class="form-control mb-2">

                    <label>Giá</label>
                    <input type="number" class="form-control mb-2">

                    <label>Hãng</label>
                    <select class="form-control mb-2">
                        <option>Apple</option>
                        <option>Samsung</option>
                        <option>Xiaomi</option>
                    </select>

                    <label>Ảnh</label>
                    <input type="file" class="form-control">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Lưu</button>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL EDIT PRODUCT -->
    <div class="modal fade" id="editModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5>Sửa sản phẩm</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Tên sản phẩm</label>
                    <input class="form-control mb-2" value="iPhone 15 Pro Max">

                    <label>Giá</label>
                    <input type="number" class="form-control mb-2" value="29990000">

                    <label>Hãng</label>
                    <select class="form-control mb-2">
                        <option selected>Apple</option>
                        <option>Samsung</option>
                    </select>

                    <label>Chọn ảnh mới</label>
                    <input type="file" class="form-control">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Cập nhật</button>
                </div>

            </div>
        </div>
    </div>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function showSection(id) {
            document.querySelectorAll(".section").forEach(s => s.classList.remove("active"));
            document.getElementById(id).classList.add("active");

            document.querySelectorAll("#sidebar a").forEach(a => a.classList.remove("active"));
            event.target.classList.add("active");
        }

        function openAddModal() {
            new bootstrap.Modal(document.getElementById('addModal')).show();
        }

        function openEditModal() {
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>

</body>
</html>
