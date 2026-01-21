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

            echo "<script>alert('ขออนุมัติใบรับสินค้าเรียบร้อยแล้ว'); window.location='create_gr.php';</script>";
        }

        $stmt_gr->close();
    }

    $conn->close();
}
?>
