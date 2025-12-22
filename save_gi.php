<?php
include 'server.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ดึงค่าจากฟอร์ม
    $gi_id = $_POST['gi_id'];
    $gi_number = $_POST['gi_number'];
    $gi_date = $_POST['gi_date'];
    
    $ref_so_number = $_POST['ref_so_number'];
    $ref_so_date = $_POST['ref_so_date'];

    $product_ids = $_POST['product_id'];       // array
    $gi_qtys = $_POST['gi_qty'];               // array

    $created_by = 'admin'; // ชั่วคราว

    // ===== Insert ข้อมูลลง goods_issue =====
    $sql_gi = "INSERT INTO goods_issue (gi_id, gi_number, gi_date, ref_so_number, ref_so_date, created_by) 
               VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_gi = $conn->prepare($sql_gi);
    $stmt_gi->bind_param("isssss", $gi_id, $gi_number, $gi_date, $ref_so_number, $ref_so_date, $created_by);

    if ($stmt_gi->execute()) {

        // ===== Insert รายการสินค้า =====
        $sql_item = "INSERT INTO goods_issue_item (gi_item_id, gi_id, product_id, gi_qty) 
                     VALUES (?, ?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);

        // ===== Insert Stock Movement =====
        $sql_movement = "INSERT INTO stock_movement 
            (movement_id, product_id, movement_date, movement_type, ref_type, ref_id, movement_qty, created_by)
            VALUES (?, ?, NOW(), 'OUT', 'GI', ?, ?, ?)";
        $stmt_movement = $conn->prepare($sql_movement);

        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $qty = $gi_qtys[$i];

            // ===== สร้าง gi_item_id =====
            $result_last_item = $conn->query("SELECT MAX(gi_item_id) AS last_id FROM goods_issue_item");
            $row_last_item = $result_last_item->fetch_assoc();
            $gi_item_id = $row_last_item['last_id'] ? $row_last_item['last_id'] + 1 : 1;

            // Insert goods_issue_item
            $stmt_item->bind_param("iiii", $gi_item_id, $gi_id, $product_id, $qty);
            $stmt_item->execute();

            // ===== สร้าง movement_id =====
            $result_last_move = $conn->query("SELECT MAX(movement_id) AS last_id FROM stock_movement");
            $row_last_move = $result_last_move->fetch_assoc();
            $movement_id = $row_last_move['last_id'] ? $row_last_move['last_id'] + 1 : 1;

            // Insert Stock Movement
            $stmt_movement->bind_param("iiiis", $movement_id, $product_id, $gi_id, $qty, $created_by);
            $stmt_movement->execute();
        }

        $stmt_item->close();
        $stmt_movement->close();

        echo "<script>alert('เปิดใบสั่งขายเรียบร้อยแล้วและบันทึกการเบิกสินค้าออกคลัง'); window.location='create_gi.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด ทำรายการไม่สำเร็จ'); window.location='create_gi.php';</script>" . $stmt_so->error;
    }

    $stmt_so->close();
    $conn->close();
}
?>
