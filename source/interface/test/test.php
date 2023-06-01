<?php
chdir('..');
require('include/kc.php');

$page=utils::get_page('http://spys.ru/en/http-proxy-list/',false,null,'xf4=2');
echo $page;
exit();


$frags=explode('<font class=spy1',$page);
$proxies=array();
$match=null;
$last=null;
foreach($frags as $frag)
{
    $valid = preg_match('/.(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $frag,$match);
    if ($valid && $last !== $match[1]) {
        $last = $match[1];
        $proxies[]=array('host'=>$match[1],'prt'=>8080,'health'=>0);
    }
    //
}
exit();
$kc=new kc('Numerous','Numerous1','android',45);
$kc->logon();
$kc->mail_delete_auction();

exit();

?>
