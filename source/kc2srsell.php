<?php
require('functions.php');
require('interface/include/template.php');

$id = $_POST['id'];
$cookie = $_POST['cookie'];
$price = $_POST['price'];
$world = $_POST['world'];
$device=$_POST['device'];
$client=$clients[$_POST['device']];


if ($cookie=='' || $id=='') {
    echo template("Cookie or ID blank<BR /><BR />",'kc2_err.html','');
    exit;
}

require ('interface/include/db.php');
$db = new db();

try {
    
    $epoch = unixepoch();
    $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/AuctionSell.do";
    $data = "uniq_id=$id&price=$price&_tm_=$epoch";
    senddata($client,$url,$data);
    
    $db->card_sold($id,null,$price);
    $textout['username']='';
    $textout['echo']='Card Put in AH';
    echo template::simple($textout,'interface/template/kc2_ok.html');
        
} catch (Exception $e) {
    $err = $e->getMessage();
    echo template("$err<BR /><BR />",'interface/template/kc2_err.html','');
}
?>
