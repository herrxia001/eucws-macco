<style>
.navbar-brand{
	font-size: 16px;
}
.nav-item{
	font-size: 16px;
}
</style>

    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
		<a class="navbar-brand" href="a_ordmgt.php">EUIMS发票管理系统</a>
		<label id="myTitle" style="font-size:16px; color:#fff" class="mt-2"></label>

		<button class="navbar-toggler" type="button" data-toggle="collapse" 
			data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarsExampleDefault">
			<ul class="navbar-nav ml-auto navbar-right">
				<li class="nav-item">
					<a class="nav-link" href="a_neword.php">待处理</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="a_ordmgt.php">销售</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="a_purmgt.php">进货</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="a_refmgt.php">退货</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="a_voidmgt.php">作废</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="dropdownSys" data-toggle="dropdown">库存</a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="a_artmgt.php">当前库存</a>
                        <!--a class="dropdown-item" href="a_artmgt_old.php">历史库存</a-->
						<a class="dropdown-item" href="a_art_rpt_yr.php">库存年报</a>
						<!--a class="dropdown-item" href="a_art_rpt_qt.php">库存季报</a-->
						<?php if ($_SESSION['uDb'] == "cara") { ?>
						<a class="dropdown-item" href="a_artmgt_old.php">库存明细</a>
						<?php } else { ?>
						<a class="dropdown-item" href="a_art_trans.php">库存明细</a>
						<?php } ?>
					</div>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="dropdownSys" data-toggle="dropdown">设置</a>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="a_company.php">公司资料</a>
						<a class="dropdown-item" href="a_options.php">系统选项</a>
					</div>
				</li>
			</li>
			</ul>
		</div>
	</nav>

