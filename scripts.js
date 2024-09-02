function redirectToDeviceInfoPage() {
    var selectedDevice = document.getElementById("deviceSelect").value;
    window.location.href = "device_info.php?city=<?php echo $selectedCity; ?>&device=" + selectedDevice;
}
function redirectToDeviceInfoPage() {
    var selectedDevice = document.getElementById("deviceSelect").value;
    window.location.href = "device_info.php?city=<?php echo $selectedCity; ?>&device=" + selectedDevice;
}

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