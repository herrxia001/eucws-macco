<?php
/************************************************************************************
	File:		cust_list.php
	Purpose:	customer management
	
	2021-02-10: when select one customer, show modalCust instead of customer.php
************************************************************************************/

// Start session
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

// Include files
include_once 'resource.php';
include_once 'db_functions.php';

// Init variables
$thisResource = new myResource($_SESSION['uLanguage']);
$backPhp = 'management.php';

if(isset($_GET['back']))
	$backPhp = $_GET['back'].'.php';
?>

<!doctype html>
<html lang="zh">
<head>
    <?php include 'include/header.php' ?>
	<title>EUCWS - Customers</title>
</head>
<style>
.dropdown-menu{
    max-height: 200px;
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
</style>
<body>
	<?php include 'include/nav.php' ?>
	<?php include "include/modalCust.php" ?>
	<?php include "include/modalCustSearch.php" ?>
	
	<div class="container">
		<div class="row">
			<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-2" align="left">
				<a class="btn btn-secondary" href=<?php echo $backPhp ?> role="button"><span class='fa fa-arrow-left'></a>		
			</div>
			<div class="p-1 col-6 col-sm-6 col-md-6 col-lg-6" align="center">
				<a><?php echo $thisResource->fmCustListTitleSub ?></a>		
			</div>
			<div class="p-1 col-4 col-sm-4 col-md-4 col-lg-4" align="right">
				<button type="button" id="searchCust" name="searchCust" class="btn btn-secondary"  onclick="searchCust()"><span class='fa fa-search'></button>			
				<button type="button" id="newCust" name="newCust" class="btn btn-secondary"  onclick="newCust()"><span class='fa fa-plus'></button>
			</div>
		</div>
		<div class="row"> 
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12">
		<table id="tableCusts" class="table-sm" data-toggle="table" data-single-select="true" data-click-to-select="true">
			<thead class="thead-light">
				<tr>
				<th data-field="id" data-width="" data-width-unit="%" data-visible="false"></th>
				<th data-field="idx_code" data-width="30" data-width-unit="%" data-sortable="true"><?php echo $thisResource->fmCustListCapId ?></th>
				<th data-field="idx_name" data-width="70" data-width-unit="%" data-sortable="true"><?php echo $thisResource->fmCustListCapName ?></th>
				</tr>
			</thead>
			<tbody>
			<!-- Load by JS -->
			</tbody>
		</table>
		</div>
		</div>
		
	<div class="loader" id="loader"></div>
	
	</div> <!-- end of container -->

<script src="js/ajax.js"></script>
<script src="js/autocomplete.js?202109131930"></script>
<script src="js/modalCust.js?<?= rand() ?>"></script>	
<script src="js/modalCustSearch.js?202109090946"></script>
<script>

var myCustomer = new Object();
var customers = new Object(), custsCount = 0;
var $tableCusts = $("#tableCusts");
var loadOK = 0;
	
$tableCusts.bootstrapTable({   
	formatNoMatches: function () {
		if (loadOK == -1)
			return "数据错误。请稍后再试。";
		else
			return "数据载入中。。。";
    }
});

function getCustById(id) {
	for (var i=0; i<custCount; i++) {
		if (customers[i]['k_id'] == id)
			return customers[i];
	}
	return null;
}

function loadCusts(result) {
	loadOK = 1;
	customers = result; 
	custCount = customers.length;
	document.getElementById("loader").style.display = "none";
	
	$tableCusts.bootstrapTable('removeAll');
	var rows = [];
	for (var i=0; i<custCount; i++){
		rows.push({
			id: customers[i]['k_id'],
			idx_code: customers[i]['k_code'],
			idx_name: customers[i]['k_name']
		});
	}
	$tableCusts.bootstrapTable('append', rows);	
}

function loadCustNo(result) {
	document.getElementById("loader").style.display = "none";
	loadOK = -1;
}

function searchCustomers() {
	document.getElementById("loader").style.display = "block";
	var link = "getCusts.php";
	getRequest(link, loadCusts, loadCustNo);
}

// Display Title
$(document).ready(function(){
	 document.getElementById("myTitle").innerHTML = '<?php echo $thisResource->fmCustListTitle ?>'; 
	 // Load customers
	searchCustomers();	
});

// Prevent 'enter' key for submission
$('form input').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
		return false;
    }
});

// Click a row to view customer
$tableCusts.on('click-row.bs.table', function (e, row, $element) {
	myCustomer = getCustById(row.id);
	mkInit(myCustomer);
	$modalCust.modal();
});

// New customer
function newCust(){
	myCustomer['k_id'] = "";
	mkInit(myCustomer);
	$modalCust.modal();	
}

// Search customer
function searchCust(){
	mksInit();
	$modalCustSearch.modal();
}

// modalCust: Save customer
function mkSaveCust(customer) {
	searchCustomers();
}

</script>

</body>
</html>
