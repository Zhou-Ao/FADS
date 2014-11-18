<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="./../backend/cssstyle.css">
<script type="text/javascript" src="./../backend/jsfunctions.js"></script>
<script type="text/javascript">

</script>
<title>LSBC - Delete Record</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : deleteFA.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Delete FA functionality of the Application
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
// Checks if user has DeleteFA permission
if(!$_SESSION['canDeleteFA']){
	header("refresh:0.001;url=./home.php");
	return;
}

//errorCode
//1: Invalid Input Sequence
//2: Invalid Inputs
//3: Transaction Error 
$errCode = 0;

//PAGE INPUT variables
$faID = "";
$faApplicationID = "";
$faDisbursementID = "";

//!check page state
//sanitize inputs, ensure inputs are valid, delete entries, return to original page

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	global $faID,$faApplicationID,$faDisbursementID,$errCode;
	tryAssignInputVars();
	$pageState = getPageState();
	 		
	if($pageState==-1){
		//return to home page
		$errCode = 1;
		return;
	} 
	
	//echo "valid input(s)";
	
	if($pageState==1){
		//FAID
		if(tryGetFAIDfromFAID($faID)==-1){
			//echo "invalid faID";
			$errCode = 2;
			return;	
		}
		$txState = deleteFADetail($faID);
		if($txState==-1){
			//echo "transaction error in deleteFADetail";
			$errCode = 3;
			return;	
		}
		
		//echo "success";
		$errCode = 0;
		header("refresh:0.001;url=./searchFA.php");
	} else if($pageState==2){
		$faID = tryGetFAIDfromFAApplicationID($faApplicationID);
		if($faID==-1){
			//echo "invalid faApplicationID";
			$errCode = 2;
			return;	
		}
		$txState = deleteFAApplication($faApplicationID);
		if($txState==-1){
			//echo "transaction error in deleteFAApplication";
			$errCode = 3;
			return;	
		}
		//echo "success";
		$errCode = 0;
		header("refresh:0.001;url=./viewFA.php?faID=".$faID);
	} else if($pageState==3){
		$faID = tryGetFAIDfromFADisbursementID($faDisbursementID);
		if($faID==-1){
			//echo "invalid faDisbursementID";
			$errCode = 2;
			return;	
		}
		//delete disbursement
		$txState = deleteFADisbursement($faDisbursementID);
		if($txState==-1){
			//echo "transaction error in deleteFADisbursement";
			$errCode = 3;
			return;	
		}
		
		//echo "success";
		$errCode = 0;
		header("refresh:0.001;url=./viewFA.php?faID=".$faID);
	}
}
pageLogic();

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global $faID,$faApplicationID,$faDisbursementID;
	
	$toreturn = true;
	if(isset($_GET['faID'])) { $faID = $_GET['faID']; }
	if(isset($_GET['faApplicationID'])) { $faApplicationID = $_GET['faApplicationID']; }
	if(isset($_GET['faDisbursementID'])) { $faDisbursementID = $_GET['faDisbursementID']; }
	
	return $toreturn;
}
// Determines page state based on user inputs
function getPageState(){
	//PAGE STATE
	//1: delete Details (and Apps and Disbursements)
	//2: delete Application (and Disbursements)
	//3: delete Disbursement
	//-1 : invalid
	global $faID,$faApplicationID,$faDisbursementID;
	
	$toreturn = 0;
	//Check : Page request Method is set to GET
	if($_SERVER['REQUEST_METHOD'] != 'GET'){
			return -1;
	}
	
	//Check : Page variables 
	if($faID!="" && $faApplicationID=="" && $faDisbursementID==""){
		//faID
		if(!matchRegex($faID,"/^[1-9]{1}[0-9]*$/")){
			return -1;
		}
		$toreturn = 1;
	} else if($faID=="" && $faApplicationID!="" && $faDisbursementID==""){
		//faApplicationID
		if(!matchRegex($faApplicationID,"/^[1-9]{1}[0-9]*$/") ){
			return -1;
		}
		$toreturn = 2;
	} else if($faID=="" && $faApplicationID=="" && $faDisbursementID!=""){
		//faDisbursementID
		if(!matchRegex($faDisbursementID,"/^[1-9]{1}[0-9]*$/") ){
			return -1;
		}
		$toreturn = 3;
	} else {
		//improper array of inputs
		return -1;	
	}
	return $toreturn;
}
// Attempts to get faID from faID; returns -1 if fail
function tryGetFAIDfromFAID($faID){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT faID from FADetails WHERE faID=?;");
	$command->bind_param('i',$faID);
	if(!$command->execute()){
		echo "sql error in transaction";
		return -1;
	}
	$result = $command->get_result();
	if($result->num_rows!=1){
		return -1;
	} 
	$arr = $result->fetch_array(MYSQLI_NUM);
	return $arr[0];
}
// Attempts to get faID from faApplicationID; returns -1 if fail
function tryGetFAIDfromFAApplicationID($faApplicationID){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT faID from FAApplication WHERE faApplicationID=?;");
	$command->bind_param('i',$faApplicationID);
	if(!$command->execute()){
		echo "sql error in transaction";
		return -1;
	}
	$result = $command->get_result();
	if($result->num_rows!=1){
		return -1;
	} 
	$arr = $result->fetch_array(MYSQLI_NUM);
	return $arr[0];
}
// Attempts to get faID from faDisbursementID; returns -1 if fail
function tryGetFAIDfromFADisbursementID($faDisbursementID){
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT FAApplication.faID from FAApplication,FADisbursement WHERE FAApplication.faApplicationID=FADisbursement.faApplicationID AND faDisbursementID=?;");
	$command->bind_param('i',$faDisbursementID);
	if(!$command->execute()){
		echo "sql error in transaction";
		return -1;
	}
	$result = $command->get_result();
	if($result->num_rows!=1){
		return -1;
	} 
	$arr = $result->fetch_array(MYSQLI_NUM);
	return $arr[0];
}
// Deletes FA and all its dependencies
function deleteFADetail($faID){
	//delete FA detail and all dependencies
	//delete FAApplications and FADisbursements (iterate through)
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT faApplicationID FROM FAApplication WHERE faID=?");
	$command->bind_param('i',$faID);
	$command->execute();
	$result = $command->get_result();
	while($row = $result->fetch_assoc()){
		deleteFAApplication($row['faApplicationID']);
	}
	
	//delete FADetails
	initializeDB();
	$command = $_SESSION['connection']->prepare("DELETE FROM FADetails WHERE faID=?");
	$command->bind_param('i',$faID);
	$command->execute();
	
	return 0;
}
// Deletes FA Application
function deleteFAApplication($faApplicationID){
	//delete FAApplication and all dependencies
	//update isValid
	
	//delete FADisbursements (more efficient version, does not need to update FAApplication totalAmtDisbursed
	initializeDB();
	$command = $_SESSION['connection']->prepare("DELETE FROM FADisbursement WHERE faApplicationID=?");
	$command->bind_param('i',$faApplicationID);
	$command->execute();
	
	//delete FAApplication
	initializeDB();
	$command = $_SESSION['connection']->prepare("DELETE FROM FAApplication WHERE faApplicationID=?");
	$command->bind_param('i',$faApplicationID);
	$command->execute();
	
	return 0;
}
// Deletes FA Disbursement
function deleteFADisbursement($faDisbursementID){
	//delete fadisbursement
	//update total amount for faapplication	
	$amount = 0;
	$internalfaApplicationID = 0;
	
	//get amount for FADisbursement
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT amount,faApplicationID FROM FADisbursement WHERE faDisbursementID=?;");
	$command->bind_param('i',$faDisbursementID);
	$command->execute();
	$result = $command->get_result();
	if($result->num_rows!=1){
		return -1;
	}
	$arr = $result->fetch_array(MYSQLI_NUM);
	$amount = $arr[0];
	$internalfaApplicationID = $arr[1];
	
	//update FAApplication with correct totalAmtDisbursed
	initializeDB();
	$command = $_SESSION['connection']->prepare("UPDATE FAApplication SET totalAmtDisbursed=totalAmtDisbursed-? WHERE faApplicationID=?;");
	$command->bind_param('di',$amount,$internalfaApplicationID);
	$command->execute();
	
	//delete FADisbursement
	initializeDB();
	$command = $_SESSION['connection']->prepare("DELETE FROM FADisbursement WHERE faDisbursementID=?;");
	$command->bind_param('i',$faDisbursementID);
	$command->execute();
	
	return 0;
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
    	<div class="pageTitle">Delete Record</div>
        <div>
        	<?php 
				switch($errCode){
					 case 0:{
						 echo "Success! Redirecting...";
						 break;
					 }
					 default:{
						 echo "An error has occured. Redirecting...";
						 //header("refresh:2;url=./searchFA.php");
					 }
				}
			?>
        </div>
    </div>

	<div class="contentBtm">LSBC Financial Application Management System v1.0 @ 2014</div>
</div>	

</body>
</html>