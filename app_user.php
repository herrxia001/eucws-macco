<?php
/************************************************************************************
	File:		app_user.php
	Purpose:	APP user management
************************************************************************************/

// Start session
session_start();
if(!$_SESSION['uId'])
	header("Location:index.php");

// Include files
include 'db_functions.php';
include_once 'resource_'.$_SESSION['uLanguage'].'.php';

// Init variables
$thisResource = new myResource();

?>

<!doctype html>
<html lang="zh">
<head>
    <?php include 'include/header.php' ?>
	<title>APP Product Management</title>
</head>

<style>
.dropdown-menu{
    max-height: 300px;
    overflow-y: scroll;
}
</style>

<body>
	<?php include 'include/nav.php' ?>		
	
	<div class="container">	
<!-- buttons -->
		<div class="row">
			<div class="p-1 input-group col-8 col-sm-8 col-md-8 col-lg-6">
				<div class="input-group-prepend"><span class="input-group-text caption"><?php echo $thisResource->appStatus ?></span></div>
				<button type="button" class="p-1 ml-1 btn btn-outline-secondary button-l dropdown-toggle" id="status_str" data-toggle="dropdown">
					<?php echo $thisResource->appUserPending ?></button>
				<input type='hidden' id="status">
				<div class="dropdown-menu">
					<div class="dropdown-item" href="#" onclick="selStatus(this)"><?php echo $thisResource->appUsersPending ?>
						<input type='hidden' value='0'></div>
					<div class="dropdown-item" href="#" onclick="selStatus(this)"><?php echo $thisResource->appUsersApproved ?>
						<input type='hidden' value='1'></div>
					<div class="dropdown-item" href="#" onclick="selStatus(this)"><?php echo $thisResource->appUsersRejected ?>
						<input type='hidden' value='2'></div>
					<div class="dropdown-item" href="#" onclick="selStatus(this)"><?php echo $thisResource->appAll ?>
						<input type='hidden' value='-1'></div>
				</div>
			</div>
			<div class="p-1 col-4 col-sm-4 col-md-4 col-lg-2" align="right">
				<button type="button" class="btn btn-primary" onclick="searchAll()"><?php echo $thisResource->appRefresh ?></button>
			</div>
		</div>
<!-- Search result table -->
		<div class="row">
			<div class="p-1 input-group col-8 col-sm-8 col-md-8 col-lg-6">
				<div class="input-group-prepend"><span class="input-group-text caption"><?php echo $thisResource->comCustomer ?></span></div>
				<input type="text" class="form-control autocomplete" id="k_name" autofocus>
			</div>
			<div class="p-1 col-4 col-sm-4 col-md-4 col-lg-2" align="right">
				<label class="mt-2" id="lbSum"></label>
			</div>			
		</div>
		<div class="row">
			<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-8">	
			<table id="table" class="table-sm" data-toggle="table" data-single-select="true" data-click-to-select="true">
				<thead class="thead-light">
					<tr>
					<th class="p-1" data-field="id" data-width="0" data-width-unit="%" data-visible="false"></th>									
					<th class="p-1" data-field="idx_name" data-width="25" data-width-unit="%" data-sortable="true"><?php echo $thisResource->comCustomer ?></th>
					<th class="p-1" data-field="idx_time" data-width="25" data-width-unit="%" data-sortable="true"><?php echo $thisResource->comTime ?></th>
					<th class="p-1" data-field="idx_post" data-width="15" data-width-unit="%" data-sortable="true"><?php echo $thisResource->comPost ?></th>
					<th class="p-1" data-field="idx_status" data-width="15" data-width-unit="%" data-sortable="true"><?php echo $thisResource->appStatus ?></th>
					<!--th class="p-1" data-field="idx_url" data-width="20" data-width-unit="%" data-sortable="true">链接</th-->
					</tr>
				</thead>
				<tbody>
				<!-- load table by JS -->
				</tbody>
			</table>
			</div>
		</div>	

</div> <!-- End of container -->

<!-- Modal Customer -->
<div class="modal fade" id="modalCustView" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
	data-backdrop="static" data-keyboard="false" style="overflow-y:scroll">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		<div class="modal-body">
<!-- menu -->
		<div class="row">
			<div class="p-1 col-3">
				<label class="ml-2 mt-2" style="font-weight: bold" id="m_title"></label>
			</div>
			<div class="p-1 col-6" align="center">
				<button type="button" class="btn btn-outline-secondary" id="btnFile" onclick="viewFile()"><?php echo $thisResource->appUserViewFile ?></button>
				<button type="button" class="ml-1 btn btn-outline-primary" id="btnMessage" onclick="viewMsg()"><?php echo $thisResource->appUserViewMessage ?></button>
			</div>
			<div class="p-1 col-3" align="right">
				<button type="button" class="btn" onclick="closeCustView()"><span class='fa fa-times'></span></button>
			</div>
		</div>
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:1px; width:100%">
		</div>
<!-- apc_id hidden -->
		<input type="text" class="form-control" id="m_apc_id" hidden>	
<!-- apc_name -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comName ?></span></div>
			<input type="text" class="form-control" id="m_apc_name" style="background-color:white" readonly>
		</div>
<!-- type -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comType ?></span></div>
			<input type="text" class="form-control" id="m_type" style="background-color:white" readonly>
		</div>	
<!-- country -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comCountry ?></span></div>
			<input type="text" class="form-control" id="m_country" style="background-color:white" readonly>
		</div>
<!-- VAT -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comVat ?></span></div>
			<input type="text" class="form-control" id="m_vat" style="background-color:white" readonly>
			<button type="button" class="btn btn-secondary ml-1" onclick="validVIES()"><span class='fa fa-search'></span></button>
		</div>		
<!-- address -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comAddress ?></span></div>
			<input type="text" class="form-control" id="m_address" style="background-color:white" readonly>
		</div>
<!-- address1 -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comAddress1 ?></span></div>
			<input type="text" class="form-control" id="m_address1" style="background-color:white" readonly>
		</div>
<!-- post -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comPost ?></span></div>
			<input type="text" class="form-control" id="m_post" style="background-color:white" readonly>
		</div>
<!-- city -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comCity ?></span></div>
			<input type="text" class="form-control" id="m_city" style="background-color:white" readonly>
		</div>
<!-- conatct -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comContact ?></span></div>
			<input type="text" class="form-control" id="m_contact" style="background-color:white" readonly>
		</div>
<!-- email -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comEMail ?></span></div>
			<input type="text" class="form-control" id="m_email" style="background-color:white" readonly>
		</div>
<!-- WhatsApp -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comWhatsApp ?></span></div>
			<input type="text" class="form-control" id="m_whatsapp" style="background-color:white" readonly>
		</div>
<!-- phone -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comTel ?></span></div>
			<input type="text" class="form-control" id="m_tel" style="background-color:white" readonly>
		</div>
<!-- memo -->
		<div class="input-group p-1"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;"><?php echo $thisResource->comRemark ?></span></div>
			<textarea class="form-control" id="m_memo" style="background-color:white" readonly rows="4"></textarea>
		</div>
<!-- url -->
		<div class="input-group p-1" style="display: none;"> 
			<div class="input-group-prepend"><span class="input-group-text" style="width:100px;">链接</span></div>
			<!-- input type="text" class="form-control" id="m_url" style="background-color:white" readonly -->
			<a id="m_url" style="padding: 10px;"></a>
		</div>
<!-- foot -->
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:1px; width:100%">
		</div>
		<div class="row">
			<div class="p-1 col" align="right">
				<button type="button" class="btn btn-danger" id="btnReject" onclick="rejectCust()"><?php echo $thisResource->appUsersReject ?></span></button>
				<button type="button" class="mr-1 btn btn-success" id="btnApprove" onclick="approveCust()"><?php echo $thisResource->appUsersApprove ?></span></button>
			</div>
		</div>
		
		</div> <!-- end of modal body -->	
		</div> <!-- end of modal-content -->
	</div> <!-- end of modal-dialog -->
</div> <!-- end of modal -->

<!-- Modal Message -->
<div class="modal fade" id="modalCustMsg" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
	data-backdrop="static" data-keyboard="false" style="overflow-y:scroll;">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-body">
<!-- menu -->
		<div class="row">
			<div class="p-1 col-4" align="left">
				<label class="ml-2 mt-2" style="font-weight: bold"><?php echo $thisResource->appUserMessage ?></label>
			</div>
			<div class="p-1 col-8" align="right">
				<button type="button" class="btn" onclick="closeCustMsg()"><span class='fa fa-times'></span></button>
			</div>
		</div>
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:1px; width:100%">
		</div>
<!-- memo -->
		<div class="p-1"> 
			<textarea class="form-control" id="m_msg" style="background-color:white" rows="5"></textarea>
		</div>
<!-- foot -->
		<div class="row">
			<hr style="border:1px solid lightgrey; margin:1px; width:100%">
		</div>
		<div class="row">
			<div class="p-1 col" align="right">
				<button type="button" class="btn btn-danger" id="btnConfirm" onclick="confirmReject()"><?php echo $thisResource->appUsersConfirmReject ?></span></button>
			</div>
		</div>
		</div> <!-- end of modal body -->	
		</div> <!-- end of modal-content -->
	</div> <!-- end of modal-dialog -->
</div> <!-- end of modal -->

<!-- modalFileView -->
<div class="modal fade" id="modalFileView" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
	data-backdrop="static" data-keyboard="false" style="overflow-y:scroll;">
	<div class="modal-dialog" role="document" style="height:98%">
		<div class="modal-content" style="height:95%">
		<div class="modal-body">
		<div class="row">
			<div class="col p-1" align="right">
				<button type="button" class="btn" onclick="closeFileView()"><span class='fa fa-times'></span></button>
			</div>
		</div>
		<div class="row" style="height:92%">
			<div class="col p-1">
				<div id="adobe-dc-view" ></div>
				<iframe id="file_pdf" style="width: 100%; height: 100%;"></iframe>
			</div>
		</div>
		</div> <!-- end of modal body -->	
		</div> <!-- end of modal-content -->
	</div> <!-- end of modal-dialog -->
</div> <!-- end of modalFileView -->

<script src="https://documentcloud.adobe.com/view-sdk/main.js"></script>
<script src="js/ajax.js"></script>
<script src="js/autocomplete.js"></script>

<script>
/************************************************************************************
	PHP
************************************************************************************/
var myResource = <?php echo json_encode($thisResource) ?>; 
var myDB = <?php echo json_encode($_SESSION['uDb']) ?>; 
/************************************************************************************
	LOCAL
************************************************************************************/
var $table = $("#table");
var myStatus = -1;
var users = [], k_names = [];
var countTotal = 0;
var selectedUser = {};
var modalCust = $("#modalCustView");
var modalMsg = $("#modalCustMsg");
var modalFile = $("#modalFileView");
/************************************************************************************
	INIT
************************************************************************************/
$(document).ready(function(){
	document.getElementById("myTitle").innerHTML = myResource['comAppUsers'];
	myStatus = "0";
	document.getElementById("status_str").innerText = getStatusNameById(myStatus);
	searchAll();
});

$table.bootstrapTable({   
	formatNoMatches: function () {
         return myResource['sysMsgNoRecord'];
    }
});

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
	users = result;
	k_names = new Array();
	for (var i=0; i<users.length; i++) {
		k_names[i] = users[i]['apc_name'];
	}
	autocomplete_like(document.getElementById("k_name"), k_names);
	loadTable();
}

function searchNo(result) {
	users = null;
	loadTable();
}

function searchAll() {
	getRequest("apGetUsers.php", searchYes, searchNo);
}

function displaySum(){
	document.getElementById("lbSum").innerText = myResource['comCustomer']+": "+countTotal+" / "+users.length;
}

function loadTable(){
	countTotal = 0;
	
	if (users == null || users == 0){
		$table.bootstrapTable('removeAll');
		document.getElementById("lbSum").innerText = myResource['comCustomer']+": 0 / 0";
		return;
	}

	var rows = []; 	
	for(var i=0; i<users.length; i++){
		users[i]['status_str'] = getStatusNameById(users[i]['status']);
		var url = "";
		if(users[i]['status'] == 1){
			users[i]['url'] = "https://www.shop-reho.com/?token="+users[i]['token'];
		}
		if (myStatus != "-1" && users[i]['status'] != myStatus)
			continue;	
		rows.push({
			id: users[i]['apc_id'],
			idx_name: users[i]['apc_name'],
			idx_time: users[i]['time_created'].substr(0, 10),
			idx_post: users[i]['post'],
			idx_status: users[i]['status_str'],
			idx_url: users[i]['url']
		});
		countTotal++;
	}
	$table.bootstrapTable('removeAll');
	$table.bootstrapTable('append', rows);	
	displaySum();
}

function getUserByName(name) {
	for (var i=0; i<users.length; i++) {
		if (users[i]['apc_name'] == name)
			return users[i];
	}
	return null;
}

function getUserById(id) {
	for (var i=0; i<users.length; i++) {
		if (users[i]['apc_id'] == id)
			return users[i];
	}
	return null;
}

/************************************************************************************
	VIEW
************************************************************************************/
$('#table').on('click-row.bs.table', function (e, row, $element) {
	selectedUser = getUserById(row.id);
	if (selectedUser != null)
		viewUser();
});

function doneAutocomp() {
	var k_name = document.getElementById("k_name").value();
	if (k_name == "")
		return;
	selectedUser = getUserByName(k_name);
	if (selectedUser != null)
		viewUser();	
}

function viewUser() {
	document.getElementById("m_title").innerHTML = getStatusNameById(selectedUser['status']);
	document.getElementById("m_apc_id").value = selectedUser['apc_id'];
	document.getElementById("m_apc_name").value = selectedUser['apc_name'];
	document.getElementById("m_type").value = selectedUser['type'] == "0" ? myResource['appUsersTypeComp'] : myResource['appUsersTypePer'];
	document.getElementById("m_country").value = selectedUser['country'];
	document.getElementById("m_vat").value = selectedUser['taxno'];
	document.getElementById("m_address").value = selectedUser['address'];
	document.getElementById("m_address1").value = selectedUser['address1'];
	document.getElementById("m_post").value = selectedUser['post'];
	document.getElementById("m_city").value = selectedUser['city'];
	document.getElementById("m_contact").value = selectedUser['contact'];
	document.getElementById("m_email").value = selectedUser['email'];
	document.getElementById("m_whatsapp").value = selectedUser['whatsapp'];
	document.getElementById("m_tel").value = selectedUser['cell'];
	document.getElementById("m_memo").value = selectedUser['memo'];
	document.getElementById("m_url").text = selectedUser['url'];
	document.getElementById("m_url").href = selectedUser['url'];
	if (selectedUser['status'] == "0") {
		document.getElementById("btnMessage").style.display = "none";
		document.getElementById("btnReject").style.display = "inline";
		document.getElementById("btnApprove").style.display = "inline";
	} else if (selectedUser['status'] == "1") {
		document.getElementById("btnMessage").style.display = "none";
		document.getElementById("btnReject").style.display = "none";
		document.getElementById("btnApprove").style.display = "none";
	} else {
		document.getElementById("btnMessage").style.display = "inline";
		document.getElementById("btnReject").style.display = "none";
		document.getElementById("btnApprove").style.display = "none";
	}
	if (selectedUser['file'] == "0") {
		document.getElementById("btnFile").innerText = myResource['appUserNoFile'];
		document.getElementById("btnFile").disabled = true;
	} else {
		document.getElementById("btnFile").innerText = myResource['appUserViewFile'];
		document.getElementById("btnFile").disabled = false;
	}
	
	modalCust.modal();
}

function closeCustView() {
	modalCust.modal("toggle");
}

function rejectCust() {
	if (!confirm(myResource['appMsgUserReject']))
		return;
		
	document.getElementById("btnConfirm").style.display = "block";
	modalMsg.modal();
}

function closeCustMsg() {
	modalMsg.modal("toggle");
}

function viewMsg() {
	document.getElementById("btnConfirm").style.display = "none";
	document.getElementById("m_msg").value = selectedUser['message'];
	modalMsg.modal();
}

function confirmReject() {
	var msg = document.getElementById("m_msg").value;
	if (msg == "") {
		$('#m_msg').focus();
		return;
	}
	modalMsg.modal("toggle");
	
	selectedUser['status'] = "2";
	selectedUser['msg'] = "";
	
	var form = new FormData();
	form.append('apc_id', selectedUser['apc_id']);
	form.append('company', JSON.stringify(selectedUser));
	postRequest('apPostUserUpdate.php', form, updateBackYes, updateBackNo);
}

function approveCust() {
	if (!confirm(myResource['appMsgUserApprove']))
		return;
	
	selectedUser['status'] = "1";
	selectedUser['msg'] = "";
	
	var form = new FormData();
	form.append('apc_id', selectedUser['apc_id']);
	form.append('company', JSON.stringify(selectedUser));
	postRequest('apPostUserUpdate.php', form, updateBackYes, updateBackNo);		
}

function updateBackYes(result) {
	modalCust.modal("toggle");		
	searchAll();
}

function updateBackNo(result) {
	alert(myResource['msgErrDatabase']);
}

/************************************************************************************
	FILTER
************************************************************************************/
function getStatusNameById(id) {
	var name = "";
	switch(id) {
		case "0": name = myResource['appUsersPending']; break;
		case "1": name = myResource['appUsersApproved']; break;
		case "2": name = myResource['appUsersRejected']; break;
		default: name = myResource['appAll'];
	}
	
	return name;
}

function selStatus(e) { 
	document.getElementById("status_str").innerText = $(e).text();
	myStatus = e.getElementsByTagName("input")[0].value; 
	
	loadTable();
}

/************************************************************************
	VAT NUMBER
************************************************************************/
var viesResult;
function validVIES() {
	var number = document.getElementById("m_vat").value;
	if (number == "" || number.length < 3) {
		$("#m_vat").trigger('focus');
		return;
	}
	
	var countryCode = (number.substring(0,2)).toUpperCase();
	var vatNumber = (number.substring(2)).toUpperCase();

	var link = "getVIES.php?countrycode="+countryCode+"&vatnumber="+vatNumber;
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			viesResult = this.responseText;
			alert(viesResult);
		};
	};
	xhr.open("GET", link, true);
	xhr.send();
}

/************************************************************************
	VIEW FILE
************************************************************************/
function viewFile() {
	$("#file_pdf").attr("src","").hide();

	const fileName = selectedUser['apc_id']+".pdf";
	/*const previewConfig = {
		showLeftHandPanel: false,
		showAnnotationTools: false,
		dockPageControls: false
	}
	var adobeDCView = new AdobeDC.View({clientId: "4bb6313760cd4a97be4c334ec8171293", divId: "adobe-dc-view"});
    adobeDCView.previewFile({
        content: {location: {url: "files/"+myDB+"/app/"+fileName}},
        metaData: {fileName: fileName}
    }, previewConfig);*/
	$("#file_pdf").attr("src","files/"+myDB+"/app/"+fileName).show();
	modalFile.modal();
}

function closeFileView() {
	modalFile.modal("toggle");
}

</script>

</body>
</html>