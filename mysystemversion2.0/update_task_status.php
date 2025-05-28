<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];

    // Update only if user is assigned to this task
    $sql = "UPDATE tasks SET status=? WHERE id=? AND assigned_to=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $status, $task_id, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Task status updated.";
    } else {
        $_SESSION['error'] = "Failed to update status.";
    }

    header("Location: task_list.php");
    exit();
}
?>
