<?php
/********************************************************************************
	File:		order.php
	Purpose:	Order
	
	2021-01-20:	removed auto custs
	2021-02-15: added units
	2021-02-16: added thisItem; removed hidden values from modalOrderItem	
	2021-02-19:	added unit to order_items
	2021-03-14: recalculate count_sum and price_sum when viewing order
	2021-03-17: barcode support
*********************************************************************************/ 
// Start session; If session expired, load the login page.
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

// Include files
include_once 'resource.php';
include_once 'db_functions.php';
include_once 'db_invoice.php';

// Init variables
$thisResource = new myResource($_SESSION['uLanguage']);	
$myId = '';
$_SESSION['orderType'] = 0;
$backPhp = 'ord_mgt.php';
$myVariants = dbQueryVariants();

// Start a new order
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(isset($_GET['back']))
	{
		$backPhp = $_GET['back'].'.php';
	}
	if (isset($_GET['o_id']))
	{
		$myId = $_GET['o_id'];
		$_SESSION['orderType'] = 1;
	}
	else
	{
		$myId = dbGetOrderId();
		if($myId <= 0)
			header('Location: '.$backPhp);
		if (dbCreateOrder($myId) <=0)
			header('Location: '.$backPhp);
		$_SESSION['orderType'] = 0;
	}
}

?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">	
	<title>EUCWS - Order</title>
<style>
body {
 padding-top: 0.2rem;
}
iframe {
	margin: 0px; 
	padding: 0px; 
	display: block;
}
#modalVariantSelect {
	z-index: 10000;
}
#modalCust {
	z-index: 10000;
}
.loader {
  position: fixed;
  left: 30%;
  border: 16px solid #f3f3f3;
  border-radius: 50%;
  border-top: 16px solid #3498db;
  width: 80px;
  height: 80px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
</head>

<body>	
	<?php include "include/modalCust.php" ?>
	<?php include "include/modalCustSearch.php" ?>
	<?php include 'include/modalVariantSelect.php' ?>
	
	<form action="" method="post">
	<div class="container">	
	
<!-- order data header -->			
	<div class="row"> 
		<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-1" align="left">
			<button type="button" id="cancel" name="cancel" class="btn btn-secondary" onclick="cancelOrder()"><span class='fa fa-arrow-left'></span></button>
		</div>
		<div class="input-group p-1 col-8 col-sm-8 col-md-8 col-lg-6">
			<input type="text" class="form-control" name="o_id" id="o_id" value="<?php echo $myId ?>" hidden>	
			<button type="button" class="btn btn-secondary" id="btnCust" onclick="showCust()"><span class='fa fa-address-book-o'></span></button>
			<input type="text" class="ml-1 form-control" name="k_name" id="k_name" value="" readonly>
			<button type="button" class="ml-1 btn btn-secondary" id="btnSrchCust" onclick="searchCust()"><span class='fa fa-search'></span></button>
			
		</div>
		<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-1" align="right">		
			<button type="button" id="ok" name="ok" class="btn btn-primary" onclick="submitOrder()"><span class='fa fa-check'></span></button>
		</div>
	</div>
<!-- buttons -->
	<div class="row">
		<div class="p-1 col-3 col-sm-3 col-md-3 col-lg-2" align="left">
			<button type="button" class="btn btn-success" id="btnInvoice" onclick="createInvoice()"><?php echo $thisResource->fmOrderBtnInvoice ?></button>			
		</div>
		<div class="p-1 col-9 col-sm-9 col-md-9 col-lg-6" align="right">
			<button type="button" class="btn btn-primary" id="btnBarcode"  onclick="showBarcode()"><span class='fa fa-barcode'></span></button>
			<button type="button" class="btn btn-secondary" id="btnLiefer"  onclick="printLiefer()"><span class='fa fa-truck'></span></button>
			<button type="button" class="btn btn-secondary" id="btnDis"  onclick="showDis()"><span class='fa fa-percent'></span></button>
			<button type="button" class="btn btn-secondary" id="btnFee"  onclick="showFee()"><span class='fa fa-eur'></span></button>
			<button type="button" class="btn btn-secondary" id="btnPrint"  onclick="printOrder()"><span class='fa fa-print'></span></button>
			<button type="button" class="btn btn-primary" id="btnNew"  onclick="showNewSearch()"><span class='fa fa-plus'></span></button>			
		</div>
	</div>	
<!-- order items -->
<div class="loader" id="loader"></div>
	<div class="row"> 
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-8">
			<table id="myTable" class="table-sm" data-toggle="table"
				data-single-select="true" data-click-to-select="true" data-unique-id="id" 
				data-detail-view="true" data-detail-formatter="orderFormatter">
				<thead class="thead-light">
					<tr>
					<th class="p-1" data-field="id" data-visible="false">#</th>
					<th class="p-1" data-field="idx_image" data-width="15" data-width-unit="%" data-halign="center" data-align="center"></th>
					<th class="p-1" data-field="idx_data" data-width="45" data-width-unit="%" data-halign="center" data-align="left"><?php echo $thisResource->fmOrderCapItem ?></th>
					<th class="p-1" data-field="idx_count" data-width="10" data-width-unit="%" data-halign="center" data-align="right"><?php echo $thisResource->fmOrderCapCount ?></th>
					<th class="p-1" data-field="idx_price" data-width="10" data-width-unit="%" data-halign="center" data-align="right">&euro;</th>
					<th class="p-1" data-field="idx_subtotal" data-width="20" data-width-unit="%" data-halign="center" data-align="right">&euro;&euro;</th>					
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
<!-- summary -->
	<div class="row">
		<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-2" align="left">
			<button type="button" class="btn btn-danger" id="btnDestroy" onclick="destroyOrder()"><span class='fa fa-trash'></span></button>	
		</div>
		<div class="p-1 col-10 col-sm-10 col-md-10 col-lg-6" align = "right">
			<b><?php echo $thisResource->fmOrderTotalNum ?>&nbsp;</b><label id="sumCount" style="color:blue">0</label>
			<b>&nbsp;&nbsp;<?php echo $thisResource->fmOrderTotal ?>&nbsp;</b><label id="sumPrice" style="color:blue">0.00</label>
			<b>&nbsp;&nbsp;<?php echo $thisResource->fmOrderNet ?>&nbsp;</b><label id="sumTotal" style="color:blue">0.00</label>
		</div>
	</div>
	<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-8" align = "right">
			<button type="button" class="btn btn-outline-secondary" id="btnPay"  onclick="showPay()"><span class='fa fa-credit-card'></span></button>
			<b><?php echo $thisResource->fmOrderPaid ?>&nbsp;</b><label id="sumPaid" style="color:blue">0.00</label>
			<b>&nbsp;&nbsp;<?php echo $thisResource->fmOrderUnpaid ?>&nbsp;</b><label id="sumDue" style="color:red">0.00</label>
		</div>
	</div>
	
<!-- Modal: New item search-->
<div class="modal fade" id="modalOrderNewSearch" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdOrderNewSearchTitle"><?php echo $thisResource->fmOrderSrchTitle ?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12">
					<div class="input-group p-1">
						<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->fmOrderSrchArt ?></span></div>
						<input type="text" class="form-control" name="ms_i_code" id="ms_i_code">
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
		</div>
		</div>
	</div>
</div> <!-- End of Modal: New item search -->	

<!-- Modal: New order item -->
<div class="modal fade" id="modalOrderItem" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
	data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdOrderItemTitle"><?php echo $thisResource->fmOrderItemTitle ?></b>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		</div>
		<div class="modal-body">
			<input type="text" class="form-control" name="m_type" id="m_type" hidden>			
			<div class="row">
				<div class="col-2 p-1" align="center">
					<img id="m_img" width="60" height="80" style="object-fit: cover" onclick="mdShowImageView(this)">
				</div>
				<div class="col-10 p-1">
					<div class="input-group">
						<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->fmOrderItemArt ?></span></div>
						<input type="text" class="form-control" name="m_i_code" id="m_i_code" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->fmOrderItemName ?></span></div>
						<input type="text" class="form-control" name="m_i_name" id="m_i_name" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->fmOrderItemUnit ?></span></div>
						<input type="text" class="form-control" name="m_unit_str" id="m_unit_str" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->fmOrderItemStock ?></span></div>
						<input type="text" class="form-control" name="m_old_count" id="m_old_count" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->fmOrderItemCount ?></span></div>
						<button type="button" class="btn btn-secondary ml-1 mr-1" id="btnCountMinus" onclick="countMinus()"
							style="touch-action: none"><span class='fa fa-minus'></span></button>
						<input type="number" min="0" step="1" class="form-control" name="m_count" id="m_count">
						<button type="button" class="btn btn-secondary ml-1" id="btnCountAdd" onclick="countAdd()" 
							style="touch-action: none"><span class='fa fa-plus'></span></button>
						<button type="button" class="ml-1 btn btn-success" id="btnVariant" onclick="showVariantWindow()"><?php echo $thisResource->fmOrderItemVar ?></button>
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->fmOrderItemPrice ?></span></div>
						<input type="number" min="0" step="0.01" class="form-control" name="m_price" id="m_price">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-2 p-1" align="left">
					<button type="button" class="ml-1 btn btn-danger" id="btnDel" onclick="delItem()"><span class='fa fa-trash'></span></button>
				</div>
				<div class="col-10 p-1" align="right">
					<button type="button" class="mr-1 btn btn-primary" id="btnDone" onclick="doneItem()"><span class='fa fa-check'></span></button>
				</div>
			</div>
			<div id="m_imageView" class="w3-modal" onclick="this.style.display='none'">
				<span class="w3-button w3-hover-red w3-xlarge w3-display-topright">&times;</span>
					<div class="w3-modal-content w3-animate-zoom">
					<img id="m_imageZoom" src="" style="width:100%">
				</div>
			</div>
		</div> <!-- body -->
		</div> <!-- content -->
	</div> <!-- dialog -->
</div> <!-- End of Modal: New order item-->

<!-- Modal: Discount -->
<div class="modal fade" id="modalOrderDis" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdOrderDisTitle"><?php echo $thisResource->fmOrderDisTitle ?></b>
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text"  style="width:120px;"><?php echo $thisResource->fmOrderDisRate ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mdi_discount_rate" id="mdi_discount_rate">
			</div>	
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text"  style="width:120px;"><?php echo $thisResource->fmOrderDisValue ?></span></div>
				<input type="text" class="form-control" name="mdi_discount" id="mdi_discount" readonly>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></span></button>
			<button type="button" class="btn btn-primary" id="btnDisOk" onclick="doneDis()"><span class='fa fa-check'></span></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Discount -->

<!-- Modal: Fees -->
<div class="modal fade" id="modalOrderFee" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdOrderFeeTitle"><?php echo $thisResource->fmOrderFeeTitle ?></b>
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->fmOrderFeeShipping ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee1" id="mf_fee1">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->fmOrderFeeNach ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee2" id="mf_fee2">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->fmOrderFeeBank ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee3" id="mf_fee3">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->fmOrderFeeOther ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee4" id="mf_fee4">
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></span></button>
			<button type="button" class="btn btn-primary" id="btnFeeOk" onclick="doneFee()"><span class='fa fa-check'></span></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Fees -->

<!-- Modal: Payment -->
<div class="modal fade" id="modalOrderPay" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdOrderPayTitle"><?php echo $thisResource->fmOrderPayTitle ?></b>			
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->fmOrderPayAmount ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mp_amount" id="mp_amount">
				<div class="dropdown ml-2">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"><?php echo $thisResource->fmOrderPayMethod ?></button>
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
				<table id="tablePay" class="table-sm" data-toggle="table" data-single-select="true" data-click-to-select="true" data-unique-id="id" >
					<thead class="thead-light">
						<tr>
						<th class="p-1" data-field="id" data-width="0" data-width-unit="%" data-visible="false">#</th>
						<th class="p-1" data-field="idx_type" data-width="0" data-width-unit="%" data-visible="false"></th>
						<th class="p-1" data-field="idx_value" data-width="40" data-width-unit="%"><?php echo $thisResource->fmOrderPayMethod ?></th>
						<th class="p-1" data-field="idx_amount" data-width="40" data-width-unit="%"><?php echo $thisResource->fmOrderPayValue ?></th>
						<th class="p-1" data-field="idx_del" data-width="20" data-width-unit="%"></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->fmOrderPayTotal ?></span></div>
				<input type="text" class="form-control" name="mp_total" id="mp_total" readonly>
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->fmOrderPayPaid ?></span></div>
				<input type="text" class="form-control" name="mp_pays_total" id="mp_pays_total" readonly>
			</div>			
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->fmOrderPayUnpaid ?></span></div>
				<input type="text" class="form-control" name="mp_pays_due" id="mp_pays_due" readonly>
			</div>	
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></span></button>
			<button type="button" class="btn btn-primary" id="btnPayOk" onclick="donePay()"><span class='fa fa-check'></span></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Payment -->

<!-- Modal: barcode input -->
<div class="modal fade" id="modalBarcode" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdbcTitle"><?php echo $thisResource->fmOrderBCTitle ?></b>
		</div>
		<div class="modal-body">
			<audio id="playBeepYes" src="beepYes.mp3"></audio>
			<audio id="playBeepNo" src="beepNo.mp3"></audio>
			<div class="row">
				<div class="col-12 p-1" align="center">
					<a><?php echo $thisResource->fmOrderBCHint ?></a>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="col-2 p-1" align="center">
					<img id="mdbc_img" width="60" height="80" style="object-fit: cover">
				</div>
				<div class="col-6 p-1">
					<label id="mdbc_i_code" style="font-weight: bold;"></label><br>
					<label id="mdbc_variant" style="font-weight: bold;"></label><br>
					<label id="mdbc_i_name"></label><br>							
					<label id="mdbc_old_count"></label><br>
					<label id="mdbc_price"></label>
				</div>
				<div class="col-4 p-1">
					<b id="mdbc_count" style="font-size:28px; color:green"></b><br>
					<label id="mdbc_unit"></label>
				</div>
			</div>
			<hr>
			<div class="container" id="mdbcKeyboard">
				<div class="row">
					<div class="col-1"></div>
					<div class="col-10" align="center">
						<button type="button" class="btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKey1" onclick="mdbcKey1()">1</button>
						<button type="button" class="btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKey2" onclick="mdbcKey2()">2</button>
						<button type="button" class="btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKey3" onclick="mdbcKey3()">3</button>
						<button type="button" class="btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKeyAdd" onclick="mdbcKeyAdd()"><b>+</b></button>
					</div>
					<div class="col-1"></div>
				</div>
				<div class="row">
					<div class="col-1"></div>
					<div class="col-10" align="center">
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKey4" onclick="mdbcKey4()">4</button>
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKey5" onclick="mdbcKey5()">5</button>
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKey6" onclick="mdbcKey6()">6</button>
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKeySub" onclick="mdbcKeySub()"><b></b>-</button>
					</div>
					<div class="col-1"></div>
				</div>
				<div class="row">
					<div class="col-1"></div>
					<div class="col-10" align="center">
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKey7" onclick="mdbcKey7()">7</button>
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKey8" onclick="mdbcKey8()">8</button>
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKey9" onclick="mdbcKey9()">9</button>
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKeyC" onclick="mdbcKeyC()">C</button>
					</div>
					<div class="col-1"></div>
				</div>
				<div class="row">
					<div class="col-2"></div>
					<div class="col-8" align="center">
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKeyComma" onclick="mdbcKeyComma()">,</button>
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKey0" onclick="mdbcKey0()">0</button>
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKeyPeriod" onclick="mdbcKeyPeriod()">.</button>
						<button type="button" class="mt-1 btn btn-outline-secondary" style="width:40px; touch-action: none" id="mdbcBtnKeyReserved" >&nbsp;</button>
					</div>
					<div class="col-2"></div>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="col-12 p-1" align="right">
					<button type="button" class="btn btn-secondary" id="mdbcBtnCancel" onclick="mdbcCancel()"><span class='fa fa-times'></span></button>
					<button type="button" class="mr-1 btn btn-primary" id="mdbcBtnDone" onclick="mdbcDone()"><span class='fa fa-check'></span></button>
				</div>
			</div>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: barcode input -->

<div id="modalImageView" class="w3-modal" onclick="this.style.display='none'">
    <span class="w3-button w3-hover-red w3-xlarge w3-display-topright">&times;</span>
    <div class="w3-modal-content w3-animate-zoom">
      <img id="modalImage" src="" style="width:100%">
    </div>
 </div>
  
</div> <!-- end of container -->
</form>	<!-- end of form -->

<script src="js/ajax.js"></script>
<script src="js/autocomplete.js?202109131930"></script>
<script src="js/modalCust.js?202108121658"></script>	
<script src="js/modalCustSearch.js?202109090946"></script>
<script src="js/modalVariantSelect.js?202108181509"></script>
<script>

var myRes = <?php echo json_encode($thisResource) ?>;

var oId, orderType = 0;
var myCustomer = new Object();
var $table = $("#myTable");
// Modals
var $modalOrderNewSearch = $("#modalOrderNewSearch");
var $modalOrderItem = $("#modalOrderItem");
var $modalOrderFee = $("#modalOrderFee"), $modalOrderPay = $("#modalOrderPay");
var $modalOrderDis = $("#modalOrderDis");
//var $modalImageView = $("#mdImageView");
// Data
var order = {}, orderItems = [];
var itemCount = 0, itemIdCount = 0;
var thisItem = {};
// Variant
var orderVariant = new Array(), variantCount = 0;
var a_variants = <?php echo json_encode($myVariants) ?>;
// autocpmplete
var a_icode = JSON.parse(localStorage.getItem("a_icode"));
var a_image = JSON.parse(localStorage.getItem("a_image"));
var a_ivariants =  new Array();
// Sys Options
var sysOptions = new Object();
sysOptions = JSON.parse(localStorage.getItem("sysOptions"));
/************************************************************************************
	INIT
************************************************************************************/
document.getElementById("loader").style.display = "block";
document.getElementById("myTable").style.display = "none";
getRequest("getInvVariantAll.php", loadInvVariantOK, loadInvVariantError);
function loadInvVariantOK(result) {
	document.getElementById("loader").style.display = "none";
	document.getElementById("myTable").style.display = "table";
	a_ivariants = result;
	initSys();
}
function loadInvVariantError(result) {
	alert("ERROR");
	var url = "<?php echo $backPhp; ?>";
	window.location.assign(url);
}

function initSys() {
	oId = "<?php echo $myId ?>";
	orderType = "<?php echo $_SESSION['orderType'] ?>";
	if (orderType == 1) {
		getRequest("getOrderById.php?o_id="+oId, loadOrder, loadError);	
	}
	else {
		order['o_id'] = oId;
		initOrder();
		displaySum();	
	}
}	
function initOrder() {
	order['discount_rate'] = "0.00";
	order['fee1'] = "0.00";
	order['fee2'] = "0.00";
	order['fee3'] = "0.00";
	order['fee4'] = "0.00";
	
	order['pay_cash'] = "0.00";
	order['pay_card'] = "0.00";
	order['pay_bank'] = "0.00";
	order['pay_check'] = "0.00";
	order['pay_other'] = "0.00";
	order['pay_paypal'] = "0.00";
	order['pay_vorkasse'] = "0.00";
	
	order['count_sum'] = "0";
	order['price_sum'] = "0.00";
	order['total_sum'] = "0.00";
	order['paid_sum'] = "0.00";
	order['due'] = "0.00";
	
	order['profit'] = "0.00";
	order['k_id'] = "0";
	
	myCustomer['k_id'] = "0";
}
$table.bootstrapTable({   
	formatNoMatches: function () {
         return myRes['fmOrderTableEmpty'];
    }
});
/************************************************************************************
	LOAD
************************************************************************************/
$(document).ready(function(){
	// Load data for autocomplete 
	if (sysOptions != null && sysOptions['sysSearchLike'])
		autocomplete_like(document.getElementById("ms_i_code"), a_icode, a_image);
	else
		autocomplete(document.getElementById("ms_i_code"), a_icode, a_image);
});
// Load Order after getRequest
function loadOrder(result) {
	order = result;
	
	if (order['k_id'] != "" && order['k_id'] != "0")
		getRequest("getCustById.php?k_id="+order['k_id'], loadCust, loadError);	
	else {
		myCustomer['k_id'] = "0";
		document.getElementById("k_name").value = "";
	}

	getRequest("getOrderItemsById.php?o_id="+oId, loadOrderItems, loadError);
	getRequest("getOrderVariantById.php?o_id="+oId, loadOrderVariantYes, loadOrderVariantNo);
}
// Load Customer
function loadCust(result) {
	myCustomer = result;
	document.getElementById("k_name").value = myCustomer['k_name'];
}
// Load Order Items after getRequest
function loadOrderItems(result) {
	orderItems = result;
	itemCount = orderItems.length;
	itemIdCount = itemCount;
	
	var countSum = 0, priceSum = 0;
	var rows = [];
	var dataStr, imgSrc, imgStr, countStr, subtotal, altSrc;
	for (var i=0; i<itemCount; i++) {
		orderItems[i]['id'] = i;
		if (orderItems[i]['i_name'] != null)
			dataStr = "<a style='font-weight:bold;'>"+orderItems[i]['i_code']+"</a><br>"+"<a >"+orderItems[i]['i_name']+"</a>";
		else
			dataStr = "<a style='font-weight:bold;'>"+orderItems[i]['i_code']+"</a>";
		imgSrc = orderItems[i]['path']+"/"+orderItems[i]['i_id']+"_"+orderItems[i]['m_no']+".jpg";
		if (orderItems[i]['m_no'] != null)
			altSrc = imgSrc;
		else
			altSrc = ""; 
		imgStr = "<img width='40' height='60' style='object-fit: cover' src='"+imgSrc+"' alt='"+altSrc+"' onclick='showImageView(this)'>";
		if (orderItems[i]['unit'] == "1") {
			countStr = orderItems[i]['count'];
			orderItems[i]['real_count'] = orderItems[i]['count'];
		} else {
			countStr = orderItems[i]['count']+"<br>(x"+orderItems[i]['unit']+")";
			orderItems[i]['real_count'] = (parseInt(orderItems[i]['count'])*parseInt(orderItems[i]['unit'])).toString();
		}
		subtotal = parseInt(orderItems[i]['real_count'])*parseFloat(orderItems[i]['price']);
		orderItems[i]['subtotal'] = subtotal.toFixed(2);
		rows.push({
			id: i,
			idx_image: imgStr,
			idx_data: dataStr,
			idx_count: countStr,
			idx_price: orderItems[i]['price'],
			idx_subtotal: orderItems[i]['subtotal']
		});	
		countSum += parseInt(orderItems[i]['real_count']);
		priceSum += subtotal;
	}
	$table.bootstrapTable('append', rows);
	order['count_sum'] = countSum.toString();
	order['price_sum'] = priceSum.toFixed(2);
	displaySum(0);
}
// Display error
function loadError(result) {
	alert(myRes['msgErrLoadDb']);
}
// Load orderVariant
function loadOrderVariantYes(result) {
	orderVariant = new Array();
	variantCount = 0;
	var newVariant = null, newVariantCount = 0;
	var lastIid = "";
	for (var i=0; i<result.length; i++) {
		if (lastIid != result[i]['i_id']) {
			lastIid = result[i]['i_id'];
			if (newVariant != null) {
				orderVariant[variantCount] = newVariant;
				variantCount++;
			}
			newVariant = new Array();
			newVariantCount = 0;
		}
		newVariant[newVariantCount] = result[i];
		newVariantCount++;
	}
	orderVariant[variantCount] = newVariant;
	variantCount++;
}
function loadOrderVariantNo(result) {
	
}
// Display sum
function displaySum(option) {	
	var sumPrice = parseFloat(order['price_sum']);
	var sumTotal = sumPrice - parseFloat(order['discount_rate'])/100*sumPrice + 
					parseFloat(order['fee1']) + parseFloat(order['fee2']) + parseFloat(order['fee3']) + parseFloat(order['fee4']);
	var sumPaid = parseFloat(order['pay_cash']) + parseFloat(order['pay_card']) + parseFloat(order['pay_bank']) + 
					parseFloat(order['pay_check']) + parseFloat(order['pay_other']) + parseFloat(order['pay_paypal']) + parseFloat(order['pay_vorkasse']);
	var sumDue = sumTotal - sumPaid;
	if(sumDue <= 0)
		sumDue = 0;

	order['total_sum'] = sumTotal.toFixed(2);
	order['paid_sum'] = sumPaid.toFixed(2);
	order['due'] = sumDue.toFixed(2);
	
	document.getElementById("sumCount").innerHTML = order['count_sum'];
	document.getElementById("sumPrice").innerHTML = order['price_sum'];
	document.getElementById("sumTotal").innerHTML = order['total_sum'];
	document.getElementById("sumPaid").innerHTML = order['paid_sum'];
	document.getElementById("sumDue").innerHTML = order['due'];
	
	if (!option)
		saveDbOrder();
}
// Submit order
function submitOrder() {
	if (itemCount <= 0) {
		alert(myRes['msgErrNoData']);
		return;
	}
	if (!confirm(myRes['msgConfirmSave'])) {
		return;
	}
	saveDbOrder(1);
}
// Cancel/close order
function cancelOrder() {
	if (itemCount <= 0)
		delDbOrder();
	else {
		var url = "<?php echo $backPhp; ?>";
		window.location.assign(url);
	}
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
	ITEM WINDOW FUNCTIONS
************************************************************************/
function doneItem() {
	// get values from modalOrderItem
	var count = document.getElementById("m_count").value;
	var price = document.getElementById("m_price").value;
	// Validation
	if (!checkValid(count, thisItem['old_count'], price))
		return;
	count = parseInt(count).toString();
	price = parseFloat(price).toFixed(2);
	// Close the modalOrderItem
	$modalOrderItem.modal("toggle");
	// update or add
	var type = document.getElementById("m_type").value;
	if (type == "1")
		updateItem(count, price);
	else
		addItem(count, price);	
}
$modalOrderItem.on('shown.bs.modal', function () {
	  $('#m_count').trigger('focus');
})
function showVariantButton(option) {
	if (option) {
		document.getElementById("btnVariant").style.display = "inline";
		document.getElementById("btnCountMinus").style.display = "none";
		document.getElementById("btnCountAdd").style.display = "none";
		document.getElementById("m_count").readOnly = true;
	} else {
		document.getElementById("btnVariant").style.display = "none";
		document.getElementById("btnCountMinus").style.display = "inline";
		document.getElementById("btnCountAdd").style.display = "inline";
		document.getElementById("m_count").readOnly = false;
	}
}
function checkValid(count, old_count, price) {
	if (count == "" || !onlyDigits(count) || parseInt(count) <= 0) {
		alert(myRes['msgErrDataInput']);
		$('#m_count').trigger('focus');
		return false;
	}
/*	
	if (parseInt(count) > parseInt(old_count)) {
		alert(myRes['msgErrNoEnoughStock']);
		$('#m_count').trigger('focus');
		return false;
	}
*/
	if (price == "" || !onlyNumber(price)) {
		alert(myRes['msgErrDataInput']);
		$('#m_price').trigger('focus');
		return false;
	}
	return true;
}
/************************************************************************
	NEW ITEM 
************************************************************************/
// Show new search
function showNewSearch() {
	document.getElementById("ms_i_code").value = "";
	$modalOrderNewSearch.modal();
}
$modalOrderNewSearch.on('shown.bs.modal', function () {
	  $('#ms_i_code').trigger('focus');
})
// This is a callback for autocomplete
function doneAutocomp() {
	if ($modalCustSearch.is(':visible'))
		doneAutocompCust();
	else if ($modalCust.is(':visible') == false)
		searchCode();
}
// Search by i_code
function searchCode() {
	var code = document.getElementById("ms_i_code").value;
	if (code == "")
		return false;
//	var link = "getInvByCode.php?code="+code;
//	getRequest(link, searchCodeYes, searchCodeNo);
	var ok = -1;
	for (var i=0; i<a_ivariants.length; i++) {
		if (a_ivariants[i]['i_code'] == code) {
			ok = i;			
			break;
		}
	}
	if (ok >= 0)
		searchCodeYes(a_ivariants[ok]);
	else
		searchCodeNo(null);
}
// New thisItem
function newThisItem(inv) {
	thisItem = new Object();
	thisItem['i_id'] = inv['i_id'];
	thisItem['i_code'] = inv['i_code'];
	thisItem['i_name'] = inv['i_name'];
	thisItem['old_count'] = inv['count'];
	thisItem['price'] = inv['price'];
	thisItem['cost'] = inv['cost'];		
	thisItem['m_no'] = inv['m_no'];
	thisItem['path'] = inv['path'];
	if (inv['unit'] == "1") {
		thisItem['unit'] = "1";
	} else {
		thisItem['unit'] = inv['unit'];
	}		
}
// Result back
function searchCodeYes(invs) {
//	var inv = invs[0];
	var inv = invs;
	// check if the product exists in the list
	var isCodeExist = false;
	for (var i=0; i<itemCount; i++) {
		if (orderItems[i]['i_id'] == inv['i_id']) {
			isCodeExist = true;
			break;
		}
	}
	if (isCodeExist) {
		alert(myRes['msgErrDupProduct']);
		$('#m_i_code').trigger('focus');
		return;
	}	
	// close search window
	$modalOrderNewSearch.modal("toggle");		
	// search variant
	document.getElementById("btnVariant").style.display = "none";
//	getRequest("getVariant.php?i_id="+inv['i_id'], searchVariantYes, searchVariantNo);
	if (inv['variant'] != null)
		searchVariant(inv['i_id']);
	else
		searchVariantNo();
	// thisItem
	newThisItem(inv);
	// unit
	if (inv['unit'] == "1") {
		document.getElementById("m_unit_str").value = myRes['txtUnitOne']+" (x1)";
	} else {
		document.getElementById("m_unit_str").value = myRes['txtUnitMore']+" (x"+inv['unit']+")";	
	}		
	// set type
	document.getElementById("m_type").value = "0";
	// set values
	document.getElementById("m_i_code").value = thisItem['i_code'];
	document.getElementById("m_i_name").value = thisItem['i_name'];
	document.getElementById("m_count").value = "";
	document.getElementById("m_old_count").value = thisItem['old_count'];
	document.getElementById("m_price").value = thisItem['price'];	
	// src for m_img
	var imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+thisItem['m_no']+".jpg";
	document.getElementById("m_img").src = imgSrc;
	if (thisItem['m_no'] != null)
		document.getElementById("m_img").alt = imgSrc;
	else
		document.getElementById("m_img").alt = "";
	// hide 'delete'
	document.getElementById("btnDel").style.display = "none";
	// show modal
	$modalOrderItem.modal();
}
// Error or no found
function searchCodeNo(invs) {
	alert(myRes['msgErrProductNoExist']);
	$('#m_i_code').trigger('focus');
}
// search variant
function searchVariant(iId) {
	var result = new Array();
	for (var i=0; i<a_ivariants.length; i++) {
		if (a_ivariants[i]['i_id'] == iId) {
			var vart = new Object();
			vart['i_id'] = iId;
			vart['iv_id'] = a_ivariants[i]['iv_id'];
			vart['variant'] = a_ivariants[i]['variant'];
			vart['amount'] = a_ivariants[i]['amount'];
			vart['barcode'] = a_ivariants[i]['barcode'];
			vart['m_no'] = a_ivariants[i]['im_no'];
			result.push(vart);
		}
	}
	result.sort(function(a, b){var x=a['variant'];y=b['variant'];return ((x < y) ? -1 : ((x > y) ? 1 : 0));});
	if (result.length <= 0)
		searchVariantNo();
	else
		searchVariantYes(result);
}

// Search variant back
function searchVariantYes(result) {
	mdvsVariant = result;
	showVariantButton(true);	
}
function searchVariantNo() {
	mdvsVariant = null;
	showVariantButton(false);	
}
// Add new item
function addItem(count, price) {
	var countSum = parseInt(order['count_sum']);
	var priceSum = parseFloat(order['price_sum']);
	// Add new item to table
	thisItem['id'] = itemIdCount;
	thisItem['o_id'] = oId;
	thisItem['count'] = count;
	thisItem['price'] = price;	
	var subtotal = 0;
	if (thisItem['unit'] == "1")
		thisItem['real_count'] = thisItem['count'];
	else
		thisItem['real_count'] = (parseInt(thisItem['count'])*parseInt(thisItem['unit'])).toString();
	subtotal =  parseInt(thisItem['real_count'])*parseFloat(thisItem['price']);
	thisItem['subtotal'] = subtotal.toFixed(2);
	orderItems[itemCount] = thisItem;
	// add variant to orderVariant
	if (mdvsVariant != null) {
		orderVariant[variantCount] = mdvsVariant;
		variantCount++;
	}
	// add new item to table		
	var rows = [];
	var countStr, dataStr, imgSrc, imgStr, altSrc;
	if (thisItem['i_name'] != null)
		dataStr = "<a style='font-weight:bold;'>"+thisItem['i_code']+"</a><br>"+"<a >"+thisItem['i_name']+"</a>";
	else
		dataStr = "<a style='font-weight:bold;'>"+thisItem['i_code']+"</a>";
	imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+thisItem['m_no']+".jpg";
	if (thisItem['m_no'] != null)
		altSrc = imgSrc;
	else
		altSrc = "";
	imgStr = "<img width='40' height='60' style='object-fit: cover' src='"+imgSrc+"' alt='"+altSrc+"' onclick='showImageView(this)'>";
	if (thisItem['unit'] == "1")
		countStr = thisItem['count'];
	else
		countStr = thisItem['count']+"<br>(x"+thisItem['unit']+")";
	rows.push({
		id: itemIdCount,
		idx_image: imgStr,
		idx_data: dataStr,
		idx_count: countStr,
		idx_price: thisItem['price'],
		idx_subtotal: thisItem['subtotal']
	});
	$table.bootstrapTable('append', rows);
	// recalculate summary
	countSum += parseInt(thisItem['real_count']); 
	priceSum += subtotal;
	order['count_sum'] = countSum.toString();
	order['price_sum'] = priceSum.toFixed(2);
	displaySum();
	// increase counts
	itemCount++;
	itemIdCount++;
	// add orderItem to database
	addDbItem(thisItem);
	if (mdvsVariant != null)
		addDbVariant(mdvsVariant);
}
/************************************************************************
	EDIT ITEM 
************************************************************************/
$table.on('click-row.bs.table', function (e, row, $element, field) {
	if (field == "idx_image") {
		return;
	}
	var index = getItemIndexById(row.id);
	if (index < 0)
		return;
	thisItem = orderItems[index];
	// Set type
	document.getElementById("m_type").value = "1";
	////Set values
	document.getElementById("m_i_code").value = thisItem['i_code'];
	document.getElementById("m_i_name").value = thisItem['i_name'];
	document.getElementById("m_count").value = thisItem['count'];
	document.getElementById("m_old_count").value = thisItem['old_count'];	
	document.getElementById("m_price").value = thisItem['price'];
	// unit
	if (thisItem['unit'] == "1") {
		document.getElementById("m_unit_str").value = myRes['txtUnitOne']+" (x1)";	
	} else {
		document.getElementById("m_unit_str").value = myRes['txtUnitMore']+" (x"+thisItem['unit']+")";	
	}	
	// Image
	document.getElementById("m_img").src = "blank.jpg";
	var imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+thisItem['m_no']+".jpg";
	document.getElementById("m_img").src = imgSrc;
	if (thisItem['m_no'] != null)
		document.getElementById("m_img").alt = imgSrc;
	else
		document.getElementById("m_img").alt = "";
	// Show 'delete'
	document.getElementById("btnDel").style.display = "block";
	// Variant
	var v_idx = getVariantIndexById(thisItem['i_id']);
	if (v_idx >= 0) {
		mdvsVariant = orderVariant[v_idx];
		showVariantButton(true);
	} else {
		mdvsVariant = null;
		showVariantButton(false);
	}
	// Show modalOrderItem
	$modalOrderItem.modal();
});
// Update item
function updateItem(count, price) {
	// we do NOT update thisItem yet, because we need calculate sum and diff
	var real_count = "", subtotal = 0, countStr = "";
	if (thisItem['unit'] == "1") {
		countStr = count;
		real_count = count;
	}
	else {
		countStr = count+"<br>(x"+thisItem['unit']+")";
		real_count = (parseInt(count)*parseInt(thisItem['unit'])).toString();		
	}
	subtotal =  parseInt(real_count)*parseFloat(price);
	// update table
	$table.bootstrapTable('updateCellByUniqueId', {
        id: thisItem['id'],
        field: 'idx_count',
        value: countStr
     })
	 $table.bootstrapTable('updateCellByUniqueId', {
        id: thisItem['id'],
        field: 'idx_price',
        value: price
     })
	 $table.bootstrapTable('updateCellByUniqueId', {
        id: thisItem['id'],
        field: 'idx_subtotal',
        value: subtotal.toFixed(2)
     })	 
	// create item for database
	var item = new Object();
	item['o_id'] = thisItem['o_id'];
	item['i_id'] = thisItem['i_id'];
	item['cost'] = thisItem['cost'];
	item['price'] = price;
	var diff = parseInt(thisItem['count']) - parseInt(count); 
	if (diff > 0) {
		item['count'] = diff.toString();
		updateDbItem(item, 0);
	} else {
		var diff1 = 0 - diff;
		item['count'] = diff1.toString();
		updateDbItem(item, 1);
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
	// update variant
	if (mdvsVariant != null)
		updateDbVariant(mdvsVariant);
}
/************************************************************************
	DELETE ITEM 
************************************************************************/
function delItem() {
	if (!confirm(myRes['msgConfirmDelete'])) {
		return;
	}
	$modalOrderItem.modal("toggle");	
	// Remove item from table
	$table.bootstrapTable('removeByUniqueId', thisItem['id']);
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
	var index = getItemIndexById(thisItem['id']);
	orderItems.splice(index, 1);
	itemCount = itemCount - 1;
	displaySum();
	// delete variant
	if (mdvsVariant != null) {
		delDbVariant(mdvsVariant);
		v_idx = getVariantIndexById(mdvsVariant['i_id']);
		ordervariant.splice(index, 1);
		variantCount = variantCount - 1;
	}		
}
/************************************************************************
	DATABASE FUNCTIONS
************************************************************************/
function prepareOrder() {
	if (order['k_id'] == "")
		order['k_id'] = "0";
	
	var profit = 0;
	for (var i=0; i<orderItems.length; i++) {
		if (order['discount_rate'])
			profit += parseInt(orderItems[i]['real_count']) * ((1-parseFloat(order['discount_rate'])/100)*parseFloat(orderItems[i]['price'])  - parseFloat(orderItems[i]['cost']));
		else
			profit += parseInt(orderItems[i]['real_count']) * (parseFloat(orderItems[i]['price'])  - parseFloat(orderItems[i]['cost']));
	}
	order['profit'] = profit.toFixed(2);
}

// Add orderItem to database
function addDbItem(item) {	
	var link = "postOrderItemAdd.php";
	var form = new FormData();
	form.append('orderitem', JSON.stringify(item));
	postRequest(link, form, submitOk1, displayError1);	
}

// Delete orderItem from database
function delDbItem(item) {
	var link = "postOrderItemDel.php";
	var form = new FormData();
	form.append('orderitem', JSON.stringify(item));
	postRequest(link, form, submitOk1, displayError1);
}

// Update orderItem in database
function updateDbItem(item, option) {	
	var link = "postOrderItemUpdate.php";
	var form = new FormData();
	form.append('option', option);
	form.append('orderitem', JSON.stringify(item));
	postRequest(link, form, submitOk1, displayError1);
}
function saveDbOrder(option) {
	// Prepare order record for database
	prepareOrder();
	// Update order in database
	var link = "postOrderUpdate.php";
	var form = new FormData();
	form.append('order', JSON.stringify(order));
	if (option)
		postRequest(link, form, submitOk, displayError);
	else
		postRequest(link, form, null, null);
}

function submitOk(ok) {
	var url = "<?php echo $backPhp; ?>";
	window.location.assign(url);	
}

function displayError(err) {
}

function submitOk1(ok) {
}

function displayError1(err) {
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
}

function showFee() {
	displayFee();
	$modalOrderFee.modal();	
}

function doneFee() {
	var fees = ["fee1", "fee2", "fee3", "fee4"];
	var data = "";
	
	for (var i=0; i<4; i++) {
		data = checkNumber("mf_"+fees[i], 0, parseFloat(order['price_sum']));
		if (!data) {
			alert(myRes['msgErrDataInput']);
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
         return myRes['fmOrderPayNoItem'];
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
	document.getElementById("mp_total").value = order['total_sum'];
	document.getElementById("mp_pays_total").value = pays_total.toFixed(2);
	document.getElementById("mp_pays_due").value = pays_due.toFixed(2);
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
		alert(myRes['fmOrderPayDuplicate']);
		$("#mp_amount").trigger('focus');
		return;
	}
	// check amount	
	var amount = document.getElementById("mp_amount").value;
	if (!onlyNumber(amount) || parseFloat(amount) <= 0 || parseFloat(amount) > parseFloat(pays_due.toFixed(2))) {
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
	pays_due = parseFloat(order['total_sum']) - pays_total;
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
	order['due'] = pays_due;
	$modalOrderPay.modal("toggle");
	displaySum();
}
/************************************************************************
	DESTROY
************************************************************************/
function destroyDone(result) {
	var url = "<?php echo $backPhp; ?>";
	window.location.assign(url);
}
function destroyError(result) {

}
function destroyOrder() {
	if (!confirm(myRes['fmOrderConfirmDelete'])) {
		return;
	}
	delDbOrder();
}
function delDbOrder() {
	var link = "postOrderDel.php";
	var form = new FormData();
	form.append('order', JSON.stringify(order));
	if(orderItems && orderItems.length > 0)
		form.append('orderitems', JSON.stringify(orderItems));
	if(orderVariant && orderVariant.length > 0)
		form.append('ordervariants', JSON.stringify(orderVariant));
	postRequest(link, form, destroyDone, destroyError);	
}
/************************************************************************
	INVOICE
************************************************************************/
function invoiceOk(result) {
	alert(myRes['fmOrderInvoiceOk']);
}

function invoiceError(result) {
	alert(myRes['fmOrderInvoiceErr']);
}

function invoiceExist(result) {
	alert(myRes['fmOrderInvoiceDup']);
	return false;
}

function invoiceNew(result) {
	var link = "postInvoiceAdd.php";
	var form = new FormData();
	form.append('order', JSON.stringify(order));
	form.append('orderitems', JSON.stringify(orderItems));
	postRequest(link, form, invoiceOk, invoiceError);	
}

function createInvoice() {
	if (itemCount <= 0) {
		alert(myRes['msgErrNoData']);
		return false;
	}

	getRequest("getInvoiceByOrder.php?o_id="+oId, invoiceExist, invoiceNew);	
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
	FUNCTIONS
************************************************************************/
// Prevent 'enter' key to submit
$('form input').keydown(function (e) { 
    if (e.keyCode == 13) {
        e.preventDefault();
/*		if ($("#m_i_code").is(":focus")){
			searchCode();
		} else
			return false;
*/
    }
});

// Validation
function onlyDigits(s) {
	if (s == "")
		return true;
	var d;
	for (var i=0; i<s.length; i++) {
		d = s[i];
		if (d < "0" || d > "9")
			return false
	}
	return true;
}

function onlyNumber(s) {
	if (s == "")
		return true;
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

// System
function countAdd() {
	var v = document.getElementById("m_count").value;
	if (v == "")
		v = "0";
	var d = parseInt(v);
	var v1 = document.getElementById("m_old_count").value;
	var d1 = parseInt(v1);
	if (d == d1)
		return;
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
	if (data == "")
		return "0.00";
	if (!onlyNumber(data))
		return false;
	var d = parseFloat(data);	
	if (d < min || d > max)
		return false;
	
	return d.toFixed(2);
}
/************************************************************************
	VARIANT
************************************************************************/
function showVariantWindow() {
	var myId = thisItem['i_id'];
	var myCode = thisItem['i_code'];
	var myPath = thisItem['path'];
	mdvsShow(myId, oId, myCode, myPath, 1);
}
// Done variant
function mdvsDoneVariant() {
	
}
// Find variant in orderVariant by i_id
function getVariantIndexById(id) {
	for (var i=0; i<variantCount; i++) {
		if (orderVariant[i][0]['i_id'] == id)
			return i;
	}	
	return -1;
}
// Add variant
function addDbVariant(variant) {
	var form = new FormData();
	form.append('ordervariant', JSON.stringify(variant));
	postRequest("postOrderVariantAdd.php", form, null, null);
}
// Update variant
function updateDbVariant(variant) {
	var form = new FormData();
	form.append('ordervariant', JSON.stringify(variant));
	postRequest("postOrderVariantUpdate.php", form, null, null);
}
// Delete variant
function delDbVariant(variant) {
	var form = new FormData();
	form.append('ordervariant', JSON.stringify(variant));
	postRequest("postOrderVariantDel.php", form, null, null);
}
/************************************************************************
	BARCODE
************************************************************************/
var barcodeOK = false, newBarcode = true;
var mdbcCount = 0;
var mdbcVariantId = 0;
var mdbcType = 0;
$modalBarcode = $('#modalBarcode');
function showBarcode() {
	mdbcClearAll();
	$modalBarcode.modal();
}
function mdbcClearAll() {
	thisId = document.getElementById("mdbc_i_code");
	thisId.style.backgroundColor = "none";
	thisId.style.color = "black";
	thisId.innerText = myRes['fmOrderItemArt']+":";
	document.getElementById("mdbc_i_name").innerText = myRes['fmOrderItemName']+":";	
	document.getElementById("mdbc_variant").innerText = myRes['fmOrderItemVar']+":";
	document.getElementById("mdbc_old_count").innerText = myRes['fmOrderItemStock']+":";	
	document.getElementById("mdbc_price").innerText = myRes['fmOrderItemPrice']+":";
	var imgSrc = "blank.jpg";
	document.getElementById("mdbc_img").src = imgSrc;
	mdbcResetCount();
	newBarcode = true;	
}
// Query record by barcode
function queryItembyBarcode(code1) { 
	if (code1 == "")
		return false;
/*
	var link = "getInvByCode1.php?code1="+code1;
	getRequest(link, searchBarcodeYes, searchBarcodeNo);
*/
	mdbcClearAll();
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
	if (ok >= 0)
		searchBarcodeYes(a_ivariants[ok]);
	else
		searchBarcodeNo(null);		
}

function searchBarcodeYes(invs) {
	barcodeOK = true;
	playBeep(1);
//	var inv = invs[0];
	var inv = invs;
	thisId = document.getElementById("mdbc_i_code");
	thisId.style.backgroundColor = "green";
	thisId.style.color = "white";
	thisId.innerText = myRes['fmOrderItemArt']+": "+inv['i_code'];
	document.getElementById("mdbc_i_name").innerText =  myRes['fmOrderItemName']+": "+inv['i_name'];
	document.getElementById("mdbc_old_count").innerText =  myRes['fmOrderItemStock']+": "+inv['count'];	
	document.getElementById("mdbc_price").innerText = myRes['fmOrderItemPrice']+": "+inv['price'];
	var imgSrc = inv['path']+"/"+inv['i_id']+"_"+inv['im_no']+"_s.jpg";
	document.getElementById("mdbc_img").src = imgSrc;
	
	document.getElementById("mdbc_count").innerText = "1";
	mdbcCount = 1;
	if (inv['unit'] == "1") {
		document.getElementById("mdbc_unit").innerText =  myRes['txtUnitOne']+" (x1)";
	} else {
		document.getElementById("mdbc_unit").innerText = myRes['txtUnitMore']+" (x"+inv['unit']+")";	
	}
	// variant
	if (inv['variant'] != null) {
		mdbcVariantId = inv['iv_id'];
		document.getElementById("mdbc_variant").innerText = myRes['fmOrderItemVar']+": "+inv['variant'];
	} else {
		mdbcVariantId = 0;
		document.getElementById("mdbc_variant").innerText = myRes['fmOrderItemVar']+": No";
	}
	// update or add
	var index = -1;
	mdvsVariant = null;
	for (var i=0; i<itemCount; i++) {
		if (orderItems[i]['i_id'] == inv['i_id']) {
			index = i;
			break;
		}
	}
	if (index >= 0) {
		mdbcType = 1;
		thisItem = orderItems[index];
		var v_idx = getVariantIndexById(thisItem['i_id']);
		if (v_idx >= 0)
			mdvsVariant = orderVariant[v_idx];
	} else {
		mdbcType = 0;
		newThisItem(inv);
		if (inv['variant'] != null)
			searchBCVariant(inv['i_id']);
		else
			searchBCVariantNo();
	}
}
function playBeep(option) {
	if (option == 0)
		document.getElementById('playBeepNo').play();
	else
		document.getElementById('playBeepYes').play();
}
// Error or no found
function searchBarcodeNo(invs) {
	barcodeOK = false;
	playBeep(0);
	thisId = document.getElementById("mdbc_i_code");
	thisId.style.backgroundColor = "red";
	thisId.style.color = "white";
	thisId.innerText = myRes['fmOrderBCError'];;
}
// search BC variant
function searchBCVariant(iId) {
	var result = new Array();
	for (var i=0; i<a_ivariants.length; i++) {
		if (a_ivariants[i]['i_id'] == iId) {
			var vart = new Object();
			vart['i_id'] = iId;
			vart['iv_id'] = a_ivariants[i]['iv_id'];
			vart['variant'] = a_ivariants[i]['variant'];
			vart['amount'] = a_ivariants[i]['amount'];
			vart['barcode'] = a_ivariants[i]['barcode'];
			vart['m_no'] = a_ivariants[i]['im_no'];
			result.push(vart);
		}
	}
	result.sort(function(a, b){var x=a['variant'];y=b['variant'];return ((x < y) ? -1 : ((x > y) ? 1 : 0));});
	if (result.length <= 0)
		searchBCVariantNo();
	else
		searchBCVariantYes(result);
}
// Search variant back
function searchBCVariantYes(result) {
	mdvsVariant = result;
}
function searchBCVariantNo() {
	mdvsVariant = null;
}
function mdbcAddCount(keyCode) {
	var count = mdbcCount*10+(keyCode-48);
	if (count >= 8000000)
		return;
	mdbcCount = count;
	document.getElementById("mdbc_count").innerText = mdbcCount.toString();
	
}
function mdbcBackCount() {
	if (mdbcCount > 10) {
		var countStr = mdbcCount.toString();
		countStr = countStr.substr(0, countStr.length-1);
		mdbcCount = parseInt(countStr);
	} else {
		mdbcCount = 0;
	}
	document.getElementById("mdbc_count").innerText = mdbcCount.toString();
}
function mdbcResetCount() {
	mdbcCount = 0;
	document.getElementById("mdbc_count").innerText = "0";
}
var mdbcCode1 = "";
var keytime = 0;
document.getElementById("modalBarcode").addEventListener('keydown', function(e) {
	if (e.keyCode == 13){ 
		e.preventDefault();
		if (mdbcCode1.length < 2)
			return;
		mdbcSaveItem();
		queryItembyBarcode(mdbcCode1);
		mdbcCode1 = "";;
		newcode = "";
		keycount = 0;
//		mdbcResetCount();
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
// Cancel item
function mdbcCancel() {
	barcodeOK = false;
	$modalBarcode.modal("toggle");
}
// Save item
function mdbcDone() {
	mdbcSaveItem();
	$modalBarcode.modal("toggle");
}
function mdbcSaveItem() {
	if (!barcodeOK)
		return;
	barcodeOK = false;
	var count = document.getElementById("mdbc_count").innerText;
	var price = thisItem['price'];
	if (mdbcType == 1) {
		var itemcount = (parseInt(count) + parseInt(thisItem['count'])).toString();
		if (mdvsVariant != null) {
			for (var i=0; i<mdvsVariant.length; i++) {
				if (mdvsVariant[i]['iv_id'] == mdbcVariantId) {
					mdvsVariant[i]['count'] = (parseInt(count) + parseInt(mdvsVariant[i]['count'])).toString();
					mdvsVariant[i]['count_diff'] = parseInt(count);
				} else {
					mdvsVariant[i]['count_diff'] = 0;
				}
			}
		}
		updateItem(itemcount, price);
	} else {
		if (mdvsVariant != null) {
			for (var i=0; i<mdvsVariant.length; i++) {
				mdvsVariant[i]['o_id'] = oId;
				mdvsVariant[i]['i_id'] = thisItem['i_id'];
				if (mdvsVariant[i]['iv_id'] == mdbcVariantId) {
					mdvsVariant[i]['count'] = count;
					mdvsVariant[i]['count_diff'] = count;
				} else {
					mdvsVariant[i]['count'] = 0;
					mdvsVariant[i]['count_diff'] = 0;
				}
			}
		}
		addItem(count, price);
	}
}
// Keyboard
function mdbcCalCount(key) {
	if (newBarcode) {
		var count = key;
		newBarcode = false;
	} else {
		var count = mdbcCount*10+key;
	}
	if (count >= 8000000)
		return;
	mdbcCount = count;
	document.getElementById("mdbc_count").innerText = mdbcCount.toString();	
}
function mdbcKey0() {mdbcCalCount(0);}
function mdbcKey1() {mdbcCalCount(1);}
function mdbcKey2() {mdbcCalCount(2);}
function mdbcKey3() {mdbcCalCount(3);}
function mdbcKey4() {mdbcCalCount(4);}
function mdbcKey5() {mdbcCalCount(5);}
function mdbcKey6() {mdbcCalCount(6);}
function mdbcKey7() {mdbcCalCount(7);}
function mdbcKey8() {mdbcCalCount(8);}
function mdbcKey9() {mdbcCalCount(9);}
function mdbcKeyC() {mdbcResetCount();}
function mdbcKeyAdd() { if (mdbcCount < 8000000) mdbcCount++; document.getElementById("mdbc_count").innerText = mdbcCount.toString();}
function mdbcKeySub() { if (mdbcCount > 0) mdbcCount--; document.getElementById("mdbc_count").innerText = mdbcCount.toString();}
function mdbcKeyPeriod() {}
function mdbcKeyComma() {}
/************************************************************************
	PRINT
************************************************************************/
var printType = 0;
function printLiefer() {
	printType = 1;
	printForm();
}
function printOrder() {
	printType = 0;
	printForm();
}
function trueString(str) {
	if (str == null || str == "")
		return "";
	else
		return str;
}
function printForm() {
	var company = <?php echo json_encode($_SESSION['myCompany']) ?>;
	var withHeader = true;
	var d = new Date();
	var t = d.getDate();
	if (t < 10) t = '0'+t;
	var m = d.getMonth()+1;
	if (m < 10) m = '0'+m;
	var dt = t+"/"+m+"/"+d.getFullYear();
 
	if (/Android/i.test(navigator.userAgent)) {
		output = '<html><head><style type="text/css" media="print">@page { size:auto; margin:0.5cm 0.5cm 0.5cm 1cm; }\</style></head><body>';
	} else if (/iPhone|iPad/i.test(navigator.userAgent)){
//		output = '<html><head><style type="text/css" media="print">@page { size:auto; margin:0.8cm 0.8cm 0.8cm 1.5cm; }\</style></head><body>';
		output = '<html><head><style type="text/css">body{margin:0.8cm 0.8cm 0.8cm 1.5cm;}</style></head><body>';
	} else {
		output = '<html><head><style type="text/css" media="print">@page { size:21.0cm 29.7cm; margin:0.8cm 0.8cm 0.8cm 1.5cm; }\</style></head><body>';
	}
	if (myCustomer == null || myCustomer['k_id'] == "" || myCustomer['k_id'] == "0") {
		withHeader = false;
	} else {
		withHeader = true;
		// Company
		output += '<table width="100%" border="0" cellpadding="5" cellspacing="0">';
		output += '<tr><td align="right">';
		output += '<b style="font-size:12px">'+company["c_name"]+'</b><br>';
		output += '<a style="font-size:12px">'+company["address"]+'&nbsp;'+company["post"]+'&nbsp;'+company["city"]+'</a><br>';
		output += '<a style="font-size:12px">Tel:'+company["tel"];
		if (company["mobile"] != null && company["mobile"] != "")
			output += '&nbsp;Mobile:'+company["mobile"]+'</a><br>';
		else
			output += '<br>';
		output += '<a style="font-size:12px">WhatsApp:'+company["whatsapp"];
		if (company["email"] != null && company["email"] != "")
			output += '&nbsp;E-Mail:'+company["email"]+'</a><br>';
		else
			output += '<br>';
		output += '</td></tr></table>';
		// Customer
		output += '<table width="50%" border="1px dotted" cellpadding="2" cellspacing="0">';
		output += '<tr><td align="left">';
		output += '<b style="font-size:12px;">Empfänger</b>';
		output += '</td></tr>';
		output += '<tr><td>';
		if (myCustomer["name1"] != null && myCustomer["name1"] != "")
			output += '<a style="font-size:12px">'+'&nbsp;&nbsp;'+myCustomer["name1"]+'</b><br>';
		output += '<a style="font-size:12px">'+'&nbsp;&nbsp;'+trueString(myCustomer["k_name"])+'</b><br>';
		output += '<a style="font-size:12px">'+'&nbsp;&nbsp;'+trueString(myCustomer["address"])+'</a><br>';
		output += '<a style="font-size:12px">'+'&nbsp;&nbsp;'+trueString(myCustomer["post"])+'&nbsp;'+trueString(myCustomer["city"])+'</a><br>';
		output += '<a style="font-size:12px">'+'&nbsp;&nbsp;'+trueString(myCustomer["country"])+'</a><br>';
		output += '</td></tr></table>';
		output += '<br>';
	}
	// Title 
	output += '<table width="100%" style="border:1px solid #808080" cellpadding="2" cellspacing="0">'
	output += '<tr style="font-size:14px">';
	if (printType == 1)
		output += '<td><h3>Lieferschein</h3></td>';
	else
		output += '<td><h3>Bestellung</h3></td>';
	output += '<td style="border-left:1px solid #808080">Nr.:&nbsp;'+oId+'</td>';
	if (order['date'] == null || order['date'] == "")
		output += '<td style="border-left:1px solid #808080">Datum:&nbsp;'+currentDate(0)+'</td>';
	else
		output += '<td style="border-left:1px solid #808080">Datum:&nbsp;'+convertDate(order['date'],0)+'</td>';
	if (myCustomer != null && myCustomer['k_id'] != "0")
		output += '<td style="border-left:1px solid #808080">Kunden Nr.:&nbsp;'+myCustomer["k_code"]+'</td>';
	else
		output += '<td style="border-left:1px solid #808080">Kunden Nr.:&nbsp;</td>';
	output += '</tr></table>';
	output += '<br>';
	// Table
	output += '<table width="100%" style="border:1px solid #808080;" cellpadding="2" cellspacing="0"><thead>';
	output += '<tr style="font-size:12px">';
	output += '<th style="border-bottom:1px solid #808080;" align="center" >Artikel Nr. und Bezeichnung</th>';
	output += '<th style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="center">Anzahl</th>';
	output += '<th style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="right">Einzelpreis</th>';
	output += '<th style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="right">Nettobetrag</th>';
	output += '</tr></thead><tbody>';
	for (i=0; i<itemCount; i++) {
		var code = "";
		if (orderItems[i]['position'] != null && orderItems[i]['position'] != "")
			code += orderItems[i]['position']+'&nbsp;';
		if (orderItems[i]['i_name'] != null && orderItems[i]['i_name'] != "")
			code += orderItems[i]['i_name']+'&nbsp;';
		code += 'ART.'+orderItems[i]['i_code'];
		if (orderItems[i]['color'] != null && orderItems[i]['color'] != "")
			code += '&nbsp;'+orderItems[i]['color'];
		output += '<tr style="font-size:12px; font-family:Arial">';
		output += '<td style="padding:1px;">'+'&nbsp;&nbsp;'+code+'</td>';
		if (orderItems[i]['unit'] == "1")
			output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+orderItems[i]['count']+'&nbsp;</td>';	
		else
			output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+orderItems[i]['count']+'&nbsp;(x'+orderItems[i]['unit']+')&nbsp;</td>';
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+orderItems[i]['price']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+orderItems[i]['subtotal']+'</td>';
		output += '</tr>';
	}
	output += '<tr><td align="center" style="font-size:12px; font-family:Arial; border-top:1px solid #808080;" colspan="5">===Gesamtmenge:&nbsp;'+order['count_sum']+'&nbsp;Stück===</td></tr>';	
	// Spacing
	if (/Android/i.test(navigator.userAgent)) {
		if (withHeader)
			var maxCount = 36;
		else
			var maxCount = 48;
	} else if (/iPhone|iPad/i.test(navigator.userAgent)){
		if (withHeader)
			var maxCount = 32;
		else
			var maxCount = 43;
	} else {
		if (withHeader)
			var maxCount = 38;
		else
			var maxCount = 50;
	}
	for (i=0; i<maxCount-itemCount - 10; i++) {
		output += '<tr><td style="padding:1px; font-size:12px; font-family:Arial" colspan="5">&nbsp;</td></tr>';
	}
	// Summary
	output += '<tr align="right" style="padding:1px; font-size:14px;">';
	output += '<td colspan="3" style="border-top:1px solid #808080;">SUMME:&nbsp;</td><td style="border-top:1px solid #808080;">'+order['price_sum']+'&nbsp;&euro;&nbsp;&nbsp;</td>';
	output += '</tr>';
	output += '<tr align="right" style="padding:1px; font-size:14px;">';
	output += '<td colspan="3">Sk(%):&nbsp;</td><td>'+order['discount_rate']+'%&nbsp;&nbsp;</td>';
	output += '</tr>';
	output += '<tr align="right" style="padding:1px; font-size:18px; font-weight:bold;">';
	output += '<td colspan="3">TOTAL:&nbsp;</td><td>'+order['total_sum']+'&nbsp;&euro;&nbsp;&nbsp;</td>';
	output += '</tr>';
	output += '</tbody></table>';
	
	output += '</body></html>'; 
	
	if (/Android/i.test(navigator.userAgent)) {
		openPrintDialogue(output);""
	} else {
		var mywindow = window.open();
		mywindow.document.write(output);
		mywindow.document.close();
		mywindow.focus();
		mywindow.print();
		if (/iPhone|iPad/i.test(navigator.userAgent)) {
			mywindow.onafterprint = function () {
			mywindow.close();
			}
		} else {
			mywindow.close();
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

function currentDate(option) {
	var dt, d = new Date();
	var t = d.getDate();
	if (t < 10) t = '0'+t;
	var m = d.getMonth()+1;
	if (m < 10) m = '0'+m;
	
	if (option == 1)
		dt = t+m+d.getFullYear();
	else if (option == 2)
		dt = d.getFullYear()+"-"+m+"-"+t;
	else
		dt = t+"/"+m+"/"+d.getFullYear();
	
	return dt;
}

function convertDate(date, option) {
	if (date.length < 10)
		return "00/00/0000";
	var y = date.substring(0,4);
	var m = date.substring(5,7);
	var d = date.substring(8,10);
	if (option == 1)
		var dt = y+"-"+m+"-"+d;
	else
		var dt = d+"/"+m+"/"+y;
	
	return dt;
}

function orderFormatter(index, row) {
    var html = '<div class="p-1"> <table class="table" data-toggle="table">';
	html += '<thead><tr>';
	html += '<th data-field="idx_count" style="width:15%"></th>';
	html += '<th data-field="idx_item" style="width:20%"></th>';
	html += '<th data-field="idx_image" style="width:65%"></th>';
	html += '</tr></thead>';
	html += '<tbody>';

	var thisOrderItem = orderItems[index]; 
	var thisID = orderItems[index]['i_id']; 
	var thisIndex = getVariantIndexById(thisID); 
	if (thisIndex < 0) {
		html += '</tbody>';
		html += '</table></div>'; 
		return html;
	}
	var thisVar = orderVariant[thisIndex];
	var imgSrc, imgStr, countStr, altSrc;
	
	for (var i=0; i<thisVar.length; i++){
		if (thisVar[i]['count'] == "0")
			continue;
		imgSrc = thisOrderItem['path']+"/"+thisID+"_"+thisVar[i]['m_no']+".jpg";
		if (thisVar[i]['m_no'] != null)
			altSrc = imgSrc;
		else
			altSrc = "";
		imgStr = "<img width='40' height='60' style='object-fit: cover' src='"+imgSrc+"' alt='"+altSrc+"' onclick='showImageView(this)'>";
		if (thisOrderItem['unit'] == 1) {
			countStr = thisVar[i]['count'];
		} else {
			countStr = thisVar[i]['count']+" (x"+thisOrderItem['unit']+")";
		}			
		html += '<tr><td style="text-align:right">'+countStr+'</td><td  style="text-align:center">'+imgStr+'</td><td>'+thisVar[i]['variant']+'</td></tr>';
	}

	html += '</tbody>';
	html += '</table></div>'; 
	return html;
}

function showImageView(e) {
	var altSrc = $(e).attr("alt");
	if (altSrc == "")
		return;
	document.getElementById("modalImage").src = altSrc;
	document.getElementById("modalImageView").style.display = "block";
}

function mdShowImageView(e) {
	var altSrc = $(e).attr("alt");
	if (altSrc == "")
		return;
	document.getElementById("m_imageZoom").src = altSrc;
	document.getElementById("m_imageView").style.display = "block";
}

</script>

</body>
</html>
