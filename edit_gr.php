<?php
include('server.php');

/* ===============================
   รับค่า gr_id
================================ */
if (!isset($_GET['gr_id'])) {
    die('ไม่พบข้อมูลใบรับสินค้า');
}
$gr_id = intval($_GET['gr_id']);

/* ===============================
   ดึงข้อมูลหัว GR (ต้องเป็น Reject)
================================ */
$sql_gr = "SELECT * FROM goods_receipt 
           WHERE gr_id = ? AND gr_status = 'Reject'";
$stmt_gr = $conn->prepare($sql_gr);
$stmt_gr->bind_param("i", $gr_id);
$stmt_gr->execute();
$result_gr = $stmt_gr->get_result();

if ($result_gr->num_rows === 0) {
    die('ไม่พบรายการ หรือรายการไม่อยู่ในสถานะที่แก้ไขได้');
}
$gr = $result_gr->fetch_assoc();

/* ===============================
   ดึงรายการสินค้า
================================ */
$sql_items = "SELECT 
                gri.gr_item_id,
                gri.product_id,
                gri.gr_qty,
                p.product_id_full,
                p.product_name,
                p.unit
            FROM goods_receipt_item gri
            JOIN product p ON gri.product_id = p.product_id
            WHERE gri.gr_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $gr_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

/* ===============================
   บันทึกการแก้ไข
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $conn->begin_transaction();

    try {
        foreach ($_POST['gr_item_id'] as $index => $gr_item_id) {
            $qty = intval($_POST['gr_qty'][$index]);

            $sql_update = "UPDATE goods_receipt_item
                           SET gr_qty = ?
                           WHERE gr_item_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ii", $qty, $gr_item_id);
            $stmt_update->execute();
        }

        // ส่งกลับไป Pending
        $sql_status = "UPDATE goods_receipt
                       SET gr_status = 'Pending'
                       WHERE gr_id = ?";
        $stmt_status = $conn->prepare($sql_status);
        $stmt_status->bind_param("i", $gr_id);
        $stmt_status->execute();

        $conn->commit();

        echo "<script>
                alert('แก้ไขและส่งใบรับสินค้าใหม่เรียบร้อยแล้ว');
                window.location='reject_list.php';
              </script>";
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die('เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขใบรับสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark main-nav">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="img/logo.jpg" alt="" width="100" height="30" class="me-2">
            ระบบคลังสินค้า
        </a>
    </div>
</nav>

<div class="container my-4">

    <h5 class="fw-bold mb-2 text-warning">
        แก้ไขใบรับสินค้า (ถูกไม่อนุมัติ)
    </h5>

    <a href="reject_list.php" class="btn btn-outline-danger btn-sm mb-3">
        ⬅️ กลับ
    </a>

    <!-- ข้อมูลหัวเอกสาร -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>เลขที่ GR:</strong> <?= $gr['gr_number']; ?></div>
                <div class="col-md-4"><strong>วันที่:</strong> <?= $gr['gr_date']; ?></div>
                <div class="col-md-4">
                    <strong>สถานะ:</strong>
                    <span class="badge bg-danger">Reject</span>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4"><strong>เอกสารอ้างอิง:</strong> <?= $gr['ref_doc_number']; ?></div>
                <div class="col-md-4"><strong>ผู้ทำรายการ:</strong> <?= $gr['created_by']; ?></div>
            </div>
        </div>
    </div>

    <!-- ตารางสินค้า -->
    <form method="POST">

<div class="mb-3">
    <label class="form-label">รายการสินค้า</label>

    <table class="table table-bordered table-striped" id="gr_items_table">
        <thead class="table-warning">
            <tr>
                <th>รหัสสินค้า</th>
                <th>ชื่อสินค้า</th>
                <th width="120">จำนวน</th>
                <th width="120">หน่วย</th>
                <th width="80">ลบ</th>
            </tr>
        </thead>
        <tbody>

        <?php while ($item = $result_items->fetch_assoc()): ?>
            <tr>
                <td>
                    <div class="product-search-wrapper position-relative">
                        <input type="text"
                               name="product_code[]"
                               class="form-control product-search"
                               value="<?= $item['product_id_full']; ?>"
                               autocomplete="off"
                               required>

                        <div class="product-list"></div>

                        <input type="hidden"
                               name="product_id[]"
                               class="product-id"
                               value="<?= $item['product_id']; ?>">

                        <input type="hidden"
                               name="gr_item_id[]"
                               value="<?= $item['gr_item_id']; ?>">
                    </div>
                </td>

                <td>
                    <input type="text"
                           name="gr_name[]"
                           class="form-control"
                           value="<?= $item['product_name']; ?>"
                           readonly>
                </td>

                <td>
                    <input type="number"
                           name="gr_qty[]"
                           class="form-control"
                           min="1"
                           value="<?= $item['gr_qty']; ?>"
                           required>
                </td>

                <td>
                    <input type="text"
                           name="unit[]"
                           class="form-control unit-field"
                           value="<?= $item['unit']; ?>"
                           readonly>
                </td>

                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                        ลบ
                    </button>
                </td>
            </tr>
        <?php endwhile; ?>

        </tbody>
    </table>

    <button type="button"
            class="btn btn-outline-success btn-sm"
            id="add_item_btn">
        ➕ เพิ่มสินค้า
    </button>
</div>

<div class="text-end">
    <button type="submit"
            class="btn btn-warning"
            onclick="return confirm('ยืนยันการแก้ไขและส่งใหม่?');">
        แก้ไขแล้วส่งใหม่
    </button>
</div>

</div>  

<!-- JS เพิ่ม/ลบแถวสินค้า -->
<script>
document.addEventListener('DOMContentLoaded', function(){
    const addBtn = document.getElementById('add_item_btn');
    const tableBody = document.querySelector('#gr_items_table tbody');

    addBtn.addEventListener('click', function(){
        const firstRow = tableBody.querySelector('tr');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        tableBody.appendChild(newRow);
    });

    tableBody.addEventListener('click', function(e){
        if(e.target.classList.contains('remove-row')){
            const rows = tableBody.querySelectorAll('tr');
            if(rows.length > 1){
                e.target.closest('tr').remove();
            } else {
                alert('ต้องมีสินค้าอย่างน้อย 1 รายการ');
            }
        }
    });

    tableBody.addEventListener('change', function(e){
        if(e.target.tagName === 'SELECT'){
            const unitInput = e.target.closest('tr').querySelector('input[name="gr_unit[]"]');
            unitInput.value = e.target.selectedOptions[0].dataset.unit || '';
        }
    });
});
</script>

<!-- โค้ดในส่วนของการดึงข้อมูล -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    $(document).ready(function(){
        $(document).on("keyup", ".product-search", function(){
            let query = $(this).val();
            let inputField = $(this);
            let resultBox = $(this).siblings(".product-list");

            if(query.length >= 2){ // เริ่มค้นเมื่อพิมพ์เกิน 2 ตัว
                $.ajax({
                    url: "search_product.php",
                    method: "POST",
                    data: {query: query},
                    success: function(data){
                        resultBox.html(data);
                        resultBox.show();
                    }
                });
            } else{
                resultBox.hide();
            }
        });

        // เมื่อคลิกเลือกรายการที่ค้นเจอ
        $(document).on("click", ".product-item", function(){
            let product_id = $(this).data("id");
            let product_code = $(this).data("code");
            let product_name = $(this).data("name");
            let unit = $(this).data("unit");

            let parent = $(this).closest(".product-list").parent();
            parent.find(".product-search").val(product_code);
            parent.find(".product-id").val(product_id);
            parent.find(".unit-field").val(unit);

            // ✅ หา input ชื่อสินค้าในแถวเดียวกัน แล้วใส่ชื่อ
            parent.closest('tr').find('input[name="gr_name[]"]').val(product_name);
            parent.closest('tr').find('input[name="unit[]"]').val(unit);

            $(this).parent().hide(); // ซ่อนผลลัพธ์
        });
    });
    </script>

<footer class="text-center py-3 mt-auto footer-bg">
    <small>© 2025 บริษัท มาคตางค์ จำกัด | ระบบคลังสินค้า</small>
</footer>

</body>
</html>
