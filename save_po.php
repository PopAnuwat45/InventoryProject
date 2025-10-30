<?php
include 'server.php'; // เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ดึงค่าจากฟอร์ม
    $po_id = $_POST['po_id'];
    $po_number = $_POST['po_number'];
    $po_date = $_POST['po_date'];
    $supplier_id = $_POST['supplier_id'];

    $product_ids = $_POST['product_id'];       // array
    $po_qtys = $_POST['po_qty'];               // array
    $po_unit_prices = $_POST['po_unit_price']; // array

    $created_by = 'admin'; // ชั่วคราว

    // ===== Insert ข้อมูลลง purchase_order =====
    $sql_po = "INSERT INTO purchase_order (po_id, po_number, po_date, supplier_id, created_by) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt_po = $conn->prepare($sql_po);
    $stmt_po->bind_param("issis", $po_id, $po_number, $po_date, $supplier_id, $created_by);

    if ($stmt_po->execute()) {

        // ===== Insert รายการสินค้า =====
        $sql_item = "INSERT INTO purchase_order_item (po_item_id, po_id, product_id, po_qty, po_unit_price) 
                    VALUES (?, ?, ?, ?, ?)";
        $stmt_item = $conn->prepare($sql_item);

        // ===== Insert Stock Movement =====
        $sql_movement = "INSERT INTO stock_movement 
            (product_id, movement_date, movement_type, ref_type, ref_id, movement_qty, created_by)
            VALUES (?, NOW(), 'IN', 'PO', ?, ?, ?)";
        $stmt_movement = $conn->prepare($sql_movement);

        for ($i = 0; $i < count($product_ids); $i++) {
            $product_id = $product_ids[$i];
            $qty = $po_qtys[$i];
            $unit_price = $po_unit_prices[$i];

            // ===== สร้าง po_item_id =====
            $result_last_item = $conn->query("SELECT MAX(po_item_id) AS last_id FROM purchase_order_item");
            $row_last_item = $result_last_item->fetch_assoc();
            $po_item_id = $row_last_item['last_id'] ? $row_last_item['last_id'] + 1 : 1;

            // Insert รายการสินค้า
            $stmt_item->bind_param("iiiid", $po_item_id, $po_id, $product_id, $qty, $unit_price);
            $stmt_item->execute();

            // Insert Stock Movement
            $stmt_movement->bind_param("iiis", $product_id, $po_id, $qty, $created_by);
            $stmt_movement->execute();
        }

        $stmt_item->close();
        $stmt_movement->close();

        echo "<script>alert('เปิดใบสั่งซื้อเรียบร้อยแล้วและบันทึกการรับสินค้าเข้าคลัง'); window.location='create_po.php';</script>";

    } else {
        echo "<script>alert('เกิดข้อผิดพลาด ทำรายการไม่สำเร็จ'); window.location='create_po.php';</script>" . $stmt_po->error;
    }

    $stmt_po->close();
    $conn->close();
}
?>
