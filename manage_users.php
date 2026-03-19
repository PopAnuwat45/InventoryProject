<?php
include('server.php');

include('check_login.php');
include('server.php');

$name = $_SESSION['name'] ?? '';
$type = $_SESSION['type']?? '';

$current_page = basename($_SERVER['PHP_SELF']);


/* ===============================
   ADD USER
================================ */

if(isset($_POST['add_user'])){

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $name     = trim($_POST['name']);
    $type     = $_POST['type'];

    if($username != "" && $password != "" && $name != ""){

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            INSERT INTO user_tb (username,password,name,type)
            VALUES (?,?,?,?)
        ");

        $stmt->bind_param("ssss",$username,$hash,$name,$type);
        $stmt->execute();
    }

    header("Location: manage_users.php");
    exit();
}


/* ===============================
   UPDATE USER
================================ */

if(isset($_POST['update_user'])){

    $username = $_POST['username'];
    $password = $_POST['password'];
    $name     = $_POST['name'];
    $type     = $_POST['type'];

    if($password != ""){

        $hash = password_hash($password,PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            UPDATE user_tb
            SET password=?, name=?, type=?
            WHERE username=?
        ");

        $stmt->bind_param("ssss",$hash,$name,$type,$username);

    }else{

        $stmt = $conn->prepare("
            UPDATE user_tb
            SET name=?, type=?
            WHERE username=?
        ");

        $stmt->bind_param("sss",$name,$type,$username);
    }

    $stmt->execute();

    header("Location: manage_users.php");
    exit();
}


/* ===============================
   GET USERS
================================ */

$result = $conn->query("
    SELECT username,name,type
    FROM user_tb
    ORDER BY username
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
จัดการผู้ใช้งาน
</h5>

<div class="row g-2">
<?php include('menu_buttons.php'); ?>
</div>

</div>


<!-- ADD USER -->

<div class="card mb-4">

<div class="card-header fw-bold">
เพิ่มผู้ใช้ใหม่
</div>

<div class="card-body">

<form method="POST" class="row g-2">

<div class="col-md-2">

<input
type="text"
name="username"
class="form-control"
placeholder="Username"
autocomplete="off"
required
>

</div>

<div class="col-md-2">

<input
name="password"
class="form-control"
placeholder="Password"
autocomplete="off"
required
>

</div>

<div class="col-md-3">

<input
type="text"
name="name"
class="form-control"
placeholder="ชื่อผู้ใช้งาน"
autocomplete="off"
required
>

</div>

<div class="col-md-3">

<select name="type" class="form-select">

<option value="Admin">Admin</option>
<option value="Head of Purchase">Head of Purchase</option>
<option value="Purchase">Purchase</option>
<option value="Sale">Sale</option>

</select>

</div>

<div class="col-md-2">

<button class="btn btn-success w-100" name="add_user">
เพิ่มผู้ใช้
</button>

</div>

</form>

</div>

</div>


<!-- USER TABLE -->

<div class="card">

<div class="card-header fw-bold">
รายการผู้ใช้งาน
</div>

<div class="table-responsive">

<table class="table table-striped align-middle mb-0">

<thead class="table-primary">

<tr>

<th width="20%">Username</th>
<th width="30%">ชื่อผู้ใช้</th>
<th width="25%">ตั้งรหัสผ่านใหม่</th>
<th width="15%">Role</th>
<th width="10%" class="text-center">บันทึก</th>

</tr>

</thead>

<tbody>

<?php while($user = $result->fetch_assoc()): ?>

<form method="POST">

<tr>

<td>

<?= htmlspecialchars($user['username']) ?>

<input
type="hidden"
name="username"
value="<?= $user['username'] ?>"
>

</td>

<td>

<input
type="text"
name="name"
class="form-control"
autocomplete="off"
value="<?= htmlspecialchars($user['name']) ?>"
>

</td>

<td>

<input
name="password"
class="form-control"
autocomplete="off"
placeholder="กรอกเมื่อต้องการเปลี่ยน"
>

</td>

<td>

<select name="type" class="form-select">

<option value="Admin" <?= $user['type']=="Admin"?"selected":"" ?> >
Admin
</option>

<option value="Head of Purchase" <?= $user['type']=="Head of Purchase"?"selected":"" ?> >
Head of Purchase
</option>

<option value="Purchase" <?= $user['type']=="Purchase"?"selected":"" ?> >
Purchase
</option>

<option value="Sale" <?= $user['type']=="Sale"?"selected":"" ?> >
Sale
</option>

</select>

</td>

<td class="text-center">

<button
name="update_user"
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