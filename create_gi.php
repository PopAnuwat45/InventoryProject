<?php
    include('server.php');

    $created_by = 'admin'; // ชั่วคราว
    
    // ดึงปีและเดือนปัจจุบัน
    $year = date('y'); // เช่น 25
    $month = date('m'); // เช่น 10

    // หาเลขล่าสุดของเดือนนี้
    $sql_last_gi = "
        SELECT gi_id, gi_number
        FROM goods_issue
        WHERE gi_number LIKE 'GI{$year}{$month}-%'
        ORDER BY gi_id DESC
        LIMIT 1
    ";
    $result_last_gi = $conn->query($sql_last_gi);

    if ($result_last_gi && $result_last_gi->num_rows > 0) {
        $row_last_gi = $result_last_gi->fetch_assoc();
        $last_number = (int)substr($row_last_gi['gi_number'], -4);
        $next_number = $last_number + 1;
    } else {
        $next_number = 1;
    }

    // วนลูปตรวจสอบไม่ให้เลขซ้ำ
    do {
        $new_gi_number = "GI" . $year . $month . "-" . str_pad($next_number, 4, "0", STR_PAD_LEFT);
        $sql_check = "SELECT COUNT(*) AS cnt FROM goods_issue WHERE gi_number = '$new_gi_number'";
        $result_check = $conn->query($sql_check);
        $row_check = $result_check->fetch_assoc();
        if ($row_check['cnt'] > 0) {
            $next_number++;
        } else {
            break;
        }
    } while (true);

    // หา gi_id ใหม่ (ไม่ได้ AUTO_INCREMENT)
    $sql_last_id = "SELECT gi_id FROM goods_issue ORDER BY gi_id DESC LIMIT 1";
    $result_last_id = $conn->query($sql_last_id);
    if ($result_last_id && $result_last_id->num_rows > 0) {
        $row_last_id = $result_last_id->fetch_assoc();
        $new_gi_id = $row_last_id['gi_id'] + 1;
    } else {
        $new_gi_id = 1;
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
            <h5 class="mb-3 fw-bold">เบิกสินค้าออก</h5>
            <div class="row g-2">
                <?php include('menu_buttons.php') ?>
            </div>
        </div>

        <!-- Section: Create GI -->
        <h5 class="mb-3 fw-bold">ทำรายการเบิกสินค้าออก (Goods_Issue)</h5>

        <form action="save_so.php" method="POST">

        <!-- ผู้ทำรายการ -->
        <div class="mb-3">
            <label for="create_by" class="form-label">ผู้ทำรายการ</label>
            <input type="text" name="created_by" id="created_by" class="form-control" 
                value="<?php echo $created_by; ?>" readonly>
        </div>


        <div class="row">
            <!-- รหัสรายการ GI -->
            <div class="mb-3 col-md-5">
                <label for="gi_number" class="form-label">เลขที่ใบเบิกสินค้าออก (GI Number)</label>
                <input type="text" name="gi_number" id="gi_number" class="form-control" 
                    value="<?php echo $new_gi_number; ?>" readonly>
                <input type="hidden" name="gi_id" value="<?php echo $new_gi_id; ?>">
            </div>

            <!-- วันที่ GI -->
            <div class="mb-3 col-md-5">
                <label for="gi_date" class="form-label">วันที่ใบเบิกสินค้าออก</label>
                <input type="date" name="gi_date" id="gi_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>

        <div class="row">
            <!-- เลขที่ใบ SO อ้างอิง -->
            <div class="mb-3 col-md-5">
                <label for="ref_so_number" class="form-label">เลขที่ใบ SO อ้างอิง</label>
                <input type="text" name="ref_so_number" id="ref_so_number" class="form-control" placeholder ="เลขที่ใบ SO/ใบสั่งขาย">
            </div>
    
            <!-- วันที่ใบ SO อ้างอิง -->
            <div class="mb-3 col-md-5" >
                <label for="ref_so_date" class="form-label">วันที่ใบ SO อ้างอิง</label>
                <input type="date" name="ref_so_date" id="ref_so_date" class="form-control">
            </div>
         </div>

        <!-- ตารางรายการสินค้า -->
        <div class="mb-3">
            <label class="form-label">รายการสินค้า</label>
            <table class="table table-bordered table-striped" id="gi_items_table">
                <thead>
                    <tr>
                        <th>รหัสสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th>จำนวน</th>
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
                                <input type="hidden" name="product_id[]" class="product-id">
                            </div>
                        </td>
                        <td><input type="text" name="so_name[]" class="form-control" required readonly></td>
                        <td><input type="number" name="so_qty[]" class="form-control" min="1" required></td>
                        <td><input type="text" name="unit[]" class="form-control unit-field" readonly></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-outline-success btn-sm" id="add_item_btn">➕ เพิ่มสินค้า</button>
        </div>

        <div class="mb-3 text-end">
            <button type="submit" class="btn btn-primary">บันทึกใบสั่งขาย</button>
        </div>
    </form>

<!-- JS เพิ่ม/ลบแถวสินค้า -->
<script>
document.addEventListener('DOMContentLoaded', function(){
    const addBtn = document.getElementById('add_item_btn');
    const tableBody = document.querySelector('#so_items_table tbody');

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
            const unitInput = e.target.closest('tr').querySelector('input[name="so_unit[]"]');
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
            parent.closest('tr').find('input[name="so_name[]"]').val(product_name);
            parent.closest('tr').find('input[name="unit[]"]').val(unit);

            $(this).parent().hide(); // ซ่อนผลลัพธ์
        });
    });
    </script>

    <!-- jQuery สำหรับ search ลูกค้า -->
    <script>
    $(document).ready(function(){
        // Autocomplete ลูกค้า
        $(document).on("keyup", ".customer-search", function(){
            let query = $(this).val();
            let inputField = $(this);
            let resultBox = $(this).siblings(".customer-list");

            if(query.length >= 2){
                $.ajax({
                    url: "search_customer.php",
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

        $(document).on("click", ".customer-item", function(){
            let customer_id = $(this).data("id");
            let customer_name = $(this).data("name");

            let parent = $(this).closest(".customer-list").parent();
            parent.find(".customer-search").val(customer_name);
            parent.find(".customer-id").val(customer_id);

            $(this).parent().hide();
        });
    });
</script>
</body>
</html>