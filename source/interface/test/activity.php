<?php
chdir('..');
require('include/kc.php');
//$kc = new kc('Jimmybean','Jimmybean1','android',45,null,null,true);
//$kc->logon();
$kc=new kc(null,null,'android',45,'C9E2049590D3770492036D7A4000452F.tomcat2');
$memebers=$kc->alliance_memebers(382);
$player=array();
foreach ($memebers->members as $member)
    $player[$member->user_name] = array('rank'=>$member->rank_user,'npc'=>0,'pc'=>0);
$page=1;
$last_page=1;
$finish = (utils::unix_epoch() / 1000) - (60 * 60 * 24 * 31);
$finish=round($finish);
do
{
    $log=$kc->alliance_log(1,$page);
    if ($log->reports[0]->timestamp < $finish)
        break;
    $last_page=$log->page_max;
    foreach ($log->reports as $report)
    {
        $against = $report->battle->defense->user_id == 0 ? 'npc' : 'pc';
        if ($player[$report->battle->attack->user_name][$against] < $report->timestamp)
            $player[$report->battle->attack->user_name][$against] = $report->timestamp;
        $a=1;
    }
} while ($page < $last_page);




$a=1;
?>
