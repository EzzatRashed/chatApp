<?php 

// Session Start and Require DataBase PDO
session_start();
require_once 'db.php';

/* Login Function */
function login($user_token){
	global $conn; 
	$login_query = $conn->prepare("SELECT * FROM users WHERE user_token = :user_token LIMIT 1");
	$login_query->bindParam(":user_token", $user_token);
	$login_query->execute();

	if($login_query->rowCount() == 0){
		setcookie('MEMBER', '', strtotime('-3 days'));
		session_unset();
		$_SESSION['message'] = '
	    <div class="error">
	  		<p><strong style="padding: 15px">Error :</strong>Login failed, please try again.</p>
		</div>';
       	header("location: index.php");
		die();
	} else {
		$user = $login_query->fetch(PDO::FETCH_ASSOC);
		
		$_SESSION['user_id'] = $user['user_id'];
		$_SESSION['user_name'] = $user['user_name'];
		$_SESSION['user_age'] = $user['user_age'];
		$_SESSION['user_gender'] = $user['user_gender'];

		header("location: chat.php");
		die();
	}
}
/* Login Function Ends Here */


// Check For Different Cases
if (isset($_SESSION['user_id'])){
	header("location: chat.php");
	die();
}

if (isset($_COOKIE['MEMBER'])) {
	$user_token = $_COOKIE['MEMBER'];
	login($user_token);
}

if (isset($_POST['submit'])) {
	$user_name = $_POST['user_name'];
	$user_age = $_POST['user_age'];
	$user_gender = $_POST['user_gender'];

	if (empty($user_name) || empty($user_age) || empty($user_age)) {
		$_SESSION['message'] = '
	    <div class="error">
	  		<p><strong style="padding: 15px">Error :</strong>Please fill all fiels.</p>
		</div>';
       	header("location: index.php");
		die();
	}

	$error_query = $conn->prepare("SELECT * FROM users WHERE user_name = :user_name");
	$error_query->bindParam(':user_name', $user_name);
	$error_query->execute();

	if($error_query->rowCount() > 0){
		$user = $error_query->fetch(PDO::FETCH_ASSOC);
		$user_last_active = strtotime($user['user_last_active']);

		if ($user_last_active < strtotime('-3 days')) {
			$delete_query = $conn->prepare("DELETE FROM users WHERE user_name = :user_name");
			$delete_query->bindParam(':user_name', $user_name);
			$delete_query->execute();
		} else {
			$_SESSION['message'] = '
		    <div class="error">
		  		<p><strong style="padding: 15px">Error :</strong>This Nickname is taken.</p>
			</div>';
	       	header("location: index.php");
			die();
		} 
	}

	$user_token = bin2hex(random_bytes(50));
	setcookie('MEMBER', $user_token, strtotime('+3 days'));
	$sql = "INSERT INTO users (user_name, user_age, user_gender, user_token) VALUES (:user_name, :user_age, :user_gender, :user_token)";
	$insert_query = $conn->prepare($sql);
	$insert_query->bindParam(':user_name', $user_name);
	$insert_query->execute(array(
	    ':user_name' => $user_name,
	    ':user_age' => $user_age,
	    ':user_gender' => $user_gender,
	    ':user_token' => $user_token,
    ));

	login($user_token);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>chatApp - Login</title>
	<link rel="icon" href="img/icon.png">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body id="body" class="clearfix">
	<h3 class="main_header">chatApp php_ajax</h3>
	<div class="login_box clearfix">
		<div class="login_header">
			<h2>Login</h2>
		</div>
		<div class="login_body">
			<?php if( isset($_SESSION['message']) AND !empty($_SESSION['message']) ){
				echo $_SESSION['message'];
				unset($_SESSION['message']);
			} ?>
			<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
				<div class="login_container">
				    <label for="user_name">Nickname</label>
				    <input class="input_box" value="" type="text" name="user_name" required="">
				    <label for="user_age">Age</label>
				    <input class="input_box" type="number" name="user_age" required="" value="" min="13" max="70">
				    <div class="gender">
		                <input class="radio" type="radio" name="user_gender" value="Male">
		                <label class="radio" for="gender">Male</label>
		                <input class="radio" type="radio" name="user_gender" value="Female">    
		                <label class="radio" for="gender">Female</label>
		            </div>
				    <button class="submit_btn" type="submit" name="submit">Start Chat Now!</button>
			    </div>
			</form>
		</div>
	</div>
</body>
</html>