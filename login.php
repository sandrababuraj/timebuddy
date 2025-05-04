<?php
session_start(); // Start the session

require 'connection.php';

// Variable to store login status
$loginStatus = '';

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $query = "SELECT * FROM data WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Store username in session upon successful login
        $_SESSION['username'] = $username;
        header("Location: profile.php");
        exit(); // Ensure that no further code is executed after redirection
    } else {
        $loginStatus = 'Login failed. Please check your username and password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <meta charset="utf-8">
    <title>Insert Data</title>
    <link rel="stylesheet" href="style3.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200&display=swap" rel="stylesheet">
    
</head>
<body>
<div class="bgimg">
    <div id="home">
        <div class="navbar">
            <ul class="navbar list">
                <li><a href="home.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">SignUp</a></li>
                <li><a href="about.php">About</a></li>
                </ul>
            <!-- <hr class="navborder"> -->
</div>
<div class="imgform"> <img src="stu.png">
    <form action="" method="post" autocomplete="off">
    <div class="form">
            <h1>Log In</h1>
        <label for="username">Username:</label>
        <input type="text" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit" name="login">Login</button>
    </form>
    </div>
    </div>
    <?php
    // Display login status
    echo "<p>$loginStatus</p>";
    ?>
</body>
</html>
