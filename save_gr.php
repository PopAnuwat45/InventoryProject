<?php
include 'server.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $action = $_POST['action'];

    /* =====================================================
       ACTION : SAVE (สร้าง GR / Pending)
    ===================================================== */
    if ($action === 'save') {

        $gr_id = $_POST['gr_id'];
        $gr_number = $_POST['gr_number'];
        $gr_date = $_POST['gr_date'];

        $ref_doc_number = $_POST['ref_doc_number'];
        $ref_doc_date = $_POST['ref_doc_date'];

        $product_ids = $_POST['product_id'];
        $gr_qtys = $_POST['gr_qty'];

        $created_by = $_POST['created_by'];

        // ===== Insert goods_receipt (Pending) =====
        $sql_gr = "INSERT INTO goods_receipt
            (gr_id, gr_number, gr_date, ref_doc_number, ref_doc_date, created_by, gr_status)
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt_gr = $conn->prepare($sql_gr);
        $stmt_gr->bind_param(
            "isssss",
            $gr_id,
            $gr_number,
            $gr_date,
            $ref_doc_number,
            $ref_doc_date,
            $created_by
        );

        if ($stmt_gr->execute()) {

            // ===== Insert goods_receipt_item =====
            $sql_item = "INSERT INTO goods_receipt_item
                (gr_item_id, gr_id, product_id, gr_qty)
                VALUES (?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);

            for ($i = 0; $i < count($product_ids); $i++) {

                $product_id = $product_ids[$i];
                $qty = $gr_qtys[$i];

                $result = $conn->query("SELECT MAX(gr_item_id) AS last_id FROM goods_receipt_item");
                $row = $result->fetch_assoc();
                $gr_item_id = $row['last_id'] ? $row['last_id'] + 1 : 1;

                $stmt_item->bind_param(
                    "iiii",
                    $gr_item_id,
                    $gr_id,
                    $product_id,
                    $qty
                );
                $stmt_item->execute();
            }

            $stmt_item->close();

            echo "<script>alert('บันทึกใบรับสินค้า (Pending) เรียบร้อยแล้ว'); window.location='create_gr.php';</script>";
        }

        $stmt_gr->close();
    }

    /* =====================================================
       ACTION : APPROVE (อนุมัติ + กระทบสต๊อก)
    ===================================================== */
    if ($action === 'approve') {

        $gr_id = $_POST['gr_id'];
        $approve_by = $_POST['approve_by'];

        // ===== Update Status =====
        $sql_update = "UPDATE goods_receipt
            SET gr_status = 'Approved', approve_by = ?
            WHERE gr_id = ? AND gr_status = 'Pending'";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $approve_by, $gr_id);
        $stmt_update->execute();

        if ($stmt_update->affected_rows > 0) {

            // ===== ดึงรายการสินค้า =====
            $items = $conn->query("
                SELECT product_id, gr_qty
                FROM goods_receipt_item
                WHERE gr_id = $gr_id
            ");

            while ($row = $items->fetch_assoc()) {

                $result = $conn->query("SELECT MAX(movement_id) AS last_id FROM stock_movement");
                $last = $result->fetch_assoc();
                $movement_id = $last['last_id'] ? $last['last_id'] + 1 : 1;

                $conn->query("
                    INSERT INTO stock_movement
                    (movement_id, product_id, movement_date, movement_type, ref_type, ref_id, movement_qty, created_by)
                    VALUES
                    ($movement_id, {$row['product_id']}, NOW(), 'IN', 'GR', $gr_id, {$row['gr_qty']}, '$approve_by')
                ");
            }

            echo "<script>alert('อนุมัติใบรับสินค้าเรียบร้อยแล้ว'); window.location='approve_gr.php';</script>";
        }

        $stmt_update->close();
    }

    $conn->close();
}
?>
