<?php
require('include/kc.php');
require('include/template.php');

$id = $_POST['id'];
$cookie = $_POST['cookie'];
$price = $_POST['price'];
$world = $_POST['world'];
$device=$_POST['device'];
$client=$clients[$_POST['device']];

if ($cookie=='' || $id=='') {
    echo template::simple(array('echo'=> "Cookie or ID blank<BR /><BR />",'username' => ''),'template/kc2_err.html');
    exit;
}

try {
    
    $kc=new kc(null,null,$device,$world,$cookie,null,true);
    
    $kc->ah_sell($id,$price);
    
    $db->card_sold($id,null,$price,$world);
    $textout['username']='';
    $textout['echo']='Card Put in AH';
    echo template::simple($textout,'interface/template/kc2_ok.html');
        
} catch (Exception $e) {
    $err = $e->getMessage();
    echo template("$err<BR /><BR />",'interface/template/kc2_err.html','');
}
?>
