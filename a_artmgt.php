<?php
/************************************************************************************
	File:		a_artmgt.php

************************************************************************************/
// Start session; If session expired, load the login page.
session_start();
if(!$_SESSION['uId'])
	header("Location:alogin.php");

// Include files
include_once 'resource.php';
include_once 'db_functions.php';

$myCompany= dbQueryCompany();

// Init variables
$thisResource = new myResource($_SESSION['uLanguage']);

?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>
	<title>EUIMS - ARTICLES</title>
</head>

<body>
	<?php include 'include/a_nav.php' ?>
	
	<div class="container">	
<!-- summary -->
	<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-6">
			<a>&nbsp;&nbsp;库存总件数:&nbsp;&nbsp;</a><a style="color:blue" id="sumCount"></a>
			<a>&nbsp;&nbsp;库存总金额:&nbsp;&nbsp;</a><a style="color:blue" id="sumCost"></a>
		</div>
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-2" align="right">			
			<button type="button" class="btn btn-secondary" id="btnExport" onclick="exportFile()"><span class='fa fa-floppy-o'></button>
			<button type="button" class="btn btn-secondary" id="btnPrint" onclick="printFile()"><span class='fa fa-print'></button>
		</div>
	</div>
<!-- Article table -->
	<div class="row">
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-8">	
			<table id="table" class="table-sm" data-toggle="table" data-single-select="true" data-click-to-select="true" data-unique-id="id">
				<thead class="thead-light">
					<tr>
					<th class="p-1" data-field="id" data-width-unit="%" data-visible="false"></th>			
					<th class="p-1" data-field="idx_data" data-halign="center" >商品</th>
					<th class="p-1" data-field="idx_count" data-halign="center" data-align="right">库存</th>
                        <!--th class="p-1" data-field="idx_countnianbao" data-halign="center" data-align="right">库存(年报)</th-->
					<th class="p-1" data-field="idx_cost" data-halign="center" data-align="right">单位成本</th>
					<th class="p-1" data-field="idx_total" data-halign="center" data-align="right">库存金额</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>

	</div> <!-- End of container -->

<!-- Modal: modalArt -->
<div class="modal fade" id="modalArt" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdaTitle">商品库存</h5>
		</div>
		<div class="modal-body">
			<input type="text" class="form-control" name="mda_id" id="mda_id" hidden>
			<input type="text" class="form-control" name="mda_t_id" id="mda_t_id" hidden>
			<div class="row">
				<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12">
					<div class="input-group p-1">
						<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">货号</span></div>
						<input type="text" class="form-control" name="mda_code" id="mda_code" readonly>
					</div>
					<div class="input-group p-1">
						<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">名称</span></div>
						<input type="text" class="form-control" name="mda_name" id="mda_name" readonly>
					</div>
					<div class="input-group p-1">
						<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">库存</span></div>
						<input type="number" min="0" step="0.01" class="form-control" name="mda_count" id="mda_count" readonly>
					</div>
					<div class="input-group p-1">
						<div class="input-group-prepend"><span class="input-group-text" style="width:120px;">单位成本</span></div>
						<input type="number" min="0" step="0.01" class="form-control" name="mda_cost" id="mda_cost">		
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" id="mdaBtnClose" data-dismiss="modal"><span class='fa fa-times'></button>
			<button type="button" class="btn btn-primary" id="mdaBtnDone" onclick="mdaDone()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- end of modalArt -->

</body>

<script src="js/sysfunc.js?v2"></script>
<script src="js/ajax.js"></script>
<script src="js/autocomplete.js"></script>

<script>

var company = <?php echo json_encode($myCompany) ?>;
var $table = $("#table");
// Modal
var $modalArt = $("#modalArt");
// Load data
var invs;
var countTotal = 0, costTotal = 0;

/****************************************************************************************************
	INIT
****************************************************************************************************/
$(document).ready(function(){
	document.getElementById("myTitle").innerText = "当前库存";
	// Load table
	searchArts();	
});

$table.bootstrapTable({   
	formatNoMatches: function () {
         return "没有找到符合条件的数据";
    }
});

// Prevent 'enter' key for submission, only enabled for barcode input
$('form input').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
		return false;
    }
});

/****************************************************************************************************
	LOAD
****************************************************************************************************/
function searchArtsYes(result) {
	invs = result;
	loadTableFinal();
}
function searchArtsNo(result) {
	invs = 0;
	loadTableFinal();
}

function searchArts() {
	getRequest("getArts_2.php", searchArtsYes, searchArtsNo);
}

function displaySum(){
	document.getElementById("sumCount").innerText = countTotal;
	document.getElementById("sumCost").innerText = costTotal.toFixed(2);
}

function loadTableFinal(){	
	countTotal = 0;
	costTotal = 0;

	var subTotal = 0;
	var rows = [];
	for(var i=0; i<invs.length; i++){		
		var subTotal = parseFloat(invs[i]['cost'])*parseInt(invs[i]['count']);
		invs[i]['subtotal'] = subTotal.toFixed(2);
		rows.push({
			id: 			invs[i]['a_id'],
			idx_data: 		invs[i]['a_name'],
			idx_count: 		invs[i]['count'],
            //idx_countnianbao: 		invs[i]['count_nianbao'],
			idx_cost: 		invs[i]['cost'],
			idx_total: 		invs[i]['subtotal']
		});
		countTotal += parseInt(invs[i]['count']);
		costTotal += parseFloat(subTotal);
	}
	$table.bootstrapTable('removeAll');
	$table.bootstrapTable('append', rows);	
	displaySum();	
}

/****************************************************************************************************
	EDIT
****************************************************************************************************/
$('#table').on('click-row.bs.table', function (e, row, $element) {
	showArt(row.id);
})

$modalArt.on('shown.bs.modal', function () {
	  $('#mda_count').trigger('focus');
})

function showArt(id) {
	getRequest("getArtById.php?id="+id, searchArtYes, null);
}

function searchArtYes(result) {
	var art = result[0];

	//Set values
	document.getElementById("mda_id").value = art['a_id'];
	document.getElementById("mda_t_id").value = art['t_id'];
	document.getElementById("mda_code").value = art['a_code'];
	document.getElementById("mda_name").value = art['a_name'];
	document.getElementById("mda_count").value = art['count'];
	document.getElementById("mda_cost").value = art['cost'];
	
	$modalArt.modal();
}

// Done modalArt
function postArtYes(result) {
	searchArts();
}
function mdaDone() {
	var id = document.getElementById("mda_id").value;
	var code = document.getElementById("mda_code").value; 
	if (name == "") {
		$('#mda_code').trigger('focus');
		return;
	}
	var name = document.getElementById("mda_name").value; 
	if (name == "") {
		$('#mda_name').trigger('focus');
		return;
	}
	var count = document.getElementById("mda_count").value;
	if (!onlyDigits(count)) {
		$('#mda_count').trigger('focus');
		return;
	}
	var cost = document.getElementById("mda_cost").value;
	if (!onlyNumber(cost)) {
		$('#mda_cost').trigger('focus');
		return;
	}
	$modalArt.modal("toggle");
	
	var art = {};
	art['a_id'] = id;
	art['a_code'] = code;
	art['a_name'] = name;
	art['count'] = count;
	art['cost'] = cost;
	var form = new FormData();
	form.append('art', JSON.stringify(art));
	postRequest("postArtUpdate.php", form, postArtYes, null);	
}

/****************************************************************************************************
	SYSTEM
****************************************************************************************************/
function onlyDigits(s) {
	if (s == "")
		return false;
	if (parseInt(s) < 0)
		return false;
	var d;
	for (var i=0; i<s.length; i++) {
		d = s[i];
		if (d < "0" || d > "9")
			return false;
	}

	return true;
}

function onlyNumber(s) {
	if (s == "")
		return false;
	if (parseFloat(s) < 0)
		return false;
	var d;
	for (var i=0; i<s.length; i++) {
		d = s[i];
		if ((d < "0" || d > "9") && d != "," && d != ".")
			return false;
	}
	
	return true;
}

/****************************************************************************************************
	EXPORT
****************************************************************************************************/
function exportFile() {
	var output = "Artikel Nr.,Beschreibung,Durchschnittskosten,Menge Bestand,Wert Bestand\n";
	for (var i=0; i<invs.length; i++) {
		var subTotal = parseFloat(invs[i]['cost'])*parseInt(invs[i]['count']);
		output += invs[i]['a_code']+',';
		output += invs[i]['a_name']+',';
		output += invs[i]['cost']+',';
		output += invs[i]['count']+',';
		output += subTotal.toFixed(2)+'\n';
	}
	
	var dt = currentDate();
	var a = document.createElement("a");
	var file = new Blob([output], {type: 'text/plain'});
	var url = URL.createObjectURL(file);
	a.href = url;
	a.download = "bestand_"+dt+".csv";
	document.body.appendChild(a);
    a.click();
	window.URL.revokeObjectURL(url);
    a.remove();
}

/****************************************************************************************************
	PRINT
****************************************************************************************************/
function printFile() { 
	var i = 0;	
	var dt = currentDate();	
	
	var src = "files/"+"<?php echo $_SESSION['uDb']; ?>"+"/logo.png";
	var output = '<html><head>';
	output += '<style type="text/css" media="print">';
	output += '.thCenter{border-left:1px solid; text-align:center;}';
	output += '.tdLeft{padding:1px; border-left:1px solid; border-top:1px solid; text-align:left;}';
	output += '.tdRight{padding:1px; border-left:1px solid; border-top:1px solid; text-align:right;}';
	output += '@page { size:auto; margin:0.8cm 0.8cm 0.8cm 1.5cm; }\</style>';	
	// Title
	output += '<table width="100%" cellpadding="5" cellspacing="0"><tr>';
	output += '<td align="center">';
	output += '<img height="100" style="object-fit: cover" src="'+src+'"></img>';
	output += '</td>';
	output += '<td align="left" style="border-left:1px solid; border-top:1px solid; border-right:1px solid">';
	output += '<a style="font-size:12px">'+dt+'&nbsp;Bestandssituation</a><br>';
	output += '<a style="font-size:12px">Alle Artikel</a><br>';
	output += '<a style="font-size:12px">Wert bei der Durchschnittskosten</a><br>';
	output += '</td>';
	output += '</tr></table>';
	// Articles
	output += '<table width="100%" cellpadding="2" cellspacing="0" style="border:1px solid;"><thead>';
	output += '<tr style="font-size:12px;">';
	output += '<th class="thCenter">Artikel Nr.</th>';
	output += '<th class="thCenter">Beschreibung</th>';
	output += '<th class="thCenter">Durchschnitts-<br>kosten</th>';
	output += '<th class="thCenter">Menge<br>Bestand</th>';
	output += '<th class="thCenter">Wert<br>Bestand</th>';
	output += '</tr></thead><tbody>';
	for (i=0; i<invs.length; i++) {
		var subTotal = parseFloat(invs[i]['cost'])*parseInt(invs[i]['count']);
		output += '<tr style="font-size:12px;">';
		output += '<td class="tdLeft">'+'&nbsp;'+invs[i]['a_code']+'</td>';
		output += '<td class="tdLeft">'+'&nbsp;'+invs[i]['a_name']+'</td>';
		output += '<td class="tdRight">'+'&nbsp;'+invs[i]['cost']+'</td>';
		output += '<td class="tdRight">'+'&nbsp;'+invs[i]['count']+'</td>';
		output += '<td class="tdRight">'+'&nbsp;'+subTotal.toFixed(2)+'</td>';
		output += '</tr>';
	}
	output += '<tr style="font-size:12px;">';
	output += '<td class="tdRight" colspan="3">Total&nbsp;</td>';
	output += '<td class="tdRight">'+countTotal+'</td>';
	output += '<td class="tdRight">'+costTotal.toFixed(2)+'</td>';
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

</script>

</html>
