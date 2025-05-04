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
        <div class="flex-container">
            <div class="timetable-container">
                <div class="timetable">
                    <?php
                    require "connection.php";
                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }
                    // Fetch semester value from input table
                    $sql_select_semester = "SELECT sem FROM input";
                    $result_select_semester = $conn->query($sql_select_semester);

                    if ($result_select_semester->num_rows > 0) {
                        $row = $result_select_semester->fetch_assoc();
                        $sem = $row['sem'];
                    } else {
                        echo "No semester data found in the 'input' table";
                        exit; // Exit the script if semester data is not found
                    }
                    $sql_select_dept = "SELECT dept FROM input";
                    $result_select_dept = $conn->query($sql_select_dept);

                    if ($result_select_dept->num_rows > 0) {
                        $row = $result_select_dept->fetch_assoc();
                        $dept = $row['dept'];
                    } else {
                        echo "No dept data found in the 'input' table";
                        exit; // Exit the script if semester data is not found
                    }
                    //         $sql_truncate_input = "TRUNCATE TABLE input";
                    // if ($conn->query($sql_truncate_input) === FALSE) {
                    //     echo "Error truncating input table: " . $conn->error;
                    // }

                    // $sql_select_class = "SELECT class FROM input";
                    // $result_select_class = $conn->query($sql_select_class);

                    // if ($result_select_class->num_rows > 0) {
                    //     $row = $result_select_class->fetch_assoc();
                    //     $class = $row['class'];
                    // } else {
                    //     echo "No class data found in the 'input' table";
                    //     exit; // Exit the script if semester data is not found
                    // }
                    $combinations = array(
                        array("year" => 1),
                        array("year" => 2),
                        array("year" => 3),
                        array("year" => 4)
                    );

                    // Check if the form was submitted to update the classtable
                    if (isset($_POST['teachertable'])) {
                        header("Location: display2.php");
                    }

                    // Check if the form was submitted to truncate the input table
                    if (isset($_POST['truncateInput'])) {
                        // Execute SQL query to truncate input table
                        $sql_truncate_input = "TRUNCATE TABLE input";
                        if ($conn->query($sql_truncate_input) === FALSE) {
                            echo "Error truncating input table: " . $conn->error;
                        } else {
                            // Redirect to input.php
                            header("Location: input.php");
                            exit;
                        }
                    }
                    if (isset($_POST['exportExcel'])) {
                        header("Location: demo.php");
                    }
                    if (isset($_POST['new'])) {
                        header("Location: truncate.php");
                    }

                    // Iterate over each unique combination of year
                    foreach ($combinations as $combination) {
                        $year = $combination['year'];
                        echo "<h2>Timetable for Year $year, Semester $sem:</h2>";
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
                        $sql_select_labs = "SELECT lab FROM labs WHERE year = $year AND sem = '$sem' AND dept='$dept'";
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
                                $sql_select_subject = "SELECT subject, teacher FROM classtable WHERE day = '$day' AND pno = $pno AND year = $year AND sem = '$sem' AND dept='$dept'";
                                $result_select_subject = $conn->query($sql_select_subject);

                                // if ($result_select_subject->num_rows > 0) {
                                //     $row = $result_select_subject->fetch_assoc();
                                //     $subject = $row['subject'];
                                //     $teacher = $row['teacher'];
                                //     // Check if the same subject has corresponding teacher value in the same day and pno combination of another year
                                //     $sql_check_teacher_in_other_year = "SELECT COUNT(*) AS count FROM classtable WHERE day = '$day' AND pno = $pno AND teacher = '$teacher' AND sem = '$sem' AND dept='$dept' AND year != $year ";
                                //     $result_check_teacher_in_other_year = $conn->query($sql_check_teacher_in_other_year);
                                //     $row_check_teacher_in_other_year = $result_check_teacher_in_other_year->fetch_assoc();
                                //     $count = $row_check_teacher_in_other_year['count'];
                                //     // If count > 0, set background color
                                //     if ($count > 0 ) {
                                //         echo "<td style='background-color: lightgreen; color:black;'>$subject ($teacher)</td>";
                                //     }
                                //     // Check if the subject is a lab
                                //    else if (in_array($subject, $labs)) {
                                //         echo "<td style='background-color: lightblue; color:black;'>$subject ($teacher)</td>";
                                //     } else {
                                //         echo "<td>$subject ($teacher)</td>";
                                //     }
                                // } else {
                                //     echo "<td></td>";
                                // }

                                if ($result_select_subject->num_rows > 0) {
                                    $row = $result_select_subject->fetch_assoc();
                                    $subject = $row['subject'];
                                    $teacher = $row['teacher'];
                                    echo "<td>$subject ($teacher)</td>";
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
                <!-- Subjects Table -->
                <div class="subjects-container">
                    <h2>Subjects Table</h2>
                    <table>
                        <tr>
                            <th>Year</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                        </tr>
                        <?php


                        // Fetch and populate subjects table
                        // Modify SQL query as per your database schema
                        // $sql_select_subjects = "SELECT subject, teacher FROM subjects WHERE class='$class' AND sem='$sem' AND dept='$dept'";
                        $sql_select_subjects = "SELECT distinct year,subject, teacher FROM subjects WHERE sem='$sem' AND dept='$dept' order by year";
                        $result_select_subjects = $conn->query($sql_select_subjects);

                        if ($result_select_subjects->num_rows > 0) {
                            while ($row = $result_select_subjects->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['year'] . "</td>";
                                echo "<td>" . $row['subject'] . "</td>";
                                echo "<td>" . $row['teacher'] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>No subjects found</td></tr>";
                        }
                        ?>
                    </table>
                </div>

                <!-- Labs Table -->
                <div class="labs-container">
                    <h2>Labs Table</h2>
                    <table>
                        <tr>
                            <th>Year</th>
                            <th>Lab</th>
                            <th>Teacher</th>
                        </tr>
                        <?php

                        // Fetch and populate labs table
                        // Modify SQL query as per your database schema
                        $sql_select_labs = "SELECT distinct year,lab, teacher FROM labs WHERE sem='$sem' AND dept='$dept' order by year";
                        $result_select_labs = $conn->query($sql_select_labs);

                        if ($result_select_labs->num_rows > 0) {
                            while ($row = $result_select_labs->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['year'] . "</td>";
                                echo "<td>" . $row['lab'] . "</td>";
                                echo "<td>" . $row['teacher'] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>No labs found</td></tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="form">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <button type="submit" name="teachertable">Teacher's View</button>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <button type="submit" name="truncateInput">Other Dept</button>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <button type="submit" name="exportExcel">Export to Excel</button>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <button type="submit" name="new">Clear</button>
            </form>
        </div>
    </div>

</body>

</html>

<?php
