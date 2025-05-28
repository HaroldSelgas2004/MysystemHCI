<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Fetch staff added by this admin
$stmt = $conn->prepare("
    SELECT s.id AS staff_id, u.username, u.email
    FROM staffs s
    JOIN users u ON s.user_id = u.id
    WHERE s.added_by = ?
");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$staffs = $result->fetch_all(MYSQLI_ASSOC);

$message = "";
if (isset($_SESSION['success_msg'])) {
    $message = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Staff List</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
    body {
        background-color: #f0f2f5;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
            Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
        margin: 30px auto;
        max-width: 900px;
        padding: 0 15px;
    }
    h3 {
        color: #1877f2;
        font-weight: 600;
        margin-bottom: 25px;
        font-size: 1.8rem;
        text-align: center;
        text-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }
    table {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgb(0 0 0 / 0.1);
    }
    thead tr {
        background-color: #1877f2;
        color: white;
    }
    th, td {
        padding: 15px 18px !important;
        vertical-align: middle !important;
        font-size: 0.95rem;
    }
    tbody tr:hover {
        background-color: #e7f3ff;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    tbody tr:nth-child(even) {
        background-color: #f9fbfd;
    }
    .no-staff {
        text-align: center;
        padding: 30px 0;
        color: #606770;
        font-size: 1.1rem;
        font-style: italic;
    }
    .btn-group {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        gap: 15px;
    }
    .btn-primary {
        background-color: #1877f2;
        border-color: #1877f2;
        font-weight: 600;
        padding: 10px 25px;
        font-size: 1rem;
        border-radius: 6px;
        box-shadow: 0 3px 8px rgb(24 119 242 / 0.4);
        transition: background-color 0.3s ease;
    }
    .btn-primary:hover {
        background-color: #0f5bb5;
        border-color: #0f5bb5;
        box-shadow: 0 4px 12px rgb(15 91 181 / 0.6);
    }
    .btn-secondary {
        background-color: #e4e6eb;
        border-color: #d8dadf;
        color: #050505;
        font-weight: 600;
        padding: 10px 25px;
        font-size: 1rem;
        border-radius: 6px;
        transition: background-color 0.3s ease;
    }
    .btn-secondary:hover {
        background-color: #cfd2d8;
        border-color: #b9bbc2;
    }
    .message {
        color: green;
        text-align: center;
        margin-bottom: 20px;
        font-weight: 500;
    }
</style>
</head>
<body>

<h3>Staff List</h3>

<?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table class="table table-hover align-middle rounded shadow-sm">
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($staffs) > 0): ?>
            <?php foreach ($staffs as $staff): ?>
                <tr>
                    <td><?= htmlspecialchars($staff['username']) ?></td>
                    <td><?= htmlspecialchars($staff['email']) ?></td>
                    <td>
                        <form method="post" action="delete_staff.php" onsubmit="return confirm('Are you sure you want to delete this staff?');">
                            <input type="hidden" name="staff_id" value="<?= $staff['staff_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" class="no-staff">No staff added yet.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="btn-group">
    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    <a href="staff_add.php" class="btn btn-primary">Add New Staff</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
