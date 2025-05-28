<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$is_admin = ($role === 'admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'];

    // Only delete if the user added it (admin or solo)
    $sql = "DELETE FROM tasks WHERE id = ? AND added_by = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $task_id, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Task deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete task.";
    }

    header("Location: task_list.php");
    exit();
}
?>
