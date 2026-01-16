<?php
/* INVOICE HOME */

session_start();
if(!$_SESSION['uId'])
	header("Location:alogin.php");

if($_SESSION['uRole'] == 10) header("location: a_ordmgt_onlyshow.php");	

include_once 'db_invoice.php';

$myCompany = $_SESSION['myCompany'];

//$result = dbQueryInvoiceByStatus('0');
//if ($result > 0)
//{
//	$rId = $result[0]['r_id'];
	//header("Location: ainvoice.php?back=a_neword&r_id=".$rId);
//}

?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>	
	<title>EUIMS - NEW INVOICE</title>
</head>
<body>	
	<?php include 'include/a_nav.php' ?>
	
	<!--div class="row"> 
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12" align="center">
			<div><a>目前没有新的发票</a></div>
		</div>
	</div-->
	<div class="container">	
	<!-- Search result table -->
	<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12">	
			<table id="table" class="table-sm" data-toggle="table" data-single-select="true" data-click-to-select="true" data-height="480">
				<thead class="thead-light">
					<tr>
					<th class="p-1" data-field="id" data-width="" data-width-unit="%" data-visible="false"></th>
					<!--th class="p-1" data-field="idx_no" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">发票号</th-->	
					<th class="p-1" data-field="idx_date" data-width="20" data-width-unit="%" data-align="center" data-sortable="true">日期</th>
					<th class="p-1" data-field="idx_total" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">税前金额</th>
					<th class="p-1" data-field="idx_cust" data-width="30" data-width-unit="%" data-sortable="true" >客户全称</th>	



					<!--th class="p-1" data-field="idx_count" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">件数</th>
					<th class="p-1" data-field="idx_total" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">税前金额</th>
					<th class="p-1" data-field="idx_tax" data-width="5" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">MwSt.</th>
					<th class="p-1" data-field="idx_fee1" data-width="15" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">运费</th-->
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
				<!--a>&nbsp;&nbsp;MwSt.:&nbsp;&nbsp;</a><a style="color:blue" id="sumTax"></a>
				<a>&nbsp;&nbsp;总运费:&nbsp;&nbsp;</a><a style="color:blue" id="sumFee1"></a>
				<a>&nbsp;&nbsp;总金额:&nbsp;&nbsp;</a><a style="color:blue" id="sumNet"></a-->
			</div>
		</div>
</div>
</body>

<script src="js/ajax.js"></script>
<script>

/*var myInterval;
var status = "<?php echo $result ?>";
if (status <= '0') {
	myInterval = setInterval(getInvoice, 5000);
}

function getInvoice() {	
	getRequest("getInvoiceFromOrder.php", getYes, getNo);
}

function getYes(result) {
	clearInterval(myInterval);
	
	var url = "ainvoice.php?back=a_neword&r_id="+result['r_id'];
	window.location.assign(url);
}
function getNo(result) {
	
}*/
var orders = new Array();
var countTotal = 0, priceTotal = 0, taxTotal = 0, netTotal = 0, invoiceTotal = 0, fee1Total = 0;
var customers;
var $table = $("#table");

// Click a row to view product
$('#table').on('click-row.bs.table', function (e, row, $element) {
	var url = "ainvoice.php?back=a_neword&r_id="+row.id;
	window.location.assign(url);
});

loadCusts();
function searchOrders(){
	var link = "getInvoiceFromOrder_2.php";
	getRequest(link, afterSearch, displayNo);
}
function afterSearch(result){
	orders = result;
	loadTable();
}
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

function loadTable(){
	countTotal = 0;
	priceTotal = 0;
	taxTotal = 0;
	fee1Total = 0;
	netTotal = 0;
	invoiceTotal = 0;
	var orderCount = orders.length;
	if (orderCount <= 0) {
		displaySum();
		return;
	}
	$table.bootstrapTable('removeAll');
	var rows = [];
	var inNo = "", payFlag = 0;
	for(var i=0; i<orderCount; i++){
	
		orders[i]['k_name'] = getCustNameById(orders[i]['k_id']);
		var tax = parseFloat(orders[i]['total_sum'])*parseFloat(orders[i]['tax_rate'])/100;
		rows.push({
			id: orders[i]['r_id'],
			//idx_no: inNo,
			idx_date: orders[i]['date'].substring(0,10),
			idx_cust: orders[i]['k_name'],
			idx_count: orders[i]['count_sum'],
			idx_total: orders[i]['total_sum'],
			idx_tax: tax.toFixed(2),
			idx_net: orders[i]['net'],
			idx_fee1: orders[i]['fee1']
		});
		invoiceTotal++;
		countTotal += parseInt(orders[i]['count_sum']);
		priceTotal += parseFloat(orders[i]['total_sum']);
		taxTotal += tax;
		netTotal += parseFloat(orders[i]['net']);
		fee1Total += parseFloat(orders[i]['fee1']);
	}
	$table.bootstrapTable('append', rows);	
	displaySum();
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
// Display summary
function displaySum(){
	document.getElementById("itemCount").innerText = invoiceTotal;
	document.getElementById("sumCount").innerText = countTotal;
	document.getElementById("sumPrice").innerText = priceTotal.toFixed(2);
	/*document.getElementById("sumTax").innerText = taxTotal.toFixed(2);
	document.getElementById("sumNet").innerText = netTotal.toFixed(2);
	document.getElementById("sumFee1").innerText = fee1Total.toFixed(2);*/
}
</script>

</html>

