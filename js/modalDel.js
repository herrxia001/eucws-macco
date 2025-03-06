var modalDel = $("#modalDelete");
var functionDone, target_modal;
function delItemModal(){
    if(document.getElementById("pwd").value == ""){
        alert("请输入密码");
        return;
    }
    // check passowrd //
    var user = localStorage.getItem("user");
    var form = new FormData();
	form.append('user', JSON.stringify(user));
	form.append('password', JSON.stringify(document.getElementById("pwd").value)); 
	postRequest("postLogin.php", form, checkSubmitYes, checkSubmitNo);
}

function cancelDel() {
	modalDel.modal("toggle");
    if(target_modal != null) target_modal.modal("toggle");
}
function showDelModal(delfunc, t_modal = null){
    modalDel.modal();
    functionDone = delfunc;
    target_modal = t_modal;
}



function checkSubmitYes(result) {
    // delete item //
    cancelDel();
    functionDone();
}
function checkSubmitNo(result) {
    alert("密码错误");
	$('#pwd').focus();
}