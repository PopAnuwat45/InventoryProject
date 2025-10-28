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

    // ดึงปีและเดือนปัจจุบัน
    $year = date('y'); // เช่น 68
    $month = date('m'); // เช่น 10

    // ดึงข้อมูลใบ PO ล่าสุด
    $sql_last_po = "SELECT po_id, po_number FROM purchase_order ORDER BY po_id DESC LIMIT 1";
    $result_last_po = $conn->query($sql_last_po);

    if ($result_last_po && $result_last_po->num_rows > 0) {
        $row_last_po = $result_last_po->fetch_assoc();
        $last_id = (int)$row_last_po['po_id'];
        $last_po_number = $row_last_po['po_number'];

        // แยกเลขท้าย เช่น PO6810-0001 → 0001
        $last_number = (int)substr($last_po_number, -4);
        $next_number = $last_number + 1;

        // สร้างรหัสใหม่
        $new_po_id = $last_id + 1;
        $new_po_number = "PO" . $year . $month . "-" . str_pad($next_number, 4, "0", STR_PAD_LEFT);
    } else {
        // ถ้ายังไม่มีข้อมูลเลย
        $new_po_id = 1;
        $new_po_number = "PO" . $year . $month . "-0001";
    }
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
                    <a href ="#" class="btn btn-outline-primary w-100">📦 เปิดใบสั่งซื้อสินค้า</a>
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

    <!-- รหัสใบสั่งซื้อ -->
    <div class="mb-3">
        <label for="po_number" class="form-label">รหัสใบสั่งซื้อ (PO Number)</label>
        <input type="text" name="po_number" id="po_number" class="form-control" 
            value="<?php echo $new_po_number; ?>" readonly>
        <input type="hidden" name="po_id" value="<?php echo $new_po_id; ?>">
    </div>

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
                    <th>รหัสสินค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>จำนวน</th>
                    <th>ราคาต่อหน่วย</th>
                    <th>หน่วยนับ</th>
                    <th>ลบ</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="product-search-wrapper" style="position: relative;">
                        <input type="text" name="product_code[]" class="form-control product-search" placeholder="พิมพ์รหัสสินค้า เช่น P0001" autocomplete="off" required>
                        <div class="product-list"></div>

                        <!-- ที่จะเอาค่าที่ค้นเจอเก็บไว้ (hidden) -->
                        <input type="hidden" name="product_id[]" class="product-id">
                        </div>
                    </td>
                    <td><input type="text" name="po_name[]" class="form-control" require></td>
                    <td><input type="number" name="po_qty[]" class="form-control" min="1" required></td>
                    <td><input type="number" name="po_unit_price[]" class="form-control" min="0" step="0.01" required></td>
                    <td><input type="text" name="unit[]" class="form-control unit-field" readonly></td>
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

    <!-- โค้ดในส่วนของการดึงข้อมูล -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    $(document).ready(function(){
        $(document).on("keyup", ".product-search", function(){
            let query = $(this).val();
            let inputField = $(this);
            let resultBox = $(this).siblings(".product-list");

            if(query.length >= 2){ // เริ่มค้นเมื่อพิมพ์เกิน 2 ตัว
                $.ajax({
                    url: "search_product.php",
                    method: "POST",
                    data: {query: query},
                    success: function(data){
                        resultBox.html(data);
                        resultBox.show();
                    }
                });
            } else{
                resultBox.hide();
            }
        });

        // เมื่อคลิกเลือกรายการที่ค้นเจอ
        $(document).on("click", ".product-item", function(){
            let product_id = $(this).data("id");
            let product_code = $(this).data("code");
            let product_name = $(this).data("name");
            let unit = $(this).data("unit");

            let parent = $(this).closest(".product-list").parent();
            parent.find(".product-search").val(product_code);
            parent.find(".product-id").val(product_id);
            parent.find(".unit-field").val(unit);

            // ✅ หา input ชื่อสินค้าในแถวเดียวกัน แล้วใส่ชื่อ
            parent.closest('tr').find('input[name="po_name[]"]').val(product_name);
            parent.closest('tr').find('input[name="unit[]"]').val(unit);

            $(this).parent().hide(); // ซ่อนผลลัพธ์
        });
    });
    </script>
</body>
</html>