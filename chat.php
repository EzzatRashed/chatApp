<?php 

// Session Start and Require DataBase PDO
session_start();
require_once 'db.php';

function deleteQuery($sql, $param1, $param2){
	global $conn;
    $query = $conn->prepare($sql);
    $query->bindParam($param1, $param2);
    $query->execute();
}

// Logout Function
if (isset($_GET['logout']) && $_GET['logout'] == true && isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id']; 
	$user_name = $_SESSION['user_name']; 

	$sql = "DELETE FROM users WHERE user_id = :user_id";
	deleteQuery($sql, ':user_id', $user_id);

	$sql = "DELETE FROM conversations WHERE conv_username1 = :user_name OR conv_username2 = :user_name";
	deleteQuery($sql, ':user_name', $user_name);

	$sql = "DELETE FROM messages WHERE msg_from = :user_name OR msg_from = :user_name";
	deleteQuery($sql, ':user_name', $user_name);

	unset($_COOKIE['MEMBER']);
	setcookie('MEMBER', '', strtotime('-3 days'), '/chatApp');
}

if (!isset($_COOKIE['MEMBER'])) {
	unset($_SESSION['user_id']);
	session_unset();
}

if (!isset($_SESSION['user_id'])) {
	header("location: /chatApp");
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
<body id="body" class="clearfix">
	<h3 class="main_header"><a href="https://github.com/EzzatRashed/chatApp">chatApp php_ajax</a></h3>
	<div class="chat_box">
		<div class="side_list">
			<div class="Contacts_list">
				<div class="Contacts_header clearfix">
					<h3>Contacts</h3>
					<input class="search" id="search_input" type="text" onkeyup="searchContacts()" placeholder="Search">
				</div>
				<div class="loader">
					<div></div>
				</div>
				<ul id="contacts">
					
				</ul>
			</div>
			<div class="inbox">
				<div class="inbox_header clearfix">
					<h3>Inbox</h3>
					<a href="<?php echo $_SERVER['PHP_SELF'].'?logout=true';?>" class="logout"><span>Sign Out</span><img src="img/logout.svg" alt="sign_out"></a>
				</div>
				<div class="loader">
					<div></div>
				</div>
				<ul id="my_inbox">
					
				</ul>
			</div>
		</div>
		<div id="chat">
			<h2 class="default_h2">Welcome Back!</h2>
			<p class="default_p">You can now chat with anyone you want, Just click on them and start the conversation!</p>
		</div>
	</div>
</body>
<script type="text/javascript" src="js/main.js"></script>
</html>