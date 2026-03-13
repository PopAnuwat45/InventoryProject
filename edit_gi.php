<?php
include('server.php');

/* ===============================
   รับค่า gi_id
================================ */
if (!isset($_GET['gi_id'])) {
    die('ไม่พบข้อมูลใบเบิกสินค้า');
}

$gi_id = intval($_GET['gi_id']);

/* ===============================
   ดึงข้อมูลหัว GI
================================ */
$sql_gi = "SELECT * FROM goods_issue 
           WHERE gi_id = ? AND gi_status='Reject'";

$stmt = $conn->prepare($sql_gi);
$stmt->bind_param("i",$gi_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows==0){
    die('ไม่พบรายการ หรือไม่สามารถแก้ไขได้');
}

$gi = $result->fetch_assoc();

/* ===============================
   ดึงรายการสินค้า
================================ */
$sql_items = "SELECT 
                gii.gi_item_id,
                gii.product_id,
                gii.gi_qty,
                p.product_id_full,
                p.product_name,
                u.unit_name
            FROM goods_issue_item gii
            JOIN product p ON gii.product_id = p.product_id
            LEFT JOIN unit u ON p.unit_id = u.unit_id
            WHERE gii.gi_id = ?";

$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i",$gi_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

/* ===============================
   บันทึกการแก้ไข
================================ */
if($_SERVER['REQUEST_METHOD']=='POST'){

$conn->begin_transaction();

try{

$product_ids = $_POST['product_id'] ?? [];
$qtys = $_POST['gi_qty'] ?? [];
$item_ids = $_POST['gi_item_id'] ?? [];

$existing_ids=[];

/* ===============================
   Loop update / insert
================================ */

foreach($product_ids as $index=>$product_id){

$product_id = intval($product_id);
$qty = intval($qtys[$index]);
$gi_item_id = intval($item_ids[$index]);

if($product_id<=0 || $qty<=0){
continue;
}

/* UPDATE */

if($gi_item_id>0){

$sql_update="UPDATE goods_issue_item 
             SET gi_qty=? 
             WHERE gi_item_id=?";

$stmt_update=$conn->prepare($sql_update);
$stmt_update->bind_param("ii",$qty,$gi_item_id);
$stmt_update->execute();

$existing_ids[]=$gi_item_id;

}

/* INSERT */

else{

$sql_insert="INSERT INTO goods_issue_item
            (gi_id,product_id,gi_qty)
            VALUES (?,?,?)";

$stmt_insert=$conn->prepare($sql_insert);
$stmt_insert->bind_param("iii",$gi_id,$product_id,$qty);
$stmt_insert->execute();

$new_id=$conn->insert_id;
$existing_ids[]=$new_id;

}

}

/* ===============================
   ลบรายการที่ถูกลบจากหน้าเว็บ
================================ */

if(!empty($existing_ids)){

$ids=implode(",",array_map('intval',$existing_ids));

$sql_delete="
DELETE FROM goods_issue_item
WHERE gi_id=$gi_id
AND gi_item_id NOT IN ($ids)
";

$conn->query($sql_delete);

}

/* ===============================
   เปลี่ยนสถานะเป็น Pending
================================ */

$sql_status="UPDATE goods_issue
             SET gi_status='Pending'
             WHERE gi_id=?";

$stmt_status=$conn->prepare($sql_status);
$stmt_status->bind_param("i",$gi_id);
$stmt_status->execute();

$conn->commit();

echo "<script>
alert('แก้ไขและส่งใหม่เรียบร้อย');
window.location='reject_list.php';
</script>";

exit;

}
catch(Exception $e){

$conn->rollback();
die("เกิดข้อผิดพลาด : ".$e->getMessage());

}

}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>แก้ไขใบเบิกสินค้า</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">

</head>

<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark main-nav">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="img/logo.jpg" width="100" height="30" class="me-2">
            ระบบคลังสินค้า
        </a>
    </div>
</nav>

<div class="container my-4 flex-grow-1">

<h5 class="fw-bold mb-2">
แก้ไขใบเบิกสินค้า (ถูกไม่อนุมัติ)
</h5>

<a href="javascript:history.back()" class="btn btn-outline-danger btn-sm mb-3">
⬅️ กลับ
</a>

<!-- ข้อมูลหัวเอกสาร -->

<div class="card mb-3">
    <div class="card-body">

        <div class="row">

            <div class="col-md-4">
                <strong>เลขที่ GI:</strong> <?= $gi['gi_number']; ?>
            </div>

            <div class="col-md-4">
                <strong>วันที่:</strong> <?= $gi['gi_date']; ?>
            </div>

            <div class="col-md-4">
                <strong>สถานะ:</strong>
                <span class="badge bg-danger">Reject</span>
            </div>

        </div>

        <div class="row mt-2">

            <div class="col-md-4">
            <strong>เลขที่ SO:</strong> <?= $gi['ref_so_number']; ?>
            </div>

            <div class="col-md-4">
            <strong>ผู้ทำรายการ:</strong> <?= $gi['created_by']; ?>
            </div>

            <div class="col-md-4"><strong>เหตุผลที่ไม่อนุมัติ:</strong> <?= $gi['reject_reason']; ?></div>

        </div>

    </div>
</div>

<form method="POST">

<div class="mb-3">

<label class="form-label">รายการสินค้า</label>

<table class="table table-bordered table-striped" id="gi_items_table">

<thead class="table-warning">

<tr>
<th>รหัสสินค้า</th>
<th>ชื่อสินค้า</th>
<th width="120">จำนวน</th>
<th width="120">หน่วย</th>
<th width="90">ลบ</th>
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
autocomplete="off">

<div class="product-list"></div>

<input type="hidden"
name="product_id[]"
class="product-id"
value="<?= $item['product_id']; ?>">

<input type="hidden"
name="gi_item_id[]"
value="<?= $item['gi_item_id']; ?>">

</div>
</td>

<td>
<input type="text"
name="gi_name[]"
class="form-control"
value="<?= $item['product_name']; ?>"
readonly>
</td>

<td>
<input type="number"
name="gi_qty[]"
class="form-control"
min="1"
value="<?= $item['gi_qty']; ?>"
required>
</td>

<td>
<input type="text"
name="unit[]"
class="form-control unit-field"
value="<?= $item['unit_name']; ?>"
readonly>
</td>

<td>
<button type="button"
class="btn btn-danger btn-sm remove-row">
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

</form>

</div>



<footer class="text-center py-3 mt-auto footer-bg">
    <small>© 2025 บริษัท มาคตางค์ จำกัด | ระบบคลังสินค้า</small>
</footer>


<script>

document.addEventListener('DOMContentLoaded', function(){

const addBtn = document.getElementById('add_item_btn')
const tableBody = document.querySelector('#gi_items_table tbody')

addBtn.addEventListener('click', function(){

const firstRow = tableBody.querySelector('tr')
const newRow = firstRow.cloneNode(true)

newRow.querySelectorAll('input').forEach(input => input.value='')

newRow.querySelector('input[name="gi_item_id[]"]').value=''

tableBody.appendChild(newRow)

})

tableBody.addEventListener('click', function(e){

if(e.target.classList.contains('remove-row')){

const rows = tableBody.querySelectorAll('tr')

if(rows.length > 1){

e.target.closest('tr').remove()

}else{

alert('ต้องมีสินค้าอย่างน้อย 1 รายการ')

}

}

})

})



</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function(){

$(document).on("keyup", ".product-search", function(){

let query = $(this).val()
let inputField = $(this)
let resultBox = $(this).siblings(".product-list")

if(query.length >= 2){

$.ajax({
url: "search_product.php",
method: "POST",
data: {query: query},
success: function(data){
resultBox.html(data)
resultBox.show()
}
})

}else{

resultBox.hide()

}

})

$(document).on("click", ".product-item", function(){

let product_id = $(this).data("id")
let product_code = $(this).data("code")
let product_name = $(this).data("name")
let unit = $(this).data("unit")

let parent = $(this).closest(".product-list").parent()

parent.find(".product-search").val(product_code)
parent.find(".product-id").val(product_id)
parent.find(".unit-field").val(unit)

parent.closest('tr').find('input[name="gi_name[]"]').val(product_name)
parent.closest('tr').find('input[name="unit[]"]').val(unit)

$(this).parent().hide()

})

})
</script>
</body>
</html>