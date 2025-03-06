<?php
/************************************************************************************
	File:		a_purmgt.php
	Purpose:	INVOICE PURCHASE
	
	2021-02-19: new purchase time settings
	2021-02-20: support unit; added purchase deletion
************************************************************************************/

// Start session
session_start();
if(!$_SESSION['uId'])
	header("Location:alogin.php");

// Include files
include 'resource.php';
include 'db_functions.php';
include 'db_invoice.php';

// Init variables
$thisResource = new myResource($_SESSION['uLanguage']);
$mySuppliers = dbQueryAllSuppliers();
$myCompany= dbQueryCompany();
$myArts = dbQueryArticles();

?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>
	<title>EUIMS - PURCHASE</title>
</head>
<style>
.dropdown-menu{
    max-height: 300px;
    overflow-y: scroll;
}
.modal-dialog {
	font-size: 14px;
}
</style>
<body>
	<?php include 'include/a_nav.php' ?>
	<?php include "include/modalSelTime.php" ?>
	<?php include "include/modalDel.php" ?>
	<div class="container">	
<!-- buttons -->
		<div class="row">
			<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8">
				<button type="button" class="btn btn-outline-secondary" id="selTime" onclick="selectTime()" style="width:220px">
					<?php echo $thisResource->mdstRdThisMonth ?></button>
				<button type="button" class="ml-1 btn btn-outline-secondary dropdown-toggle" id="s_name" data-toggle="dropdown" style="width:220px">
					全部厂家</button>
				<div class="dropdown-menu">
				<input type="text" style="position: sticky; top: 0; margin-left: 20px; margin-right: 20px; width: calc(100% - 40px);" class="form-control" placeholder="搜索.." id="myinput" oninput="filterFunction($(this))">
					<a class="dropdown-item" href="#" onclick="filterSup(this)">全部厂家</a>
					<a class="dropdown-item" href="#" onclick="filterSup(this)">未知厂家</a>
					<?php for($i=0; $i<count($mySuppliers); $i++)
						echo "<a class='dropdown-item' href='#' onclick='filterSup(this)'>".$mySuppliers[$i]['s_name']."</a>";
					?>
				</div>
				<div class="dropdown">
					<button type="button" class="ml-1 btn btn-outline-secondary dropdown-toggle" id="s_isPayed" data-toggle="dropdown" style="width:220px">
						全部</button>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="#" onclick="filterPay(this, -1)">全部</a>
						<a class="dropdown-item" href="#" onclick="filterPay(this, 0)">未付</a>
						<a class="dropdown-item" href="#" onclick="filterPay(this, 1)">已付</a>
					</div>
				</div>
			</div>
			<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-4" align="right">						
				<button type="button" class="btn btn-secondary" id="btnExport" onclick="exportFile()"><span class='fa fa-floppy-o'></button>
				<button type="button" class="btn btn-secondary" id="btnPrint" onclick="printFile()"><span class='fa fa-print'></button>
				<button type="button" class="btn btn-primary" onclick="newPur()"><span class='fa fa-plus'></button>
			</div>
		</div>	
<!-- Search result table -->
		<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12">	
		<table id="table" class="table-sm" data-toggle="table" data-single-select="true" data-click-to-select="true" data-height="480">
			<thead class="thead-light">
				<tr>
				<th class="p-1" data-field="id" data-width="" data-width-unit="%" data-visible="false"></th>
				<th class="p-1" data-field="idx_no" data-width="10" data-width-unit="%" data-halign="center" data-align="left" data-sortable="true">发票号</th>	
				<th class="p-1" data-field="idx_date" data-width="10" data-width-unit="%" data-halign="center" data-align="center" data-sortable="true">发票时间</th>
				<th class="p-1" data-field="idx_paidDatum" data-width="20" data-width-unit="%" data-halign="center" data-align="center" data-sortable="true">付款时间</th>
				<th class="p-1" data-field="idx_sup" data-width="40" data-width-unit="%" data-halign="center" data-sortable="true">厂家</th>	
				<th class="p-1" data-field="idx_count" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">件数</th>
				<th class="p-1" data-field="idx_total" data-width="10" data-width-unit="%" data-halign="center" data-align="right" data-sortable="true">金额</th>
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
				<a>&nbsp;&nbsp;总金额:&nbsp;&nbsp;</a><a style="color:blue" id="sumCost"></a>
			</div>
		</div>
		
	</div> <!-- End of container -->

<!-- Modal: modalPur -->
<div class="modal fade" id="modalPur" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-body">
			<input type="text" class="form-control" name="mdp_id" id="mdp_id" hidden>
			<input type="text" class="form-control" name="mdp_s_id" id="mdp_s_id" hidden>
			<div class="container">
			<div class="row">
				<div class="input-group p-1">
					<div class="input-group-prepend"><span class="input-group-text" style="font-size:14px;">厂家</span></div>
					<input type="text" class="form-control" style="font-size:14px;" name="mdp_s_name" id="mdp_s_name" readonly>
					<div class="input-group-append">
						<div class="dropdown dropleft">
							<button type="button" id="mdpBtnSup" class="ml-1 btn btn-outline-secondary dropdown-toggle" style="font-size:14px;" data-toggle="dropdown">选择厂家</button>
							<ul class="dropdown-menu">
							<input type="text" style="position: sticky; top: 0; margin-left: 20px; margin-right: 20px; width: calc(100% - 40px);" class="form-control" placeholder="搜索.." id="myinput_2" oninput="filterFunction($(this))">
							<?php for($i=0; $i<count($mySuppliers); $i++)
								echo "<a class='dropdown-item' href='#' onclick='selSup(this)'>".$mySuppliers[$i]['s_name']."</a>";
							?>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="input-group p-1">
					<div class="input-group-prepend"><span class="input-group-text" style="font-size:14px;">编号</span></div>
					<input type="text" class="form-control" style="font-size:14px;" name="mdp_p_id" id="mdp_p_id">
					<div class="input-group-prepend ml-2"><span class="input-group-text" style="font-size:14px;">时间</span></div>
					<input type="date" class="form-control" style="font-size:14px;" id="mdp_date" name="mdp_date" value="<?php echo date('Y-m-d') ?>">
				</div>
			</div>
			</div>
			<hr>
			<div class="container">
			<div class="row">
				<div class="input-group p-1">
					<div class="input-group-prepend"><span class="input-group-text" style="font-size:14px;">名称</span></div>
					<input type="text" class="form-control autocomplete" style="font-size:14px;" name="mdp_a_name" id="mdp_a_name">
					<div class="input-group-append">
						<div class="dropdown dropleft">
							<button type="button" id="mdpBtnName" class="ml-1 btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" style="font-size:14px;">选择商品</button>
							<ul class="dropdown-menu">
							<?php for($i=0; $i<count($myArts); $i++)
								echo "<a class='dropdown-item' href='#' onclick='selArt(this)'>".$myArts[$i]['a_name']."</a>";
							?>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="input-group p-1">
					<div class="input-group-prepend"><span class="input-group-text" style="font-size:14px;">数量</span></div>
					<input type="number" style="font-size:14px;" min="0" step="1" class="form-control" name="mdp_count" id="mdp_count">
					<div class="ml-1 input-group-prepend"><span class="input-group-text" style="font-size:14px;">单位</span></div>
					<input type="number" style="font-size:14px;" min="0" step="1" class="form-control" name="mdp_unit" id="mdp_unit">
					<div class="ml-1 input-group-prepend"><span class="input-group-text" style="font-size:14px;">进价</span></div>
					<input type="number" style="font-size:14px;" min="0" step="0.01" class="form-control" name="mdp_cost" id="mdp_cost">
					<button type="button" style="font-size:14px;" id="mdpBtnAdd" class="ml-1 btn btn-primary" onclick="mdpAddItem()"><span class='fa fa-plus'></button>
				</div>
			</div>		
			<div class="row">
				<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12">
					<table id="tablePur" class="table-sm" data-toggle="table" 
						data-single-select="true" data-click-to-select="true" data-unique-id="id" data-height="250">
						<thead class="thead-light">
							<tr>
							<th class="p-1" data-field="id" data-width="0" data-width-unit="%" data-visible="false"></th>
							<th class="p-1" data-field="idx_name" data-width="40" data-width-unit="%">名称</th>
							<th class="p-1" data-field="idx_count" data-width="15" data-width-unit="%">件数</th>
							<th class="p-1" data-field="idx_cost" data-width="15" data-width-unit="%">进价</th>
							<th class="p-1" data-field="idx_total" data-width="20" data-width-unit="%">小计</th>
							<th class="p-1" data-field="idx_del" data-width="10" data-width-unit="%"></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
			</div>
		</div>
		<div class="row">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="font-size:14px;">折扣(%)</span></div>
				<input type="number" style="font-size:14px;" min="0" step="0.01" class="form-control" name="mdp_discount" id="mdp_discount">
				<div class="input-group-prepend ml-1"><span class="input-group-text" style="font-size:14px;">费用</span></div>
				<input type="number" style="font-size:14px;" min="0" step="0.01" class="form-control" name="mdp_fee" id="mdp_fee">
				<div class="input-group-prepend ml-1"><span class="input-group-text" style="font-size:14px;">税率(%)</span></div>
				<input type="number" style="font-size:14px;" min="0" step="0.01" class="form-control" name="mdp_tax" id="mdp_tax">				
			</div>
		</div>
		<div class="row">
			<div class="input-group p-1">
				<div class="dropdown">
					<button type="button" class="btn btn-secondary dropdown-toggle" style="font-size:14px;" id="mdp_pay" data-toggle="dropdown">付款方式</button>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="#" onclick="mdSelPay(this)">现金</a>
						<a class="dropdown-item" href="#" onclick="mdSelPay(this)">转账</a>
						<a class="dropdown-item" href="#" onclick="mdSelPay(this)">其他</a>
					</div>
				</div>
				<button type="button" class="ml-1 btn btn-primary" style="font-size:14px;" id="mdpBtnCal" onclick="mdpCalSum()">总计</button>
				<input type="number" style="font-size:14px;" class="form-control" name="mdp_total" id="mdp_total" readonly>
			</div>
		</div>



		<div class="row">
			<div class="input-group p-1">
			<div class="form-check ml-3 pt-2">
					<input class="form-check-input" type="radio" value="1" name="isPayed" id="mdp_isPayed" onclick="if($('#mdp_paidDatum').val()=='') $('#mdp_paidDatum').val(currentDate(2))">
					<label class="form-check-label" for="mdp_isPayed" onclick="if($('#mdp_paidDatum').val()=='') $('#mdp_paidDatum').val(currentDate(2))">
						已付
					</label>
				</div>
				<div class="form-check ml-3 pt-2">
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
		<hr>
		<div class="container">
		<div class="row">
			<div class="p-1 col-9 col-sm-9 col-md-9 col-lg-9">
				<button type="button" class="btn btn-danger" id="mdpBtnDestroy" onclick="mdpDestroy()"><span class='fa fa-trash'></button>
			</div>
			<div class="p-1 col-3 col-sm-3 col-md-3 col-lg-3" align="right">
				<button type="button" class="btn btn-secondary" id="mdpBtnClose" data-dismiss="modal"><span class='fa fa-times'></button>
				<button type="button" class="btn btn-primary" id="mdpBtnDone" onclick="mdpDone()"><span class='fa fa-check'></button>
			</div>
		</div>
		</div>
		</div>
	</div>
</div> <!-- end of modalArt -->

</body>
<script src="js/sysfunc.js?v1"></script>
<script src="js/ajax.js"></script>
<script src="js/autocomplete.js"></script>
<script src="js/modalSelTime.js?202106212113"></script>
<script src="js/modalDel.js?<?= rand() ?>"></script>

<script>
var company = <?php echo json_encode($myCompany) ?>;
var myArts = <?php echo json_encode($myArts) ?>;
var sups = <?php echo json_encode($mySuppliers) ?>;
var purs = [], purCount = 0;
var $table = $("#table");
var countTotal = 0, costTotal = 0;
var sId = "";
var sPay = -1;

var $modalPur = $("#modalPur");
var $tablePur = $('#tablePur');
var myPur = {}, myPurItems = [];
var myPurItemCount = 0, myPurItemId = 0;
var purType = 0, fId = 0;
var mdTotalSum = 0, mdCrossSum = 0, mdDiscount = 0, mdFee = 0, mdTax = 0, mdPay = 0;

var a_arts = [];
for (var i=0; i<myArts.length; i++) {
	a_arts[i] = myArts[i]['a_name'];
}
autocomplete(document.getElementById("mdp_a_name"), a_arts);
/*************************************************** 
	INIT
****************************************************/
$table.bootstrapTable({   
	formatNoMatches: function () {
         return "没有符合条件的发票";
    }
});
$tablePur.bootstrapTable({   
	formatNoMatches: function () {
         return "没有发票项目";
    }
});
$(document).ready(function(){
	document.getElementById("myTitle").innerText = "进货发票列表";
	
	// Search by default
	mdstSetChecked("timeThisMonth");
	fTime = mdstGetValue(0);
	searchPurs();
 });
// Prevent 'enter' key for submission, only enabled for barcode input
$('form input').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
		return false;
    }
});
/*************************************************** 
	FUNCTIONS
****************************************************/
function getSupIdByName(name) {	
	if (name == "全部厂家")
		return "";
	if (name == "未知厂家")
		return "0";

	for (var i=0; i<sups.length; i++) {
		if (sups[i]['s_name'] == name) {
			return sups[i]['s_id'];
			break;
		}
	}
	
	return "0";
}
function getSupNameById(id) {	
	for (var i=0; i<sups.length; i++) {
		if (sups[i]['s_id'] == id) {
			return sups[i]['s_name'];
			break;
		}
	}
	
	return "";
}
function getArtNameById(id) {
	for (var i=0; i<myArts.length; i++) {
		if (myArts[i]['a_id'] == id) {
			return myArts[i]['a_name'];
		}
	}
	
	return "";
}
// Display summary
function displaySum(){
	document.getElementById("itemCount").innerText = purCount;
	document.getElementById("sumCount").innerText = countTotal;
	document.getElementById("sumCost").innerText = costTotal.toFixed(2);
}
/*************************************************** 
	LOAD TABLE
****************************************************/
function loadTable(){
	countTotal = 0;
	costTotal = 0;
	if (purCount <= 0) {
		displaySum();
		return;
	}

	$table.bootstrapTable('removeAll');
	var rows = [];
	for(var i=0; i<purCount; i++){
		purs[i]['s_name'] = getSupNameById(purs[i]['s_id']);
		rows.push({
			id: purs[i]['f_id'],
			idx_no: purs[i]['p_id'],
			idx_date: purs[i]['date'].substring(0,10),
			idx_paidDatum: purs[i]['paidDatum'],
			idx_sup: purs[i]['s_name'],
			idx_count: purs[i]['count_sum'],
			idx_total: purs[i]['total_sum']
		});
		countTotal += parseInt(purs[i]['count_sum']);
		costTotal += parseFloat(purs[i]['total_sum']);
	}
	$table.bootstrapTable('append', rows);	
	for(var i=0; i<purCount; i++){
		if(purs[i]['isPayed'] == 0){
			//----unpay set color----//
			$table.find("tr:nth-child("+(i+1)+")").css("color","red");
		}
	}
	displaySum();
}
/*************************************************** 
	SEARCH PURCHASE
****************************************************/
function searchPursYes(result){
	purs = result;
	purCount = purs.length;
	loadTable();
}
function searchPursNo(result) {
	$table.bootstrapTable('removeAll');
	purCount = 0;
	countTotal = 0;
	costTotal = 0;
	displaySum();
}
function searchPurs(){
	var timeResult = mdstGetValue();
	var link = "getAPurs.php?";
	link += timeResult;
	link += "&s_pay="+sPay;
	if (sId != "")
		link += "&s_id="+sId;
	getRequest(link, searchPursYes, searchPursNo);
}
/*************************************************** 
	TIME FILTER
****************************************************/
// Show time selection (modalTime)
function selectTime(){
	$modalSelTime.modal();	
}

// Finish time selection (modalTime)
function mdstDoneTime(){
	$modalSelTime.modal("toggle");	
	 
	var timeStr = mdstGetStr();	
	document.getElementById("selTime").innerText = timeStr;
	
	searchPurs();
}
/*************************************************** 
	SUPPLIER
****************************************************/
function filterSup(e) {
	var x = $(e).text();
	document.getElementById("s_name").innerText = x;

	sId = getSupIdByName(x);
	
	searchPurs();
}
/*************************************************** 
	IS Payed Filter
****************************************************/
function filterPay(e, typ) {
	var x = $(e).text();
	document.getElementById("s_isPayed").innerText = x;
	sPay = typ;
	searchPurs();
}
/*************************************************** 
	NEW PURCHASE
****************************************************/
function newPur() {
	purType = 0;
	
	document.getElementById("mdp_id").value = "";
	document.getElementById("mdp_s_id").value = "";
	document.getElementById("mdp_s_name").value = "";
	document.getElementById("mdp_p_id").value = "";	
	document.getElementById("mdp_date").value = currentDate(2);		
	setDateMax();
	resetItemInput();
	
	$tablePur.bootstrapTable('removeAll');
	
	myPur = null;
	myPurItems = new Array();
	myPurItemCount = 0;
	
	mdCrossSum = 0;
	mdTotalSum = 0;
	mdDiscount = 0;
	mdFee = 0;
	mdTax = 0;
	mdDisplayCal();
	mdDisplaySum();
	
	document.getElementById("mdp_isPayed").checked = true;
	document.getElementById("mdp_paidDatum").value = currentDate(2);
	$modalPur.modal();
}
function setDateMax() {
	var now = new Date(),
    maxDate = now.toISOString().substring(0,10);
	$('#mdp_date').prop('max', maxDate);
}
function resetItemInput() {
	document.getElementById("mdp_a_name").value = "";
	document.getElementById("mdp_count").value = "";
	document.getElementById("mdp_unit").value = "1";
	document.getElementById("mdp_cost").value = "";
}
function selSup(e) {
	var x = $(e).text();
	document.getElementById("mdp_s_name").value = x;
}
function selArt(e) {
	var x = $(e).text();
	document.getElementById("mdp_a_name").value = x;
}
// add item
function mdpAddItem() {
	var i = 0;
	// check a_name
	var name = document.getElementById("mdp_a_name").value;
	if (name == "") {
		$('#mdp_a_name').trigger('focus');
		return;
	}
	var aid = "";
	for (i=0; i<myArts.length; i++) {
		if (myArts[i]['a_name'] == name) {
			aid = myArts[i]['a_id'];
			break;
		}
	}
	if (aid == "") {
		$('#mdp_a_name').trigger('focus');
		return;
	}
/*
	var aidExist = false;
	for (i=0; i<myPurItemCount; i++) {
		if (myPurItems[i]['a_id'] == aid) {
			aidExist = true;
			break;
		}
	}
	if (aidExist) {
		alert("该项目已存在");
		return;
	}
*/
	// check count, unit, cost
	var count = document.getElementById("mdp_count").value;
	if (count == "" || parseInt(count) <= 0) {
		$('#mdp_count').trigger('focus');
		return;
	}
	count = parseInt(count).toString();
	var unit = document.getElementById("mdp_unit").value;
	if (unit == "" || parseInt(unit) <= 0) {
		$('#mdp_unit').trigger('focus');
		return;
	}
	unit = parseInt(unit).toString();
	var cost = document.getElementById("mdp_cost").value;
	if (cost == "" || parseFloat(cost) <= 0) {
		$('#mdp_cost').trigger('focus');
		return;
	}
	cost = parseFloat(cost).toFixed(2);
	// add item to table
	var delStr = "<button type='button' class='btn btn-outline-secondary' id='btnDelItem"+myPurItemId+"' onclick='mdpDelItem(this)'><span class='fa fa-trash'></button>";	
	var countStr, real_count, subtotal;
	if (unit == "1") {
		countStr = count;
		real_count = count;
	} else {
		countStr = count+" (x"+unit+")";
		real_count = (parseInt(count)*parseInt(unit)).toString();		
	}
	subtotal = parseInt(real_count)*parseFloat(cost);
	var rows = [];
	rows.push({
		id: myPurItemId,
		idx_name: name,
		idx_count: countStr,
		idx_cost: cost,
		idx_total: subtotal.toFixed(2),
		idx_del: delStr
	});		
	$tablePur.bootstrapTable('append', rows);
	// add new item
	var item = new Object();
	item['id'] = myPurItemId;
	item['a_id'] = aid;
	item['count'] = count;
	item['unit'] = unit;
	item['real_count'] = real_count;
	item['cost'] = cost;
	item['subtotal'] = subtotal.toFixed(2);
	myPurItems[myPurItemCount] = item;
	myPurItemCount++;
	myPurItemId++;
	resetItemInput();
	mdCrossSum += subtotal;
	mdDisplaySum();
}
// display total
function mdDisplaySum() {
	mdTotalSum = (mdCrossSum*(1-mdDiscount/100)+mdFee)*(1+mdTax/100);
	document.getElementById("mdp_total").value = mdTotalSum.toFixed(2);
}
// get data 
function mdGetData() {
	var disc = document.getElementById("mdp_discount").value;
	if (disc == "") disc = "0";
	if (parseFloat(disc) < 0 || parseFloat(disc) > 100) {
		$('#mdp_discount').trigger('focus');
		return -1;
	}		
	mdDiscount = parseFloat(disc);
	
	var fee = document.getElementById("mdp_fee").value;
	if (fee == "") fee = "0";
	if (parseFloat(fee) < 0) {
		$('#mdp_fee').trigger('focus');
		return -1;
	}
	mdFee = parseFloat(fee);
	
	var tax = document.getElementById("mdp_tax").value;
	if (tax == "") tax = "0";
	if (parseFloat(tax) < 0 || parseFloat(tax) > 100) {
		$('#mdp_tax').trigger('focus');
		return -1;
	}
	mdTax = parseFloat(tax);
	
	return 0;
}
// calculate total
function mdpCalSum() {
	if (mdGetData() < 0)
		return;	
	mdDisplaySum();	
}
// display cal
function mdDisplayCal() {
	document.getElementById("mdp_discount").value = mdDiscount.toFixed(2);
	document.getElementById("mdp_fee").value = mdFee.toFixed(2);
	document.getElementById("mdp_tax").value = mdTax.toFixed(2);
	switch(mdPay) {
		case 1: document.getElementById("mdp_pay").innerText = "现金"; break;
		case 2: document.getElementById("mdp_pay").innerText = "转账"; break;
		case 3: document.getElementById("mdp_pay").innerText = "其他"; break;
		default: document.getElementById("mdp_pay").innerText = "付款方式";
	}
}
// payment
function mdSelPay(e) {
	var x = $(e).text();
	document.getElementById("mdp_pay").innerText = x;
	switch(x) {
		case "现金": mdPay = 1;break;
		case "转账": mdPay = 2;break;
		case "其他": mdPay = 3;break;
		default: mdPay = 0;
	}
}
// delete item
var del_id = "";
function mdpDelItem(e) {
	$modalPur.modal("toggle");
	del_id = $(e).attr("id");
	showDelModal(mdpDelItem_2, $modalPur);
}
function mdpDelItem_2(){
	//var id = $(e).attr("id");
	var id = del_id;
	id = id.replace("btnDelItem","");
	for (var i=0; i<myPurItemCount; i++) {
		if (myPurItems[i]['id'] == id) { 
			mdCrossSum = mdCrossSum - parseFloat(myPurItems[i]['subtotal']);
			myPurItems.splice(i,1);
			myPurItemCount--;			
			break;
		}
	}	
	$tablePur.bootstrapTable('removeByUniqueId', id);
	mdDisplaySum();	
	$modalPur.modal("toggle");
}
// save purchase
function mdpDone() {
	if (myPurItemCount <= 0) {
		alert("发票没有内容");
		return;
	}	
	var s_name = document.getElementById("mdp_s_name").value;
	if (s_name == "") {
		$('#mdp_s_name').trigger('focus');
		return;
	}
	var s_id = getSupIdByName(s_name);
	if (s_id == "0" || s_id == "") {
		$('#mdp_s_name').trigger('focus');
		return;
	}
	var p_id = document.getElementById("mdp_p_id").value;
	if (p_id == "") {
		$('#mdp_p_id').trigger('focus');
		return;
	}
	var date = document.getElementById("mdp_date").value;
	if (mdpCalSum() < 0)
		return;
	mdDisplaySum();
	// database
	myPur = new Object();
	myPur['p_id'] = p_id;
	myPur['s_id'] = s_id;
	myPur['date'] = date;
	var countSum = 0, costSum = 0;
	for (var i=0; i<myPurItemCount; i++) {
		countSum += parseInt(myPurItems[i]['real_count']);
	}
	myPur['count_sum'] = countSum.toString();
	myPur['cost_sum'] = mdCrossSum.toFixed(2);
	myPur['total_sum'] = mdTotalSum;
	myPur['discount'] = mdDiscount.toFixed(2);
	myPur['fee'] = mdFee.toFixed(2);
	myPur['tax'] = mdTax.toFixed(2);
	myPur['payment'] = mdPay.toFixed(2);

	myPur['isPayed'] = 0;
	myPur['paidDatum'] = "";
	if(document.getElementById("mdp_isPayed").checked == true){
		myPur['isPayed'] = 1;
		myPur['paidDatum'] = document.getElementById("mdp_paidDatum").value;
	}

	if (purType == 1) {
		var form = new FormData();
		form.append('f_id', fId);
		postRequest("postAPurDel.php", form, dbDelPurYes, dbDelPurNo);
	} else {
		dbAddPur();
	}
}
// database actions
function dbDelPurYes(result) {
	dbAddPur();
}
function dbDelPurNo(result) {
	alert("进货发票保存错误");
}
function dbAddPur() {
	var form = new FormData();
	form.append('pur', JSON.stringify(myPur));
	form.append('puritems', JSON.stringify(myPurItems));
	postRequest("postAPurNew.php", form, dbAddPurYes, dbAddPurNo);
}
function dbAddPurYes(result) {
	$modalPur.modal("toggle");
	searchPurs();	
}
function dbAddPurNo(result) {
	alert("保存发票出现错误");
}
/*************************************************** 
	DELETE PURCHASE
****************************************************/
function mdpDestroy() {
	if (myPur == null) {
		$modalPur.modal("toggle");
		return;
	}
	if (!confirm("确定要删除该发票?"))
		return;
	dbDestroyPur();
}
function dbDestroyPur() {
	var form = new FormData();
	form.append('f_id', fId);
	postRequest("postAPurDel.php", form, dbDestroyPurYes, dbDestroyPurNo);
}
function dbDestroyPurYes(result) {
	$modalPur.modal("toggle");	
	searchPurs();	
}
function dbDestroyPurNo(result) {
	alert("删除发票出现错误");
}
/*************************************************** 
	VIEW/EDIT PURCHASE
****************************************************/
// Click a row to view product
$('#table').on('click-row.bs.table', function (e, row, $element) {	
	queryPurItems(row.id);
});
function queryPurItems(fId) { 
	getRequest("getAPurItems.php?f_id="+fId, queryPurItemsYes, queryPurItemsNo);
}
function queryPurItemsYes(result) { 
	purType = 1;
	
	myPurItems = result;
	myPurItemCount = myPurItems.length;
	myPurItemId = 0;
	myPur = null; 
	mdCrossSum = 0;

	for (var i=0; i<purs.length; i++) {
		if (purs[i]['f_id'] == myPurItems[0]['f_id']) {
			myPur = purs[i];
			fId = myPur['f_id'];
			break;
		}
	}
	if (myPur == null)
		return;
	
	mdTotalSum = parseFloat(myPur['total_sum']);
	mdDiscount = parseFloat(myPur['discount']);
	mdFee = parseFloat(myPur['fee']);
	mdTax = parseFloat(myPur['tax']);
	mdPay = parseInt(myPur['payment']);
	
	document.getElementById("mdp_id").value = myPur['f_id'];
	document.getElementById("mdp_s_id").value = myPur['s_id'];
	document.getElementById("mdp_s_name").value = getSupNameById(myPur['s_id']);
	document.getElementById("mdp_p_id").value = myPur['p_id'];
	document.getElementById("mdp_date").value = convertDate(myPur['date'], 1);	
	if(myPur['isPayed'] == 1){
		document.getElementById("mdp_paidDatum").value = myPur['paidDatum'];
		document.getElementById("mdp_isPayed").checked = true;
	}
	else{
		document.getElementById("mdp_isNotPayed").checked = true;
		document.getElementById("mdp_paidDatum").value = "";	
	}
	setDateMax();
	resetItemInput();
	
	$tablePur.bootstrapTable('removeAll');
	var rows = [];	
	var countStr, subtotal;
	for (var i=0; i<myPurItemCount; i++) {
		myPurItems[i]['id'] = myPurItemId;
		var delStr = "<button type='button' class='btn btn-outline-secondary' id='btnDelItem"+myPurItemId+"' onclick='mdpDelItem(this)'><span class='fa fa-trash'></button>";
		if (myPurItems[i]['unit'] == "1") {
			countStr = myPurItems[i]['count'];
			myPurItems[i]['real_count'] = myPurItems[i]['count'];
		} else {
			countStr = myPurItems[i]['count']+" (x"+myPurItems[i]['unit']+")";
			myPurItems[i]['real_count'] = (parseInt(myPurItems[i]['count'])*parseInt(myPurItems[i]['unit'])).toString();			
		}
		subtotal = parseInt(myPurItems[i]['real_count'])*parseFloat(myPurItems[i]['cost']);		
		myPurItems[i]['subtotal'] = subtotal.toFixed(2);		
		rows.push({
			id: myPurItemId,
			idx_name: getArtNameById(myPurItems[i]['a_id']),
			idx_count: countStr,
			idx_cost: myPurItems[i]['cost'],
			idx_total: myPurItems[i]['subtotal'],
			idx_del: delStr
		});	
		mdCrossSum += subtotal;
		myPurItemId++;
	}		
	$tablePur.bootstrapTable('append', rows);	
	$modalPur.modal();
	
	mdDisplayCal();
	mdDisplaySum();
}
function queryPurItemsNo(result) {
	alert("读取发票出现错误");
}
/*************************************************** 
	EXPORT
****************************************************/
function exportFile() {
	var output = "Rechnung Nr.,Datum Rechnung,Datum Zahlung,Steuergrundlage,MwSt,Menge,Gesamtbetrag,Firma\n";
	for (var i=0; i<purCount; i++) {

		var paidDatum = purs[i]['paidDatum'];
		if(paidDatum !== null && paidDatum != "" ) paidDatum = convertDate(purs[i]['paidDatum'].substring(0,10));
		else paidDatum = "-";

		output += purs[i]['p_id']+',';
		output += convertDate(purs[i]['date'].substring(0,10))+',';
		output += paidDatum+',';
		output += purs[i]['total_sum']+',';
		output += ',';
		output += purs[i]['count_sum']+',';
		output += purs[i]['total_sum']+',';
		output += purs[i]['s_name']+'\n';
	}
	
	var a = document.createElement("a");
	var file = new Blob([output], {type: 'text/plain'});
	a.href = URL.createObjectURL(file);
	a.download = "Rechnung-Lieferant-"+currentDate(1)+".csv";
	document.body.appendChild(a);
    a.click();window.URL.revokeObjectURL(url);
    a.remove();
}
/*************************************************** 
	PRINT
****************************************************/
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
	output += '<a style="font-size:12px">Vom&nbsp;'+convertDate(timeRange['timefrom'])+'&nbsp;bis&nbsp;'+convertDate(timeRange['timeto'])+'&nbsp;Rechnungsliste (Lieferant)</a><br>';
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
	output += '<th align="left" style="border-left:1px solid;">Menge</th>';
	output += '<th align="left" style="border-left:1px solid;">Gesamtbetrag</th>';
	output += '<th align="left" style="border-left:1px solid;">Firma</th>';
	output += '</tr></thead><tbody>';
	for (var i=0; i<purCount; i++) {
		var paidDatum = purs[i]['paidDatum'];
		if(paidDatum !== null && paidDatum != "" ) paidDatum = convertDate(purs[i]['paidDatum'].substring(0,10));
		else paidDatum = "-";
		output += '<tr style="font-size:12px;">';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+purs[i]['p_id']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+convertDate(purs[i]['date'].substring(0,10))+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+paidDatum+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+'&nbsp;'+purs[i]['total_sum']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right"></td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+'&nbsp;'+purs[i]['count_sum']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+'&nbsp;'+purs[i]['total_sum']+'</td>';
		output += '<td style="padding:1px; border-left:1px solid; border-top:1px solid;">'+'&nbsp;'+purs[i]['s_name']+'</td>';
		output += '</tr>';
	}
	output += '<tr style="font-size:12px;">';
	output += '<td align="right" style="padding:1px; border-left:1px solid; border-top:1px solid;" colspan="4">Gesamtsumme&nbsp;</td>';
	output += '<td align="right" style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+costTotal.toFixed(2)+'</td>';
	output += '<td align="right" style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right"></td>';
	output += '<td align="right" style="padding:1px; border-left:1px solid; border-top:1px solid;" align="right">'+costTotal.toFixed(2)+'</td>';
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
