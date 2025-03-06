<?php
/************************************************************************************
	File:		inv_types.php
	Purpose:	product types
************************************************************************************/

// Start session; If session expired, load the login page.
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

// Include files
include_once 'db_functions.php';
include_once 'resource_'.$_SESSION['uLanguage'].'.php';
$thisResource = new myResource();

// Init variables
$backPhp = 'management.php';

// Query all product types
$myTypes = dbQueryTypes();

if(isset($_GET['back']))
	$backPhp = $_GET['back'].'.php';

?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>
	<title>EUCWS - Product Types</title>
</head>
<style>
body {
 padding-top: 0rem;
}
</style>
<body>
	<div class="container">	
	
	<div class="row mb-2">
		<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-2" style="background-color: DarkSlateGrey">
			<a class="btn" href=<?php echo $backPhp ?> role="button"><span style="color:white" class='fa fa-arrow-left'></span></a>
		</div>
		<div class="p-1 col-8 col-sm-8 col-md-8 col-lg-4"  style="background-color: DarkSlateGrey" align="center"> 
			<a><span id="myTitle" style="color: white; font-weight: bold"><?php echo $thisResource->comTypes ?></span></a>
		</div>
		<div class="p-1 col-2 col-sm-2 col-md-2 col-lg-2" style="background-color: DarkSlateGrey" align="right">
			<button type="button" class="btn" onclick="newType()"><span style="color:white" class='fa fa-plus'></span></button>
		</div>
	</div>

		<div class="row"> 
			<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-8">
			<table id="table" data-toggle="table" data-single-select="true" data-click-to-select="true">
			<thead>
				<tr>
				<th data-field="id" data-width="10" data-width-unit="%"><?php echo $thisResource->comID ?></th>
				<th data-field="idx_name" data-width="85" data-width-unit="%"><?php echo $thisResource->comName ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if(is_array($myTypes)) for($i=0; $i<count($myTypes); $i++) { if($myTypes[$i]['t_id'] == 0) continue; ?>    
				<tr>
				<td><?php echo $myTypes[$i]['t_id'] ?></td>
				<td><?php echo $myTypes[$i]['t_name'] ?></td>
				</tr>
				<?php } ?>
			</tbody>
			</table>
			</div>
		</div>
	</div>
	
<!-- Modal for edit type -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="modalLabel"><?php echo $thisResource->comTypeEdit ?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<input type="text" class="form-control" id="t_name_old" name="t_name_old" hidden>
			<div class="row">
				<div class="input-group p-1">	
					<div class="input-group-prepend"><span class="input-group-text" ><?php echo $thisResource->comID ?></span></div>				
					<input type="text" class="form-control" id="t_id" name="t_id" readonly>
				</div>
			</div>
			<div class="row">
				<div class="input-group p-1">	
					<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comName ?></span></div>				
					<input type="text" class="form-control" id="t_name" name="t_name" autofocus>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal"><span class='fa fa-times'></button>
			<button type="button" class="btn btn-primary" onclick="saveType()"><span class='fa fa-check'></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal for edit type -->

<!-- Modal for add type -->
<div class="modal fade" id="modalAdd" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="modalLabel"><?php echo $thisResource->comTypeAdd ?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="input-group p-1">	
					<div class="input-group-prepend"><span class="input-group-text"><?php echo $thisResource->comName ?></span></div>				
					<input type="text" class="form-control" id="t_name_add" name="t_name_add" autofocus>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-primary" onclick="addType()"><span class='fa fa-check'></span></button>
		</div>
		</div>
	</div>
</div> <!-- End of Modal for add type -->

</body>

<script src="js/ajax.js"></script>
<script>

var myRes = <?php echo json_encode($thisResource) ?>;
var types = <?php echo json_encode($myTypes) ?>;
var $table = $("#table");
var $modalEdit = $("#modalEdit"), $modalAdd = $("#modalAdd");

/********************************************************************
	INIT
********************************************************************/
// Prevent 'enter' key for submission
$('form input').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
		return false;
    }
});
$table.bootstrapTable({   
	formatNoMatches: function () {
         return myRes['sysMsgNoRecord'];
    }
});
/********************************************************************
	EDIT TYPE
********************************************************************/
// Click a row to view product
$('#table').on('click-row.bs.table', function (e, row, $element) {	
	document.getElementById("t_id").value = row.id;
	document.getElementById("t_name").value = row.idx_name;
	document.getElementById("t_name_old").value = row.idx_name;
	$modalEdit.modal();
})

$modalEdit.on('shown.bs.modal', function () {
  $('#t_name').trigger('focus')
})
function saveType(){
	var id = document.getElementById("t_id").value;
	var name = document.getElementById("t_name").value;
	var name_old = document.getElementById("t_name_old").value;
	if (name == name_old) {
		$modalEdit.modal("toggle");
		return;		
	}
	if (name == "") {
		$('#t_name').trigger('focus');
		return;
	}
	if(!checkType(name)) {
		alert(myRes['msgErrDupData']);
		$('#t_name').trigger('focus');
		return;
	}
	
	$modalEdit.modal("toggle");
	
	var form = new FormData();
	form.append('t_id', id);
	form.append('t_name', name);
	postRequest('postTypeUpdate.php', form, postTypeBack, null);		
}
/********************************************************************
	ADD TYPE
********************************************************************/
function newType(){
	document.getElementById("t_name_add").value = "";
	$modalAdd.modal();
}
$modalAdd.on('shown.bs.modal', function () {
  $('#t_name_add').trigger('focus')
})
function addType(){		
	var name = document.getElementById("t_name_add").value;
	if (name == "") {
		$('#t_name_add').trigger('focus');
		return;
	}
	if(!checkType(name)) {
		alert(myRes['msgErrDupData']);
		$('#t_name_add').trigger('focus');
		return;
	}
	
	$modalAdd.modal("toggle");
	
	var form = new FormData();
	form.append('t_name', name);
	postRequest('postTypeAdd.php', form, postTypeBack, null);	
}
/********************************************************************
	DATABASE ACTION
********************************************************************/
function postTypeBack(result) {
	getRequest("getTypes.php", tableLoad, null);
}
function tableLoad(result){
	types = result;
	$table.bootstrapTable('removeAll');
	var rows = [];
	for(var i=0; i<types.length; i++){
		if(types[i]['t_id'] == 0) continue;
		rows.push({
			id: types[i]['t_id'],
			idx_name: types[i]['t_name'],
		});
	}
	$table.bootstrapTable('append', rows);
}
/********************************************************************
	FUNCTIONS
********************************************************************/
function checkType(type) { 
	for(var i=0; i<types.length; i++){
		if (type == types[i]['t_name']) {
			return false;
		}
	}
	return true;
}

</script>

</html>

