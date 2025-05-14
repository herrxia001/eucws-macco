<?php session_start();
include_once '../database.php';
require_once 'tcpdf/examples/tcpdf_include.php';

function toFixed($number, $decimals) {
    return number_format($number, $decimals, '.', "");
}
function notZero($s) {
	if ($s != "" && $s != "0" && $s != "0.00")
		return true;
	else
		return false;			
}
function isCHECust($ustno) {
	if ($ustno == null)
		return false;
	if ($ustno != "" && strlen($ustno) > 3 && substr($ustno,0,3) == "CHE")
		return true;
	else
		return false;
}
function notGermanCust($ustno) {
	if ($ustno == null)
		return false;
	if ($ustno != "" && substr($ustno,0,2) != "DE")
		return true;
	else
		return false;
}
function convertDate($date){
    return substr($date,8,2)."/".substr($date,5,2)."/".substr($date,0,4);
}

$db_root = $root_db;
$db = $_SESSION['uDb'];

$company = array();
$thisDb = new myDatabase($db_root);
$sql = "SELECT * FROM company WHERE c_id = ".$_SESSION['cId'];
$result = $thisDb->dbQuery($sql);
foreach($result AS $element){
    $company = $element;
}

$thisDb = new myDatabase($db);

$files = glob('data/*'); // get all file names
foreach($files as $file){ // iterate files
  if(is_file($file)) {
    unlink($file); // delete file
  }
}

$archive_file_name = 'data/Rechnung Reprot '.date("YmdHis").'.zip';
$zip = new ZipArchive;
$res = $zip->open($archive_file_name, ZipArchive::CREATE);

$check_st = false;

if($_GET["id"] != "")
    $sql = "SELECT * FROM a_invoice WHERE r_id = '".$_GET["id"]."'";
else if($_GET["id_str"] != "")
    $sql = "SELECT * FROM a_invoice WHERE r_id IN (".$_GET["id_str"].")";

$result = $thisDb->dbQuery($sql);
foreach($result AS $order){
    $check_st = true;

    $sql = "SELECT * FROM customer WHERE k_id = '".$order['k_id']."'";
    $result = $thisDb->dbQuery($sql);
    foreach($result AS $element){
        $myCustomer = $element;
    }

    $date_tmp = substr($order['date'],0,4).substr($order['date'],5,2).substr($order['date'],8,2);
    $date_tmp_2 = substr($order['date'],8,2).".".substr($order['date'],5,2).".".substr($order['date'],0,4);
    $liefer_date_tmp = substr($order['lieferdatum'],0,4).substr($order['lieferdatum'],5,2).substr($order['lieferdatum'],8,2);
    $liefer_date_tmp_2 = substr($order['lieferdatum'],8,2).".".substr($order['lieferdatum'],5,2).".".substr($order['lieferdatum'],0,4);

    $faelligdate = date('d.m.Y', strtotime($order['date']. ' + 14 days'));

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // set default header data
    //$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 067', PDF_HEADER_STRING);

    // set header and footer fonts
    //$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    //$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    //$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    //$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    //$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
    //$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

    // set auto page breaks
    //$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    $l = Array();

    // PAGE META DESCRIPTORS --------------------------------------

    $l['a_meta_charset'] = 'UTF-8';
    $l['a_meta_dir'] = 'ltr';
    $l['a_meta_language'] = 'de';

    $grey = "#e2e2e2";

    // TRANSLATIONS --------------------------------------
    $l['w_page'] = 'Seite';
    $pdf->setLanguageArray($l);

    // set font
    //$pdf->setFont('courier', '', 12);
    $pdf->setFont('helvetica', '', 12);

    // add a page
    $pdf->AddPage();

    $subject = "Rechnung ".$order['invoice_no'];
    $bodypdf = "";
    $bodyHtml = "";
    $src = $domain."files/".$db."/logo.png";

    $header = '<html><body>';
	$footer = '</body></html>';	


// Company
$bodyHtml .= '<table width="100%" cellpadding="5" cellspacing="0"><tr>';
$bodyHtml .= '<td align="center">';
$bodyHtml .= '<img height="100" style="object-fit: cover" src="'.$src.'" />';
$bodyHtml .= '</td>';
$bodyHtml .= '<td align="right" style="font-size:12px;">';
$bodyHtml .= '<b>'.$company["c_name"].'</b><br>';
$bodyHtml .= $company["address"].'&nbsp;'.$company["post"].'&nbsp;'.$company["city"].'<br>';
$bodyHtml .= 'Steuer Nr.:'.$company["tax_no"].'&nbsp;UID Nr.:'.$company["uid_no"].'<br>';
$bodyHtml .= 'Tel:'.$company["tel"].'&nbsp;Mobile:'.$company["mobile"].'<br>';
$bodyHtml .= 'WhatsApp:'.$company["whatsapp"].'&nbsp;E-Mail:'.$company["email"].'<br>';
if ($company['website'] != null && $company['website'] != "")
    $bodyHtml .= 'Website:'.$company["website"].'<br>';
if ($company['geschaeftsfuehrer'] != null && $company['geschaeftsfuehrer'] != "")
    $bodyHtml .= 'Geschäftsführer:'.$company["geschaeftsfuehrer"].'<br>';
if ($company['hrb'] != null && $company['hrb'] != "")
    $bodyHtml .= 'Handelsregisternummer:'.$company["hrb"].'<br>';
$bodyHtml .= '</td>';
$bodyHtml .= '</tr></table>';


// second row
$bodyHtml .= '<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>';
//  coloumn - customer
$bodyHtml .= '<td width="50%" style="padding-left: 0px;"><table width="100%" style="border:1px solid #808080;" cellpadding="0" cellspacing="0">';
$bodyHtml .= '<tr><td style="border-bottom:1px solid #808080;"><b style="font-size:12px;">&nbsp;Empfänger</b></td></tr>';
$bodyHtml .= '<tr><td style="font-size:12px;">';
if ($myCustomer["name1"] != null && $myCustomer["name1"] != "")
    $bodyHtml .= '&nbsp;&nbsp;'.$myCustomer["name1"].'<br>';
$bodyHtml .= '&nbsp;&nbsp;'.$myCustomer["k_name"].'<br>';
if ($myCustomer["address"] != null && $myCustomer["address"] != "")
    $bodyHtml .= '&nbsp;&nbsp;'.$myCustomer["address"].'<br>';
if ($myCustomer["post"] != null && $myCustomer["post"] != "")
    $bodyHtml .= '&nbsp;&nbsp;'.$myCustomer["post"];
if ($myCustomer["city"] != null && $myCustomer["city"] != "")
    $bodyHtml .= '&nbsp;'.$myCustomer["city"].'<br>';
else
    $bodyHtml .= '<br>';
if ($myCustomer["country"] != null && $myCustomer["country"] != "")
    $bodyHtml .= '&nbsp;&nbsp;'.$myCustomer["country"];
if ($myCustomer["ustno"] != null && $myCustomer["ustno"] != "") {
    if (isCHECust($myCustomer['ustno']))
        $bodyHtml .= '&nbsp;VAT#:'.$myCustomer["ustno"];
    else
        $bodyHtml .= '&nbsp;Ust-IdNr.:'.$myCustomer["ustno"];
}
$bodyHtml .= '</td></tr></table></td>';
// column - bank
$bodyHtml .= '<td><table width="100%" border="0" cellpadding="2" cellspacing="0">';
$bodyHtml .= '<tr style="font-size:12px" align="right">';
$bodyHtml .= '<td>BANKVERBINDUNG:<br>IBAN:&nbsp;'.$company["iban"].'<br>BIC:&nbsp;'.$company["bic"].'</td>';
$bodyHtml .= '</tr>';
$bodyHtml .= '</table></td>';
// column - QR Code
$qrsrc = "https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=BCD\n001\n1\nSCT\n".$company['bic'] . "\n" . $company['c_name'] . "\n" . $company['iban'] . "\n" . "EUR" . $order['net'] . "\n\n" . $order['invoice_no'] . "\n";

$bodyHtml .= '<td><div id="qrcode" style="width:80px; height:80px; margin-top:0px; margin-bottom:5px;"><img src="'.$qrsrc.'" /></div></td>';

// end of second row	
$bodyHtml .= '</tr></table>';

$bodyHtml .= '<table width="100%" cellspacing="0" cellpadding="0"><tr><td></td></tr></table>';

// Title
$bodyHtml .= '<table width="100%" style="border:1px solid #808080;" cellpadding="2" cellspacing="0">';
$bodyHtml .= '<tr style="font-size:14px">';
$bodyHtml .= '<td><h3>Rechnung</h3></td>';
$bodyHtml .= '<td style="border-left:1px solid #808080;">Nr.:<br>&nbsp;&nbsp;&nbsp;&nbsp;'.$order['invoice_no'].'</td>';
$bodyHtml .= '<td style="border-left:1px solid #808080;">Datum:<br>&nbsp;&nbsp;&nbsp;&nbsp;'.convertDate($order['date']).'</td>';
$bodyHtml .= '<td style="border-left:1px solid #808080;">Kunden Nr.:<br>&nbsp;&nbsp;&nbsp;&nbsp;'.$myCustomer["k_code"].'</td>';
$bodyHtml .= '<td style="border-left:1px solid #808080;">Lieferdatum:<br>&nbsp;&nbsp;&nbsp;&nbsp;'.convertDate($order['lieferdatum']).'</td>';
$bodyHtml .= '<td style="border-left:1px solid #808080;">Währung:<br>&nbsp;&nbsp;&nbsp;&nbsp;EUR</td>';
$bodyHtml .= '<td style="border-left:1px solid #808080;">Seite:<br>&nbsp;&nbsp;&nbsp;&nbsp;1/1</td>';
$bodyHtml .= '</tr></table>';
// Table
$bodyHtml .= '<table width="100%" style="border:1px solid #808080;" cellpadding="2" cellspacing="0"><thead>';
$bodyHtml .= '<tr style="font-size:12px">';
$bodyHtml .= '<th width="50%" style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="center" >Artikel Nr. und Bezeichnung</th>';
$bodyHtml .= '<th width="10%" style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="center">Anzahl</th>';
$bodyHtml .= '<th width="15%" style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="right">Einzelpreis</th>';
$bodyHtml .= '<th width="15%" style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="right">Nettobetrag</th>';
$bodyHtml .= '<th width="10%" style="border-left:1px solid #808080; border-bottom:1px solid #808080;" align="right">MwSt.</th>';
$bodyHtml .= '</tr></thead><tbody>';






	
    $bodypdf .= '<table width="100%" cellpadding="0" cellspacing="0"><tr>';
    // Company //
    if($company["country"] == "Deutschland") $company["country"] = "DE";
    $bodypdf .= '<td colspan="3" width="60%">';
    $bodypdf .= '<b>'.$company["c_name"].'</b></td>';
    $bodypdf .= '<td colspan="3" width="40%" align="right">';
    $bodypdf .= '<u>Zahlungsdetails</u></td></tr>';

    $bodypdf .= '<tr><td colspan="3" width="50%">';
    $bodypdf .= $company["address"].'<br>';
    $bodypdf .= $company["post"].'&nbsp;'.$company["city"].', '.$company["country"].'<br><br>';

    $bodypdf .= 'Ust-ID.: '.$company["uid_no"].'<br>';
    $bodypdf .= 'St-Nr.: '.$company["tax_no"].'<br>';
    $bodypdf .= 'E-Adresse: '.$company["email"].'<br>';
    $bodypdf .= '</td>';

    // Zahlungsdetails //
    $bodypdf .= '<td colspan="3" width="50%" align="right">';
    $bodypdf .= 'Bank: Deutsche Bank<br>';
    $bodypdf .= 'Kontoinhaber: '.$company["c_name"].'<br>';
    $bodypdf .= 'IBAN: '.$company["iban"].'<br>';
    $bodypdf .= 'BIC: '.$company["bic"].'<br><br>';

    $bodypdf .= '<u>Kontakt</u><br>';
    $bodypdf .= 'Name: '.$company["geschaeftsfuehrer"].'<br>';
    $bodypdf .= 'Tel: '.$company["tel"].'<br>';
    $bodypdf .= '</td>';

    $bodypdf .= '</tr>';
    $bodypdf .= '<tr><td colspan="6"><hr style="color: '.$grey.'; height: 1px; background-color: '.$grey.';"></td></tr>';


    // Empfänger //
    $bodypdf .= '<tr>';
    // Company //
    if($myCustomer["country"] == 'Germany') $myCustomer["country"] = 'DE';
    $bodypdf .= '<td rowspan="5" colspan="3" width="50%">';
    $bodypdf .= '<b>Empänger</b><br/>';
    $bodypdf .=  $myCustomer["k_name"].'<br/>';
    $bodypdf .=  $myCustomer["address"].'<br/>';
    $bodypdf .=  $myCustomer["post"].' '.$myCustomer["city"].', '.$myCustomer["country"].'<br/><br/>';

    if($myCustomer["ustno"] != "")
        $bodypdf .=  'Ust-ID: '.$myCustomer["ustno"].'<br/>';
    if($myCustomer["email"] != "")
        $bodypdf .=  'E-Adresse: '.$myCustomer["email"].'<br/>';
    if($myCustomer["tel"] != "")
        $bodypdf .=  'Tel: '.$myCustomer["tel"];
    $bodypdf .= '</td>';
    $bodypdf .= '<td width="25%">Rechnungs-Nr.:</td>';
    $bodypdf .= '<td width="25%" align="right">'.$order['invoice_no'].'</td></tr>';

    $bodypdf .= '<tr style="background-color: '.$grey.';"><td width="25%">Rechnungsdatum:</td>';
    $bodypdf .= '<td width="25%" align="right">'.$date_tmp_2.'</td></tr>';


    $bodypdf .= '<tr><td width="25%">Lieferdatum:</td>';
    $bodypdf .= '<td width="25%" align="right">'.$liefer_date_tmp_2.'</td></tr>';

    $bodypdf .= '<tr style="background-color: '.$grey.';"><td width="25%">Fälligkeitsdatum:</td>';
    $bodypdf .= '<td width="25%" align="right">'.$faelligdate.'</td></tr>';

    $bodypdf .= '<tr><td width="25%">Bestellnummber:</td>';
    $bodypdf .= '<td width="25%" align="right">'.$order['o_id'].'</td></tr>';
    

    // Rechnung //
    $bodypdf .= '<tr><td colspan="6"></td></tr>';
    $bodypdf .= '<tr><td colspan="6"><h3>Rechnung</h3></td></tr>';
    $bodypdf .= '<tr><td colspan="6"></td></tr>';

    $bodypdf .= '<tr style="background-color: '.$grey.';"><td style="border-bottom:2px solid #000000;" width="6%"></td><td style="border-bottom:2px solid #000000;" width="45%">Position</td><td style="border-bottom:2px solid #000000;" width="10%">Anzahl</td><td style="border-bottom:2px solid #000000;" width="13%">Preis</td><td style="border-bottom:2px solid #000000;" width="13%">Steuer</td><td style="border-bottom:2px solid #000000;" width="13%">Gesamt</td></tr>';


    $output = '<?xml version="1.0" encoding="UTF-8" ?><rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100" xmlns:a="urn:un:unece:uncefact:data:standard:QualifiedDataType:100" xmlns:qdt="urn:un:unece:uncefact:data:standard:QualifiedDataType:10" xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
    $output .= '<rsm:ExchangedDocumentContext>';
    $output .= '<ram:GuidelineSpecifiedDocumentContextParameter>';
    $output .= '<ram:ID>urn:cen.eu:en16931:2017</ram:ID>';
    $output .= '</ram:GuidelineSpecifiedDocumentContextParameter>';
    $output .= '</rsm:ExchangedDocumentContext>';
    
    $output .= '<rsm:ExchangedDocument>';
    $output .= '<ram:ID>'.$order['invoice_no'].'</ram:ID>';
    $output .= '<ram:TypeCode>380</ram:TypeCode>';
    $output .= '<ram:IssueDateTime>';
    $output .= '<udt:DateTimeString format="102">'.$date_tmp.'</udt:DateTimeString>';
    $output .= '</ram:IssueDateTime>';
    $output .= '<ram:IncludedNote>';
    $output .= '<ram:Content>Rechnung gemäß Bestellung vom '.$date_tmp_2.'.</ram:Content>';
    $output .= '</ram:IncludedNote>';
    $output .= '<ram:IncludedNote>';
    $output .= '<ram:Content>'.$company["c_name"].' ';
    $output .= $company["address"].', ';
    $output .= $company["post"] . ' ' . $company["city"] .' ';
    $output .= $company["country"].' ';
    $output .= 'Geschäftsführer: ' . $company["geschaeftsfuehrer"].' ';
    $output .= 'Handelsregisternummer: ' . $company["hrb"].' ';
    $output .= '</ram:Content>';
    $output .= '<ram:SubjectCode>REG</ram:SubjectCode>';
    $output .= '</ram:IncludedNote>';
    $output .= '</rsm:ExchangedDocument>';
    $output .= '<rsm:SupplyChainTradeTransaction>';

    $sql = "SELECT a_in_items.*, inventory.i_code, inventory.i_name, inventory.path, inventory.m_no FROM a_in_items,inventory  WHERE r_id = '".$order['r_id']."' AND a_in_items.i_id = inventory.i_id";
    $a_in_items = $thisDb->dbQuery($sql);
    $i=0;
    foreach($a_in_items AS $orderItems){

        if ($orderItems['unit'] == 1) {
			$orderItems['real_count'] = $orderItems['count'];
		} else {
			$orderItems['real_count'] = $orderItems[i]['count']*$orderItems['unit'];
		}

        $priceStr = $orderItems['price'];
        $pdf_priceStr = $orderItems['price'];
        $discount = $orderItems['discount'];
        $rabatt = "";
        if($discount > 0){
			$rabatt = " (Rabatt: ".toFixed($discount, 0)."%)";
			$priceStr = toFixed((((100-$discount) * $orderItems['price']) /100),2);
            $pdf_priceStr = "<del>".$orderItems['price']."</del> ".$priceStr;
		}
        $discount = 0;
		if (floatval($order['discount_rate'])>0) {
			$rabatt .= " (Rabatt: ".$order['discount_rate']."%)";
			$discount = floatval($orderItems['subtotal'])*floatval($order['discount_rate'])/100;
			$orderItems['subtotal'] = toFixed((floatval($orderItems['subtotal']) - $discount),2);
		}
        $subtotal = $orderItems['real_count']*floatval($priceStr);
		$orderItems['subtotal'] = toFixed($subtotal,2);

        $code = "";
		if ($orderItems['i_id'] != "0") {
			if ($orderItems['i_name'] != null && $orderItems['i_name'] != "")
				$code .= $orderItems['i_name'].'&nbsp;';
			$code .= 'ART.'.$orderItems['i_code'];
		} else {
			$code .= 'ART.'.$orderItems['ai_code'];
		}
		if (isCHECust($myCustomer['ustno']) && $orderItems['note'] != null)
			$code .= '&nbsp;'.$orderItems['note'];

        $countStr = $orderItems['count'];
		if ($orderItems['unit'] != "1")
			$countStr = $orderItems['count']." (x".$orderItems['unit'].")";


        $bodyHtml .= '<tr style="font-size:12px;">';
        $bodyHtml .= '<td width="50%" style="padding:1px; border-left:1px solid #808080;">'.$code.$rabatt.'</td>';
        $bodyHtml .= '<td width="10%" style="padding:1px; border-left:1px solid #808080;" align="right">'.$countStr.'&nbsp;</td>';		
        $bodyHtml .= '<td width="15%" style="padding:1px; border-left:1px solid #808080;" align="right">'.$pdf_priceStr.'</td>';
        $bodyHtml .= '<td width="15%" style="padding:1px; border-left:1px solid #808080;" align="right">'.$orderItems['subtotal'].'</td>';
        $bodyHtml .= '<td width="10%" style="padding:1px; border-left:1px solid #808080;" align="right">'.$order['tax_rate'].'</td>';
        $bodyHtml .= '</tr>';

        $bodypdf .= '<tr><td width="6%">'.($i+1).'</td>';
        $bodypdf .= '<td width="45%">'.$code.$rabatt.'</td>';
        $bodypdf .= '<td width="10%">'.$countStr.'</td>';
        $bodypdf .= '<td width="13%">'.$pdf_priceStr.' €</td>';
        $bodypdf .= '<td width="13%">'.$order['tax_rate'].'%</td>';
        $bodypdf .= '<td width="13%">'.$orderItems['subtotal'].' €</td></tr>';


        $output .= '<ram:IncludedSupplyChainTradeLineItem>';
        $output .= '<ram:AssociatedDocumentLineDocument>';
        $output .= '<ram:LineID>'.($i+1).'</ram:LineID>';
        $output .= '</ram:AssociatedDocumentLineDocument>';
        $output .= '<ram:SpecifiedTradeProduct>';
        $output .= '<ram:GlobalID schemeID="0160">'.$orderItems['i_code'].'</ram:GlobalID>';
        $output .= '<ram:SellerAssignedID>'.$orderItems['i_id'].'</ram:SellerAssignedID>';
        $output .= '<ram:Name>'.$orderItems['i_name'].' ' . $rabatt .'</ram:Name>';
        $output .= '</ram:SpecifiedTradeProduct>';
        $output .= '<ram:SpecifiedLineTradeAgreement>';
        $output .= '<ram:GrossPriceProductTradePrice>';
        $output .= '<ram:ChargeAmount>'.$priceStr.'</ram:ChargeAmount>';
        $output .= '</ram:GrossPriceProductTradePrice>';
        $output .= '<ram:NetPriceProductTradePrice>';
        $output .= '<ram:ChargeAmount>'.$priceStr.'</ram:ChargeAmount>';
        $output .= '</ram:NetPriceProductTradePrice>';
        $output .= '</ram:SpecifiedLineTradeAgreement>';
        $output .= '<ram:SpecifiedLineTradeDelivery>';
        $output .= '<ram:BilledQuantity unitCode="H87">'.$orderItems['real_count'].'</ram:BilledQuantity>';
        $output .= '</ram:SpecifiedLineTradeDelivery>';
        $output .= '<ram:SpecifiedLineTradeSettlement>';
        $output .= '<ram:ApplicableTradeTax>';
        $output .= '<ram:TypeCode>VAT</ram:TypeCode>';
        $output .= '<ram:CategoryCode>S</ram:CategoryCode>';
        $output .= '<ram:RateApplicablePercent>'.$order['tax_rate'].'</ram:RateApplicablePercent>';
        $output .= '</ram:ApplicableTradeTax>';
        $output .= '<ram:SpecifiedTradeSettlementLineMonetarySummation>';
        $output .= '<ram:LineTotalAmount>'.$orderItems['subtotal'].'</ram:LineTotalAmount>';
        $output .= '</ram:SpecifiedTradeSettlementLineMonetarySummation>';
        $output .= '</ram:SpecifiedLineTradeSettlement>';
        $output .= '</ram:IncludedSupplyChainTradeLineItem>';
        $i++;
    }

    $bodyHtml .= '<tr><td align="center" style="font-size:12px; font-family:Arial; border-top:1px solid #808080;" colspan="5">===Gesamtmenge:&nbsp;'.$order['count_sum'].'&nbsp;Stück===</td></tr>';
    if (notGermanCust($myCustomer['ustno']) && !isCHECust($myCustomer['ustno']))
        $bodyHtml .= '<tr><td align="center" style="font-size:12px; font-family:Arial" colspan="5">Die i.g. Lieferung erfolgt gem. &sect;6a UStG bzw. nach Artikel 22 Ab s. 3 der 6.EG-Richtlinie steuerfrei. Muster einer Gelangensbestätigung im Sinne des &sect;17a Abs.2 Nr.2 UstDV</td></tr>';
    if (isCHECust($myCustomer['ustno']) )
        $bodyHtml .= '<tr><td align="center" style="font-size:12px; font-family:Arial" colspan="5">Der Ausführer der Waren, auf die sich dieses Handelspapier bezieht, erklärt, dass diese Waren, so weit nich anders angegeben, präferenzbegünstigte EU-Ursprungswaren sind.<br>Neuss, '.convertDate($order['date']).'</td></tr>';


    // Spacing
	$maxCount = 21;
	if ($order['price_sum'] == $order['total_sum'] && $order['discount_rate'] == '0.00')
		$maxCount = 26;
	if (notGermanCust($myCustomer['ustno'])) {
		if (isCHECust($myCustomer['ustno']))
			$maxCount = $maxCount - 5;
		else
			$maxCount = $maxCount - 2;
	}
	for ($t=0; $t<$maxCount-$i-3; $t++) {
		$bodyHtml .= '<tr><td style="padding:1px; font-size:12px;" colspan="5">&nbsp;</td></tr>';
	}
	$bodyHtml .= '</tbody></table>';



    // kosten //
	$fee = 0;
    // Fees
    if (floatval($order['fee1'])>0) {
        $fee += floatval($order['fee1']);
    }
    if (floatval($order['fee2'])>0) {
        $fee += floatval($order['fee2']);
    }
    if (floatval($order['fee3'])>0) {
        $fee += floatval($order['fee3']);
    }
    if (floatval($order['fee4'])>0) {
        $fee += floatval($order['fee4']);
    }
    if (floatval($order['fee5'])>0) {
        $fee += floatval($order['fee5']);
    }
    if($fee > 0){
		$output .= '<ram:IncludedSupplyChainTradeLineItem>';
        $output .= '<ram:AssociatedDocumentLineDocument>';
        $output .= '<ram:LineID>'.($i+1).'</ram:LineID>';
        $output .= '</ram:AssociatedDocumentLineDocument>';
        $output .= '<ram:SpecifiedTradeProduct>';
        $output .= '<ram:Name>Extra Kosten</ram:Name>';
        $output .= '</ram:SpecifiedTradeProduct>';
        $output .= '<ram:SpecifiedLineTradeAgreement>';
        $output .= '<ram:GrossPriceProductTradePrice>';
        $output .= '<ram:ChargeAmount>'.$fee.'</ram:ChargeAmount>';
        $output .= '</ram:GrossPriceProductTradePrice>';
        $output .= '<ram:NetPriceProductTradePrice>';
        $output .= '<ram:ChargeAmount>'.$fee.'</ram:ChargeAmount>';
        $output .= '</ram:NetPriceProductTradePrice>';
        $output .= '</ram:SpecifiedLineTradeAgreement>';
        $output .= '<ram:SpecifiedLineTradeDelivery>';
        $output .= '<ram:BilledQuantity unitCode="H87">1</ram:BilledQuantity>';
        $output .= '</ram:SpecifiedLineTradeDelivery>';
        $output .= '<ram:SpecifiedLineTradeSettlement>';
        $output .= '<ram:ApplicableTradeTax>';
        $output .= '<ram:TypeCode>VAT</ram:TypeCode>';
        $output .= '<ram:CategoryCode>S</ram:CategoryCode>';
        $output .= '<ram:RateApplicablePercent>'.$order['tax_rate'].'</ram:RateApplicablePercent>';
        $output .= '</ram:ApplicableTradeTax>';
        $output .= '<ram:SpecifiedTradeSettlementLineMonetarySummation>';
        $output .= '<ram:LineTotalAmount>'.$fee.'</ram:LineTotalAmount>';
        $output .= '</ram:SpecifiedTradeSettlementLineMonetarySummation>';
        $output .= '</ram:SpecifiedLineTradeSettlement>';
        $output .= '</ram:IncludedSupplyChainTradeLineItem>';
	}

    $seller_tel = $company['tel'];
	if($seller_tel == "") $seller_tel = $company['mobile'];
    $output .= '<ram:ApplicableHeaderTradeAgreement>';
    $output .= '<ram:BuyerReference>'.$order['invoice_no'].'</ram:BuyerReference>';
    $output .= '<ram:SellerTradeParty>';
    $output .= '<ram:ID>'.$company['c_id'].'</ram:ID>';
    $output .= '<ram:GlobalID schemeID="0088">'.$company['c_id'].'</ram:GlobalID>';
    $output .= '<ram:Name>'.$company['c_name'].'</ram:Name>';
    $output .= '<ram:DefinedTradeContact>';
    $output .= '<ram:PersonName>'.$company['geschaeftsfuehrer'].'</ram:PersonName>';
    $output .= '<ram:DepartmentName>Buchhaltung</ram:DepartmentName>';
    $output .= '<ram:TelephoneUniversalCommunication>';
    $output .= '<ram:CompleteNumber>'.$seller_tel.'</ram:CompleteNumber>';
    $output .= '</ram:TelephoneUniversalCommunication>';
    $output .= '<ram:EmailURIUniversalCommunication>';
    $output .= '<ram:URIID>'.$company['email'].'</ram:URIID>';
    $output .= '</ram:EmailURIUniversalCommunication>';
    $output .= '</ram:DefinedTradeContact>';
    $output .= '<ram:PostalTradeAddress>';
    $output .= '<ram:PostcodeCode>'.$company['post'].'</ram:PostcodeCode>';
    $output .= '<ram:LineOne>'.$company['address'].'</ram:LineOne>';
    $output .= '<ram:CityName>'.$company['city'].'</ram:CityName>';
    $output .= '<ram:CountryID>DE</ram:CountryID>';
    $output .= '</ram:PostalTradeAddress>';
    $output .= '<ram:SpecifiedTaxRegistration>';
    $output .= '<ram:ID schemeID="FC">'.$company["tax_no"].'</ram:ID>';
    $output .= '</ram:SpecifiedTaxRegistration>';
    $output .= '<ram:SpecifiedTaxRegistration>';
    $output .= '<ram:ID schemeID="VA">'.$company["uid_no"].'</ram:ID>';
    $output .= '</ram:SpecifiedTaxRegistration>';
    $output .= '</ram:SellerTradeParty>';
    $output .= '<ram:BuyerTradeParty>';
    $output .= '<ram:ID>'.$myCustomer["k_id"].'</ram:ID>';
    $output .= '<ram:Name>'.$myCustomer["k_name"].'</ram:Name>';
    $output .= '<ram:PostalTradeAddress>';
    $output .= '<ram:PostcodeCode>'.$myCustomer["post"].'</ram:PostcodeCode>';
    $output .= '<ram:LineOne>'.$myCustomer["address"].'</ram:LineOne>';
    $output .= '<ram:CityName>'.$myCustomer["city"].'</ram:CityName>';
    $output .= '<ram:CountryID>DE</ram:CountryID>';
    $output .= '</ram:PostalTradeAddress>';
    $output .= '</ram:BuyerTradeParty>';
    $output .= '</ram:ApplicableHeaderTradeAgreement>';
    $output .= '<ram:ApplicableHeaderTradeDelivery>';
    $output .= '<ram:ActualDeliverySupplyChainEvent>';
    $output .= '<ram:OccurrenceDateTime>';
    $output .= '<udt:DateTimeString format="102">'.$liefer_date_tmp.'</udt:DateTimeString>';
    $output .= '</ram:OccurrenceDateTime>';
    $output .= '</ram:ActualDeliverySupplyChainEvent>';
    $output .= '</ram:ApplicableHeaderTradeDelivery>';
    $output .= '<ram:ApplicableHeaderTradeSettlement>';
    $output .= '<ram:InvoiceCurrencyCode>EUR</ram:InvoiceCurrencyCode>';
    $output .= '<ram:SpecifiedTradeSettlementPaymentMeans>';
	$typcode = 58;
	if(floatval($order['pay_cash']) > 0) $typcode = 10;
	else if(floatval($order['pay_check']) > 0) $typcode = 20;
	else if(floatval($order['pay_bank']) > 0) $typcode = 30;
	else if(floatval($order['pay_card']) > 0) $typcode = 48;
    $output .= '<ram:TypeCode>'.$typcode.'</ram:TypeCode>';
    $output .= '<ram:PayeePartyCreditorFinancialAccount>';
    $output .= '<ram:IBANID>'.$company["iban"].'</ram:IBANID>';
    $output .= '</ram:PayeePartyCreditorFinancialAccount>';
    $output .= '</ram:SpecifiedTradeSettlementPaymentMeans>';
    $output .= '<ram:ApplicableTradeTax>';
	$tax = toFixed(floatval($order['total_sum'])*floatval($order['tax_rate'])/100+0.0000001,2);	
    $output .= '<ram:CalculatedAmount>'.$tax.'</ram:CalculatedAmount>';
    $output .= '<ram:TypeCode>VAT</ram:TypeCode>';
    $output .= '<ram:BasisAmount>'.$order['total_sum'].'</ram:BasisAmount>';
    $output .= '<ram:CategoryCode>S</ram:CategoryCode>';
    $output .= '<ram:RateApplicablePercent>'.$order['tax_rate'].'</ram:RateApplicablePercent>';
	$output .= '</ram:ApplicableTradeTax>';
    $output .= '<ram:SpecifiedTradePaymentTerms>';
    $output .= '<ram:Description>* Die Waren bleiben bis zur vollständigen Bezahlung unser Eigentum. Reklamation nur innerhalb von 7 Tagen. ';
	$output .= '<br>* Bitte kontrollieren Sie die berechnete Menge sofort. Spätere Mengenreklamationen können nicht anerkannt werden. Reduzierte Ware ist vom Umtausch und Skonto ausgeschlossen. ';
	$output .= '<br>Im Falle der Rechnungsbegleichung per Überweisung bitten wir Sie, den fälligen Betrag innerhalb von 14 Tagen auf unser Konto bei der Sparkasse Neuss mit der IBAN '.$company["iban"].' (BIC '.$company["bic"].') zu überweisen. Wir bitten Sie, auf Ihrer Überweisung die Rechnungsnummer anzugeben.';
	$output .= '</ram:Description>';
    $output .= '</ram:SpecifiedTradePaymentTerms>';
    $output .= '<ram:SpecifiedTradeSettlementHeaderMonetarySummation>';

	
	
	// Total

	$output .= '<ram:LineTotalAmount>'.$order['total_sum'].'</ram:LineTotalAmount>';
	$output .= '<ram:ChargeTotalAmount>0</ram:ChargeTotalAmount>';
	$output .= '<ram:AllowanceTotalAmount>0</ram:AllowanceTotalAmount>';
	$output .= '<ram:TaxBasisTotalAmount>'.$order['total_sum'].'</ram:TaxBasisTotalAmount>';
	$output .= '<ram:TaxTotalAmount currencyID="EUR">'.$tax.'</ram:TaxTotalAmount>';
	$output .= '<ram:GrandTotalAmount>'.$order['net'].'</ram:GrandTotalAmount>';
	$output .= '<ram:TotalPrepaidAmount>0.00</ram:TotalPrepaidAmount>';
	$output .= '<ram:DuePayableAmount>'.$order['net'].'</ram:DuePayableAmount>';
    $output .= '</ram:SpecifiedTradeSettlementHeaderMonetarySummation>';
    $output .= '</ram:ApplicableHeaderTradeSettlement>';
    $output .= '</rsm:SupplyChainTradeTransaction>';
	$output .= '</rsm:CrossIndustryInvoice>';

    $bodyHtml .= '<table width="100%" cellspacing="0" cellpadding="0"><tr><td></td></tr></table>';
    $bodypdf .= '<tr><td colspan="6"><hr style="color: '.$grey.'; height: 1px; background-color: '.$grey.';"></td></tr>';

    // Summary
    $bodyHtml .= '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
    // Left
    $bodyHtml .= '<tr>';
    $bodyHtml .= '<td width="49%" valign="top"><table width="100%" style="border:1px solid #808080;" cellpadding="0" cellspacing="0">';

    $bodyHtml .= '<tr align="right" style="font-size:12px">';
    $bodyHtml .= '<td style="padding:1px;">MwSt Code</td>';
    $bodyHtml .= '<td style="padding:1px;">Satz</td>';
    $bodyHtml .= '<td style="padding:1px;">Nettobetrag</td>';
    $bodyHtml .= '<td style="padding:1px;">MwSt&nbsp;&nbsp;</td>';
    $bodyHtml .= '</tr>';
    $bodyHtml .= '<tr align="right" style="font-size:12px">';
    $bodyHtml .= '<td style="padding:1px;">'.$order['tax_rate'].'</td>';
    $bodyHtml .= '<td style="padding:1px;">'.$order['tax_rate'].'%</td>';
    $bodyHtml .= '<td style="padding:1px;">'.$order['total_sum'].'</td>';
    $bodyHtml .= '<td style="padding:1px;">'.$tax.'&nbsp;&nbsp;</td>';
    $bodyHtml .= '</tr>';


    // Payment
$pays_num = 0;
$bodyHtml .= '<tr style="font-size:12px;">';
$bodyHtml .= '<td colspan="4" style="border-top:1px solid #808080;">';
$bodyHtml .= '<p><br>&nbsp;&nbsp;Zahlungsart:&nbsp;';
if (notZero($order['pay_cash'])) {
    $bodyHtml .= 'Bar:&nbsp;'.$order['pay_cash'].'&nbsp;'; $pays_num++;
}
if (notZero($order['pay_card'])) {
    $bodyHtml .= 'Karte:&nbsp;'.$order['pay_card'].'&nbsp;'; $pays_num++;
}
if (notZero($order['pay_bank'])) {
    if ($pays_num == 2) $bodyHtml .= '<br>';
    $bodyHtml .= 'Überweisung:&nbsp;'.$order['pay_bank'].'&nbsp;'; $pays_num++;
}
if (notZero($order['pay_check'])) {
    if ($pays_num == 2) $bodyHtml .= '<br>';
    $bodyHtml .= 'Scheck:&nbsp;'.$order['pay_check'].'&nbsp;'; $pays_num++;
}
if (notZero($order['pay_other'])) {
    if ($pays_num == 2) $bodyHtml .= '<br>';
    $bodyHtml .= 'Nachnahme:&nbsp;'.$order['pay_other']; $pays_num++;
}
if (notZero($order['pay_paypal'])) {
    if ($pays_num == 2) $bodyHtml .= '<br>';
    $bodyHtml .= 'PayPal:&nbsp;'.$order['pay_paypal'].'&nbsp;'; $pays_num++;
}	
if (notZero($order['pay_vorkasse'])) {
    if ($pays_num == 2) $bodyHtml .= '<br>';
    $bodyHtml .= 'Vorkasse:&nbsp;'.$order['pay_vorkasse'].'&nbsp;'; $pays_num++;
}	
if ($order['note'] != null && $order['note'] != "") {
    $bodyHtml .= '<br>&nbsp;&nbsp;Memo:&nbsp;'.$order['note']."<br>";
}

$bodyHtml .= '<br></p></td>';
$bodyHtml .= '</tr>';

$bodyHtml .= '</table></td>';


// Right
$bodyHtml .= '<td width="2%"></td>';
$bodyHtml .= '<td width="49%" valign="top" style="padding-right: 0px;">';
$bodyHtml .= '<table width="100%" style="min-height: 74px; border:1px solid #808080;" cellpadding="0" cellspacing="0">';	

// Discount
if (notZero($order['discount_rate'])) {
    $discount = toFixed(floatval($order['price_sum'])*floatval($order['discount_rate'])/100, 2);
    $nettosumme = floatval($order['price_sum']) - floatval($discount);
    $bodyHtml .= '<tr style="font-size:12px;">';
    $bodyHtml .= '<td style="padding:1px;" align="right">Summe:</td><td style="padding:1px;" align="right">'.$order['price_sum'].'&nbsp;&nbsp;</td>';
    $bodyHtml .= '</tr>';
    $bodyHtml .= '<tr style="font-size:12px;">';
    $bodyHtml .= '<td style="padding:1px;" align="right">Skont:&nbsp;'.$order['discount_rate'].'%:</td><td style="padding:1px;" align="right">'.$discount.'&nbsp;&nbsp;</td>';
    $bodyHtml .= '</tr>';
    $bodyHtml .= '<tr style="font-size:12px;">';
    $bodyHtml .= '<td style="padding:1px; border-bottom:1px solid #808080; border-top:1px solid #808080;" align="right">Nettosumme:</td><td align="right" style="padding:1px; border-bottom:1px solid #808080; border-top:1px solid #808080;">'.$nettosumme.'&nbsp;&nbsp;</td>';
    $bodyHtml .= '</tr>';
}

// Fees
if (notZero($order['fee1'])) {
    $bodyHtml .= '<tr style="font-size:12px;">';
    $bodyHtml .= '<td style="padding:1px;" align="right">Versandkosten:&nbsp;</td><td style="padding:1px;" align="right">'.$order['fee1'].'&nbsp;&nbsp;</td>';
    $bodyHtml .= '</tr>';
}

if (notZero($order['fee2'])) {
    $bodyHtml .= '<tr style="font-size:12px;">';
    $bodyHtml .= '<td style="padding:1px;" align="right">Nachnahmekosten:&nbsp;</td><td style="padding:1px;" align="right">'.$order['fee2'].'&nbsp;&nbsp;</td>';
}
if (notZero($order['fee3'])) {
    $bodyHtml .= '<tr style="font-size:12px;">';
    $bodyHtml .= '<td style="padding:1px;" align="right">Inkassokosten:&nbsp;</td><td style="padding:1px;" align="right">'.$order['fee3'].'&nbsp;&nbsp;</td>';
    $bodyHtml .= '</tr>';
}
if (notZero($order['fee4'])) {
    $bodyHtml .= '<tr style="font-size:12px;">';
    $bodyHtml .= '<td style="padding:1px;" align="right">Verpackungskosten:&nbsp;</td><td style="padding:1px;" align="right">'.$order['fee4'].'&nbsp;&nbsp;</td>';
    $bodyHtml .= '</tr>';
}
if (notZero($order['fee5'])) {
    $bodyHtml .= '<tr style="font-size:12px;">';
    $bodyHtml .= '<td style="padding:1px;" align="right">Nebenkosten:&nbsp;</td><td style="padding:1px;" align="right">'.$order['fee5'].'&nbsp;&nbsp;</td>';
    $bodyHtml .= '</tr>';
}

// Total
$bodyHtml .= '<tr style="font-size:14px;">';
$bodyHtml .= '<td style="padding:1px; border-top:1px solid #808080;" align="right"><b>Steuergrundlage:</b></td><td style="padding:1px; border-top:1px solid #808080;" align="right">'.$order['total_sum'].'&nbsp;&nbsp;</td>';
$bodyHtml .= '</tr>';

$bodyHtml .= '<tr style="font-size:14px;">';
$bodyHtml .= '<td style="padding:1px;" align="right"><b>Total MwSt.:</b></td><td style="padding:1px;" align="right">'.$tax.'&nbsp;&nbsp;</td>';
$bodyHtml .= '</tr>';

$bodyHtml .= '<tr style="font-size:14px;">';
$bodyHtml .= '<td style="padding:1px;" align="right"><b>Total (inkl. MwSt):</b></td><td style="padding:1px;" align="right"><b style="font-size: 20px;">'.$order['net'].'&nbsp;&nbsp;</b></td>';
$bodyHtml .= '</tr>';

$bodyHtml .= '</table></td>';

$bodyHtml .= '</tr>';
$bodyHtml .= '</table>';


// reklamation
$bodyHtml .= '<p style="font-size:9px;">* Die Waren bleiben bis zur vollständigen Bezahlung unser Eigentum. Reklamation nur innerhalb von 7 Tagen.</p>';

// reklamation1
if ($order['pay_bank'] > 0 || $order['pay_vorkasse'] > 0) {
    $bodyHtml .= '<p style="font-size:9px;">'.
                '* Bitte kontrollieren Sie die berechnete Menge sofort. Spätere Mengenreklamationen können nicht anerkannt werden. Reduzierte Ware ist vom Umtausch und Skonto ausgeschlossen.'. 
                ' Im Falle der Rechnungsbegleichung per Überweisung bitten wir Sie, den fälligen Betrag innerhalb von 14 Tagen auf unser Konto bei der Sparkasse Neuss mit der IBAN '.$company["iban"].' (BIC '.$company["bic"].') zu überweisen. Wir bitten Sie, auf Ihrer Überweisung die Rechnungsnummer anzugeben.'.
                '</p>';
}
                


$bodyHtml = $header . $bodyHtml;

    // Fees
    if($fee > 0){
        $bodypdf .= '<tr><td width="50%"></td><td width="25%">Extra Kosten:</td><td width="25%" align="right">'.toFixed($fee,2).' €</td></tr>';
    }

    // Discount
    if (notZero($order['discount_rate'])) {
        $discount = toFixed(floatval($order['price_sum'])*floatval($order['discount_rate'])/100, 2);
        $nettosumme = floatval($order['price_sum']) - floatval($discount);

        $bodypdf .= '<tr><td width="50%"></td><td width="25%">Summe:</td><td width="25%" align="right">'.$order['price_sum'].' €</td></tr>';
        $bodypdf .= '<tr><td width="50%"></td><td width="25%">Skont:</td><td width="25%" align="right">'.$discount.' €</td></tr>';
        $bodypdf .= '<tr><td width="50%"></td><td width="25%">Nettosumme:</td><td width="25%" align="right">'.$nettosumme.' €</td></tr>';

    }


    $bodypdf .= '<tr><td width="50%"></td><td width="25%">Gesamt (Netto):</td><td width="25%" align="right">'.$order['total_sum'].' €</td></tr>';
    $bodypdf .= '<tr><td width="50%"></td><td width="25%">Steuer '.$order['tax_rate'].'% (S):</td><td width="25%" align="right">'.$tax.' €</td></tr>';
    $bodypdf .= '<tr><td width="50%"></td><td width="25%">Gesamt (Brutto):</td><td width="25%" align="right">'.$order['net'].' €</td></tr>';

    $bodypdf .= '<tr><td width="50%"></td><td style="background-color:'.$grey.'" width="25%">Fälliger Betrag:</td><td style="background-color:'.$grey.'" width="25%" align="right">'.$order['net'].' €</td></tr>';



    $bodypdf .= '<tr><td colspan="6"></td></tr>';

    $bodypdf .= '<tr><td width="100%;" colspan="6"><b>Hinweise und Bemerkungen</b><br>';
    $bodypdf .= 'Bitte überweisen Sie den Rechnungsbetrag in Höhe von '.$order['net'].' EUR bis zum Fälligkeitsdtum '.$faelligdate.'</td></tr>';


                
$bodypdf .= '</table>';

$bodypdf = $header . $bodypdf;

//$bodyHtml .= '<p style="page-break-before: always"></p>';

	
// Print Gelangensbestaetigung
	if (notGermanCust($myCustomer['ustno']) && !isCHECust($myCustomer['ustno'])) {

		$bodyHtml .= '<p style="page-break-before: always">';
		$bodyHtml .= '<table width="100%" cellpadding="2" cellspacing="0"><tr><td align="right">';
		$bodyHtml .= 'Rechnungsnummmer:&nbsp;'.$order['invoice_no'].'<br>';
		if ($company['email'] != null && $company['email'] != "")
			$bodyHtml .=  $company['email'].'<br>';
		else
			$bodyHtml .=  $company['tel'].'<br>';
		$bodyHtml .= '</td></tr></table>';
		$bodyHtml .= '<h3 style="text-align: center;">Gelangensbestätigung</h3><br>';
		$bodyHtml .= '<p>Bestätigung über das Gelangen des Gegenstands einer innergemeinschatflichen Lieferung in einen anderen EU-Mitgliedstaat</p><br>';
		if ($myCustomer["name1"] != null && $myCustomer["name1"] != "")
			$bodyHtml .=  $myCustomer["name1"].',&nbsp;';
		$bodyHtml .=  $myCustomer["k_name"].',&nbsp;';
		if ($myCustomer["address"] != null && $myCustomer["address"] != "")
			$bodyHtml .=  $myCustomer["address"].',&nbsp;';
		if ($myCustomer["post"] != null && $myCustomer["post"] != "")
			$bodyHtml .=  $myCustomer["post"].',&nbsp;';
		if ($myCustomer["city"] != null && $myCustomer["city"] != "")
			$bodyHtml .=  $myCustomer["city"].',&nbsp;';
		if ($myCustomer["country"] != null && $myCustomer["country"] != "")
			$bodyHtml .=  $myCustomer["country"];
		$bodyHtml .= '<hr>';
		$bodyHtml .= '<p style="font-size:12px;">(Name und Anschrift des Abnehmers der innergemeinschaftlichen Lieferung. ggf. E-Mail-Adresse)</p><br>';
		$bodyHtml .= '<p>Hiermit bestätige ich als Abnehmer, dass ich folgenden Gegenstand / dass folgender Gegenstand einer innergemeinschaftlichen Lieferung</p><br>';
		$bodyHtml .=  $order['count_sum'].'<br>';
		$bodyHtml .= '<hr>';
		$bodyHtml .= '<p style="font-size:12px;">(Menge des Gegenstands der Lieferung)</p><br>';
		$bodyHtml .= '<br><br><br><hr>';
		$bodyHtml .= '<p style="font-size:12px;">(handelsüberliche Bezeichnung. beiFahtzeugen zusätzlich die Fahrzeug-Identifikationsnummer)</p><br><br>';
		$bodyHtml .= 'Im<br>';
		$bodyHtml .= '<hr>';
		$bodyHtml .= '<p style="font-size:12px;">(Monat und Jahr des Erhalts des Liefergegenstands im Mitgliedstaat, in den der Liefergegenstand gelang ist, wenn der Liefernde Unternehmer den Liefergegenstand befördert order versendet hat oder wenn der Abnehmer den Liefegegenstand versendet hat)</p><br><br>';
		$bodyHtml .= 'Im&nbsp;'.convertDate($order['date']).'<br>';
		$bodyHtml .= '<hr>';
		$bodyHtml .= '<p style="font-size:12px;">(Monat und Jahr des Endes der beförderung, wenn der Abnehmer den Liefegegenstand selbst befördert hat)</p><br><br>';
		$bodyHtml .= 'in / nach&nbsp;';
		if ($myCustomer["post"] != null && $myCustomer["post"] != "")
			$bodyHtml .=  $myCustomer["post"].',&nbsp;';
		if ($myCustomer["city"] != null && $myCustomer["city"] != "")
			$bodyHtml .=  $myCustomer["city"].',&nbsp;';
		if ($myCustomer["country"] != null && $myCustomer["country"] != "")
			$bodyHtml .=  $myCustomer["country"];
		$bodyHtml .= '<hr>';
		$bodyHtml .= '<p style="font-size:12px;">(Mitgliedstaat und Ort, wohin der Liefergegenstands im Rahmen einer Beförderung order Versendung gelangt ist)</p><br><br><br>';
		$bodyHtml .= 'erhalten habe / gelangt ist.';
		$bodyHtml .= '<br><br><br><br>';
		$bodyHtml .= '<p style="font-size:12px;">(Unterschrift des Abnehmers oder seines Vertretungsberechtigen sowie Name des Unterzeichnenden in Druckschrift)</p><br><br>';
	}
	
	$bodyHtml .= $footer;

    $bodypdf .= $footer;

    $pdf->writeHTML($bodyHtml, true, false, false, false, '');
    //$pdf->Output('example_067.pdf','I');

    unlink("data/".date("Y").$subject.".xml");
    file_put_contents("data/".date("Y").$subject.".xml", $output);

    $pdf->Annotation(0, 0, 0, 0, $subject.".xml", array('Subtype'=>'FileAttachment', 'FS' => "data/".date("Y").$subject.".xml"));

    $pdf->Output($subject.".pdf",'D');

    

    
    //$zip->addFromString($subject.".xml", $output);
    //$zip->addFromString($subject.".pdf", $pdf->Output('example_067.pdf','S'));
    
}
/*if($check_st == true){
    $zip->close();
    header("Content-type: application/zip"); 
    header("Content-Disposition: attachment; filename=$archive_file_name");
    header("Content-length: " . filesize($archive_file_name));
    header("Pragma: no-cache"); 
    header("Expires: 0"); 
    readfile("$archive_file_name");
}*/
?>
<script>myWindow.close();</script>