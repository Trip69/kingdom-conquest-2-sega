<?php
require ('include/kc.php');

$core_data=utils::load_json('data/core.json');
$mon_arr = utils::make_monster_array($core_data);

function template_simple($args,$template)
{
    $return=file_get_contents($template);
    foreach ($args as $key => $value)
        $return = str_replace('X'.$key.'X',$value,$return);
    return $return;
}

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

$world=isset($_GET['world'])?$_GET['world']:45;
$db=new db();

$cards=$db->list_all_cards($world,true,true);
$echo_data=array('echo' => '', 'card_log' => '');
foreach ($cards as $card) {
    if ($mon_arr[$card['id']]->is_auction == 0) continue;
    elseif ($card['account_name']=='LordMacNiell' || $card['account_name']=='Zakarim') //Path and Shar
        continue;
    $echo_data['echo'].='<tr><td>'. kc::$edition_ids[$card['edition']].' ' . $mon_arr[$card['id']]->name.'</td><td>'.$card['awake'].'</td><td>'.$card['account_name'].'</td></tr>'."\r\n";
}
echo template_simple($echo_data,'template/alt_awake.html');

$db->close();
?>
