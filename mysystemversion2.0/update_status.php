<?php
// Check if user is assigned to this task
$task_id = $_GET['id'];
$query = "SELECT * FROM tasks WHERE id = $task_id";
$result = mysqli_query($conn, $query);
$task = mysqli_fetch_assoc($result);

if ($_SESSION['user_id'] != $task['assigned_to']) {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $update = "UPDATE tasks SET status = '$status' WHERE id = $task_id";
    mysqli_query($conn, $update);
    header('Location: task_list.php');
}
?>

<form method="POST">
    <select name="status">
        <option value="not started" <?= $task['status'] == 'not started' ? 'selected' : '' ?>>Not Started</option>
        <option value="ongoing" <?= $task['status'] == 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
        <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
    </select>
    <button type="submit">Update Status</button>
</form>
