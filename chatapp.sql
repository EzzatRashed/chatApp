CREATE DATABASE chatapp;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `user_name` varchar(30) NOT NULL,
  `user_age` int(2) NOT NULL,
  `user_gender` varchar(10) NOT NULL,
  `user_token` varchar(100) NOT NULL,
  `user_last_active` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `conversations` (
  `conv_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `conv_token` varchar(20) NOT NULL,
  `conv_username1` varchar(30) NOT NULL,
  `conv_username2` varchar(30) NOT NULL,
  `conv_last_active` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `messages` (
  `msg_id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `conv_token` varchar(30) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `msg_body` varchar(1000) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `msg_from` varchar(30) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `msg_to` varchar(30) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `msg_seen` tinyint(1) NOT NULL DEFAULT '0',
  `msg_date_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
