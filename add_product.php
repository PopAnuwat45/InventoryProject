<?php
include('check_login.php');
include('server.php');

$name = $_SESSION['name'] ?? '';
$type = $_SESSION['type']?? '';

/* =====================================
   Generate Product Code
===================================== */

$sql = "
    SELECT product_id_full
    FROM product
    ORDER BY product_id DESC
    LIMIT 1
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();

    $last_code = $row['product_id_full'];

    $number = (int)substr($last_code, 1);

    $number++;

    $product_code = "P" . str_pad($number, 4, "0", STR_PAD_LEFT);

} else {

    $product_code = "P0001";

}


/* =====================================
   Get unit list
===================================== */

$unit_sql = "
    SELECT
        unit_id,
        unit_name
    FROM unit
    ORDER BY unit_name
";

$unit_result = $conn->query($unit_sql);


/* =====================================
   Get location list
===================================== */

$location_sql = "
    SELECT
        location_id,
        location_full_id
    FROM location
    ORDER BY location_full_id
";

$location_result = $conn->query($location_sql);


/* =====================================
   Insert product
===================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_name  = $_POST['product_name'];
    $unit_id       = $_POST['unit_id'];
    $location_id   = $_POST['location_id'];
    $reorder_point = $_POST['reorder_point'];

    $insert_sql = "
        INSERT INTO product
        (
            product_id_full,
            product_name,
            unit_id,
            location_id,
            reorder_point
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($insert_sql);

    $stmt->bind_param(
        "ssiii",
        $product_code,
        $product_name,
        $unit_id,
        $location_id,
        $reorder_point
    );

    $stmt->execute();

    header("Location: manage_products.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>เพิ่มสินค้า</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">

</head>

<body class="d-flex flex-column min-vh-100">

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg navbar-dark main-nav">

    <div class="container">

        <a class="navbar-brand fw-bold d-flex align-items-center" href="manage_products.php">

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


<!-- MAIN CONTENT -->

<div class="container my-4">

    <h5 class="fw-bold mb-4">
        เพิ่มสินค้าใหม่
    </h5>

    <a href="javascript:history.back()" class="btn btn-outline-danger btn-sm mb-3">
        ⬅️ กลับ
    </a>

    <form method="POST">

        <div class="row">

            <!-- Product Code -->

            <div class="mb-3 col-md-4">

                <label class="form-label">
                    รหัสสินค้า
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= $product_code ?>"
                >

            </div>


            <!-- Product Name -->

            <div class="mb-3 col-md-8">

                <label class="form-label">
                    ชื่อสินค้า
                </label>

                <input
                    type="text"
                    name="product_name"
                    class="form-control"
                    required
                >

            </div>


            <!-- Unit -->

            <div class="mb-3 col-md-4">

                <label class="form-label">
                    หน่วย
                </label>

                <select name="unit_id" class="form-select" required>

                    <option value="">
                        เลือกหน่วย
                    </option>

                    <?php while ($unit = $unit_result->fetch_assoc()): ?>

                        <option value="<?= $unit['unit_id'] ?>">

                            <?= $unit['unit_name'] ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- Location -->

            <div class="mb-3 col-md-4">

                <label class="form-label">
                    ตำแหน่งจัดเก็บ
                </label>

                <select name="location_id" class="form-select" required>

                    <option value="">
                        เลือกตำแหน่ง
                    </option>

                    <?php while ($loc = $location_result->fetch_assoc()): ?>

                        <option value="<?= $loc['location_id'] ?>">

                            <?= $loc['location_full_id'] ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- ROP -->

            <div class="mb-3 col-md-4">

                <label class="form-label">
                    จุดสั่งซื้อซ้ำ (ROP)
                </label>

                <input
                    type="number"
                    name="reorder_point"
                    class="form-control"
                    required
                >

            </div>

        </div>


        <!-- Buttons -->

        <div class="mt-3">

            <button class="btn btn-success">
                บันทึกสินค้า
            </button>

            <a href="manage_products.php" class="btn btn-secondary">
                ยกเลิก
            </a>

        </div>

    </form>

</div>


<!-- FOOTER -->

<footer class="text-center py-3 mt-auto footer-bg">

<small>
© 2025 บริษัท มาคตางค์ จำกัด | ระบบคลังสินค้า
</small>

</footer>

</body>

</html>