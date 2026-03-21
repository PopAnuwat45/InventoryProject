<?php
include 'server.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $action = $_POST['action'];

    /* =====================================================
       ACTION : SAVE (สร้าง GI / Pending)
    ===================================================== */
    if ($action === 'save') {

        $gi_id = $_POST['gi_id'];
        $gi_number = $_POST['gi_number'];
        $gi_date = $_POST['gi_date'];

        $ref_so_number = $_POST['ref_so_number'];
        $ref_so_date = $_POST['ref_so_date'];

        $product_ids = $_POST['product_id'];
        $gi_qtys = $_POST['gi_qty'];

        $created_by = $_SESSION['username'];

        // ===== Insert goods_issue (Pending) =====
        $sql_gi = "INSERT INTO goods_issue
            (gi_id, gi_number, gi_date, ref_so_number, ref_so_date, created_by, gi_status)
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt_gi = $conn->prepare($sql_gi);
        $stmt_gi->bind_param(
            "isssss",
            $gi_id,
            $gi_number,
            $gi_date,
            $ref_so_number,
            $ref_so_date,
            $created_by
        );

        if ($stmt_gi->execute()) {

            // ===== Insert goods_issue_item =====
            $sql_item = "INSERT INTO goods_issue_item
                (gi_item_id, gi_id, product_id, gi_qty)
                VALUES (?, ?, ?, ?)";
            $stmt_item = $conn->prepare($sql_item);

            for ($i = 0; $i < count($product_ids); $i++) {

                $product_id = $product_ids[$i];
                $qty = $gi_qtys[$i];

                // ===== สร้าง gi_item_id =====
                $result = $conn->query("SELECT MAX(gi_item_id) AS last_id FROM goods_issue_item");
                $row = $result->fetch_assoc();
                $gi_item_id = $row['last_id'] ? $row['last_id'] + 1 : 1;

                $stmt_item->bind_param(
                    "iiii",
                    $gi_item_id,
                    $gi_id,
                    $product_id,
                    $qty
                );
                $stmt_item->execute();
            }

            $stmt_item->close();

            echo "<script>
                alert('ขออนุมัติใบเบิกสินค้าเรียบร้อยแล้ว');
                window.location='create_gi.php';
            </script>";
        }

        $stmt_gi->close();
    }

    $conn->close();
}
?>
