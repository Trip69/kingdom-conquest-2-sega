<?php
chdir('..');
require('include/kc.php');


class test
{
    function out()
    {
        echo $_SERVER['HTTP_HOST'];
    }
}

$sr_crap=array(10101103,10702103,10001903,10000703,10300403);

$kc = new kc('Jimmybean','Jimmybean1','android',45,null,null,true,true);
$kc->load_core_data();
$ids = $kc->db->get_uids($kc->world,0,10501003);
$filter=array();

foreach ($sr_crap as $crap_m_id)
    foreach ($ids as $id)
        if ($crap_m_id == $id['id'])
            $filter[]=$id;


echo var_dump($filter);
?>
