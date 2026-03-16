<?php
include('server.php');

$current_page = basename($_SERVER['PHP_SELF']);


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
            จัดการผู้ใช้งาน
        </h5>

        <div class="row g-2">
            <?php include('menu_buttons.php'); ?>
        </div>

    </div>


    <!-- SECTION MANAGE USER -->



<!-- FOOTER -->

<footer class="text-center py-3 mt-auto footer-bg">

    <small>
        © 2025 บริษัท มาคตางค์ จำกัด | ระบบคลังสินค้า
    </small>

</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>