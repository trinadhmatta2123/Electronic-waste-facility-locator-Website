<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["username"])) {
    header("location: login.php");
    exit;
}

$username = $_SESSION["username"];
$selectedCity = isset($_GET['city']) ? htmlspecialchars($_GET['city']) : '';
$selectedDevice = isset($_GET['device']) ? htmlspecialchars($_GET['device']) : '';
$pointsIncrease = 10; // Points to be added when the user uploads an image

// Include config file here to connect to the database
require_once "config.php";

$components = [];
$sql = "SELECT component FROM device_components WHERE device = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("s", $selectedDevice);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $components[] = $row['component'];
        }
    }
}
// Handle image upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the form was submitted
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $targetDir = "uploads/"; // Directory to store uploaded images
        $targetFile = $targetDir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        }
    // Update user points in the database
    $sqlUpdatePoints = "UPDATE users SET points = points + ? WHERE username = ?";
    if ($stmt = $mysqli->prepare($sqlUpdatePoints)) {
        $stmt->bind_param("is", $pointsIncrease, $param_username);
        $param_username = $username;

        $stmt->execute();
        $stmt->close();
    }
}

// Fetch user points from the database
$sql = "SELECT points FROM users WHERE username = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("s", $param_username);
    $param_username = $username;

    if ($stmt->execute()) {
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($points);
            $stmt->fetch();
        }
    }
    $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Device Information</title>
    <style>
        body { font: 14px sans-serif; text-align: center; }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Welcome, <b><?php echo htmlspecialchars($username); ?></b></h1>
        <p>Selected City: <b><?php echo $selectedCity; ?></b></p>
        <p>Selected Device: <b><?php echo $selectedDevice; ?></b></p>
        <p>Your Points: <b><?php echo $points; ?></b></p>
    </div>

    <!-- Image upload form -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?city=" . $selectedCity . "&device=" . $selectedDevice; ?>" method="post" enctype="multipart/form-data">
        <label for="image">Upload Image:</label>
        <input type="file" name="image" id="image" required>
        <br>
        <input type="submit" value="Upload and Earn Points">
    </form>

    <a href="dashboard.php">Go back to Dashboard</a><br>
    <a href="logout.php">Sign Out</a>
</body>
</html>
