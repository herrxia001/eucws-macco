<?php 
/************************************************************************************
	File:		ainvoice.php
	Purpose:	invoice
************************************************************************************/
// Start session; If session expired, load the login page.
session_start();
if(!$_SESSION['uId'])
	header("Location:alogin.php");

// Include files
include_once 'resource.php';
include_once 'db_functions.php';
include_once 'db_invoice.php';

// Init variables
$thisResource = new myResource($_SESSION['uLanguage']);	
$myId = "";
$newInvoiceId = "";
$myCompany= dbQueryCompany();
$backPhp = 'a_ordmgt.php';
$myArts = dbQueryArticles();

// Start a new order
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(isset($_GET['back']))
	{
		$backPhp = $_GET['back'].'.php';
	}
	if (isset($_GET['r_id']))
	{
		$myId = $_GET['r_id'];
		$newInvoiceId = dbGetInvoiceNo_2($myId);
	}
}

?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>	
	<title>EUIMS - Invoice</title>
	<script>
	var tabID = sessionStorage.tabID ? 
            sessionStorage.tabID : 
            sessionStorage.tabID = Math.random();
	if(tabID != '<?= $_SESSION['tabID'] ?>'){
		if(confirm("页面已经打开,重新登入？"))
			window.location.href = "alogin.php";
		else
			$("*").remove();
	}
	</script>
</head>
<style>
body {
 padding-top: 0.5rem;
}
.dropdown-menu{
    max-height: 300px;
    overflow-y: scroll;
}
</style>
<body>	
	<?php include "include/modalCust.php" ?>
	<?php include "include/modalCustSearch.php" ?>
	<?php include "include/modalDel.php" ?>

	<!-- Modal: barcode input -->
<div class="modal fade" id="modalBarcode" tabindex="-1" role="dialog"
	data-backdrop="static" data-keyboard="false" style="padding:1">
	<div class="modal-dialog" style="width:95%; height:100%; margin:1">
		<div class="modal-content" style="height:95%;">
		<div id="bcModalBody" class="modal-body" style="overflow-y: auto">
		<audio id="playBeepYes" src="beepYes.mp3" type="audio/mp3" style="display: none;"></audio>
		<audio id="playBeepNo" src="beepNo.mp3" type="audio/mp3" style="display: none;"></audio>
<!-- top menu -->		
		<div class="row">
			<div class="p-1 col-7" align="left">
				<button style="float: left;" type="button" class="mr-1 btn btn-success" onclick="startscann('qr-reader')"><span class='fa fa-camera'></span></button>
				<label id="mdbcTitle" class="ml-2 mt-2" style="font-weight: bold"></label>
			</div>
			<div class="p-1 col-5" align="right">
				<button type="button" class="btn btn-secondary" onclick="bcCancel()"><span class='fa fa-times'></span></button>
				<button type="button" class="btn btn-primary" style="width:40px" onclick="bcDone()"><span class='fa fa-check'></span></button>
			</div>
		</div>
            <div id="qr-reader" style="width: 100%;"></div>
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:1px; width:100%">
		</div>
		<div class="row">
			<div class="col p-1" align="center">
				<label id="bcScanInfo">请扫描条码</label>
			</div>
		</div>
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:2px; width:100%">
		</div>
		<div class="container" id="bcBody">
			
		</div>
		<div class="row">
			<div class="p-1 col-7" align="left">
				<button type="button" class="mr-1 btn btn-success" onclick="startscann('qr-reader')"><span class='fa fa-camera'></span></button>
				<label id="mdbcTitle1" class="ml-2 mt-2" style="font-weight: bold"></label>
			</div>
			<div class="p-1 col-5" align="right">
				<button type="button" class="btn btn-secondary" onclick="bcCancel()"><span class='fa fa-times'></span></button>
				<button type="button" class="btn btn-primary" style="width:40px" onclick="bcDone()"><span class='fa fa-check'></span></button> 
			</div>
		</div>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: barcode input -->


<!-- Modal: barcode input -->
<div class="modal fade" id="modalBcInput" tabindex="-1" role="dialog">	
	<div class="modal-dialog modal-sm modal-dialog-centered">
		<div class="modal-content">
		<div class="modal-body">
			<div class="row p-1">
				<input type="number" class="form-control" name="bc_edit_amount" id="bc_input_amount">
			</div>
			<div class="row p-1">
			<div class="col" align="center">
				<button type="button" class="btn btn-outline-secondary" onclick="bcCancelInputAmount()"><span class='fa fa-times'></span></button>
				<button type="button" class="ml-5 btn btn-outline-secondary" onclick="bcDoneInputAmount()"><span class='fa fa-check'></span></button>
			</div>
			</div>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: barcode input -->


	<form action="" method="post">
	<div class="container">		
<!-- order data header -->			
	<div class="row"> 
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-6" align="left">
			<div class="input-group-prepend"><span class="input-group-text">发票号</span></div>
			<input type="text" class="form-control" name="invoice_no" id="invoice_no" value="" readonly>
			<button type="button" class="ml-1 btn btn-secondary" id="btnEditNo" onclick="editInvoiceNo()">更改</button>
			<div class="input-group-prepend ml-2"><span class="input-group-text">客户</span></div>
			<input type="text" class="form-control" name="k_name" id="k_name" value="" readonly>
			<button type="button" class="ml-1 btn btn-secondary" id="btnSrchCust" onclick="searchCust()">搜索</button>
			<button type="button" class="ml-1 btn btn-secondary" id="btnShowCust" onclick="showCust()">查看</button>
		</div>
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-6" align="right">
			<button type="button" class="btn btn-danger" id="btnVoid" onclick="voidOrder()">作废</button>
			<button type="button" class="btn btn-danger" id="btnDestroy" onclick="destroyOrder()">删除</button>
			<button type="button" class="btn btn-secondary" id="btnOptions" onclick="showOptions()">选项</button>		
			<button type="button" class="btn btn-success" id="btnPrint" onclick="printOrder()">打印</button>
			<button type="button" class="btn btn-success" id="btnERechnung" onclick="eRechnungOrder()">下载</button>
			<button type="button" class="btn btn-primary" id="btnSave" onclick="submitOrder()">保存</button>		
			<button type="button" class="btn btn-secondary" id="btnClose" onclick="closeOrder()">关闭</button>
			<button type="button" class="btn btn-secondary" id="btnNew" onclick="newItem()">加货</button>
			<button type="button" class="btn btn-primary" id="btnBarcode" onclick="showBarcode()" style=""><span class="fa fa-barcode"></span></button>			
		</div>
	</div>
<!-- order items -->
	<div class="row"> 
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12">
			<table id="myTable" data-toggle="table"
				data-single-select="true" data-click-to-select="true" data-unique-id="id" data-height="440">
				<thead>
					<tr>
					<th class="p-1" data-field="id" data-visible="false">#</th>
					<th class="p-1" data-field="idx_image" data-width="15" data-width-unit="%" data-halign="center" data-align="left">照片</th>
					<th class="p-1" data-field="idx_code" data-width="20" data-width-unit="%" data-halign="center" data-align="left">货号</th>
					<th class="p-1" data-field="idx_name" data-width="25" data-width-unit="%" data-halign="center" data-align="left">名称</th>
					<th class="p-1" data-field="idx_count" data-width="10" data-width-unit="%" data-halign="center" data-align="right">件数</th>
					<th class="p-1" data-field="idx_price" data-width="10" data-width-unit="%" data-halign="center" data-align="right">售价</th>
					<th class="p-1" data-field="idx_subtotal" data-width="10" data-width-unit data-halign="center" data-align="right">小计</th>					
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
<!-- buttons -->
	<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-3" align="left">
			<button type="button" class="btn btn-secondary" id="btnTax"  onclick="showTax()">税</button>
			<button type="button" class="btn btn-secondary" id="btnDis"  onclick="showDis()">折扣</button>
			<button type="button" class="btn btn-secondary" id="btnFee"  onclick="showFee()">费用</button>
			<button type="button" class="btn btn-secondary" id="btnPay"  onclick="showPay()">付款</button>
		</div>
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-3" align="left">
			<div class="input-group-prepend"><span class="input-group-text">时间</span></div>
			<input type="text" class="form-control" id="in_date" name="in_date" readonly>
			<button type="button" class="ml-1 btn btn-secondary" id="btnTime"  onclick="changeTime()">更改</button>
		</div>
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-3" align = "center">
			<b>总件数:&nbsp;</b><label id="sumCount" style="color:blue">0</label>
			<b>&nbsp;&nbsp;总金额:&nbsp;</b><label id="sumPrice" style="color:blue">0.00</label>
		</div>
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-3" align = "right">
			<b>&nbsp;&nbsp;折扣(%):&nbsp;</b><label id="sumDiscountRate" style="color:blue">0.00</label>
			<b>&nbsp;&nbsp;费用:&nbsp;</b><label id="sumFees" style="color:blue">0.00</label>
		</div>
	</div>	
<!-- summary -->
	<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12" align = "right">
			<b>&nbsp;&nbsp;税前金额:&nbsp;</b><label id="sumTotal" style="color:blue">0.00</label>
		</div>
	</div>
	<div class="row">	
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12" align = "right">						
			<b>&nbsp;&nbsp;MwSt(</b><label id="sumTaxRate" style="color:blue">0.00</label><b>%)</b>
			<label id="sumTax" style="color:blue">0.00</label>
			<b>&nbsp;&nbsp;完税金额:&nbsp;</b><label id="sumNet" style="color:blue">0.00</label>
		</div>
	</div>
	<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12" align = "right">
			<b>已付:&nbsp;</b><label id="sumPaid" style="color:blue">0.00</label>
			<b>&nbsp;&nbsp;未付:&nbsp;</b><label id="sumDue" style="color:red">0.00</label>
		</div>
	</div>

<!-- Modal: invoice item -->
<div class="modal fade" id="modalOrderItem" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdOrderItemTitle">发票项目</b>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-2 p-1" align="center">
					<img id="m_img" width="60" height="80" style="object-fit: cover"></img>
				</div>
				<div class="col-10 p-1">
					<div class="input-group">
						<div class="input-group-prepend"><span class="input-group-text">货号</span></div>
						<input type="text" class="form-control" name="m_i_code" id="m_i_code" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">名称</span></div>
						<input type="text" class="form-control" name="m_i_name" id="m_i_name" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">单位</span></div>
						<input type="text" class="form-control" name="m_unit_str" id="m_unit_str" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">数量</span></div>
						<button type="button" class="btn btn-secondary mx-2" id="btnCountMinus" onclick="countMinus()"><span class='fa fa-minus'></button>
						<input type="number" min="0" step="1" class="form-control" name="m_count" id="m_count">
						<button type="button" class="btn btn-secondary mx-2" id="btnCountAdd" onclick="countAdd()"><span class='fa fa-plus'></button>	
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">价格</span></div>
						<input type="number" min="0" step="0.01" class="form-control" name="m_price" id="m_price" oninput="refreshSale();">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">折扣%</span></div>
						<input type="number" min="0" step="1" max="100" class="form-control" name="m_discount" id="m_discount" oninput="refreshSale();">
						<div class="input-group-append"><span class="input-group-text" id="after_price"></span></div>
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">备注</span></div>
						<input type="text" class="form-control" name="m_note" id="m_note">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-2 p-1" align="left">
					<button type="button" class="btn btn-danger" id="btnDel" onclick="mdoDel()"><span class='fa fa-trash'></button>
				</div>
				<div class="col-10 p-1" align="right">
					<button type="button" class="btn btn-primary" id="btnDone" onclick="mdoDone()"><span class='fa fa-check'></button>
				</div>
			</div>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: invoice item-->

<!-- Modal: modalNewItem -->
<div class="modal fade" id="modalNewItem" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdnTitle">发票项目</b>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="p-1 col-12">
					<div class="input-group">
						<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">名称</span></div>
						<input type="text" class="form-control" name="mdn_a_name" id="mdn_a_name" readonly style="background-color:white">
						<div class="input-group-append">
							<div class="dropdown dropleft">
								<button type="button" id="mdnBtnName" class="ml-2 btn btn-secondary dropdown-toggle" data-toggle="dropdown">选择商品</button>
								<ul class="dropdown-menu">
								<?php for($i=0; $i<count($myArts); $i++) {
								echo "<a class='dropdown-item' href='#' onclick='selArt(this)'>".$myArts[$i]['a_name']."</a>";
								} ?>
								</ul>
							</div>
						</div>
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">货号</span></div>
						<input type="text" class="form-control" name="mdn_code" id="mdn_code">		
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">件数</span></div>
						<input type="number" min="0" step="1" class="form-control" name="mdn_count" id="mdn_count">		
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">售价</span></div>
						<input type="number" min="0" step="0.01" class="form-control" name="mdn_price" id="mdn_price" oninput="refreshSale_2();">		
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">折扣%</span></div>
						<input type="number" min="0" step="1" max="100" class="form-control" name="mdn_discount" id="mdn_discount" oninput="refreshSale_2();">
						<div class="input-group-append"><span class="input-group-text" id="mdn_after_price"></span></div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="p-1 col-2" align="left">
					<button type="button" class="btn btn-danger" id="mdnBtnDel" onclick="mdnDel()"><span class='fa fa-trash'></button>
				</div>
				<div class="p-1 col-10" align="right">
					<button type="button" class="btn btn-primary" id="mdnBtnDone" onclick="mdnDone()"><span class='fa fa-check'></button>
				</div>
			</div>
		</div>
		</div>
	</div>
</div> <!-- end of modalNewItem -->

<!-- Modal: Tax -->
<div class="modal fade" id="modalOrderTax" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdOrderTaxTitle">税</h5>
		</div>
		<div class="modal-body">
			<form>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text">税率&nbsp;(%)</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mt_tax_rate" id="mt_tax_rate">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text">应付税款</span></div>
				<input type="number" class="form-control" name="mt_tax" id="mt_tax" readonly>
			</div>
			</form>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></button>
			<button type="button" class="btn btn-primary" id="btnTaxOk" onclick="doneTax()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Tax -->

<!-- Modal: Discount -->
<div class="modal fade" id="modalOrderDis" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdOrderDisTitle">折扣</h5>
		</div>
		<div class="modal-body">
			<form>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text">折扣&nbsp;(-%)</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mdi_discount_rate" id="mdi_discount_rate" oninput="displayDiscount()">
			</div>	
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text">折扣金额</span></div>
				<input type="text" class="form-control" name="mdi_discount" id="mdi_discount" oninput="changDis()">
			</div>
			</form>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></button>
			<button type="button" class="btn btn-primary" id="btnDisOk" onclick="doneDis()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Discount -->

<!-- Modal: Fees -->
<div class="modal fade" id="modalOrderFee" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdOrderFeeTitle">费用</h5>
		</div>
		<div class="modal-body">
			<form>	
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">运费</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee1" id="mf_fee1">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">货到付款费</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee2" id="mf_fee2">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">银行费用</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee3" id="mf_fee3">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">包装费用</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee4" id="mf_fee4">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">其它费用</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee5" id="mf_fee5">
			</div>
			</form>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></button>
			<button type="button" class="btn btn-primary" id="btnFeeOk" onclick="doneFee()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Fees-->

<!-- Modal: Payment -->
<div class="modal fade" id="modalOrderPay" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdOrderPayTitle">付款</h5>			
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">待付金额</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mp_amount" id="mp_amount">
				<div class="dropdown ml-2">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">付款方式</button>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="#" onclick="selPay(this)">Bar</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">Karte</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">Überweisung</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">Scheck</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">Nachnahme</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">PayPal</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">Vorkasse</a>
					</div>
				</div>
			</div>
			<div class="p-1">
				<table id="tablePay" data-toggle="table" data-single-select="true" data-click-to-select="true" data-unique-id="id" >
					<thead>
						<tr>
						<th class="p-1" data-field="id" data-width="10" data-width-unit="%" data-visible="false">#</th>
						<th class="p-1" data-field="idx_type" data-width="25" data-width-unit="%" data-visible="false">方式</th>
						<th class="p-1" data-field="idx_value" data-width="25" data-width-unit="%">付款方式</th>
						<th class="p-1" data-field="idx_amount" data-width="30" data-width-unit="%">金额</th>
						<th class="p-1" data-field="idx_del" data-width="10" data-width-unit="%"></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">应付</span></div>
				<input type="text" class="form-control" name="mp_total" id="mp_total" readonly>
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">已付</span></div>
				<input type="text" class="form-control" name="mp_pays_total" id="mp_pays_total" readonly>
			</div>			
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">未付</span></div>
				<input type="text" class="form-control" name="mp_pays_due" id="mp_pays_due" readonly>
			</div>	
			<div class="row">
				<div class="input-group p-1">
					<div class="form-check ml-4 pt-2">
						<input class="form-check-input" type="radio" value="1" name="isPayed" id="mdp_isPayed" onclick="if($('#mdp_paidDatum').val()=='') $('#mdp_paidDatum').val(currentDate(2))">
						<label class="form-check-label" for="mdp_isPayed" onclick="if($('#mdp_paidDatum').val()=='') $('#mdp_paidDatum').val(currentDate(2))">
							已付
						</label>
					</div>
					<div class="form-check ml-4 pt-2">
						<input class="form-check-input" type="radio" value="0" name="isPayed" id="mdp_isNotPayed" onclick="$('#mdp_paidDatum').val('')">
						<label class="form-check-label" for="mdp_isNotPayed" onclick="$('#mdp_paidDatum').val('')">
							未付
						</label>
					</div>
					<div class="input-group-prepend ml-2"><span class="input-group-text" style="font-size:14px;">付款时间</span></div>
					<input type="date" class="form-control" style="font-size:14px;" id="mdp_paidDatum" name="mdp_paidDatum" value="">
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></button>
			<button type="button" class="btn btn-primary" id="btnPayOk" onclick="donePay()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Payment -->

<!-- Modal: Edit Invoice No. -->
<div class="modal fade" id="modalEditInvoiceNo" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdeinTitle">修改发票号</h5>
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">发票号</span></div>
				<input type="text" min="0" step="0.01" class="form-control" name="mdein_no" id="mdein_no">
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></button>
			<button type="button" class="btn btn-primary" id="mdeinBtnOk" onclick="doneEditInvoiceNo()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Edit Invoice No. -->

<!-- Modal: Edit Time -->
<div class="modal fade" id="modalEditTime" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdeinTitle">修改发票时间</h5>
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">Datum</span></div>
				<input type="date" class="form-control" name="mdet_datum" id="mdet_datum">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">Lieferdatum</span></div>
				<input type="date" class="form-control" name="mdet_lieferdatum" id="mdet_lieferdatum">
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></button>
			<button type="button" class="btn btn-primary" id="mdetBtnOk" onclick="doneEditTime()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Edit Time -->

<!-- Modal: Options -->
<div class="modal fade" id="modalOptions" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdopTitle">发票选项</h5>
		</div>
		<div class="modal-body">
			<a>打印选项<br></a>
				<div class="form-check">
				<label class="form-check-label">
					<input type="checkbox" class="form-check-input" id="mdopPrintNoName" value="">不打印商品名称 
				</label>
				</div>
				<div class="form-check">
				<label class="form-check-label">
					<input type="checkbox" class="form-check-input" id="mdopPrintOnePage" value="">只打印单张发票 
				</label>
				</div>
				<div class="form-check">
				<label class="form-check-label">
					<input type="checkbox" class="form-check-input" id="mdopPrintNonEU" value="">打印非欧盟客户相关信息 
				</label>
				</div>
			<hr>
			<a>退货单<br></a>
			<button type="button" class="btn btn-secondary" id="mdopBtnRefund" onclick="createRefund()">创建退货单</button>
			<hr>
			<a>添加备注<br></a>
			<textarea class="form-control" rows="2" id="mdopNote"></textarea>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></button>
			<button type="button" class="btn btn-primary" id="mdopBtnOk" onclick="doneOptions()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Edit Time -->

	</div> <!-- end of container -->
	</form>	<!-- end of form -->
	<iframe id="printf" name="printf" style="display: none;"></iframe>
<script src="js/sysfunc.js?<?= rand ?>"></script>
<script src="js/ajax.js"></script>
<script src="js/autocomplete.js?<?= rand ?>"></script>	
<script src="js/modalCustSearch.js?<?= rand ?>"></script>
<script src="js/modalCust.js?<?= rand ?>"></script>
<script src="js/aOptions.js?<?= rand ?>"></script>
<script src="js/qrcode.js"></script>
<script src="js/html5-qrcode.min.js?<?= rand ?>"></script>
<script src="js/modalDel.js"></script>

<script>
var rId;
var myCustomer = new Object();
var company = <?php echo json_encode($myCompany) ?>;
var $table = $("#myTable");
// Modals
var $modalOrderItem = $("#modalOrderItem"), $modalNewItem = $("#modalNewItem");
var $modalOrderFee = $("#modalOrderFee"), $modalOrderPay = $("#modalOrderPay");
var $modalOrderDis = $("#modalOrderDis"), $modalOrderTax = $("#modalOrderTax");
var $modalEditInvoiceNo = $("#modalEditInvoiceNo");
var $modalEditTime = $("#modalEditTime");
var $modalOptions = $("#modalOptions");
// autocpmplete
var a_icode = new Array(), a_image = new Array();
//var a_icode = JSON.parse(localStorage.getItem("a_icode"));
//var a_image = JSON.parse(localStorage.getItem("a_image"));
var a_ivariants =  new Array();
// Data
var order = {}, orderItems = [];
var itemCount = 0, itemIdCount = 0;
var thisItem = {};
var myArts;
// Options
initAOptions();
var opPrintOnePage = false;

 // Init variables
rId = "<?php echo $myId ?>";
order['r_id'] = rId;

$table.bootstrapTable({   
	formatNoMatches: function () {
         return "请按 + 来添加商品";
    }
});

getRequest("getInvVariantAll.php", loadInvVariantOK, loadInvVariantError);
function loadInvVariantOK(result) {
	a_ivariants = result;
	var tmp = "", index = 0;
	for(var i=0; i<result.length; i++){
		if(tmp == result[i]['i_code']) continue;
		tmp = result[i]['i_code'];
		a_icode[index] = result[i]['i_code'];
		var imgFile = result[i]['path']+"/"+result[i]['i_id']+"_"+result[i]['m_no']+"_s.jpg";
		a_image[index] = imgFile;
		index++;
	}
	autocomplete_like_callback(document.getElementById("mdn_code"), a_icode, a_image, finish_item_select);
}
function loadInvVariantError(result) {
	alert("ERROR");
}
$(document).ready(function(){
	// Load all articles
	loadArts();
	// Load orderItems
	getRequest("getInvoiceById.php?r_id="+rId, loadOrder, loadError);
})

// This is a callback for autocomplete
function finish_item_select() {
	var code = document.getElementById("mdn_code").value;
	if (code == "")
		return false;
	var ivar = findArray(a_ivariants, "i_code", code);
	if (ivar != null)
		searchCodeYes(ivar);
	else
		searchCodeNo(null);
}
// Error or no found
function searchCodeNo(invs) {
	alert("没找到");
	$('#mdn_code').trigger('focus');
	thisItem['i_id'] = "0";
}

// Result back
function searchCodeYes(invs) {	
	// set values
	document.getElementById("mdn_price").value = invs['price'];
	thisItem['i_id'] = invs['i_id'];
	thisItem['path'] = invs['path'];
	thisItem['m_no'] = invs['m_no'];
	thisItem['i_code'] = invs['i_code'];
	thisItem['i_name'] = invs['i_name'];
	thisItem['ai_code'] = invs['ai_code'];
	thisItem['a_name'] = invs['a_name'];
	document.getElementById("mdn_a_name").value = invs['t_name'];
	document.getElementById("mdn_discount").value = "";
	if( document.getElementById("mdn_count").value == "") document.getElementById("mdn_count").value = 1;
	/*else document.getElementById("mdn_count").value = document.getElementById("mdn_count").value + 1;
	if(document.getElementById("mdn_code").value != "" && document.getElementById("mdn_code").value != invs['i_code']) document.getElementById("mdn_count").value = 1;
	document.getElementById("mdn_code").value = invs['i_code'];*/
	refreshSale_2();
}


// Load articles for new items
function loadArtsYes(result) {
	myArts = result; 
}
function loadArts() {
	getRequest("getArts.php", loadArtsYes, null);
}

// Load Order
function loadError(result) {
//	alert("读取发票过程中出现错误");
}
function loadOrder(result) { 
	order = result;
//	order['tax_rate'] = company['tax'];
	
	getRequest("getInvoiceItemsById.php?r_id="+rId, loadOrderItems, loadError);
	
	if (order['k_id'] != "" && order['k_id'] != "0")
		getRequest("getCustById.php?k_id="+order['k_id'], loadCust, loadError);	
	else
		myCustomer['k_id'] = "0";
	
	if (order['invoice_no'] != "0")
		document.getElementById("invoice_no").value = order['invoice_no'];
	else{
		document.getElementById("invoice_no").value = '<?= $newInvoiceId ?>';
	}

	document.getElementById("in_date").value = convertDate(order['date']);
	displaySum();
	
	// buttons
	if (order['invoice_no'] == '0' || order['invoice_no'] == '') {
		document.getElementById("btnVoid").style.display = "none";
		document.getElementById("btnDestroy").style.display = "inline";
	} else {
		document.getElementById("btnVoid").style.display = "inline";
		document.getElementById("btnDestroy").style.display = "inline";
	}
}
function refreshSale(){
    var price = $("#m_price").val();
    var discount = $("#m_discount").val();
    if(discount != ""){
        $("#after_price").text((price*((100-discount)/100)).toFixed(2));
    }else{
        $("#after_price").text(price);
    }
}
function refreshSale_2(){
    var price = $("#mdn_price").val();
    var discount = $("#mdn_discount").val();
    if(discount != ""){
        $("#mdn_after_price").text((price*((100-discount)/100)).toFixed(2));
    }else{
        $("#mdn_after_price").text(price);
    }
}
// Load Customer
function notGermanCust(ustno) {
	if (ustno == null)
		return false;
	if (ustno != "" && ustno.substring(0,2) != "DE")
		return true;
	else
		return false;
}

function isCHECust(ustno) {
	if (ustno == null)
		return false;
	if (ustno != "" && ustno.length > 3 && ustno.substring(0,3) == "CHE")
		return true;
	else
		return false;
}

function loadCust(result) {
	myCustomer = result;
	document.getElementById("k_name").value = myCustomer['k_name'];
	if (order['tax_rate'] == "0.00") {
		if (!notGermanCust(myCustomer['ustno'])) {
			order['tax_rate'] = company['tax'];
			displaySum();
		}
	}
}

// Load Order Items after getRequest
function loadOrderItems(result) {
	orderItems = result;
	itemCount = orderItems.length;
	itemIdCount = itemCount;

	var rows = [];
	var imgSrc, imgStr, code, name, countStr, subtotal;
	for (var i=0; i<itemCount; i++) {
		var calcPrice = orderItems[i]['price'];
		var priceStr = orderItems[i]['price'];
		var discount = orderItems[i]['discount'];
		var rabatt = "";
		if(discount > 0){
			rabatt = " (Rabatt "+discount+"%)";
			calcPrice = (((100-discount) * orderItems[i]['price']) /100).toFixed(2);
			priceStr = "<a style='text-decoration-line: line-through;'>"+orderItems[i]['price']+"</a> "+calcPrice;
		}
		orderItems[i]['id'] = i;
		if (orderItems[i]['i_id'] != "0") {
			imgSrc = orderItems[i]['path']+"/"+orderItems[i]['i_id']+"_"+orderItems[i]['m_no']+"_s.jpg";
			imgStr = "<img width='40' height='60' style='border:1px dotted; object-fit: cover' src='"+imgSrc+"' >";
			code = orderItems[i]['i_code'];
			name = orderItems[i]['i_name'];
		} else {
			imgStr = "";
			code = orderItems[i]['ai_code'];
			name = orderItems[i]['a_name'];
		}
		if (orderItems[i]['unit'] == "1") {
			countStr = orderItems[i]['count'];
			orderItems[i]['real_count'] = orderItems[i]['count'];
		} else {
			countStr = orderItems[i]['count']+" (x"+orderItems[i]['unit']+")";
			orderItems[i]['real_count'] = parseInt(orderItems[i]['count'])*parseInt(orderItems[i]['unit']);
		}
		subtotal = parseInt(orderItems[i]['real_count'])*parseFloat(calcPrice);
		orderItems[i]['subtotal'] = subtotal.toFixed(2);
		rows.push({
			id: orderItems[i]['id'],
			idx_image: imgStr,
			idx_code: code,
			idx_name: name+rabatt,
			idx_count: countStr,
			idx_price: priceStr,
			idx_subtotal: orderItems[i]['subtotal']
		});		
	}
	$table.bootstrapTable('append', rows);
}

// Display sum
function displaySum() {	
	var sumPrice = parseFloat(order['price_sum']);
	var sumFees = parseFloat(order['fee1']) + parseFloat(order['fee2']) + parseFloat(order['fee3']) + parseFloat(order['fee4']) + parseFloat(order['fee5']);
	var sumTotal = sumPrice - parseFloat(order['discount_rate'])/100*sumPrice + sumFees;
	var sumTax = sumTotal*parseFloat(order['tax_rate'])/100+0.0000001;
	var sumNet = sumTotal + sumTax;		
	var sumPaid = parseFloat(order['pay_cash']) + parseFloat(order['pay_card']) + parseFloat(order['pay_bank']) + 
					parseFloat(order['pay_check']) + parseFloat(order['pay_other']) + parseFloat(order['pay_paypal']) + parseFloat(order['pay_vorkasse']);
	var sumDue = parseFloat(sumNet.toFixed(2)) - parseFloat(sumPaid.toFixed(2));
	
	order['total_sum'] = sumTotal.toFixed(2);
	order['net'] = sumNet.toFixed(2);
	order['paid_sum'] = sumPaid.toFixed(2);
	order['due'] = sumDue.toFixed(2);
	if (order['due'] == "-0.00")
		order['due'] = "0.00";
	
	document.getElementById("sumCount").innerHTML = order['count_sum'];
	document.getElementById("sumPrice").innerHTML = order['price_sum'];
	
	document.getElementById("sumDiscountRate").innerHTML = order['discount_rate'];
	document.getElementById("sumFees").innerHTML = sumFees.toFixed(2);
	document.getElementById("sumTotal").innerHTML = order['total_sum'];
	
	document.getElementById("sumTaxRate").innerHTML = order['tax_rate'];
	document.getElementById("sumTax").innerHTML = sumTax.toFixed(2);
	document.getElementById("sumNet").innerHTML = order['net'];
	
	document.getElementById("sumPaid").innerHTML = order['paid_sum'];
	document.getElementById("sumDue").innerHTML = order['due'];

	saveDbOrder();

}

// Find orderItems item by searching id
function getItemIndexById(id) {
	for (var i=0; i<itemCount; i++) {
		if (orderItems[i]['id'] == id)
			return i;
	}	
	return -1;
}
/************************************************************************
	NEW ITEM
************************************************************************/
// barcode input //
var mdbcCode1 = "";
var keytime = 0;
var bcInv = null;

var bcList = [];
var bcCount = 0, bcValue = 0;
$modalBarcode = $('#modalBarcode');
var bcBody = document.getElementById("bcBody");
var bcRecordHeight;

document.getElementById("modalBarcode").addEventListener('keydown', function(e) {
	if (e.keyCode == 13){ 
		e.preventDefault();
		if (mdbcCode1.length < 2)
			return;
		queryItembyBarcode(mdbcCode1);
		mdbcCode1 = "";;
		return;
	} 
	var d = new Date();
	var t = d.getTime(); 
	if ((t - keytime) > 100)
		mdbcCode1 = e.key;
	else
		mdbcCode1 += e.key;
	keytime = t;
});

var html5QrcodeScanner;
var is_on = 0;
var is_scann= 0;

function onScanSuccess(decodedText, decodedResult) {
	if(is_scann == 1){
		queryItembyBarcode(decodedText);
		is_scann = 0;
		setTimeout(
			function() {
				is_scann = 1;
		}, 3000);

	}
    //console.log('Code scanned = ${decodedText}', decodedResult);
    //html5QrcodeScanner.clear();
	//is_on = 0;
    //html5QrcodeScanner.stop();
}

function startscann(obj){
	if (is_on == 0) {
		html5QrcodeScanner = new Html5QrcodeScanner(obj, { fps: 5, qrbox: 250 });
		html5QrcodeScanner.render(onScanSuccess);
		//html5QrcodeScanner.start();
		is_on = 1;
		is_scann = 1;
		//html5QrcodeScanner.scanFile();
		//https://blog.minhazav.dev/QR-and-barcode-scanner-using-html-and-javascript/
	}else{
		html5QrcodeScanner.clear();
		//html5QrcodeScanner.stop();
		is_on = 0;
		is_scann= 0;
	}
}
function stopscann(){
    html5QrcodeScanner.clear();
    //html5QrcodeScanner.stop();
	is_on = 0;
	is_scann= 0;
    document.getElementById("bcScanInfo").innerText = "";
}

// show modalBarcode
function showBarcode() {
	mdbcClearAll();
	document.getElementById("bcScanInfo").innerText = "请扫码条码";
	$modalBarcode.modal();
	bcDisplaySum();
}
// clear all elements
function mdbcClearAll() {
	bcList = [];
	var parent = document.getElementById("bcBody")
	while (parent.firstChild) {
		parent.firstChild.remove();
	}
	mdbcCode1 = "";
	bcCount = 0;
	bcValue = 0;
}
// display summary
function bcDisplaySum() {
	bcCount = 0;
	bcValue = 0;
	for (var i=0; i<bcList.length; i++) {
		bcCount += bcList[i]['count'];
		bcValue += parseFloat(bcList[i]['price'])*bcList[i]['count'];
	}
	var sumStr =  "货品: " + bcList.length  + "  " + "件数: " + bcCount.toString() + "  " + "金额: " + bcValue.toFixed(2);
	document.getElementById("mdbcTitle").innerHTML = sumStr;
	document.getElementById("mdbcTitle1").innerHTML = sumStr;
}

// query record by barcode
function queryItembyBarcode(code1) { 
	if (code1 == "")
		return false;
	var ok = -1;
	for (var i=0; i<a_ivariants.length; i++) {
		if (a_ivariants[i]['barcode'] == null && a_ivariants[i]['code1'] == code1) {
			ok = i;
			break;
		}
		if (a_ivariants[i]['barcode'] == code1) {
			ok = i;
			break;
		}
	}
	if (ok >= 0) {
		bcInv = a_ivariants[ok];
		bcUpdateList();
		document.getElementById("bcScanInfo").style.color = "white";
		document.getElementById("bcScanInfo").style.backgroundColor = "green";
		document.getElementById("bcScanInfo").innerText = "条码有效";
		playBeep(1);
	} else {
		document.getElementById("bcScanInfo").style.color = "white";
		document.getElementById("bcScanInfo").style.backgroundColor = "red";
		document.getElementById("bcScanInfo").innerText = "条码错误";
		playBeep(0);
	}		
}
// play beep
function playBeep(option) {
	if (option == 0)
		beep(400,300,100,'square'); //document.getElementById('playBeepNo').play();
	else
		beep(400,600,100,'sine'); //document.getElementById('playBeepYes').play();
}
const myAudioContext = new AudioContext();
function beep(duration, frequency, volume, style){
    return new Promise((resolve, reject) => {
        // Set default duration if not provided
        duration = duration || 200;
        frequency = frequency || 440;
        volume = volume || 100;

        try{
            let oscillatorNode = myAudioContext.createOscillator();
            let gainNode = myAudioContext.createGain();
            oscillatorNode.connect(gainNode);

            // Set the oscillator frequency in hertz
            oscillatorNode.frequency.value = frequency;

            // Set the type of oscillator
            oscillatorNode.type= style;
            gainNode.connect(myAudioContext.destination);

            // Set the gain to the volume
            gainNode.gain.value = volume * 0.01;

            // Start audio with the desired duration
            oscillatorNode.start(myAudioContext.currentTime);
            oscillatorNode.stop(myAudioContext.currentTime + duration * 0.001);

            // Resolve the promise when the sound is finished
            oscillatorNode.onended = () => {
                resolve();
            };
        }catch(error){
            reject(error);
        }
    });
}
// update list
function bcUpdateList() {
	var listItem = findArray(bcList, "i_id", bcInv['i_id']);
	if (listItem == null) {
		bcCreateRecord();
		bcScrollBottom();
	}
	else {
		if (bcInv['variant'] != null) {
			var listSubItem = findArray(listItem['subitems'], "iv_id", bcInv['iv_id']); 
			if (listSubItem == null) {
				bcCreateSub(listItem);
				bcScrollBottom();
			} else {
				bcUpdateSub(listSubItem, 1);
			}
		} else {
			var listSubItem = listItem['subitems'][0];
			bcUpdateSub(listSubItem, 1);
		}
		bcUpdateRecord(listItem);
	}
	bcDisplaySum();
}
// scroll body to bottom
function bcScrollBottom() {
	var bcModalBody = document.getElementById("bcModalBody");
//	bcModalBody.scrollTop += bcRecordHeight;
	bcModalBody.scrollTop = bcModalBody.scrollHeight - bcModalBody.clientHeight;
}
function bcUpdateRecord(listItem) {
	var count = 0;
	for (var i=0; i<listItem['subitems'].length; i++) {
		count += listItem['subitems'][i]['amount'];
	}
	listItem['count'] = count;
	var id = "bc_count_"+listItem['i_id'];	
	document.getElementById(id).innerText = count;	
}
// create record
function bcCreateRecord() {
	// col
	var col = document.createElement("div");
	col.classList.add("col");
	col.classList.add("p-1");
	bcBody.appendChild(col);
	// row
	var row = document.createElement("div");
	row.classList.add("row");	
	col.appendChild(row);
	// col1 - ArtNr
	var col1 = document.createElement("div");
	col1.classList.add("col-6");
	row.appendChild(col1);
	var artNo = document.createElement("label"); 
	if(bcInv['discount'] !== null && bcInv['discount'] > 0)
		artNo.innerText = bcInv['i_code'] + " (促销)";
	else
		artNo.innerText = bcInv['i_code'];
	col1.appendChild(artNo);
	// col4 - price
	var col4 = document.createElement("div");
	col4.classList.add("col-3");
	col4.align = "right";
	row.appendChild(col4);
	var price = document.createElement("label");
	if(bcInv['discount'] !== null && bcInv['discount'] > 0)
    	price.innerHTML = "<a style='text-decoration-line: line-through;'>"+bcInv['price']+"</a> " + ((bcInv['price'] * bcInv['discount'])/100).toFixed(2)+"&euro;";
	else
		price.innerHTML = bcInv['price']+"&euro;";
	col4.appendChild(price);
	// col3 - count
	var col3 = document.createElement("div");
	col3.classList.add("col-3");
	col3.align = "right";
	row.appendChild(col3);
	var count = document.createElement("label");
	count.id = "bc_count_"+bcInv['i_id'];
	count.style.fontWeight = "bold";
	count.style.color = "green";
	count.innerText = "1";
	col3.appendChild(count);
	var unit = document.createElement("label");
	unit.style.fontSize = "x-small"; 
	unit.innerHTML = "&nbsp;x" + bcInv['unit'];
	col3.appendChild(unit);
	// hr
	bcCreateHr(col);
	// add new list item
	var listItem = new Object();
	listItem['inv'] = bcInv;
	listItem['col'] = col;
	listItem['row'] = row;
	listItem['i_id'] = bcInv['i_id'];
	listItem['price'] = bcInv['price'];
	if(bcInv['discount'] !== null && bcInv['discount'] > 0)
		listItem['discount'] = bcInv['discount'];
	listItem['count'] = 1;	
	var listSubItems = new Array();
	listItem['subitems'] = listSubItems;
	// create sub record
	bcCreateSub(listItem);	
	// add item to list
	bcList.push(listItem);
	bcCount++;
}
// create hr
function bcCreateHr(col) {
	var rowHr = document.createElement("div");
	rowHr.classList.add("row");
	col.appendChild(rowHr);
	var hr = document.createElement("hr");
	hr.style = "border:1px solid lightgrey; margin:2px; width:100%";
	rowHr.appendChild(hr);
}
// create sub record
function bcCreateSub(listItem) {
	var option = 0;
	if (bcInv['variant'] != null)
		option = 1;
	col = listItem['col'];
	// row
	var row = document.createElement("div");
	row.classList.add("row");
	col.appendChild(row);
	// colImg
	var colImg = document.createElement("div");
	colImg.classList.add("col-2");
	colImg.classList.add("p-1");
	row.appendChild(colImg);
	var img = document.createElement("img");
	img.width="60";
	img.height="60";
	img.style="object-fit: cover";
	var imgNo = null;
	if (option == 0)
		imgNo = bcInv['m_no'];
	else
		imgNo = bcInv['im_no'];
	if (imgNo == null) 
		img.src = "blank.jpg";
	else
		img.src = bcInv['path']+"/"+bcInv['i_id']+"_"+imgNo+".jpg";
	colImg.appendChild(img);
	// colVariant
	var colVar = document.createElement("div");
	colVar.classList.add("col-4");
	colVar.classList.add("p-1");
	colVar.classList.add("align-self-center");
	colVar.align = "center";
	row.appendChild(colVar);
	var vart = document.createElement("label");
	vart.style.textAlign = "center";
	if(bcInv['size'] == null) bcInv['size'] = "";
	if (option == 0) {
		if (bcInv['color'] != null)
			vart.innerText = bcInv['color'];
		else
			vart.innerText = "";
	}
	else
		vart.innerText = bcInv['variant']+" "+bcInv['size'];
	colVar.appendChild(vart);
	// colCount
	var colCount = document.createElement("div");
	colCount.classList.add("col-6");
	colCount.classList.add("p-1");
	colCount.align = "right";
	row.appendChild(colCount);
	// button minus
	var btnMinus = document.createElement("label");
	btnMinus.style.fontSize = "36px";
	if (option == 0) 
		btnMinus.id = "bc_minus_"+bcInv['i_id']+"_0";
	else
		btnMinus.id = "bc_minus_"+bcInv['i_id']+"_"+bcInv['iv_id'];
	btnMinus.innerHTML = "&nbsp;&minus;&nbsp;";
	btnMinus.addEventListener("click", bcMinus);
	colCount.appendChild(btnMinus);
	// count
	var count = document.createElement("label");
	count.style.fontSize = "36px";
	count.style.width = "50px";
	count.style.textAlign = "center";
	if (option == 0) 
		count.id = "bc_amount_"+bcInv['i_id']+"_0";
	else
		count.id = "bc_amount_"+bcInv['i_id']+"_"+bcInv['iv_id'];
	count.innerText = "1";	
	count.addEventListener("click", bcShowInput);
	colCount.appendChild(count);
	// button add
	var btnAdd = document.createElement("label");
	btnAdd.style.fontSize = "36px";
	if (option == 0) 
		btnAdd.id = "bc_add_"+bcInv['i_id']+"_0";
	else
		btnAdd.id = "bc_add_"+bcInv['i_id']+"_"+bcInv['iv_id'];
	btnAdd.innerHTML = "&nbsp;&plus;&nbsp;";
	btnAdd.addEventListener("click", bcAdd);
	colCount.appendChild(btnAdd);
	// hr
	bcCreateHr(col);
	// list sub item
	var listSubItem = new Object();
	listSubItem['row'] = row;
	listSubItem['i_id'] = listItem['i_id'];
	if (option == 0)
		listSubItem['iv_id'] = "0";
	else
		listSubItem['iv_id'] = bcInv['iv_id'];
	listSubItem['amount'] = 1;
	listItem['subitems'].push(listSubItem);
	// record height
	if (option == 0) {
		var rect = col.getBoundingClientRect(); 
		bcRecordHeight = rect.height;
	}	
}
// show input amount
var bcIMItem, bcIMSubItem;
$modalBcInput = $('#modalBcInput');
$modalBcInput.on('shown.bs.modal', function () {
	$("#bc_input_amount").trigger('focus');
})
function bcShowInput(e) {
	var id = e.target.id;
	var index = id.replace("bc_amount_", "");
	var delim = index.indexOf("_");
	var thisIId = index.substr(0, delim);
	var thisIvId = index.substr(delim+1);
	bcIMItem = findArray(bcList, "i_id", thisIId); 
	bcIMSubItem = findArray(bcIMItem["subitems"], "iv_id", thisIvId); 
	document.getElementById("bc_input_amount").value = "";
	$modalBcInput.modal();
	$modalBcInput.focus();
}
function bcCancelInputAmount() {	
	$modalBcInput.modal("toggle");
	$modalBarcode.focus();
}
function bcDoneInputAmount() {
	var amount = document.getElementById("bc_input_amount").value;
	$modalBcInput.modal("toggle");
	$modalBarcode.focus();
	if (amount == "")
		return;
	bcUpdateSub(bcIMSubItem, 0, parseInt(amount));
	bcUpdateRecord(bcIMItem);
	bcDisplaySum();
}
// add amount
function bcAdd(e) {
	var id = e.target.id;
	var index = id.replace("bc_add_", "");
	var delim = index.indexOf("_");
	var thisIId = index.substr(0, delim);
	var thisIvId = index.substr(delim+1); 
	var listItem = findArray(bcList, "i_id", thisIId); 
	var subItem = findArray(listItem["subitems"], "iv_id", thisIvId);
	bcUpdateSub(subItem, 1);
	bcUpdateRecord(listItem);
	bcDisplaySum();
}
// minus amount
function bcMinus(e) {
	var id = e.target.id;
	var index = id.replace("bc_minus_", "");
	var delim = index.indexOf("_");
	var thisIId = index.substr(0, delim);
	var thisIvId = index.substr(delim+1);	
	var listItem = findArray(bcList, "i_id", thisIId);
	var subItem = findArray(listItem["subitems"], "iv_id", thisIvId);
	bcUpdateSub(subItem, -1);
	bcUpdateRecord(listItem);
	bcDisplaySum();
}
// update sub record
function bcUpdateSub(listSubItem, op, value) {
	if (op == 0)
		listSubItem['amount'] = value;
	else if (op > 0)
		listSubItem['amount']++;
	else {
		listSubItem['amount']--;
	}
	var id = "bc_amount_"+listSubItem['i_id']+"_"+listSubItem['iv_id']; 
	document.getElementById(id).innerText = listSubItem['amount'].toString();
}
// close modal
function bcCancel() {
	if (bcList.length > 0) {
		if (!confirm("数据尚未保存，确定退出?"))
			return;
	}
	$modalBarcode.modal("toggle");
    stopscann();
}
function bcDone() {
	if (bcList.length > 0)
		bcSave();
	$modalBarcode.modal("toggle");
    stopscann();
}
// save item
function bcSave() {



	var index = -1;
	for (var i=0; i<bcList.length; i++) {
		if (bcList[i]['count'] <= 0)
			continue;

		var code = bcList[i]['inv']['i_code'];
		var count = bcList[i]['count'];
		var price = bcList[i]['price'];	
		var discount = 0;

		var calcPrice = price;
		var priceStr = price;
		if(discount > 0){
			calcPrice = (((100-discount) * price) /100).toFixed(2);
			priceStr = "<a style='text-decoration-line: line-through;'>"+price+"</a> "+calcPrice;
		}
		var ivar = findArray(a_ivariants, "i_code", code);
		if (ivar != null){
			thisItem['i_id'] = ivar['i_id'];
			thisItem['path'] = ivar['path'];
			thisItem['m_no'] = ivar['m_no'];
			thisItem['i_code'] = ivar['i_code'];
			thisItem['i_name'] = ivar['i_name'];
			thisItem['ai_code'] = ivar['ai_code'];
			thisItem['a_name'] = ivar['i_name'];
		}else{
			alert("没找到名称!");
			return;
		}
		var name = thisItem['a_name'];	
		
		// Get a_id
		for (var i_art_tmp=0; i_art_tmp<myArts.length; i_art_tmp++) {
			if (myArts[i_art_tmp]['a_name'] == ivar['t_name']) {
				var aid = myArts[i_art_tmp]['a_id'];
				var cost = myArts[i_art_tmp]['cost'];
				break;
			}
		}

		// Init new item
		thisItem['id'] = itemIdCount;
		thisItem['r_id'] = rId;
		thisItem['ai_code'] = code;
		thisItem['ai_id'] = itemIdCount;
		thisItem['a_name'] = name;
		thisItem['a_id'] = aid;
		//thisItem['i_id'] = "0";
		thisItem['count'] = count;
		thisItem['cost'] = cost;
		thisItem['price'] = price;
		thisItem['discount'] = discount;
		thisItem['unit'] = "1";
		thisItem['real_count'] = count;
		var subtotal = parseInt(count)*parseFloat(calcPrice);
		thisItem['subtotal'] = subtotal.toFixed(2);
		// Add new item to orderItems
		orderItems[itemCount] = thisItem;
		// Add new row
		var rows = [];
		var imgStr = "";
		if (thisItem['i_id'] != "0") {
			var imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+thisItem['m_no']+"_s.jpg";
			imgStr = "<img width='40' height='60' style='border:1px dotted; object-fit: cover' src='"+imgSrc+"' >";
		}
		rows.push({
			id: thisItem['id'],
			idx_image: imgStr,
			idx_code: thisItem['ai_code'],
			idx_name: thisItem['a_name'],
			idx_count: thisItem['count'],
			idx_price: priceStr,
			idx_subtotal: thisItem['subtotal']
		});	
		$table.bootstrapTable('append', rows);
		// Recalculate summary
		var countSum = parseInt(order['count_sum']) + parseInt(thisItem['count']); 
		var priceSum = parseFloat(order['price_sum']) + subtotal;
		order['count_sum'] = countSum.toString();
		order['price_sum'] = priceSum.toFixed(2);
		displaySum();
		// Increase counts
		itemCount++;
		itemIdCount++;
		// Update database
		addDbItem(thisItem);
	}
}
//--------------------//









$modalNewItem.on('shown.bs.modal', function () {
	  $('#mdn_name').trigger('focus');
	  mdbcCode1 = "";
})
function selArt(e) {
	var x = $(e).text();
	document.getElementById("mdn_a_name").value = x;
}
function newItem() {	
	thisItem = new Object();
	thisItem['id'] = -1;
	thisItem['i_id'] = "0";
	document.getElementById("mdn_a_name").value = "";
	document.getElementById("mdn_code").value = "";
	document.getElementById("mdn_count").value = "";
	document.getElementById("mdn_price").value = "";
	document.getElementById("mdn_discount").value = "";
	document.getElementById("mdnBtnName").style.display = "block";
	document.getElementById("mdnBtnDel").style.display = "none";
	
	$modalNewItem.modal();
}
function mdnDone() {
	var code = document.getElementById("mdn_code").value;
	if (code == "") {
		$('#mdn_code').trigger('focus');
		return;
	}
	var count = document.getElementById("mdn_count").value;
	if (!notZero(count) || !onlyDigits(count)) {
		$('#mdn_count').trigger('focus');
		return;
	}
	count = parseInt(count).toString();
	var price = document.getElementById("mdn_price").value;	
	if (!notZero(price) || !onlyNumber(price)) {
		$('#mdn_price').trigger('focus');
		return;
	}
	price = parseFloat(price).toFixed(2);
	var discount = document.getElementById("mdn_discount").value;
	// Update item
	if (thisItem['id'] != -1) {
		$modalNewItem.modal("toggle");
		updateItem(count, price, code, null, discount);
		return;
	}
	var calcPrice = price;
	var priceStr = price;
	if(discount > 0){
		calcPrice = (((100-discount) * price) /100).toFixed(2);
		priceStr = "<a style='text-decoration-line: line-through;'>"+price+"</a> "+calcPrice;
	}
	var name = document.getElementById("mdn_a_name").value;	
	if (name == "") {
		$('#mdn_a_name').trigger('focus');
		return;
	}
	// Get a_id
	for (var i=0; i<myArts.length; i++) {
		if (myArts[i]['a_name'] == name) {
			var aid = myArts[i]['a_id'];
			var cost = myArts[i]['cost'];
			break;
		}
	}	
	$modalNewItem.modal("toggle");
	// Init new item
	thisItem['id'] = itemIdCount;
	thisItem['r_id'] = rId;
	thisItem['ai_code'] = code;
	thisItem['ai_id'] = itemIdCount;
	thisItem['a_name'] = name;
	thisItem['a_id'] = aid;
	//thisItem['i_id'] = "0";
	thisItem['count'] = count;
	thisItem['cost'] = cost;
	thisItem['price'] = price;
	thisItem['discount'] = discount;
	thisItem['unit'] = "1";
	thisItem['real_count'] = count;
	var subtotal = parseInt(count)*parseFloat(calcPrice);
	thisItem['subtotal'] = subtotal.toFixed(2);
	// Add new item to orderItems
	orderItems[itemCount] = thisItem;
	// Add new row
	var rows = [];
	var imgStr = "";
	if (thisItem['i_id'] != "0") {
		var imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+thisItem['m_no']+"_s.jpg";
		imgStr = "<img width='40' height='60' style='border:1px dotted; object-fit: cover' src='"+imgSrc+"' >";
	}
	rows.push({
		id: thisItem['id'],
		idx_image: imgStr,
		idx_code: thisItem['ai_code'],
		idx_name: thisItem['a_name'],
		idx_count: thisItem['count'],
		idx_price: priceStr,
		idx_subtotal: thisItem['subtotal']
	});	
	$table.bootstrapTable('append', rows);
	// Recalculate summary
	var countSum = parseInt(order['count_sum']) + parseInt(thisItem['count']); 
	var priceSum = parseFloat(order['price_sum']) + subtotal;
	order['count_sum'] = countSum.toString();
	order['price_sum'] = priceSum.toFixed(2);
	displaySum();
	// Increase counts
	itemCount++;
	itemIdCount++;
	// Update database
	addDbItem(thisItem);		
}
/************************************************************************
	VIEW/EDIT ITEM
************************************************************************/
$modalOrderItem.on('shown.bs.modal', function () {
	  $('#m_count').trigger('focus');
})
$table.on('click-row.bs.table', function (e, row, $element) {	
	var index = getItemIndexById(row.id);
	if (index < 0)
		return;
	thisItem = orderItems[index];
	if (thisItem['i_id'] != "0") {
		//Set values
		document.getElementById("m_i_code").value = thisItem['i_code'];
		document.getElementById("m_i_name").value = thisItem['i_name'];
		document.getElementById("m_count").value = thisItem['count'];
		document.getElementById("m_price").value = thisItem['price'];
		document.getElementById("m_discount").value = thisItem['discount'];
		document.getElementById("m_note").value = thisItem['note'];
		refreshSale();
		// unit
		if (thisItem['unit'] == "1") {
			document.getElementById("m_unit_str").value = "件 (x1)";	
		} else {
			document.getElementById("m_unit_str").value = "包 (x"+thisItem['unit']+")";	
		}	
		// Image
		document.getElementById("m_img").src = "blank.jpg";
		var imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+thisItem['m_no']+"_s.jpg";
		document.getElementById("m_img").src = imgSrc;
		
		$modalOrderItem.modal();
	} else {		
		document.getElementById("mdn_code").value = thisItem['ai_code'];
		document.getElementById("mdn_a_name").value = thisItem['a_name'];
		document.getElementById("mdn_count").value = thisItem['count'];
		document.getElementById("mdn_price").value = thisItem['price'];
		document.getElementById("m_discount").value = thisItem['discount'];
		document.getElementById("mdnBtnName").style.display = "none";
		document.getElementById("mdnBtnDel").style.display = "block";
		refreshSale();
		$modalNewItem.modal();
	}
});
/************************************************************************
	UPDATE ITEM (FROM ORDER)
************************************************************************/
// Done order item
function mdoDone() {
	var count = document.getElementById("m_count").value;
	if (!notZero(count) || !onlyDigits(count)) {
		$('#m_count').trigger('focus');
		return;
	}
	var price = document.getElementById("m_price").value;
	if (!notZero(price) || !onlyNumber(price)) {
		$('#m_price').trigger('focus');
		return;
	}
	var note = document.getElementById("m_note").value;
	$modalOrderItem.modal("toggle");	
	var discount = document.getElementById("m_discount").value;	
	updateItem(count, price, null, note, discount);
}
/************************************************************************
	UPDATE ITEM
************************************************************************/
function updateItem(count, price, code, note, discount) {
	// we do NOT update thisItem yet, because we need calculate sum and diff
	var subtotal = 0, countStr = "";
	if (thisItem['unit'] == "1") {
		countStr = count;
		real_count = count;
	}
	else {
		countStr = count+" (x"+thisItem['unit']+")";
		real_count = (parseInt(count)*parseInt(thisItem['unit'])).toString();		
	}
	var calcPrice = price;
	var priceStr = price;
	if(discount > 0){
		calcPrice = (((100-discount) * price) /100).toFixed(2);
		priceStr = "<a style='text-decoration-line: line-through;'>"+price+"</a> "+calcPrice;
	}
	subtotal =  parseInt(real_count)*parseFloat(calcPrice);
	// update table	
	var idx = thisItem['id'];
	$table.bootstrapTable('updateCellByUniqueId', {
        id: idx,
        field: 'idx_count',
        value: countStr
     })
	 $table.bootstrapTable('updateCellByUniqueId', {
        id: idx,
        field: 'idx_price',
        value: priceStr
     })
	 $table.bootstrapTable('updateCellByUniqueId', {
        id: idx,
        field: 'idx_subtotal',
        value: subtotal.toFixed(2)
     })
	 if (code != null && code != "") {
		 $table.bootstrapTable('updateCellByUniqueId', {
			id: idx,
			field: 'idx_code',
			value: code
		})
	 }	 
	// create item for database
	var item = new Object();
	item['r_id'] = thisItem['r_id'];
	item['i_id'] = thisItem['i_id'];
	item['a_id'] = thisItem['a_id'];
	item['ai_id'] = thisItem['ai_id'];
	item['ai_code'] = thisItem['ai_code'];
	item['cost'] = thisItem['cost'];
	item['price'] = price;
	item['unit'] = thisItem['unit'];
	item['note'] = note;
	item['discount'] = discount;
	var diff = parseInt(thisItem['count']) - parseInt(count); 
	item['count'] = diff.toString();
	if (diff > 0) {
		item['count'] = diff.toString();
		updateDbItem(item, 1);
	} else {
		var diff1 = 0 - diff;
		item['count'] = diff1.toString();
		updateDbItem(item, 0);
	}
	// update sum
	var countSum = parseInt(order['count_sum']);
	var priceSum = parseFloat(order['price_sum']);
	countSum = countSum - parseInt(thisItem['real_count']) + parseInt(real_count);
	priceSum = priceSum - parseFloat(thisItem['subtotal']) + subtotal;
	order['count_sum'] = countSum.toString();
	order['price_sum'] = priceSum.toFixed(2);
	displaySum();
	// update thisItem
	thisItem['count'] = count;
	thisItem['real_count'] = real_count;
	thisItem['price'] = price;
	thisItem['subtotal'] = subtotal.toFixed(2);	
	thisItem['note'] = note;
}
/************************************************************************
	DELETE ITEM
************************************************************************/
// Delete order item
function mdoDel() {
	if (!confirm("确定要删除该项目?")) {
		return;
	}
	$modalOrderItem.modal("toggle");
	delItem();
}
// Delete new item
function mdnDel() {
	if (!confirm("确定要删除该项目?")) {
		return;
	}
	$modalNewItem.modal("toggle");
	delItem();
}
// Deletion
function delItem() {
	// Remove item from table
	var idx = thisItem['id'];
	$table.bootstrapTable('removeByUniqueId', idx);
	// Delete from database
	delDbItem(thisItem);
	// Recalculate summary
	var countSum = parseInt(order['count_sum']);
	var priceSum = parseFloat(order['price_sum']);
	countSum = countSum - parseInt(thisItem['real_count']);
	priceSum = priceSum - parseFloat(thisItem['subtotal']);
	order['count_sum'] = countSum.toString();
	order['price_sum'] = priceSum.toFixed(2);
	// Remove item form orderItems
	var index = getItemIndexById(idx);
	orderItems.splice(index, 1);
	itemCount = itemCount - 1;
	displaySum();		
}
/************************************************************************
	DATABASE FUNCTIONS
************************************************************************/
// Prepare order record for database
function prepareOrder() {
	if (order['k_id'] == "" || order['k_id'] == "0")
		order['k_id'] = "0";
	
	var profit = 0;
	for (var i=0; i<orderItems.length; i++) {
		profit += parseInt(orderItems[i]['count']) * (parseFloat(orderItems[i]['price'])  - parseFloat(orderItems[i]['cost']));
	}
	order['profit'] = profit.toFixed(2);
}
function submitOk1(ok) {
//	alert("项目操作成功");
}
function displayError1(err) {
//	alert("项目操作过程中出现问题!");
}
// Add orderItem to database
function addDbItem(item) {	
	var link = "postInvoiceItemAdd.php";
	var form = new FormData();
	form.append('orderitem', JSON.stringify(item));
	postRequest(link, form, submitOk1, displayError1);	
}
// Delete orderItem from database
function delDbItem(item) {
	var link = "postInvoiceItemDel.php";
	var form = new FormData();
	form.append('orderitem', JSON.stringify(item));
	postRequest(link, form, submitOk1, displayError1);
}
// Update orderItem in database
function updateDbItem(item, option) {
	var link = "postInvoiceItemUpdate.php";
	var form = new FormData();
	form.append('option', option);
	form.append('orderitem', JSON.stringify(item));
	postRequest(link, form, submitOk1, displayError1);
}
/* SAVE INVOICE */
function submitOk(ok) {
//	alert("发票添加成功!");
		
}
function displayError(err) {
//	alert("发票添加过程中出现问题!");
}
function saveDbOrder() {
	// Prepare order record for database
	prepareOrder();
	// Update order in database
	var link = "postInvoiceUpdate.php";
	var form = new FormData(); 
	form.append('order', JSON.stringify(order));
	postRequest(link, form, submitOk, displayError);
}
/************************************************************************
	TAX
************************************************************************/
/* Show modalOrderTax */
$modalOrderTax.on('shown.bs.modal', function () {
	$("#mt_tax_rate").trigger('focus');
})

function displayTax() {
	var tax = parseFloat(order['total_sum'])*parseFloat(order['tax_rate'])/100;
	document.getElementById("mt_tax_rate").value = order['tax_rate'];
	document.getElementById("mt_tax").value = tax.toFixed(2);
}

function showTax() {
	displayTax();
	$modalOrderTax.modal();	
}

function doneTax() {
	var data = checkNumber("mt_tax_rate", 0, 100);
	if (!data) {
		$("#mt_tax_rate").trigger('focus');
			return false;
	}
	else
		order['tax_rate'] = data;

	var tax = parseFloat(order['total_sum'])*parseFloat(order['tax_rate'])/100;
	document.getElementById("mt_tax").value = tax.toFixed(2);
	
	$modalOrderTax.modal("toggle");
	displaySum();
}
/************************************************************************
	DISCOUNT
************************************************************************/
$modalOrderDis.on('shown.bs.modal', function () {
	$("#mdi_discount_rate").trigger('focus');
})

function displayDis() {
	var discount = parseFloat(order['price_sum'])*parseFloat(order['discount_rate'])/100;
	document.getElementById("mdi_discount_rate").value = displayValue(order['discount_rate']);
	document.getElementById("mdi_discount").value = discount.toFixed(2);
}

function showDis() {
	displayDis();
	$modalOrderDis.modal();	
}
function changDis(){
	var discount = document.getElementById("mdi_discount").value;
	var discount_rate = (discount / parseFloat(order['price_sum'])) * 100;
	document.getElementById("mdi_discount_rate").value = displayValue(discount_rate.toFixed(2));
}
function displayDiscount() {
	var discount_rate = document.getElementById("mdi_discount_rate").value;
	var discount = parseFloat(order['price_sum'])*discount_rate/100;
	document.getElementById("mdi_discount").value = discount.toFixed(2);
}
function doneDis() {
	var data = checkNumber("mdi_discount_rate", 0, 100);
	if (!data) {
		$("#mdi_discount_rate").trigger('focus');
			return false;
	}
	else
		order['discount_rate'] = data;
	
	var discount = parseFloat(order['price_sum'])*parseFloat(order['discount_rate'])/100;
	document.getElementById("mdi_discount").value = discount.toFixed(2);
	
	$modalOrderDis.modal("toggle");
	displaySum();
}
/************************************************************************
	FEES
************************************************************************/
$modalOrderFee.on('shown.bs.modal', function () {
	$("#mf_fee1").trigger('focus');
})

function displayFee() {
	document.getElementById("mf_fee1").value = displayValue(order['fee1']);
	document.getElementById("mf_fee2").value = displayValue(order['fee2']);
	document.getElementById("mf_fee3").value = displayValue(order['fee3']);
	document.getElementById("mf_fee4").value = displayValue(order['fee4']);
	document.getElementById("mf_fee5").value = displayValue(order['fee5']);
}

function showFee() {
	displayFee();
	$modalOrderFee.modal();	
}

function doneFee() {	
	var fees = ["fee1", "fee2", "fee3", "fee4", "fee5"];
	var data = "";
	
	for (var i=0; i<5; i++) {
		data = checkNumber("mf_"+fees[i], 0, parseFloat(order['price_sum']));
		if (!data) {
			alert("输入的数据有误");
			$("#mf_"+fees[i]).trigger('focus');
			return false;
		}
		else
			order[fees[i]] = data;
	}
	
	$modalOrderFee.modal("toggle");	
	displaySum();
}
/************************************************************************
	PAYMENT
************************************************************************/
var pays = ["pay_cash", "pay_card", "pay_bank", "pay_check", "pay_other", "pay_paypal", "pay_vorkasse"];
var pays_data = {};
var pays_due = 0, pays_total = 0;
var $tablePay = $("#tablePay");
var pays_count = 0;

$tablePay.bootstrapTable({   
	formatNoMatches: function () {
         return "没有付款项目";
    }
});

$modalOrderPay.on('shown.bs.modal', function () {
	$("#mp_amount").trigger('focus');
})
// Display payment
function displayPay() {
	pays_count = 0;
	pays_total = 0;
	var rows = [];
	$tablePay.bootstrapTable('removeAll');
	for (var i=0; i<7; i++) {
		pays_data[pays[i]] = order[pays[i]];
		pays_total += parseFloat(pays_data[pays[i]]);
		if (pays_data[pays[i]] != "" && pays_data[pays[i]] != "0.00" && pays_data[pays[i]] != "0") {
			var type_value;
			switch (pays[i]) {
				case "pay_card": type_value = "Karte"; break;
				case "pay_bank": type_value = "Überweisung"; break;
				case "pay_check": type_value = "Scheck"; break;
				case "pay_other": type_value = "Nachnahme"; break;
				case "pay_paypal": type_value = "PayPal"; break;
				case "pay_vorkasse": type_value = "Vorkasse"; break;
				default: type_value = "Bar";
			}
			var delStr = "<button type='button' class='btn btn-secondary' onclick='delPay()'><span class='fa fa-trash'></button>";
			rows.push({
				id: pays_count,
				idx_type: pays[i],
				idx_value: type_value,
				idx_amount: pays_data[pays[i]],
				idx_del: delStr
			});
			pays_count++;			
		}
	}
	$tablePay.bootstrapTable('append', rows);
	pays_due = parseFloat(order['due']);
	document.getElementById("mp_amount").value = pays_due.toFixed(2);	
	document.getElementById("mp_total").value = order['net'];
	document.getElementById("mp_pays_total").value = pays_total.toFixed(2);
	document.getElementById("mp_pays_due").value = pays_due.toFixed(2);
	if(order['isPayed'] == 1){
		document.getElementById("mdp_isPayed").checked = true;
		document.getElementById("mdp_paidDatum").value = order['paidDatum'];
	}
	else {
		document.getElementById("mdp_isNotPayed").checked = true;
		document.getElementById("mdp_paidDatum").value = "";
	}
}
// Show modalOrderPay
function showPay() {
	if (itemCount <= 0)
		return;
	displayPay();
	$modalOrderPay.modal();	
}
// Select payment type
function selPay(e) {
	// check if payment type exists
	var type_value = $(e).text();
	var type;
	switch (type_value) {
		case "Karte": type = "pay_card"; break;
		case "Überweisung": type = "pay_bank"; break;
		case "Scheck": type = "pay_check"; break;
		case "Nachnahme": type = "pay_other"; break;
		case "PayPal": type = "pay_paypal"; break;
		case "Vorkasse": type = "pay_vorkasse"; break;
		default: type = "pay_cash";
	}
	for (var i=0; i<7; i++) {
		if (type == pays[i] && notZero(pays_data[pays[i]]))
			break;
	}
	if (i < 7) {
		alert("付款方式已存在");
		$("#mp_amount").trigger('focus');
		return;
	}
	// check amount	
	var amount = document.getElementById("mp_amount").value; 
	if (!onlyNumber(amount) || parseFloat(amount) < 0 || parseFloat(amount) > parseFloat(pays_due.toFixed(2))) {
		alert("输入的数据错误");
		$("#mp_amount").trigger('focus');
		return;
	}
	var delStr = "<button type='button' class='btn btn-secondary' onclick='delPay()'><span class='fa fa-trash'></button>";
	
	var rows = [];
	rows.push({
		id: pays_count,
		idx_type: type,
		idx_value: type_value,
		idx_amount: amount,
		idx_del: delStr
	});		
	$tablePay.bootstrapTable('append', rows);
	pays_count++;
	
	pays_total += parseFloat(amount); 
	pays_due = parseFloat(order['net']) - pays_total; 
	document.getElementById("mp_pays_total").value = pays_total.toFixed(2);
	document.getElementById("mp_pays_due").value = pays_due.toFixed(2);
	document.getElementById("mp_amount").value = pays_due.toFixed(2);
	$("#mp_amount").trigger('focus');
	
	pays_data[type] = amount;
}
// Delete payment type
var delPayOk = 0;
$tablePay.on('click-row.bs.table', function (e, row, $element) {
	if (!delPayOk)
		return;
	var id = row.id;
	for (var i=0; i<7; i++) {
		if (row.idx_type == pays[i]) {
			pays_due += parseFloat(pays_data[pays[i]]);
			pays_total = pays_total - parseFloat(pays_data[pays[i]]);
			pays_data[pays[i]] = "0.00";
		}
	}		
	$tablePay.bootstrapTable('removeByUniqueId', id);
	delPayOk = 0;
	
	document.getElementById("mp_pays_total").value = pays_total.toFixed(2);
	document.getElementById("mp_pays_due").value = pays_due.toFixed(2);
	document.getElementById("mp_amount").value = pays_due.toFixed(2);
	$("#mp_amount").trigger('focus');
	
})
function delPay() {
	delPayOk = 1;
}
// Done payment
function donePay() {
	for (var i=0; i<7; i++) {
		order[pays[i]] = pays_data[pays[i]];
	}
	order['due'] = pays_due.toFixed(2);
	$modalOrderPay.modal("toggle");
	order['isPayed'] = 0;
	order['paidDatum'] = "";
	if(document.getElementById("mdp_isPayed").checked == true){
		order['isPayed'] = 1;
		order['paidDatum'] = document.getElementById("mdp_paidDatum").value;
	}
	displaySum();
}
/************************************************************************
	DESTORY INVOICE
************************************************************************/
function destroyDone(result) {
//	alert("发票已删除");
	var url = "<?php echo $backPhp; ?>";
	window.location.assign(url);
}

function destroyError(result) {
//	alert("发票删除出现错误");
}

function destroyOrder() {
	if (!confirm("删除该发票将导致所有相关的发票信息被删除。确认删除?")) {
		return;
	}
	showDelModal(delDbOrder);
	//delDbOrder();
}

function delDbOrder() {
	var link = "postInvoiceDel.php";
	var form = new FormData();
	form.append('order', JSON.stringify(order));
	if(orderItems && orderItems.length > 0)
		form.append('orderitems', JSON.stringify(orderItems));
	postRequest(link, form, destroyDone, destroyError);	
}
/************************************************************************
	CUSTOMER
************************************************************************/
// Search customer
function searchCust() {
	mksInit(1);
	$modalCustSearch.modal();
}
// View customer
function showCust() {
	if (order['k_id'] == "" || order['k_id'] == "0") {
		mksInit(1);
		$modalCustSearch.modal();
	} else {
		mkInit(myCustomer);
		$modalCust.modal();	
	}
}
// Update customer
function updateCust(customer) {
	myCustomer = customer;
	order['k_id'] = myCustomer['k_id'];
	if (notZero(myCustomer['discount']) && parseFloat(myCustomer['discount']) > 0 && parseFloat(myCustomer['discount']) < 100) {
		order['discount_rate'] = myCustomer['discount'];		
	} else {
		order['discount_rate'] = "0.00";
	}
	if (order['tax_rate'] == "0.00") {
		if(notGermanCust(myCustomer['ustno'])) {
			order['tax_rate'] = "0.00";
		} else {
			order['tax_rate'] = company['tax'];
		}	
	}

	document.getElementById("k_name").value = myCustomer['k_name'];
	displaySum();
}
// Done modalCustSearch
function mksDoneNext(customer) {
	updateCust(customer);
}
// Done modalCust
function mkSaveCust(customer) {
	updateCust(customer);
}
/************************************************************************
	EDIT INVOICE TIME
************************************************************************/
function changeTime() {
	if(!confirm("确定修改发票时间?"))
		return;
	document.getElementById("mdet_datum").value = convertDate(order['date'],1);
	document.getElementById("mdet_lieferdatum").value = convertDate(order['lieferdatum'],1);
	var now = new Date();
    var maxDate = now.toISOString().substring(0,10);
	$('#mdet_datum').prop('max', maxDate);
	$('#mdet_lieferdatum').prop('max', maxDate);
	$modalEditTime.modal();	
}

function doneEditTime() {
	var date = document.getElementById("mdet_datum").value;
	var lieferdate = document.getElementById("mdet_lieferdatum").value;

	order['date'] = date;
	order['lieferdatum'] = lieferdate;
	
	dbUpdateInvoiceDate();
	$modalEditTime.modal("toggle");
	document.getElementById("in_date").value = convertDate(order['date']);
}
/************************************************************************
	EDIT INVOICE NO
************************************************************************/
$modalEditInvoiceNo.on('shown.bs.modal', function () {
	$("#mdein_no").trigger('focus');
})
function editInvoiceNo() {
	if(!confirm("确定修改发票号?"))
		return;
	document.getElementById("mdein_no").value = "";
	$modalEditInvoiceNo.modal();
}
function checkInvoiceNoYes(result) {
	$modalEditInvoiceNo.modal("toggle");
	var in_no = result;	
	document.getElementById("invoice_no").value = in_no;
	order['invoice_no'] = in_no;
	
	dbUpdateInvoiceNo();
	dbUpdateInvoiceStatus();
}
function checkInvoiceNoNo(result) {
	alert("发票号不可使用");
	$("#mdein_no").trigger('focus');
}
function doneEditInvoiceNo() {
	var in_no = document.getElementById("mdein_no").value;
	if (!notZero(in_no)) {
		$("#mdein_no").trigger('focus');
		return;
	}
	getRequest("getCheckInvoiceNo.php?invoice_no="+in_no+"&year="+order['date'].substr(0, 4), checkInvoiceNoYes, checkInvoiceNoNo);
}
/************************************************************************
	ENTER
************************************************************************/
$('form input').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
		if ($("#mt_tax_rate").is(":focus")){
			var tax_rate = document.getElementById("mt_tax_rate").value;
			if (tax_rate == "" || !onlyNumber(tax_rate) || parseFloat(tax_rate) <=0 || parseFloat(tax_rate) >= 100) {
				$("#mt_tax_rate").trigger('focus');
				return false;
			}
			var tax = parseFloat(order['total_sum'])*parseFloat(tax_rate)/100;
			document.getElementById("mt_tax").value = tax.toFixed(2);
			
		} else if ($("#mdi_discount_rate").is(":focus")){
			
		} else
			return false;
    }
});
/************************************************************************
	OPTIONS
************************************************************************/
var opPrintNoName = false, opPrintNonEU = false;
function showOptions() {
	document.getElementById("mdopNote").value = order['note'];
	$modalOptions.modal();	
}
function doneOptions() {
	document.getElementById("mdopPrintNoName").checked ? opPrintNoName = true :opPrintNoName = false;
	document.getElementById("mdopPrintOnePage").checked ? opPrintOnePage = true :opPrintOnePage = false;
	document.getElementById("mdopPrintNonEU").checked ? opPrintNonEU = true :opPrintNonEU = false;
	var note = document.getElementById("mdopNote").value;
	order['note'] = note;	
	updateNote();
		
	$modalOptions.modal("toggle");
}
function updateNote() {
	var link = "postUpdateTableCol.php";
	var form = new FormData(); 
	form.append('table', "a_invoice");
	form.append('col', "note");
	form.append('value', order['note']);
	form.append('col1', "r_id");
	form.append('value1', order['r_id']);
	postRequest(link, form, null, null);	
}
/************************************************************************
	VALIDATION
************************************************************************/
function onlyDigits(s) {
	var d;
	for (var i=0; i<s.length; i++) {
		d = s[i];
		if (d < "0" || d > "9")
			return false;
	}
	return true;
}

function onlyNumber(s) {
	var d;
	for (var i=0; i<s.length; i++) {
		d = s[i];
		if ((d < "0" || d > "9") && d != "," && d != ".")
			return false;
	}
	return true;
}

function notZero(s) {
	if (s != "" && s != "0" && s != "0.00")
		return true;
	else
		return false;			
}
/************************************************************************
	FUNCTIONS
************************************************************************/
function countAdd() {
	var v = document.getElementById("m_count").value;
	if (v == "")
		v = "0";
	var d = parseInt(v);
	d++;
	document.getElementById("m_count").value = d.toString();
}

function countMinus() {
	var v = document.getElementById("m_count").value;
	if (v == "")
		return;
	var d = parseInt(v);
	if (d == 1)
		return;
	d--;
	document.getElementById("m_count").value = d.toString();	
}

function displayValue(v) {
	if (v == "0.00" || v == "0")
		return "";
	else
		return v;
}

function checkNumber(id, min, max) {
	var data = document.getElementById(id).value;
	if (data == "" || data == "0")
		return "0.00";
	if (!onlyNumber(data))
		return false;
	var d = parseFloat(data);	
	if (d < min || d > max)
		return false;
	
	return d.toFixed(2);
}
/************************************************************************
	OTHER DATABASE FUNCTIONS
************************************************************************/
/* UPDATE INVOICE_NO */
function dbUpdateInvoiceNo() {
	var form = new FormData();
	form.append('r_id', JSON.stringify(rId));
	form.append('invoice_no', JSON.stringify(order['invoice_no']));
	postRequest("postInvoiceNo.php", form, null, null);
}
/* UPDATE INVOICE STATUS */
function dbUpdateInvoiceStatus() {
	var link = "postInvoiceStatus.php";
	var form = new FormData();
	form.append('r_id', rId);
	form.append('status', "1");
	postRequest(link, form, null, null);
}
/* UPDATE INVOICE DATE */
function dbUpdateInvoiceDate() {		
	var link = "postInvoiceDate.php";
	var form = new FormData();
	form.append('r_id', JSON.stringify(rId));
	form.append('date', JSON.stringify(order['date']));
	form.append('lieferdatum', JSON.stringify(order['lieferdatum']));
	postRequest(link, form, null, null);
}
/************************************************************************
	CLOSE INVOICE
************************************************************************/
function closeInvoice() {
	var url = "<?php echo $backPhp; ?>";
	window.location.assign(url);
}
function closeOrder() {
	if (!notZero(order['invoice_no'])) {
		alert("发票没有完成. 请先保存或者打印发票, 您也可以删除当前发票");
		return;
	}
	 closeInvoice();
}
/************************************************************************
	SAVE INVOICE
************************************************************************/
function submitOrderYes(result) {
	document.getElementById("btnSave").disabled = false;
	document.getElementById("btnPrint").disabled = false;
	order['invoice_no'] = result;
	document.getElementById("invoice_no").value = order['invoice_no'];
	
	closeInvoice();
}
function submitOrderNo(result) {
	document.getElementById("btnSave").disabled = false;
	document.getElementById("btnPrint").disabled = false;
	alert("获取发票号出现错误, 请稍后再试");
}
function submitOrder() {
	if (itemCount <= 0) {
		alert("发票没有内容!");
		return;
	}
	if (order['k_id'] == "" || order['k_id'] == "0") {
		alert("请先输入客户信息");
		return;
	}
	if (!notZero(order['invoice_no'])) {
		if (!confirm("确认保存发票?"))
			return;
		document.getElementById("btnSave").disabled = true;
		document.getElementById("btnPrint").disabled = true;
		getRequest("getInvoiceNo.php?r_id="+order['r_id'], submitOrderYes, submitOrderNo);
	} else {
		 closeInvoice();
	}
		
}
/************************************************************************
	REFUND
************************************************************************/
function createRefund() {
	if (order['invoice_no'] == "0") {
		alert("没有发票号. 请先打印或保存发票");
		return;
	}		
	var form = new FormData();
	form.append('order', JSON.stringify(order));
	form.append('orderitems', JSON.stringify(orderItems));
	postRequest('postRefundAdd.php', form, refundYes, refundNo);		
}
function refundYes(result) {
	alert("退货单已创建，请到退货单列表中查看");
	$modalOptions.modal("toggle");
}
function refundNo(result) {
	alert("退货单出现错误，请稍后再试");
	$modalOptions.modal("toggle");
}

/************************************************************************
	VOID
************************************************************************/
function voidOrder() {
	if (order['invoice_no'] == "0") {
		alert("没有发票号. 请先打印或保存发票");
		return;
	}
	if (!confirm("确定要作废发票?"))
		return;
	var form = new FormData();
	form.append('order', JSON.stringify(order));
	form.append('orderitems', JSON.stringify(orderItems));
	postRequest('postInvoiceVoid.php', form, voidYes, voidNo);
}
function voidYes(result) {
	alert("发票已作废，请到作废发票列表中查看");
	closeInvoice();
}
function voidNo(result) {
	alert("系统错误，请稍后再试");
}

/************************************************************************
	PRINT
************************************************************************/
function getInNoYes(result) {
	document.getElementById("btnPrint").disabled = false;
	document.getElementById("btnSave").disabled = false;
	order['invoice_no'] = result;
	document.getElementById("invoice_no").value = order['invoice_no'];
	
	document.getElementById("btnVoid").style.display = "inline";
	document.getElementById("btnDestroy").style.display = "none";
	
	printInvoice();
}
function getInNoNo(result) {
	document.getElementById("btnPrint").disabled = false;
	document.getElementById("btnSave").disabled = false;
	alert("获取发票号出现错误, 请稍后再试");
}
function getInNoYes_Erechnung(result){
	document.getElementById("btnPrint").disabled = false;
	document.getElementById("btnSave").disabled = false;
	document.getElementById("btnERechnung").disabled = false;
	order['invoice_no'] = result;
	document.getElementById("invoice_no").value = order['invoice_no'];
	
	document.getElementById("btnVoid").style.display = "inline";
	document.getElementById("btnDestroy").style.display = "none";
	
	printInvoiceErechnung();
}
function eRechnungOrder(){
	if (itemCount <= 0) {
		alert("发票没有内容!");
		return;
	}
	if (order['k_id'] == "" || order['k_id'] == "0") {
		alert("请先输入客户信息");
		return;
	}
	// if new invoice, get invoice_no first
	if (!notZero(order['invoice_no'])) {
		if (!confirm("确认生成ERechnung?"))
			return;
		document.getElementById("btnPrint").disabled = true;
		document.getElementById("btnSave").disabled = true;
		document.getElementById("btnERechnung").disabled = true;
		getRequest("getInvoiceNo.php?r_id="+order['r_id'], getInNoYes_Erechnung, getInNoNo);
	}
	else
		printInvoiceErechnung();
}
function printOrder() {
	if (itemCount <= 0) {
		alert("发票没有内容!");
		return;
	}
	if (order['k_id'] == "" || order['k_id'] == "0") {
		alert("请先输入客户信息");
		return;
	}
	// if new invoice, get invoice_no first
	if (!notZero(order['invoice_no'])) {
		if (!confirm("确认打印发票?"))
			return;
		document.getElementById("btnPrint").disabled = true;
		document.getElementById("btnSave").disabled = true;
		getRequest("getInvoiceNo.php?r_id="+order['r_id'], getInNoYes, getInNoNo);
	}
	else
		printInvoice();
}
function printInvoiceErechnung(){
	window.open("api/xrechnung.php?id="+order['r_id']);
	return;
	var date_tmp = order['date'].substring(0,4)+order['date'].substring(5,7)+order['date'].substring(8,10);
	var date_tmp_2 = order['date'].substring(8,10)+"."+order['date'].substring(5,7)+"."+order['date'].substring(0,4);
	var liefer_date_tmp = order['lieferdatum'].substring(0,4)+order['lieferdatum'].substring(5,7)+order['lieferdatum'].substring(8,10);
	var output = '\<\?xml version="1.0" encoding="UTF-8" \?\><rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100" xmlns:a="urn:un:unece:uncefact:data:standard:QualifiedDataType:100" xmlns:qdt="urn:un:unece:uncefact:data:standard:QualifiedDataType:10" xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
    output += '<rsm:ExchangedDocumentContext>';
    output += '<ram:GuidelineSpecifiedDocumentContextParameter>';
    output += '<ram:ID>urn:cen.eu:en16931:2017</ram:ID>';
    output += '</ram:GuidelineSpecifiedDocumentContextParameter>';
    output += '</rsm:ExchangedDocumentContext>';
    
    output += '<rsm:ExchangedDocument>';
    output += '<ram:ID>'+order['invoice_no']+'</ram:ID>';
    output += '<ram:TypeCode>380</ram:TypeCode>';
    output += '<ram:IssueDateTime>';
    output += '<udt:DateTimeString format="102">'+date_tmp+'</udt:DateTimeString>';
    output += '</ram:IssueDateTime>';
    output += '<ram:IncludedNote>';
    output += '<ram:Content>Rechnung gemäß Bestellung vom '+date_tmp_2+'.</ram:Content>';
    output += '</ram:IncludedNote>';
    output += '<ram:IncludedNote>';
    output += '<ram:Content>'+company["c_name"]+' ';
    output += company["address"]+', ';
	output += company["post"] + ' ' + company["city"] +' ';
	output += company["country"]+' ';
	output += 'Geschäftsführer: ' + company["geschaeftsfuehrer"]+' ';
	output += 'Handelsregisternummer: ' + company["hrb"]+' ';
	//Geschäftsführer: Hans Muster
    //Handelsregisternummer: H A 123
    output += '</ram:Content>';
    output += '<ram:SubjectCode>REG</ram:SubjectCode>';
    output += '</ram:IncludedNote>';
    output += '</rsm:ExchangedDocument>';
    output += '<rsm:SupplyChainTradeTransaction>';
	for (i=0; i<itemCount; i++) {
		var priceStr = orderItems[i]['price'];
		var discount = orderItems[i]['discount'];
		var rabatt = "";
		if(discount > 0){
			rabatt = " (Rabatt: "+parseFloat(orderItems[i]['discount']).toFixed(0)+"%)";
			priceStr = (((100-discount) * orderItems[i]['price']) /100).toFixed(2);
		}

		var discount = 0;
		if (notZero(order['discount_rate'])) {
			rabatt += " (Rabatt: "+parseFloat(order['discount_rate']).toFixed(0)+"%)";
			discount = parseFloat(orderItems[i]['subtotal'])*parseFloat(order['discount_rate'])/100;
			orderItems[i]['subtotal'] = (parseFloat(orderItems[i]['subtotal']) - discount).toFixed(2);
		}

		output += '<ram:IncludedSupplyChainTradeLineItem>';
        output += '<ram:AssociatedDocumentLineDocument>';
        output += '<ram:LineID>'+(i+1)+'</ram:LineID>';
        output += '</ram:AssociatedDocumentLineDocument>';
        output += '<ram:SpecifiedTradeProduct>';
        output += '<ram:GlobalID schemeID="0160">'+orderItems[i]['i_code']+'</ram:GlobalID>';
        output += '<ram:SellerAssignedID>'+orderItems[i]['i_id']+'</ram:SellerAssignedID>';
        output += '<ram:Name>'+orderItems[i]['i_name']+' ' + rabatt +'</ram:Name>';
        output += '</ram:SpecifiedTradeProduct>';
        output += '<ram:SpecifiedLineTradeAgreement>';
        output += '<ram:GrossPriceProductTradePrice>';
        output += '<ram:ChargeAmount>'+priceStr+'</ram:ChargeAmount>';
        output += '</ram:GrossPriceProductTradePrice>';
        output += '<ram:NetPriceProductTradePrice>';
        output += '<ram:ChargeAmount>'+priceStr+'</ram:ChargeAmount>';
        output += '</ram:NetPriceProductTradePrice>';
        output += '</ram:SpecifiedLineTradeAgreement>';
        output += '<ram:SpecifiedLineTradeDelivery>';
        output += '<ram:BilledQuantity unitCode="H87">'+orderItems[i]['real_count']+'</ram:BilledQuantity>';
        output += '</ram:SpecifiedLineTradeDelivery>';
        output += '<ram:SpecifiedLineTradeSettlement>';
        output += '<ram:ApplicableTradeTax>';
        output += '<ram:TypeCode>VAT</ram:TypeCode>';
        output += '<ram:CategoryCode>S</ram:CategoryCode>';
        output += '<ram:RateApplicablePercent>'+order['tax_rate']+'</ram:RateApplicablePercent>';
        output += '</ram:ApplicableTradeTax>';
        output += '<ram:SpecifiedTradeSettlementLineMonetarySummation>';
        output += '<ram:LineTotalAmount>'+orderItems[i]['subtotal']+'</ram:LineTotalAmount>';
        output += '</ram:SpecifiedTradeSettlementLineMonetarySummation>';
        output += '</ram:SpecifiedLineTradeSettlement>';
        output += '</ram:IncludedSupplyChainTradeLineItem>';
	}
	
	// kosten //
	var fee = 0;
// Fees
if (notZero(order['fee1'])) {
		fee += parseFloat(order['fee1']);
	}
	if (notZero(order['fee2'])) {
		fee += parseFloat(order['fee2']);
	}
	if (notZero(order['fee3'])) {
		fee += parseFloat(order['fee3']);
	}
	if (notZero(order['fee4'])) {
		fee += parseFloat(order['fee4']);
	}
	if (notZero(order['fee5'])) {
		fee += parseFloat(order['fee5']);
	}
	if(fee > 0){
		output += '<ram:IncludedSupplyChainTradeLineItem>';
        output += '<ram:AssociatedDocumentLineDocument>';
        output += '<ram:LineID>'+(i+1)+'</ram:LineID>';
        output += '</ram:AssociatedDocumentLineDocument>';
        output += '<ram:SpecifiedTradeProduct>';
        output += '<ram:Name>Extra Kosten</ram:Name>';
        output += '</ram:SpecifiedTradeProduct>';
        output += '<ram:SpecifiedLineTradeAgreement>';
        output += '<ram:GrossPriceProductTradePrice>';
        output += '<ram:ChargeAmount>'+fee+'</ram:ChargeAmount>';
        output += '</ram:GrossPriceProductTradePrice>';
        output += '<ram:NetPriceProductTradePrice>';
        output += '<ram:ChargeAmount>'+fee+'</ram:ChargeAmount>';
        output += '</ram:NetPriceProductTradePrice>';
        output += '</ram:SpecifiedLineTradeAgreement>';
        output += '<ram:SpecifiedLineTradeDelivery>';
        output += '<ram:BilledQuantity unitCode="H87">1</ram:BilledQuantity>';
        output += '</ram:SpecifiedLineTradeDelivery>';
        output += '<ram:SpecifiedLineTradeSettlement>';
        output += '<ram:ApplicableTradeTax>';
        output += '<ram:TypeCode>VAT</ram:TypeCode>';
        output += '<ram:CategoryCode>S</ram:CategoryCode>';
        output += '<ram:RateApplicablePercent>'+order['tax_rate']+'</ram:RateApplicablePercent>';
        output += '</ram:ApplicableTradeTax>';
        output += '<ram:SpecifiedTradeSettlementLineMonetarySummation>';
        output += '<ram:LineTotalAmount>'+fee+'</ram:LineTotalAmount>';
        output += '</ram:SpecifiedTradeSettlementLineMonetarySummation>';
        output += '</ram:SpecifiedLineTradeSettlement>';
        output += '</ram:IncludedSupplyChainTradeLineItem>';
	}


	var seller_tel = company['tel'];
	if(seller_tel == "") seller_tel = company['mobile'];
    output += '<ram:ApplicableHeaderTradeAgreement>';
    output += '<ram:BuyerReference>'+order['invoice_no']+'</ram:BuyerReference>';
    output += '<ram:SellerTradeParty>';
    output += '<ram:ID>'+company['c_id']+'</ram:ID>';
    output += '<ram:GlobalID schemeID="0088">'+company['c_id']+'</ram:GlobalID>';
    output += '<ram:Name>'+company['c_name']+'</ram:Name>';
    output += '<ram:DefinedTradeContact>';
    output += '<ram:PersonName>'+company['geschaeftsfuehrer']+'</ram:PersonName>';
    output += '<ram:DepartmentName>Buchhaltung</ram:DepartmentName>';
    output += '<ram:TelephoneUniversalCommunication>';
    output += '<ram:CompleteNumber>'+seller_tel+'</ram:CompleteNumber>';
    output += '</ram:TelephoneUniversalCommunication>';
    output += '<ram:EmailURIUniversalCommunication>';
    output += '<ram:URIID>'+company['email']+'</ram:URIID>';
    output += '</ram:EmailURIUniversalCommunication>';
    output += '</ram:DefinedTradeContact>';
    output += '<ram:PostalTradeAddress>';
    output += '<ram:PostcodeCode>'+company['post']+'</ram:PostcodeCode>';
    output += '<ram:LineOne>'+company['address']+'</ram:LineOne>';
    output += '<ram:CityName>'+company['city']+'</ram:CityName>';
    output += '<ram:CountryID>DE</ram:CountryID>';
    output += '</ram:PostalTradeAddress>';
    output += '<ram:SpecifiedTaxRegistration>';
    output += '<ram:ID schemeID="FC">'+company["tax_no"]+'</ram:ID>';
    output += '</ram:SpecifiedTaxRegistration>';
    output += '<ram:SpecifiedTaxRegistration>';
    output += '<ram:ID schemeID="VA">'+company["uid_no"]+'</ram:ID>';
    output += '</ram:SpecifiedTaxRegistration>';
    output += '</ram:SellerTradeParty>';
    output += '<ram:BuyerTradeParty>';
    output += '<ram:ID>'+myCustomer["k_id"]+'</ram:ID>';
    output += '<ram:Name>'+myCustomer["k_name"]+'</ram:Name>';
    output += '<ram:PostalTradeAddress>';
    output += '<ram:PostcodeCode>'+myCustomer["post"]+'</ram:PostcodeCode>';
    output += '<ram:LineOne>'+myCustomer["address"]+'</ram:LineOne>';
    output += '<ram:CityName>'+myCustomer["city"]+'</ram:CityName>';
    output += '<ram:CountryID>DE</ram:CountryID>';
    output += '</ram:PostalTradeAddress>';
    output += '</ram:BuyerTradeParty>';
    output += '</ram:ApplicableHeaderTradeAgreement>';
    output += '<ram:ApplicableHeaderTradeDelivery>';
    output += '<ram:ActualDeliverySupplyChainEvent>';
    output += '<ram:OccurrenceDateTime>';
    output += '<udt:DateTimeString format="102">'+liefer_date_tmp+'</udt:DateTimeString>';
    output += '</ram:OccurrenceDateTime>';
    output += '</ram:ActualDeliverySupplyChainEvent>';
    output += '</ram:ApplicableHeaderTradeDelivery>';
    output += '<ram:ApplicableHeaderTradeSettlement>';
    output += '<ram:InvoiceCurrencyCode>EUR</ram:InvoiceCurrencyCode>';
    output += '<ram:SpecifiedTradeSettlementPaymentMeans>';
	var typcode = 58;
	if(parseFloat(order['pay_cash']) > 0) typcode = 10;
	else if(parseFloat(order['pay_check']) > 0) typcode = 20;
	else if(parseFloat(order['pay_bank']) > 0) typcode = 30;
	else if(parseFloat(order['pay_card']) > 0) typcode = 48;
    output += '<ram:TypeCode>'+typcode+'</ram:TypeCode>';
    output += '<ram:PayeePartyCreditorFinancialAccount>';
    output += '<ram:IBANID>'+company["iban"]+'</ram:IBANID>';
    output += '</ram:PayeePartyCreditorFinancialAccount>';
    output += '</ram:SpecifiedTradeSettlementPaymentMeans>';
    output += '<ram:ApplicableTradeTax>';
	var tax = (parseFloat(order['total_sum'])*parseFloat(order['tax_rate'])/100+0.0000001).toFixed(2);	
    output += '<ram:CalculatedAmount>'+tax+'</ram:CalculatedAmount>';
    output += '<ram:TypeCode>VAT</ram:TypeCode>';
    output += '<ram:BasisAmount>'+order['total_sum']+'</ram:BasisAmount>';
    output += '<ram:CategoryCode>S</ram:CategoryCode>';
    output += '<ram:RateApplicablePercent>'+order['tax_rate']+'</ram:RateApplicablePercent>';
	output += '</ram:ApplicableTradeTax>';
    output += '<ram:SpecifiedTradePaymentTerms>';
    output += '<ram:Description>* Die Waren bleiben bis zur vollständigen Bezahlung unser Eigentum. Reklamation nur innerhalb von 7 Tagen. ';
	output += '* Bitte kontrollieren Sie die berechnete Menge sofort. Spätere Mengenreklamationen können nicht anerkannt werden. Reduzierte Ware ist vom Umtausch und Skonto ausgeschlossen. ';
	output += 'Im Falle der Rechnungsbegleichung per Überweisung bitten wir Sie, den fälligen Betrag innerhalb von 14 Tagen auf unser Konto bei der Sparkasse Neuss mit der IBAN '+company["iban"]+' (BIC '+company["bic"]+') zu überweisen. Wir bitten Sie, auf Ihrer Überweisung die Rechnungsnummer anzugeben.';
	output += '</ram:Description>';
    output += '</ram:SpecifiedTradePaymentTerms>';
    output += '<ram:SpecifiedTradeSettlementHeaderMonetarySummation>';

	
	
	// Total

	output += '<ram:LineTotalAmount>'+order['total_sum']+'</ram:LineTotalAmount>';
	output += '<ram:ChargeTotalAmount>0</ram:ChargeTotalAmount>';
	output += '<ram:AllowanceTotalAmount>0</ram:AllowanceTotalAmount>';
	output += '<ram:TaxBasisTotalAmount>'+order['total_sum']+'</ram:TaxBasisTotalAmount>';
	output += '<ram:TaxTotalAmount currencyID="EUR">'+tax+'</ram:TaxTotalAmount>';
	output += '<ram:GrandTotalAmount>'+order['net']+'</ram:GrandTotalAmount>';
	output += '<ram:TotalPrepaidAmount>0.00</ram:TotalPrepaidAmount>';
	output += '<ram:DuePayableAmount>'+order['net']+'</ram:DuePayableAmount>';
    output += '</ram:SpecifiedTradeSettlementHeaderMonetarySummation>';
    output += '</ram:ApplicableHeaderTradeSettlement>';
    output += '</rsm:SupplyChainTradeTransaction>';
	output += '</rsm:CrossIndustryInvoice>';

	//var parser = new DOMParser();
	//var xmlDoc = parser.parseFromString(output,"text/xml");
    var a = document.createElement("a");
    var file = new Blob([output], {type: 'text/xml'});
    var url = URL.createObjectURL(file);
    a.href = url;
    a.download = "erechnung-"+myCustomer["k_code"]+"-"+order['invoice_no']+".xml";
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    a.remove();
}
	
/* PRINT */
function printInvoice() {
	var i = 0;
	var src = "files/"+"<?php echo $_SESSION['uDb']; ?>"+"/logo.png?<?= rand() ?>";
	var header = '<html><head><style type="text/css" media="print">@page { size:A4; margin:0.8cm 0.8cm 0.8cm 1.5cm; }\</style></head><body>';
	var footer = '</body></html>';	
	var printout = header;
	var output = "";
	// Company
	output += '<table width="100%" cellpadding="5" cellspacing="0"><tr>';
	output += '<td align="center">';
	output += '<img height="100" style="object-fit: cover" src="'+src+'"></img>';
	output += '</td>';
	output += '<td align="right">';
	output += '<b style="font-size:12px">'+company["c_name"]+'</b><br>';
	output += '<a style="font-size:12px">'+company["address"]+'&nbsp;'+company["post"]+'&nbsp;'+company["city"]+'</a><br>';
	output += '<a style="font-size:12px">Steuer Nr.:'+company["tax_no"]+'&nbsp;UID Nr.:'+company["uid_no"]+'</a><br>';
	output += '<a style="font-size:12px">Tel:'+company["tel"]+'&nbsp;Mobile:'+company["mobile"]+'</a><br>';
	output += '<a style="font-size:12px">WhatsApp:'+company["whatsapp"]+'&nbsp;E-Mail:'+company["email"]+'</a><br>';
	if (company['website'] != null && company['website'] != "")
		output += '<a style="font-size:12px">Website:'+company["website"]+'</a><br>';
	if (company['geschaeftsfuehrer'] != null && company['geschaeftsfuehrer'] != "")
		output += '<a style="font-size:12px">Geschäftsführer:'+company["geschaeftsfuehrer"]+'</a><br>';
	if (company['hrb'] != null && company['hrb'] != "")
		output += '<a style="font-size:12px">Handelsregisternummer:'+company["hrb"]+'</a><br>';
	output += '</td>';
	output += '</tr></table>';
// second row
	output += '<table width="100%" border="0" cellpadding="2" cellspacing="0"><tr>';
	//  coloumn - customer
	output += '<td width="50%"><table width="100%" style="border:1px solid #808080;" cellpadding="2" cellspacing="0">';
	output += '<tr><td style="border-bottom:1px solid #808080;"><b style="font-size:12px;">&nbsp;Empfänger</b></td></tr>';
	output += '<tr><td style="font-size:12px;">';
	if (myCustomer["name1"] != null && myCustomer["name1"] != "")
		output += '&nbsp;&nbsp;'+myCustomer["name1"]+'<br>';
	output += '&nbsp;&nbsp;'+myCustomer["k_name"]+'<br>';
	if (myCustomer["address"] != null && myCustomer["address"] != "")
		output += '&nbsp;&nbsp;'+myCustomer["address"]+'<br>';
	if (myCustomer["post"] != null && myCustomer["post"] != "")
		output += '&nbsp;&nbsp;'+myCustomer["post"];
	if (myCustomer["city"] != null && myCustomer["city"] != "")
		output += '&nbsp;'+myCustomer["city"]+'<br>';
	else
		output += '<br>';
	if (myCustomer["country"] != null && myCustomer["country"] != "")
		output += '&nbsp;&nbsp;'+myCustomer["country"];
	if (myCustomer["ustno"] != null && myCustomer["ustno"] != "") {
		if (isCHECust(myCustomer['ustno']))
			output += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;VAT#:'+myCustomer["ustno"];
		else
			output += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ust-IdNr.:'+myCustomer["ustno"];
	}
	output += '</td></tr></table></td>';
	// column - bank
	output += '<td><table width="100%" border="0" cellpadding="2" cellspacing="0">';
	output += '<tr style="font-size:12px" align="right">';
	output += '<td></td><td>&nbsp;&nbsp;&nbsp;&nbsp;BANKVERBINDUNG:<br>&nbsp;&nbsp;&nbsp;&nbsp;IBAN:&nbsp;'+company["iban"]+'<br>&nbsp;&nbsp;&nbsp;&nbsp;BIC:&nbsp;'+company["bic"]+'</td>';
	output += '</tr>';
	output += '</table></td>';
	// column - QR Code
	if (aOptions['printQRCode']) {
		output += '<td><div id="qrcode" style="width:80px; height:80px; margin-top:0px; margin-bottom:5px;"></div></td>';
	}
// end of second row	
	output += '</tr></table>';
	
	// Title
	output += '<table width="100%" style="border:1px solid #808080;" cellpadding="2" cellspacing="0">';
	output += '<tr style="font-size:14px">';
	output += '<td><h3>Rechnung</h3></td>';
	output += '<td style="border-left:1px solid #808080;">Nr.:<br>&nbsp;&nbsp;&nbsp;&nbsp;'+order['invoice_no']+'</td>';
	output += '<td style="border-left:1px solid #808080;">Datum:<br>&nbsp;&nbsp;&nbsp;&nbsp;'+convertDate(order['date'])+'</td>';
	output += '<td style="border-left:1px solid #808080;">Kunden Nr.:<br>&nbsp;&nbsp;&nbsp;&nbsp;'+myCustomer["k_code"]+'</td>';
	output += '<td style="border-left:1px solid #808080;">Lieferdatum:<br>&nbsp;&nbsp;&nbsp;&nbsp;'+convertDate(order['lieferdatum'])+'</td>';
	output += '<td style="border-left:1px solid #808080;">Währung:<br>&nbsp;&nbsp;&nbsp;&nbsp;EUR</td>';
	output += '<td style="border-left:1px solid #808080;">Seite:<br>&nbsp;&nbsp;&nbsp;&nbsp;1/1</td>';
	output += '</tr></table>';
	// Table
	output += '<table width="100%" style="border:1px solid #808080;" cellpadding="2" cellspacing="0"><thead>';
	output += '<tr style="font-size:12px">';
	output += '<th style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="center" >Artikel Nr. und Bezeichnung</th>';
	output += '<th style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="center">Anzahl</th>';
	output += '<th style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="right">Einzelpreis</th>';
	output += '<th style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="right">Nettobetrag</th>';
	output += '<th style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="right">MwSt.</th>';
	output += '</tr></thead><tbody>';
	for (i=0; i<itemCount; i++) {
		var priceStr = orderItems[i]['price'];
		var discount = orderItems[i]['discount'];
		var rabatt = "";
		if(discount > 0){
			rabatt = " (Rabatt: "+parseFloat(orderItems[i]['discount']).toFixed(0)+"%)";
			priceStr = "<a style='text-decoration-line: line-through;'>"+orderItems[i]['price']+"</a> "+(((100-discount) * orderItems[i]['price']) /100).toFixed(2);;
		}
		var code = "";
		if (orderItems[i]['i_id'] != "0") {
			if (orderItems[i]['i_name'] != null && orderItems[i]['i_name'] != "" && !opPrintNoName)
				code += orderItems[i]['i_name']+'&nbsp;';
			if (aOptions['printNoART'])
				code += orderItems[i]['i_code'];
			else
				code += orderItems[i]['i_code'];
		} else {
			if (opPrintNoName) {
				if (aOptions['printNoART'])
					code += orderItems[i]['ai_code'];
				else
					code += orderItems[i]['ai_code'];
			} else {
				if (aOptions['printNoART'])
					code += orderItems[i]['a_name']+'&nbsp;'+orderItems[i]['ai_code'];
				else
					code += orderItems[i]['a_name']+'&nbsp;'+orderItems[i]['ai_code'];
			}
		}
		if ((isCHECust(myCustomer['ustno']) || opPrintNonEU) && orderItems[i]['note'] != null)
			code += '&nbsp;'+orderItems[i]['note'];
		var countStr = orderItems[i]['count'];
		if (orderItems[i]['unit'] != "1")
			countStr = orderItems[i]['count']+" (x"+orderItems[i]['unit']+")";
		output += '<tr style="font-size:12px; font-family:Arial">';
		output += '<td style="padding:1px; border-left:1px solid #808080;">'+'&nbsp;&nbsp;'+code+rabatt+'</td>';
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+countStr+'&nbsp;</td>';		
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+priceStr+'</td>';
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+orderItems[i]['subtotal']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+order['tax_rate']+'</td>';
		output += '</tr>';
	}
	output += '<tr><td align="center" style="font-size:12px; font-family:Arial; border-top:1px solid #808080;" colspan="5">===Gesamtmenge:&nbsp;'+order['count_sum']+'&nbsp;Stück===</td></tr>';
	if (notGermanCust(myCustomer['ustno']) && !isCHECust(myCustomer['ustno']) && !opPrintNonEU)
		output += '<tr><td align="center" style="font-size:12px; font-family:Arial" colspan="5">Die i.g. Lieferung erfolgt gem. &sect;6a UStG bzw. nach Artikel 22 Ab s. 3 der 6.EG-Richtlinie steuerfrei. Muster einer Gelangensbestätigung im Sinne des &sect;17a Abs.2 Nr.2 UstDV</td></tr>';
	if (isCHECust(myCustomer['ustno']) || opPrintNonEU)
		output += '<tr><td align="center" style="font-size:12px; font-family:Arial" colspan="5">Der Ausführer der Waren, auf die sich dieses Handelspapier bezieht, erklärt, dass diese Waren, so weit nich anders angegeben, präferenzbegünstigte EU-Ursprungswaren sind.<br>Neuss, '+convertDate(order['date'])+'</td></tr>';
	// Spacing
	var maxCount = 34;
	if (order['price_sum'] == order['total_sum'] && order['discount_rate'] == '0.00')
		maxCount = 39;
	if (notGermanCust(myCustomer['ustno'])) {
		if (isCHECust(myCustomer['ustno']))
			maxCount = maxCount - 5;
		else
			maxCount = maxCount - 2;
	}
	for (i=0; i<maxCount-itemCount; i++) {
		output += '<tr><td style="padding:1px; font-size:12px; font-family:Arial" colspan="5">&nbsp;</td></tr>';
	}
	output += '</tbody></table>';
	// Summary
	output += '<table width="100%" border="0" cellpadding="5" cellspacing="0">';
	// Left
	output += '<tr>';
	output += '<td width="50%" valign="top"><table width="100%" style="border:1px solid #808080;" cellpadding="5" cellspacing="0">';
	// Tax
	var tax = (parseFloat(order['total_sum'])*parseFloat(order['tax_rate'])/100+0.0000001).toFixed(2);	
	output += '<tr align="right" style="font-size:12px">';
	output += '<td style="padding:1px;">MwSt Code</td>';
	output += '<td style="padding:1px;">Satz</td>';
	output += '<td style="padding:1px;">Nettobetrag</td>';
	output += '<td style="padding:1px;">MwSt&nbsp;&nbsp;</td>';
	output += '</tr>';
	output += '<tr align="right" style="font-size:12px">';
	output += '<td style="padding:1px;">'+order['tax_rate']+'</td>';
	output += '<td style="padding:1px;">'+order['tax_rate']+'%</td>';
	output += '<td style="padding:1px;">'+order['total_sum']+'</td>';
	output += '<td style="padding:1px;">'+tax+'&nbsp;&nbsp;</td>';
	output += '</tr>';
	// Payment
	var pays_num = 0;
	output += '<tr style="font-size:12px;">';
	output += '<td colspan="4" style="border-top:1px solid #808080;">';
	output += 'Zahlungsart:&nbsp;';
	if (notZero(order['pay_cash'])) {
		output += 'Bar:&nbsp;'+order['pay_cash']+';&nbsp;'; pays_num++;
	}
	if (notZero(order['pay_card'])) {
		output += 'Karte:&nbsp;'+order['pay_card']+';&nbsp;'; pays_num++;
	}
	if (notZero(order['pay_bank'])) {
		if (pays_num == 2) output += '<br>';
		output += 'Überweisung:&nbsp;'+order['pay_bank']+';&nbsp;'; pays_num++;
	}
	if (notZero(order['pay_check'])) {
		if (pays_num == 2) output += '<br>';
		output += 'Scheck:&nbsp;'+order['pay_check']+';&nbsp;'; pays_num++;
	}
	if (notZero(order['pay_other'])) {
		if (pays_num == 2) output += '<br>';
		output += 'Nachnahme:&nbsp;'+order['pay_other']; pays_num++;
	}
	if (notZero(order['pay_paypal'])) {
		if (pays_num == 2) output += '<br>';
		output += 'PayPal:&nbsp;'+order['pay_paypal']+';&nbsp;'; pays_num++;
	}	
	if (notZero(order['pay_vorkasse'])) {
		if (pays_num == 2) output += '<br>';
		output += 'Vorkasse:&nbsp;'+order['pay_vorkasse']+';&nbsp;'; pays_num++;
	}	
	output += '<br><br></td>';
	output += '</tr>';
	if (order['note'] != null && order['note'] != "") {
		output += '<tr style="font-size:12px;"><td colspan="4" >Memo:&nbsp;'+order['note']+"</td></tr>";
	}
	output += '</table></td>';
	// Right
	output += '<td width="50%" valign="top"><table width="100%" style="min-height: 74px; border:1px solid #808080;" cellpadding="5" cellspacing="0">';	
	// Discount
	if (notZero(order['discount_rate'])) {
		var discount = (parseFloat(order['price_sum'])*parseFloat(order['discount_rate'])/100).toFixed(2);
		var nettosumme = parseFloat(order['price_sum']) - parseFloat(discount);
		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px;" align="right">Summe:</td><td style="padding:1px;" align="right">'+order['price_sum']+'&nbsp;&nbsp;</td>';
		output += '</tr>';
		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px;" align="right">Skont:&nbsp;'+order['discount_rate']+'%:</td><td style="padding:1px;" align="right">'+discount+'&nbsp;&nbsp;</td>';
		output += '</tr>';
		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px; border-bottom:1px solid #808080; border-top:1px solid #808080;" align="right">Nettosumme:</td><td align="right" style="padding:1px; border-bottom:1px solid #808080; border-top:1px solid #808080;">'+nettosumme+'&nbsp;&nbsp;</td>';
		output += '</tr>';
	}
	// Fees
	if (notZero(order['fee1'])) {
		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px;" align="right">Versandkosten:&nbsp;</td><td style="padding:1px;" align="right">'+order['fee1']+'&nbsp;&nbsp;</td>';
		output += '</tr>';
	}
	if (notZero(order['fee2'])) {
		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px;" align="right">Nachnahmekosten:&nbsp;</td><td style="padding:1px;" align="right">'+order['fee2']+'&nbsp;&nbsp;</td>';
	}
	if (notZero(order['fee3'])) {
		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px;" align="right">Inkassokosten:&nbsp;</td><td style="padding:1px;" align="right">'+order['fee3']+'&nbsp;&nbsp;</td>';
		output += '</tr>';
	}
	if (notZero(order['fee4'])) {
		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px;" align="right">Verpackungskosten:&nbsp;</td><td style="padding:1px;" align="right">'+order['fee4']+'&nbsp;&nbsp;</td>';
		output += '</tr>';
	}
	if (notZero(order['fee5'])) {
		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px;" align="right">Nebenkosten:&nbsp;</td><td style="padding:1px;" align="right">'+order['fee5']+'&nbsp;&nbsp;</td>';
		output += '</tr>';
	}
	// Total
	output += '<tr style="font-size:14px;">';
	output += '<td style="padding:1px; border-top:1px solid #808080;" align="right"><b>Steuergrundlage:</b></td><td style="padding:1px; border-top:1px solid #808080;" align="right">'+order['total_sum']+'&nbsp;&nbsp;</td>';
	output += '</tr>';
	output += '<tr style="font-size:14px;">';
	output += '<td style="padding:1px;" align="right"><b>Total MwSt.:</b></td><td style="padding:1px;" align="right">'+tax+'&nbsp;&nbsp;</td>';
	output += '<tr style="font-size:14px;">';
	output += '<td style="padding:1px;" align="right"><b>Total (inkl. MwSt):</b></td><td style="padding:1px;" align="right"><b style="font-size: 1.5rem;">'+order['net']+'&nbsp;&nbsp;</b></td>';
	output += '</tr>';
	
	output += '</table></td>';
	
	output += '</tr>';
	output += '</table>';
	// reklamation
	if (aOptions['printReklamation']) {
		output += '<a style="font-size:9px;">* Die Waren bleiben bis zur vollständigen Bezahlung unser Eigentum. Reklamation nur innerhalb von 7 Tagen.</a>'
	}
	// reklamation1
	if (aOptions['printReklamation1'] && (order['pay_bank'] > 0 || order['pay_vorkasse'] > 0)) {
		output += '<a style="font-size:9px;">'+
					'* Bitte kontrollieren Sie die berechnete Menge sofort. Spätere Mengenreklamationen können nicht anerkannt werden. Reduzierte Ware ist vom Umtausch und Skonto ausgeschlossen.'+ 
					' Im Falle der Rechnungsbegleichung per Überweisung bitten wir Sie, den fälligen Betrag innerhalb von 14 Tagen auf unser Konto bei der Sparkasse Neuss mit der IBAN '+company["iban"]+' (BIC '+company["bic"]+') zu überweisen. Wir bitten Sie, auf Ihrer Überweisung die Rechnungsnummer anzugeben.'+
					'</a>';
	}
	
	printout += header + output;
	if (!opPrintOnePage) {
		printout += '<p style="page-break-before: always"></p>';
		printout += output;
	}
	
// Print Gelangensbestaetigung
	if (notGermanCust(myCustomer['ustno']) && !isCHECust(myCustomer['ustno'])) {
		output = "";
		output += '<p style="page-break-before: always">';
		output += '<table width="100%" cellpadding="2" cellspacing="0"><tr><td align="right">';
		output += 'Rechnungsnummmer:&nbsp;'+order['invoice_no']+'<br>';
		if (company['email'] != null && company['email'] != "")
			output += company['email']+'<br>';
		else
			output += company['tel']+'<br>';
		output += '</td></tr></table>';
		output += '<h3 style="text-align: center;">Gelangensbestätigung</h3><br>';
		output += '<p>Bestätigung über das Gelangen des Gegenstands einer innergemeinschatflichen Lieferung in einen anderen EU-Mitgliedstaat</p><br>';
		if (myCustomer["name1"] != null && myCustomer["name1"] != "")
			output += myCustomer["name1"]+',&nbsp;';
		output += myCustomer["k_name"]+',&nbsp;';
		if (myCustomer["address"] != null && myCustomer["address"] != "")
			output += myCustomer["address"]+',&nbsp;';
		if (myCustomer["post"] != null && myCustomer["post"] != "")
			output += myCustomer["post"]+',&nbsp;';
		if (myCustomer["city"] != null && myCustomer["city"] != "")
			output += myCustomer["city"]+',&nbsp;';
		if (myCustomer["country"] != null && myCustomer["country"] != "")
			output += myCustomer["country"];
		output += '<hr>';
		output += '<a style="font-size:12px;">(Name und Anschrift des Abnehmers der innergemeinschaftlichen Lieferung. ggf. E-Mail-Adresse)</a><br>';
		output += '<p>Hiermit bestätige ich als Abnehmer, dass ich folgenden Gegenstand / dass folgender Gegenstand einer innergemeinschaftlichen Lieferung</p><br>';
		output += order['count_sum']+'<br>';
		output += '<hr>';
		output += '<a style="font-size:12px;">(Menge des Gegenstands der Lieferung)</a><br>';
		output += '<br><br><br><hr>';
		output += '<a style="font-size:12px;">(handelsüberliche Bezeichnung. beiFahtzeugen zusätzlich die Fahrzeug-Identifikationsnummer)</a><br><br>';
		output += 'Im<br>';
		output += '<hr>';
		output += '<a style="font-size:12px;">(Monat und Jahr des Erhalts des Liefergegenstands im Mitgliedstaat, in den der Liefergegenstand gelang ist, wenn der Liefernde Unternehmer den Liefergegenstand befördert order versendet hat oder wenn der Abnehmer den Liefegegenstand versendet hat)</a><br><br>';
		output += 'Im&nbsp;'+convertDate(order['date'])+'<br>';
		output += '<hr>';
		output += '<a style="font-size:12px;">(Monat und Jahr des Endes der beförderung, wenn der Abnehmer den Liefegegenstand selbst befördert hat)</a><br><br>';
		output += 'in / nach&nbsp;';
		if (myCustomer["post"] != null && myCustomer["post"] != "")
			output += myCustomer["post"]+',&nbsp;';
		if (myCustomer["city"] != null && myCustomer["city"] != "")
			output += myCustomer["city"]+',&nbsp;';
		if (myCustomer["country"] != null && myCustomer["country"] != "")
			output += myCustomer["country"];
		output += '<hr>';
		output += '<a style="font-size:12px;">(Mitgliedstaat und Ort, wohin der Liefergegenstands im Rahmen einer Beförderung order Versendung gelangt ist)</a><br><br><br>';
		output += 'erhalten habe / gelangt ist.';
		output += '<br><br><br><br>';
		output += '<a style="font-size:12px;">(Unterschrift des Abnehmers oder seines Vertretungsberechtigen sowie Name des Unterzeichnenden in Druckschrift)</a><br><br>';
		printout += output;
	}
	
	printout += footer;
	  
	var mywindow = window.frames["printf"];
    mywindow.document.write(printout);
	mywindow.document.close();
	if (aOptions['printQRCode']) {
		var qrcode = new QRCode(mywindow.document.getElementById("qrcode"), {
			text: getQRCodeText(),
			width : 80,
			height : 80
		});
	} 
	mywindow.focus();
	if (/Android/i.test(navigator.userAgent)) {
		openPrintDialogue(printout);
	} else if (/iPhone|iPad/i.test(navigator.userAgent)) {
		mywindow.print();
		mywindow.onafterprint = function () {
			mywindow.close();
			closeInvoice();
		} 
	}else {
		mywindow.onload = function () {
			mywindow.print();
			mywindow.close();
			closeInvoice();
		}
	}
}

function openPrintDialogue(output){
	var iframe = document.createElement('iframe');
	iframe.id = 'print_order';
	iframe.name = 'print_order';
	iframe.style.visibility = "hidden";
	iframe.srcdoc = output;
	document.body.appendChild(iframe);
	window.frames['print_order'].onload = function () {
		window.frames['print_order'].focus();
		window.frames['print_order'].print();
	}		
};

function getQRCodeText() {
	var text = "BCD\n001\n1\nSCT\n" + 
		company['bic'] + "\n" + company['c_name'] + "\n" + company['iban'] + "\n" + "EUR" + order['net'] + "\n\n" + order['invoice_no'] + "\n";
	return text;
}
/************************************************************************
	FUNCTIONS
************************************************************************/
function findArray(array, key, value) {
	for (var i=0; i<array.length; i++) {
		if (array[i][key] == value) {
			return array[i];
			break;
		}
	}
	return null;
}
</script>

</body>
</html>
