<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "miniproject";

// Connect to MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$dept = 'CSE';
$sem = 'e';
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $years = $_POST['year'];
    // $dept = $_POST['dept'];
    // $sem = $_POST['sem'];
    $days = $_POST['day'];
    $pnos = $_POST['pno'];
    $subjects = $_POST['subject'];
    $teachers = $_POST['teacher'];
    // Update each record
    for ($i = 0; $i < count($years); $i++) {
        
        $sem = $sems[$i];
        $day = $days[$i];
        $pno = $pnos[$i];
        $subject = $subjects[$i];
        $teacher = $teachers[$i];

        $sql = "UPDATE classtable SET subject='$subject', teacher='$teacher' WHERE year='$year' AND dept='$dept' AND sem='$sem' AND day='$day' AND pno='$pno'";

        if ($conn->query($sql) !== TRUE) {
            echo "Error updating record: " . $conn->error . "<br>";
        }
    }

    // Redirect back to the same page to show updated data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch data from classtable
$sql = "SELECT * FROM classtable";
$result = $conn->query($sql);

// Organize data by year, dept, sem, day, and pno
$timetable = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $year = $row['year'];
        // $dept = $row['dept'];
        // $sem = $row['sem'];
        $day = $row['day'];
        $pno = $row['pno'];
        $timetable[$year][$dept][$sem][$day][$pno] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
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
        <h1>Edit Class Timetable</h1>
        <form action="" method="post">
            <?php
            $days = ['MON', 'TUE', 'WED', 'THU', 'FRI'];
            $pnos = [1, 2, 3, 4, 5, 6];
            for ($year = 1; $year <= 4; $year++) {
                // for ($dept = 1; $dept <= 2; $dept++) { // Assuming two departments
                //     for ($sem = 1; $sem <= 2; $sem++) { // Assuming two semesters
                        echo "<h2>Year $year - Dept $dept - Sem $sem</h2>";
                        echo "<table>";
                        echo "<tr><th>Day</th>";
                        foreach ($pnos as $pno) {
                            echo "<th>Period $pno</th>";
                        }
                        echo "</tr>";
                        foreach ($days as $day) {
                            echo "<tr>";
                            echo "<td>$day</td>";
                            foreach ($pnos as $pno) {
                                $subject = isset($timetable[$year][$dept][$sem][$day][$pno]['subject']) ? $timetable[$year][$dept][$sem][$day][$pno]['subject'] : '';
                                $teacher = isset($timetable[$year][$dept][$sem][$day][$pno]['teacher']) ? $timetable[$year][$dept][$sem][$day][$pno]['teacher'] : '';
                                echo "<td>";
                                echo "Subject: <input type='text' name='subject[]' value='$subject'><br>";
                                echo "Teacher: <input type='text' name='teacher[]' value='$teacher'><br>";
                                echo "<input type='hidden' name='year[]' value='$year'>";
                                echo "<input type='hidden' name='dept[]' value='$dept'>";
                                echo "<input type='hidden' name='sem[]' value='$sem'>";
                                echo "<input type='hidden' name='day[]' value='$day'>";
                                echo "<input type='hidden' name='pno[]' value='$pno'>";
                                echo "</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</table><br>";
                    // }
                // }
            }
            ?>
            <input type="submit" value="Update">
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>
