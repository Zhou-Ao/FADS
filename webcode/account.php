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
		break;
	}
	case 2:{
		document.getElementById('userType1').checked=false;
		document.getElementById('userType2').checked=true;
		document.getElementById('userType3').checked=false;
		break;
	}
	case 3:{
		document.getElementById('userType1').checked=false;
		document.getElementById('userType2').checked=false;
		document.getElementById('userType3').checked=true;
		break;
	}
	}

// 	setElementValue('userID',userID);
}

// 
</script>
<title>LSBC - Account Information</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
 * LSBC Financial Application Management System
 * Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
 * File : account.php
 * Author : Cheng Gibson, Xu Qianqian
 * Version : v1.0
 *
 * This file provides the Account functionality of the Application
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
    	<div class="pageTitle">Account Information</div>

        <div style="float:left;margin-right:2%;">
			<form><table class="formTemplate">
            <tr><td colspan="2" class="rowTitle2">User</td></tr>
            <tr><td class="rowTitle3">
            User Name
            </td><td class="rowTitle3">
            <?php 
            $command = $_SESSION['connection']->prepare("SELECT userName FROM systemuser WHERE userID=?;");
            $command->bind_param('s',$_SESSION['userID']);
            
            $command->execute();
            $result = $command->get_result();
            if($result->num_rows>0){
            	$arr = $result->fetch_array(MYSQLI_NUM);
            }
            echo $arr[0];
            ?>
            </td></tr>
            
            <tr><td colspan="2" class="rowTitle2"></td></tr>
            
            <tr><td colspan="2" class="rowTitle2">User Details</td></tr>
        	<input type="number" name="userID" id="userID" width="" size="" value="" hidden/>
            
        	<tr><td class="rowTitle3">
            First Name
            </td><td class="rowTitle3">
           	<?php echo $_SESSION['firstName']?>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Last Name
            </td><td class="rowTitle3">
            <?php echo $_SESSION['lastName']?>
            </td></tr>
            
            <tr><td colspan="2" class="rowTitle3">
            <?php echo "<a href=\"./editAccount.php?userID=".$_SESSION["userID"]."\">Edit</a>"?>
            </td></tr>
            
        </table></form>
        </div>
        
        <div style="">
        	<?php
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

<?php
//PAGE STATE
//1: Existing userName Exists
//2: Create Entry, Invalid Password (password != repassword)
//3: Create Entry, Invalid Data
//4: Create Entry, Valid Data
echo "<script type=\"text/javascript\">setFormValues();</script>";

?>

</body>
</html>