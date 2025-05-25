<?php require "connection.php";
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

// Fetch distinct teacher names
$sql = "SELECT DISTINCT teacher FROM subjects UNION SELECT DISTINCT teacher FROM labs";
$result = $conn->query($sql);

$teachers = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $teachers[] = $row['teacher'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Timetable</title>
    <link rel="stylesheet" href="style5.css">
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

        <!-- Form to fetch sem and dept -->
        <div class="form">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <div>
                    <label for="sem">Semester:</label>
                    <select id="sem" name="sem" required onchange="updateClassOptions()">
                        <option value="">Select Semester</option>
                        <option value="o">Odd</option>
                        <option value="e">Even</option>
                    </select>
                    <label for="teacher">Teacher:</label>
            <select id="teacher" name="teacher" required>
                <option value="">Select Teacher</option>
                <?php foreach($teachers as $teacher): ?>
                    <option value="<?php echo htmlspecialchars($teacher); ?>"><?php echo htmlspecialchars($teacher); ?></option>
                <?php endforeach; ?>
            </select>
                </div>
                <button type="submit" name="fetchTimetable">Fetch Timetable</button>
            </form>
        </div>

        <div class="flex-container">
            <div class="timetable-container">
                <div class="timetable">
                    <?php


                    // Handle form submission
                    if (isset($_POST['fetchTimetable'])) {
                        $sem = $_POST['sem'];
                        $teacher = $_POST['teacher'];
                        $days = array("MON", "TUE", "WED", "THU", "FRI");

                        echo "<h2>Timetable for Teacher $teacher</h2>";
                        echo "<table border='1'>";
                        echo "<tr>";
                        echo "<th>Day/Period</th>";
                        echo "<th>1</th>";
                        echo "<th>2</th>";
                        echo "<th>3</th>";
                        echo "<th>4</th>";
                        echo "<th>5</th>";
                        echo "<th>6</th>";
                        echo "</tr>";

                        foreach ($days as $day) {
                            echo "<tr>";
                            echo "<td>$day</td>";
                            for ($pno = 1; $pno <= 6; $pno++) {
                                $sql_select_class = "SELECT class,dept FROM classtable WHERE day = '$day' AND pno = $pno AND sem = '$sem' AND teacher='$teacher'";
                                $result_select_class = $conn->query($sql_select_class);

                                if ($result_select_class->num_rows > 0) {
                                    $row = $result_select_class->fetch_assoc();
                                    $class = $row['class'];
                                    $dept = $row['dept'];
                                    echo "<td>$class $dept</td>";
                                } else {
                                    echo "<td></td>";
                                }
                            }
                            echo "</tr>";
                        }

                        echo "</table>";
                    }
                    ?>
                </div>
            </div>

            <div class="legend">
                <!-- Legend can be added here if needed -->
            </div>
        </div>
        <div class="form">
            <!-- <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <button type="submit" name="teachertable">Teacher's View</button>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <button type="submit" name="truncateInput">Other Dept</button>
            </form> -->
            <!-- <form action="" method="post" style="text-align: center;"> -->
            <a href="input.php"><button type="submit" name="exportExcel">Export to Excel</button></a>
            <!-- </form> -->
            <!-- <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <button type="submit" name="new">Clear</button>
            </form> -->
        </div>
        <div class="prevnext">
            <a href="display.php" class="previous">&laquo; Previous</a>
            <a href="display2.php" class="next">Next &raquo;</a>
            <a href="display.php" class="previous round">&#8249;</a>
            <a href="display2.php" class="next round">&#8250;</a>
        </div>
    </div>

</body>

</html>