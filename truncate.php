<?php
// Database connection
require 'connection.php';
$sql_truncate_input = "TRUNCATE TABLE input";
if ($conn->query($sql_truncate_input) === FALSE) {
    echo "Error truncating input table: " . $conn->error;
}
header("Location: profile.php");
