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
	setElementValue('name',name);
	setElementValue('handphoneNum',handphoneNum);
	setElementValue('minAmount',minAmount);
	setElementValue('maxAmount',maxAmount);
	setElementValue('startDate',startDate);
	setElementValue('endDate',endDate);
}
</script>
<title>LSBC - Search Financial Application</title>
<link rel="icon" type="image/png" href="./img/web/webIcon.gif" />
</head>

<body>
<?php
/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : searchFA.php
* Author : Cheng Gibson
* Version : v1.0
*
* This file provides the Search Financial Application functionality of the Application
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
// Checks if user has SearchFA permission
if(!$_SESSION['canSearchFA']){
	header("refresh:0.0001;url=./home.php");	
	return;
}

//errorCode
//1: invalidFAData :
//2: invalidNRIC : 
$errCode = 0;

//LOCAL variables
$nric = "";
$name = "";
$handphoneNum = "";
$minAmount = "";
$maxAmount = "";
$startDate = "";
$endDate = "";

//pageLogic runs the logical functionalities of the page
function pageLogic(){
	tryAssignInputVars();
	tidyInputs();
	dumpVarsToJS();
}
pageLogic();

// Assigns user inputs to Page Variables
function tryAssignInputVars(){
	global $nric,$name,$handphoneNum,$minAmount,$maxAmount,$startDate,$endDate;
	//assigns input variables from the form into local variables
	$toreturn = true;
	
	if(isset($_GET['nric'])) { $nric =  $_GET['nric']; }
	if(isset($_GET['name'])) { $name =  $_GET['name']; }
	if(isset($_GET['handphoneNum'])) { $handphoneNum  = $_GET['handphoneNum']; }
	if(isset($_GET['minAmount'])) { $minAmount= $_GET['minAmount'];}
	if(isset($_GET['maxAmount'])) { $maxAmount = $_GET['maxAmount']; }
	if(isset($_GET['startDate'])) {$startDate = $_GET['startDate']; }
	if(isset($_GET['endDate'])) { $endDate =$_GET['endDate'];}
	
	return $toreturn;
}
// Removes illegal characters from user inputs
function tidyInputs(){
	global $nric,$name,$handphoneNum,$minAmount,$maxAmount,$startDate,$endDate;
	
	//uses regular expressions to prevent injections
	$nric = preg_replace('/[^a-zA-Z0-9]/','',$nric);
	$name = preg_replace('/[^a-zA-Z ]/','',$name);
	$minAmount = preg_replace('/[^0-9.]/','',$minAmount);
	$maxAmount = preg_replace('/[^0-9.]/','',$maxAmount);
	$handphoneNum = preg_replace('/[^0-9]/','',$handphoneNum);
	$startDate = preg_replace('/[^0-9-]/','',$startDate);
	$endDate = preg_replace('/[^0-9-]/','',$endDate);
}
// Loads user inputs into Javascript variables
function dumpVarsToJS(){
	global $nric,$name,$handphoneNum,$minAmount,$maxAmount,$startDate,$endDate;
	
	$jscommand = "<script type=\"text/javascript\">";
	$jscommand .= "nric='".$nric."';";
	$jscommand .= "name='".$name."';";
	$jscommand .= "minAmount='".$minAmount."';";
	$jscommand .= "maxAmount='".$maxAmount."';";
	$jscommand .= "handphoneNum='".$handphoneNum."';";
	$jscommand .= "startDate='".$startDate."';";
	$jscommand .= "endDate='".$endDate."';";
	$jscommand .= "</script>";
	
	echo $jscommand;
}

// Displays FA search results based on filters
function displaySearchResults(){
	// Checks if user is searching for FAApplication-related filters
	$boolIsSearchFAApplication = isSearchFAApplication();
	
	// Creates SQL statement
	$command = createSQLStatement($boolIsSearchFAApplication);
	
	$htmlcode = "";
	
	// Initializes database connection
	initializeDB();
	
	$result = $_SESSION['connection']->query($command);
	if($result->num_rows==0){
		echo "No Results Found.";
		return;	
	}
	
	$htmlcode .= "<table class=\"generatedReport\">";
	
	$htmlcode .= "<tr><td colspan=\"3\" class=\"rowTitle1\">Results</td></tr>";
	if($boolIsSearchFAApplication){
		// User has used FAApplication-related filters
		$tempFAID = -1;
		// Iterates through all search results
		while($row = $result->fetch_assoc()){
			// Checks if new FADetail (new person)
			if($tempFAID!=$row["faID"]){
				$htmlcode .= "<tr><td colspan=\"3\"></td></tr>";
				//check if new FAID	
				$tempFAID=$row["faID"];
				// Prints out FADetails
				$htmlcode .= buildDetailRow($row);
				// Prints out FAApplication, with Header
				$htmlcode .= buildApplicationRow($row,true);	
			} else {
				// Prints out FAApplication, without Header
				$htmlcode .= buildApplicationRow($row,false);		
			}
		}
	} else {
		// User did not use FAApplication-related filters
		// Iterates through all search results
		while($row = $result->fetch_assoc()){
			// Prints out FADetails
			$htmlcode .= buildDetailRow($row);
			$htmlcode .= "<tr><td colspan=\"3\" style=\"border:0px none;\"></td></tr>";
		}
	}
	$htmlcode .= "</table>";		
	
	echo $htmlcode;
}
// Displays FADetail information based on row input
function buildDetailRow($row){
	global $nric,$name,$handphoneNum,$minAmount,$maxAmount,$startDate,$endDate;
	
	$toreturn = "<tr>";
	$toreturn .= "<td class=\"rowTitle2\">NRIC</td>";
	$toreturn .= "<td class=\"rowTitle2\">First Name</td>";
	$toreturn .= "<td class=\"rowTitle2\">Last Name</td>";
	if($handphoneNum!=""){
		$toreturn .= "<td class=\"rowTitle2\">Handphone Num</td>";
	}
	$toreturn .= "</tr>";
	
	$toreturn .= "<td class=\"rowTitle4\">".$row["nric"]."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$row["firstName"]."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$row["lastName"]."</td>&nbsp;";
	if($handphoneNum!=""){
		$toreturn .= "<td class=\"rowTitle4\">".$row["handphoneNum"]."</td>&nbsp;";
	}
	$toreturn .= "<td><a href=\"./viewFA.php?faID=".$row["faID"]."\">Edit</a></td>&nbsp;";
	
	$toreturn .= "</tr>";
	
	
	
	return $toreturn;
}
// Displays FAApplication information based on row input
function buildApplicationRow($row,$printHeaderRow){
	$toreturn = "<tr>";
	
	if($printHeaderRow){
	//to prevent duplicate headers
		$toreturn .= "<td class=\"rowTitle2\">Start Date</td>";
		$toreturn .= "<td class=\"rowTitle2\">End Date</td>";
		$toreturn .= "<td class=\"rowTitle2\">Total Amt Disbursed</td>";
		$toreturn .= "</tr>";	
	}
	
	$toreturn .= "<td class=\"rowTitle4\">".$row["startDate"]."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$row["endDate"]."</td>&nbsp;";
	$toreturn .= "<td class=\"rowTitle4\">".$row["totalAmtDisbursed"]."</td>&nbsp;";
	//$toreturn .= "<td class=\"rowTitle4\">".$row["faID"]."</td>&nbsp;";
	
	$toreturn .= "</tr>";
	return $toreturn;
}

// Creates the SQL statement depending on filters used
function createSQLStatement($boolIsSearchFAApplication){
	global $nric,$name,$handphoneNum,$minAmount,$maxAmount,$startDate,$endDate;
	
	// If is FAApplication-related search, retrieves FAApplication details. Else, just retrieves from FADetails
	if($boolIsSearchFAApplication){
		$command = "SELECT 
		FADetails.faID, FADetails.nric, FADetails.firstName, FADetails.lastName, FADetails.handphoneNum,
		FAApplication.startDate, FAApplication.endDate, FAApplication.totalAmtDisbursed
		 
		FROM FADetails LEFT JOIN FAApplication ON FAApplication.faID = FADetails.faID
		WHERE FADetails.faID IS NOT NULL";
		//faDetails.faID IS NOT NULL is just a filler
	} else {
		$command = "SELECT 
		FADetails.faID, FADetails.nric, FADetails.firstName, FADetails.lastName, FADetails.handphoneNum
		 
		FROM FADetails
		WHERE FADetails.faID IS NOT NULL";
		//faDetails.faID IS NOT NULL is just a filler
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'GET'){
		//stores the dynamic command of variables		
		$subcommand = "";
		if($nric!=""){
			$subcommand.=" AND FADetails.nric LIKE '%".$nric."%'";
		}
		if($name!=""){
			$subcommand.=" AND ( FADetails.firstName LIKE '%".$name."%' OR ";
			$subcommand.=" FADetails.lastName LIKE '%".$name."%' )";
		}
		if($minAmount!=""){
			$subcommand.=" AND FAApplication.totalAmtDisbursed >= ".$minAmount."";
		}
		if($maxAmount!=""){
			$subcommand.=" AND FAApplication.totalAmtDisbursed <= ".$maxAmount."";
		}
		if($handphoneNum!=""){
			$subcommand.=" AND FADetails.handphoneNum LIKE '%".$handphoneNum."%'";
		}
		if($startDate!=""){
			$subcommand.=" AND FAApplication.startDate >= '".$startDate."'";
		}
		if($endDate!=""){
			$subcommand.=" AND FAApplication.endDate <= '".$endDate."'";
		}
				
		$subcommand .= " ORDER BY FADetails.faID ASC";
		$command .= $subcommand;
	}
	
	$command .= ";";
	
	return $command;
}
// Checks if user is using any FAApplication filters
function isSearchFAApplication(){
	global $nric,$name,$handphoneNum,$minAmount,$maxAmount,$startDate,$endDate;
	
	if($startDate!="" || $endDate!="" || $minAmount!="" || $maxAmount!=""){
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
    	<div class="pageTitle">Search Financial Application</div>
    
    	<div style="float:left;margin-right:5%;">
    	<form method='GET'><table class="formTemplate">
       		<input type="hidden" name="isProcessSearchFA" value="1"/>
            
        	<tr><td colspan="2" class="rowTitle1">Filters</td></tr>
            
            <tr><td colspan="2" class="rowTitle2">Details</td></tr>
            <tr><td class="rowTitle3">
            NRIC
            </td><td class="rowTitle3">
            <input type="text" name="nric" id="nric" width="" size="" value="" pattern="[a-zA-Z0-9]"/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Name
            </td><td class="rowTitle3">
            <input type="text" name="name" id="name" width="" value=""/ pattern="[a-zA-Z ]*"><br />
            </td></tr>
            
            <tr><td class="rowTitle3">
            Handphone Num
            </td><td class="rowTitle3">
            <input type="number" name="handphoneNum" id="handphoneNum" width="" size="" maxlength="8" value="" pattern="[0-9]{8}*"/>
            </td></tr>
            
            <tr><td colspan="2" class="rowTitle2">Applications</td></tr>
            
            <tr><td class="rowTitle3">
            Min. Amount Disbursed
            </td><td class="rowTitle3">
            <input type="text" name="minAmount" id="minAmount" width="" size="" value="" pattern="([0-9]*)|([0-9]+.[0-9]+)"/>
            </td></tr>
            
            <tr><td class="rowTitle3">
            Max. Amount Disbursed
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
        
        <div style="float:left;">
             <?php
                    if(isset($_GET['isProcessSearchFA'])){
                        $errCode = 1;
                        displaySearchResults(); 
                    }
            ?>
        </div>
	</div>
    
    <div class="contentBtm">LSBC Financial Application Management System v1.0 @ 2014</div>
	
</div>		

<?php
if($errCode==1){
	echo "<script type=\"text/javascript\">setFormValues();</script>";
}
?>

</body>
</html>