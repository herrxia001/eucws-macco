<style>
.navbar-brand{
	font-size: 16px;
}
.nav-item{
	font-size: 16px;
}
</style>

<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
	<a class="navbar-brand" href="home.php"><span style="font-size: 20px" class='fa fa-home'></span></a>
	<label id="myTitle" style="font-size:16px; color:#fff" class="mt-2"></label>

<?php if ($_SESSION['uRole'] == 0) { ?>

	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsMain" >
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarsMain">
		<ul class="navbar-nav ml-auto">
			<li class="nav-item <?= $active[0] ?>">
				<a class="nav-link" href="home.php"><?php echo $thisResource->comHome ?></a>
			</li>
			<li class="nav-item <?= $active[1] ?>">
				<a class="nav-link" href="inv_mgt.php"><?php echo $thisResource->comProductList ?></a>
			</li>
			<li class="nav-item <?= $active[2] ?>">
				<a class="nav-link" href="inv_sale.php">促销</a>
			</li>
			<li class="nav-item <?= $active[3] ?>">
				<a class="nav-link" href="order_mgt.php"><?php echo $thisResource->comOrder ?><a>
			</li>
			<li class="nav-item <?= $active[4] ?>">
				<a class="nav-link" href="pur_mgt.php"><?php echo $thisResource->comPurchase ?></a>
			</li>
			<li class="nav-item <?= $active[5] ?>">
				<a class="nav-link" href="management.php"><?php echo $thisResource->comManagement ?></a>
			</li>
			<li class="nav-item <?= $active[6] ?>">
				<a class="nav-link" href="sales_report.php"><?php echo $thisResource->comReport ?></a>
			</li>
			<li class="nav-item <?= $active[7] ?>">
				<a class="nav-link" href="app_home.php">APP</a>
			</li>
			<li class="nav-item <?= $active[8] ?>">
				<a class="nav-link" href="settings.php"><?php echo $thisResource->comSettings ?></a>
			</li>
			<li class="nav-item dropdown dropleft">
				<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
					<?php echo strtoupper($_SESSION['uLanguage']); ?></a>
				<div class="dropdown-menu">
					<a class="dropdown-item" href="set_lan.php?lan=cn">中文</a>
					<a class="dropdown-item" href="set_lan.php?lan=en">English</a>
					<a class="dropdown-item" href="set_lan.php?lan=de">Deutsch</a>
					<a class="dropdown-item" href="set_lan.php?lan=it">Italiano</a>
				</div>
			</li>
		</ul>
	</div>
	
<?php }else{ ?>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsMain" >
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarsMain">
		<ul class="navbar-nav ml-auto">
			<li class="nav-item dropdown dropleft">
				<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
					<?php echo strtoupper($_SESSION['uLanguage']); ?></a>
				<div class="dropdown-menu">
					<a class="dropdown-item" href="set_lan.php?lan=cn">中文</a>
					<a class="dropdown-item" href="set_lan.php?lan=en">English</a>
					<a class="dropdown-item" href="set_lan.php?lan=de">Deutsch</a>
					<a class="dropdown-item" href="set_lan.php?lan=it">Italiano</a>
				</div>
			</li>
		</ul>
	</div>
<?php } ?>

</nav>

