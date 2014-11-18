<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="./../backend/cssstyle.css">
<script type="text/javascript" src="./../backend/jsfunctions.js"></script>
<script type="text/javascript">
// Applies page variables into form inputs
function setFormValues(){
	setElementValue('faDisbursementID',faDisbursementID);
	setElementValue('faApplicationID',faApplicationID);
	setElementValue('dateDisbursed',dateDisbursed);
	setElementValue('type',type);
	setElementValue('amount',amount);
	setElementValue('paymentSchdNo',paymentSchdNo);
	setElementValue('issueIncharge',issueIncharge);
	setElementValue('issueApprover',issueApprover);
	setElementValue('description',description);
}
</script>
<title>LSBC - Edit Financial Application - Disbursement</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : editFA-disbursement.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Edit FA - Disbursement functionality of the Application
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

$switchMode = 0;
//SWITCHMODE
//0: do not load vars from js 
//1: load vars from js 

// Page variables
$pageState = 0;
$errorMsg = "";
$faApplicationsHTML = "";
$faID = 0;

// PAGE INPUT variables
$faDisbursementID  = "";
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
	global $faID;
	global $faDisbursementID,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	global $switchMode,$faApplicationsHTML;
	global $pageState,$errorMsg;
	
	tryAssignInputVars();
	$isInvalidInput = checkInvalidInputs();
	tidyInputs();
	
	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		//came from another page
		//check if valid faDisbursementID
		$faID = tryGetFAIDfromFADisbursementID($faDisbursementID);
		if($faID==-1){
			//invalid faDIsbursementID, redirect to home
			$errorMsg = "An error has occured. Redirecting...";
			header("refresh:2;url=./home.php");
			return;
		}
		$faApplicationsHTML = getFAApplications($faID);
		retrieveFADisbursementDetails($faDisbursementID);
		dumpVarsToJS();
		$switchMode = 1;
		return;
	}
	
	//POSTed into page
	//check existing faDisbursementID
	$faID = -1;
	$faID = tryGetFAIDfromFADisbursementID($faDisbursementID);
	if($faID==-1){
		//invalid faDisbursementID, redirect to home
		$errorMsg = "An error has occured. Redirecting...";
		header("refresh:2;url=./home.php");
		return;
	}
	
	//check existing faApplicationID
	if(validFAApplicationID($faApplicationID)==-1){
		//invalid faApplicationID, redirect to home
		$errorMsg = "An error has occured. Redirecting...";
		header("refresh:2;url=./home.php");
		return;
	}
	
	$faApplicationsHTML = getFAApplications($faID);
	dumpVarsToJS();
	$switchMode = 1;
	
	//check for invalid inputs eg. illegal characters
	if($isInvalidInput){
		$errorMsg = "Invalid Data Found";
		return;
	}
	
	//check for empty fields
	if($dateDisbursed=="" || $type=="" || $amount=="" || $paymentSchdNo=="" || $issueIncharge=="" || $issueApprover==""){
		$errorMsg = "Empty Fields!";
		return;
	}
	
	//checks for semantic errors in date,amt,schdno
	$tempErrMsg = checkValidDateAmtSchdNo();
	if($tempErrMsg!=""){
		$errorMsg = $tempErrMsg;
		return;
	}
	
	//update FAApplication and FADisbursement
	updateFAApplication();
	updateFADisbursement();
	header("refresh:0.0001;url=./viewFA.php?updateSuccess=1&faID=".$faID);
	return;
}
pageLogic();

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global  $faDisbursementID,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	$toreturn = true;
	
	if(isset($_POST['faDisbursementID'])) { $faDisbursementID =  $_POST['faDisbursementID']; }
	if(isset($_GET['faDisbursementID'])) { $faDisbursementID =  $_GET['faDisbursementID']; }
	
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
// Checks user inputs for invalid inputs, returns true if found any invalid inputs
function checkInvalidInputs(){
	global $faDisbursementID,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	
	if(!matchRegex($faDisbursementID,"/^[0-9]*$/")) { return true; }
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
	global $faDisbursementID,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	
	$faDisbursementID = preg_replace('/[^0-9]/','',$faDisbursementID);
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
	global $faDisbursementID,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	
	$jscommand = "<script type=\"text/javascript\">";
	$jscommand .= "faDisbursementID='".$faDisbursementID."';";
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
// Checks disbursement inputs to ensure valid inputs 
//(date within faApplication bounds, amount does not exceed total approved)
function checkValidDateAmtSchdNo(){
	global $faApplicationID,$faDisbursementID,$dateDisbursed,$amount,$paymentSchdNo;
	
	$htmlcode = "";
	
	//check date (within FAApplication bounds)
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
	
	//check amount (doesn't exceed total disbursed)
	$totalAmtApproved = -1;
	$totalAmtDisbursed = -1;
	$oldAmt = -1;
	
	//get old amt
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT amount FROM FADisbursement WHERE faDisbursementID=?;");
	$command->bind_param('i',$faDisbursementID);
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows!=0){
		$row = $result->fetch_assoc();
		$oldAmt = $row['amount'];
	}
	
	//get existing totalAmtApproved and totalAmtDisbursed 
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT totalAmtApproved,totalAmtDisbursed FROM FAApplication WHERE faApplicationID=?;");
	$command->bind_param('i',$faApplicationID);
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows!=0){
		$row = $result->fetch_assoc();
		$totalAmtApproved = $row['totalAmtApproved'];
		$totalAmtDisbursed = $row['totalAmtDisbursed'];
	}
	
	//checks if amount exceeds
	if($totalAmtApproved !=-1 && $totalAmtDisbursed !=-1 && $oldAmt !=-1){
		$highestAmt = $totalAmtApproved-($totalAmtDisbursed-$oldAmt);
		if($amount>$highestAmt){
			$htmlcode .= "Invalid Amount :  Amount must be equal or lower than "
			.($highestAmt).".<br />";
		}	
	}
	
	//check paymentSchdNo (check no collisions)
	initializeDB();	
	$command = $_SESSION['connection']->prepare("SELECT faDisbursementID,paymentSchdNo FROM FADisbursement WHERE faApplicationID=? AND paymentSchdNo=?;");
	$command->bind_param('ii',$faApplicationID,$paymentSchdNo);
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows!=0){
		$row = $result->fetch_assoc();
		if($faDisbursementID!=$row['faDisbursementID']){
			$htmlcode .= "Invalid Payment Schedule No : Choose another number.<br/> ";
		}
	}

	return $htmlcode;	
}

// Retrieves Disbursement details based on faDisbursementID
function retrieveFADisbursementDetails($faDisbursementID){
	global $faDisbursementID,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT * FROM FADisbursement WHERE faDisbursementID=?;");
	$command->bind_param('i',$faDisbursementID);
	
	$command->execute();
	$result = $command->get_result();
	
	if($result->num_rows>0){
		$arr = $result->fetch_array(MYSQLI_ASSOC);
		
		//EDIT CODE for relevant details
		$faDisbursementID = $arr['faDisbursementID'];
		$faApplicationID = $arr['faApplicationID'];
		$dateDisbursed = $arr['dateDisbursed'];
		$type = $arr['type'];
		$amount = $arr['amount'];
		$paymentSchdNo = $arr['paymentSchdNo'];
		$issueIncharge = $arr['issueIncharge'];
		$issueApprover = $arr['issueApprover'];
		$description = $arr['description'];
	} 
	
	return;
}
// Attempts to retireve faID from faDisbursementID, returns -1 if fail
function tryGetFAIDfromFADisbursementID($faDisbursementID){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT FAApplication.faID FROM FAApplication, FADisbursement WHERE FADisbursement.faApplicationID= FAApplication.faApplicationID AND FADisbursement.faDisbursementID=?;");	
	$command->bind_param('i',$faDisbursementID);
	
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows>0){
		$arr = $result->fetch_array(MYSQLI_NUM);
		return $arr[0];	
	} else {
		return -1;
	}
}
// Updates FADisbursement record
function updateFADisbursement(){
	global $faDisbursementID,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("UPDATE FADisbursement SET faApplicationID=?,dateDisbursed=?,type=?,amount=?,paymentSchdNo=?,issueIncharge=?,issueApprover=?,description=? WHERE faDisbursementID=?;");
	$command->bind_param('issdisssi',$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description,$faDisbursementID);
	
	$command->execute();
}
// Updates FAApplication record
function updateFAApplication(){
	global $faDisbursementID,$faApplicationID,$dateDisbursed,$type,$amount,$paymentSchdNo,$issueIncharge,$issueApprover,$description;
	
	//get old amount
	$oldAmt = -1;
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT amount FROM FADisbursement WHERE faDisbursementID=?;");
	$command->bind_param('i',$faDisbursementID);
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows!=0){
		$row = $result->fetch_assoc();
		$oldAmt = $row['amount'];
	}
	
	//update totalAmtDisbursed in faApplication
	initializeDB();
	$command = $_SESSION['connection']->prepare("UPDATE faApplication SET totalAmtDisbursed=totalAmtDisbursed-?+? WHERE faApplicationID=?;");
	$command->bind_param('ddi',$oldAmt,$amount,$faApplicationID);
	$command->execute();
}
// Retrieves all FAApplications under the given faID
function getFAApplications($faID){
	$htmlcode = "";
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT faApplicationID FROM FAApplication WHERE faID=? AND isApproved=1 ORDER BY endDate DESC;");
	$command->bind_param('i',$faID);
	
	$command->execute();
	$result = $command->get_result();
	while($row = $result->fetch_row()){
		$htmlcode .= "<option value=\"". $row[0] ."\">" . $row[0] . "</option>";
	}
	
	if($result->num_rows==0){
		$htmlcode = -1;
	}
	
	return $htmlcode;
}
// Checks if faApplicationID is valid; returns -1 if fail
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
    	<div class="pageTitle">Edit Financial Application - Disbursement</div>
        <div class="linkButton"><a href="./viewFA.php?faID=<?php echo $faID?>">Back to View Financial Application</a></div>
        <br /><br /><br />
        <div>
        	<form method="post"><table class="formTemplate">
            <input type="number" name="faDisbursementID" id="faDisbursementID" width="" size="" value="" hidden/>
            
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
    
    <div class="contentBtm">LSBC Financial Application Management System v1.0 @ 2014</div>
	
</div>	

<?php
if($switchMode ==1){
	echo "<script type=\"text/javascript\">setFormValues();</script>";
}
?>

</body>
</html>