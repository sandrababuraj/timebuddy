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
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <div>
                    <label for="sem">Semester:</label>
                    <input type="text" id="sem" name="sem" required>
                    <label for="dept">Dept:</label>
                    <input type="text" id="dept" name="dept" required>
                    <label for="class">Class:</label>
                    <input type="text" id="class" name="class" required>
                </div>
                <button type="submit" name="fetchTimetable">Fetch Timetable</button>
            </form>
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
                    // print_r($_POST);
                    // Handle updates to the timetable
                    if (isset($_POST['fetchTimetable']) || isset($_POST['updateTimetable'])) {
                        $sem = $_POST['sem'];
                        $dept = $_POST['dept'];
                        $class = $_POST['class'];
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
                        // Check if the same teacher is present in the same day and pno combination in other years of the same department
                        $sql_check_same_dept = "SELECT sem, class FROM classtable WHERE day='$day' AND pno=$pno AND dept='$dept' AND teacher='$new_teacher' AND (sem != '$sem' OR class != '$class')";
                        $result_check_same_dept = $conn->query($sql_check_same_dept);
                        if ($result_check_same_dept->num_rows > 0) {
                            echo "The same teacher is present in the same day and period combination in other years of the same department.<br>";
                            while ($row = $result_check_same_dept->fetch_assoc()) {
                                echo "Semester: " . $row['sem'] . ", Class: " . $row['class'] . "<br>";
                            }
                        } else {
                            echo "No conflicts found in the same department.<br>";
                        }

                        // Check if the same teacher is present in the same day and pno combination in other departments
                        $sql_check_other_dept = "SELECT dept, sem, class FROM classtable WHERE day='$day' AND pno=$pno AND teacher='$new_teacher' AND dept != '$dept'";
                        $result_check_other_dept = $conn->query($sql_check_other_dept);
                        if ($result_check_other_dept->num_rows > 0) {
                            echo "The same teacher is present in the same day and period combination in other department classes.<br>";
                            while ($row = $result_check_other_dept->fetch_assoc()) {
                                echo "Department: " . $row['dept'] . ", Semester: " . $row['sem'] . ", Class: " . $row['class'] . "<br>";
                            }
                        } else {
                            echo "No conflicts found in other departments.<br>";
                        }
                    }

                    // Fetch distinct subject values
                    $sql = "SELECT DISTINCT subject FROM subjects WHERE sem='$sem' AND dept='$dept' AND class='$class'";
                    $result = $conn->query($sql);

                    $subjects = [];
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $subjects[] = $row['subject'];
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

                    $teachers = array();
                    $sql_select_teachers = "SELECT teacher FROM subjects WHERE sem = '$sem' AND dept='$dept' AND class = '$class'";
                    $result_select_teachers = $conn->query($sql_select_teachers);
                    if ($result_select_labs->num_rows > 0) {
                        while ($row = $result_select_teachers->fetch_assoc()) {
                            $teachers[] = $row['teacher'];
                        }
                    }

                    $labteachers = array();
                    $sql_select_labteachers = "SELECT teacher FROM labs WHERE sem = '$sem' AND dept='$dept' AND class = '$class'";
                    $result_select_labteachers = $conn->query($sql_select_labteachers);
                    if ($result_select_labteachers->num_rows > 0) {
                        while ($row = $result_select_labteachers->fetch_assoc()) {
                            $labteachers[] = $row['teacher'];
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
            <label for='subject'>Subject:</label><br>
            <select id='subject' name='subject' onchange='updateTeacher()'>
                <option value='" . htmlspecialchars($subject) . "'>" . htmlspecialchars($subject) . "</option>";
                                // Populate the options for the select dropdown
                                foreach ($subjects as $subj) {
                                    echo "<option value='" . htmlspecialchars($subj) . "'>" . htmlspecialchars($subj) . "</option>";
                                }
                                foreach ($labs as $lab) {
                                    echo "<option value='" . htmlspecialchars($lab) . "'>" . htmlspecialchars($lab) . "</option>";
                                }
                                echo "</select>
            <br><label for='teacher'>Teacher:</label>
            <select id='subject' name='subject' onchange='updateTeacher()'>
                <option value='" . htmlspecialchars($teacher) . "'>" . htmlspecialchars($teacher) . "</option>";
                                // Populate the options for the select dropdown
                                foreach ($teachers as $teacher) {
                                    echo "<option value='" . htmlspecialchars($teacher) . "'>" . htmlspecialchars($teacher) . "</option>";
                                }
                                foreach ($labteachers as $teacher) {
                                    echo "<option value='" . htmlspecialchars($teacher) . "'>" . htmlspecialchars($teacher) . "</option>";
                                }
                                
                                echo "</select>
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

                    // if ($sem = 'o') {
                    //     $combinations = array(
                    //         array("year" => 1, "class" => 'S1'),
                    //     );
                    // } else {
                    //     $combinations = array(
                    //         array("year" => 1, "class" => 'S2'),
                    //         array("year" => 2, "class" => 'S4'),
                    //         array("year" => 3, "class" => 'S6'),
                    //         array("year" => 4, "class" => 'S8')
                    //     );
                    // }
                    // // Iterate over each unique combination of year
                    // foreach ($combinations as $combination) {
                    // $year = $combination['year'];
                    // $class = $combination['class'];
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
                    $years = array(1, 2, 3, 4);

                    // Fetch all lab subjects
                    $labs1 = array();
                    $sql_select_labs1 = "SELECT lab FROM labs WHERE  sem = '$sem' AND dept='$dept'";
                    $result_select_labs1 = $conn->query($sql_select_labs1);
                    if ($result_select_labs1->num_rows > 0) {
                        while ($row = $result_select_labs1->fetch_assoc()) {
                            $labs1[] = $row['lab1'];
                        }
                    }

                    foreach ($days as $day) {
                        echo "<tr>";
                        echo "<td>$day</td>";
                        for ($pno = 1; $pno <= 6; $pno++) {
                            $sql_select_subject1 = "SELECT subject, teacher FROM classtable WHERE day = '$day' AND pno = $pno AND sem = '$sem' AND dept='$dept' AND class = '$class'";
                            $result_select_subject1 = $conn->query($sql_select_subject1);

                            if ($result_select_subject1->num_rows > 0) {
                                $row = $result_select_subject1->fetch_assoc();
                                $subject1 = $row['subject'];
                                $teacher1 = $row['teacher'];
                                if ($class == 'S1' || $class == 'S2') {
                                    $year = 1;
                                }
                                if ($class == 'S3' || $class == 'S4') {
                                    $year = 2;
                                }
                                if ($class == 'S5' || $class == 'S6') {
                                    $year = 3;
                                }
                                if ($class == 'S7' || $class == 'S8') {
                                    $year = 4;
                                }

                                // Query to fetch weekhr from subjects table
                                // $sql_select_weekhr = "SELECT weekhr FROM subjects WHERE subject = '$subject1' AND dept='$dept' AND class='$class'";
                                // $result_select_weekhr = $conn->query($sql_select_weekhr);

                                // if ($result_select_weekhr) {
                                //     $row_result_select_weekhr = $result_select_weekhr->fetch_assoc();
                                //     $weekhr = $row_result_select_weekhr['weekhr'];
                                // } else {
                                //     // Handle error if query fails
                                //     echo "Error fetching weekhr: " . $conn->error;
                                // }

                                // // Query to fetch count from classtable
                                // $sql_select_count = "SELECT COUNT(*) as weekhr1 FROM classtable WHERE subject = '$subject1' AND dept='$dept' AND class='$class'";
                                // $result_select_count = $conn->query($sql_select_count);

                                // if ($result_select_count) {
                                //     $row_result_select_count = $result_select_count->fetch_assoc();
                                //     $weekhr1 = $row_result_select_count['weekhr1'];
                                // } else {
                                //     // Handle error if query fails
                                //     echo "Error fetching count: " . $conn->error;
                                // }


                                // Check if the same subject has corresponding teacher value in the same day and pno combination of another year
                                $sql_check_teacher_in_other_year = "SELECT COUNT(*) AS count FROM classtable WHERE day = '$day' AND pno = $pno AND teacher = '$teacher1' AND sem = '$sem' AND dept ='$dept' AND year != $year ";
                                $result_check_teacher_in_other_year = $conn->query($sql_check_teacher_in_other_year);
                                $row_check_teacher_in_other_year = $result_check_teacher_in_other_year->fetch_assoc();
                                $count = $row_check_teacher_in_other_year['count'];

                                $sql_check_teacher_in_other_dept = "SELECT COUNT(*) AS count1 FROM classtable WHERE day = '$day' AND pno = $pno AND teacher = '$teacher1' AND sem = '$sem' AND dept !='$dept' ";
                                $result_check_teacher_in_other_dept = $conn->query($sql_check_teacher_in_other_dept);
                                $row_check_teacher_in_other_dept = $result_check_teacher_in_other_dept->fetch_assoc();
                                $count1 = $row_check_teacher_in_other_dept['count1'];
                                // If count > 0, set background color

                                // if ($weekhr1 > $weekhr) {
                                //     echo $weekhr1;
                                //     echo $weekhr;
                                //     $weekhrcount++;
                                // }

                                if ($subject1 == 'REM') {
                                    echo "<td style='background-color: lightgreen; color:black;'>$subject1 ($teacher1)</td>";
                                } else if ($count > 0) {
                                    echo "<td style='background-color: orange; color:black;'>$subject1 ($teacher1)</td>";
                                } else if ($count1 > 0) {
                                    echo "<td style='background-color: red; color:black;'>$subject1 ($teacher1)</td>";
                                } else if (in_array($subject1, $labs)) {
                                    echo "<td style='background-color: white; color:black;'>$subject1 ($teacher1)</td>";
                                } else {
                                    echo "<td>$subject1 ($teacher1)</td>";
                                }
                            } else {
                                echo "<td></td>";
                            }

                            // if ($result_select_subject->num_rows > 0) {
                            //     $row = $result_select_subject->fetch_assoc();
                            //     $subject = $row['subject'];
                            //     $teacher = $row['teacher'];
                            //     echo "<td>$subject ($teacher)</td>";
                            // } else {
                            //     echo "<td></td>";
                            // }
                        }
                    }
                    echo "</tr>";

                    echo "</table>";
                    // echo $weekhrcount;
                    // if ($weekhrcount != 0) {
                    //     echo "ERROR!";
                    // }
                    // }

                    ?>
                </div>
                <div class="legend">

                    <!-- Labs Table -->
                    <div class="labs-container">
                        <table>
                            <tr>
                                <th>Subject</th>
                                <th></th>
                                <th></th>
                            </tr>
                            <?php

                            // Fetch and populate labs table
                            // Modify SQL query as per your database schema
                            // $sql_select_labs = "SELECT distinct subject, weekhr FROM subjects WHERE sem='$sem' AND dept='$dept'AND class='$class' order by year ";

                            // SQL query to select distinct subjects, week hours, and count of subjects
                            $sql_select_subjects = "
                            SELECT DISTINCT s.subject, s.weekhr, COUNT(cl.subject) as subject_count
                            FROM subjects s
                            JOIN classtable cl ON s.subject = cl.subject AND s.class = cl.class AND s.dept=cl.dept
                            WHERE s.sem = '$sem' AND s.dept = '$dept' AND s.class = '$class'
                            GROUP BY s.subject, s.weekhr
                            ORDER BY s.year";

                            $sql_select_labs = "
                            SELECT DISTINCT s.lab, s.weekhr, COUNT(cl.subject) as lab_count
                            FROM labs s
                            JOIN classtable cl ON s.lab = cl.subject AND s.class = cl.class AND s.dept=cl.dept
                            WHERE s.sem = '$sem' AND s.dept = '$dept' AND s.class = '$class'
                            GROUP BY s.lab, s.weekhr
                            ORDER BY s.year";

                            // Execute the queries
$result_select_subjects = $conn->query($sql_select_subjects);
$result_select_labs = $conn->query($sql_select_labs);

// Check if the queries were successful
if ($result_select_subjects) {
    // Check if there are any rows returned
    if ($result_select_subjects->num_rows > 0) {
        // Loop through the subjects results and display them in a table with conditional formatting
        while ($row = $result_select_subjects->fetch_assoc()) {
            $subject = $row['subject'];
            $weekhr = $row['weekhr'];
            $subject_count = $row['subject_count'];

            // Check if weekhr and subject_count are not equal
            $style = ($weekhr != $subject_count) ? "style='background-color: red;'" : "";

            echo "<tr>";
            echo "<td $style>" . $subject . "</td>";
            echo "<td $style>" . $weekhr . "</td>";
            echo "<td $style>" . $subject_count . "</td>";
            echo "</tr>";
        }
    } else {
        // If no rows are found, display a message
        echo "<tr><td colspan='3'>No subjects or labs found</td></tr>";
    }
} else {
    // If the query failed, display an error message
    echo "Error executing query: " . $conn->error;
}

// Check if the queries were successful
// if ($result_select_labs) {
//     // Check if there are any rows returned
//     if ($result_select_labs->num_rows > 0) {
//         // Loop through the subjects results and display them in a table with conditional formatting
//         while ($row = $result_select_labs->fetch_assoc()) {
//             $subject = $row['lab'];
//             $weekhr = $row['weekhr'];
//             $lab_count = $row['lab_count'];

//             // Check if weekhr and subject_count are not equal
//             $style = ($weekhr != $lab_count) ? "style='background-color: red;'" : "";

//             echo "<tr>";
//             echo "<td $style>" . $subject . "</td>";
//             echo "<td $style>" . $weekhr . "</td>";
//             echo "<td $style>" . $lab_count . "</td>";
//             echo "</tr>";
//         }
//     } else {
//         // If no rows are found, display a message
//         echo "<tr><td colspan='3'>No subjects or labs found</td></tr>";
//     }
// } else {
//     // If the query failed, display an error message
//     echo "Error executing query: " . $conn->error;
// }
                            ?>
                        </table>
                    </div>
                </div>
            </div>


            <!-- <div class="prevnext">
            <a href="display.php" class="previous">&laquo; Previous</a>
            <a href="display5.php" class="next">Next &raquo;</a>
            <a href="display.php" class="previous round">&#8249;</a>
            <a href="display5.php" class="next round">&#8250;</a>
        </div> -->
</body>

</html>