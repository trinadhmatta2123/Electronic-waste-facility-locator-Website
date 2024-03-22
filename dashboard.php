<?php
// Establish database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ewaste";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["username"])) {
    header("location: login.php");
    exit;
}

$username = $_SESSION["username"];
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

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .page-container {
            max-width: 1000px;
            background-color: #fff;
            padding: 40px;
            border-radius: 35px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
        }

        .page-header {
            text-align: center;
            margin-bottom: 20px;
        }

        h1, h2, p {
            margin: 0;
        }

        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        h2 {
            color: #555;
            font-size: 22px;
        }

        .points-bar {
            background-color: #7d2ae8;
            color: #fff;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .points-bar p {
            margin: 0;
        }

        .city-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .city-link {
            background-color: #5b13b9;
            color: #fff;
            padding: 15px;
            margin: 15px 0;
            text-decoration: none;
            border-radius: 15px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            width: calc(33.3333% - 30px);
            box-sizing: border-box;
            text-align: center;
            position: relative;
        }

        .city-link:hover {
            background-color: #3f0a6f;
            transform: scale(1.05);
        }

        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #333;
        }

        a:hover {
            text-decoration: underline;
        }

        .blink {
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% {
                opacity: 0.5;
            }
            50% {
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <h1>Hi, <b><?php echo htmlspecialchars($username); ?><br></b> Select Your City</h1>
        </div>

        <div class="points-bar">
            <p>Your Points: <b><?php echo $points; ?></b></p>
            <!-- Add any additional elements to the points bar as needed -->
        </div>

        <div class="city-container">
            <!-- Replace "CityName" with your actual city names -->
            <?php
            $cities = array("Hyderabad", "Vijayawada", "Vizag", "Banglore", "Tirupati", "Nellore", "Chittor", "Delhi", "Nagpur", "Lucknow", "kochi", "Warangal");
            foreach ($cities as $city) {
                echo '<a class="city-link" href="city_info2.php?city=' . urlencode($city) . '">' . htmlspecialchars($city) . '</a>';
            }
            ?>
        </div>

        <a href="logout.php">Sign Out</a>
    </div>
</body>
</html>
