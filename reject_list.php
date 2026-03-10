<?php
include('server.php');

$activeTab = $_GET['tab'] ?? 'gr';

$current_page = basename($_SERVER['PHP_SELF']);

/* ===============================
   ดึงข้อมูล GR Reject
================================ */
$sql_reject_gr = "SELECT 
            gr_id,
            gr_number,
            gr_date,
            ref_doc_number,
            created_by
        FROM goods_receipt
        WHERE gr_status = 'Reject'
        ORDER BY gr_date DESC";
$result_gr = $conn->query($sql_reject_gr);
$gr_reject_count = $result_gr->num_rows;

/* ===============================
   ดึงข้อมูล GI Reject
================================ */
$sql_reject_gi = "SELECT 
            gi_id,
            gi_number,
            gi_date,
            ref_so_number,
            created_by
        FROM goods_issue
        WHERE gi_status = 'Reject'
        ORDER BY gi_date DESC";
$result_gi = $conn->query($sql_reject_gi);
$gi_reject_count = $result_gi->num_rows;

$total_Reject_count = $gr_reject_count + $gi_reject_count;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการที่ไม่ได้รับอนุมัติ</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="d-flex flex-column min-vh-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark main-nav">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="img/logo.jpg" alt="" width="100" height="30" class="me-2">
            ระบบคลังสินค้า
        </a>
    </div>
</nav>

<div class="container my-4">

    <!-- Menu -->
    <div class="menu-section mb-4">
        <h5 class="mb-3 fw-bold">
            รายการที่ไม่ได้รับอนุมัติ
            
        </h5>
        <div class="row g-2">
            <?php include('menu_buttons.php')?>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3 fw-bold">
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'gr' ? 'active' : '' ?>"
                data-bs-toggle="tab" data-bs-target="#tab-gr">
                การรับสินค้า
                <?php if ($gr_reject_count > 0): ?>
                    <span class="badge bg-danger ms-1"><?= $gr_reject_count ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'gi' ? 'active' : '' ?>"
                data-bs-toggle="tab" data-bs-target="#tab-gi">
                การเบิกสินค้า
                <?php if ($gi_reject_count > 0): ?>
                    <span class="badge bg-danger ms-1"><?= $gi_reject_count ?></span>
                <?php endif; ?>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ===============================
             TAB: GR
        ================================ -->
        <div class="tab-pane fade <?= $activeTab === 'gr' ? 'show active' : '' ?>" id="tab-gr">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>เลขที่ GR</th>
                            <th>วันที่</th>
                            <th>เอกสารอ้างอิง</th>
                            <th>ผู้ทำรายการ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($gr_reject_count > 0): ?>
                            <?php while ($gr = $result_gr->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($gr['gr_number']) ?></td>
                                    <td><?= htmlspecialchars($gr['gr_date']) ?></td>
                                    <td><?= htmlspecialchars($gr['ref_doc_number']) ?></td>
                                    <td><?= htmlspecialchars($gr['created_by']) ?></td>
                                    <td class="text-center">
                                        <a href="edit_gr.php?gr_id=<?= $gr['gr_id'] ?>"
                                           class="btn btn-sm btn-warning">
                                            แก้ไข
                                        </a>
                                        <a href="cancel_gr.php?gr_id=<?= $gr['gr_id'] ?>&tab=gr"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('ต้องการยกเลิกรายการนี้หรือไม่?');">
                                            ยกเลิก
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    ไม่มีรายการที่ไม่ได้รับอนุมัติ
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===============================
             TAB: GI
        ================================ -->
        <div class="tab-pane fade <?= $activeTab === 'gi' ? 'show active' : '' ?>" id="tab-gi">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>เลขที่ GI</th>
                            <th>วันที่</th>
                            <th>SO อ้างอิง</th>
                            <th>ผู้ทำรายการ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($gi_reject_count > 0): ?>
                            <?php while ($gi = $result_gi->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($gi['gi_number']) ?></td>
                                    <td><?= htmlspecialchars($gi['gi_date']) ?></td>
                                    <td><?= htmlspecialchars($gi['ref_so_number']) ?></td>
                                    <td><?= htmlspecialchars($gi['created_by']) ?></td>
                                    <td class="text-center">
                                        <a href="edit_gi.php?gi_id=<?= $gi['gi_id'] ?>"
                                           class="btn btn-sm btn-warning">
                                            แก้ไข
                                        </a>

                                        <a href="cancel_gi.php?gi_id=<?= $gi['gi_id'] ?>&tab=gi"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('ต้องการยกเลิกรายการนี้หรือไม่?');">
                                            ยกเลิก
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    ไม่มีรายการที่ไม่ได้รับอนุมัติ
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<footer class="text-center py-3 mt-auto footer-bg">
    <small>© 2025 บริษัท มาคตางค์ จำกัด | ระบบคลังสินค้า</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
