<?php
session_start(); // Start the session

// Check if the user is logged in; otherwise, redirect to the login page
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require 'connection.php';

// Get the logged-in username
$username = $_SESSION['username'];

// Fetch user-specific data from the tb_data table
$query = "SELECT * FROM data WHERE username = '$username'";
$result = mysqli_query($conn, $query);

if ($result) {
    // Fetch the data as an associative array
    $userData = mysqli_fetch_assoc($result);
} else {
    // Handle database query error if needed
    $userData = array();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>User Profile</title>
    <link rel="stylesheet" href="style4.css">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Briem+Hand:wght@100..900&display=swap" rel="stylesheet">
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

        <?php
        // Handle tab switching
        $currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

        // Display content based on the selected tab
        switch ($currentTab) {
            case 'dashboard':
                echo '<div class="tabcontent"><h3>Dashboard</h3><p>Welcome to your dashboard, ' . $userData['name'] . '!</p></div>';
                break;

            case 'profile':
                echo '<div class="tabcontent"><h3>Profile</h3>';
                echo '<p>Name: ' . $userData['name'] . '</p>';
                echo '<p>' . $userData['role'] . '</p>';
                echo '<p>Institution: ' . $userData['institution'] . '</p>';
                echo '</div>';
                break;

            default:
                // Handle an invalid tab
                echo '<div class="tabcontent"><h3>Error</h3><p>Invalid tab selected.</p></div>';
                break;
        }

        $generateLink = 'generate.php';

        // $role = $userData['role'];
        // // Check if the role is 'Teacher'
        // if ($role === 'Teacher') {
        //     // If the role is 'Teacher', navigate to input2.php
        //     $generateLink = 'input2.php';
        // } else {
        //     // For other roles, navigate to input.php
        //     $generateLink = 'input.php';
        // }
        ?>
    

        <div class="steps">
            <div><a href="addsub.php" class="tablinks <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'addsubjects') ? 'active' : ''; ?>"><button>Add Subjects</button></a></div>
            <div><a href="addlab.php" class="tablinks <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'addlabs') ? 'active' : ''; ?>"><button>Add Labs</button></a></div>
            <div><a href="<?php echo $generateLink; ?>" class="tablinks <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'generate') ? 'active' : ''; ?>"><button>Generate</button></a></div>
            <!-- <div><a href="" class="tablinks <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'edit') ? 'active' : ''; ?>"><button>Edit</button></a></div> -->
        </div>
    </div>
</body>

</html>