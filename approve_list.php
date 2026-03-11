<?php
include('server.php');

$activeTab = $_GET['tab'] ?? 'gr';

$search = $_GET['search'] ?? '';

$current_page = basename($_SERVER['PHP_SELF']);

/* ===============================
   ดึงข้อมูล GR Approve
================================ */
$sql_gr = "SELECT 
            gr_id,
            gr_number,
            gr_date,
            ref_doc_number,
            approved_by
        FROM goods_receipt
        WHERE gr_status = 'Approve'
        AND (
            gr_number LIKE ?
            OR ref_doc_number LIKE ?
            OR approved_by LIKE ?
        )
        ORDER BY gr_date DESC";

$stmt_gr = $conn->prepare($sql_gr);

$search_param = "%{$search}%";

$stmt_gr->bind_param("sss", $search_param, $search_param, $search_param);

$stmt_gr->execute();

$result_gr = $stmt_gr->get_result();

$gr_count = $result_gr->num_rows;

/* ===============================
   ดึงข้อมูล GI Approve
================================ */
$sql_gi = "SELECT 
            gi_id,
            gi_number,
            gi_date,
            ref_so_number,
            approved_by
        FROM goods_issue
        WHERE gi_status = 'Approve'
        AND (
            gi_number LIKE ?
            OR ref_so_number LIKE ?
            OR approved_by LIKE ?
        )
        ORDER BY gi_date DESC";


$stmt_gi = $conn->prepare($sql_gi);

$stmt_gi->bind_param("sss", $search_param, $search_param, $search_param);

$stmt_gi->execute();

$result_gi = $stmt_gi->get_result();

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
            <h5 class="mb-3 fw-bold">รายการที่ได้รับการอนุมัติแล้ว</h5>
            <div class="row g-2">
                <?php include('menu_buttons.php')?>
            </div>
        </div>

        <!-- Section: Approval Requests -->
<div class="approval-section">

    <form method="GET" class="d-flex mb-3">

    <input type="hidden" name="tab" value="<?= $activeTab ?>">

    <input type="text"
        name="search"
        class="form-control w-50"
        placeholder="🔍 ค้นหาเลขเอกสาร / ผู้อนุมัติ"
        value="<?= htmlspecialchars($search) ?>">

    <button class="btn btn-outline-success ms-2">
    ค้นหา
    </button>

    <a href="approve_list.php?tab=<?= $activeTab ?>" 
    class="btn btn-outline-danger ms-2">
    ล้าง
    </a>

    </form>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3 fw-bold">
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'gr' ? 'active' : '' ?>"
             data-bs-toggle="tab" data-bs-target="#tab-gr">
                การรับสินค้า
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $activeTab === 'gi' ? 'active' : '' ?>" 
            data-bs-toggle="tab" data-bs-target="#tab-gi">
                การเบิกสินค้า
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
                            <th>ผู้อนุมัติ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($gr_count >= 0): ?>
                            <?php while ($gr = $result_gr->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($gr['gr_number']); ?></td>
                                    <td><?php echo htmlspecialchars($gr['gr_date']); ?></td>
                                    <td><?php echo htmlspecialchars($gr['ref_doc_number']); ?></td>
                                    <td><?php echo htmlspecialchars($gr['approved_by']); ?></td>
                                    <td class="text-center">
                                        <a href="approve_gr.php?gr_id=<?php echo $gr['gr_id']; ?>"
                                           class="btn btn-sm btn-primary">
                                            ดูรายละเอียด
                                        </a>
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
                            <th>ผู้อนุมัติ</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($gi_count >= 0): ?>
                            <?php while ($gi = $result_gi->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($gi['gi_number']); ?></td>
                                    <td><?php echo htmlspecialchars($gi['gi_date']); ?></td>
                                    <td><?php echo htmlspecialchars($gi['ref_so_number']); ?></td>
                                    <td><?php echo htmlspecialchars($gi['approved_by']); ?></td>
                                    <td class="text-center">
                                        <a href="approve_gi.php?gi_id=<?php echo $gi['gi_id']; ?>&from=gi"
                                           class="btn btn-sm btn-primary">
                                            ดูรายละเอียด
                                        </a>
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
