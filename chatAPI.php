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

function formatTimeString($timeStamp) {
	$str_time = strtotime($timeStamp);
	$str_time = date("Y-m-d H:i:sP", $str_time);
	$time = strtotime($str_time);
	$d = new DateTime($str_time);

	$weekDays = ['Mon', 'Tue', 'Wed', 'Thur', 'Fri', 'Sat', 'Sun'];
	$months = ['Jan', 'Feb', 'Mar', 'Apr', ' May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];

	if ($time > strtotime('-1 minutes')) {
	  return 'Just now';
	} elseif ($time > strtotime('-59 minutes')) {
	  $min_diff = floor((strtotime('now') - $time) / 60);
	  return $min_diff . ' min' . (($min_diff != 1) ? "s" : "") . ' ago';
	} elseif ($time > strtotime('-23 hours')) {
	  $hour_diff = floor((strtotime('now') - $time) / (60 * 60));
	  return $hour_diff . ' hour' . (($hour_diff != 1) ? "s" : "") . ' ago';
	} elseif ($time > strtotime('today')) {
	  return $d->format('G:i');
	} elseif ($time > strtotime('yesterday')) {
	  return 'Yesterday at ' . $d->format('G:i');
	} elseif ($time > strtotime('this week')) {
	  return $weekDays[$d->format('N') - 1] . ' at ' . $d->format('G:i');
	} else {
	  return $d->format('j') . ' ' . $months[$d->format('n') - 1] .
	  (($d->format('Y') != date("Y")) ? $d->format(' Y') : "") .
	  ' at ' . $d->format('G:i');
	}
}

function selectQuery($sql, $param1, $param2) {
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
	<li class="contact" onclick="loadContact('."'".$contact['user_name']."'".');" data-name="'.$contact['user_name'].'">
		<img src="'.getAvatar($contact['user_gender']).'" alt="'.$contact['user_name'].'">
		<div class="info">
			<h4 class="name">'.$contact['user_name'].' - <span style="font-size:12px;">'.$contact['user_age'].' yo</span></h4>
			<h4 class="status"><div class="status" style="background-color:'.$color.'"></div>'.$status.'</h4>
		</div>
	</li>
	';	
}

function generateChatHTML($msg, $msg_from, $msg_to, $msg_time, $avatar){
	if ($msg_from == $_SESSION['user_name']) {
		$class = 'msg_form_me';
	} elseif ($msg_to == $_SESSION['user_name']) {
		$class = 'msg_to_me';
	}

	echo '
	<li class="'.$class.'">
		<div class="msg_data"><img class="img_sender" src="'.$avatar.'" alt="'.$msg_from.'">
			<h4 class="msg_data_name">'.$msg_from.'</h4>
			<h4 class="msg_data_time">'.$msg_time.'</h4>
		</div>
        <div class="message '.$class.'">'.$msg.'</div>
    </li>';
}

/* Functions End Here */

// Prevent Direct Access to this File
if ($_SERVER['REQUEST_METHOD'] == 'GET' && realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) || !isset($_SESSION['user_id'])) {
    header('location: 404');
    die();
}

// Response to loadSideListHTML() AJAX Call
if (isset($_SESSION['user_id']) && isset($_POST['load_side_list']) && $_POST['load_side_list'] == true) {
		$my_user_name = $_SESSION['user_name'];

	$sql = "SELECT * FROM users ORDER BY user_last_active DESC";
	$contacts = selectQuery($sql, '', '');
	foreach($contacts as $key => $contact) {
		$user_last_active = strtotime($contact['user_last_active']);
		$user_name = $contact['user_name'];

		if ($user_last_active < strtotime('-3 days')) {
			$delete_query = $conn->prepare("DELETE FROM users WHERE user_name = :user_name");
			$delete_query->bindParam(':user_name', $user_name);
			$delete_query->execute();
			unset($contacts[$key]);
		} 
	}
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
	$conversations  = selectQuery($sql, ':user_name', $my_user_name);
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
			$contact = selectQuery($sql, ':user_name', $other_user_name);
		    echo generateSideListHTML($contact[0]);
		}
	}
}

if (isset($_SESSION['user_id']) && isset($_POST['load_chat']) && !empty($_POST['load_chat'])) {
	$my_user_name = $_SESSION['user_name'];
	$other_user_name = $_POST['load_chat'];

	$sql = "SELECT * FROM conversations WHERE (conv_username1 = :my_user_name AND conv_username2 = :other_user_name) OR (conv_username1 = :other_user_name AND conv_username2 = :my_user_name) LIMIT 1";
	$conv_query = $conn->prepare($sql);
	$conv_query->bindParam(":my_user_name", $my_user_name);
	$conv_query->bindParam(":other_user_name", $other_user_name);
	$conv_query->execute();
	echo '<ul id="chat_list" class="chat">';
	if($conv_query->rowCount() == 0){
		echo '
		<div id="empty">
			<h2 class="default_h2">No Messages Yet!</h2>
			<p class="default_p">Send a message and start the conversation now!</p>
		</div>
		';
	} else {
		$conv = $conv_query->fetch(PDO::FETCH_ASSOC);
		/*
		**
		*/
	}
	echo '
	</ul>
	<div class="send_msg">
		<textarea name="message_to_send" id="message_to_send" placeholder="Type a new message .." rows="1"></textarea>
		<button id="send_btn" onclick="send_message('."'".$other_user_name ."'".')">SEND</button>
	</div>
	';
}

if (isset($_SESSION['user_id']) && isset($_POST['msg']) && isset($_POST['to']) && !empty($_POST['msg']) && !empty($_POST['to'])) {
	$my_user_name = $_SESSION['user_name'];
	$other_user_name = $_POST['to'];
	$msg_body = htmlspecialchars($_POST['msg']);

	if ($my_user_name == $other_user_name) {
		die();
	}

	// Ensure User Exsists
	$sql = "SELECT * FROM users WHERE user_name = :other_user_name";
	$user = selectQuery($sql, ':other_user_name', $other_user_name);

	if (empty($user)){
		die();
	} 

	// Check For Exsisting Conversation
	$sql = "SELECT * FROM conversations WHERE (conv_username1 = :my_user_name AND conv_username2 = :other_user_name) OR (conv_username1 = :other_user_name AND conv_username2 = :my_user_name) LIMIT 1";
	$conv_query = $conn->prepare($sql);
	$conv_query->bindParam(":my_user_name", $my_user_name);
	$conv_query->bindParam(":other_user_name", $other_user_name);
	$conv_query->execute();
	if($conv_query->rowCount() == 0){
		$conv_token = bin2hex(random_bytes(20));
		$sql = "INSERT INTO conversations (conv_token, conv_username1, conv_username2) VALUES (:conv_token, :conv_username1, :conv_username2)";
		$insert_query = $conn->prepare($sql);
		$insert_query->execute(array(
		    ':conv_token'     => $conv_token,
		    ':conv_username1' => $my_user_name,
		    ':conv_username2' => $other_user_name,
	    ));
	} else {
		$conv = $conv_query->fetch(PDO::FETCH_ASSOC);
		$conv_token = $conv['conv_token'];
	}

	// Insert Message Query 
	$sql = "INSERT INTO messages (conv_token, msg_body, msg_from, msg_to) VALUES (:conv_token, :msg_body, :msg_from, :msg_to)";
	$insert_query = $conn->prepare($sql);
	$insert_query->execute(array(
	    ':conv_token' => $conv_token,
	    ':msg_body'   => $msg_body,
	    ':msg_from'   => $my_user_name,
	    ':msg_to'     => $other_user_name,
    ));

	// Receive Formatted Message
	echo generateChatHTML($msg_body, $my_user_name, $other_user_name, formatTimeString(date("Y-m-j H:i:s")), getAvatar($_SESSION['user_gender']));
}

?>