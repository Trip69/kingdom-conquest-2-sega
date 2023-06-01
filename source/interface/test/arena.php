<?php
chdir('..');
require('include/kc.php');

function record_result(kc $kc)
{
    if ($kc->duel_data==null)
        return false;
    foreach ($kc->duel_data->duel_monsters as $player)
    {
        if ($player->result == 0)
            continue;
        $kc->db->duel_record($kc->account,$player);
    }
}

//$kc=new kc('b13798528950','hellarse','android',45,'F17D741133186DEED853FB5CD79AFB06.tomcat1');
$kc=new kc('b13798528950','hellarse','android',45);
$kc->logon();

//record
$kc->get_duel_page();
$my_rank = $kc->duel_data->rank;
record_result($kc);

$results=$kc->db->duel_results($kc->account);
$win_ids = array();
$loss_ids = array();
foreach($results as $result)
    if ($result['result']==1) //1:Win,2:Loss,3:Draw
        $win_ids[]=(int)$result['player_id'];
    else
        $loss_ids[]=(int)$result['player_id'];

if (date('H') < 19) //seems php thinks it's in the UK?'
    return false;
        
//fight
$page_views=1;
while ($kc->duel_data->remain > 0)
{
    $best_target=array('id'=>null,'rank'=>0);
    $use_best=true;
    foreach ($kc->duel_data->duel_monsters as $player)
    {
        if ($player->result > 0 ||
            (!in_array($player->user_id,$win_ids) && $page_views < 20) ||
            in_array($player->user_id,$loss_ids) ||
            $player->rank != 2 ||
            $player->commander_id == 20002802) //Amazon
                continue;
        if (in_array($player->user_id,$win_ids)) {
            echo 'Battle previously won against<br />';
            $kc->duel($player->user_id);
            $use_best=false;
            break;
        }
        elseif ($player->rank_num > $best_target['rank'])
            $best_target=array('id'=>$player->user_id,'rank'=>$player->rank_num);
    }
    if ($use_best && $best_target['id'] != null)
    {
        echo 'Figthing best target<br />';
        $kc->duel($best_target['id']);
    }
    $kc->get_duel_page();
    record_result($kc);
    $page_views++;
}
?>
