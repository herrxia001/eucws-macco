<?php 
/********************************************************************************
	File:		purchase.php
	Purpose: 	Purchase
*********************************************************************************/
// Start session; If session expired, load the login page.
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

// Include files
include_once 'db_functions.php';
include_once 'resource_'.$_SESSION['uLanguage'].'.php';
$thisResource = new myResource();

// Init variables
$myId = '';
$_SESSION['purType'] = 0;
$backPhp = 'pur_mgt.php';
$mySups = dbQueryAllSuppliers();
$myVariants = dbQueryVariants();

// Start a new purchase
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(isset($_GET['back']))
	{
		$backPhp = $_GET['back'].'.php';
	}
	if (isset($_GET['p_id']))
	{
		$myId = $_GET['p_id'];
		$_SESSION['purType'] = 1;
	}
	else
	{
		$myId = dbGetPurId();
		if($myId <= 0)
			header('Location: '.$backPhp);
		$_SESSION['purType'] = 0;
	}
}

?>

<!doctype html>
<html lang="zh">
<head>
    <?php include 'include/header.php' ?>	
	<title>EUCWS - Purchase</title>
<style>
body {
	padding-top: 0.2rem;
}
.dropdown-menu{
    max-height: 200px;
    overflow-y: scroll;
}
</style>
</head>

<body>	
<?php include "include/modalDel.php" ?>		
	<form action="" method="post">
	<div class="container">		
<!-- purchase data header -->
	<div class="row mb-1">
		<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-2" style="background-color: DarkSlateGrey">
			<button type="button" class="btn" onclick="cancelPur()"><span style="color:white; font-size:20px" class='fa fa-arrow-left'></span></button>
		</div>
		<div class="p-1 input-group col-8 col-sm-8 col-md-8 col-lg-4"  style="background-color: DarkSlateGrey" align="center"> 
			<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comPurchaseForm ?></span></div>
			<input type="text" class="form-control" name="o_id" id="o_id" readonly>		
			<button type="button" class="btn" id="btnHeader" onclick="showHeader()"><span style="color:white; font-size:20px" class='fa fa-bars'></span></button>
		</div>
		<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-2" style="background-color: DarkSlateGrey" align="right">
			<button type="button" class="btn" onclick="submitPur()" style="font-weight:bold; color:white" ><span style="color:white; font-size:20px" class='fa fa-check'></span></button>
		</div>
	</div>
<!-- buttons -->
	<div class="row">
		<div class="p-1 col-3 col-sm-3 col-md-3 col-lg-2" align="left">	
			<button type="button" class="btn btn-outline-danger" id="btnDestroy" onclick="destroyPur()"><?php echo $thisResource->comDelete ?></span></button>		
		</div>
		<div class="p-1 col-9 col-sm-9 col-md-9 col-lg-6" align="right">
			<!--<button type="button" class="btn btn-secondary" id="btnBarcode"  onclick="showBarcode()"><span class='fa fa-barcode'></span></button>-->
			<button type="button" class="btn btn-secondary" id="btnDis" onclick="showDis()"><span class="fa fa-percent"></span></button>
			<button type="button" class="btn btn-secondary" id="btnFee" onclick="showFee()"><span class="fa fa-eur"></span></button>
			<button type="button" class="btn btn-success" id="btnBarcode"  onclick="printBarcode()"><span class='fa fa-print'></span> 打印条码</button>
			<button type="button" class="btn btn-primary" id="btnNew" style="width:100px" onclick="showNewSearch()"><span class='fa fa-plus'></span></button>			
		</div>
	</div>	
<!-- purchase items -->
	<div class="row"> 
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-8">
			<table id="myTable" class="table-sm" data-toggle="table"
				data-single-select="true" data-click-to-select="true" data-unique-id="id">
				<thead class="thead-light">
					<tr>
					<th class="p-1" data-field="id" data-visible="false"></th>
					<th class="p-1" data-field="idx_image" data-width="15" data-width-unit="%" data-align="center"></th>
					<th class="p-1" data-field="idx_data" data-width="45" data-width-unit="%"><?php echo $thisResource->comProductNo ?></th>
					<th class="p-1" data-field="idx_count" data-width="10" data-width-unit="%" data-halign="center" data-align="right"><?php echo $thisResource->comQuantity ?></th>
					<th class="p-1" data-field="idx_cost" data-width="10" data-width-unit="%" data-halign="center" data-align="right"><?php echo $thisResource->comCost ?></th>
					<th class="p-1" data-field="idx_subtotal" data-width="10" data-width-unit="%" data-halign="center" data-align="right"><?php echo $thisResource->comSubtotal ?></th>					
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
<!-- summary -->
	<div class="row mt-1">		
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-8" style="background-color: DarkSlateGrey" align="right">
			<label style="color:white"><?php echo $thisResource->comProduct ?>:&nbsp;</label>
				<label id="itemCount" class="mt-2" style="color:white; font-weight:bold">0</label>
			<label style="color:white">&nbsp;&nbsp;<?php echo $thisResource->comTotalQuantity ?>:&nbsp;</label>
				<label id="sumCount" class="mt-2" style="color:white; font-weight:bold">0</label>
			<div style="color: white;" id="price_more"></div>
			<label style="color:white">&nbsp;&nbsp;<?php echo $thisResource->comTotalGross ?>:&nbsp;</label>
				<label id="sumCost" class="mt-2" style="color:white; font-weight:bold">0.00</label>
		</div>
	</div>
	
<!-- Modal: New item search-->
<div class="modal fade" id="modalPurNewSearch" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdPurNewSearchTitle"><?php echo $thisResource->comProductSearch ?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12">
					<div class="input-group p-1">
						<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comProductNo ?></span></div>
						<input type="text" class="form-control" name="ms_i_code" id="ms_i_code">
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary" id="btnNewInv" onclick="newInv()"><?php echo $thisResource->comProductNew ?></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: New item search -->	

<!-- Modal: purchase item -->
<div class="modal fade" id="modalPurItem" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
	data-backdrop="static" data-keyboard="false" style="overflow-y:scroll">
	<div class="modal-dialog" role="document">
		<div class="modal-content">

		<div class="modal-body">
<!-- top menu -->		
		<div class="row">
			<div class="p-1 col-6">
				<label id="m_i_code" class="ml-2 mt-2" style="font-size: 20px; font-weight: bold"></label>
			</div>
			<div class="p-1 col-6" align="right">
				<button type="button" class="btn btn-secondary" onclick="cancelItem()"><span class='fa fa-times'></span></button>
				<button type="button" class="mr-1 btn btn-primary" style="width:60px" onclick="doneItem()"><span class='fa fa-check'></span></button>
			</div>
		</div>
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:2px; width:100%">
		</div>
<!-- item data -->			
		<div class="row">
			<div class="col-8 p-1">
			<div class="p-1 input-group">
				<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comName ?></span></div>
				<input type="text" class="form-control" name="m_i_name" id="m_i_name" readonly style="background-color:white">
			</div>
			<div class="p-1 input-group">
				<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comCost ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="m_cost" id="m_cost">
				<div class="input-group-append"><span class="input-group-text" id="m_old_cost" style="width:80px"></span></div>
			</div>
			<div class="p-1 input-group">
				<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comPrice ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="m_price" id="m_price">
				<div class="input-group-append"><span class="input-group-text" id="m_old_price" style="width:80px"></span></div>
			</div>
			<div class="p-1 input-group">
				<div class="input-group-prepend"><span class="input-group-text" id="m_poscap"><?php echo $thisResource->comPosition ?></span></div>
				<input type="text" class="form-control" name="m_position" id="m_position">
			</div>
			</div>
			<div class="col-4 p-1 align-self-center" align="center">
				<label id="m_quantity" style="font-size:28px; font-weight:bold; color:green">1</label>
				<br>
				<label id="m_unit_str">x1</label>
			</div>
		</div>
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:2px; width:100%">
		</div>
<!-- no variant -->
		<div class="container p-1" id="containerNoVariant">
			<div class="row" >
			<div class="col-2 p-1 align-self-center">
				<img id="m_img" width="60" height="60" style="object-fit: cover;" onclick="mdShowImageView(this)">
			</div>
			<div class="col-4 p-1 align-self-center">
				<label id="m_color"></label>
			</div>
			<div class="col-6 p-1 align-self-center" align="right">
				<div class="row input-group">
				<button type="button" class="btn btn-outline-secondary" id="m_minus" onclick="countMinus()"
						style="touch-action: none"><span class='fa fa-minus'></span></button>
				<input type="number" min="0" step="1" class="form-control" style="touch-action: none" id="m_count" oninput="countInput()">
				<button type="button" class="btn btn-outline-secondary mr-3" id="m_add" onclick="countAdd()" 
						style="touch-action: none"><span class='fa fa-plus'></span></button>
				</div>
			</div>
			</div>
		</div>
<!-- variants -->
		<div class="container p-1" id="containerWithVariant">
		<?php for ($i=0; $i<100; $i++) { ?>
		<div class="row" id="m_varitem<?php echo $i ?>" >
			<div class="col-2 p-1 align-self-center">
				<img id="m_vimg<?php echo $i ?>" width="60" height="60" style="object-fit: cover" onclick="mdShowImageView(this)">
			</div>
			<div class="col-4 p-1 align-self-center">
				<label id="m_variant<?php echo $i ?>"></label>
			</div>
			<div class="col-6 p-1 align-self-center" align="right">
				<div class="row input-group">
				<button type="button" class="btn btn-outline-secondary" id="m_vminus<?php echo $i ?>" onclick="countVMinus(this)"
						style="touch-action: none"><span class='fa fa-minus'></span></button>
				<input type="number" min="0" step="1" class="form-control" id="m_vcount<?php echo $i ?>" oninput="countVInput(this)">
				<button type="button" class="btn btn-outline-secondary mr-3" id="m_vadd<?php echo $i ?>" onclick="countVAdd(this)" 
						style="touch-action: none"><span class='fa fa-plus'></span></button>
				</div>
			</div>
		</div>
		<?php } ?>
		</div>
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:2px; width:100%">
		</div>
<!-- bottom menu -->		
		<div class="row">
			<div class="col-3 p-1" align="left">
				<button type="button" class="ml-1 btn btn-outline-danger" id="btnDel" onclick="delItem()"><?php echo $thisResource->comDelete ?></button>
			</div>
			<div class="col-4 p-1" align="center">
				<button type="button" class="btn btn-outline-success" id="mdBtnNew" onclick="newVariant()" ><?php echo $thisResource->comVariantNew ?></span></button>
			</div>
			<div class="col-5 p-1" align="right">				
				<button type="button" class="btn btn-secondary" onclick="cancelItem()" ><span class='fa fa-times'></span></button>
				<button type="button" class="mr-1 btn btn-primary" style="width:60px" onclick="doneItem()"><span class='fa fa-check'></span></button>
			</div>
		</div>
<!-- image zoom -->
		<div id="m_imageView" class="modal" onclick="this.style.display='none'">			
			<div class="modal-content">
				<img id="m_imageZoom" class="center" style="width:90%; margin-left: auto; margin-right: auto;">
			</div>
		</div>	
		
		</div>
		</div>
	</div>
</div> <!-- End of Modal: purchase item-->

<!-- Modal: Header -->
<div class="modal fade" id="modalPurHeader" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="mdPurTitle"><?php echo $thisResource->comPurchaseForm ?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
		<div class="row">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:100px"><?php echo $thisResource->comPurchaseNo ?></span></div>
				<input type="text" class="form-control" name="mph_p_code" id="mph_p_code">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:100px"><?php echo $thisResource->comSupplier ?></span></div>
				<input type="text" class="form-control autocomplete" name="mph_s_name" id="mph_s_name">
				<div class="input-group-append">
				<div class="dropdown dropleft">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"></button>
					<ul class="dropdown-menu pre-scrollable" style="overflow-y:auto">
						<input type="text" style="position: sticky; top: 0; margin-left: 20px; margin-right: 20px; width: calc(100% - 40px);" class="form-control" placeholder="搜索.." id="myinput" oninput="filterFunction($(this))">
						<?php for($i=0; $i<count($mySups); $i++) 
						echo "<a class='dropdown-item' href='#' onclick='selSup(this)'>".$mySups[$i]['s_name']."</a>";
						?>
					</ul>
				</div>
				</div>
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:100px"><?php echo $thisResource->comRemark ?></span></div>
				<input type="text" class="form-control" name="mph_note" id="mph_note">
			</div>
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:100px"><?php echo $thisResource->comDue ?></span></div>
				<input type="text" class="form-control" name="mph_unpaid" id="mph_unpaid">
			</div>

			<div class="input-group p-1">
				<div class="form-check ml-4 pt-2">
					<input class="form-check-input" type="radio" value="1" name="isPayed" id="mdp_isPayed" onclick="if($('#mdp_paidDatum').val()=='') $('#mdp_paidDatum').val(currentDate(2))">
					<label class="form-check-label" for="mdp_isPayed" onclick="if($('#mdp_paidDatum').val()=='') $('#mdp_paidDatum').val(currentDate(2))">
						已付
					</label>
				</div>
				<div class="form-check ml-4 pt-2">
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
		<div class="modal-footer">
			<button type="button" class="btn btn-primary" id="btnHeaderOk" onclick="doneHeader()"><span class='fa fa-check'></span></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Header -->

<!-- Modal: new variant -->
<div class="modal fade" id="modalNewVariant" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="background-color:rgba(169,169,169,0.5)">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title"><?php echo $thisResource->comVariantNew ?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="p-1 col">
					<div class="input-group p-1">
						<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comVariant ?></span></div>
						<input type="text" class="ml-1 form-control autocomplete" id="mdvs_new">
						<div class="input-group-append">
							<div class="dropdown dropleft">
								<button type="button" class="ml-1 btn btn-secondary dropdown-toggle" data-toggle="dropdown"></button>
								<ul class="dropdown-menu">
								<?php for($i=0; $i<count($myVariants); $i++) 
									echo "<a class='dropdown-item' onclick='selVariants(this)'>".$myVariants[$i]['variant']."</a>";
								?>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary" onclick="saveVariant()"><span class='fa fa-check'></span></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: new variant -->	





<!-- Modal: Discount -->
<div class="modal fade" id="modalOrderDis" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdOrderDisTitle"><?php echo $thisResource->comDiscount ?></b>
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text"  style="width:120px;"><?php echo $thisResource->comDiscountRate ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mdi_discount_rate" id="mdi_discount_rate" oninput="oninputDis()">
			</div>	
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text"  style="width:120px;"><?php echo $thisResource->comDiscountValue ?></span></div>
				<input type="text" class="form-control" name="mdi_discount" id="mdi_discount" readonly>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></span></button>
			<button type="button" class="btn btn-primary" id="btnDisOk" onclick="doneDis()"><span class='fa fa-check'></span></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Discount -->

<!-- Modal: Fees -->
<div class="modal fade" id="modalOrderFee" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mdOrderFeeTitle"><?php echo $thisResource->comFee ?></b>
		</div>
		<div class="modal-body">
			<div class="input-group p-1">
				<div class="input-group-prepend"><span class="input-group-text" style="width:120px;"><?php echo $thisResource->comFeeShipping ?></span></div>
				<input type="number" min="0" step="0.01" class="form-control" name="mf_fee1" id="mf_fee1">
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></span></button>
			<button type="button" class="btn btn-primary" id="btnFeeOk" onclick="doneFee()"><span class='fa fa-check'></span></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal: Fees -->


</div> <!-- end of container -->
</form>	<!-- end of form -->

<script src="js/ajax.js"></script>
<script src="js/autocomplete.js?202109232209"></script>
<script src="js/modalDel.js"></script>
<script>
var myRes = <?php echo json_encode($thisResource) ?>;
var pId, purType = 0;
var $table = $("#myTable");
// Modals
var $modalPurHeader = $("#modalPurHeader");
var $modalPurNewSearch = $("#modalPurNewSearch");
var $modalPurItem = $("#modalPurItem");
var $modalOrderDis = $("#modalOrderDis");
var $modalOrderFee = $("#modalOrderFee");
// Data
var pur = {}, purItems = new Array();
var itemCount = 0, itemIdCount = 0;
var sumCount = 0, sumCost = 0;
var thisItem = {}, thisType = 0;;
var purItemType = 0;
// Variant
var purVariant = new Array(),variantCount = 0;;
// autocpmplete
var a_icode = JSON.parse(localStorage.getItem("a_icode"));
var a_image = JSON.parse(localStorage.getItem("a_image"));
var sups = <?php echo json_encode($mySups) ?>;
var a_variants = <?php echo json_encode($myVariants) ?>;

/************************************************************************
	INIT
************************************************************************/
pId = "<?php echo $myId ?>";
purType = "<?php echo $_SESSION['purType'] ?>";

$table.bootstrapTable({   
	formatNoMatches: function () {
         return myRes['msgErrNoRecord'];
    }
});
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
$(document).ready(function(){	
	// Load purItems
	if (purType == 1) {
		getRequest("getPurItemsById.php?p_id="+pId, loadPurItems, loadError);
		getRequest("getPurVariantById.php?p_id="+pId, loadPurVariantYes, loadPurVariantNo);
	} 	
	// Load data for autocomplete 	
	autocomplete_like(document.getElementById("ms_i_code"), a_icode, a_image);
	autoSups(document.getElementById("mph_s_name"), sups);
	autoVariants(document.getElementById("mdvs_new"), a_variants);
	// If type=1, load; otherwise new
	if (purType == 1) {
		getRequest("getPurById.php?p_id="+pId, loadPur, loadError);	
	}
	else {
		initPur();
		displaySum();
		newHeader();
	}		
});
/************************************************************************
	HEADER WINDOW
************************************************************************/
$modalPurHeader.on('shown.bs.modal', function () {
	  $('#mph_p_code').trigger('focus');
})
// New header
function newHeader() {
	document.getElementById("mph_p_code").value = "";
	document.getElementById("mph_s_name").value = "";
	document.getElementById("mph_note").value = "";
	document.getElementById("mph_unpaid").value = "";
	
	$modalPurHeader.modal();
	$("#btnBarcode").hide();
}
// Done header
function doneHeader() {
	var p_code = document.getElementById("mph_p_code").value;
	var s_name = document.getElementById("mph_s_name").value;
	var s_id;
	var note = document.getElementById("mph_note").value;
	var unpaid = document.getElementById("mph_unpaid").value;
	
	if (p_code == "") {
		$('#mph_p_code').trigger('focus');
		return false;
	}
	if (s_name == "") {
		$('#mph_s_name').trigger('focus');
		return false;
	}
	s_id = getSupIdByName(s_name);
	if (s_id == "") {
		$('#mph_s_name').trigger('focus');
		return false;
	}
	if (unpaid != "" && !onlyNumber(unpaid)) {
		$('#mph_unpaid').trigger('focus');
		return false;
	}
	
	$modalPurHeader.modal("toggle");
	
	document.getElementById("o_id").value = p_code;
	
	pur['p_code'] = p_code;
	pur['s_id'] = s_id;
	pur['note'] = note;
	if (unpaid == "")
		pur['unpaid'] = "0.00";
	else
		pur['unpaid'] = parseFloat(unpaid).toFixed(2);

	pur['isPayed'] = -1;
	pur['paidDatum'] = "";
	if(document.getElementById("mdp_isPayed").checked == true){
		pur['isPayed'] = 1;
		pur['paidDatum'] = document.getElementById("mdp_paidDatum").value;
	}
	
	var link = "postPurHeader.php";
	var form = new FormData();
	form.append('pur', JSON.stringify(pur));
	postRequest(link, form, purOk, purError);
}
// Show header
function showHeader() {
	document.getElementById("mph_p_code").value = pur['p_code'];
	document.getElementById("mph_s_name").value = getSupNameById(pur['s_id']);
	document.getElementById("mph_note").value = pur['note'];
	document.getElementById("mph_unpaid").value = pur['unpaid'];
	
	if(pur['isPayed'] == 1){
		document.getElementById("mdp_isPayed").checked = true;
		document.getElementById("mdp_paidDatum").value = pur['paidDatum'];
		if($('#mdp_paidDatum').val()=='') $('#mdp_paidDatum').val(currentDate(2));
	}
	else {
		document.getElementById("mdp_isNotPayed").checked = true;
		document.getElementById("mdp_paidDatum").value = "";
	}

	$modalPurHeader.modal();
}
// Supplier funcitons
function getSupIdByName(name) {
	for (var i=0; i<sups.length; i++) {
		if(sups[i]['s_name'] == name) {
			return sups[i]['s_id'];
			break;
		}
	}
	return "";
}
function getSupNameById(id) {
	for (var i=0; i<sups.length; i++) {
		if(sups[i]['s_id'] == id) {
			return sups[i]['s_name'];
			break;
		}
	}
	return "";
}
function selSup(e) {
	var x = $(e).text();
	document.getElementById("mph_s_name").value = x;
}
/************************************************************************
	INIT & LOAD PURCHASE
************************************************************************/
function initPur() {
	pur['p_id'] = pId;
	pur['p_code'] = "";
	pur['s_id'] = "";
	pur['note'] = "";
	pur['unpaid'] = "0.00";
	
	var link = "postPurAdd.php";
	var form = new FormData();
	form.append('pur', JSON.stringify(pur));
	postRequest(link, form, purOk, purError);
}
function purOk(result) {
	
}
function purError(result) {
	
}
// Load purchase after getRequest
function loadPur(result) {
	pur = result;
	
	document.getElementById("o_id").value = pur['p_code'];

	displaySum();
}
// Load purItems after getRequest
function loadPurItems(result) {
	purItems = result;
	itemCount = purItems.length;
	itemIdCount = itemCount;
	
	$table.bootstrapTable('removeAll');
	var rows = [];
	var dataStr, imgSrc, imgStr, countStr;
	for (var i=0; i<itemCount; i++) {
		purItems[i]['id'] = i;
		if (purItems[i]['i_name'] != null)
			dataStr = "<a style='font-weight:bold;'>"+purItems[i]['i_code']+"</a><br>"+"<a >"+purItems[i]['i_name']+"</a>";
		else
			dataStr = "<a style='font-weight:bold;'>"+purItems[i]['i_code']+"</a>";
		imgSrc = purItems[i]['path']+"/"+purItems[i]['i_id']+"_"+purItems[i]['m_no']+"_s.jpg";
		imgStr = "<img width='60' height='60' style='border:1px dotted; object-fit: cover' src='"+imgSrc+"' >";
		if (purItems[i]['unit'] == "1") {
			countStr = purItems[i]['count'];
			purItems[i]['real_count'] = purItems[i]['count'];
		} else {
			countStr = purItems[i]['count']+" (x"+purItems[i]['unit']+")";
			purItems[i]['real_count'] = (parseInt(purItems[i]['count'])*parseInt(purItems[i]['unit'])).toString();
		}
		subtotal = parseInt(purItems[i]['real_count'])*parseFloat(purItems[i]['cost']);
		purItems[i]['subtotal'] = subtotal.toFixed(2);
		rows.push({
			id: i,
			idx_image: imgStr,
			idx_data: dataStr,
			idx_count: countStr,
			idx_cost: purItems[i]['cost'],
			idx_subtotal: purItems[i]['subtotal']
		});	
		sumCount += parseInt(countStr);
		sumCost += subtotal;
	}
	$table.bootstrapTable('append', rows);
	
	displaySum();
}
// Display error
function loadError(result) {

}
// Load purVariant
function loadPurVariantYes(result) {
	purVariant = new Array();
	variantCount = 0;
	var newVariant = null, newVariantCount = 0;
	var lastIid = "";
	for (var i=0; i<result.length; i++) {
		if (lastIid != result[i]['i_id']) {
			lastIid = result[i]['i_id'];
			if (newVariant != null) {
				purVariant[variantCount] = newVariant;
				variantCount++;
			}
			newVariant = new Array();
			newVariantCount = 0;
		}
		newVariant[newVariantCount] = result[i];
		newVariantCount++;
	}
	purVariant[variantCount] = newVariant;
	variantCount++;
}
function loadPurVariantNo(result) {
	
}
/************************************************************************
	MAIN WINDOW FUNCTIONS
************************************************************************/
// Display sum
function displaySum() {
	document.getElementById("itemCount").innerHTML = itemCount;	
	document.getElementById("sumCount").innerHTML = sumCount;
	if(pur['discount'] == null) pur['discount'] = 0;
	if(pur['fee'] == null) pur['fee'] = 0;
	var total_sum = (sumCost * (100 - parseFloat(pur['discount']))) / 100 + parseFloat(pur['fee']);
	pur['total_sum'] = total_sum;
	document.getElementById("sumCost").innerHTML = total_sum.toFixed(2);
	var price_more = "";
	if(pur['discount'] > 0) price_more = "原价: <b>"+sumCost.toFixed(2)+"</b><br>打折: <b>-" + (sumCost * parseFloat(pur['discount']) / 100).toFixed(2)+" ("+pur['discount']+"%)</b>";
	if(pur['fee'] > 0){
		if(price_more == ""){
			price_more = "原价: <b>"+sumCost.toFixed(2)+"</b><br>运费: <b>" + pur['fee'] + "</b>";
		}else price_more += "<br>运费: <b>" + pur['fee'] + "</b>";
	} 
	document.getElementById("price_more").innerHTML = price_more;
}

// Find purItems item by searching id
function getItemIndexById(id) {
	for (var i=0; i<itemCount; i++) {
		if (purItems[i]['id'] == id)
			return i;
	}	
	return -1;
}
// Submit pur
function submitPur() {
	if (pur['p_code'] == '' || pur['s_id'] == '') {
		newHeader();
		return;
	}
	if (itemCount <= 0) {
		alert(myRes['msgErrNoRecord']);
		return;
	}
	saveDbPur();
	var url = "<?php echo $backPhp; ?>";
	window.location.assign(url);
}
// Cancel/close purchase
function cancelPur() {
	if(itemCount <= 0) {
		delDbPur();
	}
	else {
		var url = "<?php echo $backPhp; ?>";
		window.location.assign(url);
	}
}
/************************************************************************
	ITEM WINDOW FUNCTIONS
************************************************************************/
function cancelItem() {
	$modalPurItem.modal("toggle");
}

function doneItem() {
	// Get values from modalPurItem
	var count = "0";
	if (mdvsVariant == null)
		count = document.getElementById("m_count").value;
	else
		count = getVCount();
	var cost = document.getElementById("m_cost").value;
	var price = document.getElementById("m_price").value;
	cost = parseFloat(cost).toFixed(2);
	price = parseFloat(price).toFixed(2);
	// Close the modalPurItem
	$modalPurItem.modal("toggle");
	// update or add
	if (thisType == 1)
		updateItem(count, cost, price);
	else
		addItem(count, cost, price);
	// position
	var position = document.getElementById("m_position").value;
	if (thisItem['position'] == null || (position != "" && position != thisItem['position'])) {
		thisItem['position'] = position;
		updateDbPosition(position, thisItem['i_id']);
	}
}
$modalPurItem.on('shown.bs.modal', function () {
	$("#m_cost").trigger('focus');
})
/************************************************************************
	NEW ITEM 
************************************************************************/
// Show new search
function showNewSearch() {
	document.getElementById("ms_i_code").value = "";
	$modalPurNewSearch.modal();
}
$modalPurNewSearch.on('shown.bs.modal', function () {
	  $('#ms_i_code').trigger('focus');
})
// This is a callback for autocomplete
function doneAutocomp() {
	if ($modalPurNewSearch.is(':visible'))
		searchCode();
}
// Search inventory by i_code/
function searchCode() {
	var code = document.getElementById("ms_i_code").value;
	if (code == "")
		return false;
	getRequest("getInvByCode.php?code="+code, searchCodeYes, searchCodeNo);
}
// New thisItem
function newThisItem(inv) {
	thisItem = new Object();
	thisItem['p_id'] = pId;
	thisItem['i_id'] = inv['i_id'];
	thisItem['i_code'] = inv['i_code'];
	thisItem['i_name'] = inv['i_name'];
	thisItem['old_count'] = inv['count'];	
	thisItem['old_price'] = inv['price'];
	thisItem['old_cost'] = inv['cost'];		
	thisItem['m_no'] = inv['m_no'];
	thisItem['path'] = inv['path'];
	thisItem['position'] = inv['position'];
	thisItem['unit'] = inv['unit'];	
	thisItem['count'] = "0";
	thisItem['cost'] = inv['cost'];;
	thisItem['price'] = inv['price'];
}
// Result back
function searchCodeYes(invs) {
	var inv = invs[0];
	// check if the product exists
	if (findArray(purItems, "i_id", inv['i_id']) != null) {
		alert(myRes['msgErrDupData']);
		$('#m_i_code').trigger('focus');
		return;
	}
	// close search window
	$modalPurNewSearch.modal("toggle");	
	// thisItem
	thisType = 0;
	newThisItem(inv);
	// Search variant
	mdvsInitVariant();
	getRequest("getVariant.php?i_id="+inv['i_id'], searchVariantYes, searchVariantNo);		
	// set values
	document.getElementById("m_i_code").innerText = thisItem['i_code'];
	document.getElementById("m_i_name").value = thisItem['i_name'];	
	document.getElementById("m_old_cost").innerText = thisItem['old_cost'];	
	document.getElementById("m_old_price").innerText = thisItem['old_price'];
	document.getElementById("m_unit_str").innerText = "x"+thisItem['unit'];	
	// set default value
	document.getElementById("m_cost").value = thisItem['cost'];	
	document.getElementById("m_price").value = thisItem['price'];
	document.getElementById("m_quantity").innerText = "0";
	// hide 'delete'
	document.getElementById("btnDel").style.display = "none";
	// Position
	document.getElementById("m_position").value = thisItem['position'] == null ? "" : thisItem['position'];
	// show modal
	purItemType = 0;
	$modalPurItem.modal();
}
// Error or no found
function searchCodeNo(invs) {
	alert(myRes['msgErrProductNoExist']);
	$('#m_i_code').trigger('focus');
}
// Search variant back
function searchVariantYes(result) {
	mdvsVariant = result;
	showVariant(true);
}
function searchVariantNo(result) {
	mdvsVariant = null;
	showVariant(false);
}
// Add new item
function addItem(count, cost, price) {
	// add new item to purItems
	thisItem['id'] = itemIdCount;
	thisItem['count'] = count;
	thisItem['cost'] = cost;
	thisItem['price'] = price;
	var subtotal = 0;
	thisItem['real_count'] = (parseInt(thisItem['count'])*parseInt(thisItem['unit'])).toString();
	subtotal =  parseInt(thisItem['real_count'])*parseFloat(thisItem['cost']);
	thisItem['subtotal'] = subtotal.toFixed(2);
	purItems[itemCount] = thisItem;
	// add variant to purVariant
	if (mdvsVariant != null) {
		purVariant[variantCount] = mdvsVariant;
		variantCount++;
	}
	// add new item to table		
	var rows = [];
	var countStr, dataStr, imgSrc, imgStr;
	if (thisItem['i_name'] != null)
		dataStr = "<a style='font-weight:bold;'>"+thisItem['i_code']+"</a><br>"+"<a>"+thisItem['i_name']+"</a>";
	else
		dataStr = "<a style='font-weight:bold;'>"+thisItem['i_code']+"</a>";
	imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+thisItem['m_no']+"_s.jpg";
	imgStr = "<img width='60' height='60' style='border:1px dotted; object-fit: cover' src='"+imgSrc+"' >";
	if (thisItem['unit'] == "1")
		countStr = thisItem['count'];
	else
		countStr = thisItem['count']+" (x"+thisItem['unit']+")";
	rows.push({
		id: itemIdCount,
		idx_image: imgStr,
		idx_data: dataStr,
		idx_count: countStr,
		idx_cost: thisItem['cost'],
		idx_subtotal: thisItem['subtotal']
	});
	$table.bootstrapTable('append', rows);
	// recalculate summary
	sumCount += parseInt(thisItem['real_count']); 
	sumCost += subtotal;
	// increase counts
	itemCount++;
	itemIdCount++;
	displaySum();
	// add new item to database
	addDbItem(thisItem);
	saveDbPur();
	if (mdvsVariant != null)
		addDbVariant(mdvsVariant);
}
/************************************************************************
	EDIT ITEM 
************************************************************************/
$table.on('click-row.bs.table', function (e, row, $element) {
	var index = getItemIndexById(row.id);
	if (index < 0)
		return;	
	thisItem = purItems[index];
	thisType = 1;
	// set values
	document.getElementById("m_i_code").innerText = thisItem['i_code'];
	document.getElementById("m_i_name").value = thisItem['i_name'];	
	document.getElementById("m_cost").value = thisItem['cost'];	
	document.getElementById("m_price").value = thisItem['price'];
	document.getElementById("m_old_cost").innerText = thisItem['old_cost'];	
	document.getElementById("m_old_price").innerText = thisItem['old_price'];
	document.getElementById("m_unit_str").innerText = "x"+thisItem['unit'];		
	// Show 'delete'
	document.getElementById("btnDel").style.display = "block";
	// Variant
	mdvsInitVariant();
	var v_idx = getVariantIndexById(thisItem['i_id']);
	if (v_idx >= 0) {
		mdvsVariant = purVariant[v_idx];
		showVariant(true);
	} else {
		mdvsVariant = null;
		showVariant(false);
	}
	// Position
	document.getElementById("m_position").value = thisItem['position'] == null ? "" : thisItem['position'];
	// Show modalPurItem
	purItemType = 1;
	$modalPurItem.modal();
});
// Update item
function updateItem(count, cost, price) {
	// Calculate
	var real_count = "", subtotal = 0, countStr = "";
	if (thisItem['unit'] == "1") {
		countStr = count;
		real_count = count;
	}
	else {
		countStr = count+" (x"+thisItem['unit']+")";
		real_count = (parseInt(count)*parseInt(thisItem['unit'])).toString();		
	}
	subtotal =  parseInt(real_count)*parseFloat(cost);
	// update table
	$table.bootstrapTable('updateCellByUniqueId', {
        id: thisItem['id'],
        field: 'idx_count',
        value: countStr
     })
	 $table.bootstrapTable('updateCellByUniqueId', {
        id: thisItem['id'],
        field: 'idx_cost',
        value: cost
     })
	 $table.bootstrapTable('updateCellByUniqueId', {
        id: thisItem['id'],
        field: 'idx_subtotal',
        value: subtotal.toFixed(2)
     }) 
	// Find orderItem
	var item = new Object();
	item['p_id'] = thisItem['p_id'];
	item['i_id'] = thisItem['i_id'];
	item['cost'] = cost;
	item['price'] = price;
	var diff = parseInt(thisItem['count']) - parseInt(count); 
	if (diff > 0) {
		item['count'] = diff.toString();
		updateDbItem(item, 0);
	} else {
		var diff1 = 0 - diff;
		item['count'] = diff1.toString();
		updateDbItem(item, 1);
	}
	sumCount = sumCount - parseInt(thisItem['real_count']) + parseInt(real_count);
	sumCost = sumCost - parseFloat(thisItem['subtotal']) + subtotal;
	displaySum();
	saveDbPur();
	// update thisItem
	thisItem['count'] = count;
	thisItem['real_count'] = real_count;
	thisItem['cost'] = cost;
	thisItem['price'] = price;
	thisItem['subtotal'] = subtotal.toFixed(2);	
	// update variant
	if (mdvsVariant != null)
		updateDbVariant(mdvsVariant);
}
/************************************************************************
	DELETE ITEM 
************************************************************************/
function delItem() {
	if (!confirm(myRes['msgConfirmDelete'])) {
		return;
	}
	$modalPurItem.modal("toggle");	
	// Remove item from table
	$table.bootstrapTable('removeByUniqueId', thisItem['id']);
	// Delete from database
	delDbItem(thisItem);
	// Recalculate summary
	sumCount = sumCount - parseInt(thisItem['real_count']);
	sumCost = sumCost - parseFloat(thisItem['subtotal']);
	// Remove item form purItems
	var index = getItemIndexById(thisItem['id']);
	purItems.splice(index, 1);
	itemCount = itemCount - 1;
	displaySum();
	saveDbPur();
	// delete variant
	if (mdvsVariant != null) {
		delDbVariant(mdvsVariant);
		v_idx = getVariantIndexById(mdvsVariant['i_id']);
		purvariant.splice(index, 1);
		variantCount = variantCount - 1;
	}
}
/************************************************************************
	DATABASE FUNCTIONS
************************************************************************/
// Add puritem to database
function addDbItem(item) {	
	var link = "postPurItemAdd.php";
	var form = new FormData();
	form.append('puritem', JSON.stringify(item));
	postRequest(link, form, itemOk, itemError);
}
// Delete puritem from database
function delDbItem(item) {
	var link = "postPurItemDel.php";
	var form = new FormData();
	form.append('puritem', JSON.stringify(item));
	postRequest(link, form, itemOk, itemError);
}
// Update puritem in database
function updateDbItem(item, option) {	
	var link = "postPurItemUpdate.php";
	var form = new FormData();
	form.append('option', option);
	form.append('puritem', JSON.stringify(item));
	postRequest(link, form, itemOk, itemError);
}
// Update position
function updateDbPosition(position, iId) {	
	var link = "postUpdateTableCol.php";
	var form = new FormData();
	form.append('table', "inventory");
	form.append('col', "position");
	form.append('value', position);
	form.append('col1', "i_id");
	form.append('value1', iId);
	postRequest(link, form, itemOk, itemError);
}
// Back Yes
function itemOk(ok) {
	
}
// Back No
function itemError(err) {

}
// Prepare purchase record for database
function preparePur() {
//	pur['s_id'] = "0";	
	pur['count_sum'] = sumCount.toString();
	pur['cost_sum'] = sumCost.toFixed(2);
}
// Save purchase to database
function saveDbPur() {
	// Prepare order record for database
	preparePur();
	// Update order in database
	var link = "postPurUpdate.php";
	var form = new FormData();
	form.append('pur', JSON.stringify(pur));
	postRequest(link, form, saveDbYes, saveDbNo);	
}
function saveDbYes(ok) {
	
}
function saveDbNo(err) {

}
// Destroy purchase
function destroyDone(result) {
	var url = "<?php echo $backPhp; ?>";
	window.location.assign(url);
}
function destroyError(result) {
}
function printError(result) {
}
function printDone(result) {
	var url = "<?php echo $backPhp; ?>";
	window.location.assign(url);
}
function destroyPur() {
	if (!confirm(myRes['msgConfirmDelete']))
		return;
	showDelModal(delDbPur);
	//delDbPur();
}
function printBarcode(){
	//if (!confirm("打印所有进货单条码?"))
	//	return;
	window.location.href="bc_purchase_2.php?p_id="+pur.p_id;
	//console.log(pur.p_id);
	/*var link = "postPurPrint.php";
	var form = new FormData();
	form.append('pur', JSON.stringify(pur));
	postRequest(link, form, printDone, printError);	*/

}
function delDbPur() {
	var link = "postPurDel.php";
	var form = new FormData();
	form.append('pur', JSON.stringify(pur));
	if(purItems && purItems.length > 0)
		form.append('puritems', JSON.stringify(purItems));
	if(purVariant && purVariant.length > 0)
		form.append('purvariants', JSON.stringify(purVariant));
	postRequest(link, form, destroyDone, destroyError);	
}
/************************************************************************
	ADD NEW INVENTORY
************************************************************************/
function newInv() {
	var url = "inv_view.php?back=purchase&p_id="+pId;
	window.location.assign(url);
}
/************************************************************************
	SYSTEM FUNCTIONS
************************************************************************/
// Prevent 'enter' key to submit
$('form input').keydown(function (e) {
    if (e.keyCode == 13) { 
        e.preventDefault();
    } 
});
// Validation
function onlyDigits(s) {
	var d;
	for (var i=0; i<s.length; i++) {
		d = s[i];
		if (d < "0" || d > "9")
			return false
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

/************************************************************************
	VARIANT
************************************************************************/
// Find variant in purVariant by i_id
function getVariantIndexById(id) {
	for (var i=0; i<variantCount; i++) {
		if (purVariant[i][0]['i_id'] == id)
			return i;
	}	
	return -1;
}
// Add variant
function addDbVariant(variant) {
	var form = new FormData();
	form.append('purvariant', JSON.stringify(variant));
	postRequest("postPurVariantAdd.php", form, null, null);
}
// Update variant
function updateDbVariant(variant) {
	var form = new FormData();
	form.append('purvariant', JSON.stringify(variant));
	postRequest("postPurVariantUpdate.php", form, null, null);
}
// Delete variant
function delDbVariant(variant) {
	var form = new FormData();
	form.append('purvariant', JSON.stringify(variant));
	postRequest("postPurVariantDel.php", form, null, null);
}
/************************************************************************
	VARIANT SELECT
************************************************************************/
var mdvsVariant = null, mdvsVariantCount = 0;
var mdvsVariantMax = 100;
function showVariant(option) {
	if (option) {
		document.getElementById("containerNoVariant").style.display = "none";
		document.getElementById("mdBtnNew").style.display = "block";
		document.getElementById("containerWithVariant").style.display = "block";
		mdvsShowVariant();
	} else {
		document.getElementById("containerNoVariant").style.display = "block";
		document.getElementById("mdBtnNew").style.display = "none";
		document.getElementById("containerWithVariant").style.display = "none";
		if (thisItem['count'] == "0")
			document.getElementById("m_count").value = "";
		else
			document.getElementById("m_count").value = thisItem['count'];
		// src for m_img
		if (thisItem['m_no'] == null) {
			document.getElementById("m_img").src = "blank.jpg";
			document.getElementById("m_img").alt = "";
		} else {
			var imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+thisItem['m_no']+".jpg";
			document.getElementById("m_img").src = imgSrc;
			document.getElementById("m_img").alt = imgSrc;
		}
		document.getElementById("m_quantity").innerText = thisItem['count'];
	}
}
function mdvsInitVariant() {	
	for (var i=0; i<mdvsVariantMax; i++) {			
		document.getElementById("m_vimg"+i).src = "blank.jpg";
		document.getElementById("m_vimg"+i).alt = "";
		document.getElementById("m_variant"+i).innerText = "";
		document.getElementById("m_vcount"+i).value = "";
		$('#m_varitem'+i).hide();
	}
}
function mdvsShowVariant() {
	mdvsVariantCount = mdvsVariant.length;
	var vquantity = 0, vcount = 0;
	for (var i=0; i<mdvsVariantCount; i++) {
		mdvsVariant[i]['p_id'] = thisItem['p_id'];
		if (mdvsVariant[i]['m_no'] != null) {
			var imgSrc = thisItem['path']+"/"+thisItem['i_id']+"_"+mdvsVariant[i]['m_no']+".jpg";
			document.getElementById("m_vimg"+i).src = imgSrc;
			document.getElementById("m_vimg"+i).alt = imgSrc;
		}
		document.getElementById("m_variant"+i).innerHTML = mdvsVariant[i]['variant'];
		if (mdvsVariant[i]['count'] == null || parseInt(mdvsVariant[i]['count']) == "") {
			vcount = 0;
			document.getElementById("m_vcount"+i).value = "";
		}
		else {
			vcount = parseInt(mdvsVariant[i]['count']);
			document.getElementById("m_vcount"+i).value = vcount.toString();
		}
		$('#m_varitem'+i).show();
		vquantity += vcount;
	}
	document.getElementById("m_quantity").innerText = vquantity.toString();
}
function countVAdd(e) {
	var id = $(e).attr("id");
	var index = id.replace("m_vadd", "");
	var vcountId = "m_vcount" + index;
	var v = document.getElementById(vcountId).value;
	if (v == "")
		v = "0";
	var d = parseInt(v);
	d++;
	document.getElementById(vcountId).value = d.toString();
	updateVQuantity();
}
function countVMinus(e) {
	var id = $(e).attr("id");
	var index = id.replace("m_vminus", "");
	var vcountId = "m_vcount" + index;
	var v = document.getElementById(vcountId).value;
	if (v == "")
		return;
	var d = parseInt(v);
	if (d == 0)
		return;
	d--;
	document.getElementById(vcountId).value = d.toString();
	updateVQuantity();
}
function countVInput(e) {
	updateVQuantity();
}
function updateVQuantity() {
	var vquantity = 0, vcount = 0;
	for (var i=0; i<mdvsVariantCount; i++) {
		vcount = document.getElementById("m_vcount"+i).value;
		if (vcount == "")
			vcount = "0";			
		vquantity += parseInt(vcount);
	}
	document.getElementById("m_quantity").innerText = vquantity.toString();
}
function getVCount() {
	var vquantity = 0, vcount = 0, vdiff = 0;
	for (var i=0; i<mdvsVariantCount; i++) {
		vcount = document.getElementById("m_vcount"+i).value;
		if (vcount == "")
			vcount = "0";		
		vdiff = parseInt(vcount) - parseInt(mdvsVariant[i]['count']);
		mdvsVariant[i]['count'] = parseInt(vcount);
		mdvsVariant[i]['count_diff'] = vdiff;
		vquantity += parseInt(vcount);
	}
	return vquantity;
}
function mdShowImageView(e) {
	var altSrc = $(e).attr("alt");
	if (altSrc == "")
		return;
	document.getElementById("m_imageZoom").src = altSrc;
	document.getElementById("m_imageView").style.display = "block";
}
/************************************************************************
	NEW VARIANT
************************************************************************/
var $modalNewVariant = $("#modalNewVariant");
function newVariant() {
	document.getElementById("mdvs_new").value = "";
	$modalNewVariant.modal();
	$('#mdvs_new').trigger('focus');
}
function selVariants(e) {
	var x = $(e).text();
	document.getElementById("mdvs_new").value = x;
}
function saveVariant() {
	var newv = document.getElementById("mdvs_new").value.trim();
	if (newv == "") {
		$('#mdvs_new').trigger('focus');
		return;
	}
	for (var i=0; i<mdvsVariantCount; i++) {
		if (newv.toLowerCase() == mdvsVariant[i]['variant'].trim().toLowerCase()) {
			alert(myRes['msgErrDupData']);
			$('#mdvs_new').trigger('focus');
			return;
		}
	}
	// add new variant
	var newVariant = new Object();
	newVariant['p_id'] = pId;
	newVariant['i_id'] = thisItem['i_id'];
	newVariant['variant'] = newv;
	newVariant['amount'] = "0";
	newVariant['count'] = "0";
	newVariant['barcode'] = mdvsNewBarcode();
	mdvsVariant[mdvsVariantCount] = newVariant;
	// add new to table
	$('#m_varitem'+mdvsVariantCount).show();
	document.getElementById("m_variant"+mdvsVariantCount).innerText = newv;
	document.getElementById("m_vcount"+mdvsVariantCount).value = "";
	mdvsVariantCount++;
	// database - add a new inv_variant
	form = new FormData();
	form.append('variant', JSON.stringify(newVariant));
	postRequest("postVariantAdd.php", form, mdvsDbAddYes, mdvsDbAddNo);
}
function mdvsDbAddYes(result) {
	var newIdx = mdvsVariantCount-1;
	mdvsVariant[newIdx]['iv_id'] = result;
	// database - add a new pur_varaint
	if (purType == 1 && thisType == 1) {
		form = new FormData();
		form.append('purvariant', JSON.stringify(mdvsVariant[newIdx]));
		postRequest("postPurVariantAddOne.php", form, null, null); 
	}
	$modalNewVariant.modal("toggle");
}
function mdvsDbAddNo(result) {
	$modalNewVariant.modal("toggle");	
}
// Generate new barcode
function mdvsNewBarcode() {
	var newseq = 0, seq = 0;
	var seqStr = "";
	for (var i=0; i<mdvsVariantCount; i++) {
		if (mdvsVariant[i]['barcode'] != null && mdvsVariant[i]['barcode'] != "" && mdvsVariant[i]['barcode'].length > 6
			&& mdvsVariant[i]['barcode'].substr(0,6) == thisItem['i_id']) {
			seqStr = mdvsVariant[i]['barcode'].substr(6);
			seq = parseInt(seqStr);
			if (newseq <= seq)
				newseq = seq;
		}
	}
	newseq++;
	var newseqStr = newseq.toString();
	var j = 4-newseqStr.length;
	for (var i=0; i<j; i++) 
		newseqStr = "0" + newseqStr;
	var thisSeq = thisItem['i_id']+newseqStr;

	return thisSeq;
}
/************************************************************************
	NO VARIANT
************************************************************************/
function countAdd() {
	var v = document.getElementById("m_count").value;
	if (v == "")
		v = "0";
	var d = parseInt(v);
	d++;
	document.getElementById("m_count").value = d.toString();
	updateQuantity(d);
}
function countMinus() {
	var v = document.getElementById("m_count").value;
	if (v == "")
		return;
	var d = parseInt(v);
	if (d == 0)
		return;
	d--;
	document.getElementById("m_count").value = d.toString();
	updateQuantity(d);	
}
function countInput() {
	var count = document.getElementById("m_count").value;
	if (count == "")
		count = "0";
	updateQuantity(parseInt(count));
}
function updateQuantity(d) {
	document.getElementById("m_quantity").innerText = d.toString();
}
/************************************************************************
	FUNCTIONS
************************************************************************/
function findArray(array, key, value) {
	for (var i=0; i<array.length; i++) {
		if (array[i][key] == value) {
			return array[i];
			break;
		}
	}
	return null;
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

function displayValue(v) {
	if (v == "0.00" || v == "0")
		return "";
	else
		return v;
}
function checkNumber(id, min, max) {
	var data = document.getElementById(id).value;
	if (data == "")
		return "0.00";
	if (!onlyNumber(data))
		return false;
	var d = parseFloat(data);	
	if (d < min || d > max)
		return false;
	
	return d.toFixed(2);
}
/************************************************************************
	DISCOUNT
************************************************************************/

$modalOrderDis.on('shown.bs.modal', function () {
	$("#mdi_discount_rate").trigger('focus');
})

function displayDis() {
	var discount;
	if(pur["discount"] == null){
		discount = 0;
		pur["discount"] = 0;
	}
	else discount = parseFloat(pur['cost_sum'])*parseFloat(pur['discount'])/100;
	document.getElementById("mdi_discount_rate").value = displayValue(pur['discount']);
	document.getElementById("mdi_discount").value = discount.toFixed(2);
}

function oninputDis(){
	var discount = 0;

	var data = checkNumber("mdi_discount_rate", 0, 100);
	if (!data) {
		$("#mdi_discount_rate").trigger('focus');
			return false;
	}
	else
		discount = data;

	discount = parseFloat(pur['cost_sum'])*parseFloat(discount)/100;
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
		pur['discount'] = data;
	
	var discount = parseFloat(pur['cost_sum'])*parseFloat(pur['discount'])/100;
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
	document.getElementById("mf_fee1").value = displayValue(pur['fee']);
}
function showFee() {
	displayFee();
	$modalOrderFee.modal();	
}
function doneFee() {
	var data = checkNumber("mf_fee1", 0, 10000);
	if (!data) {
		$("#mf_fee1").trigger('focus');
			return false;
	}
	else
		pur['fee'] = data;
	
	$modalOrderFee.modal("toggle");	
	displaySum();
}
</script>

</body>
</html>
