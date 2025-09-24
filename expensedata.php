<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submission Details</title>
  <link rel="stylesheet" href="dataphp.css">
</head>
<body>

<?php
session_start();
// DB Config
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "budget_tracker";
$port = 3307;
$connect = new mysqli($servername, $username, $password, $dbname, $port);
// Connection check
if ($connect->connect_error) {
  die("Connection failed: " . $connect->connect_error);
}
// Check session user
if (!isset($_SESSION["user_id"])) {
  echo "<p style='color: red; text-align: center;'>You must be logged in to submit an expense.</p>";
  exit;
}
$user_id = $_SESSION["user_id"];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $category = isset($_POST["category"]) ? trim($_POST["category"]) : null;
  $amount = isset($_POST["amount"]) ? floatval($_POST["amount"]) : null;
  $date = isset($_POST["date"]) ? trim($_POST["date"]) : null;
  if ($category && $amount && $date) {
    // 1. Insert expense into expense_data
    $stmt = $connect->prepare("INSERT INTO expense_data(User_id, Category, Amount, Date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $user_id, $category, $amount, $date);
    if ($stmt->execute()) {
      // 2. Update budget in login_data
      $newBudget = $_SESSION["budget"] - $amount;
      $_SESSION["budget"] = $newBudget;
      $update_stmt = $connect->prepare("UPDATE login_data SET Budget = ? WHERE ID = ?");
      $update_stmt->bind_param("di", $newBudget, $user_id);
      $update_stmt->execute();
      $update_stmt->close();
      echo "<h2 style='color: green; text-align: center;'>Expense added successfully!</h2>";
      echo "<p style='text-align: center;'>Remaining Budget: ₹" . number_format($newBudget, 2) . "</p>";
    } else {
      echo "<p style='color: red; text-align: center;'>Failed to add expense. Please try again.</p>";
    }
    $stmt->close();
  } else {
    echo "<p style='color: red; text-align: center;'>All fields are required.</p>";
  }
} else {
  echo "<p style='color: red; text-align: center;'>Invalid request method.</p>";
}
$connect->close();
?>
</body>
</html>

