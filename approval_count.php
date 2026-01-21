<?php
include('server.php');

/* นับ GR Pending */
$sql_gr = "SELECT COUNT(*) AS cnt FROM goods_receipt WHERE gr_status = 'Pending'";
$gr_count = $conn->query($sql_gr)->fetch_assoc()['cnt'];

/* นับ GI Pending */
$sql_gi = "SELECT COUNT(*) AS cnt FROM goods_issue WHERE gi_status = 'Pending'";
$gi_count = $conn->query($sql_gi)->fetch_assoc()['cnt'];

$total_approval_count = $gr_count + $gi_count;
?>
