<?php
include 'server.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ดึงค่าจากฟอร์ม
    $so_id = $_POST['so_id'];
    $so_number = $_POST['so_number'];
    $so_date = $_POST['so_date'];
    $customer_id = $_POST['customer_id'];

    $product_ids = $_POST['product_id'];       // array
    $so_qtys = $_POST['so_qty'];               // array
    $so_unit_prices = $_POST['so_unit_price']; // array

    $created_by = 'admin'; // ชั่วคราว

    // ===== Insert ข้อมูลลง sale_order =====
    $sql_so = "INSERT INTO sale_order (so_id, so_number, so_date, customer_id, created_by) 
               VALUES (?, ?, ?, ?, ?)";
    $stmt_so = $conn->prepare($sql_so);
    $stmt_so->bind_param("issis", $so_id, $so_number, $so_date, $customer_id, $created_by);

    if ($stmt_so->execute()) {

        // ===== Insert รายการสินค้า =====
        $sql_item = "INSERT INTO sale_order_item (so_item_id, so_id, product_id, so_qty, so_unit_price) 
                     VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);

        // ===== Insert Stock Movement =====
        $sql_movement = "INSERT INTO stock_movement 
            (movement_id, product_id, movement_date, movement_type, ref_type, ref_id, movement_qty, created_by)
            VALUES (?, ?, NOW(), 'OUT', 'SO', ?, ?, ?)";
        $stmt_movement = $conn->prepare($sql_movement);

        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $qty = $so_qtys[$i];
            $unit_price = $so_unit_prices[$i];

            // ===== สร้าง so_item_id =====
            $result_last_item = $conn->query("SELECT MAX(so_item_id) AS last_id FROM sale_order_item");
            $row_last_item = $result_last_item->fetch_assoc();
            $so_item_id = $row_last_item['last_id'] ? $row_last_item['last_id'] + 1 : 1;

            // Insert sale_order_item
            $stmt_item->bind_param("iiiid", $so_item_id, $so_id, $product_id, $qty, $unit_price);
            $stmt_item->execute();

            // ===== สร้าง movement_id =====
            $result_last_move = $conn->query("SELECT MAX(movement_id) AS last_id FROM stock_movement");
            $row_last_move = $result_last_move->fetch_assoc();
            $movement_id = $row_last_move['last_id'] ? $row_last_move['last_id'] + 1 : 1;

            // Insert Stock Movement
            $stmt_movement->bind_param("iiiis", $movement_id, $product_id, $so_id, $qty, $created_by);
            $stmt_movement->execute();
        }

        $stmt_item->close();
        $stmt_movement->close();

        echo "<script>alert('เปิดใบสั่งขายเรียบร้อยแล้วและบันทึกการเบิกสินค้าออกคลัง'); window.location='create_so.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด ทำรายการไม่สำเร็จ'); window.location='create_so.php';</script>" . $stmt_so->error;
    }

    $stmt_so->close();
    $conn->close();
}
?>
