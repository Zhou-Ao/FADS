<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="./../backend/cssstyle.css">
<script type="text/javascript" src="./../backend/jsfunctions.js"></script>
<script type="text/javascript">
// Applies page variables into form inputs
function setFormValues(){
	setElementValue('name',name);
	setElementValue('minAmount',minAmount);
	setElementValue('maxAmount',maxAmount);
	setElementValue('startDate',startDate);
	setElementValue('endDate',endDate);
}
</script>
<title>LSBC - Generate Report</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : generateReport.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Generate Report functionality of the Application
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
// Checks if user has GenerateReport permission
if(!$_SESSION['canGenerateReport']){
	header("refresh:0.001;url=./home.php");
	return;
}

//errorCode
//1: invalidFAData :
//2: invalidNRIC : 
$errCode = 0;

//LOCAL variables
$name = "";
$minAmount= "";
$maxAmount= "";
$startDate= "";
$endDate= "";

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	tryAssignInputVars();
	tidyInputs();
	dumpVarsToJS();
}
pageLogic();

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global $name,$minAmount,$maxAmount, $startDate,$endDate;
	//assigns input variables from the form into local variables
	$toreturn = true;
	
	if(isset($_GET['name'])) { $name =  $_GET['name']; }
	if(isset($_GET['minAmount'])) { $minAmount= $_GET['minAmount'];}
	if(isset($_GET['maxAmount'])) { $maxAmount = $_GET['maxAmount']; }
	if(isset($_GET['startDate'])) {$startDate = $_GET['startDate']; }
	if(isset($_GET['endDate'])) { $endDate =$_GET['endDate'];}
	
	return $toreturn;
}
// Removes illegal characters from user inputs
function tidyInputs(){
	global $name,$minAmount,$maxAmount, $startDate,$endDate;
	//uses regular expressions to prevent injections
	$name = preg_replace('/[^a-zA-Z ]/','',$name);
	$minAmount = preg_replace('/[^0-9.]/','',$minAmount);
	$maxAmount = preg_replace('/[^0-9.]/','',$maxAmount);
	$startDate = preg_replace('/[^0-9-]/','',$startDate);
	$endDate = preg_replace('/[^0-9-]/','',$endDate);
}
// Loads user inputs into Javascript variables
function dumpVarsToJS(){
	global $name,$minAmount,$maxAmount, $startDate,$endDate;
	$jscommand = "<script type=\"text/javascript\">";
	$jscommand .= "name='".$name."';";
	$jscommand .= "minAmount='".$minAmount."';";
	$jscommand .= "maxAmount='".$maxAmount."';";
	$jscommand .= "startDate='".$startDate."';";
	$jscommand .= "endDate='".$endDate."';";
	$jscommand .= "</script>";
	echo $jscommand;
}
// Displays report based on filters
function displaySearchResults(){
	//possibly can ensure at least one search parameter, doesn't make sense to search with no parameters (will return whole database)
	$boolIsValidSearch = true;
	if(!$boolIsValidSearch){
		echo "No Results Found.";
		return;	
	}
	
	$command = createSQLStatement($boolIsValidSearch);
	//echo $command."<br/><br/>";
	$htmlcode = "";
	
	initializeDB();
	$result = $_SESSION['connection']->query($command);
	if($result->num_rows==0){
		echo "No Results Found.";
		return;	
	}
	
	$htmlcode .= "<table class=\"generatedReport\">";
	
	$htmlcode .= "<tr><td colspan=\"3\" class=\"rowTitle1\">Report Results</td></tr>";
	
	
	$totalAmtPerson = 0;
	$totalAmtPeriod = 0;
	
	$tempFAID = -1;
	$tempFAApplicationID = -1;
	$tempFADisbursementID = -1;
	$rowNum=0;
	$numRows = $result->num_rows;
	
	while($row = $result->fetch_assoc()){
		$rowNum++;
		if($tempFAID!=$row["faID"]){
			//check if new FAID	
			$tempFAID=$row["faID"];
			
			if($rowNum!=1){
				$htmlcode .= "<tr><td colspan=\"2\" class=\"rowTitle3\">Total Amount for Person</td><td class=\"rowSubtotal rowTitle2\">".
				$totalAmtPerson."</td></tr>";
				$htmlcode .= "<tr><td colspan=\"3\"></td></tr>";
				$totalAmtPerson = 0;
			}
			
			$htmlcode .= buildDetailRow($row);
		} 
		if($tempFAApplicationID!=$row["faApplicationID"]){
			//check if new FAApplicationID	
			$tempFAApplicationID=$row["faApplicationID"];
			$htmlcode .= buildApplicationRow($row);
			$htmlcode .= buildDisbursementRow($row,true);
		} else {
			$htmlcode .= buildDisbursementRow($row,false);
		}
		
		$totalAmtPerson += $row["amount"];
		$totalAmtPeriod += $row["amount"];
		
		if($rowNum==$numRows){
			$htmlcode .= "<tr><td colspan=\"2\" class=\"rowTitle2\">Total Amount for Person</td><td class=\"rowSubtotal rowTitle2\">".$totalAmtPerson."</td></tr>";
			$htmlcode .= "<br />";
			$totalAmtPerson = 0;
		}
	}
	
	$htmlcode .= "<tr><td colspan=\"3\"></td></tr>";
	$htmlcode .= "<tr><td colspan=\"2\" class=\"rowTitle2\">Total Amount for Period</td><td class=\"rowSubtotal rowTitle2\">".$totalAmtPeriod."</td></tr>";
			
	$htmlcode .= "</table>";		
	
	echo $htmlcode;
}
// Displays FADetail information based on row input
function buildDetailRow($row){
	$toreturn = "";
	
	/*
	$toreturn .= "<tr class=\"rowTitle rowFADetail\">";
	$toreturn .= "<td colspan=\"3\">Name</td>";
	$toreturn .= "</tr>";
	*/ 
	
	$toreturn .= "<tr class=\"rowFADetail\">";
	//$toreturn .= "<td><a href=\"./viewFA.php?faID=".$row["faID"]."\" >View</a></td>&nbsp;";
	$toreturn .= "<td colspan=\"3\" class=\"rowTitle4\">".$row["firstName"]." ";
	$toreturn .= "".$row["lastName"]."</td>&nbsp;";
	$toreturn .= "</tr>";	

	return $toreturn;
}
// Displays FAApplication information based on row input
function buildApplicationRow($row){
	$toreturn = "";
	
	$toreturn .= "<tr class=\"rowFAApplication\">";
	$toreturn .= "<td class=\"rowTitle2\">Start Date</td><td class=\"rowTitle2\">End Date</td><td class=\"rowTitle2\">Total Amt</td>";
	$toreturn .= "</tr>";
	
	//date("d-m-Y", strtotime($date))
	
	$toreturn .= "<tr class=\"rowFAApplication\">";
	$toreturn .= "<td class=\"rowTitle4\">".$row["startDate"]."</td>";
	$toreturn .= "<td class=\"rowTitle4\">".$row["endDate"]."</td>";
	$toreturn .= "<td class=\"rowTitle4\">".$row["totalAmtDisbursed"]."</td>";

	$toreturn .= "</tr>";
	
	return $toreturn;
}
// Displays FADisbursement information based on row input
function buildDisbursementRow($row,$printTitle){
	$toreturn = "";
	if($printTitle){
		$toreturn .= "<tr class=\"rowFADisbursement\">";
		$toreturn .= "<td colspan=\"2\" class=\"rowTitle2\">Date Disbursed</td><td class=\"rowTitle2\">Amt</td>";
		$toreturn .= "</tr>";
	}
	
	$toreturn .= "<tr class=\"rowFADisbursement\">";
	$toreturn .= "<td colspan=\"2\" class=\"rowTitle4\">".$row["dateDisbursed"]."</td>";
	$toreturn .= "<td class=\"rowTitle4\">".$row["amount"]."</td>";
	$toreturn .= "</tr>";
	
	return $toreturn;
}
// Creates the SQL statement depending on filters used
function createSQLStatement(){
	global $name,$minAmount,$maxAmount, $startDate,$endDate;
	
	$command = "SELECT FADetails.faID, FADetails.firstName, FADetails.lastName, 
	FAApplication.faApplicationID, FAApplication.startDate, FAApplication.endDate, FAApplication.totalAmtDisbursed,
	FADisbursement.dateDisbursed, FADisbursement.amount
	FROM FADisbursement LEFT JOIN FAApplication ON FADisbursement.faApplicationID=FAApplication.faApplicationID
	LEFT JOIN FADetails ON FAApplication.faID=FADetails.faID
	WHERE FADetails.faID IS NOT NULL
	";
	//faDetails.faID IS NOT NULL is just a filler
	
	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		//stores the dynamic command of variables

		$subcommand = "";
		if($name!=""){
			$subcommand.=" AND ( FADetails.firstName LIKE '%".$name."%' OR ";
			$subcommand.=" FADetails.lastName LIKE '%".$name."%' )";
		}
		if($minAmount!=""){
			$subcommand.=" AND FADisbursement.amount >= ".$minAmount."";
		}
		if($maxAmount!=""){
			$subcommand.=" AND FADisbursement.amount <= ".$maxAmount."";
		}
		if($startDate!=""){
			$subcommand.=" AND FADisbursement.dateDisbursed >= '".$startDate."'";
		}
		if($endDate!=""){
			$subcommand.=" AND FADisbursement.dateDisbursed <= '".$endDate."'";
		}
		
		$command .= $subcommand;
	}
	
	$command .= ";";
	
	return $command;
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
    	<div class="pageTitle">Generate Report</div>
    	
        <div style="float:left;margin-right:5%;">
    	<form method='GET'><table class="formTemplate">
        	<tr><td colspan="2" class="rowTitle1">Filters</td></tr>
            <input type="hidden" name="isProcessGenerateReport" value="1"/>
            
            <tr><td colspan="2" class="rowTitle2">Details</td></tr>
            <tr><td class="rowTitle3">
            Name
            </td><td class="rowTitle3">
            <input type="text" name="name" id="name" width="" value="" pattern="[a-zA-Z ]*"/>
            </td></tr>
            
            <!--
            <br />FAApplication<br />
            minAmount<input type="text" name="minAmount" id="minAmount" width="" size="" value=""/><br />
            maxAmount<input type="text" name="maxAmount" id="maxAmount" width="" size="" value=""/><br />
            startDate<input type="date" name="startDate" id="startDate" width="" size="" value=""/><br />
            endDate<input type="date" name="endDate" id="endDate" width="" size="" value=""/><br />
            -->
            
            <tr><td colspan="2" class="rowTitle2">Disbursements</td></tr>
            <tr><td class="rowTitle3">
            Min. Amount
            </td><td class="rowTitle3">
            <input type="text" name="minAmount" id="minAmount" width="" size="" value=""  pattern="([0-9]*)|([0-9]+.[0-9]+)"/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Max Amount
            </td><td class="rowTitle3">
            <input type="text" name="maxAmount" id="maxAmount" width="" size="" value="" pattern="([0-9]*)|([0-9]+.[0-9]+)"/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Start Date
            </td><td class="rowTitle3">
            <input type="date" name="startDate" id="startDate" width="" size="" value=""/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            End Date
            </td><td class="rowTitle3">
            <input type="date" name="endDate" id="endDate" width="" size="" value=""/>
            </td></tr>
            
            <tr><td colspan="2" class="rowTitle3">
            <input type="submit" />
            </td></tr>
            
        </table></form>
        </div>
        
    	
        <div style="float:left;margin-right:5%;">
    	<?php 
		if(isset($_GET['isProcessGenerateReport'])){
			$errCode = 1;	
			displaySearchResults(); 
		}
		?>
        </div>
        
    </div>
    
    <div class="contentBtm">LSBC Financial Application Management System v1.0 @ 2014</div>
	
</div>	

<?php
//insert logic to trigger error messages
//errorCode
//1: invalidFAData : 
//2: invalidNRIC :
//jsmsg($errCode); 
if($errCode==1){
	echo "<script type=\"text/javascript\">setFormValues();</script>";
} 
?>

</body>
</html>