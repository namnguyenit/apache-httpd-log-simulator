<?php
// Bắt đầu quá trình ghi log trực tiếp
require_once('custom_logger.php');
write_enriched_log();
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Caf&eacute;!</title>
	<link rel="stylesheet" href="css/styles.css">
</head>

<body class="bodyStyle">

	<div id="header" class="mainHeader">
		<hr>
		<div class="center">Caf&eacute;</div>
	</div>
	<br>
	<?php
		// Get the application environment parameters from the Parameter Store.
		include ('getAppParameters.php');

		// Display the server metadata information if the showServerInfo parameter is true.
		include('serverInfo.php');
	?>
	<hr>
	<div class="topnav">
		<a href="index.php">Home</a>
		<a href="#aboutUs">About Us</a>
		<a href="#contactUs">Contact Us</a>
		<a href="menu.php">Menu</a>
		<a href="orderHistory.php">Order Histor</a>
	</div>
	<hr>
	<div id="mainContent">

		<div id="mainPictures" class="center">
			<table>
				<tr>
					<td><img src="images/Coffee-and-Pastries.jpg" height=auto width="490"></td>
					<td><img src="images/Cake-Vitrine.jpg" height=auto width="450"></td>
				</tr>
			</table>
			<hr>
			<p>Our caf&eacute; offers an assortment of delicious and delectable pastries and coffees that will put a smile on your face. From cookies to croissants, tarts and cakes, each treat is especially prepared to excite your tastebuds and brighten your day!</p>
			<br>
			<table>
				<tr>
					<td bgcolor="aquamarine">
						<div class="cursiveText">Frank bakes a rich variety of cookies. Try them all!</div>
						<table>
							<tr>
								<td><img src="images/Cookies.jpg" height=auto width="300"></td>
							</tr>
						</table>
					</td>
					<td bgcolor="orange">
						<table>
							<tr>
								<td><img src="images/Cup-of-Hot-Chocolate.jpg" height=auto width="200"></td>
								<td class="cursiveText">Tea,<br>Coffee,<br>Lattes,<br> and Hot Chocolate.<br>Yes, we have it!</td>
							</tr>
						</table>
					</td>
					<td bgcolor="aquamarine">
						<div class="cursiveText">Our tarts are always <br/> a customer favorite!<br><br>
					  </div>
						<table>
							<tr>
								<td><img src="images/Strawberry-Tarts.jpg" height=auto width="170"></td>
								<td><img src="images/Strawberry-Blueberry-Tarts.jpg" height=auto width="170"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<hr>
		</div>
	</div>

	<div id="aboutUs" class="center">
		<hr>
		<div>
			<h2>About Us</h2>
		</div>
			<table>
				<tr>
					<td><img src="images/Frank-Martha.jpg" height=auto width="400"></td>
					<td><p>Frank and Martha have been adding sweetness to their customers' lives since 2020.  Frank's recipes have been passed down from his mother and use simple and fresh ingredients to produce delightful flavors.  Both of them will personally greet you with a welcoming smile when you visit!</p></td>
				</tr>
			</table>
			<hr>
		</div>

	<div id="contactUs" align="center">
		<hr>
		<div>
			<h2>Contact Us</h2>
		</div>
		<table>
			<tr>
				<td><img src="images/Coffee-Shop.jpg" height=auto width="120"></td>
			</tr>
		</table>
		<div><p>123 Any Street<br>Any Town, USA<br><br>Tel: +1-800-555-0193</p></div>
		<div>
			<h3>Hours</h3>
		</div>
		<div>Weekdays: 6:00am - 6:00pm<br>Saturday: 7:00am - 7:00pm<br>Closed on Sundays</div>
	</div>

	<div id="Copyright" class="center">
		<h5>&copy; 2020, Amazon Web Services, Inc. or its Affiliates. All rights reserved.</h5>
	</div>

<div class="center" style="margin: 20px;">
    <button id="getLocationBtn">Cho phép lấy vị trí</button>
    <div id="locationStatus"></div>
</div>
<script>
document.getElementById('getLocationBtn').onclick = function() {
    const statusDiv = document.getElementById('locationStatus');
    if (navigator.geolocation) {
        statusDiv.textContent = 'Đang lấy vị trí...';
        navigator.geolocation.getCurrentPosition(function(position) {
            fetch('processLocation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                })
            })
            .then(response => response.json())
            .then(data => {
                statusDiv.textContent = 'Đã gửi vị trí thành công!';
            })
            .catch(error => {
                statusDiv.textContent = 'Gửi vị trí thất bại!';
            });
        }, function(error) {
            statusDiv.textContent = 'Bạn đã từ chối hoặc có lỗi khi lấy vị trí.';
        });
    } else {
        statusDiv.textContent = 'Trình duyệt không hỗ trợ lấy vị trí.';
    }
};
</script>
<script>
(function() {
    function sendLocation(coords) {
        fetch('log_location.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(coords)
        });
    }
    function saveAndSendLocation(position) {
        var coords = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude
        };
        localStorage.setItem('user_location', JSON.stringify(coords));
        sendLocation(coords);
    }
    if (localStorage.getItem('user_location')) {
        var coords = JSON.parse(localStorage.getItem('user_location'));
        sendLocation(coords);
    } else if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(saveAndSendLocation);
    }
})();
</script>

</body>
</html>
