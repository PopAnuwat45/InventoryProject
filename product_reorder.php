<?php
include('check_login.php');
include('server.php');

$name = $_SESSION['name'] ?? '';
$type = $_SESSION['type']?? '';

$current_page = basename($_SERVER['PHP_SELF']);


/* =====================================
   STOCK + REORDER ALERT
===================================== */

$sql = "

SELECT
    p.product_id,
    p.product_id_full,
    p.product_name,
    p.reorder_point,
    u.unit_name,
    l.location_full_id,

    IFNULL(SUM(
        CASE
            WHEN sm.movement_type = 'IN' THEN sm.movement_qty
            WHEN sm.movement_type = 'OUT' THEN -sm.movement_qty
        END
    ),0) AS current_stock

FROM product p

LEFT JOIN stock_movement sm
ON p.product_id = sm.product_id

LEFT JOIN unit u
ON p.unit_id = u.unit_id

LEFT JOIN location l
ON p.location_id = l.location_id

GROUP BY
    p.product_id

HAVING current_stock <= p.reorder_point

ORDER BY p.product_id_full ASC

";

$result = $conn->query($sql);

$total_reorder_count = $result->num_rows;

?>


<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>ระบบคลังสินค้า</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">

</head>

<body class="d-flex flex-column min-vh-100">


<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg navbar-dark main-nav">

<div class="container">

<a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">

<img src="img/logo.jpg" width="100" height="30" class="me-2">

ระบบคลังสินค้า

</a>

<!-- ขวา -->
        <div class="ms-auto d-flex align-items-center">

            <!-- User Info -->
            <div class="d-flex align-items-center text-white me-3">
                <i class="bi bi-person-circle fs-4 me-2"></i>
                <span>
                    <?php 
                        echo ($name ?? 'Guest') . 
                            ' (' . ($type ?? '-') . ')'; 
                    ?>
                </span>
            </div>

            <!-- Logout -->
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                ออกจากระบบ
            </a>
        </div>

</div>

</nav>



<div class="container my-4">

<!-- MENU -->

<div class="menu-section mb-4">

<h5 class="mb-3 fw-bold">
สินค้าถึงจุดสั่งซื้อซ้ำ
</h5>

<div class="row g-2">
<?php include('menu_buttons.php'); ?>
</div>

</div>



<!-- ALERT -->

<div class="alert alert-warning">

พบสินค้าที่ถึงจุดสั่งซื้อ

<strong><?= $total_reorder_count ?></strong>

รายการ

</div>



<!-- TABLE -->

<div class="table-responsive">

<table class="table table-hover table-striped align-middle">

<thead class="table-danger">

<tr>

<th>รหัสสินค้า</th>
<th>ชื่อสินค้า</th>
<th>ตำแหน่ง</th>
<th class="text-center">คงเหลือ</th>
<th class="text-center">ROP</th>
<th class="text-center">หน่วย</th>

</tr>

</thead>


<tbody>

<?php if($result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td>
<?= htmlspecialchars($row['product_id_full']) ?>
</td>

<td>
<?= htmlspecialchars($row['product_name']) ?>
</td>

<td>
<?= htmlspecialchars($row['location_full_id']) ?>
</td>

<td class="text-center">

<span class="badge bg-danger">

<?= $row['current_stock'] ?>

</span>

</td>

<td class="text-center">
<?= $row['reorder_point'] ?>
</td>

<td class="text-center">
<?= $row['unit_name'] ?>
</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="6" class="text-center text-muted">

ไม่มีสินค้าที่ต้องสั่งซื้อ

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>



<!-- FOOTER -->

<footer class="text-center py-3 mt-auto footer-bg">

<small>
© 2025 บริษัท มาคตางค์ จำกัด | ระบบคลังสินค้า
</small>

</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>