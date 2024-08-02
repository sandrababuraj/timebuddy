<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require 'connection.php';

// Initialize variables to store form data
$year = $sem = $dept = $class = $subject = $teacher = $weekhr = '';

// Initialize variable to store error messages
$errors = array();

// Fetch distinct departments and their total weekly hours from subjects table
$subjectsQuery = "SELECT dept, SUM(weekhr) as total_weekhr FROM subjects GROUP BY dept";
$subjectsResult = $conn->query($subjectsQuery);

// Fetch distinct departments and their total weekly hours from labs table
$labsQuery = "SELECT dept, SUM(weekhr) as total_weekhr FROM labs GROUP BY dept";
$labsResult = $conn->query($labsQuery);

$departments = [];

// Merge results from both queries
if ($subjectsResult->num_rows > 0) {
    while ($row = $subjectsResult->fetch_assoc()) {
        $departments[$row['dept']]['subjects'] = $row['total_weekhr'];
    }
}

if ($labsResult->num_rows > 0) {
    while ($row = $labsResult->fetch_assoc()) {
        $departments[$row['dept']]['labs'] = $row['total_weekhr'];
    }
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $year = $_POST['year'];
    $sem = $_POST['sem'];
    $dept = $_POST['dept'];
    $class = $_POST['class'];
    $subject = $_POST['subject'];
    $teacher = $_POST['teacher'];
    $weekhr = $_POST['weekhr'];

    // Validate form fields
    if (empty($year)) {
        $errors['year'] = "Year is required";
    }
    if (empty($sem)) {
        $errors['sem'] = "Semester is required";
    }
    if (empty($dept)) {
        $errors['dept'] = "Department is required";
    }
    if (empty($class)) {
        $errors['class'] = "Class is required";
    }
    if (empty($subject)) {
        $errors['subject'] = "Subject is required";
    }
    if (empty($teacher)) {
        $errors['teacher'] = "Teacher is required";
    }
    if (empty($weekhr)) {
        $errors['weekhr'] = "Hours per week is required";
    } else if (!is_numeric($weekhr) || $weekhr <= 0) {
        $errors['weekhr'] = "Hours per week must be a positive number";
    }

    // Calculate current total hours for the department
    $currentTotalHours = 0;
    if (isset($departments[$dept])) {
        $currentTotalHours = (isset($departments[$dept]['subjects']) ? intval($departments[$dept]['subjects']) : 0) + (isset($departments[$dept]['labs']) ? intval($departments[$dept]['labs']) : 0);
    }

    // Check if the new total hours would exceed 240
    if ($currentTotalHours + intval($weekhr) > 240) {
        $errors['weekhr'] = "Adding these hours would exceed the maximum allowed hours for the department.";
    }

    // If there are no errors, insert data into the database
    if (empty($errors)) {
        // Prepare and bind the SQL statement
        $stmt = $conn->prepare("INSERT INTO subjects (year, sem, dept, class, subject, teacher, weekhr) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssi", $year, $sem, $dept, $class, $subject, $teacher, $weekhr);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to a success page or show a success message
            echo "Success";
            exit();
        } else {
            // Display an error message if the SQL statement fails
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Add subjects</title>
    <link rel="stylesheet" href="style6.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200&display=swap" rel="stylesheet">
    <style>
        .right-table {
            position: absolute;
            right: 20px;
            top: 20px;
        }

        .right-table table {
            border-collapse: collapse;
            width: 100%;
        }

        .right-table th,
        .right-table td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .error {
            color: red;
        }
    </style>
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
            <h2>Add Subject</h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label>Year</label><br>
                    <input list="yearList" name="year" value="<?php echo htmlspecialchars($year); ?>"><br>
                    <datalist id="yearList">
                        <option value="1">
                        <option value="2">
                        <option value="3">
                        <option value="4">
                    </datalist>
                    <span class="error"><?php echo isset($errors['year']) ? $errors['year'] : ''; ?></span>
                </div>
                <div class="form-group">
                    <label>Semester</label><br>
                    <input list="semList" name="sem" value="<?php echo htmlspecialchars($sem); ?>"><br>
                    <datalist id="semList">
                        <option value="o">Odd</option>
                        <option value="e">Even</option>
                    </datalist>
                    <span class="error"><?php echo isset($errors['sem']) ? $errors['sem'] : ''; ?></span>
                </div>

                <div class="form-group">
                    <label>Department</label><br>
                    <input list="deptList" name="dept" value="<?php echo htmlspecialchars($dept); ?>"><br>
                    <datalist id="deptList">
                        <option value="CSE">
                        <option value="CE">
                        <option value="IT">
                        <option value="ECE">
                        <option value="EEE">
                    </datalist>
                    <span class="error"><?php echo isset($errors['year']) ? $errors['year'] : ''; ?></span>
                </div>
                <div class="form-group">
                    <label>Class</label><br>
                    <input list="classList" name="class" value="<?php echo htmlspecialchars($class); ?>"><br>
                    <datalist id="classList">
                        <option value="S1">
                        <option value="S2">
                        <option value="S3">
                        <option value="S4">
                        <option value="S5">
                        <option value="S6">
                        <option value="S7">
                        <option value="S8">
                    </datalist>
                    <span class="error"><?php echo isset($errors['class']) ? $errors['class'] : ''; ?></span>
                </div>
                <div class="form-group">
                    <label>Subject</label><br>
                    <input type="text" name="subject" value="<?php echo htmlspecialchars($subject); ?>"><br>
                    <span class="error"><?php echo isset($errors['subject']) ? $errors['subject'] : ''; ?></span>
                </div>
                <div class="form-group">
                    <label>Teacher</label><br>
                    <input type="text" name="teacher" value="<?php echo htmlspecialchars($teacher); ?>"><br>
                    <span class="error"><?php echo isset($errors['teacher']) ? $errors['teacher'] : ''; ?></span>
                </div>
                <div class="form-group">
                    <label>Hours per Week</label><br>
                    <input type="number" name="weekhr" value="<?php echo htmlspecialchars($weekhr); ?>"><br>
                    <span class="error"><?php echo isset($errors['weekhr']) ? $errors['weekhr'] : ''; ?></span>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
        <div class="right-table">
            <table>
                <tr>
                    <th>Department</th>
                    <th>Subjects Hours</th>
                    <th>Labs Hours</th>
                    <th>Total Hours</th>
                </tr>
                <?php foreach ($departments as $dept => $hours) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dept); ?></td>
                        <td><?php echo isset($hours['subjects']) ? $hours['subjects'] : 0; ?></td>
                        <td><?php echo isset($hours['labs']) ? $hours['labs'] : 0; ?></td>
                        <td><?php echo (isset($hours['subjects']) ? $hours['subjects'] : 0) + (isset($hours['labs']) ? $hours['labs'] : 0); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="prevnext">
        <a href="profile.php" class="previous">&laquo; Previous</a>
        <a href="addlab.php" class="next">Next &raquo;</a>
        <a href="profile.php" class="previous round">&#8249;</a>
        <a href="addlab.php" class="next round">&#8250;</a>
        </div>
    </div>
    
</body>

</html>