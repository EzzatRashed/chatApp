<?php

/* 

This API responds with HTML text instead of JSON format.
I intended to make it simple as possible and not using any javascript libraries to convert JSON to HTML.
If you want to use JSON to reduce server traffic, you can use any of those libraries:
1 - jQuery   -- https://github.com/jquery/jquery
2 - Mustache -- https://github.com/janl/mustache.js
3 - JsRender -- https://github.com/BorisMoore/jsrender
4 - Or you can use ES6's template litrals, but still not supported in all browsers.

EDIT: Okay, obviously using pure javascript wasn't the best idea. :(

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

function deleteQuery($sql, $param1, $param2){
	global $conn;
    $query = $conn->prepare($sql);
    $query->bindParam($param1, $param2);
    $query->execute();
}

function generateErrorHTML($error_msg){
	return '
	<li class="error_msg">
		<h4>'.$error_msg.'</h4>
	</li>
	';
}

function generateSideListHTML($contact, $inbox){
	if ($contact['user_id'] == $_SESSION['user_id']) {
		return;
	}

	$inbox_msgs = '';
	$new_msgs = '';
	if ($inbox == true) {
		global $conn;
		$sql = "SELECT * FROM messages WHERE msg_from = :other_user_name AND msg_to = :my_user_name AND msg_seen = '0'";
		$msgs = $conn->prepare($sql);
		$msgs->bindParam(':other_user_name', $contact['user_name']);
		$msgs->bindParam(':my_user_name', $_SESSION['user_name']);
		$msgs->execute();
		$msgs = $msgs->fetchAll(PDO::FETCH_ASSOC);
		if (count($msgs) > 0) {
			$inbox_msgs = '<span style="color:#ff4f4f; font-size:15px; font-weight:700;"> ('.count($msgs).')</span>';
			$new_msgs = 'new_msgs';
		}
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
			<h4 class="name '.$new_msgs.'">'.$contact['user_name'].' - <span style="font-size:12px;">'.$contact['user_age'].' yo </span>'.$inbox_msgs.'</h4>
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
	} else {
		return;
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

function updateUserLastActive($my_user_name){
	global $conn;
	$update_query = $conn->prepare("UPDATE users SET user_last_active = CURRENT_TIMESTAMP WHERE user_name = :my_user_name");
	$update_query->bindParam(":my_user_name", $my_user_name);
	$update_query->execute();
}

function checkExsistingConv($my_user_name, $other_user_name){
	global $conn;

	if ($my_user_name == $other_user_name) {
		return;
	}

	// Ensure User Exsists
	$sql = "SELECT * FROM users WHERE user_name = :other_user_name";
	$user = selectQuery($sql, ':other_user_name', $other_user_name);

	if (empty($user)){
		return;
	} 

	// Check Exsisting Conversation
	$sql = "SELECT * FROM conversations WHERE (conv_username1 = :my_user_name AND conv_username2 = :other_user_name) OR (conv_username1 = :other_user_name AND conv_username2 = :my_user_name) LIMIT 1";
	$conv_query = $conn->prepare($sql);
	$conv_query->bindParam(":my_user_name", $my_user_name);
	$conv_query->bindParam(":other_user_name", $other_user_name);
	$conv_query->execute();
	return $conv_query->fetch(PDO::FETCH_ASSOC);
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
			$sql = "DELETE FROM users WHERE user_name = :user_name";
			deleteQuery($sql, ':user_name', $user_name);

			$sql = "DELETE FROM conversations WHERE conv_username1 = :user_name OR conv_username2 = :user_name";
			deleteQuery($sql, ':user_name', $user_name);

			$sql = "DELETE FROM messages WHERE msg_from = :user_name OR msg_from = :user_name";
			deleteQuery($sql, ':user_name', $user_name);
			unset($contacts[$key]);
		} 
	}
	if (count($contacts) == 1) {
		echo '
		<h2 class="default_h2">Still Nobody Here!</h2>
		';
	} else {
		foreach($contacts as $contact) {
			echo generateSideListHTML($contact, false);
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
		    echo generateSideListHTML($contact[0], true);
		}
	}
}

// Response to loadContact() AJAX Call
if (isset($_SESSION['user_id']) && isset($_POST['load_chat']) && !empty($_POST['load_chat'])) {
	$my_user_name = $_SESSION['user_name'];
	$other_user_name = $_POST['load_chat'];

	$conv_query = checkExsistingConv($my_user_name, $other_user_name);
	echo '<ul id="chat_list" class="chat">';
	if(empty($conv_query)){
		echo '
		<div id="empty">
			<h2 class="default_h2">No Messages Yet!</h2>
			<p class="default_p">Send a message and start the conversation now!</p>
		</div>
		';
	} else {
		$conv_token = $conv_query['conv_token'];
		$sql = "SELECT * FROM messages WHERE conv_token = :conv_token";
		$messages = selectQuery($sql, ':conv_token', $conv_token);
		foreach ($messages as $message) {
			$msg_id = $message['msg_id'];
			$msg = $message['msg_body'];
			$msg_from = $message['msg_from'];
			$msg_to = $message['msg_to'];
			$msg_time = formatTimeString($message['msg_date_time']);
			if ($msg_from == $my_user_name) {
				$avatar = getAvatar($_SESSION['user_gender']);
			} elseif ($msg_from == $other_user_name) {
				$sql = "SELECT * FROM users WHERE user_name = :user_name";
				$contact = selectQuery($sql, ':user_name', $other_user_name);
				$avatar = getAvatar($contact[0]['user_gender']);
				$update_query = $conn->prepare("UPDATE messages SET msg_seen = '1' WHERE msg_id='$msg_id'");
				$update_query->execute();
			} else {
				die();
			}
			echo generateChatHTML($msg, $msg_from, $msg_to, $msg_time, $avatar);
		}
	}
	echo '
	</ul>
	<div id="img_upload_bar" style="display:none;">
		<h4 id="image_name"></h4>
	</div>
	<div id="emojis_menu" class="clearfix" style="display:none;">';
	$emojis_array = array('😂','🤣','😀','😁','😃','😄','😅','😆' ,'😉' ,'😊' ,'😋' ,'😎','😍' ,'😘' ,'😗' ,'😙' ,'😚' ,'🙂' ,'🤗' ,'🤩' ,'🤔' ,'🤨' ,'😐' ,'😑' ,'😶' ,'🙄' ,'😏' ,'😣' ,'😥' ,'😮' ,'🤐' ,'😯' ,'😪' ,'😫' ,'😴' ,'😌' ,'😛' ,'😜' ,'😝' ,'🤤' ,'😒' ,'😓' ,'😔' ,'😕' ,'🙃' ,'🤑','😲','☹','🙁' ,'😖' ,'😞' ,'😟' ,'😤' ,'😢' ,'😭' ,'😦','😧','😨' ,'😩' ,'🤯','😬','😰' ,'😱' ,'😳' ,'🤪' ,'😵' ,'😡' ,'😠' ,'🤬' ,'😷' ,'🤒' ,'🤕' ,'🤢' ,'🤮' ,'🤧' ,'😇' ,'🤠' ,'🤡' ,'🤥' ,'🤫' ,'🤭' ,'🧐' ,'🤓' ,'😈' ,'👿' ,'👹' ,'👺','💀','👻' ,'👽','🤖' ,'💩','😺','😸','😹' ,'😻' ,'😼' ,'😽' ,'🙀' ,'😿' ,'😾');


	foreach ($emojis_array as $emoji) {
		echo '<span class="emojis_menu_element">'.$emoji.'</span>';
	}
	echo '
	</div>
	<div class="send_msg">
		<form <form onsubmit="return sendMessage(this);">
			<textarea name="message_to_send" id="message_to_send" placeholder="Type a new message .." rows="1"></textarea>
			<button id="send_btn" name="submit">SEND</button>
			<img src="img/emoji.png" id="emojis_button" onclick="emojisMenu();">
			<label for="img_to_upload" id="send_pic"><img src="img/image.svg" id="send_pic"></label>
			<input style="display: none;" type="file" name="img_to_upload" id="img_to_upload" onchange="imageBar(this.files)">
		</form>
	</div>
	';

	// Update user_last_active Column
	updateUserLastActive($my_user_name);
}

// Response to sendMessage() AJAX Call
if (isset($_SESSION['user_id']) && isset($_POST['to']) && !empty($_POST['to']) || isset($_SESSION['user_id']) && isset($_SERVER['HTTP_TO']) && !empty($_SERVER['HTTP_TO'])) {
	$my_user_name = $_SESSION['user_name'];
	if (isset($_POST['msg']) && !empty($_POST['msg'])) {
		$other_user_name = $_POST['to'];		
		$msg_body = htmlspecialchars($_POST['msg']);
		$msg_body = preg_replace('#&lt;(/?(?:span))&gt;#', '<\1>', $msg_body); // Allow span to pass
	} elseif (isset($_FILES['img_to_upload']) && !empty($_FILES['img_to_upload']) && isset($_SERVER['HTTP_TO']) && !empty($_SERVER['HTTP_TO'])) {
		$other_user_name = $_SERVER['HTTP_TO'];

		$img_array = $_FILES['img_to_upload'];
    	$image_name = $img_array['name'];
   		$image_tmp_name = $img_array['tmp_name'];
  	    $image_size = $img_array['size'];
   		$image_error = $img_array['error'];
   		$image_ext = explode('.', $image_name);
	    $image_actual_ext = strtolower(end($image_ext));
	    $allowed = array('jpg', 'jpeg', 'png','');
	    $inarray = in_array($image_actual_ext, $allowed);
	    if (!$inarray == true) {
	    	$error_msg = 'This file extension is not allowed.';
	   		echo generateErrorHTML($error_msg);
	   		die();
	    }
        if (!$image_error === 0) {
        	$error_msg = 'There was an error uploading the image.';
	      	echo generateErrorHTML($error_msg);
	   		die();
        }
        if ($image_size > 5000000) {
        	$error_msg = 'This image exceeded the size limit 5MB.';
      		echo generateErrorHTML($error_msg);
	   		die();
        }
		
			
		if ($image_name != "") {
			$image_name_new = uniqid('', true).".".$image_actual_ext;
        	$image_destination = 'uploads/'.$image_name_new;
        	move_uploaded_file($image_tmp_name, $image_destination);
        }

		$msg_body = '<img class="uploaded_img" src="'.$image_destination.'">';	
	} else {
		die();
	}

	// Ensure User Exsists
	$sql = "SELECT * FROM users WHERE user_name = :other_user_name";
	$user = selectQuery($sql, ':other_user_name', $other_user_name);

	if (empty($user)){
		echo generateErrorHTML('User is not available any more!');
		die();
	} 

	// Check For Exsisting Conversation
	$conv_query = checkExsistingConv($my_user_name, $other_user_name);
	if(empty($conv_query)){
		$conv_token = bin2hex(random_bytes(10));
		$sql = "INSERT INTO conversations (conv_token, conv_username1, conv_username2) VALUES (:conv_token, :conv_username1, :conv_username2)";
		$insert_query = $conn->prepare($sql);
		$insert_query->execute(array(
		    ':conv_token'     => $conv_token,
		    ':conv_username1' => $my_user_name,
		    ':conv_username2' => $other_user_name,
	    ));
	} else {
		$conv_token = $conv_query['conv_token'];
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
    $update_query = $conn->prepare("UPDATE conversations SET conv_last_active = CURRENT_TIMESTAMP WHERE conv_token = :conv_token");
	$update_query->bindParam(":conv_token", $conv_token);
	$update_query->execute();

	// Receive Formatted Message
	echo generateChatHTML($msg_body, $my_user_name, $other_user_name, formatTimeString(date("Y-m-j H:i:s")), getAvatar($_SESSION['user_gender']));

	// Update user_last_active Column
	updateUserLastActive($my_user_name);
}

// Response to getLastMsg() AJAX Call
if (isset($_SESSION['user_id']) && isset($_POST['get_msg_from']) && !empty($_POST['get_msg_from'])){
	$other_user_name = $_POST['get_msg_from'];

	$sql = "SELECT * FROM users WHERE user_name = :user_name";
	$contact = selectQuery($sql, ':user_name', $other_user_name);

	if (empty($contact)){
		die();
	} 

	$avatar = getAvatar($contact[0]['user_gender']);
	$sql = "SELECT * FROM messages WHERE msg_from = :other_user_name AND msg_seen = '0'";
	$messages = selectQuery($sql, ':other_user_name', $other_user_name);
	foreach ($messages as $message) {
		$msg_id = $message['msg_id'];
		$msg = $message['msg_body'];
		$msg_from = $message['msg_from'];
		$msg_to = $message['msg_to'];
		$msg_time = formatTimeString($message['msg_date_time']);

		echo generateChatHTML($msg, $msg_from, $msg_to, $msg_time, $avatar);

		$update_query = $conn->prepare("UPDATE messages SET msg_seen = '1' WHERE msg_id='$msg_id'");
		$update_query->execute();
	}
}

// Response to updateChatbox() AJAX Call
if (isset($_SESSION['user_id']) && isset($_POST['get_data_time']) && !empty($_POST['get_data_time'])){
	$my_user_name = $_SESSION['user_name'];
	$other_user_name = $_POST['get_data_time'];

	// Check For Exsisting Conversation
	$conv_query = checkExsistingConv($my_user_name, $other_user_name);
	if(empty($conv_query)){
		die();
	} else {
		$conv_token = $conv_query['conv_token'];
	}

	$sql = "SELECT * FROM messages where conv_token = :conv_token ORDER BY msg_id";
	$messages = selectQuery($sql, ':conv_token', $conv_token);
	foreach ($messages as $message) {
		$msg_data_time = formatTimeString($message['msg_date_time']);

		echo $msg_data_time.',';
	}
}

// Response to getMsgStatus() AJAX Call
if (isset($_SESSION['user_id']) && isset($_POST['get_msg_status']) && !empty($_POST['get_msg_status'])){
	$my_user_name = $_SESSION['user_name'];
	$other_user_name = $_POST['get_msg_status'];

	// Check For Exsisting Conversation
	$conv_query = checkExsistingConv($my_user_name, $other_user_name);
	if(empty($conv_query)){
		die();
	} else {
		$conv_token = $conv_query['conv_token'];
	}

	$sql = "SELECT * FROM messages where conv_token = :conv_token ORDER BY msg_id DESC LIMIT 1";
	$message = selectQuery($sql, ':conv_token', $conv_token);

	if ($message[0]['msg_seen'] == 1) {
		echo 'seen';
	}
}

?>