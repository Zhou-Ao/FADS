<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="./../backend/cssstyle.css">
<script type="text/javascript" src="./../backend/jsfunctions.js"></script>
<script type="text/javascript">
// Applies page variables into form inputs
function setFormValues(){
	setElementValue('userName',userName);
	setElementValue('password',password);
	setElementValue('repassword',repassword);
	setElementValue('firstName',firstName);
	setElementValue('lastName',lastName);
	
	switch(userType){
	case 1: {
		document.getElementById('userType1').checked=true;
		document.getElementById('userType2').checked=false;
		document.getElementById('userType3').checked=false;
	}
	case 2:{
		document.getElementById('userType1').checked=false;
		document.getElementById('userType2').checked=true;
		document.getElementById('userType3').checked=false;
	}
	case 3:{
		document.getElementById('userType1').checked=false;
		document.getElementById('userType2').checked=false;
		document.getElementById('userType3').checked=true;
	}
	
}

// 
</script>
<title>LSBC - Create User</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
 * LSBC Financial Application Management System
 * Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
 * File : createFA-application.php
 * Author : Cheng Gibson, Zhou Ao
 * Version : v1.0
 *
 * This file provides the View User functionality of the Application
 */

// Includes common code, starts session, and initializes database connection
include './../backend/phpFunctions.php';
session_start();
initializeDB();

// Checks if user is logged in
if(!isLoggedIn()){
	// Redirects users to login page
	header("refresh:0.0001;url=./login.php");
	return;
}
// Checks if user has CreateUser permission
if(!$_SESSION['canViewUser']){
	header("refresh:0.001;url=./home.php");
	return;
}

// Displays Users
function displayUsers(){
	$userID=null;
	
	// Creates SQL statement
	$command_info = "SELECT systemuser.userID, systemuser.userName, systemuserdetails.firstName, systemuserdetails.lastName
				FROM systemuser INNER JOIN systemuserdetails ON systemuser.userID = systemuserdetails.userID";
// 	$command_perms = "";
	
	$htmlcode = "";

	// Initializes database connection
	initializeDB();

	$result = $_SESSION['connection']->query($command_info);
	if($result->num_rows==0){
		echo "No Users Found.";
		return;
	}

	$htmlcode .= "<table class=\"generatedReport\">";
	$htmlcode .= "<tr><td colspan=\"3\" class=\"rowTitle1\">User</td></tr>";
	
	// Iterates through all users
	while($row = $result->fetch_assoc()){
		$htmlcode .= "<tr><td colspan=\"3\"></td></tr>";

		// Prints out users
		$htmlcode .= buildUserRow($row);
	}
	
	$htmlcode .= "</table>";

	echo $htmlcode;
}

// Displays FADetail information based on row input
function buildUserRow($row){

	$toreturn = "<tr>";
	$toreturn .= "<td class=\"rowTitle2\">User Name</td>";
	$toreturn .= "<td class=\"rowTitle2\">First Name</td>";
	$toreturn .= "<td class=\"rowTitle2\">Last Name</td>";
	$toreturn .= "</tr>";

	$toreturn .= "<td class=\"rowTitle4\">".$row["userName"]."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$row["firstName"]."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$row["lastName"]."</td>&nbsp;";

	$toreturn .= "<td><a href=\"./editUser.php?userID=".$row["userID"]."\">Edit</a></td>&nbsp;";
	$toreturn .= "<td><a href=\"#\" 
		onclick=\"confirmUrlEvent('Confirm Delete?' , './deleteUser.php?userID=".$row["userID"]."');\"
		>Delete</a><br></td>&nbsp;";

	$toreturn .= "</tr>";



	return $toreturn;
}
?>
<div class="mainContainer">
	<div class="contentTop">
    	<div class="navBar">
       		<div class="navBarButton"><a href="./home.php">Home</a></div>
            <div class="navBarVertBar"></div>
            <div class="navBarButton"><a href="./account.php">Account</a></div>
            
            <div class="navBarLogoAndName">
            <a style="color:red" href="#" onclick="confirmUrlEvent('Confirm Logout?','./logout.php');">Logout</a>
            Welcome <?php echo $_SESSION['firstName']." ".$_SESSION['lastName']; ?>.
            </div>
        </div>
    </div>

	<div class="contentMain">
		<?php displayUsers()?>
	</div>
	
	<div class="contentBtm">LSBC Financial Application Management System v1.0 @ 2014</div>
</div>

</body>