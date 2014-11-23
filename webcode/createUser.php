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
	
	// Assigns the userType to the correct radio button
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
	default :{
		// default case
		document.getElementById('userType1').checked=false;
		document.getElementById('userType2').checked=false;
		document.getElementById('userType3').checked=true;
		break;
	}
	}
}
</script>
<title>LSBC - Create User</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>
<?php
/*
 * LSBC Financial Application Management System
 * Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
 * File : createFA-application.php
 * Author : Cheng Gibson, Xu Qianqian
 * Version : v1.0
 *
 * This file provides the Create User functionality of the Application
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
if(!$_SESSION['canCreateUser']){
	header("refresh:0.001;url=./home.php");
	return;
}

$pageState = 0;
$errorMsg = "";

//PAGE INPUT variables
$userName = "";
$password = "";
$repassword = "";
$firstName = "";
$lastName = "";
$userType = "";

//Page Permission variables
$usertype = null;
$isSuperuser = 0;
$canViewUser = 0;
$canCreateUser = 0;
$canEditUser = 0;
$canDeleteUser = 0;

$canViewFA = 0;
$canCreateFA = 0;
$canEditFA = 0;
$canDeleteFA = 0;
$canSearchFA = 0;

$canGenerateReport = 0;
$canIssueDisbursement = 0;

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	global $userName,$password,$repassword,$firstName,$lastName,$userType;
	global $pageState,$errorMsg;
	
	tryAssignInputVars();
	$pageState = getPageState();
	//PAGE STATE
	//1: Existing userName Exists
	//2: Create Entry, Invalid Password (password != repassword)
	//3: Create Entry, Invalid Data
	//4: Create Entry, Valid Data
	 
	if($pageState==0.5){
		return;
	} else if($pageState==1){
		dumpVarsToJS();
		$errorMsg = "Existing User Name Exists!";
		return;
	} else if($pageState==2) {
		dumpVarsToJS();
		$errorMsg = "Passwords do not match!";
		return;
	} else if($pageState==3) {
		dumpVarsToJS();
		$errorMsg = "Invalid Data!";
		return;
	} else if($pageState==4) {
		//dumpVarsToJS();
		//Create Entry
		if(createEntryUser()){
			$errorMsg = "Creation Successful!";
		}else{
			$errorMsg = "Creation Failed!";
		}
		
	}
}
pageLogic();

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global $userName,$password,$repassword,$firstName,$lastName,$userType;
	$toreturn = true;

	if(isset($_POST['userName'])) { $userName =  $_POST['userName']; }else {
		$toreturn = false;
	}
	if(isset($_POST['password'])) { $password =  $_POST['password']; }else {
		$toreturn = false;
	}
	if(isset($_POST['repassword'])) { $repassword  = $_POST['repassword']; }else {
		$toreturn = false;
	}
	if(isset($_POST['firstName'])) {$firstName = $_POST['firstName']; }else {
		$toreturn = false;
	}
	if(isset($_POST['lastName'])) { $lastName =$_POST['lastName'];}else {
		$toreturn = false;
	}
	if(isset($_POST['userType'])) { $userType= $_POST['userType'];}else {
		$toreturn = false;
	}

	return $toreturn;
}

function getPageState(){
	//1: Existing userName Exists
	//2: Create Entry, Invalid Password (password != repassword)
	//3: Create Entry, Invalid Data
	//4: Create Entry, Valid Data

	global $userName,$password,$repassword,$firstName,$lastName,$userType;
	
	//check if just entered page
	if($_SERVER['REQUEST_METHOD'] != 'POST'){
		return 0.5;
	}

	//Check existing entry
	if($userName!=""){
		//Validate username (ensure no existing user name)
		$userID = tryGetUserIDfromUserName($userName);
		if($userID!=-1){
			return 1;
		} else {
			//attempt create entry
		}
	}
	
	//Check Password
	if($password != $repassword){
		return 2;
	}
	
	tidyInputs();
	
	if($userName=="" || $password=="" ||$repassword=="" ||$firstName=="" ||$lastName=="" ||$userType==""){
		//essential fields are empty
		return 3;
	}
	
	
	//Valid Inputs
	return 4;
}
// Loads user inputs into Javascript variables
function dumpVarsToJS(){
	global $userName,$password,$repassword,$firstName,$lastName,$userType;

	$jscommand = "<script type=\"text/javascript\">";
	$jscommand .= "userName='".$userName."';";
	$jscommand .= "password='".$password."';";
	$jscommand .= "repassword='".$repassword."';";
	$jscommand .= "firstName='".$firstName."';";
	$jscommand .= "lastName='".$lastName."';";
	$jscommand .= "userType='".$userType."';";
	$jscommand .= "</script>";
	echo $jscommand;
}

// Removes illegal characters from user inputs
function tidyInputs(){
	global $firstName,$lastName;
	
	$firstName = preg_replace('/[^a-zA-Z]/','',$firstName);
	$lastName = preg_replace('/[^a-zA-Z]/','',$lastName);

	if(isset($_GET['userID'])) {
		$_GET['userID'] = preg_replace('/[^0-9]/','',$_GET['userID']);
	}
}

// Attempts to retrieve userID from userName; returns -1 if fail
function tryGetUserIDfromUserName($userName){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT userID,userName FROM systemuser WHERE userName=?;");
	$command->bind_param('s',$userName);

	$command->execute();
	$result = $command->get_result();
	if($result->num_rows>0){
		$arr = $result->fetch_array(MYSQLI_NUM);
		//echo "faID:".$arr[0];
		return $arr[0];
	} else {
		return -1;
	}
}

// Creates User record
function createEntryUser(){
	global $userName,$password,$repassword,$firstName,$lastName,$userType;
	global $isSuperuser, $canViewUser, $canEditUser, $canCreateUser, $canDeleteUser, $canViewFA, $canSearchFA, $canCreateFA, $canEditFA, $canDeleteFA, $canGenerateReport, $canIssueDisbursement;
	
	// Generate password hash
	$passwordHash = sha1($password);
	
	//Insert data to systemuser
	$command1 = $_SESSION ['connection']->prepare ( "INSERT INTO systemuser VALUES(NULL,?,?)" );
	$command1->bind_param ( 'ss',$userName, $passwordHash);
	$iscreate1 = $command1->execute ();
	
	//gets userID 
	$userID = tryGetUserIDfromUserName($userName);
	
	//Insert data to systemuserdetails
	$command2 = $_SESSION ['connection']->prepare ( "INSERT INTO systemuserdetails VALUES(?,?,?)" );
	$command2->bind_param ( 'iss', $userID, $firstName, $lastName );
	$iscreate2 = $command2->execute ();
	
	//
	if (setUserperms()) {
		$command3 = $_SESSION ['connection']->prepare ( "INSERT INTO systemuserperms VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)" );
		$command3->bind_param ( 'iiiiiiiiiiiii', $userID, $isSuperuser, $canViewUser, $canCreateUser, $canEditUser, $canDeleteUser, $canViewFA, $canCreateFA, $canEditFA, $canDeleteFA, $canSearchFA, $canGenerateReport, $canIssueDisbursement );
		$iscreate3 = $command3->execute ();
	}
	
	
	if ($iscreate1 && $iscreate2 && $iscreate3) {
		return true;
	} else {
		return false;
	}

}

// Generate userID
function generateUserID() {
	$command = $_SESSION ['connection']->query ( "SELECT userID FROM systemuser ORDER BY userID DESC" );
	$array = $command->fetch_array ( MYSQLI_NUM );
	return (max ( $array ) + 1);
}

// Set User Permissions
function setUserperms() {
	$state = false;
	global $userType;
	global $isSuperuser, $canViewUser, $canEditUser, $canCreateUser, $canDeleteUser, $canViewFA, $canSearchFA, $canCreateFA, $canEditFA, $canDeleteFA, $canGenerateReport, $canIssueDisbursement;

	switch ($userType) {
		case 1 : {//Super User
			$isSuperuser = 1;
			$canViewUser = 1;
			$canEditUser = 1;
			$canCreateUser = 1;
			$canDeleteUser = 1;
			$canViewFA = 1;
			$canSearchFA = 1;
			$canCreateFA = 1;
			$canEditFA = 1;
			$canDeleteFA = 1;
			$canGenerateReport = 1;
			$canIssueDisbursement = 1;
				
			$state = true;
			break;
		}
		case 2 : {//Administrator
			$isSuperuser = 0;
			$canViewUser = 0;
			$canEditUser = 0;
			$canCreateUser = 0;
			$canDeleteUser = 0;
			$canViewFA = 1;
			$canSearchFA = 1;
			$canCreateFA = 1;
			$canEditFA = 1;
			$canDeleteFA = 1;
			$canGenerateReport = 1;
			$canIssueDisbursement = 1;
				
			$state = true;
			break;
		}
		case 3 : {//System User
			$isSuperuser = 0;
			$canViewUser = 0;
			$canEditUser = 0;
			$canCreateUser = 0;
			$canDeleteUser = 0;
			$canViewFA = 0;
			$canSearchFA = 1;
			$canCreateFA = 1;
			$canEditFA = 0;
			$canDeleteFA = 0;
			$canGenerateReport = 0;
			$canIssueDisbursement = 0;
				
			$state = true;
			break;
		}
		default : 
	}
	return $state;
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
    	<div class="pageTitle">Create System User</div>
        
        <div class="linkButton"><a href="./account.php">Back to Account</a></div>
        <br /><br /><br />
        
        <div>
			<form method='POST'><table class="formTemplate">
            <tr><td colspan="2" class="rowTitle2">User</td></tr>
            <tr><td class="rowTitle3">
            User Name*
            </td><td class="rowTitle3">
            <input type="text" name="userName" id="userName" width="" size="" value="" pattern="[a-zA-Z0-9]+" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Password*
            </td><td class="rowTitle3">
            <input type="password" name="password" id="password" width="" size="" value="" pattern="[a-zA-Z0-9]+" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Password Again*
            </td><td class="rowTitle3">
            <input type="password" name="repassword" id="repassword" width="" size="" value="" pattern="[a-zA-Z0-9]+" required/>
            </td></tr>
            
            <tr><td colspan="2" class="rowTitle2"></td></tr>
            
            <tr><td colspan="2" class="rowTitle2">User Details</td></tr>
        	<input type="number" name="userID" id="userID" width="" size="" value="" hidden/>
            
        	<tr><td class="rowTitle3">
            First Name*
            </td><td class="rowTitle3">
            <input type="text" name="firstName" id="firstName" width="" size="" value="" pattern="[a-zA-Z]+" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Last Name*
            </td><td class="rowTitle3">
            <input type="text" name="lastName" id="lastName" width="" size="" value="" pattern="[a-zA-Z]+" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            User Type*
            </td><td class="rowTitle3">
            Super User<input type="radio" name="userType" id="userType1" width="" size="" value="1"/><br />
            Administrator<input type="radio" name="userType" id="userType2" width="" size="" value="2"/><br />
            System User<input type="radio" name="userType" id="userType3" width="" size="" value="3"/>
            </td></tr>
            
            <tr><td colspan="2" class="rowTitle3">
            <input type="submit" />
            </td></tr>
            
        </table></form>
        
        <div class="errorMsg">
            	<?php echo $errorMsg; ?>
            </div>   
    	</div>
         
    	<div>
    	
		<div class="contentBtm">LSBC Financial Application Management System v1.0 @ 2014</div>
	
</div>

<?php
//PAGE STATE
//1: Existing userName Exists
//2: Create Entry, Invalid Password (password != repassword)
//3: Create Entry, Invalid Data
//4: Create Entry, Valid Data
if($pageState!=0.5 && $pageState!=4){
echo "<script type=\"text/javascript\">setFormValues();</script>";
}
?>

</body>
</html>
