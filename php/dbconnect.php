<?php
//error_reporting(0);
ob_start();
session_start();
define('ROLE_ADMIN', 'admin');
define('ROLE_SECRETARIA', 'secretaria');
$siteName = "Cipet.in";

//DEFINE("BASE_URL","http://cipetbhopal.com/");
//DEFINE("BASE_URL","http://localhost/");

DEFINE ('DB_USER', 'root');
DEFINE ('DB_PSWD', ''); 
DEFINE ('DB_HOST', 'localhost'); 
DEFINE ('DB_NAME', 'paysystem'); 

date_default_timezone_set('America/Bogota'); 
$conn =  new mysqli(DB_HOST,DB_USER,DB_PSWD,DB_NAME);
if($conn->connect_error)
die("Failed to connect database ".$conn->connect_error );