<?php
/********************************************************************************
	File:		pur_mgt.php
	Purpose:	purchase management
*********************************************************************************/

// Start session
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

// Include files
include 'db_functions.php';
include_once 'resource_'.$_SESSION['uLanguage'].'.php';
$thisResource = new myResource();

// Init variables
$mySuppliers = dbQueryAllSuppliers();
$active[4] = "active";
?>

<!doctype html>
<html lang="zh">
<head>
    <?php include 'include/header.php' ?>
	<title>EUCWS- Purchase Management</title>
</head>

<style>
.dropdown-menu{
    max-height: 300px;
    overflow-y: scroll;
}
</style>

<body>

	<?php include 'include/nav.php' ?>	
	<?php include "include/modalSelTime.php" ?>
<div class="container">	
<!-- buttons -->
		<div class="row">
			<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-3">
				<button type="button" class="btn btn-outline-secondary" id="selTime" onclick="selectTime()">
					<?php echo $thisResource->mdstRdThisMonth ?></button>
					<div class="dropdown ml-1" style="display: inline-block;">
						<button type="button" class="p-1 ml-1 btn btn-outline-secondary dropdown-toggle" id="s_name" data-toggle="dropdown" style="width:100px">
						<?php echo $thisResource->comSupplierAll ?></button>
						<div class="dropdown-menu">
							<input type="text" style="position: sticky; top: 0; margin-left: 20px; margin-right: 20px; width: calc(100% - 40px);" class="form-control" placeholder="搜索.." id="myinput" oninput="filterFunction($(this))">
							<a class="dropdown-item" href="#" onclick="filterSup(this)"><?php echo $thisResource->comSupplierAll ?></a>
							<a class="dropdown-item" href="#" onclick="filterSup(this)"><?php echo $thisResource->comSupplierUnknown ?></a>
							<?php for($i=0; $i<count($mySuppliers); $i++)
								echo "<a class='dropdown-item' href='#' onclick='filterSup(this)'>".$mySuppliers[$i]['s_name']."</a>";
							?>
						</div>
					</div>

					<div class="dropdown ml-1" style="display: inline-block;">
						<button type="button" id="btnisPay" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">全部</button>
						<div class="dropdown-menu">
							<a class="dropdown-item" href="#" onclick="selisPay(this, -1)">全部</a>
							<a class="dropdown-item" href="#" onclick="selisPay(this, 1)">已付</a>
							<a class="dropdown-item" href="#" onclick="selisPay(this, 0)">未付</a>
						</div>
					</div>
			</div>
			<div class="p-1 col-8 col-sm-8 col-md-8 col-lg-3 input-group">
				<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comProductNo ?></span></div>
				<input type="text" class="form-control" name="i_code" id="i_code">
				<button type="button" class="ml-1 btn btn-outline-secondary" onclick="allCode()"><?php echo $thisResource->appAll ?></button>
			</div>
			<div class="p-1 col-4 col-sm-4 col-md-4 col-lg-2" align="right">

				<div class="dropdown ml-1" style="display: inline-block;">
					<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
						选项</button>
					<div class="dropdown-menu">
						<div class="dropdown-item" href="#" onclick="setPayOrder(1)">批量设为已付</div>
						<div class="dropdown-item" href="#" onclick="setPayOrder(0)">批量设为未付</div>
					</div>
				</div>
				<button type="button" class="ml-1 btn btn-secondary" id="btnPrint" onclick="printFile()"><span class="fa fa-print"></span></button>
				<button type="button" class="ml-1 btn btn-primary" onclick="newPur()"><span class='fa fa-plus'></button>
			</div>
		</div>	
<!-- summary -->
		<div class="row">
			<div class="col-12 col-sm-12 col-md-12 col-lg-8" align="center" style="border:1px solid lightgray;">
				<a><?php echo $thisResource->comTotalRecord ?>:&nbsp;&nbsp;</a><a style="color:blue" id="purCount"></a>
				<a>&nbsp;&nbsp;<?php echo $thisResource->comTotalQuantity ?>:&nbsp;&nbsp;</a><a style="color:blue" id="sumCount"></a>
				<a>&nbsp;&nbsp;折扣:&nbsp;&nbsp;</a><a style="color:blue" id="discount"></a>
				<a>&nbsp;&nbsp;运费:&nbsp;&nbsp;</a><a style="color:blue" id="fee"></a>
				<a>&nbsp;&nbsp;<?php echo $thisResource->comTotalGross ?>:&nbsp;&nbsp;</a><a style="text-decoration: line-through;" id="totalCost"></a><a style="color:blue" id="sumCost"></a>
			</div>
		</div>
<!-- Search result table -->
		<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-8">	
		<table id="table" class="table-sm" data-toggle="table" data-single-select="true" data-click-to-select="true">			
			<thead class="thead-light">
				<tr>
				<th class="p-1" data-field="id" data-width="0" data-width-unit="%" data-visible="false"></th>
				<th class="p-1" data-field="idx_checkbox" data-width="4" data-width-unit="%" data-halign="center"><input type="checkbox" onclick="selectAll($(this).is(':checked'))"></th>
				<th class="p-1" data-field="idx_date" data-width="24" data-width-unit="%" data-sortable="true"><?php echo $thisResource->comTime ?></th>
				<th class="p-1" data-field="idx_no" data-width="24" data-width-unit="%" data-sortable="true"><?php echo $thisResource->comPurchaseNo ?></th>
				<th class="p-1" data-field="idx_name" data-width="24" data-width-unit="%" data-sortable="true"><?php echo $thisResource->comSupplier ?></th>
				<th class="p-1" data-field="idx_count" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true"><?php echo $thisResource->comQuantity ?></th>
				<th class="p-1" data-field="idx_total" data-width="24" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true"><?php echo $thisResource->comValue ?></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		</div>
		</div>

		
	</div> <!-- End of container -->

</body>

<script src="js/ajax.js"></script>
<script src="js/autocomplete.js"></script>
<script src="js/modalSelTime.js?012901"></script>
<script>
var myRes = <?php echo json_encode($thisResource) ?>;
var purs = new Array(), purCount = 0;
var $table = $("#table");
var link = "getPurs.php";
var sortCol = "p_date", sortOp = 1;
var countTotal = 0, costTotal = 0, fee = 0, sumTotal = 0;
// i_code
var a_icode = JSON.parse(localStorage.getItem("a_icode"));
var a_image = JSON.parse(localStorage.getItem("a_image"));
// suppliers
var sups = <?php echo json_encode($mySuppliers) ?>;
var sId = "";
var ispay = -1;
function selectAll(st){
	$(".sel_checkbox").prop("checked", st);
}
function setPayOrder(st){
	console.log(st);
	var sThisVal = "";
	$('input:checkbox.sel_checkbox').each(function () {
		if(this.checked) {
			if(sThisVal == "") sThisVal = $(this).val();
			else sThisVal = sThisVal + "," +$(this).val();
		}
	});
	if(sThisVal == ""){
		alert("请选择进货单!");
		return;
	}
	var link = "postPurGroupPay.php";
	var form = new FormData();
	form.append('isPayed', st);
	form.append('pid', sThisVal);
	postRequest(link, form, searchPurs, error);
}
function error(result){
	
}

function getSupIdByName(name) {	
	if (name == myRes['comSupplierAll'])
		return "";
	if (name == myRes['comSupplierUnknwon'])
		return "0";

	for (var i=0; i<sups.length; i++) {
		if (sups[i]['s_name'] == name) {
			return sups[i]['s_id'];
			break;
		}
	}
	
	return "0";
}
function getSupNameById(sid) {
	if (sid == "")
		return myRes['comSupplierAll'];
	for (var i=0; i<sups.length; i++) {
		if (sups[i]['s_id'] == sid)
			return sups[i]['s_name'];
	}
	
	return myRes['comSupplierUnknwon'];
}

$table.bootstrapTable({   
	formatNoMatches: function () {
         return myRes['sysMsgNoRecord'];
    }
});

// Display summary
function displaySum(){
	document.getElementById("purCount").innerText = purCount;
	document.getElementById("sumCount").innerText = countTotal;
	document.getElementById("sumCost").innerText = " "+costTotal.toFixed(2);
	var discount = costTotal - sumTotal - fee;
	var discountRate = 0;
	if(sumTotal > 0) discountRate = ((-1 * discount) / sumTotal) * 100;
	document.getElementById("discount").innerText = discount.toFixed(2) + "("+discountRate.toFixed(2)+"%)";
	document.getElementById("fee").innerText = fee.toFixed(2);
	if(sumTotal != costTotal)
		document.getElementById("totalCost").innerText = sumTotal.toFixed(2);
	else
		document.getElementById("totalCost").innerText = "";
}
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
function printFile(){
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
	output += '<a style="font-size:12px">Vom&nbsp;'+convertDate(timeRange['timefrom'])+'&nbsp;bis&nbsp;'+convertDate(timeRange['timeto'])+'&nbsp;Bestellungsliste (Lieferant)</a><br>';
	output += '</td>';
	output += '</tr></table>';
	// Articles
	output += '<table width="100%" cellpadding="2" cellspacing="0" style="border:1px solid;"><thead>';
	output += '<tr style="font-size:12px;">';
	output += '<th align="center" style="border-left:1px solid;">Datum</th>';
	output += '<th align="center" style="border-left:1px solid;">Nr.</th>';
	output += '<th align="left" style="border-left:1px solid;">Firma</th>';
	output += '<th align="left" style="border-left:1px solid;">Menge</th>';
	output += '<th align="left" style="border-left:1px solid;">Gesamtbetrag</th>';
	output += '</tr></thead><tbody>';
	for (var i=0; i<purCount; i++) {

		if(ispay == 1){
			if(purs[i]['isPayed'] == 0) continue;
		}else if(ispay == 0){
			if(purs[i]['isPayed'] == 1) continue;
		}

		
		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+convertDate(purs[i]['p_date'].substring(0,10))+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+purs[i]['p_code']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+purs[i]['k_name']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+purs[i]['count_sum']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+purs[i]['total_sum'].toFixed(2)+'</td>';
		output += '</tr>';
	}
	output += '<tr style="font-size:12px;">';
	output += '<td align="right" style="padding:1px; border-left:1px solid; border-top:1px solid;" colspan="3">Gesamtsumme&nbsp;</td>';
	output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+countTotal+'</td>';
	output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+costTotal.toFixed(2)+'</td>';

	output += '</tr>';
	output += '</tbody></table>';
	// Footer
	output += '<a style="font-size:12px">Datum:&nbsp;'+dt+'</a>';
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
function loadTable(){
	countTotal = 0;
	costTotal = 0;
	fee = 0;
	sumTotal = 0;
	if (purCount <= 0) {
		displaySum();
		return;
	}
	for(var i=0; i<purCount; i++){
		purs[i]['k_name'] = getSupNameById(purs[i]['s_id']);
	}	
	purs.sort(sortTable(sortCol, sortOp));
	$table.bootstrapTable('removeAll');
	var rows = [];
	for(var i=0; i<purCount; i++){

		if(ispay == 1){
			if(purs[i]['isPayed'] == 0) continue;
		}else if(ispay == 0){
			if(purs[i]['isPayed'] == 1) continue;
		}

		if(purs[i]['discount'] == null) purs[i]['discount'] = 0;
		if(purs[i]['fee'] == null) purs[i]['fee'] = 0;
		purs[i]['total_sum'] = (parseFloat(purs[i]['cost_sum']) * (100 - parseFloat(purs[i]['discount']))) / 100 + parseFloat(purs[i]['fee']);

		var checkboxStr = "<input type='checkbox' value='"+purs[i]['p_id']+"' class='sel_checkbox' >";
		rows.push({
			id: purs[i]['p_id'],
			idx_checkbox: checkboxStr,
			idx_date: purs[i]['p_date'].substring(0,10),
			idx_no: purs[i]['p_code'],
			idx_name: purs[i]['k_name'],
			idx_count: purs[i]['count_sum'],
			idx_total: purs[i]['total_sum'].toFixed(2)
		});
		countTotal += parseInt(purs[i]['count_sum']);
		costTotal += parseFloat(purs[i]['total_sum']);
		sumTotal += parseFloat(purs[i]['cost_sum']);
		fee += parseFloat(purs[i]['fee']);
	}
	$table.bootstrapTable('append', rows);	

	var index = 0;
	for(var i=0; i<purCount; i++){
		if(ispay == 1){
			if(purs[i]['isPayed'] == 0) continue;
		}else if(ispay == 0){
			if(purs[i]['isPayed'] == 1) continue;
		}
		if(purs[i]['isPayed'] == 0){
			//----unpay set color----//
			$table.find("tr:nth-child("+(index+1)+")").css("color","red");
		}
		index++;
	}

	displaySum();
	// maintain previous scroll position
	var pos = localStorage.getItem("pur_mgt_scrolltop");
	document.documentElement.scrollTop = pos;
	localStorage.setItem("pur_mgt_scrolltop", 0)
}

function sortTable(key, option){
    return function(a, b){ 
		var x = a[key]; var y = b[key];
		if (key == "p_date") {
			var x1 = a[key].substring(0,10); var x2 = a[key].substring(11,19); x = x1+"T"+x2; x = new Date(x); 
			var y1 = b[key].substring(0,10); var y2 = b[key].substring(11,19); y = y1+"T"+y2; y = new Date(y);
		}
		if (key == "count_sum") {
			x= parseInt(x); y = parseInt(y);
		}
		if (key == "cost_sum") {
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
	purs = result;
	purCount = purs.length;
	loadTable();
}

function displayNo(result) {
	purs = null;
	purCount = 0;
	$table.bootstrapTable('removeAll');
	countTotal = 0;
	costTotal = 0;
	fee = 0;
	sumTotal = 0;
	displaySum();
}

function searchPurs(){
	var timeResult = mdstGetValue(0);
	var code = document.getElementById("i_code").value;
	var link = "getPurs.php?";
	
	if (timeResult != "")
		link += timeResult;
	if (sId != "")
		link += "&s_id="+sId;
	if (code != "")
		link += "&i_code="+code;
	getRequest(link, afterSearch, displayNo);
}

/****************************************************************************
	INIT
****************************************************************************/
$(document).ready(function(){

	if(localStorage.getItem("purPaySearch") == "0"){ // not pay
		document.getElementById("btnisPay").innerText = "未付";
		ispay = 0;
	}else if(localStorage.getItem("purPaySearch") == "1"){ // payed
		document.getElementById("btnisPay").innerText = "已付";
		ispay = 1;
	}

	document.getElementById("myTitle").innerHTML = myRes['comPurchase'];
	autocomplete_like(document.getElementById("i_code"), a_icode, a_image);
	// time
	var timeChecked = localStorage.getItem("pur_mgt_timecheck");
	if (timeChecked == null)
		mdstSetChecked("timeThisMonth");
	else
		mdstSetChecked(timeChecked);
	var timeStr = mdstGetStr();	
	document.getElementById("selTime").innerText = timeStr;
	// supplier
	sId = localStorage.getItem("pur_mgt_supid");
	if (sId == null)
		sId = "";
	document.getElementById("s_name").innerText = getSupNameById(sId);
	// i_code
	var code = localStorage.getItem("pur_mgt_i_code");
	if (code == null)
		code = "";
	document.getElementById("i_code").value = code;
	// sort	
	sortCol = localStorage.getItem("pur_mgt_sortcol"); 
	sortOp = localStorage.getItem("pur_mgt_sortop");
	if (sortCol == null)
		sortCol = "p_date";
	if (sortOp == null)
		sortOp = 1;
	// search
	searchPurs();
 });

// Prevent 'enter' key for submission, only enabled for barcode input
$('form input').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
		return false;
    }
});


function selisPay(e, typ) {
	var type_value = $(e).text();
	document.getElementById("btnisPay").innerText = type_value;
	ispay = typ;
	loadTable();
	localStorage.setItem("purPaySearch", typ);
}

/****************************************************************************
	View purchase
****************************************************************************/
$('#table').on('click-row.bs.table', function (e, row, $element, column) {

	if(column == "idx_checkbox") return;

	// save the current scroll position
	var pos =  document.documentElement.scrollTop;
	localStorage.setItem("pur_mgt_scrolltop", pos);
	// view purchase
	var url = "purchase.php?back=pur_mgt&p_id="+row.id;
	window.location.assign(url);
});

/****************************************************************************
	SORT
****************************************************************************/
$('#table').on('sort.bs.table', function (e, name, order) {
	switch(name) {
		case "idx_date": sortCol = 'p_date';  break;
		case "idx_name": sortCol = 'k_name';  break;
		case "idx_count": sortCol = 'count_sum';  break;
		case "idx_total": sortCol = 'cost_sum';  break;
		default: sortCol = "p_date"; 
	}
	if (order == "asc")
		sortOp = 0;
	else
		sortOp = 1;
	localStorage.setItem("pur_mgt_sortcol", sortCol);
	localStorage.setItem("pur_mgt_sortop", sortOp); 
});

/****************************************************************************
	Filter by time
****************************************************************************/
function selectTime(){
	$modalSelTime.modal();	
}

function mdstDoneTime(){
	$modalSelTime.modal("toggle");	
	 
	var timeStr = mdstGetStr();		
	document.getElementById("selTime").innerText = timeStr;
	// save time option
	var timeChecked = mdstGetChecked();
	localStorage.setItem("pur_mgt_timecheck", timeChecked);
	
	searchPurs();
}

/****************************************************************************
	New purchase
****************************************************************************/
function newPur() {
	var url = "purchase.php?back=pur_mgt";
	window.location.assign(url);
}

/****************************************************************************
	Filter by supplier
****************************************************************************/
function filterSup(e) {
	var x = $(e).text();
	document.getElementById("s_name").innerText = x;
	sId = getSupIdByName(x);
	localStorage.setItem("pur_mgt_supid", sId);
	
	searchPurs();
}

/****************************************************************************
	Search by i_code
****************************************************************************/
function doneAutocomp() {
	var code = document.getElementById("i_code").value;
	if (code == "")
		return;
	localStorage.setItem("pur_mgt_i_code", code);
	
	searchPurs();
}

function allCode() {
	document.getElementById("i_code").value = "";
	localStorage.setItem("pur_mgt_i_code", "");
	
	searchPurs();
}
/**
 * Filter funktion
 */
function filterFunction(obj) {
  var input, filter, ul, li, a, i;
  input = document.getElementById(obj.attr("id"));
  filter = input.value.toUpperCase();
  div = input.parentNode;
  a = div.getElementsByTagName("a");
  for (i = 0; i < a.length; i++) {
    txtValue = a[i].textContent || a[i].innerText;
    if (txtValue.toUpperCase().indexOf(filter) > -1) {
      a[i].style.display = "";
    } else {
      a[i].style.display = "none";
    }
  }
}
</script>

</html>
