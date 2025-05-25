<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Add Labs</title>
    <link rel="stylesheet" href="style6.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200&display=swap" rel="stylesheet">
    <script>
        function validateForm() {
            var selects = document.getElementsByTagName('select');
            for (var i = 0; i < selects.length; i++) {
                if (selects[i].value === "") {
                    alert("Please fill all the fields before submitting.");
                    return false;
                }
            }
            return true;
        }
    </script>
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
<?php
require "connection.php";
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$deptcombinations = array('CSE', 'IT');
$semcombinations = array('o'); // Add 'e' if needed
$combinations1 = array(1, 2, 3, 4);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process the form submission
    foreach ($deptcombinations as $dept) {
        foreach ($semcombinations as $semester) {
            foreach ($combinations1 as $year) {
                if (isset($_POST['combinations'][$dept][$semester][$year])) {
                    $combinations = $_POST['combinations'][$dept][$semester][$year];

                    foreach ($combinations as $lab => $combination) {
                        list($day, $pnos) = explode("-", $combination);
                        $pnos = explode(", ", $pnos);
                        // Fetch the teacher for the lab
                        $sql_fetch_teacher = "SELECT teacher FROM labs WHERE lab = '$lab' AND year = $year AND sem = '$semester' AND dept='$dept'";
                        $result_fetch_teacher = $conn->query($sql_fetch_teacher);
                        if ($result_fetch_teacher->num_rows > 0) {
                            $teacher = $result_fetch_teacher->fetch_assoc()['teacher'];

                            foreach ($pnos as $pno) {
                                // Update the classtable with the selected combination
                                $sql_update_classtable_lab = "UPDATE classtable SET subject = '$lab', teacher = '$teacher' WHERE day = '$day' AND pno = '$pno' AND year = $year AND sem = '$semester' AND dept='$dept'";
                                if ($conn->query($sql_update_classtable_lab) === FALSE) {
                                    echo "Error updating lab data in classtable: " . $conn->error;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    echo "Labs allocated successfully.";
} else {
    // Display the form
    echo "<div class='form'><form method='post' action='' onsubmit='return validateForm();'>";
    foreach ($deptcombinations as $dept) {
        echo "<h1>$dept</h1>";
        foreach ($semcombinations as $semester) {
            foreach ($combinations1 as $year) {
                
                $sql_select_labs = "SELECT lab, teacher, weekhr FROM labs WHERE year = $year AND sem = '$semester' AND dept='$dept'";
                $result_select_labs = $conn->query($sql_select_labs);
                
                if ($result_select_labs->num_rows > 0) {
                    $class = '';
                    if ($semester == 'o') {
                        $class = 'S' . (2 * $year - 1);
                    } elseif ($semester == 'e') {
                        $class = 'S' . (2 * $year);
                    }
                    echo "<div class='box'><h2>Class:$class</h2>";
                    while ($row = $result_select_labs->fetch_assoc()) {
                        $lab = $row['lab'];
                        $teacher = $row['teacher'];
                        $weekhr = $row['weekhr'];
                        echo "<h3>Lab: $lab (Teacher: $teacher, Week Hours: $weekhr)</h3>";
                        echo "<label for='combinations_$dept_$semester_$year_$lab'>Select Day and Period Combination:</label><br>";
                        echo "<select name='combinations[$dept][$semester][$year][$lab]'>";
                        echo "<option value=''>Select</option>"; // Initial null option

                        // Define lab-specific day and period combinations
                        $labCombinations = array();
                        if ($weekhr == 3) {
                            $labCombinations = array(
                                array("day" => "MON", "pno" => array(1, 2, 3)),
                                array("day" => "MON", "pno" => array(4, 5, 6)),
                                array("day" => "TUE", "pno" => array(1, 2, 3)),
                                array("day" => "TUE", "pno" => array(4, 5, 6)),
                                array("day" => "WED", "pno" => array(1, 2, 3)),
                                array("day" => "WED", "pno" => array(4, 5, 6)),
                                array("day" => "THU", "pno" => array(1, 2, 3)),
                                array("day" => "THU", "pno" => array(4, 5, 6)),
                                array("day" => "FRI", "pno" => array(1, 2, 3)),
                                array("day" => "FRI", "pno" => array(4, 5, 6))
                            );
                        } elseif ($weekhr == 2) {
                            $labCombinations = array(
                                array("day" => "MON", "pno" => array(1, 2)),
                                array("day" => "MON", "pno" => array(2, 3)),
                                array("day" => "MON", "pno" => array(4, 5)),
                                array("day" => "MON", "pno" => array(5, 6)),
                                array("day" => "TUE", "pno" => array(1, 2)),
                                array("day" => "TUE", "pno" => array(2, 3)),
                                array("day" => "TUE", "pno" => array(4, 5)),
                                array("day" => "TUE", "pno" => array(5, 6)),
                                array("day" => "WED", "pno" => array(1, 2)),
                                array("day" => "WED", "pno" => array(2, 3)),
                                array("day" => "WED", "pno" => array(4, 5)),
                                array("day" => "WED", "pno" => array(5, 6)),
                                array("day" => "THU", "pno" => array(1, 2)),
                                array("day" => "THU", "pno" => array(2, 3)),
                                array("day" => "THU", "pno" => array(4, 5)),
                                array("day" => "THU", "pno" => array(5, 6)),
                                array("day" => "FRI", "pno" => array(1, 2)),
                                array("day" => "FRI", "pno" => array(2, 3)),
                                array("day" => "FRI", "pno" => array(4, 5)),
                                array("day" => "FRI", "pno" => array(5, 6))
                            );
                        }
                        
                        // Display combinations in the dropdown
                        foreach ($labCombinations as $combination) {
                            $day = $combination['day'];
                            $pnos = implode(", ", $combination['pno']);
                            echo "<option value='$day-$pnos'>$day (Periods: $pnos)</option>";
                        }

                        echo "</select><br><br>";
                    }
                    echo"</div>";
                    
                } else {
                    echo "<h3>No labs found for Year $year, $dept, $semester</h3>";
                }
            }
        }
    }
    
    echo "<button type='submit' value='Allocate Labs'>Allocate</button>";
    echo "</form></div>";
}
$conn->close();
?>
        </div>
    </div>
</body>
</html>
