<!-- <?php
        session_start();
        require 'connection.php'; ?>

<?php
// echo "Script is running.";

if (isset($_POST["submit"])) {
    $name = $_POST["name"];
    $institution = $_POST["institution"];
    $role = $_POST["role"];
    $username = $_POST["username"];
    $password = $_POST["password"];

    $query = "INSERT INTO data VALUES('$name', '$institution', '$role', '$username', '$password')";

    echo "Before query: $query";

    if (mysqli_query($conn, $query)) {
        echo "<p>Data Inserted Successfully</p>";
    } else {
        echo "<p>Error: " . mysqli_error($conn) . "</p>";
    }
}
?> -->

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Insert Data</title>
    <link rel="stylesheet" href="style copy.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200&display=swap" rel="stylesheet">
 
</head>
<style media="screen">
    label {
        display: block;
    }
</style>

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

                        <h1>Sign Up</h1>
                        <label for="">Name</label>
                        <input type="text" name="name" required value="">
                        <label for="">Institution</label>
                        <input type="text" name="institution" required value="">
                        <label for="">Role</label>
                        <select name="role" required>
                            <option value="" selected hidden>Select Role</option>
                            <option value="Teacher">Teacher</option>
                            <option value="Student">Student</option>
                        </select>

                        <label for="">User Name</label>
                        <input type="text" name="username" required value="">
                        <label for="">Password</label>
                        <input type="password" name="password" required value="">

                        <br>
                        <button type="submit" name="submit">SignUp</button>
                    </div>
            </div>
            </form>
        </div>
</body>

</html>