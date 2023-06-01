<?php
$a_ids=array(
137251,
137233,
137219,
137216,
137071,
136995,
136858,
136837,
136813,
136742,
136680,
136679,
136627,
136626,
136532,
136494,
136445,
136422,
136356,
136207,
136199,
136112,
136082,
136006,
135988);
require('include/kc.php');
$kc=new kc('Numerous','Numerous1','android',45,null,null);
$kc->logon();
foreach($a_ids as $id)
{
    $kc->ah_cancel($id);
}
$a=1;
?>
