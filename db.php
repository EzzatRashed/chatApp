<?php

// Prevent Direct Access to this File
if ($_SERVER['REQUEST_METHOD'] == 'GET' && realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('location: 404.php');
    die();
}

// PDO Params
$host = 'localhost';
$db = 'chatApp';
$charset = 'UTF8';
$user = 'root';
$pass = '';

// PDO Connection to MySQL
$conn = new PDO('mysql:host='.$host.';dbname='.$db.';charset='.$charset, $user, $pass ,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8"));

?>