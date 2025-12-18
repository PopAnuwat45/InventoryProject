<?php
include 'server.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ดึงค่าจากฟอร์ม
    $gr_id = $_POST['gr_id'];
    $gr_number = $_POST['gr_number'];
    $gr_date = $_POST['gr_date'];
    $supplier_id = $_POST['supplier_id'];

    $ref_doc_number = $_POST['ref_doc_number'];
    $ref_doc_date = $_POST['ref_doc_date'];

    $product_ids = $_POST['product_id'];       // array
    $gr_qtys = $_POST['gr_qty'];               // array

    $created_by = $_POST['created_by'];

    // ===== Insert ข้อมูลลง goods_receipt =====
    $sql_gr = "INSERT INTO goods_receipt (gr_id, gr_number, gr_date, supplier_id, ref_doc_number, ref_doc_date, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_gr = $conn->prepare($sql_gr);
    $stmt_gr->bind_param("ississs", $gr_id, $gr_number, $gr_date, $supplier_id, $ref_doc_number, $ref_doc_date, $created_by);

    if ($stmt_gr->execute()) {

        // ===== Insert รายการสินค้า =====
        $sql_item = "INSERT INTO goods_receipt_item (gr_item_id, gr_id, product_id, gr_qty) 
                    VALUES (?, ?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);

        // ===== Insert Stock Movement =====
        $sql_movement = "INSERT INTO stock_movement 
            (movement_id, product_id, movement_date, movement_type, ref_type, ref_id, movement_qty, created_by)
            VALUES (?, ?, NOW(), 'IN', 'GR', ?, ?, ?)";
        $stmt_movement = $conn->prepare($sql_movement);

        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $qty = $gr_qtys[$i];

            // ===== สร้าง gr_item_id =====
            $result_last_item = $conn->query("SELECT MAX(gr_item_id) AS last_id FROM goods_receipt_item");
            $row_last_item = $result_last_item->fetch_assoc();
            $gr_item_id = $row_last_item['last_id'] ? $row_last_item['last_id'] + 1 : 1;

            // Insert รายการสินค้า
            $stmt_item->bind_param("iiii", $gr_item_id, $gr_id, $product_id, $qty);
            $stmt_item->execute();

            // ===== สร้าง movement_id =====
            $result_last_move = $conn->query("SELECT MAX(movement_id) AS last_id FROM stock_movement");
            $row_last_move = $result_last_move->fetch_assoc();
            $movement_id = $row_last_move['last_id'] ? $row_last_move['last_id'] + 1 : 1;

            // Insert Stock Movement
            $stmt_movement->bind_param("iiiis", $movement_id, $product_id, $gr_id, $qty, $created_by);
            $stmt_movement->execute();
        }

        $stmt_item->close();
        $stmt_movement->close();

        echo "<script>alert('ทำรายการรับสินค้าเรียบร้อยแล้ว'); window.location='create_gr.php';</script>";

    } else {
        echo "<script>alert('เกิดข้อผิดพลาด ทำรายการไม่สำเร็จ'); window.location='create_gr.php';</script>" . $stmt_gr->error;
    }

    $stmt_gr->close();
    $conn->close();
}
?>
