<?php
include('check_login.php');
include('server.php');

$name = $_SESSION['name'] ?? '';
$type = $_SESSION['type']?? '';

$current_page ="manage_products.php";


/* ===============================
   ADD LOCATION
================================ */

if(isset($_POST['add_location'])){

    $location = trim($_POST['location_full_id']);

    if($location != ""){

        $stmt = $conn->prepare("
            SELECT location_id
            FROM location
            WHERE location_full_id = ?
        ");

        $stmt->bind_param("s",$location);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 0){

            $stmt = $conn->prepare("
                INSERT INTO location (location_full_id)
                VALUES (?)
            ");

            $stmt->bind_param("s",$location);
            $stmt->execute();

        }

    }

    header("Location: manage_location.php");
    exit();
}


/* ===============================
   UPDATE LOCATION
================================ */

if(isset($_POST['update_location'])){

    $location_id = $_POST['location_id'];
    $location    = trim($_POST['location_full_id']);

    if($location != ""){

        $stmt = $conn->prepare("
            UPDATE location
            SET location_full_id = ?
            WHERE location_id = ?
        ");

        $stmt->bind_param("si",$location,$location_id);
        $stmt->execute();
    }

    header("Location: manage_location.php");
    exit();
}


/* ===============================
   DELETE LOCATION
================================ */

if(isset($_GET['delete'])){

    $location_id = $_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM location
        WHERE location_id = ?
    ");

    $stmt->bind_param("i",$location_id);
    $stmt->execute();

    header("Location: manage_location.php");
    exit();
}


/* ===============================
   GET LOCATION
================================ */

$result = $conn->query("
    SELECT *
    FROM location
    ORDER BY location_full_id
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
จัดการตำแหน่งที่จัดเก็บสินค้า
</h5>

<div class="row g-2">
<?php include('menu_buttons.php'); ?>
</div>

</div>

<a href="manage_products.php" class="btn btn-outline-danger btn-sm mb-3">
        ⬅️ กลับ
    </a>

<!-- ADD LOCATION -->

<div class="card mb-4">

<div class="card-header fw-bold">
เพิ่มตำแหน่งจัดเก็บสินค้า
</div>

<div class="card-body">

<form method="POST" class="row g-2">

<div class="col-md-4">

<input
type="text"
name="location_full_id"
class="form-control"
placeholder="เช่น A1-01"
required
>

</div>

<div class="col-md-2">

<button
class="btn btn-success w-100"
name="add_location"
>
เพิ่มตำแหน่ง
</button>

</div>

</form>

</div>

</div>


<!-- LOCATION TABLE -->

<div class="card">

<div class="card-header fw-bold">
รายการตำแหน่งจัดเก็บสินค้า
</div>

<div class="table-responsive">

<table class="table table-striped align-middle mb-0">

<thead class="table-primary">

<tr>

<th width="20%">ลำดับ</th>
<th width="50%">ตำแหน่งจัดเก็บ</th>
<th width="15%" class="text-center">บันทึก</th>

</tr>

</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>

<form method="POST">

<tr>

<td>

<?= $row['location_id'] ?>

<input
type="hidden"
name="location_id"
value="<?= $row['location_id'] ?>"
>

</td>

<td>

<input
type="text"
name="location_full_id"
class="form-control"
value="<?= htmlspecialchars($row['location_full_id']) ?>"
>

</td>

<td class="text-center">

<button
name="update_location"
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