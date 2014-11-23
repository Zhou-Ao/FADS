<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="./../backend/cssstyle.css">
<script type="text/javascript" src="./../backend/jsfunctions.js"></script>
<script type="text/javascript">
// Applies page variables into form inputs
function setFormValues(){
	setElementValue('nric',nric);
	setElementValue('firstName',firstName);
	setElementValue('lastName',lastName);
	setElementValue('dob',dob);
	setElementValue('address1',address1);
	setElementValue('address2',address2);
	setElementValue('poCode',poCode);
	setElementValue('homeNum',homeNum);
	setElementValue('handphoneNum',handphoneNum);
	setElementValue('email',email);
	setElementValue('description',description);
	
	setElementValue('faID',faID);
}
</script>
<title>LSBC - Edit Financial Application - Details</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : editFA-details.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Edit FA - Details functionality of the Application
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
// Checks if user has EditFA permission
if(!$_SESSION['canEditFA']){
	header("refresh:0.001;url=./home.php");
	return;
}

//pageState
//0: don't assign input fields
//1: assign input fields
$pageState = 0;
$errorMsg = "";

//PAGE INPUT variables
$nric = "";
$firstName  = "";
$lastName = "";
$dob = "";
$address1 = "";
$address2 = "";
$poCode = "";
$homeNum = "";
$handphoneNum = "";
$email = "";
$description = "";
$faID = "";

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;
	global $faID;
	global $pageState,$errorMsg;
	
	tryAssignInputVars();
	
	//GET
	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		if(!isset($_GET['faID'])){
			//page does not work without faID
			$errorMsg = "An error has occured. Redirecting...";
			header("refresh:2;url=./home.php");
			return;
		}
		//GET faID is SET
		
		//get faID, replace illegal characters
		$faID = $_GET['faID'];
		tidyInputs();
		
		if(!validateFAID($faID)){
			//invalid faID
			$errorMsg = "An error has occured. Redirecting...";
			header("refresh:2;url=./home.php");
			return;
		}
		
		//faID is valid
		retrieveFADetails($faID);
		dumpVarsToJS();
		$pageState = 1;
	} 
	else
	{
		//check if valid faID
		if(!validateFAID($faID)){
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
		
		if(!checkValidNRIC($nric)){
			$errorMsg = "Invalid NRIC!";
			tidyInputs();
			dumpVarsToJS();
			$pageState = 1;
			return;
		}
		
		if(checkNRICExists($nric,$faID)){
			$errorMsg = "NRIC already exists!";
			tidyInputs();
			dumpVarsToJS();
			$pageState = 1;
			return;
		}
		
		//update faDetail
		tidyInputs();
		dumpVarsToJS();
		$pageState = 1;
		updateFADetails();
		header("refresh:0.0001;url=./viewFA.php?updateSuccess=1&faID=".$faID);
	}
	
}
pageLogic();

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;
	global $faID;
	$toreturn = true;
	
	if(isset($_POST['nric'])) { $nric =  $_POST['nric']; }
	if(isset($_POST['firstName'])) { $firstName  = $_POST['firstName']; }
	if(isset($_POST['lastName'])) {$lastName = $_POST['lastName']; }
	if(isset($_POST['dob'])) { $dob =$_POST['dob'];}
	if(isset($_POST['address1'])) { $address1= $_POST['address1'];}
	if(isset($_POST['address2'])) { $address2 = $_POST['address2']; }
	if(isset($_POST['poCode'])) { $poCode = $_POST['poCode']; }
	if(isset($_POST['homeNum'])) { $homeNum = $_POST['homeNum']; }
	if(isset($_POST['handphoneNum'])) { $handphoneNum = $_POST['handphoneNum']; }
	if(isset($_POST['email'])) {$email = $_POST['email'];  }
	if(isset($_POST['description'])) { $description = $_POST['description']; }
	if(isset($_POST['faID'])) { $faID =  $_POST['faID']; }
	
	return $toreturn;
}
// Removes illegal characters from user inputs
function tidyInputs(){
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;
	global $faID;
	
	$nric = preg_replace('/[^a-zA-Z0-9]/','',$nric);
	$firstName = preg_replace('/[^a-zA-Z0-9 ]/','',$firstName);
	$lastName = preg_replace('/[^a-zA-Z0-9 ]/','',$lastName);
	$dob = preg_replace('/[^0-9-]/','',$dob);
	$address1 = preg_replace('/[^a-zA-Z0-9 -_.,#]/','',$address1);
	$address2 = preg_replace('/[^a-zA-Z0-9 -_.,#]/','',$address2);
	$poCode = preg_replace('/[^0-9]/','',$poCode);
	$homeNum = preg_replace('/[^0-9]/','',$homeNum);
	$handphoneNum = preg_replace('/[^0-9]/','',$handphoneNum);
	$email = preg_replace('/[^a-zA-Z0-9@._-]/','',$email);
	$description = preg_replace('/[^a-zA-Z0-9 -_.,#]/','',$description);
	
	$faID = preg_replace('/[^0-9]/','',$faID);
}
// Loads user inputs into Javascript variables
function dumpVarsToJS(){
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;
	global $faID;
	
	$jscommand = "<script type=\"text/javascript\">";
	$jscommand .= "nric='".$nric."';";
	$jscommand .= "firstName='".$firstName."';";
	$jscommand .= "lastName='".$lastName."';";
	$jscommand .= "dob='".$dob."';";
	$jscommand .= "address1='".$address1."';";
	$jscommand .= "address2='".$address2."';";
	$jscommand .= "poCode='".$poCode."';";
	$jscommand .= "homeNum='".$homeNum."';";
	$jscommand .= "handphoneNum='".$handphoneNum."';";
	$jscommand .= "email='".$email."';";
	$jscommand .= "description='".$description."';";
	
	$jscommand .= "faID='".$faID."';";
	
	$jscommand .= "</script>";
	echo $jscommand;
}

// Retrieves FADetail information for faID
function retrieveFADetails($faID){
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;
	global $faID;
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT * FROM FADetails WHERE faID=?;");
	$command->bind_param('i',$faID);
	
	$command->execute();
	$result = $command->get_result();
	
	if($result->num_rows>0){
		$arr = $result->fetch_array(MYSQLI_ASSOC);
		
		$nric = $arr['nric'];
		$firstName = $arr['firstName'];
		$lastName =$arr['lastName'];
		$dob =$arr['dob'];
		$address1 =$arr['address1'];
		$address2 =$arr['address2'];
		$poCode = $arr['poCode'];
		$homeNum =$arr['homeNum'];
		$handphoneNum =$arr['handphoneNum'];
		$email =$arr['email'];
		$description =$arr['description'];
		
	} 
	return;
}
// Checks input formats to ensure that they are valid
function validateInputs(){
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;
	global $faID;
	if(!matchRegex($nric,'/^[a-zA-Z0-9]*$/')) { return false; }
	if(!matchRegex($firstName,'/^[a-zA-Z0-9 ]*$/')) { return false; }
	if(!matchRegex($lastName,'/^[a-zA-Z0-9 ]*$/')) { return false; }
	if(!matchRegex($dob,'/^[0-9-]*$/')) { return false; }
	if(!matchRegex($address1,'/^[a-zA-Z0-9 -_.,#]*$/')) { return false; }
	if(!matchRegex($address2,'/^[a-zA-Z0-9 -_.,#]*$/')) { return false; }
	if(!matchRegex($poCode,'/^[0-9]*$/')) { return false; }
	if(!matchRegex($homeNum,'/^[0-9]*$/')) { return false; }
	if(!matchRegex($handphoneNum,'/^[0-9]*$/')) { return false; }
	if(!matchRegex($email,'/^[a-zA-Z0-9@._-]*$/')) { return false; }
	if(!matchRegex($description,'/^[a-zA-Z0-9 -_.,#]*$/')) { return false; }
	
	return true;
}
// Validates existence of faID; returns -1 if fail
function validateFAID($faID){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT faID FROM FADetails WHERE faID=?;");
	$command->bind_param('i',$faID);
	
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows!=0){
		return true;	
	} else {
		return false;
	}
}
// Updates FA Details record
function updateFADetails(){
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;
	global $faID;
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("UPDATE FADetails SET nric=?,firstName=?,lastName=?,dob=?,address1=?,address2=?,poCode=?,homeNum=?,handphoneNum=?,email=?,description=? WHERE faID=?;");
	$command->bind_param('ssssssiiissi',$nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,$homeNum,$handphoneNum,$email,$description,$faID);
	
	$command->execute();
}
// Check if NRIC exists in FADetails; returns false if fail
function checkNRICExists($nric,$faID){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT nric FROM FADetails WHERE nric=? AND faID!=?;");
	$command->bind_param('si',$nric,$faID);
	
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows>0){
		return true;	
	} else {
		return false;
	}
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
    	<div class="pageTitle">Edit Financial Application - Details</div>
        <div class="linkButton"><a href="./viewFA.php?faID=<?php echo $faID?>">Back to View Financial Application</a></div>
        <br /><br /><br />
        
        <div>
        	<form method='POST'><table class="formTemplate">
            <tr><td colspan="2" class="rowTitle2">Personal Details</td></tr>
            <input type="text" name="faID" id="faID" width="" size="" value="" hidden/>
            
            <tr><td class="rowTitle3">
            NRIC*
            </td><td class="rowTitle3">
            <input type="text" name="nric" id="nric" width="" size="" value="" pattern="[Ss]{1}[0-9]{7}[a-iA-IzZjJ]{1}" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            First Name*
            </td><td class="rowTitle3">
            <input type="text" name="firstName" id="firstName" width="" value="" pattern="[a-zA-Z ]*"/ required><br />
            </td></tr>
            
            <tr><td class="rowTitle3">
            Last Name*
            </td><td class="rowTitle3">
            <input type="text" name="lastName" id="lastName" width="" value=""  pattern="[a-zA-Z ]*" required ><br />
            </td></tr>
            
            <tr><td class="rowTitle3">
            DOB*
            </td><td class="rowTitle3">
            <input type="date" name="dob" id="dob" width="" size="" value="" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Address 1*
            </td><td class="rowTitle3">
            <textarea rows="4" cols="50" name="address1" id="address1" required="required"></textarea>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Address 2
            </td><td class="rowTitle3">
            <textarea rows="4" cols="50" name="address2" id="address2"></textarea>
            </td></tr>
            
            <tr><td class="rowTitle3">
            PO Code*
            </td><td class="rowTitle3">
            <input type="text" name="poCode" id="poCode" width="" size="" maxlength="6" value="" pattern="[0-9]{6}" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Home Number
            </td><td class="rowTitle3">
            <input type="text" name="homeNum" id="homeNum" width="" size="" maxlength="8" value="" pattern="[0-9]{6}"/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Handphone Number
            </td><td class="rowTitle3">
            <input type="text" name="handphoneNum" id="handphoneNum" width="" size="" maxlength="8" value="" pattern="[0-9]{8}"/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Email
            </td><td class="rowTitle3">
            <input type="email" name="email" id="email" width="" size="" value=""/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Description
            </td><td class="rowTitle3">
            <textarea rows="4" cols="50" name="description" id="description"></textarea>
            </td></tr>
            
			<tr><td colspan="2" class="rowTitle3">
            <input type="submit" />
            </td></tr>

            </table></form>
            
            <div class="errorMsg">
            	<?php echo $errorMsg; ?>
            </div>
        </div>
    </div>
    
    <div class="contentBtm">LSBC Financial Application Management System v1.0 @ 2014</div>
	
</div>	

<?php
echo "<script type=\"text/javascript\">setFormValues();</script>";
?>

</body>
</html>