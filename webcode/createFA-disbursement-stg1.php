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
}
// Enables/Disables form inputs depending on page state
function switchMode($mode){
	if($mode){
		//document.getElementById('nric').disabled=true;
	} else {
		document.getElementById('faApplicationID').disabled=true;
		document.getElementById('dateDisbursed').disabled=true;
		document.getElementById('type').disabled=true;
		document.getElementById('amount').disabled=true;
		document.getElementById('paymentSchdNo').disabled=true;
		document.getElementById('issueIncharge').disabled=true;
		document.getElementById('issueApprover').disabled=true;
		document.getElementById('description').disabled=true;
	}
}
</script>
<title>LSBC - Create FA - Disbursement</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : createFA-disbursement-stg1.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Create FA - Disbursement stg1 functionality of the Application
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

// Page variables
$pageState = 0;
$errorMsg = "";
$faApplicationsHTML = "";

$switchMode = 0;
//SWITCHMODE
//0: enable NRIC 
//1: disable NRIC

//PAGE INPUT variables
$nric = "";

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	global $nric;
	global $switchMode,$faApplicationsHTML;
	global $pageState,$errorMsg;
	
	tryAssignInputVars();
	
	$switchMode = 0;
	
	$faApplicationsHTML = "<option value=\"". "" ."\">" . " - " . "</option>";
	
	if(isset($_GET['errCode'])){
		$errCode = $_GET['errCode'];
		if($errCode==1){
			$errorMsg = "Invalid NRIC";	
		}
		if($errCode==2){
			$errorMsg = "NRIC has No Applications";	
		}
	}
	
	dumpVarsToJS();
}
pageLogic();

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global $nric;
	$toreturn = true;
	
	return $toreturn;
}
// Determines page state based on user inputs
function getPageState(){
	global $nric;
	
	//PAGE STATE
	//Unused Method
}
// Removes illegal characters from user inputs
function tidyInputs(){
	global $nric;
	
	$nric = preg_replace('/[^a-zA-Z0-9]/','',$nric);
}
// Loads user inputs into Javascript variables
function dumpVarsToJS(){
	global $nric;
	
	$jscommand = "<script type=\"text/javascript\">";
	$jscommand .= "nric='".$nric."';";
	$jscommand .= "</script>";
	echo $jscommand;
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
    	<div class="pageTitle">Create Financial Application - Disbursement</div>
        
        <div>
        	<form method='POST' action="./createFA-disbursement-stg2.php"><table class="formTemplate">
            <input type="text" name="fromExt" id="fromExt" width="" size="" value="" autofocus hidden/>
            <tr><td colspan="2" class="rowTitle2">Details</td></tr>
            <tr><td class="rowTitle3">
            NRIC*
            </td><td class="rowTitle3">
            <input type="text" name="nric" id="nric" width="" size="" value="" pattern="[Ss]{1}[0-9]{7}[a-iA-IzZjJ]{1}" required/>
            </td></tr>
            
            <tr><td colspan="2" class="rowTitle2"></td></tr>
            
            <tr><td colspan="2" class="rowTitle2">Application</td></tr>
            <tr><td class="rowTitle3">
            FA Application
            </td><td class="rowTitle3">
            <select name="faApplicationID" id="faApplicationID">
            <?php echo $faApplicationsHTML; ?>
            </select>
            </td></tr>
            
            <tr><td colspan="2" class="rowTitle2"></td></tr>
            
            <tr><td colspan="2" class="rowTitle2">Disbursement Details</td></tr>
            
            <tr><td class="rowTitle3">
            Date Disbursed*
            </td><td class="rowTitle3">
            <input type="date" name="dateDisbursed" id="dateDisbursed" width="" size="" />
            </td></tr>
            
            <tr><td class="rowTitle3">
            Type*
            </td><td class="rowTitle3">
            <input type="text" name="type" id="type" width="" />
            </td></tr>
            
            <tr><td class="rowTitle3">
            Amount*
            </td><td class="rowTitle3">
            <input type="text" name="amount" id="amount" width="" size="" />
            </td></tr>
            
            <tr><td class="rowTitle3">
            Payment Schedule Number*
            </td><td class="rowTitle3">
            <input type="number" name="paymentSchdNo" id="paymentSchdNo" width="" size="" min="1"/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Issuer In-Charge*
            </td><td class="rowTitle3">
            <input type="text" name="issueIncharge" id="issueIncharge" width="" size="" />
            </td></tr>
            
            <tr><td class="rowTitle3">
            Issuer Approver*
            </td><td class="rowTitle3">
            <input type="text" name="issueApprover" id="issueApprover" width="" size="" />
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
echo "<script type=\"text/javascript\">setFormValues();</script>";
if($switchMode==0){
	echo "<script type=\"text/javascript\">switchMode(false);</script>";
} else {
	echo "<script type=\"text/javascript\">switchMode(true);</script>";
}

?>

</body>
</html>