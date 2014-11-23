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

	setElementValue('userID',userID);
}

// 
</script>
<title>LSBC - Edit User</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
 * LSBC Financial Application Management System
 * Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
 * File : editUser.php
 * Author : Cheng Gibson, Xu Qianqian
 * Version : v1.0
 *
 * This file provides the Edit User functionality of the Application
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
if(!$_SESSION['canEditUser']){
	header("refresh:0.001;url=./home.php");
	return;
}

//PAGE INPUT variables
$userName = "";
$password = "";
$repassword = "";
$firstName = "";
$lastName = "";
$userType = "";

//Page Permission variables
$userID = 0;
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

function pageLogic(){
	global $userName,$password,$repassword,$firstName,$lastName,$userType;
	global $pageState,$errorMsg;
	global $userID;


	tryAssignInputVars();

	//GET
	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		if(!isset($_GET['userID'])){
			//page does not work without userID
			$errorMsg = "An error has occured. Redirecting...";
			header("refresh:2;url=./home.php");
			return;
		}
		//GET userID is SET

		//get userID, replace illegal characters
		$userID = $_GET['userID'];
		tidyInputs();

		if(!validateUserID($userID)){
			//invalid userID
			$errorMsg = "An error has occured. Redirecting...";
			header("refresh:2;url=./home.php");
			return;
		}

		//userID is valid
		retrieveUserDetails($userID);
		dumpVarsToJS();
		$pageState = 1;
	}
	else
	{
		//check if valid userID
		if(!validateUserID($userID)){
			$errorMsg = "An error has occured. Redirecting...";
			header("refresh:2;url=./home.php");
			return;
		}

		//check if valid inputs
		if(!validateInputs()){
			$errorMsg = "Invalid Inputs!";
			tidyInputs();
			dumpVarsToJS();
			$pageState = 1;
			return;
		}

		if(checkUserNameExists($userName, $userID)){
			$errorMsg = "User Name already exists!";
			tidyInputs();
			dumpVarsToJS();
			$pageState = 1;
			return;
		}
		
		if($password != $repassword){
			$errorMsg = "Invalid Password!";
			tidyInputs();
			dumpVarsToJS();
			$pageState = 1;
			return;
		}

		//update User
		tidyInputs();

		if(updateUser()){
			$errorMsg = "Edition Successful!";
			header("refresh:2;url=./viewUser.php");
		}else{
			$errorMsg = "Edition Failed!";
			tidyInputs();
			dumpVarsToJS();
			$pageState = 1;
			return;
		}
		
	}

}
pageLogic();

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global $userName,$password,$repassword,$firstName,$lastName,$userType;
	global $userID;
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
	if(isset($_POST['userID'])) { $userID= $_POST['userID'];}else {
		$toreturn = false;
	}

	return $toreturn;
}

// Removes illegal characters from user inputs
function tidyInputs(){
	global $firstName,$lastName;

	$firstName = preg_replace('/[^a-zA-Z]/','',$firstName);
	$lastName = preg_replace('/[^a-zA-Z]/','',$lastName);

}

// Validates existence of userID; returns false if fail
function validateUserID($userID){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT userID FROM systemuser WHERE userID=?;");
	$command->bind_param('i',$userID);

	$command->execute();
	$result = $command->get_result();
	if($result->num_rows!=0){
		return true;
	} else {
		return false;
	}
}

// Retrieves User Detail information for userID
function retrieveUserDetails($userID){
	global $userName,$firstName,$lastName;

	initializeDB();
	$sql = "SELECT systemuser.userName, systemuserdetails.firstName, systemuserdetails.lastName
				FROM systemuser INNER JOIN systemuserdetails ON systemuser.userID = systemuserdetails.userID
				WHERE systemuser.userID = ?";
	$command = $_SESSION['connection']->prepare($sql);
	$command->bind_param('i',$userID);

	$command->execute();
	$result = $command->get_result();

	if($result->num_rows>0){
		$arr = $result->fetch_array(MYSQLI_ASSOC);

		$userName = $arr['userName'];
		$firstName = $arr['firstName'];
		$lastName =$arr['lastName'];

	}
	return;
}

// Loads user inputs into Javascript variables
function dumpVarsToJS(){
	global $userName,$password,$repassword,$firstName,$lastName,$userType;
	global $userID;

	$jscommand = "<script type=\"text/javascript\">";
	$jscommand .= "userName='".$userName."';";
	$jscommand .= "password='".$password."';";
	$jscommand .= "repassword='".$repassword."';";
	$jscommand .= "firstName='".$firstName."';";
	$jscommand .= "lastName='".$lastName."';";
	$jscommand .= "userType='".$userType."';";
	
	$jscommand .= "userID='".$userID."';";
	
	$jscommand .= "</script>";
	echo $jscommand;
}

// Checks input formats to ensure that they are valid
function validateInputs(){
	global $userName,$password,$repassword,$firstName,$lastName,$userType;
	if(!matchRegex($userName,'/^[a-zA-Z0-9]*$/')) { return false; }
	if(!matchRegex($password,'/^[a-zA-Z0-9 ]*$/')) { return false; }
	if(!matchRegex($repassword,'/^[a-zA-Z0-9 ]*$/')) { return false; }
	if(!matchRegex($firstName,'/^[a-zA-Z0-9 ]*$/')) { return false; }
	if(!matchRegex($lastName,'/^[a-zA-Z0-9 ]*$/')) { return false; }
	if(!matchRegex($userType,'/^[123]*$/')) { return false; }

	return true;
}

// Check if userName exists in systemuser; returns false if fail
function checkUserNameExists($userName,$userID){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT userName FROM systemuser WHERE userName=? AND userID!=?;");
	$command->bind_param('si',$userName,$userID);

	$command->execute();
	$result = $command->get_result();
	if($result->num_rows>0){
		return true;
	} else {
		return false;
	}
}

// Updates User record
function updateUser(){
	global $userName,$password,$repassword,$firstName,$lastName,$userType;
	global $isSuperuser, $canViewUser, $canEditUser, $canCreateUser, $canDeleteUser, $canViewFA, $canSearchFA, $canCreateFA, $canEditFA, $canDeleteFA, $canGenerateReport, $canIssueDisbursement;
	global $userID;

	// Generate password hash
	$passwordHash = sha1($password);

	//Update systemuser
	$command1 = $_SESSION ['connection']->prepare ( "UPDATE systemuser SET userName = ?, password = ? WHERE userID = ?" );
	$command1->bind_param ( 'ssi', $userName, $passwordHash, $userID );
	$iscreate1 = $command1->execute ();

	//Update to systemuserdetails
	$command2 = $_SESSION ['connection']->prepare ( "UPDATE systemuserdetails SET firstName=?, lastName=? WHERE userID=?" );
	$command2->bind_param ( 'ssi', $firstName, $lastName, $userID );
	$iscreate2 = $command2->execute ();

	//Update to systemuserperms
	if (setUserperms()) {
		$command3 = $_SESSION ['connection']->prepare ( "UPDATE systemuserperms SET isSuperuser=?, canViewUser=?,
				 canCreateUser=?, canEditUser=?, canDeleteUser=?, canViewFA=?, canCreateFA=?, canEditFA=?,
				 canDeleteFA=?, canSearchFA=?, canGenerateReport=?, canIssueDisbursement=? WHERE userID=?" );
		$command3->bind_param ( 'iiiiiiiiiiiii', $isSuperuser, $canViewUser, $canCreateUser, $canEditUser,
				 $canDeleteUser, $canViewFA, $canCreateFA, $canEditFA, $canDeleteFA, $canSearchFA, $canGenerateReport,
				 $canIssueDisbursement, $userID );
		$iscreate3 = $command3->execute ();
	}


	if ($iscreate1 && $iscreate2 && $iscreate3) {
		return true;
	} else {
		return false;
	}

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
			$canCreatFA = 1;
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
    	<div class="pageTitle">Edit System User</div>
		<div class="linkButton"><a href="./viewUser.php">Back to View User</a></div>
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
echo "<script type=\"text/javascript\">setFormValues();</script>";

?>

</body>
</html>
