<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : phpFunctions.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file stores the common functionalities used by the many PHP pages in the Application
*/

// Suppresses errors (for Live environment)
error_reporting(0);

// Checks if user is logged in
function isLoggedIn(){
	if(isset($_SESSION['userID'])){
		return 1;	
	} else {
		return 0;
	}
}

// Initializes the user's connection session variable for database usage
function initializeDB(){
	$_SESSION['connection'] =  new mysqli("localhost","lsbcacc","lsbcadmin","lsbc_fads");
	//("localhost", "user", "password", "database");
	// Check connection
	if (mysqli_connect_errno())
	  {
	  //echo "Failed to connect to MySQL: " . mysqli_connect_error();
	  }
}
// Checks if user has connection to the database
function checkDBConnection(){
	echo mysqli_ping($_SESSION['connection']);
}

// Adds an element to the array passed in
function addToArray($array,$element){
	$array[count($array)]=$element;
	return $array;
}
// Removes an element from the array passed in
function removeFromArray($array,$element){
	$newarr = array();
	$i=0;while ($i < count($array)){
		if($array[$i]!=$element){
			$newarr[count($newarr)]= $array[$i];
		}
		$i++;
	}
	return $newarr;
}

// PHP function to throw a Javascript alert message
function jsmsg($msg){
	echo "<script>alert('$msg');</script>";
}

// Convenient alias to perform Regex matching
function matchRegex($data,$regex){
	return preg_match($regex,$data);
}

// Checks if nric is of valid format (computes and compares check digit)
function checkValidNRIC($nric){
	if(!matchRegex($nric,"/^[S]{1}[0-9]{7}[a-zA-Z]{1}$/")){
		return false;
	}
	
	$modNum = ( ($nric{1}*2) + ($nric{2}*7) + ($nric{3}*6) + ($nric{4}*5) + ($nric{5}*4) + ($nric{6}*3) + ($nric{7}*2))%11;
	switch($modNum){
		case 0:
			if($nric{8}!="J") return false;
			break;
		case 1:
			if($nric{8}!="Z") return false;
			break;
		case 2:
			if($nric{8}!="I") return false;
			break;
		case 3:
			if($nric{8}!="H") return false;
			break;
		case 4:
			if($nric{8}!="G") return false;
			break;
		case 5:
			if($nric{8}!="F") return false;
			break;
		case 6:
			if($nric{8}!="E") return false;
			break;
		case 7:
			if($nric{8}!="D") return false;
			break;
		case 8:
			if($nric{8}!="C") return false;
			break;
		case 9:
			if($nric{8}!="B") return false;
			break;
		case 10:
			if($nric{8}!="A") return false;
			break;
		default:
			break;
	}
	
	return true;
}
?>
