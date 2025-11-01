<?php
        // connect to DB
    include('server.php');

    // ดึงข้อมูลสินค้า
        $sql = "SELECT 
            product.product_id_full,
            product.product_name,
            product.unit,
            location.location_full_id
        FROM product
        LEFT JOIN location 
            ON product.location_id = location.location_id
        ORDER BY product.product_id_full, location.location_full_id";

        $result = $conn->query($sql);

?>
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
                <?php include('menu_buttons.php')?>
            </div>
        </div>

        <!-- Section: Inventory Table -->
        <div class="inventory-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">รายการสินค้าในคลัง</h5>
                <input type="text" class="form-control w-50 w-md-25" placeholder="🔍 ค้นหาสินค้าจากรหัสที่นี่...">
            </div>

            <?php
            if ($result->num_rows > 0){
            echo '<div class="table-responsive">
                <table class="table table-hover align-middle table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>รหัสสินค้า</th>
                            <th>ชื่อสินค้า</th>
                            <th>จำนวนคงเหลือ</th>
                            <th>หน่วยนับ</th>
                            <th>ตำแหน่งจัดเก็บ</th>                            
                        </tr>
                    </thead>
                    <tbody>';
            while($row = $result->fetch_assoc()) {
                echo'   <tr>
                            <td>' . htmlspecialchars($row['product_id_full']) . '</td>
                            <td>' . htmlspecialchars($row['product_name']) . '</td>
                            <td>รอข้อมูล</td>
                            <td>' . htmlspecialchars($row['unit']) . '</td>
                            <td>' . htmlspecialchars($row['location_full_id']) . '</td>                             
                        </tr>';
            }                                
            echo'   </tbody>
                </table>
            </div>';
            } else {
                echo "ไม่มีข้อมูลสินค้า";
            }

            $conn->close();
            ?>
            


        </div>

    </div>

    <!-- Footer -->
    <footer class="text-center py-3 mt-auto footer-bg">
        <small>© 2025 บริษัท มาคตางค์ จำกัด | ระบบคลังสินค้า</small>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
