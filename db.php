<?php

// Prevent Direct Access to this File
if ($_SERVER['REQUEST_METHOD'] == 'GET' && realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('location: 404');
    die();
}

// PDO Params
$host = 'localhost';
$db = 'chatapp';
$charset = 'utf8mb4'; // This is a super set of utf-8 that supports emojis
$user = 'root';
$pass = '';

// PDO Connection to MySQL
$conn = new PDO('mysql:host='.$host.';dbname='.$db.';charset='.$charset, $user, $pass ,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));

?>