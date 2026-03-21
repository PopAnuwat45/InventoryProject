<?php
include('server.php');

/* =====================================
   Get product_id
===================================== */

if (!isset($_GET['product_id'])) {
    die("ไม่พบรหัสสินค้า");
}

$product_id = $_GET['product_id'];


/* =====================================
   Get product data
===================================== */

$sql = "
    SELECT
        product_id,
        product_id_full,
        product_name,
        unit_id,
        location_id,
        reorder_point
    FROM product
    WHERE product_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบข้อมูลสินค้า");
}

$product = $result->fetch_assoc();


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
   Update product
===================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id_full = $_POST['product_id_full'];
    $product_name  = $_POST['product_name'];
    $unit_id       = $_POST['unit_id'];
    $location_id   = $_POST['location_id'];
    $reorder_point = $_POST['reorder_point'];

    // ✅ เช็คซ้ำก่อน
    $check_sql = "
        SELECT product_id 
        FROM product 
        WHERE product_id_full = ?
        AND product_id != ?
    ";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("si", $product_id_full , $product_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // ❌ มีรหัสซ้ำ
        echo "<div class='alert alert-danger'>รหัสสินค้านี้มีอยู่แล้ว</div>";
    } else {

    $update_sql = "
        UPDATE product
        SET
            product_id_full = ?,
            product_name  = ?,
            unit_id       = ?,
            location_id   = ?,
            reorder_point = ?
        WHERE product_id = ?
    ";

    $stmt = $conn->prepare($update_sql);

    $stmt->bind_param(
        "ssiiii",
        $product_id_full,
        $product_name,
        $unit_id,
        $location_id,
        $reorder_point,
        $product_id
    );

    $stmt->execute();

    header("Location: manage_products.php");
    exit();
}
}

?>

<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>แก้ไขสินค้า</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

    </div>

</nav>


<!-- MAIN CONTENT -->

<div class="container my-4">

    <h5 class="fw-bold mb-4">
        แก้ไขข้อมูลสินค้า
    </h5>

    <form method="POST">

        <div class="row">

            <!-- Product Code -->

            <div class="mb-3 col-md-4">

                <label class="form-label">
                    รหัสสินค้า
                </label>

                <input
                    type="text"
                    name="product_id_full"
                    class="form-control"
                    value="<?= $product['product_id_full'] ?>"
                    required
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
                    value="<?= $product['product_name'] ?>"
                    required
                >

            </div>


            <!-- Unit -->

            <div class="mb-3 col-md-4">

                <label class="form-label">
                    หน่วย
                </label>

                <select name="unit_id" class="form-select">

                    <?php while ($unit = $unit_result->fetch_assoc()): ?>

                        <option
                            value="<?= $unit['unit_id'] ?>"
                            <?= ($unit['unit_id'] == $product['unit_id']) ? 'selected' : '' ?>
                        >

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

                <select name="location_id" class="form-select">

                    <?php while ($loc = $location_result->fetch_assoc()): ?>

                        <option
                            value="<?= $loc['location_id'] ?>"
                            <?= ($loc['location_id'] == $product['location_id']) ? 'selected' : '' ?>
                        >

                            <?= $loc['location_full_id'] ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- Reorder Point -->

            <div class="mb-3 col-md-4">

                <label class="form-label">
                    จุดสั่งซื้อซ้ำ ReorderPoint (ROP)
                </label>

                <input
                    type="number"
                    name="reorder_point"
                    class="form-control"
                    value="<?= $product['reorder_point'] ?>"
                    required
                >

            </div>

        </div>


        <!-- Buttons -->

        <div class="mt-3">

            <button class="btn btn-success">
                บันทึกข้อมูล
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