<?php
/************************************************************************************
	File:		inv_mgt.php
	Purpose:	product management
************************************************************************************/

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
$myInvTypes = dbQueryTypes();
$active[1] = "active";
?>

<!doctype html>
<html lang="zh">
<head>
    <?php include 'include/header.php' ?>
	<title>EUCWS - Products</title>
</head>

<style>
.dropdown-menu{
    max-height: 300px;
    overflow-y: scroll;
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
.dot {
  height: 8px;
  width: 8px;
  background-color: red;
  border-radius: 50%;
  display: inline-block;
}
</style>

<body>
	<?php include 'include/nav.php' ?>		
	<?php include "include/modalSelTime.php" ?>	
	
	<div class="container">	
<!-- buttons -->
		<div class="row">
			<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-2">
				<button type="button" class="mx-1 btn btn-secondary" id="btnFilter" onclick="showFilter()"><span class='fa fa-bars'></button>
			</div>
			<div class="input-group p-1 col-8 col-sm-8 col-md-8 col-lg-4">
				<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comProductNo ?></span></div>
				<input type="text" class="form-control" name="i_code" id="i_code" autofocus>
			</div>
			<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-2" align="right">
				<button type="button" class="mx-1 btn btn-primary" id="btnNew" onclick="newInv()"><span class='fa fa-plus'></button>
			</div>
		</div>
		<div class="row">
			<div class="p-1 col-6 col-sm-6 col-md-6 col-lg-4">
				<button type="button" class="p-1 btn btn-outline-secondary" id="selTime" onclick="selectTime()" style="width:220px">
					<?php echo $thisResource->mdstRdAll ?></button>
			</div>
			<div class="p-1 col-6 col-sm-6 col-md-6 col-lg-4" align="right">
				<div class="dropdown">
				<button type="button" class="p-1 btn btn-outline-secondary dropdown-toggle" id="sortOption" data-toggle="dropdown" style="width:130px">
					<?php echo $thisResource->comSortUpdated ?></button>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="#" onclick="doneSort(this)"><?php echo $thisResource->comSortUpdated ?></a>
						<a class="dropdown-item" href="#" onclick="doneSort(this)"><?php echo $thisResource->comSortCreated ?></a>
						<a class="dropdown-item" href="#" onclick="doneSort(this)"><?php echo $thisResource->comSortCodeAZ ?></a>
						<a class="dropdown-item" href="#" onclick="doneSort(this)"><?php echo $thisResource->comSortCodeZA ?></a>
						<a class="dropdown-item" href="#" onclick="doneSort(this)"><?php echo $thisResource->comSortCountDesc ?></a>
						<a class="dropdown-item" href="#" onclick="doneSort(this)"><?php echo $thisResource->comSortCountAsc ?></a>
						<a class="dropdown-item" href="#" onclick="doneSort(this)"><?php echo $thisResource->comSortValueDesc ?></a>
						<a class="dropdown-item" href="#" onclick="doneSort(this)"><?php echo $thisResource->comSortValueAsc ?></a>
					</div>
				</div>
			</div>
		</div>
<!-- summary -->
		<div class="row p-1">
			<div class="col-12 col-sm-12 col-md-12 col-lg-8" align="center" style="border:1px solid lightgray;">
				<a><?php echo $thisResource->comProduct ?>:&nbsp;&nbsp;</a><a style="color:blue" id="totalCount"></a>
				<a>&nbsp;&nbsp;<?php echo $thisResource->comTotalQuantity ?>:&nbsp;&nbsp;</a><a style="color:blue" id="sumCount"></a>
				<a>&nbsp;&nbsp;<?php echo $thisResource->comTotalGross ?>:&nbsp;&nbsp;</a><a style="color:blue" id="sumCost"></a>
			</div>
		</div>		
<!-- Search result table -->
		<div class="row">
			<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-8">	
			<table id="table" class="table-sm" data-toggle="table" data-single-select="true" data-click-to-select="true">
				<thead class="thead-light">
					<tr>
					<th class="p-1" data-field="idx_image" data-width="10" data-width-unit="%"></th>
					<th class="p-1" data-field="id" data-width="0" data-width-unit="%" data-visible="false"><?php echo $thisResource->comID ?></th>				
					<th class="p-1" data-field="idx_data" data-width="55" data-width-unit="%"><?php echo $thisResource->comProduct ?></th>
					<th class="p-1" data-field="idx_count" data-width="20" data-width-unit="%" data-halign="center" data-align="right"><?php echo $thisResource->comQuantity ?></th>
					<th class="p-1" data-field="idx_cost" data-width="15" data-width-unit="%" data-halign="center" data-align="right"><?php echo $thisResource->comValue ?></th>
					</tr>
				</thead>
				<tbody>
				<!-- load table by JS -->
				</tbody>
			</table>
			</div>
		</div>
		
		<div class="loader" id="loader"></div>
		
</div> <!-- End of container -->

<!-- Modal: modalFilter -->
<div class="modal fade" id="modalFilter" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdfTitle"><?php echo $thisResource->comOptions ?></b>
		</div>
		<div class="modal-body">
<!-- Suppliers -->	
		<div class="row">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comSupplier ?></span></div>
				<div class="dropdown">
				<button type="button" class="p-1 ml-1 btn btn-outline-secondary dropdown-toggle" id="s_name" data-toggle="dropdown" style="width:220px">
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
			</div>
		</div>
<!-- Types -->
		<div class="row">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comType ?></span></div>
				<div class="dropdown">
				<button type="button" class="p-1 ml-1 btn btn-outline-secondary dropdown-toggle" id="t_name" data-toggle="dropdown" style="width:220px">
					<?php echo $thisResource->comTypeAll ?></button>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="#" onclick="filterType(this)"><?php echo $thisResource->comTypeAll ?></a>
						<a class="dropdown-item" href="#" onclick="filterType(this)"><?php echo $thisResource->comTypeUnkown ?></a>
						<?php for($i=0; $i<count($myInvTypes); $i++) 
							echo "<a class='dropdown-item' href='#' onclick='filterType(this)'>".$myInvTypes[$i]['t_name']."</a>";
						?>
					</div>
				</div>
			</div>
		</div>

    <!-- Season -->
    <div class="row">
        <div class="input-group p-1">
            <div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comSeason ?></span></div>
            <div class="dropdown">
                <button type="button" class="p-1 ml-1 btn btn-outline-secondary dropdown-toggle" id="season" data-toggle="dropdown" style="width:220px">
                    <?php echo $thisResource->comSeasonAll ?></button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" onclick="filterSeason(this, -1)"><?php echo $thisResource->comSeasonAll ?></a>
                    <?php foreach($seasonArr AS $key => $value)
                        echo "<a class='dropdown-item' href='#' onclick='filterSeason(this, ".$key.")'>".$value."</a>";
                    ?>
                </div>
            </div>
        </div>
    </div>
<!-- status -->
		<div class="row">
			<div class="p-1 input-group">
				<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comStatus ?></span></div>
				<button type="button" class="p-1 ml-1 btn btn-outline-secondary dropdown-toggle" id="status_str" data-toggle="dropdown" style="width:220px">
					<?php echo $thisResource->comAll ?></button>
				<input type='hidden' id="status" value=''>
				<div class="dropdown-menu">
					<div class="dropdown-item" href="#" onclick="selStatus(this)"><?php echo $thisResource->comAll ?>
						<input type='hidden' value=''></div>
					<div class="dropdown-item" href="#" onclick="selStatus(this)"><?php echo $thisResource->comStatusNormal ?>
						<input type='hidden' value='0'></div>
					<div class="dropdown-item" href="#" onclick="selStatus(this)">未完成<input type='hidden' value='-1'></div>
					<div class="dropdown-item" href="#" onclick="selStatus(this)"><?php echo $thisResource->comStatusOffline ?>
						<input type='hidden' value='1'></div>
				</div>
			</div>
		</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-success" id="btnClearFilter" onclick="clearFilter()"><?php echo $thisResource->comAll ?></button>
			<button type="button" class="btn btn-primary" id="btnDoneFilter" onclick="doneFilter()"><span class='fa fa-check'></button>
		</div>
	</div>
	</div>
</div>

</body>

<script src="js/ajax.js"></script>
<script src="js/autocomplete.js?202109091456"></script>
<script src="js/modalSelTime.js"></script>

<script>
var myRes = <?php echo json_encode($thisResource) ?>;
var $table = $("#table");
var sortCol = "time_updated", sortOp = 1;
var CountTotal = 0, costTotal = 0, itemTotal = 0;
var fTime = new Object(), fType = "", fSup = "", fStatus = "", fSeason = "";
// Load data
var invs;
var a_icode = JSON.parse(localStorage.getItem("a_icode"));
var a_image = JSON.parse(localStorage.getItem("a_image"));
var a_sups = <?php echo json_encode($mySuppliers) ?>;
var a_types = <?php echo json_encode($myInvTypes) ?>;

var season_arr = <?php echo json_encode($seasonArr) ?>;

// Time filter
fTime['timefrom'] = "2020-01-01";
var timenow = new Date();
var monthnow = timenow.getMonth()+1;
fTime['timeto'] = timenow.getFullYear()+"-"+monthnow+"-"+timenow.getDate();
// Sys Options
var sysOptions = new Object();
sysOptions = JSON.parse(localStorage.getItem("sysOptions"));
/************************************************************************************
	INIT
************************************************************************************/
$(document).ready(function(){
	// display Title
	document.getElementById("myTitle").innerHTML = '<?php echo $thisResource->fmInvMgtTitle ?>';
	// load data for autocomplete 
	if (sysOptions != null && sysOptions['sysSearchLike'])
		autocomplete_like(document.getElementById("i_code"), a_icode, a_image);
	else
		autocomplete(document.getElementById("i_code"), a_icode, a_image);
	// load sort
	sortCol = localStorage.getItem("inv_mgt_sortcol");
	sortOp = localStorage.getItem("inv_mgt_sortop");
	document.getElementById("sortOption").innerText = getSort();
	// load filters
	fSup = localStorage.getItem("inv_mgt_sup"); 
	if (fSup == null) fSup = "";
	document.getElementById("s_name").innerHTML = getSupNameById(fSup);
	fType = localStorage.getItem("inv_mgt_type"); 
	document.getElementById("t_name").innerHTML = getTypeNameById(fType);
	if (fType == null) fType = "";
	fStatus = localStorage.getItem("inv_mgt_status"); 
	if (fStatus == null) fStatus = "";
	document.getElementById("status_str").innerHTML = getStatusNameById(fStatus);

    fSeason = localStorage.getItem("inv_mgt_season");
    if (fSeason == null) fSeason = "";
    document.getElementById("season").innerHTML = getSeasonNameById(fSeason);

	if (fSup == "" && fType == "" && fStatus == "" && fSeason == "")
		document.getElementById("btnFilter").style.border = "none";
	else
		document.getElementById("btnFilter").style.border = "2px solid red";	
	mdstSetChecked("timeAll");
	// search
	searchAll();
});
$table.bootstrapTable({   
	formatNoMatches: function () {
         return myRes['sysMsgNoRecord'];
    }
});
// Prevent 'enter' key for submission, only enabled for barcode input
$('form input').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
		return false;
    }
});
/************************************************************************************
	LOAD TABLE
************************************************************************************/
function searchYes(result) {	
	invs = result;
	loadTable();
}
function searchNo(result) {
	loadTable();
}
function searchAll() {
	document.getElementById("loader").style.display = "block";
	getRequest("getInvs.php", searchYes, searchNo);
}
function displaySum(){
	document.getElementById("totalCount").innerText = itemTotal;
	document.getElementById("sumCount").innerText = countTotal;
	document.getElementById("sumCost").innerText = costTotal.toFixed(0);
}
function loadTable(){
	$table.bootstrapTable('removeAll');
	document.getElementById("loader").style.display = "none";
	countTotal = 0;
	costTotal = 0;
	itemTotal = 0;
	if(invs.length == 0){	
		displaySum();
		return;
	}

	invs.sort(sortTable(sortCol, sortOp));
	var rows = [];
	var dataStr, imgSrc, imgStr, count, countStr, cost;
	for(var i=0; i<invs.length; i++){
		// filter
		var tu = invs[i]['time_updated'].substring(0,10)+"T"+invs[i]['time_updated'].substring(11,19);
		var tr = new Date(tu); 
		ftf = fTime['timefrom']+"T00:00:01"; ftt = fTime['timeto']+"T23:59:59"; 
		ftfr = new Date(ftf); fttr = new Date(ftt);
		if (tr < ftfr || tr > fttr)
			continue;
		if (fSup != "" && fSup != null) {
			if (fSup != invs[i]['s_id'])
				continue;
		}
		if (fType != "" && fType != null) {
			if (fType != invs[i]['t_id'])
				continue;
		}
		if (fSup != "" && fSup != null) {
			if (fSup != invs[i]['s_id'])
				continue;
		}
		if (fStatus != "" && fStatus != null) {
			if (fStatus != invs[i]['status'])
				continue;
		}
        if (fSeason != "" && fSeason != null) {
            if (fSeason != invs[i]['season'])
                continue;
        }
		// Make row
		if (invs[i]['status'] != "0")
			dataStr = "<span class='mr-2 dot'></span>";
		else
			dataStr = "";
		if (invs[i]['i_name'] && invs[i]['i_name'] != '')
			dataStr += "<b>"+invs[i]['i_code']+"</b><br><a>"+invs[i]['i_name']+"</a>";
		else
			dataStr += "<b>"+invs[i]['i_code']+"</b>";

			<?php if(in_array($_SESSION['uId'], array(1,6))) {?>	
        if (invs[i]['season'] && invs[i]['season'] != '')
            dataStr += "<br>"+season_arr[invs[i]['season']];
<?php } ?>

        if(invs[i]['m_no'] != null)
            imgSrc = invs[i]['path']+"/"+invs[i]['i_id']+"_"+invs[i]['m_no']+"_s.jpg";
        else
            imgSrc = "blank.jpg";


		imgStr = "<img width='60' height='80' style='object-fit: cover' src='"+imgSrc+"' >";
		if (invs[i]['unit'] == null || invs[i]['unit'] == "1") {
			count = parseInt(invs[i]['count']);
			countStr = count.toString();
			cost = parseFloat(invs[i]['cost'])*parseInt(invs[i]['count']);
		} else {
			count = parseInt(invs[i]['count'])*parseInt(invs[i]['unit']);
			countStr = invs[i]['count']+"(x"+invs[i]['unit']+")";
			cost = parseFloat(invs[i]['cost'])*parseInt(invs[i]['count'])*parseInt(invs[i]['unit']);
		}
		
		rows.push({
			id: invs[i]['i_id'],
			idx_image: imgStr,
			idx_data: dataStr,
			idx_count: countStr,
			idx_cost: cost.toFixed(0)
		});
		countTotal += count;
		costTotal += cost;
		itemTotal++;
	}
	$table.bootstrapTable('append', rows);	
	displaySum();	
	// maintain previous scroll position
	var pos = localStorage.getItem("inv_mgt_scrolltop");
	document.documentElement.scrollTop = pos;
	localStorage.setItem("inv_mgt_scrolltop", 0);
	
	
}
/************************************************************************************
	VIEW
************************************************************************************/
// Click a row to view product
$('#table').on('click-row.bs.table', function (e, row, $element) {	
	// save the current scroll position
	var pos =  document.documentElement.scrollTop;
	localStorage.setItem("inv_mgt_scrolltop", pos);
	// view product
	var url = "inv_view.php?back=inv_mgt&id="+row.id;
	window.location.assign(url);
});
/************************************************************************************
	FILTER
************************************************************************************/
$modalFilter = $('#modalFilter');
function showFilter() {
	$modalFilter.modal();
}
function doneFilter(){
	$modalFilter.modal("toggle");
	localStorage.setItem("inv_mgt_sup", fSup);
	localStorage.setItem("inv_mgt_type", fType);
	localStorage.setItem("inv_mgt_status", fStatus);
    localStorage.setItem("inv_mgt_season", fSeason);
	if (fSup == "" && fType == "" && fStatus == "")
		document.getElementById("btnFilter").style.border = "none";
	else
		document.getElementById("btnFilter").style.border = "2px solid red";
	loadTable();
}
function clearFilter() {
	$modalFilter.modal("toggle");
	var x = "<?php echo $thisResource->comSupplierAll ?>";
	document.getElementById("s_name").innerHTML = x; 
	fSup ="";
	var y = "<?php echo $thisResource->comTypeAll ?>";
	document.getElementById("t_name").innerHTML = y; 
	fType = "";
	var z = "<?php echo $thisResource->comAll ?>";
	document.getElementById("status_str").innerHTML = z;
	fStatus = "";

    var a = "<?php echo $thisResource->comSeasonAll ?>";
    document.getElementById("season").innerHTML = a;
    fSeason = "";

	localStorage.setItem("inv_mgt_sup", fSup);
	localStorage.setItem("inv_mgt_type", fType);
	localStorage.setItem("inv_mgt_status", fStatus);
    localStorage.setItem("inv_mgt_season", fSeason);
	document.getElementById("btnFilter").style.border = "none";
	loadTable();
}
// Filter by supplier
function filterSup(e) {
	var x = $(e).text();
	document.getElementById("s_name").innerHTML = x;
	fSup = getSupIdByName(x);	
}
// Filter by types
function filterType(e) {
	var x = $(e).text();
	document.getElementById("t_name").innerHTML = x;
	fType = getTypeIdByName(x);	
}
// Filter by season
function filterSeason(e, id) {
    var x = $(e).text();
    document.getElementById("season").innerHTML = x;
    if(id > 0) fSeason = id;
    else fSeason = "";
}
// Filter by status
function selStatus(e) {
	document.getElementById("status_str").innerText = $(e).text();
	fStatus = e.getElementsByTagName("input")[0].value;  
	document.getElementById("status").value = fStatus;
}
/************************************************************************************
	SORT
************************************************************************************/
function getSort() {
	var x = "";
	if (sortCol == 'time_created' && sortOp == 1)
		x = "<?php echo $thisResource->comSortCreated ?>";
	else if (sortCol == 'i_code' && sortOp == 0)
		x = "<?php echo $thisResource->comSortCodeAZ ?>";
	else if (sortCol == 'i_code' && sortOp == 1)
		x = "<?php echo $thisResource->comSortCodeZA ?>";
	else if (sortCol == 'count' && sortOp == 0)
		x = "<?php echo $thisResource->comSortCountAsc ?>";
	else if (sortCol == 'count' && sortOp == 1)
		x = "<?php echo $thisResource->comSortCountDesc ?>";
	else if (sortCol == 'cost' && sortOp == 0)
		x = "<?php echo $thisResource->comSortValueAsc ?>";
	else if (sortCol == 'cost' && sortOp == 1)
		x = "<?php echo $thisResource->comSortValueDesc ?>";
	else
		x = "<?php echo $thisResource->comSortUpdated ?>";
	
	return x;
}
function doneSort(e){
	var x = $(e).text();

	switch(x) {
		case "<?php echo $thisResource->comSortCreated ?>": sortCol = 'time_created'; sortOp = 1; break;
		case "<?php echo $thisResource->comSortCodeAZ ?>": sortCol = 'i_code'; sortOp = 0; break;
		case "<?php echo $thisResource->comSortCodeZA ?>": sortCol = 'i_code'; sortOp = 1; break;
		case "<?php echo $thisResource->comSortCountDesc ?>": sortCol = 'count'; sortOp = 1; break;
		case "<?php echo $thisResource->comSortCountAsc ?>": sortCol = 'count'; sortOp = 0; break;
		case "<?php echo $thisResource->comSortValueDesc ?>": sortCol = 'cost'; sortOp = 1; break;
		case "<?php echo $thisResource->comSortValueAsc ?>": sortCol = 'cost'; sortOp = 0; break;
		default: sortCol = 'time_updated'; sortOp = 1;
	}
	localStorage.setItem("inv_mgt_sortcol", sortCol);
	localStorage.setItem("inv_mgt_sortop", sortOp);
	document.getElementById("sortOption").innerText = x;
	loadTable();
}
// Sort table
function sortTable(key, option){ 
    return function(a, b){ 
		var x = a[key]; var y = b[key];
		if (key == "time_created" || key == "time_updated") {
			var x1 = a[key].substring(0,10); var x2 = a[key].substring(11,19); x = x1+"T"+x2; x = new Date(x); 
			var y1 = b[key].substring(0,10); var y2 = b[key].substring(11,19); y = y1+"T"+y2; y = new Date(y);
		} else if (key == "count") {
			if (a['unit'] == null || a['unit'] == "1")
				x = parseInt(a['count']);
			else
				x = parseInt(a['count'])*parseInt(a['unit']);
			if (b['unit'] == null || b['unit'] == "1")
				y = parseInt(b['count']);
			else
				y = parseInt(b['count'])*parseInt(b['unit']);			
		} else if (key == "cost") {
			if (a['unit'] == null || a['unit'] == "1")
				x = parseInt(a['count'])*parseFloat(a['cost']);
			else
				x = parseInt(a['count'])*parseInt(a['unit'])*parseFloat(a['cost']);
			if (b['unit'] == null || b['unit'] == "1")
				y = parseInt(b['count'])*parseFloat(b['cost']); 
			else
				y = parseInt(b['count'])*parseInt(b['unit'])*parseFloat(b['cost']);
		}
		if(option == 1){
			return ((x < y) ? 1 : ((x > y) ? -1 : 0));
		}
		else {
			return ((x < y) ? -1 : ((x > y) ? 1 : 0));
		}  
    }    
}    
/************************************************************************************
	TIME
************************************************************************************/
// Show time selection (modalTime)
function selectTime(){
	$modalSelTime.modal();	
}

// Finish time selection (modalTime)
function mdstDoneTime(){
	$modalSelTime.modal("toggle");	
	 
	var timeStr = mdstGetStr();	
	document.getElementById("selTime").innerText = timeStr;	
	fTime = mdstGetValue(1);
	
	loadTable();
}
/************************************************************************************
	SEARCH
************************************************************************************/
function searchInv() {
	var code = document.getElementById("i_code").value;
	if (code == "")
		return false;	
	var id = "";
	for (var i=0; i<invs.length; i++) {
		if (invs[i]['i_code'] == code) {
			id = invs[i]['i_id'];
			break;
		}
	}
	if (id == "")
		return false;
	var url = "inv_view.php?back=inv_mgt&id="+id;
	window.location.assign(url);
}
// This is a callback for autocomplete
function doneAutocomp() {
	searchInv();
}
/************************************************************************************
	NEW
************************************************************************************/
function newInv() { 	 
	var url = "inv_view.php?back=inv_mgt";
	window.location.assign(url);
}
/************************************************************************************
	FUNCTIONS
************************************************************************************/
function getSupIdByName(name) {	
	if (name == "<?php echo $thisResource->comSupplierAll ?>")
		return "";
	if (name == "<?php echo $thisResource->comSupplierUnknown ?>")
		return "0";

	for (var i=0; i<a_sups.length; i++) {
		if (a_sups[i]['s_name'] == name) {
			return a_sups[i]['s_id'];
			break;
		}
	}
	
	return "0";
}
function getSupNameById(id) {	
	if (id == null || id == "")
		return "<?php echo $thisResource->comSupplierAll ?>";
	if (id == "0")
		return "<?php echo $thisResource->comSupplierUnknown ?>";

	for (var i=0; i<a_sups.length; i++) {
		if (a_sups[i]['s_id'] == id) {
			return a_sups[i]['s_name'];
			break;
		}
	}
	
	return "";
}
function getTypeIdByName(name) {
	if (name == "<?php echo $thisResource->comTypeAll ?>")
		return "";
	if (name == "<?php echo $thisResource->comTypeUnknown ?>")
		return "0";
	
	for (var i=0; i<a_types.length; i++) {
		if (a_types[i]['t_name'] == name) {
			return a_types[i]['t_id'];
			break;
		}
	}
	
	return "";
}
function getTypeNameById(id) {
	if (id == null || id == "")
		return "<?php echo $thisResource->comTypeAll ?>";
	if (id=="0")
		return "<?php echo $thisResource->comTypeUnknown ?>";
	
	for (var i=0; i<a_types.length; i++) {
		if (a_types[i]['t_id'] == id) {
			return a_types[i]['t_name'];
			break;
		}
	}
	
	return "";
}

function getStatusNameById(id) {
	if (id == null || id == "")
		return "<?php echo $thisResource->comAll ?>";
	else if (id == "1")
		return "<?php echo $thisResource->comStatusOffline ?>";
	else if (id == "0")
		return "<?php echo $thisResource->comStatusNormal ?>";
	else
		return "未完成";
}

function getSeasonNameById(id) {
    if (id == null || id == "")
        return "<?php echo $thisResource->comSeasonAll ?>";
    else
        return season_arr[id];
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
