<?php
/************************************************************************************
	File:		management.php
	Purpose:	management functions
************************************************************************************/
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'resource_'.$_SESSION['uLanguage'].'.php';
$thisResource = new myResource();
$active[5] = "active";
include_once 'db_functions.php';
?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>
	<title>Management</title>
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
				<a href="inv_types.php?back=management"><img class="mb-3" src="images/m_types.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="inv_types.php?back=management" role="button" style="width:100px"><?php echo $thisResource->comTypes ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="inv_suppliers.php?back=management"><img class="mb-3" src="images/m_supps.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="inv_suppliers.php?back=management" role="button" style="width:100px"><?php echo $thisResource->comSuppliers ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="cust_list.php?back=management"><img class="mb-3" src="images/m_custs.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="cust_list.php?back=management" role="button" style="width:100px"><?php echo $thisResource->comCustomers ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="inv_units.php?back=management"><img class="mb-3" src="images/m_units.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="inv_units.php?back=management" role="button" style="width:100px"><?php echo $thisResource->comPackages ?></a></p>
			</div>
		</div>
		<div class="row">
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="inv_variant.php?back=management"><img class="mb-3" src="images/m_variant.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="inv_variant.php?back=management" role="button" style="width:100px"><?php echo $thisResource->comVariants ?></a></p>
			</div>
		</div>
	</div> <!-- end of container -->
</body>

<script>

var myRes = <?php echo json_encode($thisResource) ?>;

$(document).ready(function(){
	// Display Title
	document.getElementById("myTitle").innerText = myRes['comManagement'];
 });

</script>

</html>
