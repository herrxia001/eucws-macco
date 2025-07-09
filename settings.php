<?php
/************************************************************************************
	File:		settings.php
	Purpose:	Settings
************************************************************************************/
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'resource_'.$_SESSION['uLanguage'].'.php';
$thisResource = new myResource();
$active[8] = "active";
include_once 'db_functions.php';
?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>
	<title>EUCWS - Settings</title>
</head>
<style>
.center {
  text-align: center;
  border: 3px;
}
img {
  display: block;
  margin-left: auto;
  margin-right: auto;
}
</style>
<body>
	<?php include 'include/nav.php' ?>

	<br>
    <div class="container">
		<div class="row">
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="set_password.php"><img class="mb-3" src="images/s_password.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="set_password.php" role="button" style="width:100px"><?php echo $thisResource->comPassMgt ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="set_printlist.php"><img class="mb-3" src="images/s_company.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="clean.php" role="button" style="width:100px">资料清理</a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="set_printlist.php"><img class="mb-3" src="images/bc_purchase.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="set_printlist.php" role="button" style="width:100px">打印管理</a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="set_print.php"><img class="mb-3" src="images/bc_settings.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="set_print.php" role="button" style="width:100px">打印设置</a></p>
			</div>
			<?php if ($_SESSION['uRole'] == 0) { ?>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="set_company.php"><img class="mb-3" src="images/s_company.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="set_company.php" role="button" style="width:100px"><?php echo $thisResource->comCompProfile ?></a></p>
			</div>
<!--
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="set_user.php"><img class="mb-3" src="images/s_users.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="set_user.php" role="button" disabled></a></p>
			</div>
-->
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="set_sys.php"><img class="mb-3" src="images/h_settings.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="set_sys.php" role="button" style="width:100px"><?php echo $thisResource->comOptions ?></a></p>
			</div>
			<?php } ?>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="logout.php"><img class="mb-3" src="images/s_logout.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="logout.php" role="button" style="width:100px"><?php echo $thisResource->comLogout ?></a></p>
			</div>
		</div>
	</div> <!-- icons -->

</body>

<script>
var myRes = <?php echo json_encode($thisResource) ?>;

$(document).ready(function(){
	 document.getElementById("myTitle").innerHTML = myRes['comSettings'];
 });
 
</script>

</html>
