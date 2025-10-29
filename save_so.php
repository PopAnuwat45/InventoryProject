<?php
include 'server.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ดึงค่าจากฟอร์ม
    $so_id = $_POST['so_id'];
    $so_number = $_POST['so_number'];
    $so_date = $_POST['so_date'];
    $customer_id = $_POST['customer_id'];

    $product_ids = $_POST['product_id'];       // array
    $so_names = $_POST['so_name'];             // array (ชื่อสินค้า)
    $so_qtys = $_POST['so_qty'];               // array
    $so_unit_prices = $_POST['so_unit_price']; // array
    $units = $_POST['unit'];                   // array

    // ===== Insert ข้อมูลลง sale_order =====
    $created_by = 'admin'; // ชั่วคราว
    $sql_so = "INSERT INTO sale_order (so_id, so_number, so_date, customer_id, created_by) 
               VALUES (?, ?, ?, ?, ?)";
    $stmt_so = $conn->prepare($sql_so);
    $stmt_so->bind_param("issis", $so_id, $so_number, $so_date, $customer_id, $created_by);

    if ($stmt_so->execute()) {

        // ===== Insert รายการสินค้า =====
        $sql_item = "INSERT INTO sale_order_item (so_item_id, so_id, product_id, so_qty, so_unit_price) 
                     VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);

        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $qty = $so_qtys[$i];
            $unit_price = $so_unit_prices[$i];

            // ===== สร้าง so_item_id เอง =====
            $result_last_item = $conn->query("SELECT MAX(so_item_id) AS last_id FROM sale_order_item");
            $row_last_item = $result_last_item->fetch_assoc();
            $so_item_id = $row_last_item['last_id'] ? $row_last_item['last_id'] + 1 : 1;

            $stmt_item->bind_param("iiiid", $so_item_id, $so_id, $product_id, $qty, $unit_price);
            $stmt_item->execute();
        }

        $stmt_item->close();

        echo "<script>alert('เปิดใบสั่งขายเรียบร้อยแล้ว'); window.location='create_so.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด ทำรายการไม่สำเร็จ'); window.location='create_so.php';</script>" . $stmt_so->error;
    }

    $stmt_so->close();
    $conn->close();
}
?>
