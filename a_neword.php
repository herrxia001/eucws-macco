<?php
/* INVOICE HOME */

session_start();
if(!$_SESSION['uId'])
	header("Location:alogin.php");

header("location: a_neword_2.php");

exit();

include_once 'db_invoice.php';

$myCompany = $_SESSION['myCompany'];

$result = dbQueryInvoiceByStatus('0');
if ($result > 0)
{
	$rId = $result[0]['r_id'];
	header("Location: ainvoice.php?back=a_neword&r_id=".$rId);
}

?>

<!doctype html>
<html lang="en">
<head>
    <?php include 'include/header.php' ?>	
	<title>EUIMS - NEW INVOICE</title>
</head>
<body>	
	<?php include 'include/a_nav.php' ?>
	
	<div class="row"> 
		<div class="p-1 col-12 col-sm-12 col-md-12 col-lg-12" align="center">
			<div><a>目前没有新的发票</a></div>
		</div>
	</div>
</body>

<script src="js/ajax.js"></script>
<script>

var myInterval;
var status = "<?php echo $result ?>";
if (status <= '0') {
	myInterval = setInterval(getInvoice, 5000);
}

function getInvoice() {	
	getRequest("getInvoiceFromOrder.php", getYes, getNo);
}

function getYes(result) {
	clearInterval(myInterval);
	
	var url = "ainvoice.php?back=a_neword&r_id="+result['r_id'];
	window.location.assign(url);
}
function getNo(result) {
	
}

</script>

</html>

