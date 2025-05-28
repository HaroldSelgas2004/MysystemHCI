<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'solo') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Replace with actual user profile pic URL or default pic
$profile_pic_url = 'default_profile.png';

// Count totals filtered by this user's user_id
$stmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE added_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($task_count);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE added_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($project_count);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Solo Dashboard - Task Management</title>
<style>
  body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f2f5;
    display: flex;
    height: 100vh;
  }
  .sidebar {
    background: white;
    width: 260px;
    box-shadow: 2px 0 6px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    padding: 20px;
  }
  .profile-section {
    text-align: center;
    margin-bottom: 30px;
  }
  .profile-section .profile-pic {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #1877f2;
  }
  .profile-section .username {
    margin-top: 12px;
    font-weight: 700;
    font-size: 1.2rem;
    color: #1877f2;
  }
  .menu {
    flex-grow: 1;
  }
  .menu-item {
    padding: 12px 15px;
    font-weight: 600;
    font-size: 1rem;
    color: #444;
    cursor: pointer;
    border-radius: 8px;
    margin-bottom: 6px;
    user-select: none;
    transition: background 0.3s ease;
  }
  .menu-item:hover,
  .menu-item.active {
    background: #e7f0ff;
    color: #1877f2;
  }
  .submenu {
    background: #f7f9ff;
    border-radius: 8px;
    margin-left: 12px;
    margin-bottom: 15px;
    display: none;
    flex-direction: column;
  }
  .submenu-item {
    padding: 10px 18px;
    font-weight: 500;
    font-size: 0.9rem;
    color: #555;
    cursor: pointer;
    border-radius: 6px;
    margin: 4px 0;
    user-select: none;
    transition: background 0.2s ease;
  }
  .submenu-item:hover {
    background: #d6e4ff;
    color: #1877f2;
  }
  .dashboard-content {
    flex-grow: 1;
    padding: 25px 30px;
    background: #fff;
    overflow-y: auto;
  }
  .dashboard-cards {
    display: flex;
    gap: 20px;
  }
  .card {
    flex: 1;
    background: #1877f2;
    color: white;
    padding: 25px 20px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(24, 119, 242, 0.4);
    cursor: pointer;
    user-select: none;
    transition: background 0.3s ease;
    text-align: center;
  }
  .card:hover {
    background: #165ecf;
  }
  .logout-btn {
    display: inline-block;
    margin-top: 10px;
    background: #e9ebee;
    color: #1877f2;
    border: none;
    padding: 8px 20px;
    font-weight: 600;
    border-radius: 25px;
    cursor: pointer;
    transition: background 0.3s ease;
    width: 100%;
    font-size: 1rem;
  }
  .logout-btn:hover {
    background: #1877f2;
    color: white;
  }
</style>
<script>
  function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    const isVisible = submenu.style.display === "flex";
    // close all submenus
    document.querySelectorAll('.submenu').forEach(el => el.style.display = 'none');
    // toggle clicked submenu
    submenu.style.display = isVisible ? "none" : "flex";

    // toggle active class on menu items
    document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
    if (!isVisible) {
      document.querySelector(`[onclick="toggleSubmenu('${id}')"]`).classList.add('active');
    }
  }
</script>
</head>
<body>

<div class="sidebar">
  <div class="profile-section">
    <img src="<?= htmlspecialchars($profile_pic_url) ?>" alt="Profile Picture" class="profile-pic" />
    <div class="username"><?= htmlspecialchars($username) ?></div>
  </div>

  <div class="menu">
    <div class="menu-item" onclick="toggleSubmenu('taskSubmenu')">TaskManage</div>
    <div class="submenu" id="taskSubmenu">
      <div class="submenu-item" onclick="location.href='add_task.php'">Add Task</div>
      <div class="submenu-item" onclick="location.href='task_list.php'">Task List</div>
    </div>

    <div class="menu-item" onclick="toggleSubmenu('projectSubmenu')">ProjectManage</div>
    <div class="submenu" id="projectSubmenu">
      <div class="submenu-item" onclick="location.href='project_add.php'">Add Project</div>
      <div class="submenu-item" onclick="location.href='project_list.php'">Project List</div>
    </div>

    <form action="logout.php" method="post" style="margin-top: 10px;">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </div>
</div>

<div class="dashboard-content">
  <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
  <div class="dashboard-cards">
    <div class="card" onclick="location.href='task_list.php'">
      <h3><?= $task_count ?></h3>
      <p>Tasks</p>
    </div>
    <div class="card" onclick="location.href='project_list.php'">
      <h3><?= $project_count ?></h3>
      <p>Projects</p>
    </div>
  </div>
</div>

</body>
</html>
