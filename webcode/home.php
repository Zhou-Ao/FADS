<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="./../backend/cssstyle.css">
<script type="text/javascript" src="./../backend/jsfunctions.js"></script>
<title>LSBC - Home</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : home.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file is the Home Page of the Application
*/

// Includes common code, starts session, and initializes database conenction
include './../backend/phpFunctions.php';
session_start();
initializeDB();

// Checks if user is logged in
if(!isLoggedIn()){
	// Redirects users to login page
	header("refresh:0.0001;url=./login.php");	
	return;
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
    	<div class="pageTitle">Home</div>
        <br />
        <div>
        	<?php
			// Displays button only if user has SearchFA permissions
			if($_SESSION['canSearchFA']){
				echo "<div class=\"linkButton\"><a href=\"./searchFA.php\">Search/Edit/Delete FA</a></div>";	
				
			}
			// Displays button only if user has GenerateReport permissions
			if($_SESSION['canGenerateReport']){
				echo "<div class=\"linkButton\"><a href=\"./GenerateReport.php\">Generate Report</a></div>";	
				
			}
			
			echo "<br /><br /><br />";
			
			// Displays button only if user has CreateFA permissions
			if($_SESSION['canCreateFA']){
				echo "<div class=\"linkButton\"><a href=\"./createFA-details.php\">Create FA - Details</a></div>";	
				echo "<div class=\"linkButton\"><a href=\"./createFA-application.php\">Create FA - Application</a></div>";		
			}
			// Displays button only if user has SearchFA permissions
			if($_SESSION['canCreateFA'] || $_SESSION['canIssueDisbursement'] ){
				echo "<div class=\"linkButton\"><a href=\"./createFA-disbursement-stg1.php\">Create FA - Disbursement</a></div>";	
			}
			
			echo "<br /><br /><br />";
			
			// Displays button only if user has createUser permission
			if($_SESSION['canCreateUser']){
				echo "<div class=\"linkButton\"><a href=\"./createUser.php\">Create User</a></div>";
			}
			
			// Displays button only if user has viewUser permission
			if($_SESSION['canCreateUser']){
				echo "<div class=\"linkButton\"><a href=\"./viewUser.php\">View/Edit/Delete User</a></div>";
			}
			?>
        	
        </div>
        	
    </div>

	<div class="contentBtm">LSBC Financial Application Management System v1.0 @ 2014</div>
</div>

</body>
</html>