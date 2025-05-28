<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$is_admin = ($role === 'admin');

// Fetch tasks
if ($is_admin) {
    $sql = "SELECT t.*, u.username AS assigned_username 
            FROM tasks t 
            JOIN users u ON t.assigned_to = u.id
            WHERE t.added_by = ? OR t.assigned_to = ?
            ORDER BY t.deadline ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $user_id);
} else {
    $sql = "SELECT t.*, u.username AS assigned_username 
            FROM tasks t 
            JOIN users u ON t.assigned_to = u.id
            WHERE t.assigned_to = ?
            ORDER BY t.deadline ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $task_id = $_POST['task_id'];
        $new_status = $_POST['status'];

        $check = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND assigned_to = ?");
        $check->bind_param("ii", $task_id, $user_id);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows > 0) {
            $update = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
            $update->bind_param("si", $new_status, $task_id);
            $update->execute();
        }
    }

    // Handle delete
    if ($is_admin && isset($_POST['delete_task'])) {
        $task_id = $_POST['task_id'];

        $check = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND added_by = ?");
        $check->bind_param("ii", $task_id, $user_id);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows > 0) {
            $delete = $conn->prepare("DELETE FROM tasks WHERE id = ?");
            $delete->bind_param("i", $task_id);
            $delete->execute();
        }
    }

    header("Location: task_list.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Task List</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style> /* your same styles */
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
        border-radius: 12px 12px 0 0;
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
    .no-tasks {
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
</style>
</head>
<body>

<h3>Task List</h3>

<table class="table table-hover align-middle rounded shadow-sm">
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Deadline</th>
            <th>Assigned To</th>
            <th>Status</th>
            <th>Note</th>
            <th>Actions</th>
        </tr>
    </thead>
  <tbody>
<?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
            <td><?= htmlspecialchars($row['deadline']) ?></td>
            <td><?= htmlspecialchars($row['assigned_username']) ?></td>
            <td><?= htmlspecialchars($row['note']) ?></td>
            <td>
                <form action="update_task_status.php" method="post" style="display:inline-block;">
                    <input type="hidden" name="task_id" value="<?= $row['id'] ?>">
                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                        <option disabled selected>Update Status</option>
                        <option value="Not Started">Not Started</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Completed">Completed</option>
                    </select>
                </form>
                <?php
                    $can_delete = false;
                    // Admins can delete tasks they added
                    if ($is_admin && $row['added_by'] == $user_id) {
                        $can_delete = true;
                    }
                    // Solo users can delete only their own tasks
                    if (!$is_admin && $row['added_by'] == $user_id && $row['assigned_to'] == $user_id) {
                        $can_delete = true;
                    }
                ?>
                <?php if ($can_delete): ?>
                    <form action="delete_task.php" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this task?');">
                        <input type="hidden" name="task_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="6" class="no-tasks">No tasks found.</td>
    </tr>
<?php endif; ?>
</tbody>

</table>

<div class="btn-group">
    <a href="<?= $is_admin ? 'admin_dashboard.php' : 'solo_dashboard.php' ?>" class="btn btn-secondary">Back to Dashboard</a>
    <a href="add_task.php" class="btn btn-primary">Add New Task</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>








    