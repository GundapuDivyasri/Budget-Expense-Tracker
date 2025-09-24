<?php 
session_start();
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "budget_tracker";
$port = 3307;

$connect = new mysqli($servername, $username, $password, $dbname, $port);
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}
// Check user login
if (!isset($_SESSION["user_id"])) {
    echo "<h1>Please log in to view your expenses</h1>";
    exit();
}
$user_id = $_SESSION["user_id"];
// Handle DELETE
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $connect->prepare("DELETE FROM expense_data WHERE Id = ? AND User_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: data.php");
    exit();
}
// Handle INSERT/UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = isset($_POST["category"]) ? trim($_POST["category"]) : null;
    $amount = isset($_POST["amount"]) ? floatval($_POST["amount"]) : null;
    $date = isset($_POST["date"]) ? trim($_POST["date"]) : null;
    $budget = isset($_POST["budget"]) ? floatval($_POST["budget"]) : null;
    if ($category && $amount && $date) {
        $stmt = $connect->prepare("INSERT INTO expense_data (User_id, Category, Amount, Date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $user_id, $category, $amount, $date);
        $stmt->execute();
        $stmt->close();
    }
    if ($budget !== null && $budget > 0) {
        $stmt = $connect->prepare("UPDATE login_data SET Budget = ? WHERE ID = ?");
        $stmt->bind_param("di", $budget, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}
// Fetch budget
$budget_stmt = $connect->prepare("SELECT Budget FROM login_data WHERE ID = ?");
$budget_stmt->bind_param("i", $user_id);
$budget_stmt->execute();
$budget_result = $budget_stmt->get_result();
$budget = ($row = $budget_result->fetch_assoc()) ? $row['Budget'] : 0.00;
$budget_stmt->close();
// Fetch expenses
$expense_stmt = $connect->prepare("SELECT Id, Category, Amount, Date FROM expense_data WHERE User_id = ?");
$expense_stmt->bind_param("i", $user_id);
$expense_stmt->execute();
$expenses = $expense_stmt->get_result();
$total_spent = 0;
$expense_rows = [];
while ($row = $expenses->fetch_assoc()) {
    $total_spent += $row['Amount'];
    $expense_rows[] = $row;
}
$remaining_budget = $budget - $total_spent;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Expense Data</title>
    <link rel="stylesheet" type="text/css" href="dataphp.css?ver=1.1">
</head>
<body>
<header class="header">
    <div class="name">My Tracker</div>
    <nav class="nav-bar">
        <ul class="nav-links">
            <li><a href="home.html">Home</a></li>
            <li><a href="data.php">Expenses</a></li>
            <li><a href="logout.php">Logout</a></li> 
        </ul>
    </nav>
</header>
<main>
    <h1>Your Expense Data</h1>
    <?php if (!empty($expense_rows)): ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Category</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            <?php foreach ($expense_rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Category']) ?></td>
                    <td>₹<?= number_format($row['Amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['Date']) ?></td>
                    <td>
                        <a href="data.php?delete_id=<?= $row['Id'] ?>" onclick="return confirm('Are you sure you want to delete this expense?');">🗑️ Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <h2>No expenses found</h2>
    <?php endif; ?>
    <h2>Budget: ₹<?= number_format($budget, 2) ?></h2>
    <h2>Total Spent: ₹<?= number_format($total_spent, 2) ?></h2>
    <h2>Remaining Balance: ₹<?= number_format($remaining_budget, 2) ?></h2>
    <!-- <h2>Add Expense / Update Budget</h2>
    <form method="POST" action="data.php" style="display: flex; flex-direction: column; max-width: 300px;">
        <label for="category">Category:</label>
        <input type="text" name="category" required>

        <label for="amount">Amount:</label>
        <input type="number" name="amount" step="0.01" required>

        <label for="date">Date:</label>
        <input type="date" name="date" required>

        <label for="budget">Update Budget (optional):</label>
        <input type="number" name="budget" step="0.01">

        <br>
        <button type="submit">Submit</button>
    </form> -->
</main>
<?php
$expense_stmt->close();
$connect->close();
?>
</body>
</html>
