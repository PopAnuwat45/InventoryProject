<?php
include('server.php');

if(isset($_GET['gr_id'])){

    $gr_id = intval($_GET['gr_id']);

    $sql = "UPDATE goods_receipt 
            SET gr_status = 'Cancel'
            WHERE gr_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i",$gr_id);
    $stmt->execute();
}

$tab = $_GET['tab'] ?? 'gr';

header("Location: reject_list.php?tab=".$tab);
exit();
?>