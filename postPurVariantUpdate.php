<?php
/*
	File:		postPurVariantUpdate.php
	Purpose:	Update pur_variant in database.
*/

session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'db_functions.php';

if(isset($_POST['purvariant']))
{
	$purvariant = json_decode($_POST['purvariant'], true);
	$result = dbUpdatePurVariant($purvariant);
	if(!$result)
		echo json_encode("NO");
	else
		echo json_encode("OK");
}
else
	echo json_encode("NO");

?>
