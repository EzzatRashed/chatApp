<?php 

// Session Start and Require DataBase PDO
session_start();
require_once 'db.php';

if (!isset($_COOKIE['MEMBER'])) {
	setcookie('MEMBER', '', strtotime('-3 days'));
	session_unset();
}

if (!isset($_SESSION['user_id'])) {
	header("location: index.php");
	die();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>chatApp</title>
	<link rel="icon" href="img/icon.png">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body id="body" class="clearfix" onload="load_data('chat_load=1')">
	<h3 class="main_header">chatApp php_ajax</h3>
	<div id="loader">
		<div></div>
	</div>
	<div id="chat_box">
		
	</div>
</body>
<script>
	function load_data(a) {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200 && this.responseText != "") {
				document.getElementById('loader').setAttribute("style","display:none;");
		 		document.getElementById('chat_box').innerHTML = this.responseText;
			}
		};
		xhttp.open("POST", "chatAPI.php", true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.send(a);
	}
</script>
</html>