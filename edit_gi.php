<?php
include('server.php');

/* ===============================
   รับค่า gi_id
================================ */
if (!isset($_GET['gi_id'])) {
    die('ไม่พบข้อมูลใบรับสินค้า');
}
$gi_id = intval($_GET['gi_id']);

/* ===============================
   ดึงข้อมูลหัว GI (ต้องเป็น Reject)
================================ */
$sql_gi = "SELECT * FROM goods_issue 
           WHERE gi_id = ? AND gi_status = 'Reject'";
$stmt_gi = $conn->prepare($sql_gi);
$stmt_gi->bind_param("i", $gi_id);
$stmt_gi->execute();
$result_gi = $stmt_gi->get_result();

if ($result_gi->num_rows === 0) {
    die('ไม่พบรายการ หรือรายการไม่อยู่ในสถานะที่แก้ไขได้');
}
$gi = $result_gi->fetch_assoc();

/* ===============================
   ดึงรายการสินค้า
================================ */
$sql_items = "SELECT 
                gii.gi_item_id,
                gii.product_id,
                gii.gi_qty,
                p.product_id_full,
                p.product_name,
                p.unit
            FROM goods_issue_item gii
            JOIN product p ON gii.product_id = p.product_id
            WHERE gii.gi_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $gi_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

/* ===============================
   บันทึกการแก้ไข
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $conn->begin_transaction();

    try {
        foreach ($_POST['gi_item_id'] as $index => $gi_item_id) {
            $qty = intval($_POST['gi_qty'][$index]);

            $sql_update = "UPDATE goods_issue_item
                           SET gi_qty = ?
                           WHERE gi_item_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ii", $qty, $gi_item_id);
            $stmt_update->execute();
        }

        // ส่งกลับไป Pending
        $sql_status = "UPDATE goods_issue
                       SET gi_status = 'Pending'
                       WHERE gi_id = ?";
        $stmt_status = $conn->prepare($sql_status);
        $stmt_status->bind_param("i", $gi_id);
        $stmt_status->execute();

        $conn->commit();

        echo "<script>
                alert('แก้ไขและส่งใบรับสินค้าใหม่เรียบร้อยแล้ว');
                window.location='reject_list.php';
              </script>";
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die('เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขใบเบิกสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark main-nav">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="img/logo.jpg" alt="" width="100" height="30" class="me-2">
            ระบบคลังสินค้า
        </a>
    </div>
</nav>

<div class="container my-4">

    <h5 class="fw-bold mb-2">
        แก้ไขใบเบิกสินค้า (ถูกไม่อนุมัติ)
    </h5>

    <a href="reject_list.php" class="btn btn-outline-danger btn-sm mb-3">
        ⬅️ กลับ
    </a>

    <!-- ข้อมูลหัวเอกสาร -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>เลขที่ GI:</strong> <?= $gi['gi_number']; ?></div>
                <div class="col-md-4"><strong>วันที่:</strong> <?= $gi['gi_date']; ?></div>
                <div class="col-md-4">
                    <strong>สถานะ:</strong>
                    <span class="badge bg-danger">Reject</span>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4"><strong>เอกสารอ้างอิง:</strong> <?= $gi['ref_so_number']; ?></div>
                <div class="col-md-4"><strong>ผู้ทำรายการ:</strong> <?= $gi['created_by']; ?></div>
            </div>
        </div>
    </div>

    <!-- ตารางสินค้า -->
    <form method="POST">

<div class="mb-3">
    <label class="form-label">รายการสินค้า</label>

    <table class="table table-bordered table-striped" id="gi_items_table">
        <thead class="table-warning">
            <tr>
                <th>รหัสสินค้า</th>
                <th>ชื่อสินค้า</th>
                <th width="120">จำนวน</th>
                <th width="120">หน่วย</th>
            </tr>
        </thead>
        <tbody>

        <?php while ($item = $result_items->fetch_assoc()): ?>
            <tr>
                <td>
                    <div class="product-search-wrapper position-relative">
                        <input type="text"
                               name="product_code[]"
                               class="form-control product-search"
                               value="<?= $item['product_id_full']; ?>"
                               autocomplete="off"
                               required
                               readonly>

                        <div class="product-list"></div>

                        <input type="hidden"
                               name="product_id[]"
                               class="product-id"
                               value="<?= $item['product_id']; ?>">

                        <input type="hidden"
                               name="gi_item_id[]"
                               value="<?= $item['gi_item_id']; ?>">
                    </div>
                </td>

                <td>
                    <input type="text"
                           name="gi_name[]"
                           class="form-control"
                           value="<?= $item['product_name']; ?>"
                           readonly>
                </td>

                <td>
                    <input type="number"
                           name="gi_qty[]"
                           class="form-control"
                           min="1"
                           value="<?= $item['gi_qty']; ?>"
                           required>
                </td>

                <td>
                    <input type="text"
                           name="unit[]"
                           class="form-control unit-field"
                           value="<?= $item['unit']; ?>"
                           readonly>
                </td>

            </tr>
        <?php endwhile; ?>

        </tbody>
    </table>

</div>

<div class="text-end">
    <button type="submit"
            class="btn btn-warning"
            onclick="return confirm('ยืนยันการแก้ไขและส่งใหม่?');">
        แก้ไขแล้วส่งใหม่
    </button>
</div>

</div>  



<footer class="text-center py-3 mt-auto footer-bg">
    <small>© 2025 บริษัท มาคตางค์ จำกัด | ระบบคลังสินค้า</small>
</footer>

</body>
</html>
