<?php
// connect to DB

include('check_login.php');
include('server.php');

$name = $_SESSION['name'] ?? '';
$type = $_SESSION['type']?? '';

$search = $_GET['search'] ?? '';

$current_page = basename($_SERVER['PHP_SELF']);

// Pagination
$limit = 5; // จำนวนรายการต่อหน้า
$page = $_GET['page'] ?? 1;
$page = (int)$page;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

$search_param = "%{$search}%";

// นับจำนวนสินค้าทั้งหมด
$count_sql = "SELECT COUNT(*) as total
FROM product
WHERE product_id_full LIKE ?
OR product_name LIKE ?";

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("ss", $search_param, $search_param);
$count_stmt->execute();

$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];

$total_pages = ceil($total_rows / $limit);


// ดึงข้อมูลสินค้า
$sql = "SELECT 
    p.product_id_full,
    p.product_name,
    u.unit_name,
    l.location_full_id,
    IFNULL(SUM(CASE WHEN sm.movement_type = 'IN' THEN sm.movement_qty ELSE 0 END),0)
    -
    IFNULL(SUM(CASE WHEN sm.movement_type = 'OUT' THEN sm.movement_qty ELSE 0 END),0)
    AS stock_balance
FROM product p
LEFT JOIN unit u ON p.unit_id = u.unit_id
LEFT JOIN location l ON p.location_id = l.location_id
LEFT JOIN stock_movement sm ON p.product_id = sm.product_id
WHERE p.product_id_full LIKE ?
OR p.product_name LIKE ?
GROUP BY p.product_id
ORDER BY p.product_id_full, l.location_full_id
LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
$stmt->execute();

$result = $stmt->get_result();

$start_item = $offset + 1;
$end_item = min($offset + $limit, $total_rows);

?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ระบบคลังสินค้า (Inventory System)</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>

<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark main-nav">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
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

    <div class="menu-section mb-4">
        <h5 class="mb-3 fw-bold">รายการสินค้าทั้งหมด</h5>
        <div class="row g-2">
            <?php include('menu_buttons.php') ?>
        </div>
    </div>

    <div class="inventory-section">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">รายการสินค้าในคลัง</h5>

            <form method="GET" class="d-flex w-50">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="🔍 ค้นหาสินค้า..."
                    value="<?= htmlspecialchars($search) ?>"
                >
                <button class="btn btn-primary ms-2">ค้นหา</button>
            </form>
        </div>


        <?php if ($result->num_rows > 0): ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-striped">

                <thead class="table-primary">
                    <tr>
                        <th>รหัสสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th>จำนวนคงเหลือ</th>
                        <th>หน่วยนับ</th>
                        <th>ตำแหน่งจัดเก็บ</th>
                    </tr>
                </thead>

                <tbody>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?= htmlspecialchars($row['product_id_full']) ?></td>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= htmlspecialchars($row['stock_balance']) ?></td>
                        <td><?= htmlspecialchars($row['unit_name']) ?></td>
                        <td><?= htmlspecialchars($row['location_full_id']) ?></td>
                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>
        </div>


        <div class="d-flex justify-content-between align-items-center mt-3">

            <div>
                แสดง <?= $start_item ?> - <?= $end_item ?> รายการ จากทั้งหมด <?= $total_rows ?> รายการ
            </div>

            <nav>
                <ul class="pagination mb-0">

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link"
                               href="?search=<?= urlencode($search) ?>&page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>

                    <?php endfor; ?>

                </ul>
            </nav>

        </div>

        <?php else: ?>

            <div class="alert alert-warning">
                ไม่มีข้อมูลสินค้า
            </div>

        <?php endif; ?>

        <?php $conn->close(); ?>

    </div>

</div>

<footer class="text-center py-3 mt-auto footer-bg">
    <small>© 2025 บริษัท มาคตางค์ จำกัด | ระบบคลังสินค้า</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>