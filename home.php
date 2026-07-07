<?php
/************************************************************************************
	File:		home.php
	Purpose:	Home page
************************************************************************************/
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'resource_'.$_SESSION['uLanguage'].'.php';
$thisResource = new myResource();

include_once 'db_functions.php';

$active[0] = "active";
?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>
	<title>MODAS - Home</title>
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
	
    <div class="container" id="maindiv">
<?php if ($_SESSION['uRole'] == 0) { ?>
		<div class="row">
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="order_new.php"><img class="mb-3" src="images/h_order.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="order_new.php" role="button" style="width:100px"><?php echo $thisResource->comOrderNew ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="purchase.php"><img class="mb-3" src="images/h_purchase.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="purchase.php" role="button" style="width:100px"><?php echo $thisResource->comPurchaseNew ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="inv_mgt.php"><img class="mb-3" src="images/h_invmgt.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="inv_mgt.php" role="button" style="width:100px"><?php echo $thisResource->comProductList ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="management.php"><img class="mb-3" src="images/h_management.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="management.php" role="button" style="width:100px"><?php echo $thisResource->comManagement ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="sales_report.php"><img class="mb-3" src="images/h_report.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="sales_report.php" role="button" style="width:100px"><?php echo $thisResource->comReport?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="app_home.php"><img class="mb-3" src="images/app.png" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="app_home.php" role="button" style="width:100px">APP</a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="settings.php"><img class="mb-3" src="images/h_settings.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="settings.php" role="button" style="width:100px"><?php echo $thisResource->comSettings ?></a></p>
			</div>
			<?php if($_SESSION['validDay'] != "" && $_SESSION['validDay'] > 0) $font_color="color: green;"; if($_SESSION['validDay'] < 0) $font_color="color: red;"; ?>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<img class="mb-3" src="images/calandar.svg" alt="" width="80" height="80">
				<div style="position: absolute;top: 30px;width: 100%;text-align: center;left: 0;right: 0;font-size: 27px;font-weight: bold; <?= $font_color ?>"><?= $_SESSION['validDay'] ?></div>
				<p><a class="btn btn-secondary" role="button" ><?php echo $thisResource->comNoch ?> <?= $_SESSION['validDay'] ?> <?php echo $thisResource->comTag ?></a></p>
			</div>	
		</div>
<?php } else if ($_SESSION['uRole'] == 1) { ?>
		<div class="row">
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="order_new.php?back=home"><img class="mb-3" src="images/h_order.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="order_new.php?back=home" role="button"><?php echo $thisResource->comOrderNew ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="purchase.php"><img class="mb-3" src="images/h_purchase.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="purchase.php" role="button" style="width:100px"><?php echo $thisResource->comPurchaseNew ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="order_mgt.php?back=home"><img class="mb-3" src="images/m_types.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="order_mgt.php?back=home" role="button"><?php echo $thisResource->comOrders ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="logout.php"><img class="mb-3" src="images/s_logout.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="logout.php" role="button"><?php echo $thisResource->comLogout ?></a></p>
			</div>
			<?php if($_SESSION['validDay'] != "" && $_SESSION['validDay'] > 0) $font_color="color: green;"; if($_SESSION['validDay'] < 0) $font_color="color: red;"; ?>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<img class="mb-3" src="images/calandar.svg" alt="" width="80" height="80">
				<div style="position: absolute;top: 30px;width: 100%;text-align: center;left: 0;right: 0;font-size: 27px;font-weight: bold; <?= $font_color ?>"><?= $_SESSION['validDay'] ?></div>
				<p><a class="btn btn-secondary" role="button" ><?php echo $thisResource->comNoch ?> <?= $_SESSION['validDay'] ?> <?php echo $thisResource->comTag ?></a></p>
			</div>	
		</div>
<?php } else if ($_SESSION['uRole'] == 2) { ?>
		<div class="row">
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="inv_srch.php"><img class="mb-3" src="images/h_invmgt.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="inv_srch.php" role="button"><?php echo $thisResource->comProduct ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="logout.php"><img class="mb-3" src="images/s_logout.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="logout.php" role="button"><?php echo $thisResource->comLogout ?></a></p>
			</div>
			<?php if($_SESSION['validDay'] != "" && $_SESSION['validDay'] > 0) $font_color="color: green;"; if($_SESSION['validDay'] < 0) $font_color="color: red;"; ?>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<img class="mb-3" src="images/calandar.svg" alt="" width="80" height="80">
				<div style="position: absolute;top: 30px;width: 100%;text-align: center;left: 0;right: 0;font-size: 27px;font-weight: bold; <?= $font_color ?>"><?= $_SESSION['validDay'] ?></div>
				<p><a class="btn btn-secondary" role="button" ><?php echo $thisResource->comNoch ?> <?= $_SESSION['validDay'] ?> <?php echo $thisResource->comTag ?></a></p>
			</div>	
		</div>	
<?php } else if ($_SESSION['uRole'] == 3) { ?>
		<div class="row">
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="order_new.php"><img class="mb-3" src="images/h_order.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="order_new.php" role="button" style="width:100px"><?php echo $thisResource->comOrderNew ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="purchase.php"><img class="mb-3" src="images/h_purchase.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="purchase.php" role="button" style="width:100px"><?php echo $thisResource->comPurchaseNew ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="inv_mgt.php"><img class="mb-3" src="images/h_invmgt.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="inv_mgt.php" role="button" style="width:100px"><?php echo $thisResource->comProductList ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="management.php"><img class="mb-3" src="images/h_management.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="management.php" role="button" style="width:100px"><?php echo $thisResource->comManagement ?></a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="app_home.php"><img class="mb-3" src="images/app.png" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="app_home.php" role="button" style="width:100px">APP</a></p>
			</div>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<a href="logout.php"><img class="mb-3" src="images/s_logout.svg" alt="" width="80" height="80"></a>
				<p><a class="btn btn-secondary" href="logout.php" role="button"><?php echo $thisResource->comLogout ?></a></p>
			</div>
			<?php if($_SESSION['validDay'] != "" && $_SESSION['validDay'] > 0) $font_color="color: green;"; if($_SESSION['validDay'] < 0) $font_color="color: red;"; ?>
			<div class="col-6 col-sm-6 col-md-3 col-lg-3 center">
				<img class="mb-3" src="images/calandar.svg" alt="" width="80" height="80">
				<div style="position: absolute;top: 30px;width: 100%;text-align: center;left: 0;right: 0;font-size: 27px;font-weight: bold; <?= $font_color ?>"><?= $_SESSION['validDay'] ?></div>
				<p><a class="btn btn-secondary" role="button" ><?php echo $thisResource->comNoch ?> <?= $_SESSION['validDay'] ?> <?php echo $thisResource->comTag ?></a></p>
			</div>	
		</div>
<?php } ?>
	</div> <!-- end of container -->	

<script src="js/ajax.js"></script>
<script src="js/autocomplete.js?202009091508"></script>
<script>
var myRes = <?php echo json_encode($thisResource) ?>;

$(document).ready(function(){
	document.getElementById("myTitle").innerText = myRes['comHome'];
 });
// Load all data
getRequest("getInvs.php", loadInvs, loadInvsNo);
function loadInvs(result) {
	var a_icode = new Array(), a_image = new Array(), imgFile;
	
	for (var i = 0; i < result.length; i++) {
		a_icode[i] = result[i]['i_code'];
		imgFile = result[i]['path']+"/"+result[i]['i_id']+"_"+result[i]['m_no']+"_s.jpg";
		a_image[i] = imgFile;
	}	
	
	localStorage.setItem("a_icode", JSON.stringify(a_icode));
	localStorage.setItem("a_image", JSON.stringify(a_image)); 
}
function loadInvsNo(result) {

}
//Save all country codes
var a_country = ['Austria', 'Belgien', 'Czechia', 'Denmark', 'Finland', 'France', 'Germany', 'Italy', 'Luxemburg', 'Nederland', 'Schweiz', 'Spain'];
localStorage.setItem("a_country", JSON.stringify(a_country));

</script>
</body>
</html>
