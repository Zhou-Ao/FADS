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
}
</script>
<title>LSBC - Create Financial Application - Details</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : createFA-details.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Create FA - Details functionality of the Application
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
// Checks if user has CreateFA permission
if(!$_SESSION['canCreateFA']){
	header("refresh:0.001;url=./home.php");
	return;
}

// Page Variables
$pageState = 0;
$errorMsg = "";

// PAGE INPUT variables
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

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;
	global $pageState,$errorMsg;
	
	tryAssignInputVars();
	$pageState = getPageState();
	//PAGE STATE
	//1: Existing Entry Exists
	//2: Create Entry, Invalid Data
	//2.5: Create Entry, Invalid Data (Illegal Characters)
	//3: Create Entry, Valid Data
	if($pageState==0.5){
		return;
	} else if($pageState==1){
		dumpVarsToJS();
		$errorMsg = "Existing NRIC Exists!";
		return;
	} else if($pageState==2.5) {
		dumpVarsToJS();
		$errorMsg = "Illegal Characters Found!";
		return;
	} else if($pageState==2.7) {
		dumpVarsToJS();
		$errorMsg = "Invalid NRIC!";
		return;
	}else if($pageState==2) {
		dumpVarsToJS();
		$errorMsg = "Missing Fields!";
		return;
	} else if($pageState==3) {
		//dumpVarsToJS();
		//Create Entry
		createEntryFADetails();
		$faID = 0;
		$faID = tryGetFAIDfromNRIC($nric);
		$errorMsg = "Creation Successful!";
	}
}
pageLogic();

function tryAssignInputVars(){
	//assigns input variables from the form into local variables
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;
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
	
	return $toreturn;
}
// Determines page state based on user inputs
function getPageState(){
	//PAGE STATE
	//1: Existing Entry Exists
	//2: Create Entry, Invalid Data
	//3: Create Entry, Valid Data
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;
	
	$toreturn = 0;
	
	//check if just entered page
	if($_SERVER['REQUEST_METHOD'] === 'GET'){
		return 0.5;	
	}
	
	//Check existing entry
	if($nric!=""){
		//Validate NRIC
		$faID = tryGetFAIDfromNRIC($nric);
		if($faID!=-1){
			return 1;
		} else {
			//attempt create entry
		}
	}
	
	tidyInputs();
	
	if($nric=="" || $firstName=="" ||$lastName=="" ||$dob=="" ||$address1=="" ||$poCode=="" || $homeNum=="" ||$handphoneNum==""){
		//essential fields are empty
		return 2;
	}
	
	if(!checkValidNRIC($nric)){
		return 2.7;	
	}
	
	//Valid Inputs
	return 3;
}

function tidyInputs(){
	//uses regular expressions to prevent injections
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;

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
}
// Checks input formats to ensure that they are valid
function validateInputs(){
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;

	//TOCODE 
}
// Loads user inputs into Javascript variables
function dumpVarsToJS(){
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;

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
	$jscommand .= "</script>";
	echo $jscommand;
}

// Attempts to retrieve faID from NRIC; returns -1 if fail
function tryGetFAIDfromNRIC($nric){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT faID,nric FROM FADetails WHERE nric=?;");
	$command->bind_param('s',$nric);
	
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
// Creates FADetails record
function createEntryFADetails(){
	global $nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,
$homeNum,$handphoneNum,$email,$description;
	$imgLoc = '';
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("INSERT INTO FADetails VALUES(NULL,?,?,?,?,?,?,?,?,?,?,?,?);");
	$command->bind_param('ssssssiiisss',$nric,$firstName,$lastName,$dob,$address1,$address2,$poCode,$homeNum,$handphoneNum,$email,$description,$imgLoc);
	
	$command->execute();
	
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
    	<div class="pageTitle">Create Financial Application - Details</div>
        <div>
        	<form method='POST'><table class="formTemplate">
            
            <tr><td colspan="2" class="rowTitle2">Personal Details</td></tr>
            
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
            Home Number*
            </td><td class="rowTitle3">
            <input type="number" name="homeNum" id="homeNum" width="" size="" maxlength="8" value="" pattern="[0-9]{6}" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Handphone Number*
            </td><td class="rowTitle3">
            <input type="number" name="handphoneNum" id="handphoneNum" width="" size="" maxlength="8" value="" pattern="[0-9]{8}" required/>
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
//PAGE STATE
//1: Existing Entry Exists
//2: Create Entry, Invalid Data
//3: Create Entry, Valid Data
if($pageState>=1 && $pageState<=2.7){
	echo "<script type=\"text/javascript\">setFormValues();</script>";
	return;
}
?>
</body>
</html>