<?php
include('server.php');

$current_page = basename($_SERVER['PHP_SELF']);

/* =====================================
   Pagination
===================================== */

$limit = 20;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;



/* =====================================
   Search
===================================== */

$search = $_GET['search'] ?? '';


/* =====================================
   Count total products
===================================== */

$count_sql = "SELECT COUNT(*) AS total FROM product";

if ($search != '') {
    $count_sql .= " WHERE product_name LIKE ? OR product_id_full LIKE ?";
}

$stmt = $conn->prepare($count_sql);

if ($search != '') {

    $like = "%{$search}%";
    $stmt->bind_param("ss", $like, $like);

}

$stmt->execute();
$count_result = $stmt->get_result();

$total_rows = $count_result->fetch_assoc()['total'];

$total_pages = ceil($total_rows / $limit);


/* =====================================
   Calculate showing rows
===================================== */

$start_item = $offset + 1;
$end_item   = $offset + $limit;

if ($end_item > $total_rows) {
    $end_item = $total_rows;
}

if ($total_rows == 0) {
    $start_item = 0;
}



/* =====================================
   Query product list
===================================== */

$sql = "
    SELECT
        p.product_id,
        p.product_id_full,
        p.product_name,
        p.reorder_point,

        u.unit_name,
        l.location_full_id,

        IFNULL(
            SUM(
                CASE
                    WHEN sm.movement_type = 'IN'
                        THEN sm.movement_qty
                    WHEN sm.movement_type = 'OUT'
                        THEN -sm.movement_qty
                END
            ),0
        ) AS stock_qty

    FROM product p

    LEFT JOIN unit u
        ON p.unit_id = u.unit_id

    LEFT JOIN location l
        ON p.location_id = l.location_id

    LEFT JOIN stock_movement sm
        ON p.product_id = sm.product_id
";

if ($search != '') {

    $sql .= "
        WHERE
            p.product_name LIKE ?
            OR p.product_id_full LIKE ?
    ";

}

$sql .= "
    GROUP BY p.product_id
    ORDER BY p.product_id ASC
    LIMIT ?
    OFFSET ?
";

$stmt = $conn->prepare($sql);

if ($search != '') {

    $like = "%{$search}%";
    $stmt->bind_param("ssii", $like, $like, $limit, $offset);

} else {

    $stmt->bind_param("ii", $limit, $offset);

}

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>ระบบคลังสินค้า</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

    </div>

</nav>


<div class="container my-4">

    <!-- MENU -->

    <div class="menu-section mb-4">

        <h5 class="mb-3 fw-bold">
            จัดการสินค้า
        </h5>

        <div class="row g-2">
            <?php include('menu_buttons.php'); ?>
        </div>

    </div>


    <!-- SECTION MANAGE PRODUCTS -->

    <div class="inventory-section">

        <h5 class="fw-bold mb-3">
            จัดการสินค้า
        </h5>


        <!-- Top bar -->

        <div class="d-flex justify-content-between mb-3">

            <a href="add_product.php" class="btn btn-primary btn-sm">
                + เพิ่มสินค้า
            </a>

            <form method="GET" class="d-flex" autocomplete="off">

                <input
                    type="text"
                    name="search"
                    class="form-control form-control-sm me-2"
                    placeholder="ค้นหาชื่อสินค้า / รหัสสินค้า"
                    value="<?= htmlspecialchars($search) ?>"
                >

                <button class="btn btn-outline-secondary btn-sm">
                    ค้นหา
                </button>

            </form>

        </div>


        <!-- Table -->

        <div class="table-responsive">

            <table class="table table-striped align-middle">

                <thead class="table-primary">

                    <tr>
                        <th>รหัสสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th>หน่วย</th>
                        <th>ตำแหน่ง</th>
                        <th>ROP</th>
                        <th>สต๊อก</th>
                        <th class="text-center">จัดการ</th>
                    </tr>

                </thead>

                <tbody>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <?php

                        $stock = $row['stock_qty'];
                        $rop   = $row['reorder_point'];

                        $stock_class = 'text-success';
                        $warning     = '';

                        if ($stock <= $rop) {

                            $stock_class = 'text-danger fw-bold';

                            $warning = '<span class="badge bg-danger ms-1">ถึงจุด ROP</span>';

                        }

                        ?>

                        <tr>

                            <td><?= $row['product_id_full'] ?></td>

                            <td><?= $row['product_name'] ?></td>

                            <td><?= $row['unit_name'] ?></td>

                            <td><?= $row['location_full_id'] ?></td>

                            <td><?= $rop ?></td>

                            <td class="<?= $stock_class ?>">

                                <?= $stock ?>

                                <?= $warning ?>

                            </td>

                            <td class="text-center">

                                <a
                                    href="edit_product.php?product_id=<?= $row['product_id'] ?>"
                                    class="btn btn-sm btn-outline-warning"
                                >
                                    แก้ไข
                                </a>


                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        </div>

      

        <!-- Pagination -->

        <div class="d-flex justify-content-between align-items-center mt-3">

         <!-- Showing rows -->

            <div class="text">
                แสดง <?= $start_item ?> - <?= $end_item ?> รายการ
                จากทั้งหมด <?= $total_rows ?> รายการ
            </div>


        <!-- Pagination -->

            <nav>

                <ul class="pagination mb-0">

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">

                            <a
                                class="page-link"
                                href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
                            >
                                <?= $i ?>
                            </a>

                        </li>

                    <?php endfor; ?>

                </ul>

            </nav>

        </div>

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