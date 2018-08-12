<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET' && realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('location: 404.php');
    die();
}

echo '
<div class="chat_box">
	<div class="people_list">
		
	</div>
	<div id="chat">
		
	</div>
</div>';

?>