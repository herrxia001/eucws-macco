<?php
/************************************************************************************
	File:		a_voidmgt.php
	Purpose:	invoice void
************************************************************************************/

// Start session
session_start();
if(!$_SESSION['uId'])
	header("Location:alogin.php");

// Include files
include 'resource.php';
include_once 'db_functions.php';

// Init variables
$thisResource = new myResource($_SESSION['uLanguage']);

$myCompany= dbQueryCompany();

?>

<!doctype html>
<html lang="en">

<head>
    <?php include 'include/header.php' ?>
	<title>EUIMS - INVOICE VOID</title>
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
	<?php include "include/modalCustSearch.php" ?>
	
	<div class="container">	
<!-- buttons -->
		<div class="row">
			<div class="p-1 input-group col-12 col-sm-12 col-md-12 col-lg-8">
				<button type="button" class="btn btn-outline-secondary" id="selTime" onclick="selectTime()">
					<?php echo $thisResource->mdstRdThisMonth ?></button>				
				<button type="button" class="ml-1 btn btn-outline-secondary" id="btnSelCust" style="width:200px" onclick="selCust()"></button>
				<button type="button" class="ml-1 btn btn-secondary" id="btnAllCust" onclick="allCust()"><span class='fa fa-users'><span></button>			
			</div>	
			<div class="p-1 input-group col-12 col-sm-12 col-md-12 col-lg-4" align="right">
				<button type="button" class="btn btn-outline-secondary dropdown-toggle"  id="btnInYear" data-toggle="dropdown"><?= date("Y") ?></button>
						<div class="dropdown-menu">
							<?php for($year = date("Y"); $year >= 2021; $year--){ ?>
								<a class="dropdown-item" href="#" onclick="selInYear(this)"><?= $year ?></a>
							<?php } ?>
						</div>
				<input type="text" class="ml-1 form-control" name="invoice_no" id="invoice_no">
				<button type="button" class="ml-1 btn btn-secondary" id="btnSrchNo" onclick="searchInvoice()"><span class='fa fa-search'></span></button>
				<button type="button" class="ml-1 btn btn-secondary" id="btnExport" onclick="exportFile()"><span class='fa fa-floppy-o'></span></button>
				<button type="button" class="ml-1 btn btn-secondary" id="btnPrint" onclick="printFile()"><span class='fa fa-print'></span></button>
				<button type="button" class="ml-1 btn btn-secondary" id="btnPdf" onclick="pdfFile()"><span class='fa fa-file'></span></button>
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
				<th class="p-1" data-field="idx_date" data-width="20" data-width-unit="%" data-align="center" data-sortable="true">发票时间</th>
				<th class="p-1" data-field="idx_cust" data-width="30" data-width-unit="%" data-sortable="true" >客户</th>	
				<th class="p-1" data-field="idx_count" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">件数</th>
				<th class="p-1" data-field="idx_total" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">税前金额</th>
				<th class="p-1" data-field="idx_tax" data-width="5" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">MwSt.</th>
				<th class="p-1" data-field="idx_net" data-width="15" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">税后金额</th>
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
			<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12" align="right">
				<a>发票数:&nbsp;&nbsp;</a><a style="color:blue" id="itemCount"></a>
				<a>&nbsp;&nbsp;总件数:&nbsp;&nbsp;</a><a style="color:blue" id="sumCount"></a>
				<a>&nbsp;&nbsp;总销售额(税前):&nbsp;&nbsp;</a><a style="color:blue" id="sumPrice"></a>
				<a>&nbsp;&nbsp;MwSt.:&nbsp;&nbsp;</a><a style="color:blue" id="sumTax"></a>
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
<script src="js/aOptions.js?202109151712"></script>

<script>
var company = <?php echo json_encode($myCompany) ?>;
var orders = new Array(), orderCount = 0;
var $table = $("#table");
var sortCol = "date", sortOp = 1;
var countTotal = 0, priceTotal = 0, taxTotal = 0, netTotal = 0, invoiceTotal = 0;
var customers, myCustomer = null;
// search invoice year
var searchYear = "2022";

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
	document.getElementById("sumPrice").innerText = priceTotal.toFixed(2);
	document.getElementById("sumTax").innerText = taxTotal.toFixed(2);
	document.getElementById("sumNet").innerText = netTotal.toFixed(2);
}

function loadTable(){
	countTotal = 0;
	priceTotal = 0;
	taxTotal = 0;
	netTotal = 0;
	invoiceTotal = 0;
	if (orderCount <= 0) {
		displaySum();
		return;
	}

	$table.bootstrapTable('removeAll');
	orders.sort(sortTable(sortCol, sortOp));
	var rows = [];
	for(var i=0; i<orderCount; i++){
		orders[i]['k_name'] = getCustNameById(orders[i]['k_id']);
		var tax = parseFloat(orders[i]['total_sum'])*parseFloat(orders[i]['tax_rate'])/100;
		rows.push({
			id: orders[i]['r_id'],
			idx_no: orders[i]['invoice_no'],
			idx_date: orders[i]['date'].substring(0,10),
			idx_cust: orders[i]['k_name'],
			idx_count: orders[i]['count_sum'],
			idx_total: orders[i]['total_sum'],
			idx_tax: tax.toFixed(2),
			idx_net: orders[i]['net']
		});
		invoiceTotal++;
		countTotal += parseInt(orders[i]['count_sum']);
		priceTotal += parseFloat(orders[i]['total_sum']);
		taxTotal += tax;
		netTotal += parseFloat(orders[i]['net']);
	}
	$table.bootstrapTable('append', rows);	
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
	var timeResult = mdstGetValue(); 
	var link = "getInvoiceVoid.php?";
	link += timeResult;
	if (myCustomer != null)
		link += "&k_id="+myCustomer['k_id'];
	getRequest(link, afterSearch, displayNo);
}

$(document).ready(function(){
	document.getElementById("myTitle").innerText = "作废发票列表";
	document.getElementById("btnSelCust").innerText = "全部客户";
	mdstSetChecked("timeThisMonth");
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
	var url = "a_void.php?back=a_voidmgt&r_id="+row.id;
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
	Search Invoice
****************************************************************************************************/
function searchInvoice() {
	var invoiceNo = document.getElementById("invoice_no").value;
	if (invoiceNo == "")
		return;
	getRequest("getInvoiceVoidByNo.php?invoice_no="+invoiceNo+"&year="+searchYear, searchInvoiceYes, searchInvoiceNo);
}
function searchInvoiceYes(result) {
	var url = "a_void.php?back=a_voidmgt&r_id="+result['r_id'];
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
	var output = "Rechnung Nr."+delim+"Datum Rechnung"+delim+"Steuergrundlage"+delim+"MwSt"+delim+"Gesamtbetrag"+delim+"Firma\n";
	for (var i=0; i<orderCount; i++) {
		var tax = parseFloat(orders[i]['total_sum'])*parseFloat(orders[i]['tax_rate'])/100;
		output += orders[i]['invoice_no']+delim;
		output += convertDate(orders[i]['date'].substring(0,10))+delim;
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
	var timeRange = mdstGetValue(1);
	
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
	output += '<th align="left" style="border-left:1px solid;">Steuergrundlage</th>';
	output += '<th align="left" style="border-left:1px solid;">MwSt</th>';
	output += '<th align="left" style="border-left:1px solid;">Gesamtbetrag</th>';
	output += '<th align="left" style="border-left:1px solid;">Firma</th>';
	output += '</tr></thead><tbody>';
	for (var i=0; i<orderCount; i++) {
		var tax = parseFloat(orders[i]['total_sum'])*parseFloat(orders[i]['tax_rate'])/100;
		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+orders[i]['invoice_no']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+convertDate(orders[i]['date'].substring(0,10))+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+'&nbsp;'+orders[i]['total_sum']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+'&nbsp;'+tax.toFixed(2)+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+'&nbsp;'+orders[i]['net']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+orders[i]['k_name']+'</td>';
		output += '</tr>';
	}
	output += '<tr style="font-size:12px;">';
	output += '<td align="right" style="padding:1px; border-left:1px solid; border-top:1px solid;" colspan="2">Gesamtsumme&nbsp;</td>';
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
	getRequest("getInvoiceVoidItemsById.php?r_id="+orders[pdfCount]['r_id'], getOrderItemsYes, getOrderItemsNo); 
}

function getOrderItemsYes(result) {
	var invoiceHTML = getInvoiceHTML(result);
	savePdf(invoiceHTML);
}

function getOrderItemsNo(result) {
	alert("error");
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
	for (i=0; i<maxCount-itemCount - 10; i++) {
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
