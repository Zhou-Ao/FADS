<?php
/*
 * LSBC Financial Application Management System
 * Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
 * File : deleteUser.php
 * Author : Cheng Gibson, Xu Qianqian
 * Version : v1.0
 *
 * This file provides the Delete User functionality of the Application
 */

// Includes common code, starts session, and initializes database connection
include './../backend/phpFunctions.php';
session_start();
initializeDB();

// Checks if user is logged in
if(!isLoggedIn()){
	// Redirects users to login page
	header("refresh:0.0001;url=./login.php");
	return ;
}
// Checks if user has CreateUser permission
if(!$_SESSION['canDeleteUser']){
	header("refresh:0.001;url=./home.php");
	return ;
}

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	//GET
	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		if(!isset($_GET['userID'])){
			//page does not work without userID
			$errorMsg = "An error has occured. Redirecting...";
			header("refresh:2;url=./home.php");
			return ;
		}
		//GET userID is SET
		
		//get userID, replace illegal characters
		$userID = $_GET['userID'];
		if(DeleteUser($userID)){
			header("refresh:0.001;url=./viewUser.php");
		}
	}
}
pageLogic();

// Deletes all relevant entries of the userID from the user tables
function DeleteUser($userID) {
	$command1 =  $_SESSION['connection']->prepare ( "DELETE FROM systemuser WHERE userID=?" ) or die ( "Failed to create prepared!" );
	$command1->bind_param ( 'i', $userID ) and $command1->execute () or die ( "Failed to create prepared!" );

	$command2 =  $_SESSION['connection']->prepare ( "DELETE FROM systemuserdetails WHERE userID=?" ) or die ( "Failed to create prepared!" );
	$command2->bind_param ( 'i', $userID ) and $command2->execute () or die ( "Failed to create prepared!" );

	$command3 =  $_SESSION['connection']->prepare ( "DELETE FROM systemuserperms WHERE userID=?" ) or die ( "Failed to create prepared!" );
	$command3->bind_param ( 'i', $userID ) and $command3->execute () or die ( "Failed to create prepared!" );

	return true;
}
?>
