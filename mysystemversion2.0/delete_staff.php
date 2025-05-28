<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['staff_id'])) {
    $staff_id = $_POST['staff_id'];
    $admin_id = $_SESSION['user_id'];

    // Verify the staff belongs to the logged-in admin
    $stmt = $conn->prepare("DELETE FROM staffs WHERE id = ? AND added_by = ?");
    $stmt->bind_param("ii", $staff_id, $admin_id);
    $stmt->execute();

    $_SESSION['success_msg'] = "Staff deleted successfully.";
    header("Location: staff_list.php");
    exit;
}
?>
