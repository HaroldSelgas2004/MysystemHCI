<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$message = "";
$search_results = [];

// Handle success message from redirect
if (isset($_SESSION['success_msg'])) {
    $message = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

// Handle search
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = "%" . trim($_GET['search']) . "%";
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE (username LIKE ? OR email LIKE ?) AND id != ?");
    $stmt->bind_param("ssi", $search_term, $search_term, $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $search_results = $result->fetch_all(MYSQLI_ASSOC);
}

// Handle staff add
if (isset($_POST['add_staff_id'])) {
    $user_to_add = intval($_POST['add_staff_id']);
    $check = $conn->prepare("SELECT id FROM staffs WHERE user_id = ? AND added_by = ?");
    $check->bind_param("ii", $user_to_add, $admin_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "User is already in your staff list.";
    } else {
        $insert = $conn->prepare("INSERT INTO staffs (user_id, added_by) VALUES (?, ?)");
        $insert->bind_param("ii", $user_to_add, $admin_id);
        if ($insert->execute()) {
            $_SESSION['success_msg'] = "Staff added successfully!";
            header("Location: staff_list.php");
            exit;
        } else {
            $message = "Failed to add staff.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Staff - Task Management</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #1877f2;
            text-align: center;
        }
        .top-buttons {
            text-align: center;
            margin-bottom: 20px;
        }
        a.button {
            display: inline-block;
            margin: 0 5px;
            padding: 10px 15px;
            background-color: #1877f2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .message {
            color: green;
            text-align: center;
            margin: 10px 0;
        }
        input[type="text"] {
            width: 70%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button.search-btn {
            padding: 10px 15px;
            background-color: #1877f2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 5px;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px 15px;
            border-bottom: 1px solid #dddfe2;
        }
        .add-btn {
            background-color: #42b72a;
            padding: 6px 12px;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .add-btn:hover {
            background-color: #36a420;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add Staff</h2>

        <div class="top-buttons">
            <a href="admin_dashboard.php" class="button">← Back to Dashboard</a>
            <a href="staff_list.php" class="button">📋 View Staff List</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="get" action="">
            <input type="text" name="search" placeholder="Search by username or email" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" required>
            <button type="submit" class="search-btn">Search</button>
        </form>

        <?php if (!empty($search_results)): ?>
            <table>
                <thead><tr><th>Username</th><th>Email</th><th>Add</th></tr></thead>
                <tbody>
                <?php foreach ($search_results as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="add_staff_id" value="<?= $user['id'] ?>">
                                <button type="submit" class="add-btn">+</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (isset($_GET['search'])): ?>
            <p>No users found matching "<?= htmlspecialchars($_GET['search']) ?>"</p>
        <?php endif; ?>
    </div>
</body>
</html>
