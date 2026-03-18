<?php
session_start();
session_unset();
session_destroy();

echo "<script type='text/javascript'>";
        echo "alert('Logout Succesfully');";
        echo "window.location = 'login.php'; ";
        echo "</script>";

exit;
?>
