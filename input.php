<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add subjects</title>
    <link rel="stylesheet" href="style6.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200&display=swap" rel="stylesheet">
</head>
<body>
<div class="bgimg">
    <div class="navbar">
        <ul class="navbar list">
            <li><a href="profile.php?tab=dashboard" class="tablinks <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="profile.php?tab=profile" class="tablinks <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'profile') ? 'active' : ''; ?>">Profile</a></li>
            <li style="float:right"><a href="login.php" class="tablinks">Sign Out</a></li>
        </ul>
    </div>
    <div class="form">
        <h2>Generate</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="dept">Department</label><br>
                <select name="dept" id="dept" required>
                    <option value="" disabled selected>Select Department</option>
                    <option value="CSE">CSE</option>
                    <option value="ECE">ECE</option>
                    <option value="EEE">EEE</option>
                    <option value="CE">CE</option>
                    <option value="IT">IT</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sem">Semester</label><br>
                <select name="sem" id="sem" required>
                    <option value="" disabled selected>Select Semester</option>
                    <option value="Odd">Odd</option>
                    <option value="Even">Even</option>
                </select>
            </div>
            <!-- <div class="form-group">
                <label for="class">Class</label><br>
                <select name="class" id="class" required>
                    <option value="" disabled selected>Select Class</option>
                    <option value="S1">S1</option>
                    <option value="S2">S2</option>
                    <option value="S3">S3</option>
                    <option value="S4">S4</option>
                    <option value="S5">S5</option>
                    <option value="S6">S6</option>
                    <option value="S7">S7</option>
                    <option value="S8">S8</option>
                </select>
            </div> -->
            <button type="submit" name="submit">Generate</button>
        </form>
    </div>
</body>
</html>

<?php
// Database connection
require 'connection.php';

// Initialize variables to store form data
$dept = $sem = '';

// Initialize variable to store success message
$success_message = 'SUCCESS';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Collect form data
    $dept = $_POST['dept'];
    $sem = ($_POST['sem'] == 'Odd') ? 'o' : 'e'; // Set 'o' for Odd and 'e' for Even
    // $class = $_POST['class'];

    // Prepare and bind the SQL statement
    $stmt = $conn->prepare("INSERT INTO input (dept, sem ) VALUES (?, ? )");
    $stmt->bind_param("ss", $dept, $sem );

    // Execute the statement
    if ($stmt->execute()) {
        $success_message = "Data inserted successfully.";
        // Redirect the user to timetable.php
        header("Location: display3.php");
        exit; // Ensure that no further code is executed after redirection
    } else {
        // Display an error message if the SQL statement fails
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
