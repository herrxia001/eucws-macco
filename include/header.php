<?php
getValidDatumDiff();
//if($_SESSION['validDay'] < 0) header("location: ./");
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>	

<link href="https://unpkg.com/bootstrap-table@1.18.0/dist/bootstrap-table.min.css" rel="stylesheet">
<script src="https://unpkg.com/bootstrap-table@1.18.0/dist/bootstrap-table.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<link href="css/autocomplete.css" rel="stylesheet">

<style>
body {
  padding-top: 4rem;
}
.caption {
	width: 80px;
}
.caption-lg {
	width: 100px;
}
.mandatory {
	font-weight: bold;
}
.button-s {
	width: 60px;
}
.button-m {
	width: 100px;
}
.button-l {
	width: 120px;
}
.modalTitle {
	margin-left: 4px;
	margin-top: 2px;
	font-size: 18px; 
	font-weight: bold;
}
.modalSepLine {
	border:1px solid lightgrey; 
	margin:2px; 
	width:100%;
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
.display_none{
	display: none !important;
}
<?php if($_SESSION['validDay'] != "" && $_SESSION['validDay'] < 0) { ?>
nav.bg-dark{
	background-color: red !important;
}
nav.navbar.bg-dark:after{
	content: "(还剩<?= $_SESSION['validDay'] ?>天)";
    position: absolute;
    color: white;
    left: 100px;
}
nav.a_nav.navbar.bg-dark:after{
	content: "(还剩<?= $_SESSION['validDay'] ?>天)";
    position: absolute;
    color: white;
    left: 170px;
}
<?php } ?>
</style>



	