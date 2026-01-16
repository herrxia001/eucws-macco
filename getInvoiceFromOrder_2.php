<?php
/* 	
	File:		getInvoiceFromOrder.php
	Purpose: 	Query invoice created by order
	Return: 	invoice.
*/
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'db_invoice.php';

$myInvoices = dbQueryInvoiceByStatus("0");
if($myInvoices <= 0)
	echo json_encode("NO");
else		
	echo json_encode($myInvoices);	

?>
