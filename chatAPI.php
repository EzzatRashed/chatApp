<?php

/* 

This API responds with HTML text instead of JSON format.
I intended to make it simple as possible and not using any javascript libraries to convert JSON to HTML.
If you want to use JSON to reduce server traffic, you can use any of those libraries:
1 - jQuery   -- https://github.com/jquery/jquery
2 - Mustache -- https://github.com/janl/mustache.js
3 - JsRender -- https://github.com/BorisMoore/jsrender
4 - Or you can use ES6's template litrals, but still not supported in all browsers.

*/

// Session Start and Require DataBase PDO
session_start();
require_once 'db.php';

/* Functions */

function getAvatar($gender){
	if ($gender == 'Male') {
		return 'img/profile_picture_male.svg';
	} elseif ($gender == 'Female') {
		return 'img/profile_picture_female.svg';
	}
}

function executeQuery($sql, $param1, $param2) {
    global $conn;
    $query = $conn->prepare($sql);
    if (!empty($param1) && !empty($param2)) {
    	$query->bindParam($param1, $param2);
    }
    $query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function generateSideListHTML($contact){
	if ($contact['user_id'] == $_SESSION['user_id']) {
		return;
	}

	$time_now = strtotime(date("Y-m-j H:i:s"));
	$last_seen = strtotime($contact['user_last_active']);
	$time_diff = $time_now - $last_seen;

	$status = "Offline";
	$color = "#ff4f4f";

	if ($time_diff < 120) {
		$status = "Online";
		$color = "#86BB71";
	}
	return '
	<li class="contact" onclick="loadContact('."'".$contact['user_name']."'".');">
		<img src="'.getAvatar($contact['user_gender']).'" alt="'.$contact['user_name'].'">
		<div class="info">
			<h4 class="name">'.$contact['user_name'].' - <span style="font-size:12px;">'.$contact['user_age'].' yo</span></h4>
			<h4 class="status"><div class="status" style="background-color:'.$color.'"></div>'.$status.'</h4>
		</div>
	</li>
	';	
}

/*function generateChatHTML($e){
	echo $e;
}*/

/* Functions End Here */

// Prevent Direct Access to this File
if ($_SERVER['REQUEST_METHOD'] == 'GET' && realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) || !isset($_SESSION['user_id'])) {
    header('location: 404');
    die();
}

// Response to loadSideListHTML() AJAX Call
if (isset($_SESSION['user_id']) && isset($_POST['load_side_list'])) {
	$my_user_name = $_SESSION['user_name'];

	$sql = "SELECT * FROM users ORDER BY user_last_active DESC";
	$contacts = executeQuery($sql, '', '');
	if (count($contacts) == 1) {
		echo '
		<h2 class="default_h2">Still Nobody Here!</h2>
		';
	} else {
		foreach($contacts as $contact) {
		    echo generateSideListHTML($contact);
		}
	}

	echo '/n/r';

	$sql = "SELECT * FROM conversations  WHERE conv_username1 = :user_name OR conv_username2 = :user_name ORDER BY conv_last_active DESC";
	$conversations  = executeQuery($sql, ':user_name', $my_user_name);
	if (empty($conversations )) {
		echo '
		<h2 class="default_h2">Still Nobody Here!</h2>
		';
	} else {
		foreach($conversations  as $conv) {
			$conv_id = $conv['conv_id'];
			if ($my_user_name == $conv['conv_username1']) {
				$other_user_name = $conv['conv_username2'];
			} elseif ($my_user_name == $conv['conv_username2']) {
				$other_user_name = $conv['conv_username1'];
			} else {
				die();
			}
			$sql = "SELECT * FROM users WHERE user_name = :user_name";
			$contact = executeQuery($sql, ':user_name', $other_user_name);
		    echo generateSideListHTML($contact[0]);
		}
	}
}

if (isset($_SESSION['user_id']) && isset($_POST['load_chat'])) {
	$my_user_name = $_SESSION['user_name'];
	$other_user_name = $_POST['load_chat'];

	$sql = "SELECT * FROM conversations WHERE (conv_username1 = :my_user_name AND conv_username2 = :other_user_name) OR (conv_username1 = :other_user_name AND conv_username2 = :my_user_name)";
	$contacts = executeQuery($sql, '', '');
}

?>