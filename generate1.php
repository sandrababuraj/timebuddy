<?php
// Database connection
require 'connection.php';

// Connect to MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
ob_start();
$sql_update_classtable_empty = "UPDATE classtable SET teacher = '', subject = ''";
if ($conn->query($sql_update_classtable_empty) === TRUE) {
    echo "Classtable updated successfully. <br>";
} else {
    echo "Error updating classtable: " . $conn->error;
}
$sql_truncate_input = "TRUNCATE TABLE input";
if ($conn->query($sql_truncate_input) === FALSE) {
    echo "Error truncating input table: " . $conn->error;
}
// Define unique combinations of year and semester
$combinations = array(
    array("year" => 1),
    array("year" => 2),
    array("year" => 3),
    array("year" => 4)
);

$deptcombinations = array(
    array("dept" => 'IT'), array("dept" => 'CSE'),array("dept" => 'CE'), array("dept" => 'EEE') , array("dept" => 'ECE')
);
$semcombinations = array(
    array("sem" => 'o')
);
// array("sem" => 'e')
foreach ($deptcombinations as $deptcombination) {
    $dept = $deptcombination['dept'];
    foreach ($semcombinations as $semcombination) {
        $semester = $semcombination['sem'];
        // Iterate over each unique combination of year
        foreach ($combinations as $combination) {
            $year = $combination['year'];
            // Loop until there are no empty subjects
            // while (checkForEmptySubjects($year, $semester, $dept, $conn)) {
                // Set all subject fields in classtable to ''
                $sql_update_subject_empty = "UPDATE classtable SET subject = '', teacher = '' WHERE year = $year AND sem = '$semester' AND dept='$dept'";
                if ($conn->query($sql_update_subject_empty) === FALSE) {
                    echo "Error updating subject field in classtable: " . $conn->error;
                    continue; // Move to the next combination
                }
                // Update classtable with REM teacher and allocate REM subjects
                allocateREM($year, $semester, $dept, $conn);

                // Allocate labs
                allocateLabs($year, $semester, $dept, $conn);

                // // Allocate other subjects
                // allocateOtherSubjectsAndTeachers($year, $semester, $dept, $conn);
            // }
        }
    }
}

foreach ($deptcombinations as $deptcombination) {
    $dept = $deptcombination['dept'];
    foreach ($semcombinations as $semcombination) {
        $semester = $semcombination['sem'];
        // Iterate over each unique combination of year
        foreach ($combinations as $combination) {
            $year = $combination['year'];
            // Loop until there are no empty subjects
            while (checkForEmptySubjects($year, $semester, $dept, $conn)) {
                // Set all subject fields in classtable to ''
                // $sql_update_subject_empty = "UPDATE classtable SET subject = '', teacher = '' WHERE year = $year AND sem = '$semester' AND dept='$dept'";
                // if ($conn->query($sql_update_subject_empty) === FALSE) {
                //     echo "Error updating subject field in classtable: " . $conn->error;
                //     continue; // Move to the next combination
                // }
                // Update classtable with REM teacher and allocate REM subjects
                // allocateREM($year, $semester, $dept, $conn);

                // // Allocate labs
                // allocateLabs($year, $semester, $dept, $conn);

                // // Allocate other subjects
                allocateOtherSubjectsAndTeachers($year, $semester, $dept, $conn);
            }
        }
    }
}
// foreach ($deptcombinations as $deptcombination) {
//     $dept = $deptcombination['dept'];
//     foreach ($semcombinations as $semcombination) {
//         $semester = $semcombination['sem'];
//         // Iterate over each unique combination of year
//         foreach ($combinations as $combination) {
//             $year = $combination['year'];
//             // Loop until there are no empty subjects
//             while (checkForEmptySubjects($year, $semester, $dept, $conn)) {
//                 // Allocate other subjects
//                 allocateOtherSubjectsAndTeachers($year, $semester, $dept, $conn);
//             }
//         }
//     }
// }
header("Location: input.php");
// Function to allocate REM subjects
function allocateREM($year, $semester, $dept, $conn)
{
    // Fetch REM weekhr and teacher from subjects table
    $sql_select_rem = "SELECT weekhr, teacher FROM subjects WHERE subject = 'REM' AND year = $year AND sem = '$semester' AND dept='$dept'";
    $result_select_rem = $conn->query($sql_select_rem);
    if ($result_select_rem->num_rows > 0) {
        $row = $result_select_rem->fetch_assoc();
        $remWeekHr = $row['weekhr'];
        $remTeacher = $row['teacher'];
    } else {
        echo "REM data not found in the 'subjects' table";
        return; // Exit the function if REM data is not found
    }

    // Allocate REM subject randomly to combinations where pno = 6
    $remCombinations = array(
        array("day" => "MON", "pno" => 6),
        array("day" => "TUE", "pno" => 6),
        array("day" => "WED", "pno" => 6),
        array("day" => "THU", "pno" => 6),
        array("day" => "FRI", "pno" => 6)
    );

    $remAllocatedCount = 0;
    while ($remAllocatedCount < $remWeekHr) {
        // Randomly select one REM combination
        $randomIndex = array_rand($remCombinations);
        $selectedCombination = $remCombinations[$randomIndex];

        $day = $selectedCombination['day'];
        $pno = $selectedCombination['pno'];

        // Check if the combination is available
        $sql_check_availability = "SELECT * FROM classtable WHERE day = '$day' AND pno = '$pno' AND subject = '' AND year = $year AND sem = '$semester' AND dept='$dept'";
        $result_check_availability = $conn->query($sql_check_availability);

        if ($result_check_availability->num_rows > 0) {
            // Update classtable with REM subject and teacher
            $sql_update_classtable_rem = "UPDATE classtable SET subject = 'REM', teacher = '$remTeacher' WHERE day = '$day' AND pno = '$pno' AND year = $year AND sem = '$semester' AND dept='$dept'";
            if ($conn->query($sql_update_classtable_rem) === FALSE) {
                echo "Error updating REM data in classtable: " . $conn->error;
            } else {
                $remAllocatedCount++;

                // Remove selected combination from remCombinations
                unset($remCombinations[$randomIndex]);
            }
        } else {
            // Remove selected combination from remCombinations even if not allocated
            unset($remCombinations[$randomIndex]);
        }
    }
}


// Function to allocate labs// Function to allocate labs
function allocateLabs($year, $semester, $dept, $conn)
{
    // Fetch labs from labs table
    $sql_select_labs = "SELECT lab, teacher, weekhr FROM labs WHERE year = $year AND sem = '$semester' AND dept='$dept'";
    $result_select_labs = $conn->query($sql_select_labs);

    // Check if there are labs in the labs table
    if ($result_select_labs->num_rows > 0) {
        while ($row = $result_select_labs->fetch_assoc()) {
            $lab = $row['lab'];
            $teacher = $row['teacher'];
            $weekhr = $row['weekhr'];

            // Define lab-specific day and pno combinations for lab allocation
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
            } else {
                echo "Invalid week hour count for lab: $weekhr";
                continue; // Skip this lab and move to the next one
            }

            // Shuffle the labCombinations array to randomize the order
            shuffle($labCombinations);

            // Allocate lab to a single combination
            $labAllocated = false; // Flag to track if the lab has been successfully allocated

            // Iterate through shuffled labCombinations array
            foreach ($labCombinations as $index => $combination) {
                $day = $combination['day'];
                $pnos = $combination['pno'];

                // Check if all pnos in the combination are available
                $allPnosAvailable = true;
                foreach ($pnos as $pno) {
                    // Check if the pno is available in the current department
                    $sql_check_availability = "SELECT * FROM classtable WHERE day = '$day' AND pno = '$pno' AND subject = '' AND year = $year AND sem = '$semester' AND dept='$dept'";
                    $result_check_availability = $conn->query($sql_check_availability);
                    if ($result_check_availability->num_rows == 0) {
                        $allPnosAvailable = false;
                        break;
                    }
                    
                    // Check if the same teacher is assigned in other departments
                    $sql_check_teacher_conflict = "SELECT * FROM classtable WHERE day = '$day' AND pno = '$pno' AND teacher = '$teacher' AND year = $year AND sem = '$semester' AND dept = '$dept' AND $year!= year";
                    $result_check_teacher_conflict = $conn->query($sql_check_teacher_conflict);
                    // Check if the same teacher is assigned in other departments
                    $sql_check_teacher_conflict1 = "SELECT * FROM classtable WHERE day = '$day' AND pno = '$pno' AND teacher = '$teacher' AND year = $year AND sem = '$semester' AND dept != '$dept'";
                    $result_check_teacher_conflict1 = $conn->query($sql_check_teacher_conflict1);
                    if ($result_check_teacher_conflict->num_rows > 0 || $result_check_teacher_conflict1->num_rows > 0) {
                        $allPnosAvailable = false;
                        break;
                    }
                }

                // If all pnos are available, allocate the lab
                if ($allPnosAvailable) {
                    foreach ($pnos as $pno) {
                        // Update lab assignment in the classtable
                        $sql_update_classtable_lab = "UPDATE classtable SET subject = '$lab', teacher = '$teacher' WHERE day = '$day' AND pno = '$pno' AND year = $year AND sem = '$semester' AND dept='$dept'";
                        if ($conn->query($sql_update_classtable_lab) === FALSE) {
                            echo "Error updating lab data in classtable: " . $conn->error;
                        }
                    }

                    // Set flag to true indicating successful allocation
                    $labAllocated = true;

                    break; // Break the loop after successful allocation
                }
            }

            // Display an error message if the lab could not be allocated
            if (!$labAllocated) {
                echo "Error: Lab '$lab' could not be allocated due to insufficient available slots.";
            }
        }
    } else {
        echo "No labs found in the 'labs' table";
    }
}

// Function to allocate other subjects and teachers
function allocateOtherSubjectsAndTeachers($year, $semester, $dept, $conn)
{
    // Fetch all subjects except REM with their corresponding weekhr values from the subjects table
    $sql_select_subjects = "SELECT subject, weekhr, teacher FROM subjects WHERE subject != 'REM' AND year = $year AND sem = '$semester' AND dept='$dept'";
    $result_select_subjects = $conn->query($sql_select_subjects);

    // Check if there are subjects in the subjects table
    if ($result_select_subjects->num_rows > 0) {
        // Initialize an empty array to store subjects and their counts
        $subjectsCounts = array();

        // Fetch subjects, their corresponding weekhr values, and teachers and store them in the subjectsCounts array
        while ($row = $result_select_subjects->fetch_assoc()) {
            $subject = $row['subject'];
            $weekhr = $row['weekhr'];
            $teacher = $row['teacher'];

            // Store the subject, its weekhr value, and teacher in the subjectsCounts array
            $subjectsCounts[$subject] = array("weekhr" => $weekhr, "teacher" => $teacher);
        }

        // Initialize an empty array to store subjects for random entry
        $subjectsArray = array();

        // Iterate through subjectsCounts array and add each subject to subjectsArray based on its count
        foreach ($subjectsCounts as $subject => $data) {
            for ($i = 0; $i < $data["weekhr"]; $i++) {
                $subjectsArray[] = array("subject" => $subject, "teacher" => $data["teacher"]);
            }
        }

        // Update subjects and teachers in the classtable table
        allocateSubjectsAndTeachers($subjectsArray, $year, $semester, $dept, $conn);
    } else {
        echo "No subjects found in the 'subjects' table for year $year, semester $semester";
    }
    return;
}


// function updateRemainingSubjects($year, $semester, $dept, $conn, $subjectsArray)
// {
//     // Query to find blank, NULL, or empty entries in the subject field of classtable
//     $sql_select_blank_entries = "SELECT id, day, pno FROM classtable WHERE subject = '' AND year = $year AND sem = '$semester' AND dept='$dept'";
//     $result_select_blank_entries = $conn->query($sql_select_blank_entries);

//     // Check if there are any blank entries in the subject field
//     if ($result_select_blank_entries->num_rows > 0) {
//         // Iterate through each blank entry and allocate remaining subjects
//         while ($row = $result_select_blank_entries->fetch_assoc()) {
//             $id = $row['id'];
//             $day = $row['day'];
//             $pno = $row['pno'];

//             // Check if there are remaining subjects in the subjectsArray
//             if (!empty($subjectsArray)) {
//                 // Get the first subject from the subjectsArray
//                 $remainingSubjectData = reset($subjectsArray);
//                 $remainingSubject = $remainingSubjectData['subject'];
//                 $remainingTeacher = $remainingSubjectData['teacher'];

//                 // Update the classtable with the remaining subject and teacher
//                 $sql_update_remaining_subject = "UPDATE classtable SET subject = '$remainingSubject', teacher = '$remainingTeacher' WHERE id = $id";
//                 if ($conn->query($sql_update_remaining_subject) === FALSE) {
//                     echo "Error updating remaining subject in classtable: " . $conn->error;
//                 } else {
//                     // Remove the allocated subject from the subjectsArray
//                     array_shift($subjectsArray);
//                 }
//             } else {
//                 // If there are no remaining subjects, break out of the loop
//                 break;
//             }
//         }
//     }
// }
// Function to allocate subjects and teachers to combinations
function allocateSubjectsAndTeachers($subjectsArray, $year, $semester, $dept, $conn)
{
    $deptcombinations = array(
        array("dept" => 'IT'), array("dept" => 'CSE'),array("dept" => 'CE'), array("dept" => 'EEE') , array("dept" => 'ECE')
    );
    // Initialize an empty array to keep track of the number of allocations per subject per day
    $allocationsPerSubjectPerDay = array();

    // Initialize an empty array to store which teachers have been assigned to each day and period combination
    $teachersAssigned = array();

    $prevSubject = null;
    $prevTeacher = null;
    $consecutiveCount = 0;

    // Fetch all combinations of day and period for the current semester
    $allCombinations = getAllCombinations($year, $semester, $dept, $conn);

    foreach ($allCombinations as $index => $combination) {
        if (empty($subjectsArray)) {
            break; // Break if there are no more subjects to allocate
        }

        $day = $combination['day'];
        $pno = $combination['pno'];

        // Shuffle the subjects array to randomize the order
        shuffle($subjectsArray);

        // Initialize an empty array to keep track of allocated teachers for the current day and period
        $allocatedTeachers = array();

        // Iterate through the shuffled subjects array
        foreach ($subjectsArray as $key => $subjectData) {
            $subject = $subjectData['subject'];
            $teacher = $subjectData['teacher'];

            // Check if the teacher has already been assigned to the same day and period combination elsewhere
            if (!isset($teachersAssigned[$day][$pno][$teacher])) {
                // Check if the subject has already been allocated twice on the current day
                if (!isset($allocationsPerSubjectPerDay[$day][$subject]) || $allocationsPerSubjectPerDay[$day][$subject] < 2) {
                    // displayTimetable($year, $semester, $conn);
                    // Fetch the corresponding teacher for the subject of the current day and period combination in other years
                    if ($prevSubject !== null && $subject === $prevSubject) {
                        continue; // Skip the current subject if it's the same as the previously allocated subject
                    }



                    // Fetch all departments from the dept table
                    // $sql_select_all_departments = "SELECT dno, dept FROM dept";
                    // $result_select_all_departments = $conn->query($sql_select_all_departments);

                    // if ($result_select_all_departments->num_rows > 0) {
                    //     while ($dept_row = $result_select_all_departments->fetch_assoc()) {
                    //         $dept_dno = $dept_row['dno'];
                    //         $dept_name = $dept_row['dept'];


                    for ($i = 1; $i <= 4; $i++) {
                        foreach ($deptcombinations as $deptcombination) {
                            $dept_name = $deptcombination['dept'];
                            if ($i == $year && $dept_name == $dept) {
                                continue; // Skip the current year
                            }

                            $sql_select_teacher_other_year = "SELECT teacher FROM classtable WHERE day = '$day' AND pno = $pno AND year = $i AND sem = '$semester' AND dept= '$dept_name'";
                            $result_select_teacher_other_year = $conn->query($sql_select_teacher_other_year);
                            if ($result_select_teacher_other_year->num_rows > 0) {
                                $row_other_year = $result_select_teacher_other_year->fetch_assoc();
                                $teacher_other_year = $row_other_year['teacher'];
                                // echo "\n,$teacher_other_year ";
                                // echo $teacher;
                                // Check if the teacher in other years is the same as the current teacher
                                if ($teacher_other_year === $teacher) {
                                    // echo "\n,$teacher_other_year ";
                                    // echo $teacher;
                                    // Skip this allocation and move to the next subject
                                    continue 3; // Continue the outer loop
                                }
                            }
                        }
                        // // Skip department clashes only if $dept_name is not the current $dept
                        // if ($dept_name != $dept) {
                        //     // Check if the teacher is already assigned to the same day and period in the current year for this department
                        //     $sql_select_teacher_same_year = "SELECT teacher FROM classtable WHERE day = '$day' AND pno = $pno AND year = $year AND sem = '$semester' AND dept = '$dept_name'";
                        //     $result_select_teacher_same_year = $conn->query($sql_select_teacher_same_year);
                        //     if ($result_select_teacher_same_year->num_rows > 0) {
                        //         $row_same_year = $result_select_teacher_same_year->fetch_assoc();
                        //         $teacher_same_year = $row_same_year['teacher'];

                        //         // Check if the teacher in the same year and different department is the same as the current teacher
                        //         if ($teacher_same_year === $teacher) {
                        //             // Skip this allocation and move to the next subject
                        //             continue 2; // Continue the outer loop
                        //         }
                        //     }


                        // } 
                        // $sql_select_teacher_other_year = "SELECT teacher FROM classtable WHERE day = '$day' AND pno = $pno AND year = $i AND sem = '$semester' AND dept = '$dept_name'";
                        //     $result_select_teacher_other_year = $conn->query($sql_select_teacher_other_year);
                        //     if ($result_select_teacher_other_year->num_rows > 0) {
                        //         $row_other_year = $result_select_teacher_other_year->fetch_assoc();
                        //         $teacher_other_year = $row_other_year['teacher'];

                        //         // Check if the teacher in other years is the same as the current teacher
                        //         if ($teacher_other_year === $teacher) {
                        //             // Skip this allocation and move to the next subject
                        //             continue 2; // Continue the outer loop
                        //         }
                        //     }

                        // // Skip department clashes only if $dept_name is not the current $dept
                        // if ($dept_name != $dept) {
                        //     // Check if the teacher is already assigned to the same day and period in the current year for this department
                        //     $sql_select_teacher_same_year = "SELECT teacher FROM classtable WHERE day = '$day' AND pno = $pno AND year = $year AND sem = '$semester' AND dept = '$dept_name'";
                        //     $result_select_teacher_same_year = $conn->query($sql_select_teacher_same_year);
                        //     if ($result_select_teacher_same_year->num_rows > 0) {
                        //         $row_same_year = $result_select_teacher_same_year->fetch_assoc();
                        //         $teacher_same_year = $row_same_year['teacher'];

                        //         // Check if the teacher in the same year and different department is the same as the current teacher
                        //         if ($teacher_same_year === $teacher) {
                        //             // Skip this allocation and move to the next subject
                        //             continue 2; // Continue the outer loop
                        //         }
                        //     }
                        // }
                    }
                    // }


                    if ($prevTeacher !== $teacher || $prevSubject !== $subject) {
                        // Check if the combination is available
                        $sql_check_availability = "SELECT * FROM classtable WHERE day = '$day' AND pno = '$pno' AND subject = '' AND teacher = '' AND year = $year AND sem = '$semester' AND dept='$dept'";
                        $result_check_availability = $conn->query($sql_check_availability);

                        if ($result_check_availability->num_rows > 0) {
                            // Update classtable with the current subject and teacher
                            $sql_update_classtable_subject_teacher = "UPDATE classtable SET subject = '$subject', teacher = '$teacher' WHERE day = '$day' AND pno = '$pno' AND year = $year AND sem = '$semester' AND dept='$dept'";

                            if ($conn->query($sql_update_classtable_subject_teacher) === FALSE) {
                                echo "Error updating subject and teacher data in classtable: " . $conn->error;
                            } else {
                                // Increment the count of allocations for the current subject on the current day
                                if (!isset($allocationsPerSubjectPerDay[$day][$subject])) {
                                    $allocationsPerSubjectPerDay[$day][$subject] = 1;
                                } else {
                                    $allocationsPerSubjectPerDay[$day][$subject]++;
                                }

                                // Add the teacher to the list of teachers assigned to this day and period combination
                                $teachersAssigned[$day][$pno][$teacher] = true;

                                $prevSubject = $subject;
                                $prevTeacher = $teacher;
                                // Remove the allocated subject from the subjects array
                                unset($subjectsArray[$key]);

                                break; // Break the inner loop after successfully allocating the subject
                            }
                        }
                    } else {
                        // Increment consecutive subject count
                        $consecutiveCount++;

                        // Check if consecutive subject count exceeds the threshold (e.g., 2)
                        if ($consecutiveCount >= 2) {
                            // If consecutive subjects are being allocated, skip this subject and move to the next one
                            continue 2; // Continue the outer loop
                        }
                    }
                }
            }
        }
    }

    // Display an error message if there are subjects that could not be allocated
    if (!empty($subjectsArray)) {
        echo "Error: Some subjects could not be allocated due to insufficient available slots.";
    }

    // Display the timetable after allocation
    // displayTimetable($year, $semester, $conn);
}

// Function to fetch all combinations of day and period for the current semester
function getAllCombinations($year, $semester, $dept, $conn)
{
    $sql_select_combinations = "SELECT DISTINCT day, pno FROM classtable WHERE year = $year AND sem = '$semester' AND dept='$dept'";
    $result_select_combinations = $conn->query($sql_select_combinations);
    $combinations = array();

    if ($result_select_combinations->num_rows > 0) {
        while ($row = $result_select_combinations->fetch_assoc()) {
            $combination = array("day" => $row['day'], "pno" => $row['pno']);
            $combinations[] = $combination;
        }
    }

    return $combinations;
}

// Function to update lab teachers
function checkForEmptySubjects($year, $semester, $dept, $conn)
{
    $sql_select_empty_subjects = "SELECT COUNT(*) AS count FROM classtable WHERE (subject IS NULL OR subject = '') AND year = $year AND sem = '$semester' AND dept='$dept'";
    $result_select_empty_subjects = $conn->query($sql_select_empty_subjects);

    if ($result_select_empty_subjects->num_rows > 0) {
        $row = $result_select_empty_subjects->fetch_assoc();
        $emptySubjectsCount = $row['count'];
        if ($emptySubjectsCount > 0) {

            echo "Found $emptySubjectsCount empty subjects in the classtable for year $year, semester $semester. Restarting allocations...\n";
            return true; // Return true if empty subjects are found
        }
    }

    return false; // Return false if no empty subjects are found
}

// Function to display the timetable
function displayTimetable($year, $semester, $dept, $conn)
{
    echo "<h2>Timetable for Year $year, Semester $semester:</h2>";
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
    $sql_select_labs = "SELECT lab FROM labs WHERE year = $year AND sem = '$semester' AND dept='$dept'";
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
            $sql_select_subject = "SELECT subject, teacher FROM classtable WHERE day = '$day' AND pno = $pno AND year = $year AND sem = '$semester' AND dept='$dept'";
            $result_select_subject = $conn->query($sql_select_subject);

            if ($result_select_subject->num_rows > 0) {
                $row = $result_select_subject->fetch_assoc();
                $subject = $row['subject'];
                $teacher = $row['teacher'];
                // Check if the same subject has corresponding teacher value in the same day and pno combination of another year
                $sql_check_teacher_in_other_year = "SELECT COUNT(*) AS count FROM classtable WHERE day = '$day' AND pno = $pno AND teacher = '$teacher' AND sem = '$semester' AND year != $year AND dept='$dept'";
                $result_check_teacher_in_other_year = $conn->query($sql_check_teacher_in_other_year);
                $row_check_teacher_in_other_year = $result_check_teacher_in_other_year->fetch_assoc();
                $count = $row_check_teacher_in_other_year['count'];
                // If count > 0, set background color
                if ($count > 0) {
                    echo "<td style='background-color: lightgreen;'>$subject ($teacher)</td>";
                }
                // Check if the subject is a lab
                else if (in_array($subject, $labs)) {
                    echo "<td style='background-color: lightblue;'>$subject ($teacher)</td>";
                } else {
                    echo "<td>$subject ($teacher)</td>";
                }
            } else {
                echo "<td></td>";
            }
        }
        echo "</tr>";
    }

    echo "</table>";
}
