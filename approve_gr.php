<?php
include('server.php');

/* ===============================
   กำหนดผู้อนุมัติ (โหมดทดสอบ)
================================ */
$approved_by = 'admin';

/* ===============================
   รับค่า gr_id
================================ */
if (!isset($_GET['gr_id'])) {
    die('ไม่พบข้อมูลใบรับสินค้า');
}
$gr_id = intval($_GET['gr_id']);

/* ===============================
   ดึงข้อมูลหัว GR
================================ */
$sql_gr = "SELECT * FROM goods_receipt WHERE gr_id = ?";
$stmt_gr = $conn->prepare($sql_gr);
$stmt_gr->bind_param("i", $gr_id);
$stmt_gr->execute();
$result_gr = $stmt_gr->get_result();

if ($result_gr->num_rows === 0) {
    die('ไม่พบข้อมูลใบรับสินค้า');
}
$gr = $result_gr->fetch_assoc();

/* ===============================
   ดึงรายการสินค้า
================================ */
$sql_items = "SELECT 
                gri.product_id,
                gri.gr_qty,
                p.product_id_full,
                p.product_name,
                u.unit_name
            FROM goods_receipt_item gri
            LEFT JOIN unit u ON p.unit_id = u.unit_id
            JOIN product p ON gri.product_id = p.product_id
            WHERE gri.gr_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $gr_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

$items = [];
while ($row = $result_items->fetch_assoc()) {
    $items[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $gr['gr_status'] === 'Pending') {

    $conn->begin_transaction();

    try {

        /* ===============================
           กรณีกด "ไม่อนุมัติ"
        ================================ */
        if (isset($_POST['reject'])) {

            $sql_reject = "UPDATE goods_receipt
                           SET gr_status = 'Reject'
                           WHERE gr_id = ? AND gr_status = 'Pending'";
            $stmt_reject = $conn->prepare($sql_reject);
            $stmt_reject->bind_param("i", $gr_id);
            $stmt_reject->execute();

            if ($stmt_reject->affected_rows === 0) {
                throw new Exception('รายการนี้ถูกดำเนินการไปแล้ว');
            }

            $conn->commit();

            echo "<script>
                    alert('ไม่อนุมัติใบรับสินค้าเรียบร้อยแล้ว');
                    window.location='approval_requests.php';
                  </script>";
            exit;
        }

        /* ===============================
           กรณีกด "อนุมัติ"
        ================================ */
        if (isset($_POST['approve'])) {

            // Update สถานะ GR
            $sql_update = "UPDATE goods_receipt
                           SET gr_status = 'Approve', approved_by = ?
                           WHERE gr_id = ? AND gr_status = 'Pending'";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $approved_by, $gr_id);
            $stmt_update->execute();

            if ($stmt_update->affected_rows === 0) {
                throw new Exception('รายการนี้ถูกอนุมัติไปแล้ว');
            }

            // ดึง movement_id ล่าสุด
            $result = $conn->query("SELECT MAX(movement_id) AS last_id FROM stock_movement");
            $row = $result->fetch_assoc();
            $movement_id = $row['last_id'] ?? 0;

            // Insert stock movement
            $sql_move = "INSERT INTO stock_movement
                (movement_id, product_id, movement_date, movement_type, ref_type, ref_id, movement_qty, created_by)
                VALUES (?, ?, NOW(), 'IN', 'GR', ?, ?, ?)";
            $stmt_move = $conn->prepare($sql_move);

            foreach ($items as $item) {
                $movement_id++;
                $stmt_move->bind_param(
                    "iiiis",
                    $movement_id,
                    $item['product_id'],
                    $gr_id,
                    $item['gr_qty'],
                    $approved_by
                );
                $stmt_move->execute();
            }

            $conn->commit();

            echo "<script>
                    alert('อนุมัติใบรับสินค้าเรียบร้อยแล้ว');
                    window.location='approval_requests.php';
                  </script>";
            exit;
        }

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
    <title>ระบบคลังสินค้า (Inventory System)</title>
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

    <h5 class="fw-bold mb-2">รายละเอียดใบรับสินค้า</h5>
    <a href="approval_requests.php" class="btn btn-outline-danger btn-sm mb-3">
        ⬅️ กลับ
    </a>

    <!-- ข้อมูลหัวเอกสาร -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>เลขที่ GR:</strong> <?= $gr['gr_number']; ?></div>
                <div class="col-md-4"><strong>วันที่:</strong> <?= $gr['gr_date']; ?></div>
                <div class="col-md-4">
                    <strong>สถานะ:</strong>
                    <?php
                    $badge = match ($gr['gr_status']) {
                        'Pending' => 'warning',
                        'Approve' => 'success',
                        'Reject'  => 'danger',
                        default   => 'secondary'
                    };
                    ?>
                    <span class="badge bg-<?= $badge ?>">
                        <?= $gr['gr_status']; ?>
                    </span>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4"><strong>เอกสารอ้างอิง:</strong> <?= $gr['ref_doc_number']; ?></div>
                <div class="col-md-4"><strong>ผู้ทำรายการ:</strong> <?= $gr['created_by']; ?></div>
            </div>
        </div>
    </div>

    <!-- ตารางสินค้า -->
    <div class="table-responsive mb-3">
        <table class="table table-striped align-middle">
            <thead class="table-primary">
                <tr>
                    <th>รหัสสินค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>จำนวน</th>
                    <th>หน่วย</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item['product_id_full']; ?></td>
                    <td><?= $item['product_name']; ?></td>
                    <td class="text-success">+<?= $item['gr_qty']; ?></td>
                    <td><?= $item['unit']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ปุ่มอนุมัติ และ ไม่อนุมัติ-->
    <?php if ($gr['gr_status'] === 'Pending'): ?>
    <form method="POST" class="text-end">

        <button type="submit"
                name="approve"
                class="btn btn-success">
            อนุมัติรายการ
        </button>    

        <button type="submit"
                name="reject"
                class="btn btn-danger me-2"
                onclick="return confirm('ยืนยันการไม่อนุมัติรายการนี้?');">
            ไม่อนุมัติ
        </button>

        

</form>
<?php endif; ?>




</div>

<footer class="text-center py-3 mt-auto footer-bg">
    <small>© 2025 บริษัท มาคตางค์ จำกัด | ระบบคลังสินค้า</small>
</footer>

</body>
</html>
