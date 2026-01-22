<?php
include('server.php');

/* นับ GR Reject */
$sql_reject_gr = "SELECT COUNT(*) AS cnt FROM goods_receipt WHERE gr_status = 'Reject'";
$gr_reject_count = $conn->query($sql_reject_gr)->fetch_assoc()['cnt'];

/* นับ GI Reject */
$sql_reject_gi = "SELECT COUNT(*) AS cnt FROM goods_issue WHERE gi_status = 'Reject'";
$gi_reject_count = $conn->query($sql_reject_gi)->fetch_assoc()['cnt'];

$total_reject_count = $gr_reject_count + $gi_reject_count;
?>
