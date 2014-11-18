/*
* LSBC Financial Application Management System
* Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
* File : jsfunctions.js
* Author : Cheng Gibson
* Version : v1.0
*
* This files stores the necessary Javascript functions required by the Application's PHP pages.
*/

// Pops up a confirmation event and ON TRUE brings the user to the input URL
function confirmUrlEvent(msg,url){
	if(confirm(msg)){
		redirectToURLBackend(url);
	}
}

// Sets a HTML element to be empty
function makeElementEmpty($element){
	document.getElementById($element).value="";
}
// Sets a HTML element to the input value
function setElementValue($element,$elementValue){
	document.getElementById($element).value=$elementValue;
}
// Retrieves a HTML element's value
function getElementValue($element){
	return document.getElementById($element).value;
}

// Redirects users to the input URL
function redirectToURLBackend($url){
	 window.location = $url;
}
// Redirects users to the input URL in a determined amount of delay by the Time input (in seconds)
function redirectToURL($url,$time){
	setTimeout("redirectToURLBackend('"+$url+"')", $time*1000);
}