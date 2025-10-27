<?php
        // connect to DB
    include('server.php');

    // ดึงข้อมูลสินค้า
    $sql = "SELECT 
            product.product_id_full,
            product.product_name,
            product.stock_qty,
            product.unit,
            location.location_full_id,
            product_location.qty AS location_qty
        FROM product
        LEFT JOIN product_location 
            ON product.product_id = product_location.product_id
        LEFT JOIN location 
            ON product_location.location_id = location.location_id
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
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                <img src="img/logo.jpg" alt="" width="100" height="30" class="me-2">
                ระบบคลังสินค้า
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-4">

        <!-- Section: Menu Buttons -->
        <div class="menu-section mb-4">
            <h5 class="mb-3 fw-bold">เปิดใบสั่งสินค้า</h5>
            <div class="row g-2">
                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">📦 เปิดใบสั่งสินค้า</a>
                </div>
                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">✅ อนุมัติใบสั่งซื้อ</a>
                </div>
                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">🚚 รับสินค้าเข้า</a>
                </div>
                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">🧾 เปิดใบสั่งขาย</a>
                </div>
                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">👤 จัดการผู้ใช้</a>
                </div>
                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">🛠 จัดการสินค้า</a>
                </div>
                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">🗄️ จัดการชั้นวางของ</a>
                </div>
                <div class="col-6 col-md-3">
                    <a href ="#" class="btn btn-outline-primary w-100">📜 ประวัติการเคลื่อนไหว</a>
                </div>
            </div>
        </div>

        <!-- Section: Create PO -->
    <h5 class="mb-3 fw-bold">สร้างใบสั่งซื้อสินค้า (Purchase Order)</h5>

    <form action="save_po.php" method="POST">

    <!-- เลือกซัพพลายเออร์ -->
    <div class="mb-3">
        <label for="supplier_id" class="form-label">ผู้จำหน่าย</label>
        <select name="supplier_id" id="supplier_id" class="form-select" required>
            <option value="">-- เลือกผู้จำหน่าย --</option>
            <?php
            $sql_supplier = "SELECT supplier_id, supplier_name FROM supplier ORDER BY supplier_name ASC";
            $result_supplier = $conn->query($sql_supplier);
            if($result_supplier->num_rows > 0){
                while($row_supplier = $result_supplier->fetch_assoc()){
                    echo '<option value="'.$row_supplier['supplier_id'].'">'.htmlspecialchars($row_supplier['supplier_name']).'</option>';
                }
            }
            ?>
        </select>
    </div>

    <!-- วันที่ PO -->
    <div class="mb-3">
        <label for="po_date" class="form-label">วันที่</label>
        <input type="date" name="po_date" id="po_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
    </div>

    <!-- ตารางรายการสินค้า -->
    <div class="mb-3">
        <label class="form-label">รายการสินค้า</label>
        <table class="table table-bordered table-striped" id="po_items_table">
            <thead>
                <tr>
                    <th>สินค้า</th>
                    <th>จำนวน</th>
                    <th>ราคาต่อหน่วย</th>
                    <th>หน่วยนับ</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="product_id[]" class="form-select" required>
                            <option value="">-- เลือกสินค้า --</option>
                            <?php
                            $sql_products = "SELECT product_id, product_id_full, product_name, unit FROM product ORDER BY product_name ASC";
                            $result_products = $conn->query($sql_products);
                            if($result_products->num_rows > 0){
                                while($row_product = $result_products->fetch_assoc()){
                                    echo '<option value="'.$row_product['product_id'].'" data-unit="'.$row_product['unit'].'">'
                                        .htmlspecialchars($row_product['product_id_full'].' - '.$row_product['product_name']).'</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td><input type="number" name="po_qty[]" class="form-control" min="1" required></td>
                    <td><input type="number" name="po_unit_price[]" class="form-control" min="0" step="0.01" required></td>
                    <td><input type="text" name="po_unit[]" class="form-control" readonly></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-outline-success btn-sm" id="add_item_btn">➕ เพิ่มสินค้า</button>
    </div>

    <div class="mb-3 text-end">
        <button type="submit" class="btn btn-primary">บันทึกใบสั่งซื้อ</button>
    </div>

</form>

<!-- JS เพิ่ม/ลบแถวสินค้า -->
<script>
document.addEventListener('DOMContentLoaded', function(){
    const addBtn = document.getElementById('add_item_btn');
    const tableBody = document.querySelector('#po_items_table tbody');

    addBtn.addEventListener('click', function(){
        const firstRow = tableBody.querySelector('tr');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        newRow.querySelector('select').selectedIndex = 0;
        tableBody.appendChild(newRow);
    });

    tableBody.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-row')){
            const rows = tableBody.querySelectorAll('tr');
            if(rows.length > 1){
                e.target.closest('tr').remove();
            } else {
                alert('ต้องมีสินค้าอย่างน้อย 1 รายการ');
            }
        }
    });

    tableBody.addEventListener('change', function(e){
        if(e.target.tagName === 'SELECT'){
            const unitInput = e.target.closest('tr').querySelector('input[name="po_unit[]"]');
            unitInput.value = e.target.selectedOptions[0].dataset.unit || '';
        }
    });
});
</script>

        

    </div>

    <!-- Footer -->
    <footer class="text-center py-3 mt-auto footer-bg">
        <small>© 2025 บริษัท มาคตางค์ จำกัด | ระบบจัดการคลังสินค้า</small>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
