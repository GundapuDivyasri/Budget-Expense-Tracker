<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "budget_tracker";
$port = 3307;
$connect = mysqli_connect($servername, $username, $password, $dbname, $port);
if (!$connect) die("Connection failed: " . mysqli_connect_error());
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $stmt = $connect->prepare("SELECT id, password, budget FROM login_data WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $budget);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["budget"] = $budget;
            header("Location: tracker.html");
            exit();
        } else {
            echo "Invalid credentials. <a href='login.html'>Try again</a>";
        }
    } else {
        echo "User not found. <a href='signup.html'>Sign up here</a>";
    }
    $stmt->close();
}
$connect->close();
?>