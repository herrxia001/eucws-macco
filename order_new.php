<?php
/********************************************************************************
	File:		order.php
	Purpose:	Order
*********************************************************************************/ 
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

include_once 'db_functions.php';
include_once 'db_invoice.php';

include_once 'resource_'.$_SESSION['uLanguage'].'.php';
$thisResource = new myResource();

$myId = '';
$_SESSION['orderType'] = 0;
$backPhp = 'order_mgt.php';

if($_SERVER['REQUEST_METHOD'] == 'GET'){
	if(isset($_GET['back'])) {
		$backPhp = $_GET['back'].'.php';
	}
	if (isset($_GET['o_id'])) {
		$myId = $_GET['o_id'];
		$_SESSION['orderType'] = 1;
	} else {
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
<html lang="zh">
<head>
    <?php include 'include/header.php' ?>
	<title>MODAS - Order</title>
<style>
body {
 padding-top: 0.2rem;
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
	<?php include "include/modalCustomer.php" ?>
	<?php include "include/modalDel.php" ?>
	<form action="" method="post">
	<input type="text" class="form-control" name="o_id" id="o_id" value="<?php echo $myId ?>" hidden>
	
	<div class="container">	
<!-- order data header -->
	<div class="row mb-1">
		<div class="p-1 col-3 col-sm-3 col-md-3 col-lg-2" style="background-color: DarkSlateGrey">
			<button type="button" class="btn" onclick="closeOrder()"><span style="color:white; font-size:20px" class='fa fa-arrow-left'></span></button>
		</div>
		<div class="p-1 input-group col-6 col-sm-6 col-md-6 col-lg-4"  style="background-color: DarkSlateGrey"> 
			<input type="text" class="form-control autocomplete" name="k_name" id="k_name">
			<button type="button" class="ml-1 btn" id="btnCust" onclick="showCust()"><span style="color:white; font-size:20px" class='fa fa-address-book-o'></span></button>
		</div>
		<div class="p-1 col-3 col-sm-3 col-md-3 col-lg-2" style="background-color: DarkSlateGrey" align="right">
			<button type="button" class="btn" id="btnSave" onclick="submitOrder()" style="font-weight:bold; color:white" ><?php echo $thisResource->comSave ?></span></button>
		</div>
	</div>	
<!-- buttons -->
	<div class="row">
		<div class="p-1 col-3 col-sm-3 col-md-3 col-lg-2" align="left">
			<button type="button" class="btn btn-outline-success" id="btnInvoice" onclick="createInvoice()"><?php echo $thisResource->comInvoice ?></button>			
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
					<th class="p-1" data-field="idx_data" data-width="45" data-width-unit="%" data-halign="center" data-align="left"><?php echo $thisResource->comProductNo ?></th>
					<th class="p-1" data-field="idx_count" data-width="10" data-width-unit="%" data-halign="center" data-align="right"><?php echo $thisResource->comQuantity ?></th>
					<th class="p-1" data-field="idx_price" data-width="10" data-width-unit="%" data-halign="center" data-align="right"><?php echo $thisResource->comPrice ?></th>
					<th class="p-1" data-field="idx_subtotal" data-width="20" data-width-unit="%" data-halign="center" data-align="right"><?php echo $thisResource->comSubtotal ?></th>					
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<div class="row">
		<div class="p-1 col-3 col-sm-3 col-md-3 col-lg-2" align="left">
			<button type="button" class="btn btn-outline-danger" onclick="cancelOrder()"><?php echo $thisResource->comCancel ?></button>	
		</div>
		<div class="p-1 col-9 col-sm-9 col-md-9 col-lg-6" align = "right">
			<label><?php echo $thisResource->comProduct ?>:&nbsp;</label>
				<label id="itemCount" class="mt-2" style="font-weight:bold">0</label>
			<label>&nbsp;&nbsp;<?php echo $thisResource->comTotalQuantity ?>:&nbsp;</label>
				<label id="sumCount" class="mt-2" style="font-weight:bold">0</label>
			<label>&nbsp;&nbsp;<?php echo $thisResource->comTotalGross ?>:&nbsp;</label>
				<label id="sumPrice" class="mt-2" style="font-weight:bold">0.00</label>
		</div>
	</div>
<!-- summary -->
	<div class="row mt-1">
		<div class="p-1 col-6 col-sm-6 col-md-6 col-lg-4" style="background-color: DarkSlateGrey">
			<b style="color:white;">&nbsp;&nbsp;<?php echo $thisResource->comDue ?>&nbsp;</b><label id="sumDue" style="color:white">0.00</label>
			<button type="button" class="ml-1 btn btn-outline-light" id="btnPay"  onclick="showPay()"><span class='fa fa-credit-card'></span></button>			
		</div>
		<div class="p-1 col-6 col-sm-6 col-md-6 col-lg-4" style="background-color: DarkSlateGrey" align="right">
			<label style="color:white">&nbsp;&nbsp;<?php echo $thisResource->comTotalNet ?>:&nbsp;</label>
			<label id="sumTotal" class="mt-2" style="color:white; font-weight:bold">0.00</label>
		</div>
	</div>
	
<!-- Modal: New item search-->
<div class="modal fade" id="modalOrderNewSearch" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdOrderNewSearchTitle"><?php echo $thisResource->comProductSearch ?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12">
					<div class="input-group p-1">
						<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comProductNo ?></span></div>
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

<!-- Modal: order item -->
<div class="modal fade" id="modalOrderItem" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
	data-backdrop="static" data-keyboard="false" style="overflow-y:scroll">
	<div class="modal-dialog" role="document">
		<div class="modal-content">

		<div class="modal-body">
<!-- top menu -->		
		<div class="row">
			<div class="p-1 col-6">
				<label id="m_i_code" class="ml-2 mt-2" style="font-size: 20px; font-weight: bold"></label>
			</div>
			<div class="p-1 col-6" align="right">
				<button type="button" class="btn btn-secondary" onclick="cancelItem()"><span class='fa fa-times'></span></button>
				<button type="button" class="mr-1 btn btn-primary" style="width:60px" onclick="doneItem()"><span class='fa fa-check'></span></button>
			</div>
		</div>
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:2px; width:100%">
		</div>
<!-- item data -->			
		<div class="row">
			<div class="col-8 p-1">
			<div class="p-1 input-group">
				<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comName ?></span></div>
				<input type="text" class="form-control" name="m_i_name" id="m_i_name" readonly style="background-color:white">
			</div>
			<div class="p-1 input-group">
				<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comInventory ?></span></div>
				<input type="text" class="form-control" name="m_old_count" id="m_old_count" readonly style="background-color:white">
			</div>
			<div class="p-1 input-group">
				<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comPrice ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="m_price" id="m_price" oninput="refreshSale();">
				<button type="button" class="ml-1 btn btn-outline-secondary" onclick="showPrice()"><span class='fa fa-ellipsis-h'></span></button>
			</div>
            <div class="p-1 input-group discount_panel">
                <div class="input-group-prepend"><span class="input-group-text">折扣%</span></div>
                <input type="number" min="0" step="1" max="100" class="form-control" name="m_discount" id="m_discount" oninput="refreshSale();">
                <div class="input-group-append"><span class="input-group-text" id="after_price"></span></div>
            </div>
			</div>
			<div class="col-4 p-1 align-self-center" align="center">
				<label id="m_quantity" style="font-size:28px; font-weight:bold; color:green">1</label>
				<br>
				<label id="m_unit_str">x1</label>
			</div>
		</div>
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:2px; width:100%">
		</div>
<!-- no variant -->
		<div class="container p-1" id="containerNoVariant">
			<div class="row" >
			<div class="col-2 p-1 align-self-center">
				<img id="m_img" width="60" height="60" style="object-fit: cover;" onclick="mdShowImageView(this)">
			</div>
			<div class="col-4 p-1 align-self-center">
				<label id="m_color"></label>
			</div>
			<div class="col-6 p-1 align-self-center" align="right">
				<div class="row input-group">
				<button type="button" class="btn btn-outline" id="m_minus" onclick="countMinus()"
						style="touch-action: none"><span class='fa fa-minus'></span></button>
				<input type="number" class="form-control" style="touch-action: none" id="m_count" oninput="countInput()">
				<button type="button" class="btn btn-outline mr-3" id="m_add" onclick="countAdd()" 
						style="touch-action: none"><span class='fa fa-plus'></span></button>
				</div>
			</div>
			</div>
		</div>
<!-- variants -->
		<div class="container p-1" id="containerWithVariant">
		<?php for ($i=0; $i<100; $i++) { ?>
		<div class="row" id="m_varitem<?php echo $i ?>" >
			<div class="col-3 p-1" align="center">
				<img id="m_vimg<?php echo $i ?>" width="60" height="60" style="object-fit: cover" onclick="mdShowImageView(this)">
			</div>
			<div class="col-4 p-1">
				<label id="m_variant<?php echo $i ?>"></label>
			</div>
			<div class="col-5 p-1 align-self-center">
				<div class="row input-group">
				<button type="button" class="btn btn-outline" id="m_vminus<?php echo $i ?>" onclick="countVMinus(this)"
						style="touch-action: none"><span class='fa fa-minus'></span></button>
				<input type="number" class="form-control" id="m_vcount<?php echo $i ?>" oninput="countVInput(this)">
				<button type="button" class="btn btn-outline" id="m_vadd<?php echo $i ?>" onclick="countVAdd(this)" 
						style="touch-action: none"><span class='fa fa-plus'></span></button>
				</div>
			</div>
		</div>
		<?php } ?>
		</div>
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:2px; width:100%">
		</div>
<!-- bottom menu -->		
		<div class="row">
			<div class="col-3 p-1" align="left">
				<button type="button" class="ml-1 btn btn-outline-danger" id="btnDel" onclick="delItem()"><?php echo $thisResource->comDelete ?></button>
			</div>
			<div class="col-9 p-1" align="right">				
				<button type="button" class="btn btn-secondary" onclick="cancelItem()" ><span class='fa fa-times'></span></button>
				<button type="button" class="mr-1 btn btn-primary" style="width:60px" onclick="doneItem()"><span class='fa fa-check'></span></button>
			</div>
		</div>
<!-- image zoom -->
		<div id="m_imageView" class="modal" onclick="this.style.display='none'">			
			<div class="modal-content">
				<img id="m_imageZoom" class="center" style="width:90%; margin-left: auto; margin-right: auto;">
			</div>
		</div>	
		
		</div>
		</div>
	</div>
</div> <!-- End of Modal: order item-->

<!-- Modal: Discount -->
<div class="modal fade" id="modalOrderDis" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdOrderDisTitle"><?php echo $thisResource->comDiscount ?></b>
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text"  style="width:120px;"><?php echo $thisResource->comDiscountRate ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mdi_discount_rate" id="mdi_discount_rate" oninput="displayDiscount()">
			</div>	
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text"  style="width:120px;"><?php echo $thisResource->comDiscountValue ?></span></div>
				<input type="text" class="form-control" name="mdi_discount" id="mdi_discount" oninput="changDis()">
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
			<b class="modal-title" id="mdOrderFeeTitle"><?php echo $thisResource->comFee ?></b>
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->comFeeShipping ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee1" id="mf_fee1">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->comFeeNachnahme ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee2" id="mf_fee2">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->comFeeBank ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee3" id="mf_fee3">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->comFeePack ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee4" id="mf_fee4">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->comFeeOther ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee5" id="mf_fee5">
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
			<b class="modal-title" id="mdOrderPayTitle"><?php echo $thisResource->comPayment ?></b>			
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->comPaymentValue ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mp_amount" id="mp_amount">
				<div class="dropdown ml-2">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"><?php echo $thisResource->comPaymentArt ?></button>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="#" onclick="selPay(this)"><?php echo $thisResource->comPayCash ?></a>
						<a class="dropdown-item" href="#" onclick="selPay(this)"><?php echo $thisResource->comPayCard ?></a>
						<a class="dropdown-item" href="#" onclick="selPay(this)"><?php echo $thisResource->comPayTransfer ?></a>
						<a class="dropdown-item" href="#" onclick="selPay(this)"><?php echo $thisResource->comPayCheck ?></a>
						<a class="dropdown-item" href="#" onclick="selPay(this)"><?php echo $thisResource->comPayOther ?></a>
						<a class="dropdown-item" href="#" onclick="selPay(this)"><?php echo $thisResource->comPayPayPal ?></a>
						<a class="dropdown-item" href="#" onclick="selPay(this)"><?php echo $thisResource->comPayPrepaid ?></a>
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
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->comTotal ?></span></div>
				<input type="text" class="form-control" name="mp_total" id="mp_total" readonly>
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->comPaid ?></span></div>
				<input type="text" class="form-control" name="mp_pays_total" id="mp_pays_total" readonly>
			</div>			
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->comDue ?></span></div>
				<input type="text" class="form-control" name="mp_pays_due" id="mp_pays_due" readonly>
			</div>	

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
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></span></button>
			<button type="button" class="btn btn-primary" id="btnPayOk" onclick="donePay()"><span class='fa fa-check'></span></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Payment -->

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
				<label id="bcScanInfo"><?php echo $thisResource->comBarcodeScanInfo ?></</label>
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

<div id="modalImageView" class="modal" onclick="this.style.display='none'">
    <div class="modal-content">
      <img id="modalImage" src="" class="center" style="width:90%; margin-left: auto; margin-right: auto;">
    </div>
 </div>
 
<!-- Modal: price -->
<div class="modal fade" id="modalPrice" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
	data-backdrop="static" data-keyboard="false" style="overflow-y:scroll">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-body">
		
<!-- top menu -->		
		<div class="row">
			<div class="p-1 col-6">
				<label id="mj_title" class="ml-2 mt-2" style="font-size: 20px; font-weight: bold"><?php echo $thisResource->comPriceSelection ?></label>
			</div>
			<div class="p-1 col-6" align="right">
				<button type="button" class="btn btn-secondary" onclick="closePrice()"><span class='fa fa-times'></span></button>
			</div>
		</div>
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:2px; width:100%">
		</div>
<!-- data -->
		<div class="row">
			<div class="p-1 col">
				<label ><?php echo $thisResource->comPriceHistory ?></label>
			</div>
		</div>
		<div class="row">
			<div class="p-1 col">
				<table id="tablePriceHist" class="table-sm" data-toggle="table" data-single-select="true" data-click-to-select="true" data-unique-id="id" >
					<thead class="thead-light">
						<tr>
						<th class="p-1" data-field="id" data-width="0" data-width-unit="%" data-visible="false">#</th>
						<th class="p-1" data-field="idx_time" data-width="50" data-width-unit="%"><?php echo $thisResource->comTime ?></th>
						<th class="p-1" data-field="idx_price" data-width="50" data-width-unit="%"><?php echo $thisResource->comPrice ?></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
		<br>

		<div class="row">
			<div class="p-1 col">
				<label ><?php echo $thisResource->comPriceSystem ?></label>
			</div>
		</div>
		<div class="row">
			<div class="p-1 col">
				<table id="tablePriceSys" class="table-sm" data-toggle="table" data-single-select="true" data-click-to-select="true" data-unique-id="id" >
					<thead class="thead-light">
						<tr>
						<th class="p-1" data-field="id" data-width="0" data-width-unit="%" data-visible="false">#</th>
						<th class="p-1" data-field="idx_note" data-width="50" data-width-unit="%"><?php echo $thisResource->comRemark ?></th>
						<th class="p-1" data-field="idx_price" data-width="50" data-width-unit="%"><?php echo $thisResource->comPrice ?></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
		
		</div>
		</div>
	</div>
</div> <!-- modalPrice -->

</form>	<!-- end of form -->
<iframe id="printf" name="printf" style="display: none;"></iframe>
<script src="js/ajax.js"></script>
<script src="js/autocomplete.js?202110211711"></script>
<script src="js/autocomplete_cust.js?<?= rand() ?>"></script>
<script src="js/modalCustomer.js?202110212239"></script>
<script src="js/html5-qrcode.min.js?202110212239"></script>
<script src="js/modalDel.js"></script>
<script>

var myRes = <?php echo json_encode($thisResource) ?>;

var oId, orderType = 0;
var $table = $("#myTable");
// Modals
var $modalOrderNewSearch = $("#modalOrderNewSearch");
var $modalOrderItem = $("#modalOrderItem");
var $modalOrderFee = $("#modalOrderFee"), $modalOrderPay = $("#modalOrderPay");
var $modalOrderDis = $("#modalOrderDis");
var $modalPrice = $("#modalPrice");
// Data
var order = {}, orderItems = [];
var itemCount = 0, itemIdCount = 0;
var thisItem = {}, thisType = 0;
var orderItemType = 0;
// Variant
var orderVariant = new Array(), variantCount = 0;
// autocpmplete
var a_icode = JSON.parse(localStorage.getItem("a_icode"));
var a_image = JSON.parse(localStorage.getItem("a_image"));
var a_ivariants =  new Array();
// customers
var customers = null;
var myCustomer = new Object();

/************************************************************************************
	INIT
************************************************************************************/
document.getElementById("loader").style.display = "block";
document.getElementById("myTable").style.display = "none";
$("#btnBarcode,#btnNew").hide();
getRequest("getInvVariantAll.php", loadInvVariantOK, loadInvVariantError);
getRequest("getCusts.php", getCustsYes, null);
function loadInvVariantOK(result) {
	document.getElementById("loader").style.display = "none";
	document.getElementById("myTable").style.display = "table";
	a_ivariants = result;
    $("#btnBarcode,#btnNew").show();
	initSys();
}
function loadInvVariantError(result) {
	alert("ERROR");
	var url = "<?php echo $backPhp; ?>";
	window.location.assign(url);
}

function getCustsYes(result) {
	customers = result;		
	acCustInitLists(customers);
	acCustLoadControl("k_name", document.getElementById("k_name"));
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
	order['fee5'] = "0.00";
	
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
	order['status'] = "0";
	
	myCustomer['k_id'] = "0";
}
$table.bootstrapTable({   
	formatNoMatches: function () {
         return myRes['msgErrNoData'];
    }
});

/************************************************************************************
	LOAD
************************************************************************************/
$(document).ready(function(){
	autocomplete_like(document.getElementById("ms_i_code"), a_icode, a_image);
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
	
	if (order['status'] == "10") {
		document.getElementById("btnSave").innerText = myRes['comAppConfirm'];
	}
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
		var calcPrice = orderItems[i]['price'];
		var priceStr = orderItems[i]['price'];
		var discount = orderItems[i]['discount'];
		if(discount > 0){
			calcPrice = (((100-discount) * orderItems[i]['price']) /100).toFixed(2);
			priceStr = "<a style='text-decoration-line: line-through;'>"+orderItems[i]['price']+"</a> "+calcPrice;
		}
		orderItems[i]['id'] = i;
		if (orderItems[i]['i_name'] != null)
			dataStr = "<a style='font-weight:bold;'>"+orderItems[i]['i_code']+"</a><br>"+"<a >"+orderItems[i]['i_name']+"</a>";
		else
			dataStr = "<a style='font-weight:bold;'>"+orderItems[i]['i_code']+"</a>";
		if(orderItems[i]['m_no'] != null)
			imgSrc = orderItems[i]['path']+"/"+orderItems[i]['i_id']+"_"+orderItems[i]['m_no']+".jpg";
		else
			imgSrc = "blank.jpg";
		if (orderItems[i]['m_no'] != null)
			altSrc = imgSrc;
		else
			altSrc = ""; 
		imgStr = "<img width='60' height='60' style='object-fit: cover' src='"+imgSrc+"' alt='"+altSrc+"' onclick='showImageView(this)'>";
		console.log(imgStr);
		if (orderItems[i]['unit'] == "1") {
			countStr = orderItems[i]['count'];
			orderItems[i]['real_count'] = orderItems[i]['count'];
		} else {
			countStr = orderItems[i]['count']+"<br>(x"+orderItems[i]['unit']+")";
			orderItems[i]['real_count'] = (parseInt(orderItems[i]['count'])*parseInt(orderItems[i]['unit'])).toString();
		}
		subtotal = parseInt(orderItems[i]['real_count'])*parseFloat(calcPrice);
		orderItems[i]['subtotal'] = subtotal.toFixed(2);
		rows.push({
			id: i,
			idx_image: imgStr,
			idx_data: dataStr,
			idx_count: countStr,
			idx_price: priceStr,
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
					parseFloat(order['fee1']) + parseFloat(order['fee2']) + parseFloat(order['fee3']) + parseFloat(order['fee4'] + parseFloat(order['fee5']));
	var sumPaid = parseFloat(order['pay_cash']) + parseFloat(order['pay_card']) + parseFloat(order['pay_bank']) + 
					parseFloat(order['pay_check']) + parseFloat(order['pay_other']) + parseFloat(order['pay_paypal']) + parseFloat(order['pay_vorkasse']);
	var sumDue = sumTotal - sumPaid;
	if(sumDue <= 0)
		sumDue = 0;

	order['total_sum'] = sumTotal.toFixed(2);
	order['paid_sum'] = sumPaid.toFixed(2);
	order['due'] = sumDue.toFixed(2);
	
	document.getElementById("itemCount").innerHTML = itemCount;
	document.getElementById("sumCount").innerHTML = order['count_sum'];
	document.getElementById("sumPrice").innerHTML = order['price_sum'];
	document.getElementById("sumTotal").innerHTML = order['total_sum'];
//	document.getElementById("sumPaid").innerHTML = order['paid_sum'];
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
function closeOrder() {
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
	// check internet //

	if(window.navigator.onLine == false){
		alert("网络已断，请链接网络后再保存!");
	}else{
		// get values from modalOrderItem
		var count = "0";
		if (mdvsVariant == null) {
			count = document.getElementById("m_count").value;
			if (count == "") {
				$('#m_count').focus();
				return;
			}
		} else
			count = getVCount();
		var price = document.getElementById("m_price").value;
        var discount = document.getElementById("m_discount").value;
		// Close the modalOrderItem
		$modalOrderItem.modal("toggle");
		// update or add
		if (thisType == 1)
			updateItem(count, price, discount);
		else
			addItem(count, price, discount);	
	}
}
function cancelItem() {
	$modalOrderItem.modal("toggle");
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
	searchCode();
}
// Search by i_code
function searchCode() {
	var code = document.getElementById("ms_i_code").value;
	if (code == "")
		return false;
	var ivar = findArray(a_ivariants, "i_code", code);
	if (ivar != null)
		searchCodeYes(ivar);
	else
		searchCodeNo(null);
}
// New thisItem
function newThisItem(inv) {
	thisItem = new Object();
	thisItem['o_id'] = oId;
	thisItem['i_id'] = inv['i_id'];
	thisItem['i_code'] = inv['i_code'];
	thisItem['i_name'] = inv['i_name'];
	thisItem['old_count'] = inv['count'];
	thisItem['discount'] = inv['discount'];
	thisItem['price'] = inv['price'];
	thisItem['cost'] = inv['cost'];		
	thisItem['m_no'] = inv['m_no'];
	thisItem['path'] = inv['path'];
	thisItem['unit'] = inv['unit'];	
	thisItem['count'] = "0";	
}
// Result back
function searchCodeYes(invs) {
	var inv = invs;
	// check if the product exists in the list
	if (findArray(orderItems, "i_id", inv['i_id']) != null) {
		alert(myRes['msgErrDupProduct']);
		$('#ms_i_code').trigger('focus');
		return;
	}	
	// close search window
	$modalOrderNewSearch.modal("toggle");		
	// thisItem
	thisType = 0;
	newThisItem(inv);
	// set values
	if(thisItem['discount'] > 0)
		document.getElementById("m_i_code").innerText = thisItem['i_code']+" (促销)";
	else
		document.getElementById("m_i_code").innerText = thisItem['i_code'];
	document.getElementById("m_i_name").value = thisItem['i_name'];	
	document.getElementById("m_old_count").value = thisItem['old_count'];	
	document.getElementById("m_unit_str").innerText = "x"+thisItem['unit'];	
	// set default value
    document.getElementById("m_price").value = thisItem['price'];
    document.getElementById("m_discount").value = "";
    $("#after_price").text("");
    if(thisItem['discount'] > 0){
        document.getElementById("m_discount").value = parseInt(thisItem['discount']);
        refreshSale();
    }

	document.getElementById("m_quantity").innerText = "0";
	// hide 'delete'
	document.getElementById("btnDel").style.display = "none";
	// search variant
	mdvsInitVariant();
	if (inv['variant'] != null)
		searchVariant(thisItem['i_id']);
	else
		searchVariantNo();
	// show modal
	orderItemType = 0;
	$modalOrderItem.modal();
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
// Error or no found
function searchCodeNo(invs) {
	alert(myRes['msgErrProductNoExist']);
	$('#ms_i_code').trigger('focus');
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
	showVariant(true);	
}
function searchVariantNo() {
	mdvsVariant = null;
	showVariant(false);	
}
// Add new item
function addItem(count, price, discount) {
	var countSum = parseInt(order['count_sum']);
	var priceSum = parseFloat(order['price_sum']);
	// Add new item to table
	thisItem['id'] = itemIdCount;
	thisItem['o_id'] = oId;
	thisItem['count'] = count;
	thisItem['price'] = price;
	thisItem['discount'] = discount;
	var priceStr = 	price;
	var calcPrice = price;
	if(discount > 0){
		calcPrice = (((100-discount) * price) /100).toFixed(2);
		priceStr = "<a style='text-decoration-line: line-through;'>"+price+"</a> "+calcPrice;
	}
	var subtotal = 0;
	if (thisItem['unit'] == "1")
		thisItem['real_count'] = thisItem['count'];
	else
		thisItem['real_count'] = (parseInt(thisItem['count'])*parseInt(thisItem['unit'])).toString();
	subtotal =  parseInt(thisItem['real_count'])*parseFloat(calcPrice);
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
	if(thisItem['m_no'] != null)
		imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+thisItem['m_no']+".jpg";
	else
		imgSrc = "blank.jpg";
	if (thisItem['m_no'] != null)
		altSrc = imgSrc;
	else
		altSrc = "";
	imgStr = "<img width='60' height='60' style='object-fit: cover' src='"+imgSrc+"' alt='"+altSrc+"' onclick='showImageView(this)'>";
	if (thisItem['unit'] == "1")
		countStr = thisItem['count'];
	else
		countStr = thisItem['count']+"<br>(x"+thisItem['unit']+")";
	rows.push({
		id: itemIdCount,
		idx_image: imgStr,
		idx_data: dataStr,
		idx_count: countStr,
		idx_price: priceStr,
		idx_subtotal: thisItem['subtotal']
	});
	$table.bootstrapTable('append', rows);
	// recalculate summary
	countSum += parseInt(thisItem['real_count']); 
	priceSum += subtotal;
	order['count_sum'] = countSum.toString();
	order['price_sum'] = priceSum.toFixed(2);	
	// increase counts
	itemCount++;
	itemIdCount++;
	displaySum();
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
	thisType = 1;
	////Set values
	document.getElementById("m_i_code").innerText = thisItem['i_code'];
	document.getElementById("m_i_name").value = thisItem['i_name'];
	document.getElementById("m_old_count").value = thisItem['old_count'];	
	document.getElementById("m_price").value = thisItem['price'];
	document.getElementById("m_discount").value = thisItem['discount'];
	document.getElementById("m_unit_str").innerText = "x"+thisItem['unit'];	
	refreshSale();
	// Show 'delete'
	document.getElementById("btnDel").style.display = "block";
	// Variant
	mdvsInitVariant();
	var v_idx = getVariantIndexById(thisItem['i_id']);
	if (v_idx >= 0) {
		mdvsVariant = orderVariant[v_idx];
		showVariant(true);
	} else {
		mdvsVariant = null;
		showVariant(false);
	}
	// Show modalOrderItem
	orderItemType = 1;
	$modalOrderItem.modal();
});
// Update item
function updateItem(count, price, discount) {
	// we do NOT update thisItem yet, because we need calculate sum and diff
	var calcPrice = price;
	var priceStr = price;
	if(discount > 0){
		calcPrice = ((price * (100-discount))/100).toFixed(2);
		priceStr = "<a style='text-decoration-line: line-through;'>"+price+"</a> "+calcPrice;
	}
	var real_count = "", subtotal = 0, countStr = "";
	if (thisItem['unit'] == "1") {
		countStr = count;
		real_count = count;
	}
	else {
		countStr = count+"<br>(x"+thisItem['unit']+")";
		real_count = (parseInt(count)*parseInt(thisItem['unit'])).toString();		
	}
	subtotal =  parseInt(real_count)*parseFloat(calcPrice);
	// update table
	$table.bootstrapTable('updateCellByUniqueId', {
        id: thisItem['id'],
        field: 'idx_count',
        value: countStr
     })
	 $table.bootstrapTable('updateCellByUniqueId', {
        id: thisItem['id'],
        field: 'idx_price',
        value: priceStr
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
	item['discount'] = discount;
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
	thisItem['discount'] = discount;
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
		orderVariant.splice(index, 1);
		
		variantCount = variantCount - 1;
	}		
}
/************************************************************************
	DATABASE FUNCTIONS
************************************************************************/
function prepareOrder() {
	if (order['k_id'] == "")
		order['k_id'] = "0";
	if (order['status'] == "10")
		order['status'] = "11";
		
	var profit = 0;
	for (var i=0; i<orderItems.length; i++) {
		var calcPrice = orderItems[i]['price'];
		if(orderItems[i]['discount'] > 0) calcPrice = ((orderItems[i]['price']*(100 - orderItems[i]['discount'])) / 100).toFixed(2);
		if (order['discount_rate'])
			profit += parseInt(orderItems[i]['real_count']) * ((1-parseFloat(order['discount_rate'])/100)*parseFloat(calcPrice)  - parseFloat(orderItems[i]['cost']));
		else
			profit += parseInt(orderItems[i]['real_count']) * (parseFloat(calcPrice)  - parseFloat(orderItems[i]['cost']));
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
         return myRes['msgErrNoData'];
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
				case "pay_card": type_value = myRes["comPayCard"]; break;
				case "pay_bank": type_value = myRes["comPayTransfer"]; break;
				case "pay_check": type_value = myRes["comPayCheck"]; break;
				case "pay_other": type_value = myRes["comPayOther"]; break;
				case "pay_paypal": type_value = myRes["comPayPayPal"]; break;
				case "pay_vorkasse": type_value = myRes["comPayPrepaid"]; break;
				default: type_value = myRes["comPayCash"];
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
	if(order['isPayed'] == 1){
		document.getElementById("mdp_isPayed").checked = true;
		document.getElementById("mdp_paidDatum").value = order['paidDatum'];
		if($('#mdp_paidDatum').val()=='') $('#mdp_paidDatum').val(currentDate(2));
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
		case myRes["comPayCard"]: type = "pay_card"; break;
		case myRes["comPayTransfer"]: type = "pay_bank"; break;
		case myRes["comPayCheck"]: type = "pay_check"; break;
		case myRes["comPayOther"]: type = "pay_other"; break;
		case myRes["comPayPayPal"]: type = "pay_paypal"; break;
		case myRes["comPayPrepaid"]: type = "pay_vorkasse"; break;
		default: type = "pay_cash";
	}
	for (var i=0; i<7; i++) {
		if (type == pays[i] && notZero(pays_data[pays[i]]))
			break;
	}
	if (i < 7) {
		alert(myRes['msgErrDupData']);
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
	order['isPayed'] = -1;
	order['paidDatum'] = "";
	if(document.getElementById("mdp_isPayed").checked == true){
		order['isPayed'] = 1;
		order['paidDatum'] = document.getElementById("mdp_paidDatum").value;
	}
	displaySum();
}
/************************************************************************
	CANCEL ORDER
************************************************************************/
function cancelDone(result) {
	var url = "<?php echo $backPhp; ?>";
	window.location.assign(url);
}
function cancelError(result) {

}
function cancelOrder() {
	if (!confirm(myRes['msgConfirmCancelOrder'])) {
		return;
	}
	showDelModal(delDbOrder);
	//delDbOrder();
}
function delDbOrder() {
	var link = "postOrderDel.php";
	var form = new FormData();
	form.append('order', JSON.stringify(order));
	if(orderItems && orderItems.length > 0)
		form.append('orderitems', JSON.stringify(orderItems));
	if(orderVariant && orderVariant.length > 0)
		form.append('ordervariants', JSON.stringify(orderVariant));
	postRequest(link, form, cancelDone, cancelError);	
}
/************************************************************************
	INVOICE
************************************************************************/
function invoiceOk(result) {
	alert(myRes['msgInvoiceOk']);
}

function invoiceError(result) {
	alert(myRes['msgInvoiceErr']);
}

function invoiceExist(result) {
	alert(myRes['msgInvoiceDup']);
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
	FUNCTIONS
************************************************************************/
// Prevent 'enter' key to submit
$('form input').keydown(function (e) { 
    if (e.keyCode == 13) {
        e.preventDefault();
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
	PRINT
************************************************************************/
var printResDE = 
		{'customer':"Empfänger", 'order':"Bestellung", 'orderNo':"Nr.", 'date':"Datum", 'custNo':"Kunden Nr.",
		'item':"Artikel Nr. und Bezeichnung", 'quantity':"Anzahl", 'price':"Enzelpreis", 'subtotal':"Nettobetrag",
		'totalQuantity': "Gesamtmenge", 'pieces':"Stück", 'sum':"SUMME", 'total':"TOTAL", 'deliveryNote':"Lieferschein"};
var printResIT = 
		{'customer':"Destinatario", 'order':"ORD", 'orderNo':"NE", 'date':"Data", 'custNo':"Numero Cliente",
		'item':"Codice e Descrizione", 'quantity':"Quantita", 'price':"Prezzo", 'subtotal':"Totale",
		'totalQuantity': "Totale", 'pieces':"Pezzi", 'sum':"IMPORTO", 'total':"TOTALE", 'deliveryNote':"D.D.T."};
var printResEN = 
		{'customer':"Customer", 'order':"Order", 'orderNo':"No.", 'date':"Date", 'custNo':"Customer No.",
		'item':"Item No. & Description", 'quantity':"Quantity", 'price':"Price", 'subtotal':"Subtotal",
		'totalQuantity': "Total Quantity", 'pieces':"Pieces", 'sum':"Sum.", 'total':"Total", 'deliveryNote':"Delivery Note"};
var printType = 0;

function printLiefer() {
	printType = 1;
	printForm();
}
function printOrder() {
	var version = <?php echo json_encode($_SESSION['version']) ?>;
	if (version == "100") {
		printForm100();
		return;
	}
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
	var printRes = printResDE;
	if (company['country'].toLowerCase() == "italy")
		printRes = printResIT;
	else if (company['country'].toLowerCase() == "germany" || company['country'].toLowerCase() == "deutschland" || company['country'] == "")
		printRes = printResDE;
	else
		printRes = printResEN;
		
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
		output += '<b style="font-size:12px;">'+printRes['customer']+'</b>';
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
		output += '<td><h3>'+printRes['deliveryNote']+'</h3></td>';
	else
		output += '<td><h3>'+printRes['order']+'</h3></td>';
	output += '<td style="border-left:1px solid #808080">'+printRes['orderNo']+':&nbsp;'+oId+'</td>';
	if (order['date'] == null || order['date'] == "")
		output += '<td style="border-left:1px solid #808080">'+printRes['date']+':&nbsp;'+currentDate(0)+'</td>';
	else
		output += '<td style="border-left:1px solid #808080">'+printRes['date']+':&nbsp;'+convertDate(order['date'],0)+'</td>';
	if (myCustomer != null && myCustomer['k_id'] != "0")
		output += '<td style="border-left:1px solid #808080">'+printRes['custNo']+':&nbsp;'+myCustomer["k_code"]+'</td>';
	else
		output += '<td style="border-left:1px solid #808080">'+printRes['custNo']+':&nbsp;</td>';
	output += '</tr></table>';
	output += '<br>';
	// Table
	output += '<table width="100%" style="border:1px solid #808080;" cellpadding="2" cellspacing="0"><thead>';
	output += '<tr style="font-size:12px">';
	output += '<th style="border-bottom:1px solid #808080;" align="center" >'+printRes['item']+'</th>';
	output += '<th style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="center">'+printRes['quantity']+'</th>';
	output += '<th style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="right">'+printRes['price']+'</th>';
	output += '<th style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="right">'+printRes['subtotal']+'</th>';
	output += '</tr></thead><tbody>';
	for (i=0; i<itemCount; i++) {
		var code = "";
		var rabatt = "";
		var priceStr = orderItems[i]['price'];
		if(orderItems[i]['discount'] > 0){
			rabatt = " (Rabatt: "+parseFloat(orderItems[i]['discount']).toFixed(0)+"%)";
			priceStr = "<a style='text-decoration-line: line-through;'>"+orderItems[i]['price']+"</a> "+(((100-orderItems[i]['discount']) * orderItems[i]['price']) /100).toFixed(2);
		}
		if (orderItems[i]['position'] != null && orderItems[i]['position'] != "")
			code += orderItems[i]['position']+'&nbsp;';
		if (orderItems[i]['i_name'] != null && orderItems[i]['i_name'] != "")
			code += orderItems[i]['i_name']+'&nbsp;';
		//code += 'ART.'+orderItems[i]['i_code'];
		code += orderItems[i]['i_code'];
		if (orderItems[i]['color'] != null && orderItems[i]['color'] != "")
			code += '&nbsp;'+orderItems[i]['color'];
		output += '<tr style="font-size:12px; font-family:Arial">';
		output += '<td style="padding:1px;">'+'&nbsp;&nbsp;'+code+rabatt+'</td>';
		if (orderItems[i]['unit'] == "1")
			output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+orderItems[i]['count']+'&nbsp;</td>';	
		else
			output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+orderItems[i]['count']+'&nbsp;(x'+orderItems[i]['unit']+')&nbsp;</td>';
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+priceStr+'</td>';
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+orderItems[i]['subtotal']+'</td>';
		output += '</tr>';
	}
	output += '<tr><td align="center" style="font-size:12px; font-family:Arial; border-top:1px solid #808080;" colspan="5">==='+printRes['totalQuantity']+':&nbsp;'+order['count_sum']+'&nbsp;'+printRes['pieces']+'===</td></tr>';	
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
	for (i=0; i<maxCount-itemCount; i++) {
		output += '<tr><td style="padding:1px; font-size:12px; font-family:Arial" colspan="5">&nbsp;</td></tr>';
	}
	// Summary
	output += '<tr align="right" style="padding:1px; font-size:14px;">';
	output += '<td colspan="3" style="border-top:1px solid #808080;">'+printRes['sum']+':&nbsp;</td><td style="border-top:1px solid #808080;">'+order['price_sum']+'&nbsp;&euro;&nbsp;&nbsp;</td>';
	output += '</tr>';
	output += '<tr align="right" style="padding:1px; font-size:14px;">';
	output += '<td colspan="3">Sk(%):&nbsp;</td><td>'+order['discount_rate']+'%&nbsp;&nbsp;</td>';
	output += '</tr>';
	output += '<tr align="right" style="padding:1px; font-size:18px; font-weight:bold;">';
	output += '<td colspan="3">'+printRes['total']+':&nbsp;</td><td>'+order['total_sum']+'&nbsp;&euro;&nbsp;&nbsp;</td>';
	output += '</tr>';
	output += '</tbody></table>';
	
	output += '</body></html>'; 
	
	if (/Android/i.test(navigator.userAgent)) {
		openPrintDialogue(output);
	} else {
		//var mywindow = window.open();
		var mywindow = window.frames["printf"];
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
		if(thisVar[i]['m_no'] != null)
			imgSrc = thisOrderItem['path']+"/"+thisID+"_"+thisVar[i]['m_no']+".jpg";
		else
			imgSrc = "blank.jpg";
		if (thisVar[i]['m_no'] != null)
			altSrc = imgSrc;
		else
			altSrc = "";
		imgStr = "<img width='60' height='60' style='object-fit: cover' src='"+imgSrc+"' alt='"+altSrc+"' onclick='showImageView(this)'>";
		if (thisOrderItem['unit'] == 1) {
			countStr = thisVar[i]['count'];
		} else {
			countStr = thisVar[i]['count']+" (x"+thisOrderItem['unit']+")";
		}
        if(thisVar[i]['size'] == null) thisVar[i]['size'] = "";
		html += '<tr><td style="text-align:right">'+countStr+'</td><td  style="text-align:center">'+imgStr+'</td><td>'+thisVar[i]['variant']+" "+thisVar[i]['size']+'</td></tr>';
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


/************************************************************************
	VARIANT SELECT
************************************************************************/
var mdvsVariant = null, mdvsVariantCount = 0;
var mdvsVariantMax = 100;
function showVariant(option) {
	if (option) {
		document.getElementById("containerNoVariant").style.display = "none";
		document.getElementById("containerWithVariant").style.display = "block";
		mdvsShowVariant();
	} else {
		document.getElementById("containerNoVariant").style.display = "block";
		document.getElementById("containerWithVariant").style.display = "none";
		if (thisItem['count'] == "0")
			document.getElementById("m_count").value = "";
		else
			document.getElementById("m_count").value = thisItem['count'];
		// src for m_img
		if (thisItem['m_no'] == null) {
			document.getElementById("m_img").src = "blank.jpg";
			document.getElementById("m_img").alt = "";
		} else {
			var imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+thisItem['m_no']+".jpg";
			document.getElementById("m_img").src = imgSrc;
			document.getElementById("m_img").alt = imgSrc;
		}
		document.getElementById("m_quantity").innerText = thisItem['count'];
	}
}
function mdvsInitVariant() {	
	for (var i=0; i<mdvsVariantMax; i++) {			
		document.getElementById("m_vimg"+i).src = "blank.jpg";
		document.getElementById("m_vimg"+i).alt = "";
		document.getElementById("m_variant"+i).innerText = "";
		document.getElementById("m_vcount"+i).value = "";
		$('#m_varitem'+i).hide();
	}
}
function mdvsShowVariant() {
	mdvsVariantCount = mdvsVariant.length;
	var vquantity = 0, vcount = 0;
	for (var i=0; i<mdvsVariantCount; i++) {
		mdvsVariant[i]['o_id'] = thisItem['o_id'];
		if (mdvsVariant[i]['m_no'] != null) {
			var imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+mdvsVariant[i]['m_no']+".jpg";
			document.getElementById("m_vimg"+i).src = imgSrc;
			document.getElementById("m_vimg"+i).alt = imgSrc;
		}
        if(mdvsVariant[i]['size'] == null) mdvsVariant[i]['size'] = "";
		document.getElementById("m_variant"+i).innerHTML = "<b>"+mdvsVariant[i]['variant']+" "+mdvsVariant[i]['size']+"</b><br>"+mdvsVariant[i]['amount'];
		if (mdvsVariant[i]['count'] == null || parseInt(mdvsVariant[i]['count']) == "") {
			vcount = 0;
			document.getElementById("m_vcount"+i).value = "";
		}
		else {
			vcount = parseInt(mdvsVariant[i]['count']);
			document.getElementById("m_vcount"+i).value = vcount.toString();
		}
		$('#m_varitem'+i).show();
		vquantity += vcount;
	}
	document.getElementById("m_quantity").innerText = vquantity.toString();
}
function countVAdd(e) {
	var id = $(e).attr("id");
	var index = id.replace("m_vadd", "");
	var vcountId = "m_vcount" + index;
	var v = document.getElementById(vcountId).value;
	if (v == "")
		v = "0";
	var d = parseInt(v);
	d++;
	document.getElementById(vcountId).value = d.toString();
	updateVQuantity();
}
function countVMinus(e) {
	var id = $(e).attr("id");
	var index = id.replace("m_vminus", "");
	var vcountId = "m_vcount" + index;
	var v = document.getElementById(vcountId).value;
	if (v == "")
		v = "0";
	var d = parseInt(v);
	d--;
	document.getElementById(vcountId).value = d.toString();
	updateVQuantity();
}
function countVInput(e) {
	updateVQuantity();
}
function updateVQuantity() {
	var vquantity = 0, vcount = 0;
	for (var i=0; i<mdvsVariantCount; i++) {
		vcount = document.getElementById("m_vcount"+i).value;
		if (vcount == "")
			vcount = "0";			
		vquantity += parseInt(vcount);
	}
	document.getElementById("m_quantity").innerText = vquantity.toString();
}
function getVCount() {
	var vquantity = 0, vcount = 0, vdiff = 0;
	for (var i=0; i<mdvsVariantCount; i++) {
		vcount = document.getElementById("m_vcount"+i).value;
		if (vcount == "")
			vcount = "0";		
		vdiff = parseInt(vcount) - parseInt(mdvsVariant[i]['count']);
		mdvsVariant[i]['count'] = parseInt(vcount);
		mdvsVariant[i]['count_diff'] = vdiff;
		vquantity += parseInt(vcount);
	}
	return vquantity;
}
function mdShowImageView(e) {
	var altSrc = $(e).attr("alt");
	if (altSrc == "")
		return;
	document.getElementById("m_imageZoom").src = altSrc;
	document.getElementById("m_imageView").style.display = "block";
}
/************************************************************************
	NO VARIANT
************************************************************************/
function countAdd() {
	var v = document.getElementById("m_count").value;
	if (v == "")
		v = "0";
	var d = parseInt(v);
	d++;
	document.getElementById("m_count").value = d.toString();
	updateQuantity(d);
}
function countMinus() {
	var v = document.getElementById("m_count").value;
	if (v == "")
		v = "0";
	var d = parseInt(v);
	d--;
	document.getElementById("m_count").value = d.toString();
	updateQuantity(d);	
}
function countInput() {
	var count = document.getElementById("m_count").value;
	if (count == "")
		count = "0";
	updateQuantity(parseInt(count));
}
function updateQuantity(d) {
	document.getElementById("m_quantity").innerText = d.toString();
}
/************************************************************************
	BARCODE
************************************************************************/
var bcList = [];
var bcInv = null;
var bcCount = 0, bcValue = 0;
$modalBarcode = $('#modalBarcode');
var bcBody = document.getElementById("bcBody");
var bcRecordHeight;

// show modalBarcode
function showBarcode() {
	mdbcClearAll();
	$modalBarcode.modal();
	bcDisplaySum();
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
	var sumStr =  myRes['comProduct'] + ": " + bcList.length  + "  " + myRes['comQuantity'] + ": " + bcCount.toString() + "  " + myRes['comValue'] + ": " + bcValue.toFixed(2);
	document.getElementById("mdbcTitle").innerHTML = sumStr;
	document.getElementById("mdbcTitle1").innerHTML = sumStr;
}
// barcode input
var mdbcCode1 = "";
var keytime = 0;
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
		document.getElementById("bcScanInfo").innerText = myRes['comBarcodeYes'];
		playBeep(1);
	} else {
		document.getElementById("bcScanInfo").style.color = "white";
		document.getElementById("bcScanInfo").style.backgroundColor = "red";
		document.getElementById("bcScanInfo").innerText = myRes['comBarcodeNo'];
		playBeep(0);
	}		
}
// scroll body to bottom
function bcScrollBottom() {
	var bcModalBody = document.getElementById("bcModalBody");
//	bcModalBody.scrollTop += bcRecordHeight;
	bcModalBody.scrollTop = bcModalBody.scrollHeight - bcModalBody.clientHeight;
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
// update record
function bcUpdateRecord(listItem) {
	var count = 0;
	for (var i=0; i<listItem['subitems'].length; i++) {
		count += listItem['subitems'][i]['amount'];
	}
	listItem['count'] = count;
	var id = "bc_count_"+listItem['i_id'];	
	document.getElementById(id).innerText = count;	
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
// create hr
function bcCreateHr(col) {
	var rowHr = document.createElement("div");
	rowHr.classList.add("row");
	col.appendChild(rowHr);
	var hr = document.createElement("hr");
	hr.style = "border:1px solid lightgrey; margin:2px; width:100%";
	rowHr.appendChild(hr);
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
// close modal
function bcCancel() {
	if (bcList.length > 0) {
		if (!confirm(myRes['msgConfirmQuit']))
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
		index = findArrayIndex(orderItems, "i_id", bcList[i]['i_id']);
		if (index < 0) {
			newThisItem(bcList[i]['inv']);
			bcGetVariant(bcList[i]);
			bcAddItem(bcList[i]);
		} else {
			thisItem = orderItems[index];
			var v_index = getVariantIndexById(bcList[i]['i_id']);
			if (v_index >= 0)
				mdvsVariant = orderVariant[v_index];
			else
				mdvsVariant = null;
			bcUpdateItem(bcList[i]);
		}
	}
}
function bcGetVariant(item) {
	var result = new Array();
	if (item['inv']['variant'] != null) {
		for (i=0; i<a_ivariants.length; i++) {
			if (a_ivariants[i]['i_id'] == item['i_id']) {
				var vart = new Object();
				vart['o_id'] = oId;
				vart['i_id'] = item['i_id'];
				vart['iv_id'] = a_ivariants[i]['iv_id'];
				vart['variant'] = a_ivariants[i]['variant'];
				vart['amount'] = a_ivariants[i]['amount'];
				vart['barcode'] = a_ivariants[i]['barcode'];
				vart['m_no'] = a_ivariants[i]['im_no'];
				vart['count'] = "0";
				vart['count_diff'] = "0";
				result.push(vart);
			}
		}
		mdvsVariant = result;
	} else {
		mdvsVariant = null;
	}
}
function bcAddItem(item) {
	var count = item['count'].toString();
	var price = item['price'];
	var discount = item['discount'];
	
	if (item['inv']['variant'] != null) {
		for (var i=0; i<mdvsVariant.length; i++) {
			for (var j=0; j<item['subitems'].length; j++) {
				if (mdvsVariant[i]['iv_id'] == item['subitems'][j]['iv_id']) {
					mdvsVariant[i]['count'] = item['subitems'][j]['amount'].toString();
					mdvsVariant[i]['count_diff'] = item['subitems'][j]['amount'].toString();
				}
			}
		}
	} 
		
	addItem(count, price, discount);
}
function bcUpdateItem(item) {
	var count = item['count'].toString();
	var price = item['price'];
	var discount = item['discount'];
	
	var itemcount = (parseInt(count) + parseInt(thisItem['count'])).toString();
	if (item['inv']['variant'] != null) {
		for (var i=0; i<mdvsVariant.length; i++) {
			for (var j=0; j<item['subitems'].length; j++) {
				if (mdvsVariant[i]['iv_id'] == item['subitems'][j]['iv_id']) {
					mdvsVariant[i]['count'] = (item['subitems'][j]['amount'] + parseInt(mdvsVariant[i]['count'])).toString();
					mdvsVariant[i]['count_diff'] = item['subitems'][j]['amount'].toString(); 
				} 
			}
		}
	}

	updateItem(itemcount, price, discount);
}
/************************************************************************
	CUSTOMER
************************************************************************/
function acCustDone(inp, id) {
	if ($modalCust.is(':visible'))
		doneAutocompCust(inp, id);
	else {
		myCustomer = getCustById(id);
		updateCust(myCustomer);
	}
}
function getCustById(id) {
	for (var i=0; i<customers.length; i++) {
		if (customers[i]['k_id'] == id)
			return customers[i];
	}
	return null;
}
function showCust() {
	mkShow(myCustomer);
}
function mkSaveCust(customer) {
	updateCust(customer);
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

/************************************************************************
	PRICE
************************************************************************/
var priceHist = [];
var priceSys = [];

var $tablePriceHist = $("#tablePriceHist");
$tablePriceHist.bootstrapTable({   
	formatNoMatches: function () {
         return myRes['sysMsgNoRecord'];
    }
});

var $tablePriceSys = $("#tablePriceSys");
$tablePriceSys.bootstrapTable({   
	formatNoMatches: function () {
         return myRes['sysMsgNoRecord'];
    }
});

function showPrice() {
	var k_id = myCustomer['k_id'] == "0" ? -1 : myCustomer['k_id']
	var geturl1 = "getOrderPrice.php?k_id="+k_id+"&i_id="+thisItem['i_id'];
	getRequest(geturl1, getPriceHistYes, getPriceHistNo);
	var geturl2 = "getInvPrice.php?i_id="+thisItem['i_id'];
	getRequest(geturl2, getPriceSysYes, getPriceSysNo);
	
	$modalPrice.modal();
}

function closePrice() {
	$modalPrice.modal("toggle");
}

function getPriceHistYes(result) {
	priceHist = result;
	var rows = [];
	for (var i=0; i<priceHist.length; i++) {
		rows.push({
			id: priceHist[i]['oi_id'],
			idx_time: priceHist[i]['date'].substr(0,10),
			idx_price: priceHist[i]['price']
		});
	}
	$tablePriceHist.bootstrapTable('removeAll');
	$tablePriceHist.bootstrapTable('append', rows);
}

function getPriceHistNo(result) {
	
}

$tablePriceHist.on('click-row.bs.table', function (e, row, $element) {
	var price = row.idx_price;
	document.getElementById("m_price").value = price;
	$modalPrice.modal("toggle");
});
	
function getPriceSysYes(result) {
	priceSys = result;
	var rows = [];
	for (var i=0; i<priceSys.length; i++) {
		rows.push({
			id: priceSys[i]['ip_id'],
			idx_note: priceSys[i]['note'],
			idx_price: priceSys[i]['price']
		});
	}
	$tablePriceSys.bootstrapTable('removeAll');
	$tablePriceSys.bootstrapTable('append', rows);
}

function getPriceSysNo(result) {
	
}

$tablePriceSys.on('click-row.bs.table', function (e, row, $element) {
	var price = row.idx_price;
	document.getElementById("m_price").value = price;
	$modalPrice.modal("toggle");
});

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
function findArrayIndex(array, key, value) {
	for (var i=0; i<array.length; i++) {
		if (array[i][key] == value) {
			return i;
			break;
		}
	}
	return -1;
}

/************************************************************************
	PRINT - ENGLISH/SMALL
************************************************************************/
function printForm100() {
	var company = <?php echo json_encode($_SESSION['myCompany']) ?>;
	var i = 0, totalCount = 0, totalPrice = 0.00;
	
	var header = '<html><head><style type="text/css" media="print">@page { size:auto; margin:1cm 1cm 1cm 1cm; }\</style></head><body>';
	var footer = '</body></html>';	
	var printout = header;
	var output = "";
	// title
	output += '<div style="border-bottom:1px solid grey" align="center"><b style="font-size:20px">Receipt</b></div>';
	output += '<div align="center">'+company["c_name"]+'</div>';
	// header
	output += '<table width="100%" border="0" cellpadding="2" cellspacing="0"><tr>';
		// customer
		output += '<td width="70%">';
		output += '<a>'+myCustomer["k_name"]+'</a><br><a>'+'Tel: '+(myCustomer["tel"] == null? "" : myCustomer["tel"])+'</a>';
		output += '</td>';
		// No. & date
		output += '<td width="30%">';
		output += '<table width="100%" border="0" cellpadding="2" cellspacing="0">';
		output += '<tr><td><b>No.:</b></td><td align="right"><b>'+oId+'</b></td></tr>';
		if (order['date'] == null || order['date'] == "")
			output += '<tr><td><a>Date:</a></td><td align="right"><a>'+currentDate(0)+'</a></td></tr>';
		else
			output += '<tr><td><a>Date:</a></td><td align="right"><a>'+order['date'].substr(0, 10)+'</a></td></tr>';
		output += '</table>';
		output += '</td>';
	output += '</tr></table>';
	// items
	output += '<table width="100%" border="0" cellpadding="2" cellspacing="0">';
	output += '<thead><tr style="font-size:12px; font-weight:bold">';
	output += '<th style="border-bottom:1px solid grey;" align="left" >Code</th>';
	output += '<th style="border-bottom:1px solid grey;" align="left" >Description</th>';
	output += '<th style="border-bottom:1px solid grey;" align="left" >Quantity</th>';
	output += '<th style="border-bottom:1px solid grey;" align="left" >Price</th>';
	output += '<th style="border-bottom:1px solid grey;" align="left" >Total</th>';
	output += '</tr></thead>';
	output += '<tbody>';
	for (i=0; i<orderItems.length; i++) {
		output += '<tr style="font-size:12px; font-family:Arial">';
		output += '<td style="padding:1px; border-bottom:1px solid grey;">'+orderItems[i]['i_code']+'</td>';
		output += '<td style="padding:1px; border-bottom:1px solid grey;">'+(orderItems[i]['i_name'] == null? "" : orderItems[i]['i_name'])+'</td>';
		output += '<td style="padding:1px; border-bottom:1px solid grey;">'+orderItems[i]['count']+'</td>';
		output += '<td style="padding:1px; border-bottom:1px solid grey;">'+orderItems[i]['price']+'</td>';
		output += '<td style="padding:1px; border-bottom:1px solid grey;">'+orderItems[i]['subtotal']+'</td>';
		output += '</tr>';
		totalCount += parseInt(orderItems[i]['count']);
		totalPrice += parseFloat(orderItems[i]['subtotal']);
	}
	output += '</tbody>';
	output += '</table>';
	// spacing
	var maxCount = 20;
	for (i=0; i<maxCount-orderItems.length; i++) {
		output += '<br>';
	}
	// summary
	output += '<table width="100%" border="1" cellpadding="5" cellspacing="2">';
	output += '<tr><td width="50%">Total Quantity: '+totalCount+' pz</td><td width="50%" align="right"><b style="font-size:20px">Total:   '+totalPrice.toFixed(2)+'</td></tr>';
	output += '<tr><td colspan="2">Note:  '+order['fee2']+'</td></tr>';
	output += '</table>';
	
	printout += header + output;
	
	printout += footer;
	
	//var mywindow = window.open();
	var mywindow = window.frames["printf"];
    mywindow.document.write(printout);
	mywindow.document.close();
	mywindow.focus();
	if (/Android|iPhone|iPad/i.test(navigator.userAgent)) {
		mywindow.print();
		mywindow.onafterprint = function () {
			mywindow.close();
		} 
	}else {
		mywindow.onload = function () {
			mywindow.print();
			mywindow.close();
		}
	}
}
</script>

</body>
</html>
