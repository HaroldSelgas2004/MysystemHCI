<?php
include 'db_connect.php';
$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Email already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            $success = true;
        } else {
            $message = "Registration failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Task Management</title>
    <style>
         body {
            background:url('RegisPhoto.jpg');
            background-color: #e9ebee;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-box {
            background: white;
            padding: 30px;
            width: 350px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        h2 {
            color: #1877f2;
            text-align: center;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #1877f2;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .link {
            text-align: center;
            margin-top: 10px;
        }
        .error {
            color: red;
            font-size: 13px;
            text-align: center;
        }
    </style>
    <?php if ($success): ?>
    <script>
        setTimeout(function() {
            alert("Account registered");
            window.location.href = "login_process.php";
        }, 500); // alert shows after 0.5 sec

        setTimeout(function() {
            window.location.href = "login_process.php";
        }, 5000); // redirect after 5 sec
    </script>
    <?php endif; ?>
</head>
<body>
<div class="form-box">
    <h2>Task Management</h2>
    <form method="post" action="register.php">
        <?php if ($message): ?><p class="error"><?= $message ?></p><?php endif; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
        <div class="link">
            Already have an account? <a href="login_process.php">Login here</a>
        </div>
    </form>
</div>
</body>
</html>
