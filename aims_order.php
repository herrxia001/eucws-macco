<?php 

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
$backPhp = 'aims_list.php';
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
	}
}

?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>	
	<title>EUIMS - Invoice</title>
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
			<div class="input-group-prepend"><span class="input-group-text">Invoice No.</span></div>
			<input type="text" class="form-control" name="invoice_no" id="invoice_no" value="" readonly>		
			<div class="input-group-prepend ml-2"><span class="input-group-text">Customer</span></div>
			<input type="text" class="form-control" name="k_name" id="k_name" value="" readonly>
			<button type="button" class="ml-1 btn btn-secondary" id="btnSrchCust" onclick="searchCust()">Search</button>
			<button type="button" class="ml-1 btn btn-secondary" id="btnShowCust" onclick="showCust()">View</button>
		</div>
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-4" align="right">		
			<button type="button" class="btn btn-secondary" id="btnOptions" onclick="showOptions()">Options</button>		
			<button type="button" class="btn btn-success" id="btnPrint" onclick="printOrder()">Print</button>
			<button type="button" class="btn btn-primary" id="btnSave" onclick="submitOrder()">Save</button>		
		</div>
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-2" align="right">
			<button type="button" class="btn btn-secondary" id="btnClose" onclick="closeOrder()">Close</button>		
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
					<th class="p-1" data-field="idx_image" data-width="15" data-width-unit="%" data-halign="center" data-align="left"></th>
					<th class="p-1" data-field="idx_code" data-width="20" data-width-unit="%" data-halign="center" data-align="left">Art. No.</th>
					<th class="p-1" data-field="idx_name" data-width="25" data-width-unit="%" data-halign="center" data-align="left">Name</th>
					<th class="p-1" data-field="idx_count" data-width="10" data-width-unit="%" data-halign="center" data-align="right">Quantity</th>
					<th class="p-1" data-field="idx_price" data-width="10" data-width-unit="%" data-halign="center" data-align="right">Price</th>
					<th class="p-1" data-field="idx_subtotal" data-width="10" data-width-unit data-halign="center" data-align="right">Subtotal</th>					
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
<!-- buttons -->
	<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-4" align="left">
			<button type="button" class="btn btn-secondary" id="btnTax"  onclick="showTax()">Tax</button>
			<button type="button" class="btn btn-secondary" id="btnDis"  onclick="showDis()">Discount</button>
			<button type="button" class="btn btn-secondary" id="btnFee"  onclick="showFee()">Fees</button>
			<button type="button" class="btn btn-secondary" id="btnPay"  onclick="showPay()">Payment</button>
		</div>
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-4" align = "center">
			<b>Quantity Total:&nbsp;</b><label id="sumCount" style="color:blue">0</label>
			<b>&nbsp;&nbsp;Total Value:&nbsp;</b><label id="sumPrice" style="color:blue">0.00</label>
		</div>
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-4" align = "right">
			<b>&nbsp;&nbsp;Discount(%):&nbsp;</b><label id="sumDiscountRate" style="color:blue">0.00</label>
			<b>&nbsp;&nbsp;Fees:&nbsp;</b><label id="sumFees" style="color:blue">0.00</label>
		</div>
	</div>	
<!-- summary -->
	<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12" align = "right">
			<b>&nbsp;&nbsp;Gross Total:&nbsp;</b><label id="sumTotal" style="color:blue">0.00</label>
		</div>
	</div>
	<div class="row">	
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12" align = "right">						
			<b>&nbsp;&nbsp;MwSt(</b><label id="sumTaxRate" style="color:blue">0.00</label><b>%)</b>
			<label id="sumTax" style="color:blue">0.00</label>
			<b>&nbsp;&nbsp;Net Total:&nbsp;</b><label id="sumNet" style="color:blue">0.00</label>
		</div>
	</div>
	<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12" align = "right">
			<b>Paid:&nbsp;</b><label id="sumPaid" style="color:blue">0.00</label>
			<b>&nbsp;&nbsp;Due:&nbsp;</b><label id="sumDue" style="color:red">0.00</label>
		</div>
	</div>

<!-- Modal: invoice item -->
<div class="modal fade" id="modalOrderItem" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdOrderItemTitle">Invoice Item</b>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-2 p-1" align="center">
					<img id="m_img" width="60" height="80" style="object-fit: cover"></img>
				</div>
				<div class="col-10 p-1">
					<div class="input-group">
						<div class="input-group-prepend"><span class="input-group-text">ART. No.</span></div>
						<input type="text" class="form-control" name="m_i_code" id="m_i_code" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">Name</span></div>
						<input type="text" class="form-control" name="m_i_name" id="m_i_name" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">Unit</span></div>
						<input type="text" class="form-control" name="m_unit_str" id="m_unit_str" readonly style="background-color:white">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">Quantity</span></div>
						<button type="button" class="btn btn-secondary mx-2" id="btnCountMinus" onclick="countMinus()"><span class='fa fa-minus'></button>
						<input type="number" min="0" step="1" class="form-control" name="m_count" id="m_count">
						<button type="button" class="btn btn-secondary mx-2" id="btnCountAdd" onclick="countAdd()"><span class='fa fa-plus'></button>	
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">Price</span></div>
						<input type="number" min="0" step="0.01" class="form-control" name="m_price" id="m_price">
					</div>
					<div class="input-group mt-1">
						<div class="input-group-prepend"><span class="input-group-text">Note</span></div>
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

<!-- Modal: Tax -->
<div class="modal fade" id="modalOrderTax" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdOrderTaxTitle">Tax</h5>
		</div>
		<div class="modal-body">
			<form>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text">Tax Rate&nbsp;(%)</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mt_tax_rate" id="mt_tax_rate">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text">Tax Value</span></div>
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
			<h5 class="modal-title" id="mdOrderDisTitle">Discount</h5>
		</div>
		<div class="modal-body">
			<form>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text">Discount Rate&nbsp;(-%)</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mdi_discount_rate" id="mdi_discount_rate">
			</div>	
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text">Discount Value</span></div>
				<input type="text" class="form-control" name="mdi_discount" id="mdi_discount" readonly>
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
			<h5 class="modal-title" id="mdOrderFeeTitle">Fees</h5>
		</div>
		<div class="modal-body">
			<form>	
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">Shipping</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee1" id="mf_fee1">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">On Delivery</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee2" id="mf_fee2">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">Banking</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee3" id="mf_fee3">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">Others</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee4" id="mf_fee4">
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
			<h5 class="modal-title" id="mdOrderPayTitle">Payment</h5>			
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">Due</span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mp_amount" id="mp_amount">
				<div class="dropdown ml-2">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">Methods</button>
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
						<th class="p-1" data-field="idx_type" data-width="25" data-width-unit="%" data-visible="false">Type</th>
						<th class="p-1" data-field="idx_value" data-width="25" data-width-unit="%">Method</th>
						<th class="p-1" data-field="idx_amount" data-width="30" data-width-unit="%">Value</th>
						<th class="p-1" data-field="idx_del" data-width="10" data-width-unit="%"></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">Total</span></div>
				<input type="text" class="form-control" name="mp_total" id="mp_total" readonly>
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">Paid</span></div>
				<input type="text" class="form-control" name="mp_pays_total" id="mp_pays_total" readonly>
			</div>			
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">Due</span></div>
				<input type="text" class="form-control" name="mp_pays_due" id="mp_pays_due" readonly>
			</div>	
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></button>
			<button type="button" class="btn btn-primary" id="btnPayOk" onclick="donePay()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Payment -->

<!-- Modal: Options -->
<div class="modal fade" id="modalOptions" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdopTitle">Options</h5>
		</div>
		<div class="modal-body">
			<a>Print<br></a>
				<div class="form-check">
				<label class="form-check-label">
					<input type="checkbox" class="form-check-input" id="mdopPrintNoName" value="">Do NOT print item name 
				</label>
				</div>
				<div class="form-check">
				<label class="form-check-label">
					<input type="checkbox" class="form-check-input" id="mdopPrintOnePage" value="">Print ONLY one page 
				</label>
				</div>
			<hr>
			<a>Refund<br></a>
			<button type="button" class="btn btn-secondary" id="mdopBtnRefund" onclick="createRefund()">Create Refund</button>
			<hr>
			<a>Remark<br></a>
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

<script src="js/sysfunc.js?01201058"></script>
<script src="js/ajax.js"></script>
<script src="js/autocomplete.js?202109131930"></script>	
<script src="js/modalCustSearch.js?202109090946"></script>
<script src="js/modalCust.js?202108121658"></script>
<script src="js/aOptions.js?202109151712"></script>
<script src="js/qrcode.js"></script>

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
         return "";
    }
});

$(document).ready(function(){
	// Load all articles
	loadArts();
	// Load orderItems
	getRequest("getInvoiceById.php?r_id="+rId, loadOrder, loadError);	

})

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
		subtotal = parseInt(orderItems[i]['real_count'])*parseFloat(orderItems[i]['price']);
		orderItems[i]['subtotal'] = subtotal.toFixed(2);
		rows.push({
			id: orderItems[i]['id'],
			idx_image: imgStr,
			idx_code: code,
			idx_name: name,
			idx_count: countStr,
			idx_price: orderItems[i]['price'],
			idx_subtotal: orderItems[i]['subtotal']
		});		
	}
	$table.bootstrapTable('append', rows);
}

// Display sum
function displaySum() {	
	var sumPrice = parseFloat(order['price_sum']);
	var sumFees = parseFloat(order['fee1']) + parseFloat(order['fee2']) + parseFloat(order['fee3']) + parseFloat(order['fee4']);
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
$modalNewItem.on('shown.bs.modal', function () {
	  $('#mdn_name').trigger('focus');
})
function selArt(e) {
	var x = $(e).text();
	document.getElementById("mdn_a_name").value = x;
}
function newItem() {	
	thisItem = new Object();
	thisItem['id'] = -1;
	document.getElementById("mdn_a_name").value = "";
	document.getElementById("mdn_code").value = "";
	document.getElementById("mdn_count").value = "";
	document.getElementById("mdn_price").value = "";
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
	// Update item
	if (thisItem['id'] != -1) {
		$modalNewItem.modal("toggle");
		updateItem(count, price, code, null);
		return;
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
	thisItem['i_id'] = "0";
	thisItem['count'] = count;
	thisItem['cost'] = cost;
	thisItem['price'] = price;
	thisItem['unit'] = "1";
	thisItem['real_count'] = count;
	var subtotal = parseInt(count)*parseFloat(price);
	thisItem['subtotal'] = subtotal.toFixed(2);
	// Add new item to orderItems
	orderItems[itemCount] = thisItem;
	// Add new row
	var rows = [];
	var imgStr = "";
	rows.push({
		id: thisItem['id'],
		idx_image: imgStr,
		idx_code: thisItem['ai_code'],
		idx_name: thisItem['a_name'],
		idx_count: thisItem['count'],
		idx_price: thisItem['price'],
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
	
		$modalOrderItem.modal();
	} else {		
		document.getElementById("mdn_code").value = thisItem['ai_code'];
		document.getElementById("mdn_a_name").value = thisItem['a_name'];
		document.getElementById("mdn_count").value = thisItem['count'];
		document.getElementById("mdn_price").value = thisItem['price'];
		document.getElementById("mdnBtnName").style.display = "none";
		document.getElementById("mdnBtnDel").style.display = "block";
		
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
	updateItem(count, price, null, note);
}
/************************************************************************
	UPDATE ITEM
************************************************************************/
function updateItem(count, price, code, note) {
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
	subtotal =  parseInt(real_count)*parseFloat(price);
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
        value: price
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
	if (!confirm("Do you really want to delete?")) {
		return;
	}
	$modalOrderItem.modal("toggle");
	delItem();
}
// Delete new item
function mdnDel() {
	if (!confirm("Do you really want to delete?")) {
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
         return "";
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
		alert("Paymant method exists");
		$("#mp_amount").trigger('focus');
		return;
	}
	// check amount	
	var amount = document.getElementById("mp_amount").value; 
	if (!onlyNumber(amount) || parseFloat(amount) < 0 || parseFloat(amount) > parseFloat(pays_due.toFixed(2))) {
		alert("Data invalid");
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
	displaySum();
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
var opPrintNoName = false;
function showOptions() {
	document.getElementById("mdopNote").value = order['note'];
	$modalOptions.modal();	
}
function doneOptions() {
	document.getElementById("mdopPrintNoName").checked ? opPrintNoName = true :opPrintNoName = false;
	document.getElementById("mdopPrintOnePage").checked ? opPrintOnePage = true :opPrintOnePage = false;
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
		alert("Please print or save");
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
	alert("Database error. Please try again later.");
}
function submitOrder() {
	if (itemCount <= 0) {
		alert("No item!");
		return;
	}
	if (order['k_id'] == "" || order['k_id'] == "0") {
		alert("Please add customer");
		return;
	}
	if (!notZero(order['invoice_no'])) {
		if (!confirm("Confirm to save?"))
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
	if (order['invoice_no'] == "") {
		alert("Error");
		return;
	}	
	var form = new FormData();
	form.append('order', JSON.stringify(order));
	form.append('orderitems', JSON.stringify(orderItems));
	postRequest('postRefundAdd.php', form, refundYes, refundNo);		
}
function refundYes(result) {
	alert("Refund created");
	$modalOptions.modal("toggle");
}
function refundNo(result) {
	alert("Error");
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
	alert("Database error. Please try again later");
}
function printOrder() {
	if (itemCount <= 0) {
		alert("No item!");
		return;
	}
	if (order['k_id'] == "" || order['k_id'] == "0") {
		alert("Please add customer");
		return;
	}
	// if new invoice, get invoice_no first
	if (!notZero(order['invoice_no'])) {
		if (!confirm("Confirm to print?"))
			return;
		document.getElementById("btnPrint").disabled = true;
		document.getElementById("btnSave").disabled = true;
		getRequest("getInvoiceNo.php?r_id="+order['r_id'], getInNoYes, getInNoNo);
	}
	else
		printInvoice();
}
	
/* PRINT */
function printInvoice() {
	var i = 0;
	var src = "files/"+"<?php echo $_SESSION['uDb']; ?>"+"/logo.png";
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
		if (isCHECust(myCustomer['ustno']) && orderItems[i]['note'] != null)
			code += '&nbsp;'+orderItems[i]['note'];
		var countStr = orderItems[i]['count'];
		if (orderItems[i]['unit'] != "1")
			countStr = orderItems[i]['count']+" (x"+orderItems[i]['unit']+")";
		output += '<tr style="font-size:12px; font-family:Arial">';
		output += '<td style="padding:1px; border-left:1px solid #808080;">'+'&nbsp;&nbsp;'+code+'</td>';
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+countStr+'&nbsp;</td>';		
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+orderItems[i]['price']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+orderItems[i]['subtotal']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid #808080;" align="right">'+order['tax_rate']+'</td>';
		output += '</tr>';
	}
	output += '<tr><td align="center" style="font-size:12px; font-family:Arial; border-top:1px solid #808080;" colspan="5">===Gesamtmenge:&nbsp;'+order['count_sum']+'&nbsp;Stück===</td></tr>';
	if (notGermanCust(myCustomer['ustno']) && !isCHECust(myCustomer['ustno']))
		output += '<tr><td align="center" style="font-size:12px; font-family:Arial" colspan="5">Die i.g. Lieferung erfolgt gem. &sect;6a UStG bzw. nach Artikel 22 Ab s. 3 der 6.EG-Richtlinie steuerfrei. Muster einer Gelangensbestätigung im Sinne des &sect;17a Abs.2 Nr.2 UstDV</td></tr>';
	if (isCHECust(myCustomer['ustno']))
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
		output += '<td style="padding:1px;" align="right">Nebenkosten:&nbsp;</td><td style="padding:1px;" align="right">'+order['fee4']+'&nbsp;&nbsp;</td>';
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
		output += '<br><br><br><br><br>';
		output += '<a style="font-size:12px;">(Unterschrift des Abnehmers oder seines Vertretungsberechtigen sowie Name des Unterzeichnenden in Druckschrift)</a><br><br>';
		printout += output;
	}
	
	printout += footer;
	  
	var mywindow = window.open();
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

function getQRCodeText() {
	var text = "BCD\n001\n1\nSCT\n" + 
		company['bic'] + "\n" + company['c_name'] + "\n" + company['iban'] + "\n" + "EUR" + order['net'] + "\n\n" + order['invoice_no'] + "\n";
	return text;
}

</script>

</body>
</html>
