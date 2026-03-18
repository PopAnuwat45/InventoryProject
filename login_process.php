<?php
    session_start();
    include('server.php');

    if (isset($_POST['submit'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
    }


    $stmt = $conn->prepare("SELECT * FROM user_tb WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();


    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $hashPasswordFromDB = $user['password'];
        // สร้าง session user_id, type เก็บค่า username และ type
        
        if (password_verify($password, $hashPasswordFromDB)) {
            $_SESSION['user_id'] = $user['username'];
            $_SESSION['type'] = $user['type'];
            $_SESSION['name'] = $user['name'];
            echo "<script type='text/javascript'>";
            echo "alert('Login Succesfully');";
            echo "window.location = 'index.php'; ";
            echo "</script>";
        } else {
        echo "<script type='text/javascript'>";
        echo "alert('Error back to Login again');";
        echo "window.location = 'login.php'; ";
        echo "</script>";
        }
        
    } else {
        echo "<script type='text/javascript'>";
        echo "alert('Error back to Login again');";
        echo "window.location = 'login.php'; ";
        echo "</script>";
    }
    
?>