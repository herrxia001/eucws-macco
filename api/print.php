<?php
include_once '../db_functions.php';
$erg = array();
$token = $_GET['token'];
$thisDb = new myDatabase($root_db);

$sqlQuery = "SELECT * FROM users WHERE printToken='".$token."' AND printToken != '' AND printToken IS NOT NULL";
$userData = $thisDb->dbQuery($sqlQuery);
if(!$userData <= 0){
    $uDb = $userData[0]['u_db']; 

    $thisDb = new myDatabase($uDb);
    $sql = "SELECT p.*, i.comment FROM print p LEFT JOIN inventory i ON (p.code = i.code1) LIMIT 10";
    $printData = $thisDb->dbQuery($sql);
    foreach($printData AS $data){
        $element = array();
        $element['printerName'] = $data['printerName'];
        $element['paperWidth'] = $data['paperWidth'];
        $element['paperHeight'] = $data['paperHeight'];
        $element['codeWidth'] = $data['codeWidth'];
        $element['codeHeight'] = $data['codeHeight'];
        $element['fontSize'] = $data['fontSize'];

        // get comment
        if(is_null($data['comment'])){
            $sql = "SELECT i.comment FROM inv_variant iv, inventory i WHERE iv.i_id = i.i_id AND iv.barcode = '".$data['code']."'";
            $printData_2 = $thisDb->dbQuery($sql);
            foreach($printData_2 AS $data_2){
                $data['comment'] = $data_2['comment'];
            }
        }

        $element['label'] = $data['label']." ".$data['label_2'];
        if(is_null($data['comment'])) $element['label_2'] = "";
        else $element['label_2'] = $data['comment'];
        $element['code'] = $data['code'];
        $element['amount'] = $data['amount'];

        $element['token'] = $token;
        $element['id'] = $data['id'];
        $element['callback'] = $domain."api/delprint.php";
    
        array_push($erg, $element);
    }
}

echo json_encode($erg);?>