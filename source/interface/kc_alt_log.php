<?php
require ('include/kc.php');

function sort_alpha($a,$b)
{
    $startA=substr($a[0],0,1)=='<'?3:0;
    $startB=substr($b[0],0,1)=='<'?3:0;
    if (substr($a[0],$startA,1) > substr($b[0],$startB,1))
        return 1;
    elseif (substr($a[0],$startA,1) < substr($b[0],$startB,1))
        return -1;
    return 0;
}
function sort_total($a,$b)
{
    if($a['total']<$b['total'])
        return 1;
    elseif($a['total']>$b['total'])
        return -1;
    elseif ($a['name']>$b['name'])
        return 1;
    elseif ($a['name']<$b['name'])
        return -1;
    else
        return 0;
    
}

$core = utils::load_json('data/core.json');
$mon_arr = utils::make_monster_array($core);
$skill_arr = utils::make_skill_array($core);

function template_simple($args,$template)
{
    $return=file_get_contents($template);
    foreach ($args as $key => $value)
        $return = str_replace('X'.$key.'X',$value,$return);
    return $return;
}
/*
function display_card($card_name,$m_id)
{
    global $mon_arr;
    if ($mon_arr[$m_id]->is_auction==0)
        return false;
    if (strpos($card_name,'Orb') !== false)
        return false;
    if (strpos($card_name,'Arcana') !== false)
        return false;
    if (strpos($card_name,'★') !== false)
        return false;
    if (strpos($card_name,'◆') !== false)
        return false;
    return true;
    
}
*/

$world=isset($_GET['world'])?$_GET['world']:45;
$db=new db();

$cards=$db->list_all_cards($world,true,false,true,false);
$echo_data=array('echo' => '', 'card_log' => '');

$all = $_GET['what']=='all';
$ah = $_GET['what']=='ah';
$srr = $_GET['what']=='srr';
$sr = $_GET['what']=='sr';
$r = $_GET['what']=='r';
$skill = $_GET['what']=='skill';

$race=strpos($_GET['what'],'race_')===0?substr($_GET['what'],5):false;
$non_ah_race=strpos($_GET['what'],'nahr_')===0?(int)substr($_GET['what'],5):false;
$sxp_race=strpos($_GET['what'],'sxp_')===0?substr($_GET['what'],4):false;
if ($non_ah_race!==false) $ah = true;
$echo_data['total']=0;
$totals = array ();
$xp_total=array();
while ($card = mysqli_fetch_array($cards)) {
    //if ($mon_arr[$card['id']]->rarity == 3 && $mon_arr[$card['id']]->is_auction == true) {$a=1;}
    if (!$all)
    {
        if ($ah && $mon_arr[$card['id']]->is_auction == 1 && $sxp_race===false) continue;
        elseif (!$ah && $mon_arr[$card['id']]->is_auction == 0 && $sxp_race===false) continue;
        elseif ($r && $mon_arr[$card['id']]->rarity != 2) continue;
        elseif ($srr && $mon_arr[$card['id']]->rarity < 2) continue;
        elseif ($sr && $mon_arr[$card['id']]->rarity < 3) continue;
        elseif ($race !== false && $mon_arr[$card['id']]->race != $race) continue;
        elseif ($non_ah_race !== false && $mon_arr[$card['id']]->race  != $non_ah_race) continue;
        elseif ($skill && $card['skill_xp'] < 750) continue;
        elseif ($sxp_race !== false && $mon_arr[$card['id']]->race != 9 && $mon_arr[$card['id']]->race  != $sxp_race) continue;
        elseif ($sxp_race !== false) {
            if (isset($xp_total[$card['account_name']]['name']))
                $xp_total[$card['account_name']]['total']+=$card['skill_xp'];
            else
                $xp_total[$card['account_name']]=array('name'=>$card['account_name'],'total'=>(int)$card['skill_xp'],'card'=>0,'tutorial'=>$card['tutorial']==0?'':'Yes');
            if ($mon_arr[$card['id']]->is_auction  && $mon_arr[$card['id']]->rarity < 3)
                $xp_total[$card['account_name']]['card']++;
            continue;
        }
    }
    if ($skill)
        $echo_data['total'] += $card['skill_xp'];
    else
        $echo_data['total']++;
    $tutorial=$card['tutorial']==0?'':'Yes';
    $awake=$card['awake']>0?$card['awake']:'';
    $skill_xp=$card['skill_xp']>100?$card['skill_xp']:'';
    $name='';
    $name = $mon_arr[$card['id']]->name;
    $skill_name = $card['skill_id'] > 0 ? $skill_arr[$card['skill_id']]->name : '';
    if ($mon_arr[$card['id']]->rarity==3) $name = "<b>$name</b>";
    if (isset($totals[$name]))
        $totals[$name]++;
    else
        $totals[$name] = 1;
    $echo_data['echo'].='<tr><td>'. kc::$edition_ids[$card['edition']].' ' . $name .'</td><td>'.$awake.'</td><td>'.$skill_xp.'</td><td>'.$skill_name.'</td><td>'.$card['account_name'].'</td><td>'.$tutorial.'</td></tr>'."\r\n";
}
if ($sxp_race === false)
{
    $echo_data['totals']='';
    $totals_sort=array();
    foreach ($totals as $name => $total)
        $totals_sort[]=array($name,$total);
    usort($totals_sort,'sort_alpha');
    foreach ($totals_sort as $total)
        $echo_data['totals'].="<tr><td style='text-align:right'>".$total[0]."</td><td>".$total[1]."</td></tr>\r\n";
} else {
    usort($xp_total,'sort_total');
    foreach ($xp_total as $total) {
        $echo_data['echo'].='<tr><td>'.$total['card'].'</td><td></td><td>'.$total['total'].'</td><td>'.$total['name'].'</td><td>'.$total['tutorial'].'</td></tr>'."\r\n";
        $echo_data['total']+=$total['total'];
    }
    $echo_data['totals']='<p>Card count is number of non sr auction cards of that race</p>';
}
echo template_simple($echo_data,'template/alt_log.html');
$db->close();
?>
