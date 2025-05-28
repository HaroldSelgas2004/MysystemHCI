
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $deadline = $_POST['deadline'];

    if ($is_admin) {
        $assigned_to = $_POST['assigned_staff_id'];
        $note = 'from admin Group Project';

        // Verify assigned staff belongs to this admin
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM staffs WHERE user_id = ? AND added_by = ?");
        $stmtCheck->bind_param("ii", $assigned_to, $user_id);
        $stmtCheck->execute();
        $stmtCheck->bind_result($count);
        $stmtCheck->fetch();
        $stmtCheck->close();
        if ($count == 0) {
            die("Error: You can only assign tasks to your own staff.");
        }
    } else {
        $assigned_to = $user_id;
        $note = '';
    }

    $stmt = $conn->prepare("INSERT INTO tasks (title, description, deadline, assigned_to, added_by, note) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $title, $description, $deadline, $assigned_to, $user_id, $note);

    if ($stmt->execute()) {
        if ($is_admin) {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: solo_dashboard.php");
        }
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Determine cancel URL based on role
$cancel_url = $is_admin ? "admin_dashboard.php" : "solo_dashboard.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Add Task</title>
<link href="https://fonts.googleapis.com/css2?family=Helvetica&display=swap" rel="stylesheet" />
<style>
  /* Facebook-style clean layout */
  body {
    background: #f0f2f5;
    font-family: 'Helvetica', Arial, sans-serif;
    margin: 0;
    padding: 0;
  }
  .container {
    max-width: 500px;
    margin: 60px auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgb(0 0 0 / 10%);
    padding: 30px 25px;
  }
  h2 {
    color: #1877f2;
    font-weight: 600;
    margin-bottom: 20px;
    font-size: 28px;
  }
  label {
    font-weight: 600;
    color: #050505;
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
  }
  input[type="text"],
  input[type="date"],
  select,
  textarea {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 20px;
    font-size: 15px;
    border: 1px solid #dddfe2;
    border-radius: 6px;
    background: #f5f6f7;
    transition: background 0.3s ease, border-color 0.3s ease;
  }
  input[type="text"]:focus,
  input[type="date"]:focus,
  select:focus,
  textarea:focus {
    background: #fff;
    border-color: #1877f2;
    outline: none;
  }
  textarea {
    resize: vertical;
    min-height: 80px;
  }
  .btn-primary {
    background-color: #1877f2;
    border: none;
    color: white;
    font-weight: 600;
    padding: 10px 18px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
  }
  .btn-primary:hover {
    background-color: #165ecc;
  }
  .btn-secondary {
    background-color: transparent;
    border: none;
    color: #1877f2;
    font-weight: 600;
    padding: 10px 18px;
    margin-left: 15px;
    cursor: pointer;
    font-size: 16px;
    text-decoration: underline;
  }
  .error-msg {
    color: #d93025;
    margin-bottom: 15px;
    font-weight: 600;
  }
</style>
</head>
<body>

<div class="container">
  <h2>Add Task</h2>

  <?php if (!empty($error)): ?>
    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" action="">
    <label for="title">Task Title</label>
    <input type="text" id="title" name="title" required />

    <label for="description">Description</label>
    <textarea id="description" name="description" required></textarea>

    <label for="deadline">Deadline</label>
    <input type="date" id="deadline" name="deadline" required />

    <?php if ($is_admin): ?>
    <label for="assigned_staff_id">Assign to Staff</label>
    <select id="assigned_staff_id" name="assigned_staff_id" required>
      <option value="">Select Staff</option>
      <?php
      $stmt = $conn->prepare("SELECT u.id, u.username FROM staffs s JOIN users u ON s.user_id = u.id WHERE s.added_by = ?");
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      while ($row = $result->fetch_assoc()) {
          echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['username']) . "</option>";
      }
      ?>
    </select>
    <?php endif; ?>

    <button type="submit" class="btn-primary">Create Task</button>
    <a href="<?= $cancel_url ?>" class="btn-secondary">Cancel</a>
  </form>
</div>

</body>
</html>
