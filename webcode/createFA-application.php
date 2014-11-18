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
}
</script>
<title>LSBC - Create Financial Application - Application</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : createFA-application.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Create FA - Application functionality of the Application
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

$pageState = 0;
$errorMsg = "";

//PAGE INPUT variables
$nric = "";
$type = "";
$description = "";
$startDate = "";
$endDate = "";
$dateApplied = "";
$isApproved = "";
$faVetter = "";
$totalAmtApproved = "";

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	global $nric,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved;
	global $pageState,$errorMsg;
	
	tryAssignInputVars();
	$pageState = getPageState();
	
	//PAGE STATE
	//0: Try Assign NRIC
	//1: Create Entry, Invalid NRIC
	//2: Create Entry, Invalid Data
	//2.5: Create Entry, Invalid Data, Date Collision
	//3: Create Entry, Valid Data
	//4: Just entered page
	//-1: Invalid State
	if($pageState==0){
		//0: Try Assign NRIC
		//add permissions check here
		if(true){
			$nric = tryGetNRICfromFAID($_GET['faID']);
			if($nric==-1) {
				$nric="";
			}
			dumpVarsToJS();
		}
		return;
	} else if($pageState==1){
		//1: Create Entry, Invalid NRIC
		dumpVarsToJS();
		$errorMsg = "Invalid NRIC";
		return;
	} else if($pageState==2) {
		//2: Create Entry, Invalid Data
		dumpVarsToJS();
		$errorMsg = "Empty Field(s)!";
		return;
	} else if($pageState==2.2) {
		//2.5: Create Entry, Invalid Data, End Date Less than Start Date
		dumpVarsToJS();
		return;
		//errorMsg is appended inside the pagestate
	} else if($pageState==2.5) {
		//2.5: Create Entry, Invalid Data, Date Collision
		dumpVarsToJS();
		return;
		//errorMsg is appended inside the pagestate
	} else if($pageState==3) {
		//3: Create Entry, Valid Data
		dumpVarsToJS();
		$faID = 0;
		$faID = tryGetFAIDfromNRIC($nric);
		createEntryFAApplication($faID);
		$errorMsg = "Creation Successful!";
	} else if($pageState==4){
		dumpVarsToJS();
		return;
	}
}
pageLogic();

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global $nric,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved;
	$toreturn = true;
	
	if(isset($_POST['nric'])) { $nric =  $_POST['nric']; }
	if(isset($_POST['type'])) { $type =  $_POST['type']; }
	if(isset($_POST['description'])) { $description  = $_POST['description']; }
	if(isset($_POST['startDate'])) {$startDate = $_POST['startDate']; }
	if(isset($_POST['endDate'])) { $endDate =$_POST['endDate'];}
	if(isset($_POST['dateApplied'])) { $dateApplied= $_POST['dateApplied'];}
	if(isset($_POST['isApproved'])) { $isApproved = $_POST['isApproved']; }
	if(isset($_POST['faVetter'])) { $faVetter = $_POST['faVetter']; }
	if(isset($_POST['totalAmtApproved'])) { $totalAmtApproved = $_POST['totalAmtApproved']; }
	
	return $toreturn;
}
// Determines page state based on user inputs
function getPageState(){
	//0: Try Assign NRIC
	//1: Create Entry, Invalid NRIC
	//2: Create Entry, Invalid Data
	//3: Create Entry, Valid Data
	//4: Just entered page
	//-1: Invalid State
	global $nric,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved;
	global $errorMsg;
	
	$toreturn = 0;
	
	tidyInputs();
	
	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		//0: Try Assign NRIC
		if(isset($_GET['faID'])){
			return 0;
			//try get NRIC from faID
		} else {
			//4: Just entered page
			return 4;	
		}
	}
	
	//1: Create Entry, Invalid NRIC
	$faID = 0;
	//check if valid NRIC
	$faID = tryGetFAIDfromNRIC($nric);
	if($faID==-1){
		return 1;	
	}
	if(!checkValidNRIC($nric)){
		return -1;
	}
	
	//2: Create Entry, Invalid Data
	if($nric=="" || $type=="" || $description=="" || $startDate=="" || $endDate=="" || $dateApplied=="" || $isApproved=="" || $faVetter=="" || $totalAmtApproved==""){
		//essential fields are empty
		return 2;
	}
	
	if(!checkStartDateEndDateValidRange($startDate,$endDate)){
		$errorMsg .= "End Date cannot be less than Start Date.";
		return 2.2;
	}
	
	//check for date collisions (no overlaps)
	if(checkDateCollision($faID,$startDate,$endDate)){
		$errorMsg .= "Current Start Date and/or End Date overlaps with Existing application(s). Please select another date.";
		return 2.5;
	}
	
	//Valid Inputs
	return 3;
}
// Removes illegal characters from user inputs
function tidyInputs(){
	global $nric,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved;
	
	$nric = preg_replace('/[^a-zA-Z0-9]/','',$nric);
	$type = preg_replace('/[^a-zA-Z0-9]/','',$type);
	$description = preg_replace('/[^a-zA-Z0-9 -_.,#]/','',$description);
	$startDate = preg_replace('/[^0-9-]/','',$startDate);
	$endDate = preg_replace('/[^0-9-]/','',$endDate);
	$dateApplied = preg_replace('/[^0-9-]/','',$dateApplied);
	$isApproved = preg_replace('/[^01]/','',$isApproved);
	$faVetter = preg_replace('/[^a-zA-Z0-9 ]/','',$faVetter);
	$totalAmtApproved = preg_replace('/[^0-9.]/','',$totalAmtApproved);
	
	if(isset($_GET['faid'])) {
		$_GET['faid'] = preg_replace('/[^0-9]/','',$_GET['faid']);
	}
}
// Loads user inputs into Javascript variables
function dumpVarsToJS(){
	global $nric,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved;
	
	$jscommand = "<script type=\"text/javascript\">";
	$jscommand .= "nric='".$nric."';";
	$jscommand .= "type='".$type."';";
	$jscommand .= "description='".$description."';";
	$jscommand .= "startDate='".$startDate."';";
	$jscommand .= "endDate='".$endDate."';";
	$jscommand .= "dateApplied='".$dateApplied."';";
	$jscommand .= "isApproved='".$isApproved."';";
	$jscommand .= "faVetter='".$faVetter."';";
	$jscommand .= "totalAmtApproved='".$totalAmtApproved."';";
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
	$command = $_SESSION['connection']->prepare("SELECT faID FROM FADetails WHERE nric=?;");
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
// Checks to ensure StartDate > EndDate
function checkStartDateEndDateValidRange($startDate,$endDate){
	$startDate = date('m-d-Y',strtotime($startDate));
	$endDate = date('m-d-Y',strtotime($endDate));
	
	if($startDate>$endDate){
		return false;
	} 
	
	return true;
}
// Checks to ensure date range does not overlap with any other existing faApplications
function checkDateCollision($faID,$startDate,$endDate){
	//since local variable, can just modify without worrying about global vars
	$startDate = date('m-d-Y',strtotime($startDate));
	$endDate = date('m-d-Y',strtotime($endDate));
	
	//gets all faApplications from faID
	initializeDB();
	$command = $_SESSION['connection']->prepare("SELECT startDate,endDate FROM FAApplication WHERE faID=?;");
	$command->bind_param('i',$faID);
	
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

// Creates FAApplication record
function createEntryFAApplication($faID){
	global $nric,$type,$description,$startDate,$endDate,$dateApplied,$isApproved,$faVetter,$totalAmtApproved;
	$imgLoc = '';
	
	initializeDB();
	$command = $_SESSION['connection']->prepare("INSERT INTO FAApplication VALUES(NULL,?,?,?,?,?,?,?,?,?,?,0);");
	$command->bind_param('issssssisd',$faID,$type,$description,$startDate,$endDate,$dateApplied,$imgLoc,$isApproved,$faVetter,$totalAmtApproved);
	
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
    	<div class="pageTitle">Create Financial Application - Application</div>
        <div>
			<form method='POST'><table class="formTemplate">
            <tr><td colspan="2" class="rowTitle2">Details</td></tr>
            <tr><td class="rowTitle3">
            NRIC*
            </td><td class="rowTitle3">
            <input type="text" name="nric" id="nric" width="" size="" value="" pattern="[Ss]{1}[0-9]{7}[a-iA-IzZjJ]{1}" required/>
            </td></tr>
            
            <tr><td colspan="2" class="rowTitle2"></td></tr>
            
            <tr><td colspan="2" class="rowTitle2">Application Details</td></tr>
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
//0: Try Assign NRIC
//1: Create Entry, Invalid NRIC
//2: Create Entry, Invalid Data
//3: Create Entry, Valid Data
//4: Just entered page
//-1: Invalid State
echo "<script type=\"text/javascript\">setFormValues();</script>";

?>

</body>
</html>