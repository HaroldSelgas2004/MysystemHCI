<?php
session_start();
include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $login_as = $_POST['login_as'];

    if (empty($username) || empty($password) || empty($login_as)) {
        $error = "All fields are required";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Login success: save session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $login_as;

                if ($login_as === "admin") {
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    header("Location: solo_dashboard.php");
                    exit();
                }
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Account not found";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Task Management</title>
    <style>
        body {
            background:url('BangaLogoSchool.png');
            background-color: #e9ebee;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-box {
            background: #fff;
            padding: 30px 25px;
            width: 360px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #1877f2;
            text-align: center;
            margin-bottom: 25px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
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
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #165ecf;
        }

        .link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .link a {
            color: #1877f2;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            text-align: center;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Task Management</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Username or Email" required value="<?= htmlspecialchars($username ?? '') ?>">
            <input type="password" name="password" placeholder="Password" required>
            <select name="login_as" required>
                <option value="">Login as...</option>
                <option value="admin" <?= (isset($login_as) && $login_as === 'admin') ? 'selected' : '' ?>>Admin(GroupLeader)</option>
                <option value="solo" <?= (isset($login_as) && $login_as === 'solo') ? 'selected' : '' ?>>Staff(Individual)</option>
            </select>
            <button type="submit">Login</button>
            <div class="link">
                No account? <a href="register.php">Register here</a>
            </div>
        </form>
    </div>
</body>
</html>
