<?php
/********************************************************************************
	File:		postInvPrint.php
	Purpose:	remove one pur and purr_items	
*********************************************************************************/ 

session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'db_functions.php';

if(isset($_POST['i_code']))
{
	$result = dbPrintInv($_POST['i_code'],$_POST['code'],$_POST['amount'],$_POST['variant']);

	if(!$result)
		echo json_encode("NO");
	else
		echo json_encode("OK");
}
else
	echo json_encode("NO");

?>