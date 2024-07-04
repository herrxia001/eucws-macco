<?php
/* 	
	File:		getInvs0.php
	Purpose: 	Query all inventories.
	Return: 	All inventories.
*/
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'db_functions.php';

$inv = dbGetInvs0();
if($inv < 0)
	echo json_encode("NO");
else		
	echo json_encode($inv);

?>
