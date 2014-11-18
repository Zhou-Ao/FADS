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
	if(faApplicationID!=""){
		setElementValue('faApplicationID',faApplicationID);
	}
	setElementValue('dateDisbursed',dateDisbursed);
	setElementValue('type',type);
	setElementValue('amount',amount);
	setElementValue('paymentSchdNo',paymentSchdNo);
	setElementValue('issueIncharge',issueIncharge);
	setElementValue('issueApprover',issueApprover);
	setElementValue('description',description);
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
* File : createFA-disbursement-stg2.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Create FA - Disbursement stg2 functionality of the Application
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
$faApplicationID  = "";
$dateDisbursed = "";
$type = "";
$amount = "";
$paymentSchdNo = "";
$issueIncharge = "";
$issueApprover = "";
$description = "";

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	global $nric,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	global $switchMode,$faApplicationsHTML;
	global $pageState,$errorMsg;
	
	tryAssignInputVars();
	$isInvalidInput = checkInvalidInputs();
	tidyInputs(); 
	
	//validate NRIC
	$faID = tryGetFAIDfromNRIC($nric);
	if($nric=="" || $faID==-1 || !checkValidNRIC($nric)){
		//invalid NRIC
		header("refresh:0.0001;url=./createFA-disbursement-stg1.php?errCode=1");
		return;
	}
	
	//check if nric has applications
	$result = getFAApplications($faID);
	if($result==-1){
		header("refresh:0.0001;url=./createFA-disbursement-stg1.php?errCode=2");
		return;
	} 
	$switchMode = 1;
	$faApplicationsHTML = getFAApplications($faID);
	
	
	//checks if POSTed from another page
	if(isset($_POST['fromExt']) || $_SERVER['REQUEST_METHOD']=="GET"){
		dumpVarsToJS();
		return;
	}
	
	//checks if POSTed from same page
	if(!isset($_POST['fromExt'])){
		//checks for invalid input eg. illegal characters
		if($isInvalidInput){
			dumpVarsToJS();
			$errorMsg = "Invalid Data Found";
			return;
			
		} else 
		//valid input
		//checks for valid faApplicationID
		if(validFAApplicationID($faApplicationID)==-1){
			dumpVarsToJS();
			$errorMsg = "Please Select Application ID";
			return;
		}
		//checks for empty fields
		if($dateDisbursed=="" || $type=="" || $amount=="" || $paymentSchdNo=="" || $issueIncharge=="" || $issueApprover==""){
			$errorMsg = "Missing fields!";
			dumpVarsToJS();
			return;
		}
		
		//checks for semantic errors in date,amt,schdno
		$tempErrMsg = checkValidDateAmtSchdNo();				
		if($tempErrMsg!=""){
			dumpVarsToJS();
			$errorMsg = $tempErrMsg;
			return;
		}
		
		//valid date and amount
		//ready for creation
		createEntryFADisbursement();
		updateFAApplication();
		//header("refresh:2;url=./home.php");
		$errorMsg = "Creation Successful!";
		wipeVars();
		dumpVarsToJS();
	}
	
}
pageLogic();

// Clears page user input variables
function wipeVars(){
	global $nric,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	
	//clear form variables
	$faApplicationID = "";
	$dateDisbursed = "";
	$type = "";
	$amount = "";
	$paymentSchdNo = "";
	$issueIncharge = "";
	$issueApprover = "";
	$description = "";
}

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global $nric,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	$toreturn = true;
	
	if(isset($_POST['nric'])) { $nric =  $_POST['nric']; }
	if(isset($_GET['nric'])) { $nric =  $_GET['nric']; }
	
	if(isset($_POST['faApplicationID'])) { $faApplicationID  = $_POST['faApplicationID']; }
	if(isset($_POST['dateDisbursed'])) {$dateDisbursed = $_POST['dateDisbursed']; }
	if(isset($_POST['type'])) { $type =$_POST['type'];}
	if(isset($_POST['amount'])) { $amount =$_POST['amount'];}
	if(isset($_POST['paymentSchdNo'])) { $paymentSchdNo= $_POST['paymentSchdNo'];}
	if(isset($_POST['issueIncharge'])) { $issueIncharge = $_POST['issueIncharge']; }
	if(isset($_POST['issueApprover'])) { $issueApprover = $_POST['issueApprover']; }
	if(isset($_POST['description'])) { $description = $_POST['description']; }
	
	return $toreturn;
}

// Checks if user input is invalid, returns true if invalid
function checkInvalidInputs(){
	global $nric,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	
	if(!matchRegex($nric,"/^[a-zA-Z0-9]*$/")) { return true; }
	if(!matchRegex($faApplicationID,"/^[0-9]*$/")) { return true; }
	if(!matchRegex($dateDisbursed,"/^[0-9-]*$/")) { return true; }
	if(!matchRegex($type,"/^[a-zA-Z]{1}$/")) { return true; }
	if(!matchRegex($amount,"/^[0-9.]*$/")) { return true; }
	if(!matchRegex($paymentSchdNo,"/^[0-9]*$/")) { return true; }
	if(!matchRegex($issueIncharge,"/^[a-zA-Z0-9 ]*$/")) { return true; }
	if(!matchRegex($issueApprover,"/^[a-zA-Z0-9 ]*$/")) { return true; }
	if(!matchRegex($description,"/^[a-zA-Z0-9 -_.,#]*$/")) { return true; }
	
	return false;
}
// Removes illegal characters from user inputs
function tidyInputs(){
	global $nric,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	
	$nric = preg_replace('/[^a-zA-Z0-9]/','',$nric);
	$faApplicationID = preg_replace('/[^0-9]/','',$faApplicationID);
	$dateDisbursed = preg_replace('/[^0-9-]/','',$dateDisbursed);
	$type = preg_replace('/[^a-zA-Z0-9]/','',$type);
	$amount = preg_replace('/[^0-9.]/','',$amount);
	$paymentSchdNo = preg_replace('/[^0-9]/','',$paymentSchdNo);
	$issueIncharge = preg_replace('/[^a-zA-Z0-9 ]/','',$issueIncharge);
	$issueApprover = preg_replace('/[^a-zA-Z0-9 ]/','',$issueApprover);
	$description = preg_replace('/[^a-zA-Z0-9 -_.,#]/','',$description);
}
// Loads user inputs into Javascript variables
function dumpVarsToJS(){
	global $nric,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	
	$jscommand = "<script type=\"text/javascript\">";
	$jscommand .= "nric='".$nric."';";
	$jscommand .= "faApplicationID='".$faApplicationID."';";
	$jscommand .= "dateDisbursed='".$dateDisbursed."';";
	$jscommand .= "amount='".$amount."';";
	$jscommand .= "type='".$type."';";
	$jscommand .= "paymentSchdNo='".$paymentSchdNo."';";
	$jscommand .= "issueIncharge='".$issueIncharge."';";
	$jscommand .= "issueApprover='".$issueApprover."';";
	$jscommand .= "description='".$description."';";
	$jscommand .= "</script>";
	echo $jscommand;
}

function tryGetNRICfromFAID($faID){
	//returns NRIC
	//if not existing, will return -1
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT nric FROM FADetails WHERE faID=?;");
	$command->bind_param('i',$faID);
	
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
// Attempts to validate faApplicationID, returns -1 if fail
function validFAApplicationID($faApplicationID){
	if($faApplicationID==""){
		return -1;	
	}
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT faApplicationID FROM FAApplication WHERE faApplicationID=?;");
	$command->bind_param('i',$faApplicationID);
	
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows==0){
		return -1;
	} else {
		return 0;
	}
}
// Checks disbursement inputs to ensure valid inputs 
//(date within faApplication bounds, amount does not exceed total approved)
function checkValidDateAmtSchdNo(){
	global $faApplicationID,$dateDisbursed,$amount,$paymentSchdNo;
	
	$htmlcode = "";
	
	//check date
	$startDate = "";
	$endDate = "";
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT startDate,endDate FROM FAApplication WHERE faApplicationID=?;");
	$command->bind_param('i',$faApplicationID);
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows!=0){
		$row = $result->fetch_assoc();
		$startDate = date('m-d-Y',strtotime($row['startDate']));
		$endDate = date('m-d-Y',strtotime($row['endDate']));
		$tempdateDisbursed = date('m-d-Y',strtotime($dateDisbursed));
		if(!($startDate <= $tempdateDisbursed && $tempdateDisbursed <= $endDate)){
			$htmlcode .= "Invalid Date : Date must be between ".$startDate." and ".$endDate."<br />";
		}
	}
	
	//check amount
	$totalAmtApproved = 0;
	$totalAmtDisbursed = 0;
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT totalAmtApproved,totalAmtDisbursed FROM FAApplication WHERE faApplicationID=?;");
	$command->bind_param('i',$faApplicationID);
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows!=0){
		$row = $result->fetch_assoc();
		$totalAmtApproved = $row['totalAmtApproved'];
		$totalAmtDisbursed = $row['totalAmtDisbursed'];
		
		if($totalAmtDisbursed+$amount>$totalAmtApproved){
			$htmlcode .= "Invalid Amount :  Amount must be equal or lower than "
			.($totalAmtApproved-$totalAmtDisbursed).".<br />";
		}
	}
	
	//check paymentSchdNo
	$inpaymentSchdNo = 0;
	initializeDB();	
	$command = $_SESSION['connection']->prepare("SELECT paymentSchdNo FROM FADisbursement WHERE faApplicationID=? ORDER BY paymentSchdNo DESC;");
	$command->bind_param('i',$faApplicationID);
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows!=0){
		$row = $result->fetch_assoc();
		$inpaymentSchdNo = $row['paymentSchdNo'];
	} 
	$inpaymentSchdNo++;
	if($inpaymentSchdNo!=$paymentSchdNo){
		$htmlcode .= "Invalid Payment Schedule No :  Amount must be "
			.$inpaymentSchdNo.".<br />";
	}

	return $htmlcode;	
}
// Retrieves all FAApplications under the given faID
function getFAApplications($faID){
	$htmlcode = "";
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT faApplicationID FROM FAApplication WHERE faID=? AND isApproved=1 ORDER BY endDate DESC;");
	$command->bind_param('i',$faID);
	
	$command->execute();
	$result = $command->get_result();
	
	$ifFirstRow = true;
	while($row = $result->fetch_row()){
		if($ifFirstRow){
			$ifFirstRow = false;
			$htmlcode .= "<option value=\"". $row[0] ."\" selected>" . $row[0] . "</option>";
		} else {
			$htmlcode .= "<option value=\"". $row[0] ."\">" . $row[0] . "</option>";
		}
		
		
	}
	
	if($result->num_rows==0){
		$htmlcode = -1;
	}
	
	return $htmlcode;
}
// Creates FA Disbursement record
function createEntryFADisbursement(){
	global $nric,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	$imgLoc = '';
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("INSERT INTO FADisbursement VALUES(NULL,?,?,?,?,?,?,?,?,?);");
	$command->bind_param('issdissss',$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description,$imgLoc);
	
	$command->execute();
	return;
}
// Updates Disbursement's relevant FA Application
function updateFAApplication(){
	global $faApplicationID,$amount,$dateDisbursed;
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("UPDATE FAApplication SET totalAmtDisbursed=totalAmtDisbursed+? WHERE faApplicationID=?;");
	$command->bind_param('di',$amount,$faApplicationID);
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
    	<div class="pageTitle">Create Financial Application - Disbursement</div>
        <div class="linkButton"><a href="./createFA-disbursement-stg1.php">Start Over</a></div><br /><br />
        
        <div>
        	<form method="post"><table class="formTemplate">
            <tr><td colspan="2" class="rowTitle2">Details</td></tr>
            <tr><td class="rowTitle3">
            NRIC*
            </td><td class="rowTitle3">
            <input type="text" name="nric" id="nric" width="" size="" value="" pattern="[Ss]{1}[0-9]{7}[a-iA-IzZjJ]{1}" required readonly />
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
            <input type="date" name="dateDisbursed" id="dateDisbursed" width="" size="" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Type*
            </td><td class="rowTitle3">
            <input type="text" name="type" id="type" width="" pattern="[a-zA-Z]{1}" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Amount*
            </td><td class="rowTitle3">
            <input type="text" name="amount" id="amount" width="" size="" pattern="([0-9]*)|([0-9]+.[0-9]+)" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Payment Schedule Number*
            </td><td class="rowTitle3">
            <input type="number" name="paymentSchdNo" id="paymentSchdNo" width="" size="" min="1" pattern="[0-9]*" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Issuer In-Charge*
            </td><td class="rowTitle3">
            <input type="text" name="issueIncharge" id="issueIncharge" width="" size="" pattern="[a-zA-Z ]*" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Issuer Approver*
            </td><td class="rowTitle3">
            <input type="text" name="issueApprover" id="issueApprover" width="" size="" pattern="[a-zA-Z ]*" required/>
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
if($switchMode==1){
	echo "<script type=\"text/javascript\">switchMode(true);</script>";
} 
echo "<script type=\"text/javascript\">setFormValues();</script>";
?>

</body>
</html>