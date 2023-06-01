<?php
require('include/kc.php');
require('include/template.php');

$db = new db();

$id = $_POST['id'];
$cookie = $_POST['cookie'];
$price = $_POST['price'];
$world = $_POST['world'];
$device=$_POST['device'];
$seller=$_POST['seller'];
$client=kc::$clients[$_POST['device']];

if ($cookie=='' || $id=='') {
    echo template::simple(array('echo'=> "Cookie or ID blank<BR /><BR />",'username' => ''),'template/kc2_err.html');
    exit;
}

if ($seller=='') {
    echo template::simple(array('echo'=> "Please select seller",'username' => ''),'template/kc2_err.html');
    exit;
}


try {
    
    $kc=new kc(null,null,$device,$world,$cookie,null);

    $kc->ah_sell($id,$price,$_POST['seller']);

    $textout['username']='';
    $textout['echo']='Card Put in AH';
    $textout['title'] = 'Sold';
    echo template::simple($textout,'template/kc2_ok.html');
        
} catch (Exception $e) {
    $fix_echo=null;
    if ($e->getCode()==25004) {
        //Currently in a unit
        
        //Make a button that logs on with interface with a remove fuctioon
        $fix_echo=array('world'=>$_POST['world']);
        $fix_echo=template::simple($fix_echo,'template/login_to_fix.html');
    }
    $err = $e->getMessage();
    echo template::simple(array('echo'=> "$err<BR /><BR />$fix_echo<BR />",'username' => ''),'template/kc2_err.html');
}
?>
