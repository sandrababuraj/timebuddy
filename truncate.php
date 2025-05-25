<?php
// Database connection
require 'connection.php';
$sql_truncate_input = "TRUNCATE TABLE input";
if ($conn->query($sql_truncate_input) === FALSE) {
    echo "Error truncating input table: " . $conn->error;
}
<<<<<<< HEAD
header("Location: profile.php");
=======
header("Location: input.php");
>>>>>>> 829cc57d27bbbc599b0eac90369f3c83aa3162a8
