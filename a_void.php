<?php 
/************************************************************************************
	File:		a_void.php
	Purpose:	void invoice
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
$myCompany= dbQueryCompany();
$backPhp = 'a_voidmgt.php';

// Start a new order
if($_SERVER['REQUEST_METHOD'] == 'GET') {
	if(isset($_GET['back'])) {
		$backPhp = $_GET['back'].'.php';
	}
	if (isset($_GET['r_id'])) {
		$myId = $_GET['r_id'];
	}
}

?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>	
	<title>EUIMS - Void Invoice</title>
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
	
	<form action="" method="post">
	<div class="container">		
<!-- order data header -->			
	<div class="row"> 
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-6" align="left">
			<div class="input-group-prepend"><span class="input-group-text">发票号</span></div>
			<input type="text" class="form-control" name="invoice_no" id="invoice_no" value="" readonly>	
			<div class="input-group-prepend ml-2"><span class="input-group-text">客户</span></div>
			<input type="text" class="form-control" name="k_name" id="k_name" value="" readonly>
		</div>
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-6" align="right">	
			<button type="button" class="btn btn-secondary" id="btnOptions" onclick="showOptions()">选项</button>
			<button type="button" class="btn btn-success" id="btnPrint" onclick="printOrder()">打印</button>	
			<button type="button" class="btn btn-info" onclick="invoiceOrder()">撤回</button>
			<button type="button" class="btn btn-secondary" id="btnClose" onclick="closeInvoice()">关闭</button>		
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
		</div>
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-3" align="left">
			<div class="input-group-prepend"><span class="input-group-text">时间</span></div>
			<input type="text" class="form-control" id="in_date" name="in_date" readonly>
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
						<input type="number" min="0" step="1" class="form-control" name="m_count" id="m_count" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">价格</span></div>
						<input type="number" min="0" step="0.01" class="form-control" name="m_price" id="m_price" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">折扣%</span></div>
						<input type="number" min="0" step="1" max="100" class="form-control" name="m_discount" id="m_discount" readonly style="background-color:white">
						<div class="input-group-append"><span class="input-group-text" id="after_price"></span></div>
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">备注</span></div>
						<input type="text" class="form-control" name="m_note" id="m_note" readonly style="background-color:white">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-12 p-1" align="right">
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
								<?php if(is_array($myArts))for($i=0; $i<count($myArts); $i++) {
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
						<input type="number" min="0" step="0.01" class="form-control" name="mdn_price" id="mdn_price">		
					</div>
				</div>
			</div>
			<div class="row">
				<div class="p-1 col-12" align="right">
					<button type="button" class="btn btn-primary" id="mdnBtnDone" onclick="mdnDone()"><span class='fa fa-check'></button>
				</div>
			</div>
		</div>
		</div>
	</div>
</div> <!-- end of modalNewItem -->

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
			<a>添加备注<br></a>
			<textarea class="form-control" rows="2" id="mdopNote"></textarea>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></button>
			<button type="button" class="btn btn-primary" id="mdopBtnOk" onclick="doneOptions()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Options -->

</div> <!-- end of container -->
</form>	<!-- end of form -->
<iframe id="printf" name="printf" style="display: none;"></iframe>
<script src="js/sysfunc.js?01201058"></script>
<script src="js/ajax.js"></script>
<script src="js/autocomplete.js?202109131930"></script>	
<script src="js/aOptions.js?202109151712"></script>

<script>

var rId = <?php echo $myId ?>;
var company = <?php echo json_encode($myCompany) ?>;

var myCustomer = new Object();
var $table = $("#myTable");
// Modals
var $modalOrderItem = $("#modalOrderItem"), $modalNewItem = $("#modalNewItem");
var $modalOptions = $("#modalOptions");
// Data
var order = {}, orderItems = [];
var itemCount = 0, itemIdCount = 0;
var thisItem = {};
var myArts;
// Options
initAOptions();
var opPrintOnePage = false;

 // Init variables
order['r_id'] = rId;

/***********************************************************************
	ENTER
************************************************************************/
$('form input').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
		return false;
    }
});

$(document).ready(function(){
	getRequest("getInvoiceVoidById.php?r_id="+rId, loadOrder, loadError);	

})

// Load Order
function loadError(result) {
//	alert("读取发票过程中出现错误");
}
function loadOrder(result) { 
	order = result;
	
	getRequest("getInvoiceVoidItemsById.php?r_id="+rId, loadOrderItems, loadError);
	
	if (order['k_id'] != "" && order['k_id'] != "0")
		getRequest("getCustById.php?k_id="+order['k_id'], loadCust, loadError);	
	else
		myCustomer['k_id'] = "0";
	
	if (order['invoice_no'] != "0")
		document.getElementById("invoice_no").value = order['invoice_no'];

	document.getElementById("in_date").value = convertDate(order['date']);
	displaySum();
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
	VIEW/EDIT ITEM
************************************************************************/
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
		refreshSale();
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
function refreshSale(){
    var price = $("#m_price").val();
    var discount = $("#m_discount").val();
    if(discount != ""){
        $("#after_price").text((price*((100-discount)/100)).toFixed(2));
    }else{
        $("#after_price").text(price);
    }
}
function mdoDone() {
	$modalOrderItem.modal("toggle");
}

function mdnDone() {
	$modalNewItem.modal("toggle");
}

/************************************************************************
	CUSTOMER
************************************************************************/
// View customer
function showCust() {
	mkInit(myCustomer);
	$modalCust.modal();	

}
// Done modalCust
function mkSaveCust(customer) {

}

/************************************************************************
	CLOSE INVOICE
************************************************************************/
function closeInvoice() {
	var url = "<?php echo $backPhp; ?>";
	window.location.assign(url);
}

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
		
	$modalOptions.modal("toggle");
}

/************************************************************************
	PRINT
************************************************************************/
function getInNoYes(result) {
	document.getElementById("btnPrint").disabled = false;
	document.getElementById("btnSave").disabled = false;
	order['invoice_no'] = result;
	document.getElementById("invoice_no").value = order['invoice_no'];
	
	printInvoice();
}
function getInNoNo(result) {
	document.getElementById("btnPrint").disabled = false;
	document.getElementById("btnSave").disabled = false;
	alert("获取发票号出现错误, 请稍后再试");
}
function printOrder() {
	printInvoice();
}
function invoiceOrder(){
	if (!confirm("确定要恢复发票?"))
		return;
		var form = new FormData();
	form.append('order', JSON.stringify(order));
	form.append('orderitems', JSON.stringify(orderItems));
	postRequest('postVoidInvoice.php', form, voidYes, voidNo);
}
function voidYes(result) {
	alert("发票已恢复，请到发票列表中查看");
	closeInvoice();
}
function voidNo(result) {
	alert("系统错误，请稍后再试");
}
	
/* PRINT */
function printInvoice() {
	var i = 0;
	var src = "files/"+"<?php echo $_SESSION['uDb']; ?>"+"/logo.png";
	var header = '<html><head>';
	header += '<style type="text/css" media="print">@page { size:A4; margin:0.8cm 0.8cm 0.8cm 1.5cm;}\</style>';
	header += '</head><body background="void.jpg">';
	header += '<img src="void.jpg" style="position: absolute; width: 100%; height: auto; top: 100px; left: 0px; z-index: -1;" />';
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
// end of second row	
	output += '</tr></table>';
	
	// Title
	output += '<table width="100%" style="border:1px solid #808080;" cellpadding="2" cellspacing="0">';
	output += '<tr style="font-size:14px">';
	output += '<td><h3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h3></td>';
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
				code += 'ART.'+orderItems[i]['i_code'];
		} else {
			if (opPrintNoName) {
				if (aOptions['printNoART'])
					code += orderItems[i]['ai_code'];
				else
					code += 'ART.'+orderItems[i]['ai_code'];
			} else {
				if (aOptions['printNoART'])
					code += orderItems[i]['a_name']+'&nbsp;'+orderItems[i]['ai_code'];
				else
					code += orderItems[i]['a_name']+'&nbsp;ART.'+orderItems[i]['ai_code'];
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
	var maxCount = 35;
	if (order['price_sum'] == order['total_sum'] && order['discount_rate'] == '0.00')
		maxCount = 40;
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
	output += '<td width="50%"><table width="100%" style="border:1px solid #808080;" cellpadding="5" cellspacing="0">';
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
		output += '<tr style="font-size:12px;"><td>Memo:&nbsp;'+order['note']+"</td></tr>";
	}
	output += '</table></td>';
	// Right
	output += '<td width="50%"><table width="100%" style="border:1px solid #808080;" cellpadding="5" cellspacing="0">';	
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
	output += '<td style="padding:1px;" align="right"><b>Total (inkl. MwSt):</b></td><td style="padding:1px;" align="right"><b>'+order['net']+'&nbsp;&nbsp;</b></td>';
	output += '</tr>';
	
	output += '</table></td>';
	
	output += '</tr>';
	output += '</table>';
	// reklamation
	if (aOptions['printReklamation']) {
		output += '<a style="font-size:9px;">* Die Waren bleiben bis zur vollständigen Bezahlung unser Eigentum. Reklamation nur innerhalb von 7 Tagen.</a>'
	}
	
	printout += header + output;
	
	printout += footer;
	  
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

function notZero(s) {
	if (s != "" && s != "0" && s != "0.00")
		return true;
	else
		return false;			
}


</script>

</body>
</html>
