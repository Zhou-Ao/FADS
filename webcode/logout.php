<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : logout.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Logout functionality of the Application
*/

// Includes common code and starts session
include './../backend/phpFunctions.php';
session_start();

// Checks if user is logged in
if(isLoggedIn()){
	// Destroys and creates a new session
	session_destroy();
	session_start();
}
// Redirects users to the Login page
header("refresh:0.0001;url=./login.php");	
?>