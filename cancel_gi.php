<?php
include('server.php');

if(isset($_GET['gi_id'])){

    $gi_id = intval($_GET['gi_id']);

    $sql = "UPDATE goods_issue
            SET gi_status = 'Cancel'
            WHERE gi_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i",$gi_id);
    $stmt->execute();
}

$tab = $_GET['tab'] ?? 'gi';

header("Location: reject_list.php?tab=".$tab);
exit();
?>