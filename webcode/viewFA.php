<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="./../backend/cssstyle.css">
<script type="text/javascript" src="./../backend/jsfunctions.js"></script>
<script type="text/javascript">

</script>
<title>LSBC - View Financial Application</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : viewFA.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the View Financial Application functionality of the Application
*/

// Includes common code, starts session, and initializes database conenction
include './../backend/phpFunctions.php';
session_start();
initializeDB();

// Checks if user is logged in
if(!isLoggedIn()){
	// Redirects users to login page
	header("refresh:0.0001;url=./login.php");	
	return;
}
// Checks if user has ViewFA permission
if(!$_SESSION['canViewFA']){
	header("refresh:0.001;url=./home.php");
	return;
}

// Page variables
$pageState = 0;
$errorMsg = "";
$viewOutput = "";

// Permission variables
$canViewFA = 0;
$canCreateFA = 0;
$canEditFA = 0;
$canDeleteFA = 0;
// Assigns user permissions to page variables
function tryAssignUserPerms(){
	global $canViewFA,$canCreateFA,$canEditFA,$canDeleteFA;
	if(isset($_SESSION['canViewFA'])) { $canViewFA =  $_SESSION['canViewFA'];}
	if(isset($_SESSION['canCreateFA'])) { $canCreateFA =  $_SESSION['canCreateFA'];}
	if(isset($_SESSION['canEditFA'])) { $canEditFA =  $_SESSION['canEditFA'];}
	if(isset($_SESSION['canDeleteFA'])) { $canDeleteFA =  $_SESSION['canDeleteFA'];}
}
tryAssignUserPerms();

//PAGE input variables
$faID = 0;

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	global $faID;
	global $viewOutput;
	global $pageState,$errorMsg;
	
	tryAssignInputVars();
	tidyInputs();
	$pageState = getPageState();
	//PAGE STATE
	//1: Valid faID
	//-1: Invalid faID
	
	// Displays update successful (returning from other pages)
	if(isset($_GET['updateSuccess'])){
		if($_GET['updateSuccess']==1){
			$errorMsg = "Update successful!";
		}
	}
	
	if($pageState==1){
		//1: Valid faID
		$viewOutput .= "<table class=\"generatedReport\">";
		$viewOutput .= displayFADetails($faID);
		$viewOutput .= displayFAApplicationsAndDisbursements($faID);
		$viewOutput .= "</table>";
		return;
	} else if($pageState==-1) {
		//-1: Invalid faID
		$errorMsg = "An error has occured. Redirecting...";
		header("refresh:2;url=./searchFA.php");
		return;
	}
}
pageLogic();

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global $faID;
	$toreturn = true;
	
	if(isset($_GET['faID'])) { $faID =  $_GET['faID']; }
	
	return $toreturn;
}
// Determines page state based on user inputs
function getPageState(){
	//PAGE STATE
	//1: Valid faID
	//-1: Invalid faID
	global $faID;
	
	$toreturn = 0;
	
	if(validateFAID($faID)){
		return 1;
	} else {
		return -1;
	}
}
// Removes illegal characters from user inputs
function tidyInputs(){
	global $faID;

	$faID = preg_replace('/[^0-9]/','',$faID);
}
// Retrieves FADetails of corresponding faID
function displayFADetails($faID){
	$toreturn = "<tr>";
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT * FROM FADetails WHERE faID=?;");
	$command->bind_param('i',$faID);
	
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows>0){
		$arr = $result->fetch_array(MYSQLI_ASSOC);
		
		$toreturn .= buildFADetailsRow($arr);
		
	} else {
		$toreturn .= "<td>No results.</td></tr>";
	}
	
	$toreturn .= "</tr>";
	return $toreturn;
}
// Retrieves FAApplications and FADisbursements of corresponding faID
function displayFAApplicationsAndDisbursements($faID){
	$toreturn = "";
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT * FROM FAApplication LEFT JOIN FADisbursement ON FAApplication.faApplicationID = FADisbursement.faApplicationID WHERE FAApplication.faID=? ORDER BY FAApplication.faApplicationID ASC, FAApplication.endDate DESC;");
	$command->bind_param('i',$faID);
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows==0){
		$toreturn .= "<tr><td>No Applications.</td></tr>";
		return $toreturn;	
	}
	$tempFAApplicationID = -1;
	$hasPrintedDisbursementHeader = false;
	// Iterates through results
	while($row = $result->fetch_array(MYSQLI_BOTH)){
		if($tempFAApplicationID!=$row[0]){
			//check if new FAID	
			$tempFAApplicationID=$row[0];
			$toreturn .= "<tr><td colspan=\"13\" style=\"border:0px none;\"></td></tr>";
			$toreturn .= buildFAApplicationRow($row);
			$hasPrintedDisbursementHeader = false;
		}
		
		// Checks if current row does not have a valid faDisbursement
		if($row['faDisbursementID']==''){
			$toreturn .= "<tr>";
			$toreturn .= "<td class=\"rowTitle4\" colspan=\"13\">No Disbursements.</td>";
			$toreturn .= "</tr>";
			continue;
		}
		
		// Checks if faDisbursement headers have already been printed
		if($hasPrintedDisbursementHeader){
			$toreturn .= buildFADisbursementRow($row,false);	
		} else {
			$toreturn .= buildFADisbursementRow($row,true);
			$hasPrintedDisbursementHeader = true;
		}
		
	}
	
	return $toreturn;
}
// Checks if faID exists (in FADetails)
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
// Tries to get NRIC from faID; returns -1 if fail
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
// Displays FADetail information based on row input
function buildFADetailsRow($arr){
	global $canViewFA,$canCreateFA,$canEditFA,$canDeleteFA;
	$toreturn = "<tr>";
	
	$toreturn .= "<td class=\"rowTitle2\">faID</td>";
	$toreturn .= "<td class=\"rowTitle2\">NRIC</td>";
	$toreturn .= "<td class=\"rowTitle2\">First Name</td>";
	$toreturn .= "<td class=\"rowTitle2\">Last Name</td>";
	$toreturn .= "<td class=\"rowTitle2\">DOB</td>";
	$toreturn .= "<td class=\"rowTitle2\">Address 1</td>";
	$toreturn .= "<td class=\"rowTitle2\">Address 2</td>";
	$toreturn .= "<td class=\"rowTitle2\">PO Code</td>";
	$toreturn .= "<td class=\"rowTitle2\">Home Num.</td>";
	$toreturn .= "<td class=\"rowTitle2\">Handphone Num.</td>";
	$toreturn .= "<td class=\"rowTitle2\">Email</td>";
	$toreturn .= "<td class=\"rowTitle2\">Description</td>";
	$toreturn .= "<td class=\"rowTitle2\">&nbsp;</td>";
	$toreturn .= "</tr>";
	
	$toreturn .= "<td class=\"rowTitle4\">".$arr['faID']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['nric']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['firstName']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['lastName']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['dob']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['address1']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['address2']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['poCode']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['homeNum']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['handphoneNum']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['email']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['description']."</td>&nbsp;";
	
	$toreturn .= "<td class=\"rowTitle4\">";
	// Shows edit button only when user has EditFA permission
	if($canEditFA==1){
		$toreturn .= "<a href=\"./editFA-details.php?faID=".$arr["faID"]."\" >Edit</a><br>";
	}
	// Shows delete button only when user has DeleteFA permission
	if($canDeleteFA==1){
		$toreturn .= "<a href=\"#\" 
		onclick=\"confirmUrlEvent('Confirm Delete?' , './deleteFA.php?faID=".$arr["faID"]."');\"
		>Delete</a><br>";
	}	
	$toreturn .= "</td>";
	
	$toreturn .= "</tr>";
	return $toreturn;
}
// Displays FAApplication information based on row input
function buildFAApplicationRow($arr){
	global $canViewFA,$canCreateFA,$canEditFA,$canDeleteFA;
	
	$toreturn = "<tr>";
	$toreturn .= "<td class=\"rowTitle2\">faApplicationID</td>";
	$toreturn .= "<td class=\"rowTitle2\">Type</td>";
	$toreturn .= "<td class=\"rowTitle2\">Description</td>";
	$toreturn .= "<td class=\"rowTitle2\">Start Date</td>";
	$toreturn .= "<td class=\"rowTitle2\">End Date</td>";
	$toreturn .= "<td class=\"rowTitle2\">Date Applied</td>";
	$toreturn .= "<td class=\"rowTitle2\">imgLoc</td>";
	$toreturn .= "<td class=\"rowTitle2\">isApproved</td>";
	$toreturn .= "<td class=\"rowTitle2\">FA Vetter</td>";
	$toreturn .= "<td class=\"rowTitle2\">Total Amt. Approved</td>";
	$toreturn .= "<td class=\"rowTitle2\">Total Amt. Disbursed</td>";
	$toreturn .= "<td class=\"rowTitle2\" colspan=\"2\"></td>";
	$toreturn .= "</tr>";
	
	$toreturn .= "<td class=\"rowTitle4\">".$arr[0]."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr[2]."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr[3]."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['startDate']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['endDate']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['dateApplied']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['imgLoc']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['isApproved']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['faVetter']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['totalAmtApproved']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['totalAmtDisbursed']."</td>";
	
	$toreturn .= "<td class=\"rowTitle4\" colspan=\"2\">";
	// Shows edit button only when user has EditFA permission
	if($canEditFA==1){
		$toreturn .= "<a href=\"./editFA-application.php?faApplicationID=".$arr[0]."\" >Edit</a><br>";
	}
	// Shows delete button only when user has DeleteFA permission
	if($canDeleteFA==1){
		$toreturn .= "<a href=\"#\" 
		onclick=\"confirmUrlEvent('Confirm Delete?' , './deleteFA.php?faApplicationID=".$arr[0]."');\"
		>Delete</a><br>";
	}	
	$toreturn .= "</td>";
	
	$toreturn .= "</tr>";

	return $toreturn;
}
// Displays FADisbursement information based on row input
function buildFADisbursementRow($arr,$hasPrintedDisbursementHeader){
	global $canViewFA,$canCreateFA,$canEditFA,$canDeleteFA;
	
	$toreturn = "<tr>";
	
	if($hasPrintedDisbursementHeader){
		$toreturn .= "<tr>";
		$toreturn .= "<td class=\"rowTitle2\">faDisbursementID</td>";
		$toreturn .= "<td class=\"rowTitle2\">Date Disbursed</td>";
		$toreturn .= "<td class=\"rowTitle2\">Type</td>";
		$toreturn .= "<td class=\"rowTitle2\">Amount</td>";
		$toreturn .= "<td class=\"rowTitle2\">Payment Schedule No.</td>";
		$toreturn .= "<td class=\"rowTitle2\">Issuer In Charge</td>";
		$toreturn .= "<td class=\"rowTitle2\">Issuer Approver</td>";
		$toreturn .= "<td class=\"rowTitle2\">Description</td>";
		$toreturn .= "<td class=\"rowTitle2\" colspan=\"6\"></td>";
		$toreturn .= "</tr>";
	} 
			
	$toreturn .= "<td class=\"rowTitle4\">".$arr['faDisbursementID']."</td>";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['dateDisbursed']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['type']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['amount']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['paymentSchdNo']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['issueIncharge']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['issueApprover']."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$arr['description']."</td>&nbsp;";
	
	$toreturn .= "<td class=\"rowTitle4\" colspan=\"5\">";
	// Shows edit button only when user has EditFA permission
	if($canEditFA==1){
		$toreturn .= "<a href=\"./editFA-disbursement.php?faDisbursementID=".$arr["faDisbursementID"]."\" >Edit</a><br>";
	}
	// Shows delete button only when user has DeleteFA permission
	if($canDeleteFA==1){
		$toreturn .= "<a href=\"#\" 
		onclick=\"confirmUrlEvent('Confirm Delete?' , './deleteFA.php?faDisbursementID=".$arr["faDisbursementID"]."');\"
		>Delete</a><br>";
	}	
	$toreturn .= "</td>";

	$toreturn .= "</tr>";
	return $toreturn;
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
    	<div class="pageTitle">View Financial Application</div>
        <div class="linkButton"><a href="./searchFA.php">Back to Search Financial Application</a></div>
        <div class="linkButton"><a href="./createFA-application.php?faID=<?php echo $faID; ?>">Create Application</a></div>
        <div class="linkButton"><a href="./createFA-disbursement-stg2.php?nric=<?php echo tryGetNRICfromFAID($faID); ?>">Create Disbursement</a></div>
        
        <div class="errorMsg">
            	<?php echo $errorMsg; ?>
            </div>
        <div>
			<?php echo $viewOutput; ?>
    	</div>
    <div>
    	
		<div class="contentBtm">LSBC Financial Application Management System v1.0 @ 2014</div>
	
</div>	

<?php

?>

</body>
</html>