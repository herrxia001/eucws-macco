<?php
/************************************************************************************
	File:		app_home.php
	Purpose:	Home page of APP management
************************************************************************************/
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'resource_'.$_SESSION['uLanguage'].'.php';
$thisResource = new myResource();
$active[7] = "active";
include_once 'db_functions.php';
?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>
	<title>MODAS - APP MANAGEMENT</title>
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
				<a href="app_mgt.php"><img class="mb-3" src="images/app_mgt.png" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="app_mgt.php" role="button"><?php echo $thisResource->comAppMgt ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="app_types.php"><img class="mb-3" src="images/app_types.png" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="app_types.php" role="button"><?php echo $thisResource->comAppTypes ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="app_user.php"><img class="mb-3" src="images/approve.png" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="app_user.php" role="button"><?php echo $thisResource->comAppUsers ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="app_report.php"><img class="mb-3" src="images/report.png" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="app_report.php" role="button"><?php echo $thisResource->comAppReport ?></a></p>
			</div>
		</div>
	</div> <!-- end of container -->

<script>
var myRes = <?php echo json_encode($thisResource) ?>;

$(document).ready(function(){
	document.getElementById("myTitle").innerText = "APP";
 });
 
</script>

</body>
</html>
