<?php
include './phpFunctions.php';
session_start();

if(isset($_SESSION['userID'])){
	session_destroy();
	session_start();
	echo "1";
} else {
	echo "0";
}
?>