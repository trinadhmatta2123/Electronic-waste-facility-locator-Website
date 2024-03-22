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
$city = $mysqli->real_escape_string($_GET['city']); // Assuming you're getting the city from a query parameter

$sql = "SELECT device, recycling_center, picture FROM city_data WHERE city = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $city);
$stmt->execute();
$result = $stmt->get_result();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Waste Locator</title>
<style>
/* Base and Reset Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    transition: all 0.3s cubic-bezier(.25,.8,.25,1);
}

body {
    font-family: 'Arial', sans-serif;
    
    color: #444;
    
    line-height: 1.6;
    overflow-x: hidden;
    height: 200vh; /* Increased length */
}

/* Header Styles */
/* Reset some default browser styling */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f5f5f5;
    margin: 0;
    padding: 0;
}

header {
    background: url('headerimg.png') no-repeat center/cover;
    height: 250px;  /* Adjusted height for the additional heading */
    position: relative;
    overflow: hidden;
}

h1 {
    color: white;
font-family: "Times New Roman", Times, serif;
    font-size: 5.1rem;
    position: absolute;
    bottom: 60%;  /* Adjusted for positioning */
    left: -100%;
    animation: slideInFromLeft 2s forwards;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2);
}

h3 {
    color: white;
    font-size: 2rem;
    position: absolute;
    bottom: 30%;  /* Adjusted for positioning */
    right: -80%;
    animation: slideInFromRight 3s 1s forwards;  /* The "1s" delay allows the h1 animation to start first */
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
}

@keyframes slideInFromLeft {
    100% {
        left: 10%;
    }
}

@keyframes slideInFromRight {
    100% {
        right: 10%;
    }
}

/* Section Styles */
section {
    margin: 4rem auto;
    max-width: 1200px;
    padding: 2.5rem;
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

section h2 {
    font-size: 2.2rem;
    margin-bottom: 2rem;
    position: relative;
    padding-bottom: 10px;
}

section h2:after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    height: 5px;
    width: 70px;
    background-color: #007BFF;
}

/* Input and Button Styles */
label {
    display: block;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

input[type="text"],
input[type="file"],
button {
    width: 100%;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border: 2px solid #ccc;
    border-radius: 8px;
    font-size: 1.1rem;
    outline: none;
    transition: transform 0.3s;
}

input[type="text"]:hover,
input[type="file"]:hover {
    transform: translateY(-3px);
}

input[type="text"]:focus,
input[type="file"]:focus {
    border-color: #007BFF;
    box-shadow: 0 0 15px rgba(0, 123, 255, 0.5);
}

button {
    background: linear-gradient(45deg, #007BFF, #33C3F0);
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
    font-size: 1.2rem;
    letter-spacing: 1.2px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(0);
}

button:hover {
    transform: translateY(-5px);
    box-shadow: 0 7px 20px rgba(0, 0, 0, 0.15);
}

/* Modal Styles */
#myModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    overflow-y: auto; /* Added for scrolling */
}

#myModal div {
    background-color: rgba(255, 255, 255, 0.95);
    background-image: url('bglearnmore.png');
    background-size: cover;
    width: 65%;
    max-width: 750px;
    margin: 8% auto;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
}

#myModal p {
    color: #333;
    font-size: 1.2rem;
    margin-top: 1rem;
}

#myModal span {
    font-size: 2.5rem;
    color: #555;
    cursor: pointer;
    transition: 0.3s;
}

#myModal span:hover {
    color: #007BFF;
}

/* Footer Styles */
footer {
    text-align: center;
    padding: 2rem 0;
    background: rgba(0, 0, 0, 0.9);
    color: #fff;
    box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.1);
}

footer p {
    font-size: 1.1rem;
    letter-spacing: 1.1px;
}

/* Responsiveness */
@media screen and (max-width: 900px) {
    section, #myModal div {
        width: 85%;
    }
}

@media screen and (max-width: 600px) {
    header h1 {
        font-size: 2.2rem;
    }

    section {
        padding: 1.5rem;
    }
    
    section h2 {
        font-size: 1.8rem;
    }
}
.image-slider {
    width: 150%;
    margin: 5rem auto;
    overflow: hidden;
}

.image-slider img {
    max-width: 150%;  // This ensures images scale down if they are too big
    display: block;  // Removes any spacing
    margin: 0 auto;  // Centers the image horizontally
}

//styling for the drop down bar


        form {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 28px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            width: 500px;
            box-sizing: border-box;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 24px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
 body {
            font-family: 'Arial', sans-serif;
            position: relative;
           
            margin: 0;
            padding: 0;
            height: 100vh;
            
        }

        .popup {
            position: fixed;
            top: 60%;
            width: 500px;
            padding: 30px;
            background-color: #c7fbf1;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-50%);
            transition: all 0.7s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        .left {
            left: -450px;
        }

        .right {
            right: -450px;
        }

        .show-popup.left {
            left: 5%;
        }

        .show-popup.right {
            right: 5%;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 28px;
            background: #e74c3c;
            color: white;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: background 0.3s;
        }

        .close:hover {
            background: #c0392b;
        }

        h2 {
            margin-top: 0;
            color: #333;
        }

        p {
            color: #555;
        }
#pointsContainer {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            position: absolute;
            top: 5px;
            right: 20px;
            background: linear-gradient(145deg, #673ab7, #8e24aa);
            color: #fff;
            padding: 15px 25px;
            border-radius: 5px;
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

       
.tri
{
font-size: 34px;
}
footer {
height:400px
    background-color: black;
    padding: 40px;
    text-align: center;
  }

  .social-icons {
    margin: 0 auto;
    width: fit-content;
  }

  .social-icons img {
    width: 40px;
    margin: 0 5px;
  }

  .contact-info {
color:white;
    margin-top: 10px;
  }
</style>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css"/>

</head>

<body onload="initPopups()" background="bgproject.png">


    <header>
        <h1>E-Waste Locator</h1>
<h3>With Recycling the possibilities are endless...</h3>
    </header>
 <div id="pointsContainer">
        <div><?php echo"User:".htmlspecialchars($username); ?></div>
        <div id="pointsDisplay"><b><?php echo"Points:".$points; ?></b></div>
    </div>

<div class="image-slider">
    <div><img src="png4.png" alt="Description of Image 1"></div>
    <div><img src="png2.png" alt="Description of Image 2"></div>
    <div><img src="png3.png" alt="Description of Image 3"></div>
    <div><img src="slide1.png" alt="Description of Image 3"></div>
<div><img src="slide2.png" alt="Description of Image 3"></div>
<div><img src="slide3.png" alt="Description of Image 3"></div>
</div>



    <section>
       <div id="content">
    <h4 style="font-size: 4em;"><b><?php echo $selectedCity; ?></b></h4><hr><hr>

    <!-- centers in cities -->
    <h5>center locations</h5>
    <?php
        // Assuming $result is your mysqli query result
        if ($result->num_rows > 0) {
            echo "<table>";
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                
                        <td><div><img src='".htmlspecialchars($row['picture'])."' alt='Recycling Center Image'></div></td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No results found.</p>";
        }
        ?>
    
</div>
<h4 class="tri">Recycling Center Information</h4>


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


<script>
    function slideOpen() {
        let content = document.getElementById("content");
        content.style.maxHeight = "2000px";  // Adjust as per your content's requirement
    }
</script>

    </script>


    <section>
    <h2>E-Waste Education</h2>
    <button onclick="showPopup()">Learn More</button>
</section>

<!-- The Modal -->
<div id="myModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0); overflow: hidden;">
    <div style="background-image: url('devbg.jpg'); background-color: white; width: 50%;height: 60%; margin: 0 auto; margin-top: 10%; padding: 20px; border: 1px solid #aaa; position: relative;">
        <span onclick="hidePopup()" style="cursor: pointer; position: absolute; right: 10px; top: 5px;">&times;</span>
        <p>As mentioned, electronic waste contains toxic components that are dangerous to human health, such as mercury, lead, cadmium, polybrominated flame retardants, barium and lithium. The negative health effects of these toxins on humans include brain, heart, liver, kidney and skeletal system damage.</p>
    </div>
</div>

<script>
    function showPopup() {
        var modal = document.getElementById("myModal");
        modal.style.display = "block";
        modal.style.animation = "slideDown 0.5s forwards";

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                hidePopup();
            }
        }
    }

    function hidePopup() {
        var modal = document.getElementById("myModal");
        modal.style.animation = "slideUp 0.5s reverse";
        setTimeout(function() {
            modal.style.display = "none";
        }, 500);  // Match the duration of the animation
    }
function initPopups() {
    setTimeout(function() {
        document.querySelector('.left').classList.add('show-popup');
        document.querySelector('.right').classList.add('show-popup');
        document.body.style.overflow = 'hidden';  // Temporarily disable scrolling
    }, 500);  // Popups will slide in 0.5 seconds after the page loads.
}

  function closePopup(popupClass) {
    document.querySelector(popupClass).style.display = "none";

    // Check if both pop-ups are closed before restoring scrolling
    if (document.querySelector('.left').style.display == "none" &&
        document.querySelector('.right').style.display == "none") {
        document.body.style.overflow = 'auto';  // Restore scrolling
    }
}

</script>

<style>
    @keyframes slideDown {
        0% {
            transform: translateY(-100%);
        }
        100% {
            transform: translateY(0);
        }
    }

    @keyframes slideUp {
        0% {
            transform: translateY(0);
        }
        100% {
            transform: translateY(-100%);
        }
    }
.image-slider {
    width: 90%;
    margin: 2rem auto;
}

.image-slider img {
    width: 100%;
    height: auto;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}
 table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
</style>



    <section>
        <h2>Device Credits</h2>
        <select id="deviceSelect">
            <option value="phone">Phone</option>
            <option value="laptop">Laptop</option>
            <option value="refrigerator">Refrigerator</option>
            <!-- Add more device options as needed -->
        </select>
        <button onclick="redirectToDeviceInfoPage()">Select Device</button>
    </form>
    </section>

    <footer>
<p>Contact Us:</p><br>

        
<div class="social-icons">
    <img src="fb.png" alt="Facebook icon">
    <img src="lkd1.png" alt="linkedin icon">
    <img src="youtube3.png" alt="YouTube icon">
<img src="instagram.png" alt="YouTube icon">
  </div>
  <div class="contact-info">
    <p>Toll Free: 101-123485-79-7986</p>
  </div>    </footer>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script type="text/javascript">
   $(document).ready(function(){
    $('.image-slider').slick({
        infinite: true,
        slidesToShow: 1,  // Display one image at a time
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 1000,  // 1 seconds
        arrows: true,
        dots: false,
        adaptiveHeight: false  // Adjusts the height based on the image's height
    });
});

 function redirectToDeviceInfoPage() {
            var selectedDevice = document.getElementById("deviceSelect").value;
            window.location.href = "device_info1.php?city=<?php echo $selectedCity; ?>&device=" + selectedDevice;
        }
</script>
<div class="popup left">
    <span class="close" onclick="closePopup('.left')">&times;</span>
    <h2>E-waste Recycling: Benefits</h2>
    <p>E-waste recycling helps reduce environmental pollution, saves energy, and conserves natural resources. It also reduces the need for new raw materials, thus preserving natural habitats.</p>
</div>

<div class="popup right">
    <span class="close" onclick="closePopup('.right')">&times;</span>
    <h2>How to Recycle E-waste</h2>
    <p>Ensure you dispose of e-waste through proper channels like dedicated e-waste collection centers. Avoid throwing electronic devices in regular trash bins.</p>
</div>


</body>

</html>
