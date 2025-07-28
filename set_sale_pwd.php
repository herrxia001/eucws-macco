<?php
/************************************************************************************
	File:		set_sale_pwd.php
	Purpose:	change pinter
************************************************************************************/
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'resource_'.$_SESSION['uLanguage'].'.php';
$thisResource = new myResource();
include_once 'db_functions.php';

if($_SERVER['REQUEST_METHOD'] == 'POST')
{	
	$thisDb = new myDatabase($root_db);
	$hashPwd = password_hash($_POST['sale_pwd_1'], PASSWORD_DEFAULT);
	$sqlUpdate = "UPDATE users SET u_password ='".$hashPwd."WHERE u_id ='2'";
	$thisDb->dbUpdate($sqlUpdate);
	$hashPwd = password_hash($_POST['sale_pwd_2'], PASSWORD_DEFAULT);
	$sqlUpdate = "UPDATE users SET u_password ='".$hashPwd."WHERE u_id ='3'";
	$thisDb->dbUpdate($sqlUpdate);
	$hashPwd = password_hash($_POST['sale_pwd_3'], PASSWORD_DEFAULT);
	$sqlUpdate = "UPDATE users SET u_password ='".$hashPwd."WHERE u_id ='4'";
	$thisDb->dbUpdate($sqlUpdate);
	header("Location:settings.php");	
}

?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>
	<title>EUCWS - Sales Pwd</title>
</head>
<style>
body {
 padding-top: 0rem;
}
</style>
<body>
<?php include 'include/nav.php' ?>
<br><br>
<br>
	<form action="" method="post">

    <div class="container">

	<div class="row mb-2">
		<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-2" style="background-color: DarkSlateGrey">
			<a class="btn" href="settings.php" role="button"><span style="color:white" class='fa fa-arrow-left'></span></a>
		</div>
		<div class="p-1 col-8 col-sm-8 col-md-8 col-lg-4"  style="background-color: DarkSlateGrey" align="center"> 
			<a style="color: white; font-weight: bold">销售端密码更改</a>
		</div>
		<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-2" style="background-color: DarkSlateGrey" align="right">
			<button type="submit" name="ok" class="btn"><span style="color:white" class='fa fa-check'></span></button>
		</div>
	</div>
	
	<div class="row">
		<div class="p-1 input-group col-12 col-sm-12 col-md-12 col-lg-8">
			<div class="input-group-prepend"><span class="input-group-text" style="width:140px;">销售1(macco_sale_1)</span></div>
			<input class="form-control" id="sale_pwd_1" name="sale_pwd_1" value="" required>
		</div>
	</div>
	<div class="row">
		<div class="p-1 input-group col-12 col-sm-12 col-md-12 col-lg-8">
			<div class="input-group-prepend"><span class="input-group-text" style="width:140px;">销售2(macco_sale_2)</span></div>
			<input class="form-control" id="sale_pwd_2" name="sale_pwd_2" value="" required>
		</div>	
	</div>		
	<div class="row">
		<div class="p-1 input-group col-12 col-sm-12 col-md-12 col-lg-8">
			<div class="input-group-prepend"><span class="input-group-text" style="width:140px;">销售3(macco_sale_3)</span></div>
			<input class="form-control" id="sale_pwd_3" name="sale_pwd_3" value="" required>
		</div>	
	</div>	





		
	</div>
	
	</form>
	
<script>
 
</script>

</body>
</html>
