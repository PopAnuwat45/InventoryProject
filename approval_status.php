<?php
include('server.php');

$activeTab = $_GET['tab'] ?? 'gr';

/* ===============================
   ดึงข้อมูล GR Pending
================================ */
$sql_gr = "SELECT 
            gr_id,
            gr_number,
            gr_date,
            ref_doc_number,
            created_by
        FROM goods_receipt
        WHERE gr_status = 'Pending'
        ORDER BY gr_date DESC";
$result_gr = $conn->query($sql_gr);
$gr_count = $result_gr->num_rows;

/* ===============================
   ดึงข้อมูล GI Pending
================================ */
$sql_gi = "SELECT 
            gi_id,
            gi_number,
            gi_date,
            ref_so_number,
            created_by
        FROM goods_issue
        WHERE gi_status = 'Pending'
        ORDER BY gi_date DESC";
$result_gi = $conn->query($sql_gi);
$gi_count = $result_gi->num_rows;

$total_approval_count = $gr_count + $gi_count;
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบคลังสินค้า (Inventory System)</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
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

    <!-- Main Content -->
    <div class="container my-4">

        <!-- Section: Menu Buttons -->
        <div class="menu-section mb-4">
            <h5 class="mb-3 fw-bold">รายการคำขออนุมัติ</h5>
            <div class="row g-2">
                <?php include('menu_buttons.php')?>
            </div>
        </div>

        <!-- Section: Approval Requests -->
<div class="approval-section">

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3 fw-bold">
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'gr' ? 'active' : '' ?>"
             data-bs-toggle="tab" data-bs-target="#tab-gr">
                การรับสินค้า
                <?php if ($gr_count > 0): ?>
                    <span class="badge bg-danger ms-1"><?php echo $gr_count; ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'gi' ? 'active' : '' ?>" 
            data-bs-toggle="tab" data-bs-target="#tab-gi">
                การเบิกสินค้า
                <?php if ($gi_count > 0): ?>
                    <span class="badge bg-danger ms-1"><?php echo $gi_count; ?></span>
                <?php endif; ?>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ===============================
             TAB: การรับสินค้า (GR)
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
                            <th class="text-center">สถานะรายการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($gr_count > 0): ?>
                            <?php while ($gr = $result_gr->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($gr['gr_number']); ?></td>
                                    <td><?php echo htmlspecialchars($gr['gr_date']); ?></td>
                                    <td><?php echo htmlspecialchars($gr['ref_doc_number']); ?></td>
                                    <td><?php echo htmlspecialchars($gr['created_by']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-warning">Pending</span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    ไม่มีรายการรออนุมัติ
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===============================
             TAB: การเบิกสินค้า (GI)
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
                            <th class="text-center">สถานะรายการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($gi_count > 0): ?>
                            <?php while ($gi = $result_gi->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($gi['gi_number']); ?></td>
                                    <td><?php echo htmlspecialchars($gi['gi_date']); ?></td>
                                    <td><?php echo htmlspecialchars($gi['ref_so_number']); ?></td>
                                    <td><?php echo htmlspecialchars($gi['created_by']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-warning">Pending</span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    ไม่มีรายการรออนุมัติ
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>


    </div>

    <!-- Footer -->
    <footer class="text-center py-3 mt-auto footer-bg">
        <small>© 2025 บริษัท มาคตางค์ จำกัด | ระบบคลังสินค้า</small>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
