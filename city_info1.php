<?php
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["username"])) {
    header("location: login.php");
    exit;
}

$username = $_SESSION["username"];
$selectedCity = isset($_GET['city']) ? htmlspecialchars($_GET['city']) : '';
$points = 0; // Default points

// Include config file here to connect to the database
require_once "config.php";

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

// Fetch device and recycling center data from the database based on the selected city
$deviceRecyclingData = array();
$sql = "SELECT device, recycling_center FROM city_data WHERE city = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("s", $param_city);
    $param_city = $selectedCity;

    if ($stmt->execute()) {
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($device, $recyclingCenter);
            while ($stmt->fetch()) {
                $deviceRecyclingData[] = array('device' => $device, 'recycling_center' => $recyclingCenter);
            }
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
    <title>City Information</title>
    <style>
        body { font: 14px sans-serif; text-align: center; }
        .device-dropdown { margin: 10px; }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Welcome, <b><?php echo htmlspecialchars($username); ?></b></h1>
        <p>Selected City: <b><?php echo $selectedCity; ?></b></p>
        <p>Your Points: <b><?php echo $points; ?></b></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Device</th>
                <th>Recycling Center</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($deviceRecyclingData as $data) {
                echo "<tr><td>{$data['device']}</td><td>{$data['recycling_center']}</td></tr>";
            }
            ?>
        </tbody>
    </table>
<div class="device-dropdown">
        <select id="deviceSelect">
            <option value="phone">Phone</option>
            <option value="laptop">Laptop</option>
            <option value="refrigerator">Refrigerator</option>
            <!-- Add more device options as needed -->
        </select>
        <button onclick="redirectToDeviceInfoPage()">Select Device</button>
    </div>
    <a href="dashboard.php">Go back to Dashboard</a><br>
    <a href="logout.php">Sign Out</a>
<script>
        function redirectToDeviceInfoPage() {
            var selectedDevice = document.getElementById("deviceSelect").value;
            window.location.href = "device_info.php?city=<?php echo $selectedCity; ?>&device=" + selectedDevice;
        }
    </script>
</body>
</html>
