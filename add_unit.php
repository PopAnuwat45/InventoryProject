<?php
include('check_login.php');
include('server.php');

$name = $_SESSION['name'] ?? '';
$type = $_SESSION['type']?? '';

$current_page ="manage_products.php";


/* ===============================
   ADD UNIT
================================ */

if(isset($_POST['add_unit'])){

    $unit = trim($_POST['unit_name']);

    if($unit != ""){

        $stmt = $conn->prepare("
            SELECT unit_id
            FROM unit
            WHERE unit_name = ?
        ");

        $stmt->bind_param("s",$unit);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 0){

            $stmt = $conn->prepare("
                INSERT INTO unit (unit_name)
                VALUES (?)
            ");

            $stmt->bind_param("s",$unit);
            $stmt->execute();

        }

    }

    header("Location: add_unit.php");
    exit();
}


/* ===============================
   UPDATE UNIT
================================ */

if(isset($_POST['update_unit'])){

    $unit_id = $_POST['unit_id'];
    $unit    = trim($_POST['unit_name']);

    if($unit != ""){

        $stmt = $conn->prepare("
            UPDATE unit
            SET unit_name = ?
            WHERE unit_id = ?
        ");

        $stmt->bind_param("si",$unit,$unit_id);
        $stmt->execute();
    }

    header("Location: add_unit.php");
    exit();
}


/* ===============================
   DELETE UNIT
================================ */

if(isset($_GET['delete'])){

    $unit_id = $_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM unit
        WHERE unit_id = ?
    ");

    $stmt->bind_param("i",$unit_id);
    $stmt->execute();

    header("Location: add_unit.php");
    exit();
}


/* ===============================
   GET UNIT
================================ */

$result = $conn->query("
    SELECT *
    FROM unit
    ORDER BY unit_id
");

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
จัดการหน่วยนับสินค้า
</h5>

<div class="row g-2">
<?php include('menu_buttons.php'); ?>
</div>

</div>

<a href="manage_products.php" class="btn btn-outline-danger btn-sm mb-3">
        ⬅️ กลับ
    </a>

<!-- ADD UNIT -->

<div class="card mb-4">

<div class="card-header fw-bold">
เพิ่มหน่วยนับสินค้า
</div>

<div class="card-body">

<form method="POST" class="row g-2">

<div class="col-md-4">

<input
type="text"
name="unit_name"
class="form-control"
placeholder="เช่น ชิ้น / กล่อง / แพ็ค"
required
>

</div>

<div class="col-md-2">

<button
class="btn btn-success w-100"
name="add_unit"
>
เพิ่มหน่วย
</button>

</div>

</form>

</div>

</div>



<!-- UNIT TABLE -->

<div class="card">

<div class="card-header fw-bold">
รายการหน่วยนับสินค้า
</div>

<div class="table-responsive">

<table class="table table-striped align-middle mb-0">

<thead class="table-primary">

<tr>

<th width="20%">ลำดับ</th>
<th width="50%">หน่วยนับ</th>
<th width="15%" class="text-center">บันทึก</th>

</tr>

</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>

<form method="POST">

<tr>

<td>

<?= $row['unit_id'] ?>

<input
type="hidden"
name="unit_id"
value="<?= $row['unit_id'] ?>"
>

</td>

<td>

<input
type="text"
name="unit_name"
class="form-control"
value="<?= htmlspecialchars($row['unit_name']) ?>"
>

</td>

<td class="text-center">

<button
name="update_unit"
class="btn btn-primary btn-sm"
>
บันทึก
</button>

</td>

</tr>

</form>

<?php endwhile; ?>

</tbody>

</table>

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