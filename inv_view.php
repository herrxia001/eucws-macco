<?php
/********************************************************************************
	File:		inv_view.php
	Purpose:	Add new or edit inventory
*********************************************************************************/

// Start session; If session expired, load the login page.
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

// Include files
include_once 'db_functions.php';
include_once 'resource_'.$_SESSION['uLanguage'].'.php';
$thisResource = new myResource();

$mySuppliers = dbQueryAllSuppliers();
$myInvTypes = dbQueryTypes();
$myUnits = dbQueryUnits();
$myVariants = dbQueryVariants();
$myAppTypes = dbAppTypesQuery();

$myNames = dbQueryAllName();
// Create path for images
$myPath = "files/".$_SESSION['uDb']."/".strval(rand(0, 9));
// back PHP
$backPhp = 'inv_mgt.php';
// action type: 0 - new; 1 - edit
$invType = 0;
// purchase ID
$pId = "";

// if GET: (if id: query by ID; else: get new ID)
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(isset($_GET['back']))
	{
		$backPhp = $_GET['back'].'.php';
		if ($_GET['back'] == "purchase")
		{
			$pId = $_GET['p_id'];
			$backPhp = "purchase.php?p_id=".$pId;
		}
	}
	if(isset($_GET['id']))
	{	
		// Query inventory by i_id
		$myId = $_GET['id'];
		$myInventory = dbQueryInventory($myId);
		$myPath = $myInventory['path'];
		// Get s_name and t_name
		$mySName = dbGetSupNameById($myInventory['s_id'], $mySuppliers);
		$myTName = dbGetTypeNameById($myInventory['t_id'], $myInvTypes);

		// Get images
		$myInvImages = dbGetInvImages($myId);
		// Get variant
		$myVariant = dbQueryVariant($myId);
		if ($myVariant <= 0)
			$withVariant = FALSE;
		else
			$withVariant = TRUE;
		// APP
		$myAppData = dbAppProductQueryById($myId);
		$myAppImages = dbAppImagesQuery($myId);
		// Price
		$myInvPrice = dbInvPriceQuery($myId);
		
		$invType = 1;
	}
	else
	{
		// New inventory
		$myId = dbGetInvId();			
		$invType = 0;
	}
}

?>

<!doctype html>
<html lang="zh">
<head>
    <?php include 'include/header.php' ?>	
	<title>EUCWS - Product</title>
</head>

<style>
body {
 padding-top: 0.2rem;
}
.dropdown-menu{
    max-height: 200px;
    overflow-y: scroll;
}
</style>

<body>
	<?php include 'include/modalVariant.php' ?>	
	<?php include 'include/modalApp.php' ?>	
	<?php include 'include/modalPrice.php' ?>
	<?php include 'include/modalPrint.php' ?>
	
	<form ID="form" action="" method="post">
    <div class="container">
<!-- buttons -->
	<div class="row">
		<div class="p-1 mt-2 col-2 col-sm-2 col-md-2 col-lg-2">
			<a class="btn btn-outline-secondary button-s" href=<?php echo $backPhp ?> role="button"><span class='fa fa-arrow-left'></a>
		</div>
<!-- status -->
		<div class="p-1 mt-2 col-4 col-sm-4 col-md-4 col-lg-2" align="center">
			<button type="button" class="btn btn-outline-secondary dropdown-toggle" id="status_str" data-toggle="dropdown"></button>
			<input type='hidden' id="status">
			<div class="dropdown-menu">
				<div class="dropdown-item" href="#" onclick="selStatus(this)"><?php echo $thisResource->comStatusNormal ?>
					<input type='hidden' value='0'></div>
				<div class="dropdown-item" href="#" onclick="selStatus(this)">未完成<input type='hidden' value='-1'></div>
				<div class="dropdown-item" href="#" onclick="selStatus(this)"><?php echo $thisResource->comStatusOffline ?>
					<input type='hidden' value='1'></div>
			</div>
		</div>
		<div class="p-1 col-6 col-sm-6 col-md-6 col-lg-4" align="right">
			<button type="button" class="btn btn-success" onclick="showPrintModalNoVariable()"><span class='fa fa-print'></span> 打印</button>
			<!--button type="button" class="btn btn-outline-secondary " onclick="showApp()">网站</button-->
			<label for="imgIng" class="btn btn-outline-secondary mt-2"><span class='fa fa-camera'></label>
			<input type="file" id="imgIng" name="imgIng" accept="image/*" multiple hidden>
			<button type="button" id="ok" class="btn btn-primary" onclick="submitForm()"><?php echo $thisResource->comSave ?></button>
		</div>
	</div>
<!-- invType (hidden) -->
	<input type="text" class="form-control" id="invType" name="invType" value="<?php echo $invType ?>" hidden>
<!-- path (hidden) -->
	<input type="text" class="form-control" id="path" name="path" value="<?php echo $myPath ?>" hidden>
<!-- i_id (hidden) -->
	<input type="text" class="form-control" id="i_id" name="i_id" value="<?php echo $myId ?>" hidden>				
<!-- i_code -->
	<div class="row">
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8">
			<div class="input-group-prepend"><span class="input-group-text" style="font-weight:bold; width:100px;"><?php echo $thisResource->comProductNo ?></span></div>
			<input type="text" class="form-control" id="i_code" name="i_code" value="<?php echo $myInventory['i_code'] ?>" 
				<?php if($invType == 0) echo 'autofocus' ?> required>			
		</div>
	</div>
<!-- type -->
	<div class="row"> 
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8"> 
			<div class="input-group-prepend"><span class="input-group-text" style="font-weight:bold; width:100px;"><?php echo $thisResource->comType ?></span></div>
			<input type="text" class="form-control autocomplete" id="t_name" name="t_name" value="<?php echo $myTName ?>">
			<div class="input-group-append">
				<div class="dropdown dropleft">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"></button>
					<ul class="dropdown-menu" id="typeList">
						<?php for($i=0; $i<count($myInvTypes); $i++) 
						echo "<a class='dropdown-item' href='#' onclick='selType(this)'>".$myInvTypes[$i]['t_name']."</a>";
						?>
					</ul>
				</div>
			</div>
			<button type="button" class="ml-1 btn btn-primary" id="btnNewType" onclick="newType()"><span class='fa fa-plus'></button>
		</div>
	</div>

<?php if(is_array($seasonArr)){?>
<!-- season -->
<div class="row">
    <div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8">
        <div class="input-group-prepend"><span class="input-group-text" style="font-weight:bold; width:100px;"><?php echo $thisResource->comSeason ?></span></div>
        <input type="text" class="form-control autocomplete" id="i_season" name="i_season" value="<?php echo $seasonArr[$myInventory['season']] ?>" readonly>
        <input type="hidden" id="i_season_id" name="i_season_id" value="<?php echo $myInventory['season'] ?>">
        <div class="input-group-append">
            <div class="dropdown dropleft">
                <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"></button>
                <ul class="dropdown-menu" id="seasonList">
                    <?php foreach($seasonArr AS $key => $value)
                        echo "<a class='dropdown-item' href='#' onclick='selSeason(this, ".$key.")'>".$value."</a>";
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>
        <?php } ?>
<!-- i_name -->	
	<div class="row">
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comName ?></span></div>
			<input type="text" class="form-control" id="i_name" name="i_name" value="<?php echo $myInventory['i_name'] ?>">

			<div class="input-group-append">
				<div class="dropdown dropleft">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"></button>
					<ul class="dropdown-menu" id="nameList">
						<input type="text" style="position: sticky; top: 0; margin-left: 20px; margin-right: 20px; width: calc(100% - 40px);" class="form-control" placeholder="搜索.." id="myinput_name" oninput="filterFunction($(this))">
						<?php for($i=0; $i<count($myNames); $i++) 
						echo "<a class='dropdown-item' href='#' onclick='selName(this)'>".$myNames[$i]['i_name']."</a>";
						?>
					</ul>
				</div>
			</div>
		</div>
	</div>	
<!-- count -->
	<div class="row">
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8"> 
			<div class="input-group-append"><span class="input-group-text" style="font-weight:bold; width:100px;">
				<?php echo $thisResource->comQuantity ?></span></div>
			<input type="number" min="0" step="1" class="form-control" id="count" name="count" value="<?php echo $myInventory['count'] ?>" required>
			<button type="button" id="btnUnit" name="btnUnit" class="ml-1 btn btn-secondary" onclick="showUnitWindow()"></button>
			<button type="button" id="btnVariant" name="btnVariant" class="ml-1 btn btn-success" onclick="showVariantWindow()"><?php echo $thisResource->comVariant ?></button>
		</div>
	</div>
<!-- cost -->
	<div class="row">
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8"> 
			<div class="input-group-prepend"><span class="input-group-text" style="font-weight:bold; width:100px;">
				<?php echo $thisResource->comCost ?></span></div>
			<input type="number" min="0" step="0.01" class="form-control" id="cost" name="cost" value="<?php echo $myInventory['cost'] ?>" required>
		</div>
	</div>	
<!-- price -->	
	<div class="row">
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8"> 
			<div class="input-group-prepend"><span class="input-group-text" style="font-weight:bold; width:100px;"><?php echo $thisResource->comPrice ?></span></div>
			<input type="number" min="0" step="0.01" class="form-control" id="price" name="price" value="<?php echo $myInventory['price'] ?>" required>
			<button type="button" id="btnPrice" class="ml-1 btn btn-outline-primary" onclick="showPrice()"><span class='fa fa-ellipsis-h'></span></button>
		</div>
	</div>		
<!-- supplier -->
	<div class="row">
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comSupplier ?></span></div>
			<input type="text" class="form-control autocomplete" id="s_name" name="s_name" value="<?php echo $mySName ?>">
			<div class="input-group-append">
				<div class="dropdown dropleft">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"></button>
					<ul class="dropdown-menu" id="supList">
						<input type="text" style="position: sticky; top: 0; margin-left: 20px; margin-right: 20px; width: calc(100% - 40px);" class="form-control" placeholder="搜索.." id="myinput" oninput="filterFunction($(this))">
						<?php for($i=0; $i<count($mySuppliers); $i++) 
						echo "<a class='dropdown-item' href='#' onclick='selSup(this)'>".$mySuppliers[$i]['s_name']."</a>";
						?>
					</ul>
				</div>
			</div>
			<button type="button" class="ml-1 btn btn-primary" id="btnNewSup" onclick="newSup()"><span class='fa fa-plus'></button>
		</div>							
	</div>
<!-- position -->	
	<div class="row">		
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comPosition ?></span></div>
			<input type="text" class="form-control" id="position" name="position" value="<?php echo $myInventory['position'] ?>">
		</div>
	</div>
<!-- color -->	
	<div class="row">		
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comColor ?></span></div>
			<input type="text" class="form-control" id="color" name="color" value="<?php echo $myInventory['color'] ?>">
		</div>
	</div>
<!-- comment -->	
	<div class="row">		
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comRemark ?></span></div>
			<input type="text" class="form-control" id="comment" name="comment" value="<?php echo $myInventory['comment'] ?>">
		</div>
	</div>
<!-- barcode -->	
	<div class="row">		
		<div class="input-group p-1 col-12 col-sm-12 col-md-12 col-lg-8"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comBarcode ?></span></div>
			<input type="text" class="form-control" id="code1" name="code1" value="<?php echo $myInventory['code1'] ?>">
			<button type="button" class="ml-1 btn btn-outlined-secondary" id="btnBarcode" onclick="inputBarcode()">&#9998</button>
		</div>
	</div>
<!-- image thumbnails -->
	<div class="row p-1">
		<div class="col-12 col-sm-12 col-md-12 col-lg-8" style="border:1px solid lightgray;">
		<?php for($i=0; $i<100; $i++){ ?>
			<img id="image_<?php echo $i ?>" src="blank.jpg" style="object-fit: cover" width="60" height="80" class="mt-1 mb-1">
			<input type="text" class="form-control" id="imageNo_<?php echo $i ?>" name="imageNo[]" value="" hidden>
		<?php } ?>
		</div>
	</div>
	
	</div> <!-- end of container -->
</form> <!-- end of form -->
	
<!-- Modal: Display big image when user clicks the image thumbnail-->
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body" align="center">
					<img id="mImage" src="" alt="image" style="border:1px dotted; object-fit: cover" width="300" height="400">
					<input type="text" class="form-control" id="mImageId" hidden>
				</div>
				<div class="modal-footer">
					<button type="button" id="mDel" name="mDel" class="btn btn-danger" onclick="delImage()">
						<?php echo $thisResource->comDelete ?></button>
					<a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
					<button type="button" id="mClose" name="mClose" class="btn btn-secondary" onclick="closeImage()">
						<span class='fa fa-times'></span></button>
				</div>
			</div>
		</div>
	</div> <!-- End of myModal -->	
	
<!-- Modal: inventory log -->
	<div class="modal fade" id="modalLog" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modalLabel"><?php echo $thisResource->fmInvNewCapHTitle ?></h5>	
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12">	
							<table id="table" data-search="false" data-toggle="table" >
								<thead>
								<tr>
								<th data-field="h_time"><?php echo $thisResource->fmInvNewCapHTime ?></th>
								<th data-field="amount"><?php echo $thisResource->fmInvNewCapHAmount ?></th>
								<th data-field="cost"><?php echo $thisResource->fmInvNewCapHCost ?></th>
								<th data-field="source"><?php echo $thisResource->fmInvNewCapHSource ?></th>
								</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
				</div>
			</div>
		</div>
	</div> <!-- End of modalLog -->		

<!-- Modal for add type -->
<div class="modal fade" id="modalType" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mtTitle"><?php echo $thisResource->comTypeAdd ?></b>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="input-group p-1">	
					<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comType ?></span></div>				
					<input type="text" class="form-control" id="mtTypeName" name="mtTypeName">
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary" onclick="mtAddType()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal for add type -->

<!-- Modal for add sup -->
<div class="modal fade" id="modalSup" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="msTitle"><?php echo $thisResource->comSupplier ?></b>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="input-group p-1">	
					<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comID ?></span></div>				
					<input type="text" class="form-control" id="msSupCode" name="msSupCode">
					<button type="button" class="ml-1 btn btn-outlined-secondary" id="msBtnAutoSup" onclick="msAutoSup()">&#9998;</button>
				</div>
			</div>
			<div class="row">
				<div class="input-group p-1">	
					<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comName ?></span></div>				
					<input type="text" class="form-control" id="msSupName" name="msSupName">
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary" onclick="msAddSup()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal for add sup -->

<!-- Modal for unit -->
<div class="modal fade" id="modalUnit" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<b class="modal-title" id="mtTitle"><?php echo $thisResource->comUnit ?></b>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		</div>
		<div class="modal-body">
			<div class="container p-1" id="muUnitArea">
				<div class="row">
					<div class="dropdown p-1">
						<a><?php echo $thisResource->comUnit ?></a>									
						<button type="button" class="p-1 ml-2 btn btn-secondary dropdown-toggle" id="muUnit" data-toggle="dropdown"></button>
						<div class="dropdown-menu">
							<a class="dropdown-item" href="#" onclick="muSelJian()">x 1</a>
							<a class="dropdown-item" href="#" onclick="muSelBao()">x N</a>
						</div>
					</div>
				</div>
			</div>
			<div class="container p-1" id="muSelUnitArea">	
				<div class="row">					
					<div class="dropdown p-1">
						<a><?php echo $thisResource->comQuantity ?></a>				
						<button type="button" class="p-1 ml-2 btn btn-secondary dropdown-toggle" id="muUnitNum" data-toggle="dropdown"></button>
						<div class="dropdown-menu" id="muUnitNumList">
							<a class="dropdown-item" href="#" onclick="muNewUnitNum()"><?php echo $thisResource->comAdd ?></a>
							<?php if(is_array($myUnits))for($i=0; $i<count($myUnits); $i++) 
							echo "<a class='dropdown-item' href='#' onclick='muSelUnitNum(this)'>".$myUnits[$i]['units']."</a>";
							?>
						</div>
					</div>
				</div>
				<div class="container p-1" id="muNewUnitArea">
					<div class="row input-group">
						<input type="number" min="0" step="1" class="form-control" id="muNewNum" name="muNewNum">
						<button type="button" class="ml-1 btn btn-secondary" id="muBtnNewCancel" onclick="muNewCancel()"><span aria-hidden="true">&times;</span></button>
						<button type="button" class="ml-1 btn btn-primary" id="muBtnNewOk" onclick="muNewOk()"><span class='fa fa-check'></span></button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary" onclick="muDone()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal for add type -->

</body>

<script src="js/ajax.js"></script>
<script src="js/autocomplete.js?202108131819"></script>
<script src="js/modalVariant.js?202110261638"></script>
<script>
/********************************************************************
	PHP
********************************************************************/
var myRes = <?php echo json_encode($thisResource) ?>;
var myId = <?php echo json_encode($myId) ?>;
var myUnits = <?php echo json_encode($myUnits) ?>;
var myInvImages = <?php echo json_encode($myInvImages) ?>;
if (myInvImages == null || myInvImages == 0) {
	myInvImages = new Array();
}
var a_sups = <?php echo json_encode($mySuppliers) ?>;
var a_types = <?php echo json_encode($myInvTypes) ?>;
var a_variants = <?php echo json_encode($myVariants) ?>;
/********************************************************************
	LOCAL
********************************************************************/
var myPath, myUnit, myType, withVariant;
var myInv;

var imgNoCount = 0, imgCount = 0;
var $modal = $("#myModal");

var pId = "<?php echo $pId ?>";
var autoBarcode = false;
// APP
var myAppData = <?php echo json_encode($myAppData) ?>; 
if (myAppData == null || myAppData == 0) {
	myAppData = new Object();
	myAppData['i_id'] = myId;
	myAppData['state'] = "-1";
}
var myAppImages = <?php echo json_encode($myAppImages) ?>; 
if (myAppImages == null || myAppImages == 0) {
	myAppImages = new Array();
}
var myAppTypes = <?php echo json_encode($myAppTypes) ?>;		
// PRICE
var myInvPrice = <?php echo json_encode($myInvPrice) ?>;
if (myInvPrice == null || myInvPrice == 0)
	myInvPrice = new Array();
	
/********************************************************************
	SYSTEM FUNCTIONS
********************************************************************/	
$(document).ready(function(){
	 // Get values from $myInventory
	 myId = document.getElementById("i_id").value;
	 myPath = document.getElementById("path").value;
	 myType = parseInt(document.getElementById("invType").value);
	 // Show UNIT
	 if (myType == 1) {
		 myInv = <?php echo json_encode($myInventory) ?>;
		 autoBarcode = true;
		 document.getElementById("code1").readOnly = true;
		 myUnit = myInv['unit'];
		 withVariant = "<?php echo $withVariant ?>";
		 if (withVariant) {
			document.getElementById("count").readOnly = true;
			document.getElementById("btnBarcode").style.display = "none";
		 }
	 } else {
		 myInv = null;
		 inputBarcode();
		 myUnit = "1";
	 }
	 showStatus();
	 displayUnits();
	 // autocomplete
	autoSups(document.getElementById("s_name"), a_sups);
	autoTypes(document.getElementById("t_name"), a_types);
	// Get images
	for (var i=0; i<100; i++) {
		document.getElementById("image_"+i).style.display = "none";
	}
	initImages();
	 // image click show modal
	var id = 0;
	$(document).on('click', "[id^=image_]", function(){		 
		if($(this).attr("src") != ''){
			$modal.modal();	
			var newSrc = $(this).attr("src");
			$("#mImage").prop("src", newSrc);
			id = $(this).attr("id");
			id = id.replace("image_", '');
			$("#mImageId").val(id);
		}
	});
 });
// Prevent 'enter" key to submit
$('form input').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
		return false;
    }
});
/********************************************************************
	IMAGE FUNCTIONS
********************************************************************/
// Get images
function initImages() {
	imgCount = myInvImages.length;		
	for (var i=0; i<imgCount; i++){
		var imgSrc = myPath+"/"+myId+"_"+myInvImages[i]['m_no']+".jpg";
		document.getElementById("image_"+i).src = imgSrc;
		document.getElementById("image_"+i).style.display = "inline";
		document.getElementById("imageNo_"+i).value = myInvImages[i]['m_no'];
		if(parseInt(myInvImages[i]['m_no']) > imgNoCount)
			imgNoCount = parseInt(myInvImages[i]['m_no']);
	}
	imgNoCount++; 
}
// Delete image
function delImage(){
	if (!confirm(myRes['msgConfirmDelete'])) {
		return;
	}
	
	var id = 0, id_next = 0, id_last = 0, i = 0, ImageNo = 0;
	
	id = $("#mImageId").val();
	id_last = imgCount - 1;
	imageNo = document.getElementById("imageNo_"+id).value;
	delFile(imageNo);
	
	for (i=parseInt(id); i<imgCount-1; i++){
		id_next = i + 1;
		document.getElementById("image_"+i).src = document.getElementById("image_"+id_next).src;
		document.getElementById("imageNo_"+i).value = document.getElementById("imageNo_"+id_next).value;
	}
	document.getElementById("image_"+id_last).src = "";
	document.getElementById("image_"+id_last).style.display = "none";
	document.getElementById("imageNo_"+id_last).value = "";
	imgCount = imgCount - 1; 
	$("#mImage").prop("src","");
	$("#mImageId").val("");
	$modal.modal('toggle');
}
// Close the modal
function closeImage(){
	$("#mImage").prop("src","");
	$("#mImageId").val("");
	$modal.modal('toggle');
}
// Compress image
function compressImage(file, cb, option) {	
    var  maxWidth = 1200, maxHeight = 1200;

    var img = new Image();
    img.src = URL.createObjectURL(file);

    var canvas = document.createElement("canvas");
    var ctx = canvas.getContext("2d");

    img.onload = function() {
        var ratio = 1;
        if (img.width > maxWidth)
            ratio = maxWidth / img.width;
        else if (img.height > maxHeight)
            ratio = maxHeight / img.height;

        canvas.width = img.width * ratio;
        canvas.height = img.height * ratio;
		ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

        cb(canvas.toDataURL("image/jpeg", 0.5), option);
    };
}
// Callback function: Display and submit image
function displayImage(image, option){
	//Display image
	document.getElementById("image_"+imgCount).src = image;
	document.getElementById("image_"+imgCount).style.display = "inline";
	document.getElementById("imageNo_"+imgCount).value = imgNoCount;
	// Display image in modalVariant
	if (option) {
		mdvEmptyImages();
		var img = document.getElementById("mdsi_image_"+imgCount);
		img.src = image;
		img.style.display = "inline";
		img.style.border = "4px solid red";
		var defaultImg = document.getElementById("mdsi_selimage");
		defaultImg.src = image;
		selImageId = imgCount;
	}
	// Convert image to blob file for submit
	var newfile = convertImage(image);
	addFile(newfile, imgNoCount);
	
	imgNoCount++;
	imgCount++;
	if(imgCount == 100)
		document.getElementById("imgIng").disabled = true;
}
//Get image after capture
var inputImg = document.getElementById("imgIng");
// Display image and submit
inputImg.onchange = function () {	
	//var file = inputImg.files[0];	
	// displayImage is a callback function
	//compressImage(file, displayImage, 0);	
	for(var i = 0; i < inputImg.files.length; i++){
		compressImage(inputImg.files[i], displayImage, 0);	
	}
}
// Convert image to file for upload
function convertImage(image){
	var byteString = atob(image.split(',')[1]);
	var ab = new ArrayBuffer(byteString.length);
	var ia = new Uint8Array(ab);
	for (var i = 0; i < byteString.length; i++) {
		ia[i] = byteString.charCodeAt(i);
	}
	var blob = new Blob([ia], { type: 'image/jpeg' });
	
	return blob;
}
// Add image file to server
function addFile(imageFile, imageNo){ 
	var form = new FormData();
	form.append('id', myId);
	form.append('path', myPath);
	form.append('imageNo', imageNo);
	form.append('image', imageFile);
	postRequest('postImageAdd.php', form, null);
}
// Delete image file from server
function delFile(imageNo){
	var form = new FormData();
	form.append('id', myId);
	form.append('path', myPath);
	form.append('imageNo', imageNo);
	postRequest('postImageDel.php', form, null);
}
/********************************************************************
	SHOW LOG
********************************************************************/
// Show history
function displayLog(log){
	if(log.lenth <= 0)
		return;
	
	var $modal1 = $("#modalLog");
	$modal1.modal();
	var $table = $("#table");
	$table.bootstrapTable('removeAll');
	
	var rows = [];
	for(var i=0; i<log.length; i++){
		var l = log[i]['cost'];
		if (l == "0.00") l = "N/A";
		rows.push({
			h_time: log[i]['h_date'],
			amount: log[i]['amount'],
			cost: l,
			source: getLogSource(log[i]['source'])
			});
		}
	$table.bootstrapTable('append', rows);		
}
function getLogSource(id){
	var src = "";
	switch(id) {
		case "1": src = "入库"; break;
		case "2":src = "恢复"; break;
		case "3":src = "修改"; break;
		case "10":src = "出库"; break;
		case "11":src = "删除"; break;
		case "13":src = "修改"; break;
		default:src = "创建";
	}	
	return src;
}
function showLog(){
	var link =  "getHist.php?id="+myId;
	getRequest(link, displayLog);
}
/********************************************************************
	SUBMIT
********************************************************************/
var inv = new Object();
var ivColumns = ['i_id', 'i_code', 'i_name', 'count', 'cost', 'price', 's_name', 't_name', 'comment', 'path', 'code1', 'position', 'color', 'status'];

function getBackYes(result){
	alert(myRes['msgErrDupID']);	
	$("#i_code").focus();
	return;	
}

function getBackNo(result){
	submitFormContinue();	
}

function submitFormContinue() {
	if (autoBarcode == false && inv['code1']!=genBarcode())
		checkBarcode(inv['code1']);
	else
		submitFormContinue1();
}

function submitFormContinue1() {
	// check type (t_name, t_id)
	if (inv['t_name'] == "") {
		$("#t_name").focus();
		return false;
	}
	inv['t_id'] = getTypeIdByName(inv['t_name']);
	if (inv['t_id'] == "0") {
		document.getElementById("mtTypeName").value = inv['t_name'];	
		$modalType.modal();
		return false;
	}
	// check name
	if (inv['i_name'].length > 100) {
		alert(myRes['msgErrDataInput']);
		$("#i_name").focus();
		return false;
	}
	// check count
	if (inv['count'] == "" || !onlyDigits(inv['count']) || 
			parseInt(inv['count']) < 0 || parseInt(inv['count']) > 8000000) {
		alert(myRes['msgErrDataInput']);
		$("#count").focus();
		return false;
	}
	inv['count'] = parseInt(inv['count']).toString();
	// check cost
	if (inv['cost'] == "" || !onlyNumber(inv['cost']) ||
			parseFloat(inv['cost']) < 0 || parseFloat(inv['cost']) > 999999) {
		alert(myRes['msgErrDataInput']);
		$("#cost").focus();
		return false;
	}
	inv['cost'] = parseFloat(inv['cost']).toFixed(2);
	// check price
	if (inv['price'] == "" || !onlyNumber(inv['price']) || 
			parseFloat(inv['price']) < 0 || parseFloat(inv['price']) > 999999) {
		alert(myRes['msgErrDataInput']);
		$("#price").focus();
		return false;
	}
/*
	if (parseFloat(inv['price']) < parseFloat(inv['cost'])) {
		if (!confirm("售价小于成本, 继续保存?")) {
			$("#price").focus();
			return false;
		}
	}
*/
	inv['price'] = parseFloat(inv['price']).toFixed(2);
	// check sup (a_name, s_id)
	inv['s_id'] = getSupIdByName(inv['s_name']);
	if (inv['s_id'] == "0" && inv['s_name'] != "") {
		document.getElementById("msSupName").value = inv['s_name'];	
		$modalSup.modal();
		return false;
	}
	
	dbSubmit();
}
function submitOk(result) {
	// Add the new inv to localStorage
	if (myType == 0) {
		var a_icode = JSON.parse(localStorage.getItem("a_icode"));
		var a_image = JSON.parse(localStorage.getItem("a_image"));
		
		a_icode.push(inv['i_code']);
		var imgFile = inv['path']+"/"+inv['i_id']+"_"+inv['m_no']+"_s.jpg";
		a_image.push(imgFile);

		localStorage.setItem("a_icode", JSON.stringify(a_icode));
		localStorage.setItem("a_image", JSON.stringify(a_image)); 
	}
	// If from purchase, add pur_item
	if (pId != null && pId != "") {
		addPurItem();
	} else {
		var url = "<?php echo $backPhp; ?>";
		window.location.assign(url);
	}
}
// Submit to database
function dbSubmit() {
	inv['unit'] = myUnit;
	inv['m_no'] = document.getElementById("imageNo_0").value;
	inv['count_a'] = inv['count'];
	var d = new Date(), m = d.getMonth() + 1;
	var t = d.getFullYear()+"-"+formatTimeStr(m)+"-"+formatTimeStr(d.getDate())
				+" "+formatTimeStr(d.getHours())+":"+formatTimeStr(d.getMinutes())+":"+formatTimeStr(d.getSeconds());
	if (myType == 0) {
		inv['time_created'] = t;
	}
	inv['time_updated'] = t;

    var i_season_id = document.getElementById("i_season_id");
    if(i_season_id) {
        inv['season'] = i_season_id.value;
    }

	var form = new FormData();
	form.append('inv', JSON.stringify(inv));
	if (myType == 0) {
		postRequest('postInvAdd.php', form, submitOk);	
	}
	else {
		postRequest('postInvUpdate.php', form, submitOk);
	}
}
// Get all values, check i_code
function submitForm() {	
	for (var i=0; i<ivColumns.length; i++) {
		inv[ivColumns[i]] = document.getElementById(ivColumns[i]).value;
	}
	if (inv['i_code'] == "") {
		$("#i_code").focus();
		return false;
	}
	if (inv['code1'] == "") {
		$("#code1").focus();
		return false;
	}
	if (myInv!=null && inv['i_code'] == myInv['i_code'])
		submitFormContinue();
	else
		getRequest("getInvByCode.php?code="+inv['i_code'], getBackYes, getBackNo);
}
/********************************************************************
	ADD PURCHASE ITEM
********************************************************************/
function addPurItemYes(result) {
	var url = "purchase.php?p_id="+pId;
	window.location.assign(url);
}
function addPurItemNo(result) {
	var url = "purchase.php?p_id="+pId;
	window.location.assign(url);
}
function addPurItem() {
	var item = new Object();
	item['p_id'] = pId;
	item['i_id'] = inv['i_id'];
	item['count'] = inv['count'];
	item['unit'] = inv['unit'];
	if (inv['unit'] == "1")
		item['real_count'] = inv['count'];
	else
		item['real_count'] = (parseInt(inv['count'])*parseInt(inv['unit'])).toString();
	item['cost'] = inv['cost'];
	item['price'] = inv['price'];
	item['old_count'] = "0";
	item['old_cost'] = "0.00";
	item['old_price'] = "0.00";

	if (nVariantCount > 0) {
		for (var i=0; i<nVariantCount; i++) {
			myVariant[i]['p_id'] = pId;
			myVariant[i]['count'] = myVariant[i]['amount'];
		}
	}
	
	var link = "postPurItemFromInv.php";
	var form = new FormData();
	form.append('puritem', JSON.stringify(item));
	if (nVariantCount > 0) {
		form.append('purvariant', JSON.stringify(myVariant));
	}
	postRequest(link, form, addPurItemYes, addPurItemNo);
}
/********************************************************************
	FUNCTIONS
********************************************************************/
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
function formatTimeStr(str) {
	var s = str.toString();
	if (s.length < 2) {
		s = "0" + s;
		return s;
	}
	else
		return s;
}
// dummy callback for autocomplete
function doneAutoComp(){
}
/********************************************************************
	SUPPLIER
********************************************************************/
var $modalSup = $('#modalSup');
var newSupIndex, newSupName;

function selSup(e) {
	var x = $(e).text();
	document.getElementById("s_name").value = x;
}
function selName(e) {
	var x = $(e).text();
	document.getElementById("i_name").value = x;
}
function getSupIdByName(name) {
	for (var i=0; i<a_sups.length; i++) {
		if (a_sups[i]['s_name'] == name) {
			return a_sups[i]['s_id'];
			break;
		}
	}
	return "0";
}
function newSup() {
	document.getElementById("msSupCode").value = "";	
	document.getElementById("msSupName").value = "";
	autoSup = true;	
	msAutoSup();
	$modalSup.modal();
}
$modalSup.on('shown.bs.modal', function () {
  $('#msSupCode').trigger('focus');
})
var autoSup = false;
function msAutoSup() {
	if (autoSup) {
		autoSup = false;
		document.getElementById("msBtnAutoSup").innerHTML = "💻";
		document.getElementById("msSupCode").readOnly = false;
		document.getElementById("msSupCode").value = "";
		$('#msSupCode').trigger('focus');
		
	} else {
		autoSup = true;
		document.getElementById("msBtnAutoSup").innerHTML = '&#9998';
		document.getElementById("msSupCode").readOnly = true;
		var autoSupCode = Date.now();
		document.getElementById("msSupCode").value = autoSupCode;
	}
}
function msAddSup(){
	var code = document.getElementById("msSupCode").value;
	if(code == "") {
		$('#msSupCode').trigger('focus');
		return false;
	}
	if (a_sups != 0) {
		for(var i=0; i<a_sups.length; i++){
			if (code == a_sups[i]['s_code']) {
				alert(myRes['msgErrDupID']);
				$('#msSupCode').trigger('focus');
				return false;
			}
		}
	}
	var name = document.getElementById("msSupName").value;
	if(name == "") {
		$('#msSupName').trigger('focus');
		return false;
	}
	
	$modalSup.modal("toggle");
	
	// add new sup to array
	if (a_sups != 0)
		newSupIndex = a_sups.length;
	else
		newSupIndex = 0;
	newSupName = name;
	var newSup = new Object();
	newSup['s_id'] = "0";
	newSup['s_code'] = code;
	newSup['s_name'] = newSupName;
	if (a_sups == 0)
		a_sups = new Array();
	a_sups.push(newSup);
	// add new sup to the dropdown list
	var ele = document.createElement("a");
    ele.classList = "dropdown-item";
    ele.href = "#";
    ele.innerText = name;
	ele.addEventListener("click", function(){document.getElementById("s_name").value = name});
    document.querySelector("#supList").appendChild(ele);	
	// update s_name
	document.getElementById("s_name").value = name;
	autoSups(document.getElementById("s_name"), a_sups);
	// add to database
	var form = new FormData();
	form.append('sup', JSON.stringify(newSup));
	postRequest('postSupAdd.php', form, postSupBack, null);	
}
function postSupBack(result) {
	a_sups[newSupIndex]['s_id'] =  result;
}
/********************************************************************
	TYPE
********************************************************************/
var $modalType = $('#modalType');
var newTypeIndex, newTypeName;

function selType(e) {
	var x = $(e).text();
	document.getElementById("t_name").value = x;
}
function selSeason(e, key) {
    var x = $(e).text();
    document.getElementById("i_season").value = x;
    document.getElementById("i_season_id").value = key;
}
function getTypeIdByName(name) {
	for (var i=0; i<a_types.length; i++) {
		if (a_types[i]['t_name'] == name) {
			return a_types[i]['t_id'];
			break;
		}
	}
	return "0";
}
function newType() {
	document.getElementById("mtTypeName").value = "";	
	$modalType.modal();
}
$modalType.on('shown.bs.modal', function () {
  $('#mtTypeName').trigger('focus');
})
function mtAddType(){		
	var name = document.getElementById("mtTypeName").value;
	if(name == "") {
		$('#mtTypeName').trigger('focus');
		return false;
	}
	if (a_types != 0) {
		for(var i=0; i<a_types.length; i++){
			if (name == a_types[i]['t_name']) {
				alert(myRes['msgErrDupData']);
				$('#mtTypeName').trigger('focus');
				return false;
			}
		}
	}
	$modalType.modal("toggle");
	
	// add new type to array
	if (a_types != 0)
		newTypeIndex = a_types.length;
	else
		newTypeIndex = 0;
	newTypeName = name;
	var newType = new Object();
	newType['t_id'] = "0";
	newType['t_name'] = newTypeName;
	if (a_types == 0)
		a_types = new Array();
	a_types.push(newType);
		
	// add new type to the dropdown list
	var ele = document.createElement("a");
    ele.classList = "dropdown-item";
    ele.href = "#";
    ele.innerText = name;
	ele.addEventListener("click", function(){document.getElementById("t_name").value = name});
    document.querySelector("#typeList").appendChild(ele);	
	// update t_name
	document.getElementById("t_name").value = name;
	autoTypes(document.getElementById("t_name"), a_types);
	// add to database
	var form = new FormData();
	form.append('t_name', name);
	postRequest('postTypeAdd.php', form, postTypeBack, null);	
}
function postTypeBack(result) {
	a_types[newTypeIndex]['t_id'] =  result;
}
/********************************************************************
	VARIANT
********************************************************************/
function showVariantWindow() {
	var myCode1 = document.getElementById("code1").value;
	mdvDisplayVariant(myId, myType, myCode1);
}
/********************************************************************
	UNIT
********************************************************************/
$modalUnit = $('#modalUnit');

function showUnitWindow() {
	if (myUnit == null || myUnit == "1")
		muSelJian();
	else
		muSelBao();
	$modalUnit.modal();
}
function muDone() {
	var unitType = document.getElementById("muUnit").innerText;
	if (unitType == "x 1")
		myUnit = "1";
	else
		myUnit = document.getElementById("muUnitNum").innerText; 
	$modalUnit.modal("toggle");
	displayUnits();
}
function muSelJian() {
	document.getElementById("muUnit").innerText = "x 1";
	$('#muSelUnitArea').hide();
}
function muSelBao() {
	document.getElementById("muUnit").innerText = "x N"; 
	if (myUnit == null || myUnit == "1")
		document.getElementById("muUnitNum").innerText = myRes['comSelect'];
	else
		document.getElementById("muUnitNum").innerText = myUnit;
	$('#muNewUnitArea').hide();
	$('#muSelUnitArea').show();
}
function muSelUnitNum(e) {
	var x = $(e).text();
	document.getElementById("muUnitNum").innerText = x;
	$('#muNewUnitArea').hide();
}
// New unit number
function muNewUnitNum() {
	document.getElementById("muUnitNum").innerText = myRes['comAdd'];
	document.getElementById("muNewNum").value = "";
	$('#muNewUnitArea').show();
	$('#muNewNum').trigger('focus');
}
function muNewCancel() {
	$('#muNewUnitArea').hide();
	if (myUnit == null || myUnit == "1")
		document.getElementById("muUnitNum").innerText = myRes['comSelect'];
	else
		document.getElementById("muUnitNum").innerText = myUnit;
}
function muNewOk() {
	// get new unit number
	var newNum = document.getElementById("muNewNum").value;
	if (newNum == "" || !onlyDigits(newNum)) {
		$('#muNewNum').trigger('focus');
		return;
	}
	newNum = parseInt(newNum).toString();
	// check if the unit number already exist
	for (var i=0; i<myUnits.length; i++) { 
		if (myUnits[i]['units'] == newNum) {
			alert(myRes['msgErrDupData']);
			$('#muNewNum').trigger('focus');
			return;
		}
	}
	$('#muNewUnitArea').hide();
	// add new unit number to the dropdown list
	var ele = document.createElement("a");
    ele.classList = "dropdown-item";
    ele.href = "#";
    ele.innerText = newNum;
	ele.addEventListener("click", function(){document.getElementById("muUnitNum").innerText = newNum});
    document.querySelector("#muUnitNumList").appendChild(ele);
	document.getElementById("muUnitNum").innerText = newNum;
	// add to database
	var form = new FormData();
	form.append('units', newNum);
	postRequest('postUnitAdd.php', form, null, null);
}
// Display unit on main screen
function displayUnits() {
	if (myUnit == null || myUnit == "1")			 
		document.getElementById("btnUnit").innerHTML = "x1";
	else
		document.getElementById("btnUnit").innerHTML = "x"+myUnit+"";
}
/********************************************************************
	BARCODE
********************************************************************/
// Print Barcode
function printBarcode(){
	






}
// auto/manual input
function inputBarcode() {
	if (autoBarcode) {
		autoBarcode = false;
		document.getElementById("btnBarcode").innerHTML = "💻";
		document.getElementById("code1").readOnly = false;
		document.getElementById("code1").value = "";
		$("#code1").focus();
		
	} else {
		autoBarcode = true;
		document.getElementById("btnBarcode").innerHTML = '&#9998';
		document.getElementById("code1").readOnly = true;
		document.getElementById("code1").value = genBarcode();
	}
}
function genBarcode() {
	var code = myId+"0001";
	
	return code;
}
function checkBarcode(code) {
	getRequest("getInvByCode1.php?code1="+code, getCode1Yes, getCode1No);
}
function getCode1Yes(result) {
	alert(myRes['msgErrDupData']);	
	$("#code1").focus();
	return;	
}
function getCode1No(result) {
	submitFormContinue1();
}


/********************************************************************
	APP
********************************************************************/
function showApp() {
	mdapShow(myAppData);
}

/********************************************************************
	STATUS
********************************************************************/
function selStatus(e) {
	document.getElementById("status_str").innerText = $(e).text();
	document.getElementById("status").value = e.getElementsByTagName("input")[0].value;  
}

function showStatus() {
	if (myInv == null || myInv['status'] == "0") {
		document.getElementById("status_str").innerText = myRes['comStatusNormal'];
		document.getElementById("status").value = "0";
	} else if(myInv['status'] == "1") {
		document.getElementById("status_str").innerText = myRes['comStatusOffline'];
		document.getElementById("status").value = "1";
	} else {
		document.getElementById("status_str").innerText = "未完成";
		document.getElementById("status").value = "-1";
	}
}

/********************************************************************
	PRICE
********************************************************************/
function showPrice() {
	mdprShow();
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

<script src="js/DragDropTouch.js"></script>
<script src="js/modalApp.js?<?= rand() ?>"></script>
<script src="js/modalPrice.js?<?= rand() ?>"></script>
<script src="js/modalPrint.js?<?= rand() ?>"></script>
</html>

