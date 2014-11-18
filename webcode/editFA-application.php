	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="./../backend/cssstyle.css">
<script type="text/javascript" src="./../backend/jsfunctions.js"></script>
<script type="text/javascript">
// Applies page variables into form inputs
function setFormValues(){
	setElementValue('faApplicationID',faApplicationID);
	setElementValue('type',type);
	setElementValue('description',description);
	setElementValue('startDate',startDate);
	setElementValue('endDate',endDate);
	setElementValue('dateApplied',dateApplied);
	if(isApproved==0){
		document.getElementById('isApproved0').checked=true;
		document.getElementById('isApproved1').checked=false;
	}
	setElementValue('faVetter',faVetter);
	setElementValue('totalAmtApproved',totalAmtApproved);
	setElementValue('totalAmtDisbursed',totalAmtDisbursed);
}
</script>
<title>LSBC - Edit Financial Application - Application</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : editFA-application.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Edit FA - Application functionality of the Application
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

$pageState = 0;
$errorMsg = "";

$faID = 0;

//PAGE INPUT variables
$faApplicationID = "";
$type = "";
$description = "";
$startDate = "";
$endDate = "";
$dateApplied = "";
$isApproved = "";
$faVetter = "";
$totalAmtApproved = "";
$totalAmtDisbursed = "";

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	global $faID;
	global $faApplicationID,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved,$totalAmtDisbursed;
	global $errorMsg;
	
	tryAssignInputVars();
	tidyInputs();
	
	$faID = tryGetFAIDfromFAApplicationID($faApplicationID);
	if($faID==-1){
		$errorMsg = "An error has occured. Redirecting...";
		header("refresh:2;url=./home.php");
		return;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		//came from another page
		retrieveFAApplicationDetails($faApplicationID);
		dumpVarsToJS();
		return;
	}
	
	dumpVarsToJS();
	
	//check for empty fields
	if($type=="" || $startDate=="" || $endDate=="" || $dateApplied=="" || $isApproved=="" || $faVetter=="" || $totalAmtApproved==""){
		$errorMsg = "Empty Fields!";
		return;
	}
	
	//check for invalid inputs eg. illegal characters
	$isInvalidInput = checkInvalidInputs();
	if($isInvalidInput){
		$errorMsg = "Invalid Data Found";
		return;
	}
	
	if(!checkStartDateEndDateValidRange($startDate,$endDate)){
		$errorMsg .= "End Date cannot be less than Start Date.";
		return;
	}
	
	//check for date collisions (no overlaps)
	if(checkDateCollision($faID,$faApplicationID,$startDate,$endDate)){
		$errorMsg = "Current Start Date and/or End Date overlaps with Existing application(s). Please select another date.";
		return;
	}
	
	//check if disbursements in FAApplication within bounds of new date
	if(!checkDisbursementsInBounds($faApplicationID,$startDate,$endDate)){
		$errorMsg = "There exists at least one current Disbursement is not within Date Range.";
		return;
	}
	
	if($totalAmtApproved<$totalAmtDisbursed){
		$errorMsg = "Total Amount Approved must be higher or equal to Total Amount Disbursed.";
		return;
	}
	
	//passed all checks
	updateFAApplication();
	header("refresh:0.0001;url=./viewFA.php?faID=".$faID);

}
pageLogic();

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global $faApplicationID,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved,$totalAmtDisbursed;
	$toreturn = true;
	
	if(isset($_POST['faApplicationID'])) { $faApplicationID =  $_POST['faApplicationID']; }
	if(isset($_GET['faApplicationID'])) { $faApplicationID =  $_GET['faApplicationID']; }
	
	if(isset($_POST['type'])) { $type =  $_POST['type']; }
	if(isset($_POST['description'])) { $description  = $_POST['description']; }
	if(isset($_POST['startDate'])) {$startDate = $_POST['startDate']; }
	if(isset($_POST['endDate'])) { $endDate =$_POST['endDate'];}
	if(isset($_POST['dateApplied'])) { $dateApplied= $_POST['dateApplied'];}
	if(isset($_POST['isApproved'])) { $isApproved = $_POST['isApproved']; }
	if(isset($_POST['faVetter'])) { $faVetter = $_POST['faVetter']; }
	if(isset($_POST['totalAmtApproved'])) { $totalAmtApproved = $_POST['totalAmtApproved']; }
	if(isset($_POST['totalAmtDisbursed'])) { $totalAmtDisbursed = $_POST['totalAmtDisbursed']; }
	
	return $toreturn;
}
// Check user input and returns true if invalid inputs are found
function checkInvalidInputs(){
	global $faApplicationID,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved,$totalAmtDisbursed;
	
	if(!matchRegex($faApplicationID,"/^[0-9]*$/")) { return true; }
	if(!matchRegex($type,"/^[a-zA-Z0-9]{1}$/")) { return true; }
	if(!matchRegex($description,"/^[a-zA-Z0-9 -_.,#]*$/")) { return true; }
	if(!matchRegex($startDate,"/^[0-9-]*$/")) { return true; }
	if(!matchRegex($endDate,"/^[0-9-]*$/")) { return true; }
	if(!matchRegex($dateApplied,"/^[0-9-]*$/")) { return true; }
	if(!matchRegex($isApproved,"/^[01]$/")) { return true; }
	if(!matchRegex($faVetter,"/^[a-zA-Z0-9 ]*$/")) { return true; }
	if(!matchRegex($totalAmtApproved,"/^[0-9.]*$/")) { return true; }
	if(!matchRegex($totalAmtDisbursed,"/^[0-9.]*$/")) { return true; }
	
	return false;
}
// Removes illegal characters from user inputs
function tidyInputs(){
	global $faApplicationID,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved,$totalAmtDisbursed;
	
	$faApplicationID = preg_replace('/[^0-9]/','',$faApplicationID);
	$type = preg_replace('/[^a-zA-Z0-9]/','',$type);
	$description = preg_replace('/[^a-zA-Z0-9 -_.,#]/','',$description);
	$startDate = preg_replace('/[^0-9-]/','',$startDate);
	$endDate = preg_replace('/[^0-9-]/','',$endDate);
	$dateApplied = preg_replace('/[^0-9-]/','',$dateApplied);
	$isApproved = preg_replace('/[^01]/','',$isApproved);
	$faVetter = preg_replace('/[^a-zA-Z0-9 ]/','',$faVetter);
	$totalAmtApproved = preg_replace('/[^0-9.]/','',$totalAmtApproved);
	$totalAmtDisbursed = preg_replace('/[^0-9.]/','',$totalAmtDisbursed);
}
// Loads user inputs into Javascript variables
function dumpVarsToJS(){
	global $faApplicationID,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved,$totalAmtDisbursed;
	
	$jscommand = "<script type=\"text/javascript\">";
		
	$jscommand .= "faApplicationID='".$faApplicationID."';";
	$jscommand .= "type='".$type."';";
	$jscommand .= "description='".$description."';";
	$jscommand .= "startDate='".$startDate."';";
	$jscommand .= "endDate='".$endDate."';";
	$jscommand .= "dateApplied='".$dateApplied."';";
	$jscommand .= "isApproved='".$isApproved."';";
	$jscommand .= "faVetter='".$faVetter."';";
	$jscommand .= "totalAmtApproved='".$totalAmtApproved."';";
	$jscommand .= "totalAmtDisbursed='".$totalAmtDisbursed."';";
	
	$jscommand .= "</script>";
	echo $jscommand;
}

// Retrieves FA Application information
function retrieveFAApplicationDetails($faApplicationID){
	global $faApplicationID,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved,$totalAmtDisbursed;
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT * FROM FAApplication WHERE faApplicationID=?;");
	$command->bind_param('i',$faApplicationID);
	
	$command->execute();
	$result = $command->get_result();
	
	if($result->num_rows>0){
		$arr = $result->fetch_array(MYSQLI_ASSOC);
		
		//no need assign, already have value
		//$faApplicationID = $arr['faApplicationID'];
		$type = $arr['type'];
		$description = $arr['description'];
		$startDate = $arr['startDate'];
		$endDate = $arr['endDate'];
		$dateApplied = $arr['dateApplied'];
		$isApproved = $arr['isApproved'];
		$faVetter = $arr['faVetter'];
		$totalAmtApproved = $arr['totalAmtApproved'];
		$totalAmtDisbursed = $arr['totalAmtDisbursed'];
	} 
	
	return;
}
// Checks to ensure date range does not overlap with any other existing faApplications
function checkDateCollision($faID,$faApplicationID,$startDate,$endDate){
	//THIS VERSION IS DIFFERENT FROM createFA-application; includes $faApplicationID to prevent collisions
	//since local variable, can just modify without worrying about global vars
	$startDate = date('m-d-Y',strtotime($startDate));
	$endDate = date('m-d-Y',strtotime($endDate));
	
	//gets all faApplications from faID
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT startDate,endDate FROM FAApplication WHERE faID=? AND faApplicationID!=?;");
	$command->bind_param('ii',$faID,$faApplicationID);
	
	$command->execute();
	$result = $command->get_result();
	
	while($row = $result->fetch_assoc()){
		$rowStartDate = date('m-d-Y',strtotime($row['startDate']));
		$rowEndDate = date('m-d-Y',strtotime($row['endDate']));
		
		//echo $rowStartDate." ".$rowEndDate." ".$startDate." ".$endDate."<br>";
		//compares to check if date is colliding	
		//startDate endDate overlaps
		if($endDate==$rowEndDate||$startDate==$rowStartDate||$startDate==$rowEndDate||$endDate==$rowStartDate){
			//jsmsg('x');
			return true;
		}
		
		//event = {    [    }    ]
		if($startDate<$rowStartDate&&$endDate>$rowStartDate&&$endDate<$rowEndDate){
			//jsmsg('1');
			return true;
		}
		//event = [   { }    ]
		if($startDate>$rowStartDate&&$endDate<$rowEndDate){
			//jsmsg('2');
			return true;	
		}
		//event = [   {    ]    }
		if($startDate>$rowStartDate&&$startDate<$rowEndDate&&$endDate>$rowEndDate){
			//jsmsg('3');
			return true;
		}
		//event = {   [  ]    }
		if($startDate<$rowStartDate&&$endDate>$rowEndDate){
			//jsmsg('4');
			return true;	
		}
		
	}
	
	return false;
}
// Checks to ensure StartDate > EndDate
function checkStartDateEndDateValidRange($startDate,$endDate){
	$startDate = date('m-d-Y',strtotime($startDate));
	$endDate = date('m-d-Y',strtotime($endDate));
	
	if($startDate>$endDate){
		return false;
	} 
	
	return true;
}
// Checks to ensure that Disbursement is within faApplication's date range
function checkDisbursementsInBounds($faApplicationID,$startDate,$endDate){
	//since local variable, can just modify without worrying about global vars
	$startDate = date('m-d-Y',strtotime($startDate));
	$endDate = date('m-d-Y',strtotime($endDate));
	
	//gets all faApplications from faID
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT dateDisbursed FROM FADisbursement WHERE faApplicationID=?;");
	$command->bind_param('i',$faApplicationID);
	
	$command->execute();
	$result = $command->get_result();
	 
	while($row = $result->fetch_assoc()){
		$dateDisbursed = date('m-d-Y',strtotime($row['dateDisbursed']));
		
		//checks if disbursement is within new Date Range
		if(!($startDate<=$dateDisbursed&&$dateDisbursed<=$endDate)){
			return false;
		}	
	}
	
	return true;
}

// Attempts to get faID from faApplicationID; returns -1 if fail
function tryGetFAIDfromFAApplicationID($faApplicationID){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT faID FROM FAApplication WHERE faApplicationID=?;");
	$command->bind_param('i',$faApplicationID);
	
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows>0){
		$arr = $result->fetch_array(MYSQLI_NUM);
		return $arr[0];	
	} else {
		return -1;
	}
}
// Updates faApplication record
function updateFAApplication(){
	global $faApplicationID,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved,$totalAmtDisbursed;
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("UPDATE FAApplication SET type=?,description=?,startDate=?,endDate=?,dateApplied=?,isApproved=?,faVetter=?,totalAmtApproved=? WHERE faApplicationID=?;");
	$command->bind_param('sssssisdi',$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved,$faApplicationID);
	
	$command->execute();
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
    	<div class="pageTitle">Edit Financial Application - Application</div>
        <div class="linkButton"><a href="./viewFA.php?faID=<?php echo $faID?>">Back to View Financial Application</a></div>
        <br /><br /><br />
        <div>
			<form method='POST'><table class="formTemplate">
            <tr><td colspan="2" class="rowTitle2">Application Details</td></tr>
        	<input type="hidden" name="isProcessEditFAApplication" value="1"/>
        	<input type="number" name="faApplicationID" id="faApplicationID" width="" size="" value="" hidden/>
            
        	<tr><td class="rowTitle3">
            Type*
            </td><td class="rowTitle3">
            <input type="text" name="type" id="type" width="1" size="" value="" pattern="[a-zA-Z]{1}" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Description
            </td><td class="rowTitle3">
            <textarea rows="4" cols="50" name="description" id="description"></textarea>
            </td></tr>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Start Date*
            </td><td class="rowTitle3">
            <input type="date" name="startDate" id="startDate" width="" size="" value="" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            End Date*
            </td><td class="rowTitle3">
            <input type="date" name="endDate" id="endDate" width="" size="" value="" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Date Applied*
            </td><td class="rowTitle3">
            <input type="date" name="dateApplied" id="dateApplied" width="" size="" value="" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Is Approved*
            </td><td class="rowTitle3">
            No<input type="radio" name="isApproved" id="isApproved0" width="" size="" value="0"/><br />
            Yes<input type="radio" name="isApproved" id="isApproved1" width="" size="" value="1" checked/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            FA Vetter*
            </td><td class="rowTitle3">
            <input type="text" name="faVetter" id="faVetter" width="" size="" value="" pattern="[a-zA-Z ]*" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Total Amt. Approved*
            </td><td class="rowTitle3">
            <input type="text" name="totalAmtApproved" id="totalAmtApproved" width="" size="" value="" pattern="([0-9]*)|([0-9]+.[0-9]+)" required/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Total Amt. Disbursed
            </td><td class="rowTitle3">
            <input type="text" name="totalAmtDisbursed" id="totalAmtDisbursed" width="" size="" value="" readonly/>
            </td></tr>
            
            
            <tr><td colspan="2" class="rowTitle3">
            <input type="submit" />
            </td></tr>
            
        </table></form>
    	</div>
        
        <div class="errorMsg">
            	<?php echo $errorMsg; ?>
            </div>
    <div>
    	
		<div class="contentBtm">LSBC Financial Application Management System v1.0 @ 2014</div>
	
</div>	

<?php
echo "<script type=\"text/javascript\">setFormValues();</script>";
?>

</body>
</html>