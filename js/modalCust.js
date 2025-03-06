
var $modalCust = $("#modalCust");
var mkColumns = ['k_id', 'k_code', 'k_name', 'name1', 'address', 'post', 'city', 'country', 'tel', 'email', 'contact', 
				'taxno', 'ustno', 'whatsapp', 'wechat', 'discount'];
var mkColumnTotal = 16, mkCode = "";
var mk_customer;

var mkAutoCode = false;

function mkInit(customer) {
	var i = 0;
	mk_customer = customer;
	var a_country = JSON.parse(localStorage.getItem("a_country"));	
	autocomplete(document.getElementById("mk_country"), a_country);	
	
	if (mk_customer['k_id'] != "" && mk_customer['k_id'] != "0") {
		for (i=0; i<mkColumnTotal; i++) {
			document.getElementById("mk_"+mkColumns[i]).value = mk_customer[mkColumns[i]];
		}
		mkCode = mk_customer['k_code'];
		document.getElementById("mk_autocode").innerHTML = "💻";
		document.getElementById("mk_k_code").readOnly = false;
		//document.getElementById("mk_autocode").style.display = "none";
	} else {
		for (i=0; i<mkColumnTotal; i++) {
			document.getElementById("mk_"+mkColumns[i]).value ="";
		}
		mkAutoCode = true;
		document.getElementById("mk_autocode").innerHTML = '&#9998';
		document.getElementById("mk_k_code").readOnly = true;
		//document.getElementById("mk_autocode").style.display = "block";
		mkAutoCust();
	}
}

$modalCust.on('shown.bs.modal', function () {
	$("#mk_k_code").trigger('focus');
})

function getBackYes(result){	
	$("#mk_k_code").trigger('focus');
	return;	
}

function getBackNo(result){
	mkDoneCustContinue();	
}

function mkSaveBack(result) { 
	mk_customer['k_id'] = result;
	$modalCust.modal("toggle");	
	mkSaveCust(mk_customer);
}

function mkDoneCustContinue() {
	if (mk_customer['k_name'] == "") {
		$("#mk_k_name").trigger('focus');
		return false;
	}	

	var link = "postCust.php";
	var form = new FormData();
	form.append('cust', JSON.stringify(mk_customer));
	postRequest(link, form, mkSaveBack, null);	
}

function mkDoneCust() {
	var i = 0;
	mk_customer = new Object();
	for (i=0; i<mkColumnTotal; i++) {
		mk_customer[mkColumns[i]] = document.getElementById("mk_"+mkColumns[i]).value;
	}
	if (mk_customer['k_code'] == "") {
		$("#mk_k_code").trigger('focus');
		return false;
	}
	if (parseFloat(mk_customer['discount']) <0 || parseFloat(mk_customer['discount']) >=100) {
		mk_customer['discount'] = "";
		$("#mk_discount").trigger('focus');
		return false;
	}

	if (mk_customer['k_code'] == mkCode)
		mkDoneCustContinue();
	else
		getRequest("getCustsByColumn.php?col=k_code&val="+mk_customer['k_code'], getBackYes, getBackNo);	
		
}

/* Validate VAT Number */
var viesResult;
function validVIES() {
	var number = document.getElementById("mk_ustno").value;
	if (number == "" || number.length < 3) {
		$("#mk_ustno").trigger('focus');
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
/*	
	var link = "getVIES_DE.php?ust1="+myCompany['uid_no']+"&ust2="+number;
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var result = this.responseText;
			alert(result);
		};
	};
	xhr.open("GET", link, true);
	xhr.send();
*/
}

function printVIES() {
	viesResult = viesResult.replaceAll(/(?:\r\n|\r|\n)/g, '<br />');
	var output = '<html><head><style type="text/css" media="print">@page { size:A4; margin:0.8cm 0.8cm 0.8cm 1.5cm; }\</style></head><body>';
	output += '<p>'+viesResult+'</p>';
	output += '</body></html>';
	
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

function mkAutoCust() {
	if (mkAutoCode) {
		mkAutoCode = false;
		document.getElementById("mk_autocode").innerHTML = "💻";
		document.getElementById("mk_k_code").readOnly = false;
		document.getElementById("mk_k_code").value = "";
		$('#mk_k_code').trigger('focus');		
	} else {
		mkAutoCode = true;
		document.getElementById("mk_autocode").innerHTML = '&#9998';
		document.getElementById("mk_k_code").readOnly = true;
		mkGetCustNo();
		
	}
};

function mkGetCustNo() {
	var prefix = document.getElementById("mk_k_code").value;
	if (prefix != "")
		getRequest("getCustNo.php?prefix="+prefix, mkAutoCustYes, mkAutoCustNo);
	else
		getRequest("getCustNo.php", mkAutoCustYes, mkAutoCustNo);
}	

function mkAutoCustYes(result) {
	document.getElementById("mk_k_code").value = result;
}

function mkAutoCustNo(result) {
	document.getElementById("mk_k_code").value = "";
}




