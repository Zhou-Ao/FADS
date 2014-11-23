<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="./../backend/cssstyle.css">
<script type="text/javascript" src="./../backend/jsfunctions.js"></script>
<title>LSBC - Login</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : login.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Login functionality of the Application
*/

// Includes common code, starts session, and initializes database conenction
include './../backend/phpFunctions.php';
session_start();
initializeDB();

// Stores error messages to be displayed to users
$errorMsg = "";

// Checks if user is logged in
if(isLoggedIn()){
	// Redirects users to home page
	header("refresh:0.0001;url=./home.php");	
	return;
}

//pageLogic runs the logical functionalities of the page
function pageLogic(){
global $errorMsg;

// Checks if POSTed into this page
if($_SERVER['REQUEST_METHOD'] == 'POST'){
		
		// Destroys old session and creates a new session
		session_destroy();
		session_start();
		
		// local variables to store name and password
		$username ="";
		$password="";
		
		// Assigns variables from the submitted form
		if(isset($_POST['userName'])){ $username = $_POST['userName'];   }
		if(isset($_POST['password'])){ $password = $_POST['password'];   }
		
		// Checks for invalid inputs
		if(!matchRegex($username,"/^[a-zA-Z0-9 ]+$/") || !matchRegex($password,"/^[a-zA-Z0-9 ]+$/")){
			$errorMsg = "Invalid Login ID / Password.";
			return;
		}
		
		// Generates password hash for login, uses SHA1
		$passwordHash = sha1($password);
		
		
		// Tries to login
		$userID = tryLogin($username,$passwordHash);
		
		// Checks if userID is not -1; -1 means user was not logged in
		if($userID!=-1){
			// Sets session variable 'userID' as the userID
			$_SESSION['userID']=$userID;
			
			// Loads user permissions into session variables
			setUserPerms($userID);
			
			//Loads user details (ie. first name, last name) into session variables
			setUserDetails($userID);
			
			// Notifies and Redirects users to the Home Page 
			header("refresh:1; url=./home.php");
			$errorMsg = "Redirecting...";
		}
		else {
			//User was not logged in
			$errorMsg = "Invalid Login ID / Password.";
		}
	
	}
}
pageLogic();


// Tries to login user given username and passwordHash
// Returns userID if successful login, else -1
function tryLogin($username,$passwordHash){
	//if return -1 = not a valid login
	$userID = -1;
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT userID FROM SystemUser WHERE userName=? AND password=?;");
	$command->bind_param('ss',$username,$passwordHash);
	
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows>0){
		$arr = $result->fetch_array(MYSQLI_NUM);
		$userID = $arr[0];	
	} 
	
	return $userID;
}

// Assigns user permissions to session variables storing user permissions; based on input user ID
function setUserPerms($userID){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT * FROM SystemUserPerms WHERE userID=?;");
	$command->bind_param('i',$userID);
	
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows>0){
		$arr = $result->fetch_array(MYSQLI_NUM);
		$_SESSION['isSuperuser']=$arr[1];
		$_SESSION['canViewUser']=$arr[2];
		$_SESSION['canCreateUser']=$arr[3];
		$_SESSION['canEditUser']=$arr[4];
		$_SESSION['canDeleteUser']=$arr[5];
		$_SESSION['canViewFA']=$arr[6];
		$_SESSION['canCreateFA']=$arr[7];
		$_SESSION['canEditFA']=$arr[8];
		$_SESSION['canDeleteFA']=$arr[9];
		$_SESSION['canSearchFA']=$arr[10];
		$_SESSION['canGenerateReport']=$arr[11];
		$_SESSION['canIssueDisbursement']=$arr[12];
	} 
}

// Assigns user details to session variables storing user permissions; based on input user ID
function setUserDetails($userID){
initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT * FROM SystemUserDetails WHERE userID=?;");
	$command->bind_param('i',$userID);
	
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows>0){
		$arr = $result->fetch_array(MYSQLI_NUM);
		
		$_SESSION['firstName']=$arr[1];
		$_SESSION['lastName']=$arr[2];
	} 
}	
?>

<div class="mainContainer">
	<div class="contentTop">
    	<div class="navBar">
        <div class="navBarButton">&nbsp;</div>
        
        </div>
    </div>
    
    <div class="contentMain">
    	<div class="pageTitle">Login</div>
        <div>
            <form method='POST'><table class="formTemplate">
                <tr><td colspan="2" class="rowTitle1">Login</td></tr>
                
                <tr><td class="rowTitle3">
                User Name
                </td><td class="rowTitle3">
                <input type="text" name="userName" id="userName" width="" size=""/>
                </td></tr>
                
                <tr><td class="rowTitle3">
                Password
                </td><td class="rowTitle3">
                <input type="password" name="password" id="password" width="" size=""/>
                </td></tr>
                
                <tr><td class="rowTitle3">
                </td><td class="rowTitle3">
                <input type="submit" />
                </td></tr>
                
            </table></form>

		</div>
        <div class="errorMsg">
            	<?php echo $errorMsg; ?>
            </div>   
            
	</div>
    
    <div class="contentBtm">LSBC Financial Application Management System v1.0 @ 2014</div>
</div>	

</body>
</html>

