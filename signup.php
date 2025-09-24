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
    $email = $_POST['email']; 
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $budget = floatval($_POST['budget']);

    $query1 = "INSERT INTO login_data (Email, Password, Budget) VALUES (?, ?, ?)"; 
    $stmt = mysqli_prepare($connect, $query1); 

    if ($stmt) { 
        mysqli_stmt_bind_param($stmt, "ssd", $email, $password, $budget); 
        if (mysqli_stmt_execute($stmt)) {
            echo "Signup successful! <a href='login.html'>Login here</a>"; 
        } else {
            echo "Failed to insert data: " . mysqli_stmt_error($stmt); 
        } 
        mysqli_stmt_close($stmt); 
    } else {
        echo "Query preparation failed: " . mysqli_error($connect);
    } 
} 
mysqli_close($connect);
?>