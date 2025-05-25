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
        <div class="form1">
            <!-- <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <div>
                    <label for="sem">Semester:</label>
                    <input type="text" id="sem" name="sem" required>
                    <label for="dept">Dept:</label>
                    <input type="text" id="dept" name="dept" required>
                    <label for="class">Class:</label>
                    <input type="text" id="class" name="class" required>
                </div>
                <button type="submit" name="fetchTimetable">Fetch Timetable</button>
            </form> -->
        </div>

        <div class="flex-container">
            <div class="timetable-container">
                <div class="timetable">
                    <?php
                    require "connection.php";
                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }
                    print_r($_POST);
                    // Handle form submission
                    // if (isset($_POST['fetchTimetable'])) {
                        // $sem = $_POST['sem'];
                        // $dept = $_POST['dept'];
                        // $class = $_POST['class'];
                        
                        $sem = 'o';
                        $dept = 'CSE';
                        $class ='S1';
                        // Handle updates to the timetable
                        if (isset($_POST['updateTimetable'])) {
                            $day = $_POST['day'];
                            $pno = $_POST['pno'];
                            $new_subject = $_POST['subject'];
                            $new_teacher = $_POST['teacher'];
                        
                            // Debugging: print out the submitted values
                            echo "Updating timetable for $day, period $pno with subject $new_subject and teacher $new_teacher<br>";

                            // Prepare the update query
                            $sql_update = "UPDATE classtable SET subject='$new_subject', teacher='$new_teacher' WHERE day='$day' AND pno=$pno AND sem='$sem' AND dept='$dept' AND class='$class'";

                            // Execute the update query and check for errors
                            if ($conn->query($sql_update) === FALSE) {
                                echo "Error updating timetable: " . $conn->error . "<br>";
                            } else {
                                echo "Timetable updated successfully.<br>";
                            }
                        }

                        echo "<h2>Timetable for Class $class $dept</h2>";
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

                        $days = array("MON", "TUE", "WED", "THU", "FRI");

                        // Fetch all lab subjects
                        $labs = array();
                        $sql_select_labs = "SELECT lab FROM labs WHERE sem = '$sem' AND dept='$dept' AND class = '$class'";
                        $result_select_labs = $conn->query($sql_select_labs);
                        if ($result_select_labs->num_rows > 0) {
                            while ($row = $result_select_labs->fetch_assoc()) {
                                $labs[] = $row['lab'];
                            }
                        }

                        foreach ($days as $day) {
                            echo "<tr>";
                            echo "<td>$day</td>";
                            for ($pno = 1; $pno <= 6; $pno++) {
                                $sql_select_subject = "SELECT subject, teacher FROM classtable WHERE day = '$day' AND pno = $pno AND sem = '$sem' AND dept='$dept' AND class = '$class'";
                                $result_select_subject = $conn->query($sql_select_subject);

                                if ($result_select_subject->num_rows > 0) {
                                    $row = $result_select_subject->fetch_assoc();
                                    $subject = $row['subject'];
                                    $teacher = $row['teacher'];
                                    echo "<td>
                                            <form action='' method='post'>
                                                <input type='hidden' name='day' value='$day'>
                                                <input type='hidden' name='pno' value='$pno'>
                                                <input type='hidden' name='sem' value='$sem'>
                                                <input type='hidden' name='dept' value='$dept'>
                                                <input type='hidden' name='class' value='$class'>
                                                <input type='text' name='subject' value='$subject'>
                                                <input type='text' name='teacher' value='$teacher'>
                                                <button type='submit' name='updateTimetable'>Update</button>
                                            </form>
                                          </td>";
                                } else {
                                    echo "<td></td>";
                                }
                            }
                            echo "</tr>";
                        }

                        echo "</table>";
                    // }
                    ?>
                </div>
            </div>
        </div>
        <div class="prevnext">
            <a href="display.php" class="previous">&laquo; Previous</a>
            <a href="display5.php" class="next">Next &raquo;</a>
            <a href="display.php" class="previous round">&#8249;</a>
            <a href="display5.php" class="next round">&#8250;</a>
        </div>
    </div>
</body>

</html>
