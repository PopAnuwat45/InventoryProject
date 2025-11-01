<?php
    include('server.php');
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
            <h5 class="mb-3 fw-bold">ประวัติการเคลื่อนไหว</h5>
            <div class="row g-2">
                <?php include('menu_buttons.php') ?>
            </div>
        </div>

        <!-- Section: Transaction -->
        <h5 class="mb-3 fw-bold">ดูประวัติการเคลื่อนไหวของสินค้า</h5>
        <!-- Section: Transaction -->
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">ค้นหารหัสสินค้า</label>
                <div class="product-search-wrapper" style="position: relative;">
                    <input type="text" name="search_product" class="form-control product-search" placeholder="พิมพ์รหัสสินค้า เช่น P0001" autocomplete="off" required>
                    <div class="product-list" style="position: absolute; z-index: 10; width: 100%; background: #fff; border: 1px solid #ddd; display: none;"></div>
                </div>
            </div>
        </form>

<?php
if (isset($_POST['search_product'])) {
    $search = $_POST['search_product'];

    // ดึง product_id จาก product_id_full
    $sql_product = "
    SELECT 
        p.product_id, 
        p.product_id_full,
        p.product_name, 
        p.unit,
        l.location_full_id
    FROM product p
    LEFT JOIN location l ON p.location_id = l.location_id
    WHERE p.product_id_full = ?
    ";
    $stmt_product = $conn->prepare($sql_product);
    $stmt_product->bind_param("s", $search);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();
    
    if ($result_product->num_rows > 0) {
        $product = $result_product->fetch_assoc();
        $product_id = $product['product_id'];
        $product_name = $product['product_name'];

        echo "<h5>สินค้า: {$product['product_id_full']} - {$product_name}</h5>";
        echo "<h5>ตำแหน่งที่เก็บ: {$product['location_full_id']}</h5>";

        // ดึงยอดคงเหลือสินค้าปัจจุบัน
    $sql_balance = "SELECT 
    IFNULL(SUM(CASE WHEN movement_type='IN' THEN movement_qty ELSE 0 END),0)
    - IFNULL(SUM(CASE WHEN movement_type='OUT' THEN movement_qty ELSE 0 END),0) AS stock_balance
    FROM stock_movement
    WHERE product_id = ?
    ";
    $stmt_balance = $conn->prepare($sql_balance);
    $stmt_balance->bind_param("i", $product_id);
    $stmt_balance->execute();
    $result_balance = $stmt_balance->get_result();
    $row_balance = $result_balance->fetch_assoc();
    $current_stock = $row_balance['stock_balance'];



        // ดึงข้อมูล Stock Movement ของสินค้า
    $sql_movement = "
    SELECT 
        sm.movement_date, 
        sm.movement_type, 
        sm.ref_type, 
        sm.ref_id, 
        sm.movement_qty, 
        sm.created_by,
        po.po_number,
        so.so_number,
        l.location_full_id
    FROM stock_movement sm
    LEFT JOIN purchase_order po 
        ON (sm.ref_type = 'PO' AND sm.ref_id = po.po_id)
    LEFT JOIN sale_order so 
        ON (sm.ref_type = 'SO' AND sm.ref_id = so.so_id)
    LEFT JOIN product p 
        ON sm.product_id = p.product_id
    LEFT JOIN location l 
        ON p.location_id = l.location_id
    WHERE sm.product_id = ?
    ORDER BY sm.movement_date ASC, sm.movement_id ASC
    ";
    $stmt_movement = $conn->prepare($sql_movement);
    $stmt_movement->bind_param("i", $product_id);
    $stmt_movement->execute();
    $result_movement = $stmt_movement->get_result();

        if ($result_movement->num_rows > 0) {
            echo '<div class="table-responsive">
                    <table class="table table-hover align-middle table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th>วันที่</th>
                                <th>ประเภท</th>
                                <th>อ้างอิง</th>
                                <th>เลขใบทำการ</th>
                                <th>รับเข้า</th>
                                <th>เบิกออก</th>
                                <th>ผู้ทำรายการ</th>
                            </tr>
                        </thead>
                        <tbody>';
            while ($row = $result_movement->fetch_assoc()) {

                // ✅ เลือกเลขใบตามประเภท IN/OUT
                $ref_number = ($row['movement_type'] === 'IN') ? $row['po_number'] : $row['so_number'];
                if (empty($ref_number)) $ref_number = '-';

                // ✅ แยกคอลัมน์รับเข้า / จ่ายออก
                $qty_in  = ($row['movement_type'] === 'IN')  ? $row['movement_qty'] : '';
                $qty_out = ($row['movement_type'] === 'OUT') ? $row['movement_qty'] : '';

                // ✅ ถ้าเป็น OUT ให้ใส่เครื่องหมายลบหน้า qty
                $qty_display = ($row['movement_type'] === 'OUT') ? '-' . $row['movement_qty'] : $row['movement_qty'];

                echo "<tr>
                        <td>{$row['movement_date']}</td>
                        <td>{$row['movement_type']}</td>
                        <td>{$row['ref_type']}</td>
                        <td>{$ref_number}</td>
                        <td class='text-success'>{$qty_in}</td>
                        <td class='text-danger'>{$qty_out}</td>
                        <td>{$row['created_by']}</td>
                      </tr>";
            }
            echo '    </tbody>
                    </table>
                  </div>';
            
            echo "<p class='fw-bold mt-2'>ยอดคงเหลือล่าสุด: {$current_stock} {$product['unit']}</p>";

        } else {
            echo "<p>ยังไม่มีการเคลื่อนไหวของสินค้านี้</p>";
        }

        $stmt_movement->close();
    } else {
        echo "<p>ไม่พบรหัสสินค้านี้</p>";
    }

    $stmt_product->close();
}
?>


        

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

    // ใส่รหัสสินค้าลงช่องค้นหา
        parent.find(".product-search").val(product_code);

    // ซ่อนกล่องรายการ
        $(this).parent().hide();

    // ✅ ส่งฟอร์มอัตโนมัติหลังจากเลือกสินค้า
        parent.closest("form").submit();
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