<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ | ระบบคลังสินค้า</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt&display=swap" rel="stylesheet">

    <!-- CSS หลัก -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        .login-container {
            min-height: 100vh;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background: #fff;
            padding: 30px;
        }

        .login-logo {
            width: 120px;
            margin-bottom: 15px;
        }

        .login-title {
            color: var(--main-blue);
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="container d-flex justify-content-center align-items-center login-container">

    <div class="login-card text-center">

        <!-- Logo -->
        <img src="img/logo.jpg" class="login-logo">

        <!-- Title -->
        <h4 class="login-title mb-3">ระบบคลังสินค้า</h4>

        <!-- Form -->
        <form method="POST" action="login_process.php">

            <!-- Username -->
            <div class="mb-3 text-start">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" autocomplete="off" required>
            </div>

            <!-- Password -->
            <div class="mb-3 text-start">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" autocomplete="off" required>
            </div>

            <!-- Button -->
            <button type="submit" name="submit" class="btn btn-primary w-100">
                เข้าสู่ระบบ
            </button>

        </form>

    </div>

</div>

</body>
</html>