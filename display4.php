<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Timetable</title>
    <link rel="stylesheet" href="style5.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200&display=swap" rel="stylesheet">
    <script>
        function updateClassOptions() {
            const sem = document.getElementById("sem").value;
            const classSelect = document.getElementById("class");
            classSelect.innerHTML = ""; // Clear current options

            const oddClasses = ["S1", "S3", "S5", "S7"];
            const evenClasses = ["S2", "S4", "S6", "S8"];
            const defaultOption = document.createElement("option");
            defaultOption.value = "";
            defaultOption.text = "Select Class";
            classSelect.appendChild(defaultOption);

            let classes = [];
            if (sem === "o") {
                classes = oddClasses;
            } else if (sem === "e") {
                classes = evenClasses;
            }

            for (const cls of classes) {
                const option = document.createElement("option");
                option.value = cls;
                option.text = cls;
                classSelect.appendChild(option);
            }
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
        <!-- Form to fetch sem and dept -->
        <div class="form1">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
        <div>
            <label for="sem">Semester:</label>
            <select id="sem" name="sem" required onchange="updateClassOptions()">
                <option value="">Select Semester</option>
                <option value="o">Odd</option>
                <option value="e">Even</option>
            </select>
            
            <label for="dept">Dept:</label>
            <select id="dept" name="dept" required>
                <option value="">Select Department</option>
                <option value="CSE">CSE</option>
                <option value="IT">IT</option>
            </select>
            
            <label for="class">Class:</label>
            <select id="class" name="class" required>
                <option value="">Select Class</option>
                <!-- Options will be dynamically added here -->
            </select>
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
                    // Handle form submission
                    if (isset($_POST['fetchTimetable'])) {
                        $sem = $_POST['sem'];
                        $dept = $_POST['dept'];
                        $class = $_POST['class'];

                        //     if($sem == 'o'){
                        //         $combinations = array(
                        //         array("year" => 1, "class" => 'S1'),
                        //         array("year" => 2, "class" => 'S3'),
                        //         array("year" => 3, "class" => 'S5'),
                        //         array("year" => 4, "class" => 'S7')
                        //     );
                        // }
                        // else{
                        //     $combinations = array(
                        //         array("year" => 1, "class" => 'S2'),
                        //         array("year" => 2, "class" => 'S4'),
                        //         array("year" => 3, "class" => 'S6'),
                        //         array("year" => 4, "class" => 'S8')
                        //     );
                        // }
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
                            header("Location: input.php");
                        }
                        if (isset($_POST['new'])) {
                            header("Location: truncate.php");
                        }

                        // Iterate over each unique combination of year
                        // foreach ($combinations as $combination) {
                        //     $year = $combination['year'];
                        //     $class = $combination['class'];
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
                            <th>Teacher</th>
                            <th>Subject</th>
                            <th></th>
                            <th></th>
                        </tr>
                        <?php

                        // Fetch and populate subjects table
                        $sql_select_subjects = "SELECT distinct year,subject, teacher, fullname, code FROM subjects WHERE sem='$sem' AND dept='$dept' AND class = '$class' order by year";
                        $result_select_subjects = $conn->query($sql_select_subjects);

                        if ($result_select_subjects->num_rows > 0) {
                            while ($row = $result_select_subjects->fetch_assoc()) {
                                echo "<tr>";

                                echo "<td>" . $row['teacher'] . "</td>";
                                echo "<td>" . $row['subject'] . "</td>";
                                echo "<td>" . $row['fullname'] . "</td>";
                                echo "<td>" . $row['code'] . "</td>";

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

                            <th>Lab</th>
                            <th>Teacher</th>
                        </tr>
                        <?php

                        // Fetch and populate labs table
                        $sql_select_labs = "SELECT distinct year,lab, teacher FROM labs WHERE sem='$sem' AND dept='$dept' AND class = '$class' order by year";
                        $result_select_labs = $conn->query($sql_select_labs);

                        if ($result_select_labs->num_rows > 0) {
                            while ($row = $result_select_labs->fetch_assoc()) {
                                echo "<tr>";

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
            <!-- <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <button type="submit" name="teachertable">Teacher's View</button>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <button type="submit" name="truncateInput">Other Dept</button>
            </form> -->
            <!-- <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;"> -->
            <a href="input.php"><button type="submit" name="exportExcel">Export to Excel</button></a>
            <!-- </form> -->
            <!-- <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                <button type="submit" name="new">Clear</button>
            </form> -->
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