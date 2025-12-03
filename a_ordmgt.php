<?php
/************************************************************************************
	File:		a_ordmgt.php
	Purpose:	invoice list
************************************************************************************/

// Start session
session_start();
if(!$_SESSION['uId'])
	header("Location:alogin.php");

// Include files
include 'resource.php';
include_once 'db_functions.php';

$myCompany= dbQueryCompany();

// Init variables
$thisResource = new myResource($_SESSION['uLanguage']);

?>

<!doctype html>
<html lang="en">

<head>
    <?php include 'include/header.php' ?>
	<title>EUIMS - INVOICE LIST</title>
</head>

<style>
.page{
	width: 21cm;
	min-height: 29.7cm; 
	margin:1cm auto;
}
.loader {
	z-index: 999;
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

<body>
	<?php include 'include/a_nav.php' ?>
	<?php include "include/modalSelTime.php" ?>
	<?php include "include/modalCust.php" ?>
	<?php include "include/modalCustSearch.php" ?>
	
	<div class="container">	
<!-- buttons -->
		<div class="row">
			<div class="p-1 input-group col-12 col-sm-12 col-md-12 col-lg-8">
				<button type="button" class="btn btn-outline-secondary" id="selTime" onclick="selectTime()">
					<?php echo $thisResource->mdstRdToday ?></button>				
				<button type="button" class="ml-1 btn btn-outline-secondary" id="btnSelCust" style="width:200px" onclick="selCust()"></button>
				<button type="button" class="ml-1 btn btn-secondary" id="btnAllCust" onclick="allCust()"><span class='fa fa-users'><span></button>



				<div class="dropdown dropleft">
					<button type="button" class="btn btn-secondary dropdown-toggle ml-2" data-toggle="dropdown" aria-expanded="false"><span class='fa fa-flag'><span></button>
					<ul class="dropdown-menu" style="">
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=Austria">Austria</a></li>
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=Belgien">Belgien</a></li>
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=Czechia">Czechia</a></li>
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=Denmark">Denmark</a></li>
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=Deutschland">Deutschland</a></li>
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=Finland">Finland</a></li>
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=France">France</a></li>
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=Italy">Italy</a></li>
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=Luxemburg">Luxemburg</a></li>						
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=Nederland">Nederland</a></li>
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=Schweiz">Schweiz</a></li>
						<li><a class="dropdown-item" href="a_sales_report.php?back=a_ordmgt&country=Spain">Spain</a></li>
					</ul>
				</div>




				<button type="button" class="ml-1 btn btn-primary" id="btnNewCust" onclick="newCust()"><span class='fa fa-user-plus'><span></button>				
			</div>	
			<div class="p-1 input-group col-12 col-sm-12 col-md-12 col-lg-4" align="right">
				<button type="button" class="btn btn-outline-secondary dropdown-toggle"  id="btnInYear" data-toggle="dropdown"><?= date("Y") ?></button>
						<div class="dropdown-menu">
							<?php for($tmp = date("Y"); $tmp >= 2021; $tmp --){
								echo '<a class="dropdown-item" href="#" onclick="selInYear(this)">'.$tmp.'</a>';
							}?>
							<!--a class="dropdown-item" href="#" onclick="selInYear(this)">2024</a>
							<a class="dropdown-item" href="#" onclick="selInYear(this)">2023</a>
							<a class="dropdown-item" href="#" onclick="selInYear(this)">2022</a>
							<a class="dropdown-item" href="#" onclick="selInYear(this)">2021</a-->
						</div>
				<input type="text" class="ml-1 form-control" name="invoice_no" id="invoice_no">
				<button type="button" class="ml-1 btn btn-secondary" id="btnSrchNo" onclick="searchInvoice()"><span class='fa fa-search'></span></button>
				<button type="button" class="ml-1 btn btn-secondary" id="btnExport" onclick="exportFile()"><span class='fa fa-floppy-o'></span></button>
				<button type="button" class="ml-1 btn btn-secondary" id="btnPrint" onclick="printFile()"><span class='fa fa-print'></span></button>
				<button type="button" class="ml-1 btn btn-secondary" id="btnPdf" onclick="pdfFile()"><span class='fa fa-file'></span></button>
				<button type="button" class="ml-1 btn btn-primary" onclick="newOrder()"><span class="fa fa-plus"></span></button>
			</div>
		</div>	
<!-- Search result table -->
		<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12">	
		<table id="table" class="table-sm" data-toggle="table" data-single-select="true" data-click-to-select="true" data-height="480">
			<thead class="thead-light">
				<tr>
				<th class="p-1" data-field="id" data-width="" data-width-unit="%" data-visible="false"></th>
				<th class="p-1" data-field="idx_no" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">发票号</th>	
				<th class="p-1" data-field="idx_date" data-width="10" data-width-unit="%" data-align="center" data-sortable="true">发票时间</th>
				<th class="p-1" data-field="idx_cust" data-width="30" data-width-unit="%" data-sortable="true" >客户</th>	
				<th class="p-1" data-field="idx_count" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">件数</th>
				<th class="p-1" data-field="idx_total" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">税前金额</th>
				<th class="p-1" data-field="idx_tax" data-width="5" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">MwSt.</th>
				<th class="p-1" data-field="idx_net" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">税后金额</th>
				<th class="p-1" data-field="idx_fee1" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">运费</th>
				<th class="p-1" data-field="idx_paidDatum" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">付款时间</th>
				<th class="p-1" data-field="idx_paidTyp" data-width="10" data-width-unit="%" data-halign="center" data-align="left" data-sortable="true">付款方式</th>
				</tr>
			</thead>
			<tbody>
			<!-- load table by JS -->
			</tbody>
		</table>
		</div>
		</div>
<!-- summary -->
		<div class="row">
			<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-2" align="left">
				<div class="dropdown" style="display: inline-block;">
					<button type="button" id="btnPay" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">付款方式</button>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="#" onclick="selPay(this)">全部</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">Bar</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">Karte</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">Überweisung</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">Scheck</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">Nachnahme</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">PayPal</a>
						<a class="dropdown-item" href="#" onclick="selPay(this)">Vorkasse</a>
					</div>
				</div>
				<div class="dropdown" style="display: inline-block;">
					<button type="button" id="btnisPay" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">全部</button>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="#" onclick="selisPay(this, -1)">全部</a>
						<a class="dropdown-item" href="#" onclick="selisPay(this, 1)">已付</a>
						<a class="dropdown-item" href="#" onclick="selisPay(this, 0)">未付</a>
					</div>
				</div>
			</div>
			<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-10" align="right">
				<a>发票数:&nbsp;&nbsp;</a><a style="color:blue" id="itemCount"></a>
				<a>&nbsp;&nbsp;总件数:&nbsp;&nbsp;</a><a style="color:blue" id="sumCount"></a>
				<a>&nbsp;&nbsp;总销售额(税前):&nbsp;&nbsp;</a><a style="color:blue" id="sumPrice"></a>
				<a>&nbsp;&nbsp;MwSt.:&nbsp;&nbsp;</a><a style="color:blue" id="sumTax"></a>
				<a>&nbsp;&nbsp;总运费:&nbsp;&nbsp;</a><a style="color:blue" id="sumFee1"></a>
				<a>&nbsp;&nbsp;总金额:&nbsp;&nbsp;</a><a style="color:blue" id="sumNet"></a>
			</div>
		</div>
		
	</div> <!-- End of container -->

<!-- modalPDF -->
<div class="modal fade" id="modalPDF" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
	data-backdrop="static" data-keyboard="false" style="overflow-y:scroll">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-body">
		
		<div class="row p-1">
			<div class="col p-1">
				<label id="pdfInfo"></label>
			</div>
		</div>
		
		</div>
		</div>
	</div>
</div> <!-- end of modalPDF -->

</body>

<!-- html3canvas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<!-- jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.3.5/purify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script src="js/sysfunc.js?v1"></script>
<script src="js/ajax.js"></script>
<script src="js/autocomplete.js?202108130959"></script>
<script src="js/modalSelTime.js?202106211441"></script>
<script src="js/modalCustSearch.js?202109090946"></script>
<script src="js/modalCust.js?202108121658"></script>
<script src="js/aOptions.js?202109151712"></script>

<script>
var company = <?php echo json_encode($myCompany) ?>;
var orders = new Array(), orderCount = 0;
var $table = $("#table");
var sortCol = "date", sortOp = 1;
var countTotal = 0, priceTotal = 0, taxTotal = 0, netTotal = 0, invoiceTotal = 0, fee1Total = 0;
var customers, myCustomer = null;
var payType = "pay_all";
var ispay = -1;
var pays = ["pay_cash", "pay_card", "pay_bank", "pay_check", "pay_other", "pay_paypal", "pay_vorkasse"];
// search invoice year
var searchYear = "2024";

initAOptions();
var opPrintOnePage = false;
var opPrintNoName = false;
var opPrintNonEU = false;

// Load customers. This must be done before all search.
function getCustsBack(result) {
	customers = result;	
	searchOrders();
}
function loadCusts() {
	getRequest("getCusts.php", getCustsBack, null);
}
function getCustNameById(kid) {
	for (var i=0; i<customers.length; i++) {
		if (customers[i]['k_id'] == kid)
			return customers[i]['k_name'];
	}	
	return "未知客户";
}

function getCustById(kid) {
	for (var i=0; i<customers.length; i++) {
		if (customers[i]['k_id'] == kid)
			return customers[i];
	}	
	return null;
}

$table.bootstrapTable({   
	formatNoMatches: function () {
         return "没有符合条件的发票";
    }
});

// Display summary
function displaySum(){
	document.getElementById("itemCount").innerText = invoiceTotal;
	document.getElementById("sumCount").innerText = countTotal;
	//document.getElementById("sumPrice").innerText = priceTotal.toFixed(2);
	document.getElementById("sumPrice").innerText = (netTotal - fee1Total - taxTotal).toFixed(2);
	document.getElementById("sumTax").innerText = taxTotal.toFixed(2);
	document.getElementById("sumNet").innerText = netTotal.toFixed(2);
	document.getElementById("sumFee1").innerText = fee1Total.toFixed(2);
}

function loadTable(){
	countTotal = 0;
	priceTotal = 0;
	taxTotal = 0;
	fee1Total = 0;
	netTotal = 0;
	invoiceTotal = 0;
	if (orderCount <= 0) {
		displaySum();
		return;
	}
	$table.bootstrapTable('removeAll');
	orders.sort(sortTable(sortCol, sortOp));
	var rows = [];
	var inNo = "", payFlag = 0;
	for(var i=0; i<orderCount; i++){
		payFlag = checkPay(orders[i]);
		if (!payFlag)
			continue;
		if(ispay == 1){
			if(orders[i]['isPayed'] == 0) continue;
		}else if(ispay == 0){
			if(orders[i]['isPayed'] == 1) continue;
		}
		if (payFlag == 1)
			inNo = orders[i]['invoice_no'];
		else
			inNo = "<a style='background-color:yellow;'>"+orders[i]['invoice_no']+"</a>";
		orders[i]['k_name'] = getCustNameById(orders[i]['k_id']);
		var tax = parseFloat(orders[i]['total_sum'])*parseFloat(orders[i]['tax_rate'])/100;
		
		var payTypTitle = "";
		if(orders[i]['pay_bank'] != "0.00"){
			payTypTitle = "Überweisung";
		}else if(orders[i]['pay_bank'] != "0.00"){
			payTypTitle = "Überweisung";
		}else if(orders[i]['pay_card'] != "0.00"){
			payTypTitle = "Karte";
		}else if(orders[i]['pay_cash'] != "0.00"){
			payTypTitle = "Bar";
		}else if(orders[i]['pay_check'] != "0.00"){
			payTypTitle = "Scheck";
		}else if(orders[i]['pay_other'] != "0.00"){
			payTypTitle = "Nachnahme";
		}else if(orders[i]['pay_paypal'] != "0.00"){
			payTypTitle = "PayPal";
		}else if(orders[i]['pay_vorkasse'] != "0.00"){
			payTypTitle = "Vorkasse";
		}
		rows.push({
			id: orders[i]['r_id'],
			idx_no: inNo,
			idx_date: orders[i]['date'].substring(0,10),
			idx_cust: orders[i]['k_name'],
			idx_count: orders[i]['count_sum'],
			idx_total: orders[i]['total_sum'],
			idx_tax: tax.toFixed(2),
			idx_net: orders[i]['net'],
			idx_fee1: orders[i]['fee1'],
			idx_paidDatum: orders[i]['paidDatum'],
			idx_paidTyp: payTypTitle
		});
		invoiceTotal++;
		countTotal += parseInt(orders[i]['count_sum']);
		priceTotal += parseFloat(orders[i]['total_sum']);
		taxTotal += tax;
		netTotal += parseFloat(orders[i]['net']);
		fee1Total += parseFloat(orders[i]['fee1']);
	}
	$table.bootstrapTable('append', rows);
	var index = 0;
	for(var i=0; i<orderCount; i++){
		payFlag = checkPay(orders[i]);
		if (!payFlag)
			continue;
		if(ispay == 1){
			if(orders[i]['isPayed'] == 0) continue;
		}else if(ispay == 0){
			if(orders[i]['isPayed'] == 1) continue;
		}
		if(orders[i]['isPayed'] == 0){
			//----unpay set color----//
			$table.find("tr:nth-child("+(index+1)+")").css("color","red");
		}
		index++;
	}
	displaySum();
}

function sortTable(key, option){
    return function(a, b){ 
		var x = a[key]; var y = b[key];
		if (key == "date") {
			var x1 = a[key].substring(0,10); var x2 = a[key].substring(11,19); x = x1+"T"+x2; x = new Date(x); 
			var y1 = b[key].substring(0,10); var y2 = b[key].substring(11,19); y = y1+"T"+y2; y = new Date(y);
		}
		if (key == "invoice_no") {
			x= parseInt(x); y = parseInt(y);
		}
		if (key == "count_sum") {
			x= parseInt(x); y = parseInt(y);
		}
		if (key == "total_sum") {
			x= parseFloat(x); y = parseFloat(y);
		}
		if(option == 1){
			return ((x < y) ? 1 : ((x > y) ? -1 : 0));
		}
		else {
			return ((x < y) ? -1 : ((x > y) ? 1 : 0));
		}  
    }    
}    

function afterSearch(result){
	orders = result;
	orderCount = orders.length;
	loadTable();
}

function displayNo(result) {
	$table.bootstrapTable('removeAll');
	orderCount = 0;
	countTotal = 0;
	priceTotal = 0;
	taxTotal = 0;
	netTotal = 0;
	displaySum();
}

function searchOrders(){
	var timeResult;
	if(localStorage.getItem("timeStr") != "" && typeof localStorage.getItem("timeStr") !== 'undefined' && localStorage.getItem("timeStr") !== null){
		document.getElementById("selTime").innerText = localStorage.getItem("timeStr");
		timeResult = localStorage.getItem("timeResult");
	}else{
		timeResult = mdstGetValue();
	}
	var link = "getInvoices.php?";
	link += timeResult;
	if (myCustomer != null)
		link += "&k_id="+myCustomer['k_id'];
	getRequest(link, afterSearch, displayNo);
}

$(document).ready(function(){
	if(localStorage.getItem("billingPaySearch") == "0"){ // not pay
		document.getElementById("btnisPay").innerText = "未付";
		ispay = 0;
	}else if(localStorage.getItem("billingPaySearch") == "1"){ // payed
		document.getElementById("btnisPay").innerText = "已付";
		ispay = 1;
	}

	document.getElementById("myTitle").innerText = "销售发票列表";
	// Search by default
	document.getElementById("btnSelCust").innerText = "全部客户";
	loadCusts();
 });

// Prevent 'enter' key for submission, only enabled for barcode input
$('form input').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
		return false;
    }
});

// Click a row to view product
$('#table').on('click-row.bs.table', function (e, row, $element) {
	var url = "ainvoice.php?back=a_ordmgt&r_id="+row.id;
	window.location.assign(url);
});

// Sort
function doneSort(e){
	var x = $(e).text();
	switch(x) {
		case "销售件数最多": sortCol = 'count_sum'; sortOp = 1; break;
		case "销售金额最大": sortCol = 'total_sum'; sortOp = 1; break;
		case "发票号从小到大": sortCol = 'invoice_no'; sortOp = 0; break;
		case "发票号从大到小": sortCol = 'invoice_no'; sortOp = 1; break;
		case "客户名称A-Z": sortCol = 'k_name'; sortOp = 0; break;
		case "客户名称Z-A": sortCol = 'k_name'; sortOp = 1; break;
		default: sortCol = "date"; sortOp = 1;
	}
	document.getElementById("sortOption").innerText = x;
	loadTable(orders);
}

// Show time selection (modalTime)
function selectTime(){
	$modalSelTime.modal();	
}

// Finish time selection (modalTime)
function mdstDoneTime(){
	$modalSelTime.modal("toggle");	
	 
	var timeStr = mdstGetStr();	
	document.getElementById("selTime").innerText = timeStr;
	localStorage.setItem("timeResult", mdstGetValue());
	//localStorage.setItem("timeRange", mdstGetValue(1));
	localStorage.setItem("timeStr", timeStr);
	searchOrders();
}

// Show Customer
function selCust() {
	mksInit(2);
	$modalCustSearch.modal();	
}

// modalCustSearch
function mksDoneNext(customer) {
	myCustomer = customer;
	var cname = "";
	if (myCustomer['k_name'].length >= 20)
		cname = myCustomer['k_name'].substr(0, 20);
	else
		cname = myCustomer['k_name'];
	document.getElementById("btnSelCust").innerText = cname;
	searchOrders();
}

// All customers
function allCust() {
	myCustomer = null;
	document.getElementById("btnSelCust").innerText = "全部客户";
	searchOrders();
}

// New customer
function newCust() {
	var customer = new Object();	
	customer['k_id'] = "";
	
	mkInit(customer);
	$modalCust.modal();	
}
// Done modalCust
function mkSaveCust(customer) {
	loadCusts();
}
/****************************************************************************************************
	Select Payment
****************************************************************************************************/
function selPay(e) {
	var type_value = $(e).text();
	document.getElementById("btnPay").innerText = type_value;
	switch (type_value) {
		case "Bar": payType = "pay_cash"; break;
		case "Karte": payType = "pay_card"; break;
		case "Überweisung": payType = "pay_bank"; break;
		case "Scheck": payType = "pay_check"; break;
		case "Nachnahme": payType = "pay_other"; break;
		case "PayPal": payType = "pay_paypal"; break;
		case "Vorkasse": payType = "pay_vorkasse"; break;
		default: payType = "pay_all";
	} 
	loadTable();
}
function selisPay(e, typ) {
	var type_value = $(e).text();
	document.getElementById("btnisPay").innerText = type_value;
	ispay = typ;
	loadTable();
	localStorage.setItem("billingPaySearch", typ);
}
function checkPay(order) {
	if (payType == "pay_all")
		return 1;
	for (var i=0; i<pays.length; i++) {
		if (pays[i] == payType && parseFloat(order[pays[i]]) > 0) {
			if (parseFloat(order[pays[i]]) == parseFloat(order['net']))
				return 1;
			else
				return 2;
			break;
		}
	}
	return 0;
}
/****************************************************************************************************
	New Invoice
****************************************************************************************************/
function newOrderYes(result) {
	var url = "ainvoice.php?back=a_ordmgt&r_id="+result;
	window.location.assign(url);
}
function newOrderNo(result) {
	alert("创建发票错误,请稍后再试");
}
function newOrder() {
	getRequest("getInvoiceNew.php", newOrderYes, newOrderNo);
}
/****************************************************************************************************
	Search Invoice
****************************************************************************************************/
function searchInvoice() {
	var invoiceNo = document.getElementById("invoice_no").value;
	if (invoiceNo == "")
		return;
	searchYear = document.getElementById("btnInYear").innerText;
	getRequest("getInvoiceByNo.php?invoice_no="+invoiceNo+"&year="+searchYear, searchInvoiceYes, searchInvoiceNo);
}
function searchInvoiceYes(result) {
	var url = "ainvoice.php?back=a_ordmgt&r_id="+result['r_id'];
	window.location.assign(url);
}
function searchInvoiceNo(result) {
	alert("没有找到该发票");
	document.getElementById("invoice_no").focus();
}

function selInYear(e) {
	searchYear = $(e).text();
	document.getElementById("btnInYear").innerText = searchYear;
}
/****************************************************************************************************
	Export
****************************************************************************************************/
function convertNumber(number) {
	var zhengshu = number.slice(0, number.length-3);
	var xiaoshu = number.substr(-2);
	var newzheng = zhengshu.replace(",",".");
	var newshu = "\""+newzheng+","+xiaoshu+"\"";

	return newshu;	
}

function exportFile() {
	var delim = ',';
	if (aOptions['exportDecimal'] == 1) delim = ';';
	var output = "Rechnung Nr."+delim+"Datum Rechnung"+delim+"Datum Zahlung"+delim+"Steuergrundlage"+delim+"MwSt"+delim+"Gesamtbetrag"+delim+"Firma\n";
	var inNo = "", payFlag = 0;
	for (var i=0; i<orderCount; i++) {
		payFlag = checkPay(orders[i]);
		if (!payFlag)
			continue;
		if(ispay == 1){
			if(orders[i]['isPayed'] == 0) continue;
		}else if(ispay == 0){
			if(orders[i]['isPayed'] == 1) continue;
		}
		if (payFlag == 1)
			inNo = orders[i]['invoice_no'];
		else
			inNo = orders[i]['invoice_no'] + " *";
		var tax = parseFloat(orders[i]['total_sum'])*parseFloat(orders[i]['tax_rate'])/100;
		output += inNo + delim;

		var paidDatum = orders[i]['paidDatum'];
		if(paidDatum !== null && paidDatum != "" ) paidDatum = convertDate(orders[i]['paidDatum'].substring(0,10));
		else paidDatum = "-";

		output += convertDate(orders[i]['date'].substring(0,10))+delim;
		output += paidDatum+delim;
		if (aOptions['exportDecimal'] == 1) {
			output += convertNumber(orders[i]['total_sum'])+delim;
			output += convertNumber(tax.toFixed(2))+delim;
			output += convertNumber(orders[i]['net'])+delim;
		} else {
			output += orders[i]['total_sum']+delim;
			output += tax.toFixed(2)+delim;
			output += orders[i]['net']+delim;
		}		
		output += orders[i]['k_name']+'\n';
	}
	
	var a = document.createElement("a");
	var file = new Blob([output], {type: 'text/plain'});
	a.href = URL.createObjectURL(file);
	a.download = "Rechnung-Kunden-"+currentDate(1)+".csv";
	document.body.appendChild(a);
    a.click();window.URL.revokeObjectURL(file);
    a.remove();
}

/* PRINT */
function printFile() { 	
	var dt = currentDate();	
	var timeRange;
	if(localStorage.getItem("timeStr") != "" && typeof localStorage.getItem("timeStr") !== 'undefined' && localStorage.getItem("timeStr") !== null){
		var urlParam = new URLSearchParams(localStorage.getItem("timeResult"));
		timeRange = { timefrom: urlParam.get('timefrom'), timeto: urlParam.get('timeto') };
	}else{
		timeRange = mdstGetValue(1);
	}
	
	var src = "files/"+"<?php echo $_SESSION['uDb']; ?>"+"/logo.png";
	var output = '<html><head><style type="text/css" media="print">@page { size:auto; margin:0.8cm 0.8cm 0.8cm 1.5cm; }\</style></head><body>';	
	// Title
	output += '<table width="100%" cellpadding="5" cellspacing="0"><tr>';
	output += '<td align="center">';
	output += '<img height="100" style="object-fit: cover" src="'+src+'"></img>';
	output += '</td>';
	output += '<td align="left" style="border-left:1px solid; border-top:1px solid; border-right:1px solid">';
	output += '<a style="font-size:12px">Vom&nbsp;'+convertDate(timeRange['timefrom'])+'&nbsp;bis&nbsp;'+convertDate(timeRange['timeto'])+'&nbsp;Rechnungsliste (Kunden)</a><br>';
	output += '</td>';
	output += '</tr></table>';
	// Articles
	output += '<table width="100%" cellpadding="2" cellspacing="0" style="border:1px solid;"><thead>';
	output += '<tr style="font-size:12px;">';
	output += '<th align="center" style="border-left:1px solid;">Rechnung Nr.</th>';
	output += '<th align="center" style="border-left:1px solid;">Datum<br>Rechnung</th>';
	output += '<th align="center" style="border-left:1px solid;">Datum<br>Zahlung</th>';
	output += '<th align="left" style="border-left:1px solid;">Steuergrundlage</th>';
	output += '<th align="left" style="border-left:1px solid;">MwSt</th>';
	output += '<th align="left" style="border-left:1px solid;">Gesamtbetrag</th>';
	output += '<th align="left" style="border-left:1px solid;">Firma</th>';
	output += '</tr></thead><tbody>';
	var inNo = "", payFlag = 0;
	for (var i=0; i<orderCount; i++) {
		payFlag = checkPay(orders[i]);
		if (!payFlag)
			continue;
		if(ispay == 1){
			if(orders[i]['isPayed'] == 0) continue;
		}else if(ispay == 0){
			if(orders[i]['isPayed'] == 1) continue;
		}
		if (payFlag == 1)
			inNo = orders[i]['invoice_no'];
		else
			inNo = orders[i]['invoice_no'] + " *";
		var tax = parseFloat(orders[i]['total_sum'])*parseFloat(orders[i]['tax_rate'])/100;

		var paidDatum = orders[i]['paidDatum'];
		if(paidDatum !== null && paidDatum != "" ) paidDatum = convertDate(orders[i]['paidDatum'].substring(0,10));
		else paidDatum = "-";

		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+inNo+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+convertDate(orders[i]['date'].substring(0,10))+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+paidDatum+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+'&nbsp;'+orders[i]['total_sum']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+'&nbsp;'+tax.toFixed(2)+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+'&nbsp;'+orders[i]['net']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+orders[i]['k_name']+'</td>';
		output += '</tr>';
	}
	output += '<tr style="font-size:12px;">';
	output += '<td align="right" style="padding:1px; border-left:1px solid; border-top:1px solid;" colspan="3">Gesamtsumme&nbsp;</td>';
	output += '<td align="right" style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+priceTotal.toFixed(2)+'</td>';
	output += '<td align="right" style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+taxTotal.toFixed(2)+'</td>';
	output += '<td align="right" style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+netTotal.toFixed(2)+'</td>';
	output += '<td align="right" style="padding:1px; border-left:1px solid; border-top:1px solid;"></td>';

	output += '</tr>';
	output += '</tbody></table>';
	// Footer
	output += '<a style="font-size:12px">Datum:&nbsp;'+dt+'&nbsp;&nbsp;Betreiber:&nbsp;'+company['c_name']+'</a>';
	// Print
	var mywindow = window.open();
    mywindow.document.write(output);
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

/****************************************************************************************************
	PDF
****************************************************************************************************/
var modalPdf = $('#modalPDF');
var pdfCount = 0;
var pdfRechNr = "";

function pdfFile() {
	if(!confirm("确定要导出 "+orderCount+" 个PDF文件?"))
		return;

	var i=0;
	var id_str = "";
	for(i = 0; i<orders.length; i++){
		if(id_str == "") id_str = orders[i].r_id;
		else id_str += "," + orders[i].r_id;
	}
	console.log(id_str);
	//window.open("api/xrechnungZip.php?id_str="+id_str);
	modalPdf.modal();
	getOrderItem();		
}

function savePdf(invoiceHTML) {
	window.jsPDF = window.jspdf.jsPDF;
	var pdfDoc = new jsPDF('p', 'mm', 'a4');
	var width = pdfDoc.internal.pageSize.width; 
	pdfDoc.html(invoiceHTML,  {
		callback: function (doc) {
			var id = pdfRechNr+".pdf";
			var pdfInfo = document.getElementById("pdfInfo").innerText;
			pdfInfo += "OK\n";
			document.getElementById("pdfInfo").innerText = pdfInfo;
			doc.save(id);
			pdfCount++;
			if (pdfCount == orderCount)
				modalPdf.modal("toggle");
			else
				getOrderItem();
		},
		x: 15,
		y: 7,
		width: 45,
		windowWidth: width
	});	
}

function getOrderItem(rId) {
	pdfRechNr = orders[pdfCount]['invoice_no'];
	var pdfInfo = document.getElementById("pdfInfo").innerText;
	pdfInfo += pdfRechNr+" ... ...";
	document.getElementById("pdfInfo").innerText = pdfInfo;
	getRequest("getInvoiceItemsById.php?r_id="+orders[pdfCount]['r_id'], getOrderItemsYes, getOrderItemsNo);
}

function getOrderItemsYes(result) {
	var invoiceHTML = getInvoiceHTML(result);
	//var invoiceXML = getInvoiceXML(result);
	savePdf(invoiceHTML);
}

function getOrderItemsNo(result) {
	alert("error");
}
function getInvoiceXML(orderItems){
	var order = orders[pdfCount];
	var cust = getCustById(order['k_id']);
	var itemCount = orderItems.length;


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
    output += '<ram:ID>'+cust["k_id"]+'</ram:ID>';
    output += '<ram:Name>'+cust["k_name"]+'</ram:Name>';
    output += '<ram:PostalTradeAddress>';
    output += '<ram:PostcodeCode>'+cust["post"]+'</ram:PostcodeCode>';
    output += '<ram:LineOne>'+cust["address"]+'</ram:LineOne>';
    output += '<ram:CityName>'+cust["city"]+'</ram:CityName>';
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

	return output;
}

function getInvoiceHTML(orderItems) { 
	var order = orders[pdfCount];
	var cust = getCustById(order['k_id']);
	var itemCount = orderItems.length;
	
	var i = 0;
	var src = "files/"+"<?php echo $_SESSION['uDb']; ?>"+"/logo.jpg";
	var header = '<html><head><style type="text/css"></style></head><body><div class="page">';
	var footer = '</div></body></html>';	
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
	if (cust["name1"] != null && cust["name1"] != "")
		output += '&nbsp;&nbsp;'+cust["name1"]+'<br>';
	output += '&nbsp;&nbsp;'+cust["k_name"]+'<br>';
	if (cust["address"] != null && cust["address"] != "")
		output += '&nbsp;&nbsp;'+cust["address"]+'<br>';
	if (cust["post"] != null && cust["post"] != "")
		output += '&nbsp;&nbsp;'+cust["post"];
	if (cust["city"] != null && cust["city"] != "")
		output += '&nbsp;'+cust["city"]+'<br>';
	else
		output += '<br>';
	if (cust["country"] != null && cust["country"] != "")
		output += '&nbsp;&nbsp;'+cust["country"];
	if (cust["ustno"] != null && cust["ustno"] != "") {
		if (isCHECust(cust['ustno']))
			output += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;VAT#:'+cust["ustno"];
		else
			output += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ust-IdNr.:'+cust["ustno"];
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
	output += '<td><h3>Rechnung</h3></td>';
	output += '<td style="border-left:1px solid #808080;">Nr.:<br>&nbsp;&nbsp;&nbsp;&nbsp;'+order['invoice_no']+'</td>';
	output += '<td style="border-left:1px solid #808080;">Datum:<br>&nbsp;&nbsp;&nbsp;&nbsp;'+convertDate(order['date'])+'</td>';
	output += '<td style="border-left:1px solid #808080;">Kunden Nr.:<br>&nbsp;&nbsp;&nbsp;&nbsp;'+cust["k_code"]+'</td>';
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
		var subtotal = parseInt(orderItems[i]['count']) * parseFloat(orderItems[i]['price']);
		orderItems[i]['subtotal'] = subtotal.toFixed(2);
		
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
		if ((isCHECust(cust['ustno']) || opPrintNonEU) && orderItems[i]['note'] != null)
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
	if (notGermanCust(cust['ustno']) && !isCHECust(cust['ustno']) && !opPrintNonEU)
		output += '<tr><td align="center" style="font-size:12px; font-family:Arial" colspan="5">Die i.g. Lieferung erfolgt gem. &sect;6a UStG bzw. nach Artikel 22 Ab s. 3 der 6.EG-Richtlinie steuerfrei. Muster einer Gelangensbestätigung im Sinne des &sect;17a Abs.2 Nr.2 UstDV</td></tr>';
	if (isCHECust(cust['ustno']) || opPrintNonEU)
		output += '<tr><td align="center" style="font-size:12px; font-family:Arial" colspan="5">Der Ausführer der Waren, auf die sich dieses Handelspapier bezieht, erklärt, dass diese Waren, so weit nich anders angegeben, präferenzbegünstigte EU-Ursprungswaren sind.<br>Neuss, '+convertDate(order['date'])+'</td></tr>';
	// Spacing
	var maxCount = 34;
	if (order['price_sum'] == order['total_sum'] && order['discount_rate'] == '0.00')
		maxCount = 39;
	if (notGermanCust(cust['ustno'])) {
		if (isCHECust(cust['ustno']))
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
	
	printout += header + output;
	
	printout += footer;
	
	return printout;
}

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

function notZero(s) {
	if (s != "" && s != "0" && s != "0.00")
		return true;
	else
		return false;			
}
</script>

</html>
