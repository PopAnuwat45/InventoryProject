<?php
    include('server.php');

    // ดึงปีและเดือนปัจจุบัน
    $year = date('y'); // เช่น 25
    $month = date('m'); // เช่น 10

    // หาเลขล่าสุดของเดือนนี้
    $sql_last_so = "
        SELECT so_id, so_number
        FROM sale_order
        WHERE so_number LIKE 'SO{$year}{$month}-%'
        ORDER BY so_id DESC
        LIMIT 1
    ";
    $result_last_so = $conn->query($sql_last_so);

    if ($result_last_so && $result_last_so->num_rows > 0) {
        $row_last_so = $result_last_so->fetch_assoc();
        $last_number = (int)substr($row_last_so['so_number'], -4);
        $next_number = $last_number + 1;
    } else {
        $next_number = 1;
    }

    // วนลูปตรวจสอบไม่ให้เลขซ้ำ
    do {
        $new_so_number = "SO" . $year . $month . "-" . str_pad($next_number, 4, "0", STR_PAD_LEFT);
        $sql_check = "SELECT COUNT(*) AS cnt FROM sale_order WHERE so_number = '$new_so_number'";
        $result_check = $conn->query($sql_check);
        $row_check = $result_check->fetch_assoc();
        if ($row_check['cnt'] > 0) {
            $next_number++;
        } else {
            break;
        }
    } while (true);

    // หา so_id ใหม่ (ไม่ได้ AUTO_INCREMENT)
    $sql_last_id = "SELECT so_id FROM sale_order ORDER BY so_id DESC LIMIT 1";
    $result_last_id = $conn->query($sql_last_id);
    if ($result_last_id && $result_last_id->num_rows > 0) {
        $row_last_id = $result_last_id->fetch_assoc();
        $new_so_id = $row_last_id['so_id'] + 1;
    } else {
        $new_so_id = 1;
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
                <?php include('menu_buttons.php') ?>
            </div>
        </div>

        <!-- Section: Transaction -->
        <h5 class="mb-3 fw-bold">ประวัติการเคลื่อนไหวของสินค้า</h5>
        <!-- Section: Transaction -->
        <div class="mb-3">
            <label class="form-label">ค้นหารหัสสินค้า</label>
            <form method="POST" action="">
                <div class="input-group mb-3">
                    <input type="text" name="search_product" class="form-control" placeholder="ใส่รหัสสินค้า เช่น P0001" required>
                    <button class="btn btn-primary" type="submit">ค้นหา</button>
                </div>
            </form>
        </div>

<?php
if (isset($_POST['search_product'])) {
    $search = $_POST['search_product'];

    // ดึง product_id จาก product_id_full
    $sql_product = "SELECT product_id, product_name, product_id_full FROM product WHERE product_id_full = ?";
    $stmt_product = $conn->prepare($sql_product);
    $stmt_product->bind_param("s", $search);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();
    
    if ($result_product->num_rows > 0) {
        $product = $result_product->fetch_assoc();
        $product_id = $product['product_id'];
        $product_name = $product['product_name'];

        echo "<h5>ประวัติการเคลื่อนไหวของสินค้า: {$product['product_id_full']} - {$product_name}</h5>";

        // ดึงข้อมูล Stock Movement ของสินค้านี้
        $sql_movement = "SELECT movement_date, movement_type, ref_type, ref_id, movement_qty, created_by
                         FROM stock_movement
                         WHERE product_id = ?
                         ORDER BY movement_date ASC, movement_id ASC";
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
                                <th>เลขใบ</th>
                                <th>จำนวน</th>
                                <th>ผู้ทำรายการ</th>
                            </tr>
                        </thead>
                        <tbody>';
            while ($row = $result_movement->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['movement_date']}</td>
                        <td>{$row['movement_type']}</td>
                        <td>{$row['ref_type']}</td>
                        <td>{$row['ref_id']}</td>
                        <td>{$row['movement_qty']}</td>
                        <td>{$row['created_by']}</td>
                      </tr>";
            }
            echo '    </tbody>
                    </table>
                  </div>';
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