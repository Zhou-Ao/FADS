<?php
/*
 * LSBC Financial Application Management System
 * Developed by NTU BC2402 AY1314S1, Chen Tao, Cheng Gibson, Kok Tze How, Xu Qianqian
 * File : index.php
 * Author : Cheng Gibson
 * Version : v1.0
 *
 * This file redirects errant users to the Login page
 */

// Redirects users to the login page
header ( "refresh:0.0001;url=./webcode/login.php" );
?>