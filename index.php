<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบคลังสินค้า (Inventory System)</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark main-nav">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
                <img src="img/logo.jpg" alt="" width="100" height="30" class="me-2">
                ระบบคลังสินค้า
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-4">

        <!-- Section: Menu Buttons -->
        <div class="menu-section mb-4">
            <h5 class="mb-3 fw-bold">เมนูหลัก (สำหรับผู้ดูแลระบบ)</h5>
            <div class="row g-2">
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-primary w-100">📦 เปิดใบสั่งสินค้า</button>
                </div>
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-primary w-100">✅ อนุมัติใบสั่งซื้อ</button>
                </div>
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-primary w-100">🚚 รับสินค้าเข้า</button>
                </div>
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-primary w-100">🧾 เปิดใบสั่งขาย</button>
                </div>
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-primary w-100">👤 จัดการผู้ใช้</button>
                </div>
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-primary w-100">🛠 จัดการสินค้า</button>
                </div>
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-primary w-100">📊 ดูสต็อกสินค้า</button>
                </div>
                <div class="col-6 col-md-3">
                    <button class="btn btn-outline-primary w-100">📜 ประวัติการเคลื่อนไหว</button>
                </div>
            </div>
        </div>

        <!-- Section: Inventory Table -->
        <div class="inventory-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">รายการสินค้าในคลัง</h5>
                <input type="text" class="form-control w-50 w-md-25" placeholder="🔍 ค้นหาสินค้า...">
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>รหัสสินค้า</th>
                            <th>ชื่อสินค้า</th>
                            <th>จำนวนคงเหลือ</th>
                            <th>หน่วยนับ</th>
                            <th>ตำแหน่งจัดเก็บ</th>
                            
                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>P001</td>
                            <td>ขวดน้ำดื่ม 500ml</td>
                            <td>120</td>
                            <td>ขวด</td>
                            <td>ชั้น A1</td>                             
                        </tr>    
                        
                       
                        
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="text-center py-3 mt-auto footer-bg">
        <small>© 2025 บริษัท มาคตางค์ จำกัด | ระบบจัดการคลังสินค้า</small>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
