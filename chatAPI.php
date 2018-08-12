<?php

// Prevent Direct Access to this File
if ($_SERVER['REQUEST_METHOD'] == 'GET' && realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('location: 404.php');
    die();
}

?>