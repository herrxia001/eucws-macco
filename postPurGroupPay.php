<?php
/*****************************************************************************
	File:		postPurGroupPay.php

*****************************************************************************/

session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'db_functions.php';

if(!isset($_POST['isPayed']) || !isset($_POST['pid']) ) {
	echo json_encode("NO");
	return;
}

$result = dbUpdatePurGroupPay($_POST['pid'], $_POST['isPayed']);
if(!$result)
	echo json_encode("NO");
else
	echo json_encode("OK");