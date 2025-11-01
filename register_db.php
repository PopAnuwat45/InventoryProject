<?php
    include('server.php');

    if (isset($_POST['sub'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $name = 'admin';
        $type = 'admin';

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    }

    


    $sql = "INSERT INTO user_tb (username, password, name,  type) 
            VALUES ('$username', '$hashedPassword', '$name', '$type')";
    
    //check
    if ($conn->query($sql) === TRUE) {
        echo "<script type='text/javascript'>";
        echo "alert('Register Succesfully');";
        echo "window.location = 'login.html'; ";
        echo "</script>";
    }
    else {
        echo "<script type='text/javascript'>";
        echo "alert('Error back to Regis again');";
        echo "window.location = 'register.html'; ";
        echo "</script>";
    }
    
?>