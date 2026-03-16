```php
<?php
include('server.php');

$current_page = basename($_SERVER['PHP_SELF']);

/* =====================================
   GET PARAMETER
===================================== */

$activeTab = $_GET['tab'] ?? 'gr';
$search    = $_GET['search'] ?? '';

$limit = 10;

$page_gr = isset($_GET['page_gr']) ? (int)$_GET['page_gr'] : 1;
$page_gi = isset($_GET['page_gi']) ? (int)$_GET['page_gi'] : 1;

if ($page_gr < 1) $page_gr = 1;
if ($page_gi < 1) $page_gi = 1;

$offset_gr = ($page_gr - 1) * $limit;
$offset_gi = ($page_gi - 1) * $limit;

$search_param = "%{$search}%";


/* =====================================
   COUNT GR
===================================== */

$sql_gr_total = "
    SELECT COUNT(*) AS total
    FROM goods_receipt
    WHERE gr_status = 'Approve'
    AND (
        gr_number LIKE ?
        OR ref_doc_number LIKE ?
        OR approved_by LIKE ?
    )
";

$stmt = $conn->prepare($sql_gr_total);
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();

$total_gr = $stmt->get_result()->fetch_assoc()['total'];
$total_pages_gr = ceil($total_gr / $limit);


/* =====================================
   GET GR DATA
===================================== */

$sql_gr = "
    SELECT
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
    ORDER BY gr_date DESC
    LIMIT ?, ?
";

$stmt_gr = $conn->prepare($sql_gr);

$stmt_gr->bind_param(
    "sssii",
    $search_param,
    $search_param,
    $search_param,
    $offset_gr,
    $limit
);

$stmt_gr->execute();
$result_gr = $stmt_gr->get_result();


/* =====================================
   COUNT GI
===================================== */

$sql_gi_total = "
    SELECT COUNT(*) AS total
    FROM goods_issue
    WHERE gi_status = 'Approve'
    AND (
        gi_number LIKE ?
        OR ref_so_number LIKE ?
        OR approved_by LIKE ?
    )
";

$stmt = $conn->prepare($sql_gi_total);
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();

$total_gi = $stmt->get_result()->fetch_assoc()['total'];
$total_pages_gi = ceil($total_gi / $limit);


/* =====================================
   GET GI DATA
===================================== */

$sql_gi = "
    SELECT
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
    ORDER BY gi_date DESC
    LIMIT ?, ?
";

$stmt_gi = $conn->prepare($sql_gi);

$stmt_gi->bind_param(
    "sssii",
    $search_param,
    $search_param,
    $search_param,
    $offset_gi,
    $limit
);

$stmt_gi->execute();
$result_gi = $stmt_gi->get_result();
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


<!-- =============================
     NAVBAR
============================= -->

<nav class="navbar navbar-expand-lg navbar-dark main-nav">

<div class="container">

<a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">

<img src="img/logo.jpg" width="100" height="30" class="me-2">

ระบบคลังสินค้า

</a>

</div>

</nav>


<!-- =============================
     MAIN CONTENT
============================= -->

<div class="container my-4">


<!-- MENU BUTTON -->

<div class="menu-section mb-4">

<h5 class="mb-3 fw-bold">รายการที่ได้รับการอนุมัติแล้ว</h5>

<div class="row g-2">

<?php include('menu_buttons.php') ?>

</div>

</div>


<!-- SEARCH -->

<form method="GET" class="d-flex mb-3">

<input type="hidden" name="tab" value="<?= $activeTab ?>">

<input
type="text"
name="search"
class="form-control w-50"
placeholder="ค้นหาเลขเอกสาร / ผู้อนุมัติ"
value="<?= htmlspecialchars($search) ?>"
>

<button class="btn btn-outline-success ms-2">
ค้นหา
</button>

<a href="approve_list.php?tab=<?= $activeTab ?>" class="btn btn-outline-danger ms-2">
ล้าง
</a>

</form>


<!-- TABS -->

<ul class="nav nav-tabs mb-3 fw-bold">

<li class="nav-item">

<button
class="nav-link <?= $activeTab === 'gr' ? 'active' : '' ?>"
data-bs-toggle="tab"
data-bs-target="#tab-gr"
>

การรับสินค้า

</button>

</li>


<li class="nav-item">

<button
class="nav-link <?= $activeTab === 'gi' ? 'active' : '' ?>"
data-bs-toggle="tab"
data-bs-target="#tab-gi"
>

การเบิกสินค้า

</button>

</li>

</ul>


<div class="tab-content">


<!-- =====================================
     TAB GR
===================================== -->

<div class="tab-pane fade <?= $activeTab === 'gr' ? 'show active' : '' ?>" id="tab-gr">

<?php
$start = $offset_gr + 1;
$end   = min($offset_gr + $limit, $total_gr);
?>



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

<?php if ($result_gr->num_rows > 0): ?>

<?php while ($gr = $result_gr->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($gr['gr_number']) ?></td>
<td><?= htmlspecialchars($gr['gr_date']) ?></td>
<td><?= htmlspecialchars($gr['ref_doc_number']) ?></td>
<td><?= htmlspecialchars($gr['approved_by']) ?></td>

<td class="text-center">

<a
href="approve_gr.php?gr_id=<?= $gr['gr_id'] ?>"
class="btn btn-sm btn-primary"
>

ดูรายละเอียด

</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="5" class="text-center text-muted">

ไม่พบข้อมูล

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>


<div class="d-flex justify-content-between align-items-center mt-3">

<p class="text-muted mb-0">
แสดง <?= $start ?> - <?= $end ?> จากทั้งหมด <?= $total_gr ?> รายการ
</p>

<nav>

<ul class="pagination mb-0">

<?php if ($page_gr > 1): ?>

<li class="page-item">
<a class="page-link"
href="?tab=gr&page_gr=<?= $page_gr - 1 ?>&search=<?= urlencode($search) ?>">
ก่อนหน้า
</a>
</li>

<?php endif; ?>


<?php for ($i = 1; $i <= $total_pages_gr; $i++): ?>

<li class="page-item <?= $i == $page_gr ? 'active' : '' ?>">
<a class="page-link"
href="?tab=gr&page_gr=<?= $i ?>&search=<?= urlencode($search) ?>">
<?= $i ?>
</a>
</li>

<?php endfor; ?>


<?php if ($page_gr < $total_pages_gr): ?>

<li class="page-item">
<a class="page-link"
href="?tab=gr&page_gr=<?= $page_gr + 1 ?>&search=<?= urlencode($search) ?>">
ถัดไป
</a>
</li>

<?php endif; ?>

</ul>

</nav>

</div>





</div>


<!-- =====================================
     TAB GI
===================================== -->

<div class="tab-pane fade <?= $activeTab === 'gi' ? 'show active' : '' ?>" id="tab-gi">

<?php
$start = $offset_gi + 1;
$end   = min($offset_gi + $limit, $total_gi);
?>



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

<?php if ($result_gi->num_rows > 0): ?>

<?php while ($gi = $result_gi->fetch_assoc()): ?>

<tr>

<td><?= htmlspecialchars($gi['gi_number']) ?></td>
<td><?= htmlspecialchars($gi['gi_date']) ?></td>
<td><?= htmlspecialchars($gi['ref_so_number']) ?></td>
<td><?= htmlspecialchars($gi['approved_by']) ?></td>

<td class="text-center">

<a
href="approve_gi.php?gi_id=<?= $gi['gi_id'] ?>&from=gi"
class="btn btn-sm btn-primary"
>

ดูรายละเอียด

</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td colspan="5" class="text-center text-muted">

ไม่พบข้อมูล

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

<div class="d-flex justify-content-between align-items-center mt-3">

<p class="text-muted mb-0">
แสดง <?= $start ?> - <?= $end ?> จากทั้งหมด <?= $total_gi ?> รายการ
</p>

<nav>

<ul class="pagination mb-0">

<?php if ($page_gi > 1): ?>

<li class="page-item">
<a class="page-link"
href="?tab=gi&page_gi=<?= $page_gi - 1 ?>&search=<?= urlencode($search) ?>">
ก่อนหน้า
</a>
</li>

<?php endif; ?>


<?php for ($i = 1; $i <= $total_pages_gi; $i++): ?>

<li class="page-item <?= $i == $page_gi ? 'active' : '' ?>">
<a class="page-link"
href="?tab=gi&page_gi=<?= $i ?>&search=<?= urlencode($search) ?>">
<?= $i ?>
</a>
</li>

<?php endfor; ?>


<?php if ($page_gi < $total_pages_gi): ?>

<li class="page-item">
<a class="page-link"
href="?tab=gi&page_gi=<?= $page_gi + 1 ?>&search=<?= urlencode($search) ?>">
ถัดไป
</a>
</li>

<?php endif; ?>

</ul>

</nav>

</div>


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
```
