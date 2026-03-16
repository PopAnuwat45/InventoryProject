<?php
include('server.php');

$current_page = basename($_SERVER['PHP_SELF']);


/* =====================================
   Filter (Year / Quarter)
===================================== */

$year = $_GET['year'] ?? date('Y');
$quarter = $_GET['quarter'] ?? 1;

switch($quarter)
{
    case 1:
        $start_month = 1;
        $end_month = 3;
        break;

    case 2:
        $start_month = 4;
        $end_month = 6;
        break;

    case 3:
        $start_month = 7;
        $end_month = 9;
        break;

    case 4:
        $start_month = 10;
        $end_month = 12;
        break;
}


/* =====================================
   Dashboard Summary
===================================== */

$sql_total_product = "SELECT COUNT(*) AS total FROM product";
$total_product = mysqli_fetch_assoc(mysqli_query($conn,$sql_total_product))['total'];

$sql_total_gr = "SELECT COUNT(*) AS total FROM goods_receipt";
$total_gr = mysqli_fetch_assoc(mysqli_query($conn,$sql_total_gr))['total'];

$sql_total_gi = "SELECT COUNT(*) AS total FROM goods_issue";
$total_gi = mysqli_fetch_assoc(mysqli_query($conn,$sql_total_gi))['total'];


/* =====================================
   Most Movement
===================================== */

$sql_top_move = "
SELECT 
p.product_name,
COUNT(sm.movement_id) AS move_count

FROM stock_movement sm

JOIN product p
ON sm.product_id = p.product_id

WHERE YEAR(sm.movement_date) = '$year'
AND MONTH(sm.movement_date) BETWEEN $start_month AND $end_month

GROUP BY sm.product_id
ORDER BY move_count DESC
LIMIT 5
";

$result_top_move = mysqli_query($conn,$sql_top_move);


/* =====================================
   Low Movement
===================================== */

$sql_low_move = "
SELECT 
p.product_name,
COUNT(sm.movement_id) AS move_count

FROM stock_movement sm

JOIN product p
ON sm.product_id = p.product_id

AND YEAR(sm.movement_date) = '$year'
AND MONTH(sm.movement_date) BETWEEN $start_month AND $end_month

GROUP BY p.product_id
ORDER BY move_count ASC
LIMIT 5
";

$result_low_move = mysqli_query($conn,$sql_low_move);

?>


<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>รายงานผลคลังสินค้า</title>

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
รายงานผลการเคลื่อนไหวสินค้า
</h5>

<div class="row g-2">
<?php include('menu_buttons.php'); ?>
</div>

</div>



<!-- FILTER -->

<div class="card mb-4">

<div class="card-body">

<form method="GET" class="row g-2">

<div class="col-md-3">

<label class="form-label">ปี</label>

<select name="year" class="form-select">

<?php for($y=date('Y')-3;$y<=date('Y');$y++){ ?>

<option value="<?= $y ?>" <?= ($y==$year)?'selected':'' ?>>

<?= $y ?>

</option>

<?php } ?>

</select>

</div>


<div class="col-md-3">

<label class="form-label">ไตรมาส</label>

<select name="quarter" class="form-select">

<option value="1" <?=($quarter==1)?'selected':''?>>ไตรมาส 1</option>
<option value="2" <?=($quarter==2)?'selected':''?>>ไตรมาส 2</option>
<option value="3" <?=($quarter==3)?'selected':''?>>ไตรมาส 3</option>
<option value="4" <?=($quarter==4)?'selected':''?>>ไตรมาส 4</option>

</select>

</div>

<div class="col-md-2 align-self-end">

<button class="btn btn-primary w-100">
แสดงรายงาน
</button>

</div>

</form>

</div>

</div>



<!-- DASHBOARD -->

<div class="row g-3 mb-4">

<div class="col-md-4">

<div class="card text-center shadow-sm">

<div class="card-body">

<h6>จำนวนสินค้าในระบบ</h6>

<h3 class="text-primary">
<?= $total_product ?>
</h3>

</div>

</div>

</div>


<div class="col-md-4">

<div class="card text-center shadow-sm">

<div class="card-body">

<h6>ใบรับสินค้า (GR)</h6>

<h3 class="text-success">
<?= $total_gr ?>
</h3>

</div>

</div>

</div>


<div class="col-md-4">

<div class="card text-center shadow-sm">

<div class="card-body">

<h6>ใบเบิกสินค้า (GI)</h6>

<h3 class="text-warning">
<?= $total_gi ?>
</h3>

</div>

</div>

</div>

</div>



<!-- REPORT TABLES -->

<div class="row">


<div class="col-md-6 mb-4">

<div class="card shadow-sm">

<div class="card-header fw-bold">
สินค้าที่เคลื่อนไหวมากที่สุด 5 รายการ
</div>

<div class="card-body">

<table class="table table-sm">

<thead>

<tr>
<th>สินค้า</th>
<th>จำนวนครั้ง</th>
</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result_top_move)){ ?>

<tr>
<td><?= $row['product_name'] ?></td>
<td><?= $row['move_count'] ?> ครั้ง</td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>



<div class="col-md-6 mb-4">

<div class="card shadow-sm">

<div class="card-header fw-bold">
สินค้าที่เคลื่อนไหวน้อยที่สุด 5 รายการ
</div>

<div class="card-body">

<table class="table table-sm">

<thead>

<tr>
<th>สินค้า</th>
<th>จำนวนครั้ง</th>
</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result_low_move)){ ?>

<tr>
<td><?= $row['product_name'] ?></td>
<td><?= $row['move_count'] ?> ครั้ง</td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

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