<?php
    // ตรวจสอบว่า login แล้วหรือไม่
    session_start();
    if($_SESSION['username'] == "") {
        echo "<script type='text/javascript'>";
        echo "alert('Please Login');";
        echo "window.location = 'login.php'; ";
        echo "</script>";
    }
?>