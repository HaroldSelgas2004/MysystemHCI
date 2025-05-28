<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $deadline = $_POST['deadline'];
    $added_by = $_SESSION['user_id'];

    $sql = "INSERT INTO projects (title, description, start_date, deadline, added_by, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ssssi", $title, $description, $start_date, $deadline, $added_by);

    if ($stmt->execute()) {
        header("Location: project_list.php");
        exit;
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Add Project</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  body {
    background-color: #f0f2f5;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
      Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
    margin: 0;
    padding: 0;
  }
  .container {
    max-width: 640px;
    margin: 40px auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
    padding: 30px 35px;
  }
  h3 {
    color: #1877f2;
    font-weight: 600;
    font-size: 1.8rem;
    text-align: center;
    margin-bottom: 30px;
    text-shadow: 0 1px 1px rgba(0,0,0,0.1);
  }
  label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #050505;
  }
  input[type="text"],
  input[type="date"],
  textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccd0d5;
    border-radius: 8px;
    font-size: 1rem;
    font-family: inherit;
    transition: border-color 0.3s ease;
  }
  input[type="text"]:focus,
  input[type="date"]:focus,
  textarea:focus {
    border-color: #1877f2;
    box-shadow: 0 0 6px rgba(24,119,242,0.5);
    outline: none;
  }
  textarea {
    min-height: 100px;
    resize: vertical;
  }
  .btn-primary {
    background-color: #1877f2;
    border: none;
    font-weight: 600;
    padding: 10px 25px;
    font-size: 1rem;
    border-radius: 6px;
    box-shadow: 0 3px 8px rgb(24 119 242 / 0.4);
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  .btn-primary:hover {
    background-color: #0f5bb5;
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
    cursor: pointer;
    margin-left: 10px;
    transition: background-color 0.3s ease;
    text-decoration: none;
    display: inline-block;
  }
  .btn-secondary:hover {
    background-color: #cfd2d8;
    border-color: #b9bbc2;
    text-decoration: none;
    color: #050505;
  }
  .form-group {
    margin-bottom: 20px;
  }
  .alert-danger {
    background-color: #fcebea;
    color: #cc1f1a;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-weight: 600;
    box-shadow: 0 1px 3px rgb(204 31 26 / 0.2);
    text-align: center;
  }
  .form-actions {
    text-align: center;
    margin-top: 25px;
  }
</style>
</head>
<body>

<div class="container">
  <h3>Add Project</h3>

  <?php if (!empty($error)): ?>
    <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form action="project_add.php" method="post" novalidate>
    <div class="form-group">
      <label for="title">Project Title</label>
      <input type="text" id="title" name="title" required />
    </div>

    <div class="form-group">
      <label for="description">Description</label>
      <textarea id="description" name="description" required></textarea>
    </div>

    <div class="form-group">
      <label for="start_date">Start Date</label>
      <input type="date" id="start_date" name="start_date" required />
    </div>

    <div class="form-group">
      <label for="deadline">Deadline</label>
      <input type="date" id="deadline" name="deadline" required />
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Create Project</button>
      <a href="<?= $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'solo_dashboard.php' ?>" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>

</body>
</html>
