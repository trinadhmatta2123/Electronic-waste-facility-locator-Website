<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("location: login.php");
    exit;
}

$username = $_SESSION["username"];
$selectedCity = isset($_GET['city']) ? htmlspecialchars($_GET['city']) : '';
$selectedDevice = isset($_GET['device']) ? htmlspecialchars($_GET['device']) : '';
$pointsIncrease = 10; // Points to be added when the user uploads an image

\require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Details</title>
    <style>
        body, h2, p {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f6f8fa;
            color: #333;
            overflow-x: hidden;
        }

        #uploadSection, #modelDetails {
            opacity: 0;
            transform: translateX(-50px);
            animation: slideIn 1.6s forwards;
        }

        #modelDetails {
            animation-delay: 0.5s;
        }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        #uploadSection {
            background-color: #ffffff;
            padding: 20px 40px;
            border-radius: 10px;
            box-shadow: 0px 3px 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 70px auto 30px auto;
        }

        h2 {
            color: #4a90e2;
            margin-bottom: 30px;
            font-size: 24px;
        }

        label {
            display: block;
            margin-bottom: 15px;
            font-weight: 600;
        }

        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 30px;
            border: 1px solid #e1e1e1;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background-color: #4a90e2;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #357ab7;
        }

        img {
            max-width: 100%;
            margin-top: 30px;
            border-radius: 10px;
        }

        #modelDetails {
            background-color: #ffffff;
            margin: 30px auto;
            max-width: 600px;
            padding: 20px 40px;
            border-radius: 10px;
            box-shadow: 0px 3px 15px rgba(0, 0, 0, 0.1);
        }

        #modelDetails p {
            margin-bottom: 25px;
        }

        #modelDetails p strong {
            font-weight: 600;
        }
#pointsContainer {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(145deg, #673ab7, #8e24aa);
            color: #fff;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 5px 5px 20px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        #pointsContainer div {
            margin: 5px 0;
        }

        #pointsDisplay {
            font-size: 1.7em;
            font-weight: 600;
            letter-spacing: 1px;
            border-bottom: 3px solid #fff;
            padding: 5px 15px;
            border-radius: 15px;
        }

        form {
            max-width: 300px;
            margin: 100px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }
.hidden {
    display: none;
}

.loader {
    border: 16px solid #f3f3f3;  /* Light grey */
    border-top: 16px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}


    </style>
</head>
<body background="devbg.jpg">
<div id="pointsContainer">
        <div><?php echo htmlspecialchars($username); ?></div>
        <div id="pointsDisplay"><b>Points:</b><b><?php echo $points; ?></b></div>
    </div>
<div id="uploadSection">
<p>Selected Device: <b><?php echo $selectedDevice; ?></b></p>
    <h2>Upload Device Image</h2>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?city=" . $selectedCity . "&device=" . $selectedDevice; ?>" method="post" enctype="multipart/form-data">
        <label for="image">Upload Image:</label>
        <input type="file" name="image" id="image" required>
        <br>
        <input type="submit" value="Upload">
    </form>
<div id="modelDetails">
 <?php if (!empty($components)): ?>
        <ul>
            <?php foreach ($components as $component): ?>
                <li><?php echo htmlspecialchars($component); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No components found for this device.</p>
    <?php endif; ?>
</div>
<a href="dashboard.php">Go back to Dashboard</a><br>
    <a href="logout.php">Sign Out</a>










</body>
</html>
