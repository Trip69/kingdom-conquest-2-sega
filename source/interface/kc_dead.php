<?php
require ('include/kc.php');

function template_simple($args,$template)
{
    $return=file_get_contents($template);
    foreach ($args as $key => $value)
        $return = str_replace('X'.$key.'X',$value,$return);
    return $return;
}

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

$world=isset($_GET['world'])?$_GET['world']:45;
$core = utils::load_json('data/core.json');
$mon_arr = utils::make_monster_array($core);
$db=new db();
$status=file_get_contents('cron/skcc.txt');

$totals=array();
$echo_data=array('presents'=>'','cards'=>'','totals'=>'','deck'=>'','account'=>'','status'=>$status);

$presents=$db->skcc_get_presents($world);
$last=null;
$row_count=0;
while ($card = mysqli_fetch_array($presents))
{
    //$name=str_replace('Ã¢Ëœâ€¦','?',$card['monster']);
    $name=$card['monster'];
    if (substr($name,0,2)=='SR')
        $name = substr($name,3);
    $name = trim($name);
    if (isset($totals[$name]))
        $totals[$name]++;
    else
        $totals[$name]=1;

    $tutorial=$card['tutorial']?'Y':'';
    $awake=$card['awake']?$card['awake']:'';
    if ($last==$card['name']) {
        $row_count++;
        $echo_data['presents'].='<tr><td>'. $name .'</td><td>'.$awake.'</td><td>'.$card['skills'].'</td><td>'.$card['name'].'</td></tr>'."\r\n";
    }
    else {
        $echo_data['presents'] = str_replace('XspanX',$row_count,$echo_data['presents']);
        $row_count=1;
        $echo_data['presents'].='<tr><td>'. $name .'</td><td>'.$awake.'</td><td>'.$card['skills'].'</td><td>'.$card['name'].'</td><td rowspan="XspanX">'.$card['cp'].'/'.$card['dp'].'</td><td rowspan="XspanX">'.$tutorial.'</td><td rowspan="XspanX">'.$card['history'].'</td><td rowspan="XspanX">'.$card['active'].'</tr>'."\r\n";
    }
    $last=$card['name'];
}
$echo_data['presents'] = str_replace('XspanX',$row_count,$echo_data['presents']);
unset($presents);

$cards=$db->skcc_get_cards($world);
while ($card = mysqli_fetch_array($cards))
{
    $name=$mon_arr[$card['id']]->name.' '.utils::get_key($card['edition'],kc::$edition_ids);
    $name = trim($name);
    if (isset($totals[$name]))
        $totals[$name]++;
    else
        $totals[$name]=1;

    if ($mon_arr[$card['id']]->rarity < 3) continue;

    $tutorial=$card['tutorial']?'Y':'';
    $awake=$card['awake']?$card['awake']:'';
    if ($last==$card['name']) {
        $row_count++;
        $echo_data['deck'].='<tr><td>'. $name .'</td><td>'.$awake.'</td><td>'.$card['name'].'</td></tr>'."\r\n";
    }
    else {
        $echo_data['deck'] = str_replace('XspanX',$row_count,$echo_data['deck']);
        $row_count=1;
        $echo_data['deck'].='<tr><td>'. $name .'</td><td>'.$awake.'</td><td>'.$card['name'].'</td><td rowspan="XspanX">'.$tutorial.'</td><td rowspan="XspanX">'.$card['history'].'</td><td rowspan="XspanX">'.$card['active'].'</td></tr>'."\r\n";
    }
    $last=$card['name'];
}
$echo_data['deck'] = str_replace('XspanX',$row_count,$echo_data['deck']);
unset($cards);

$accounts=$db->skcc_get_accounts($world);
while ($player = mysqli_fetch_array($accounts))
{
    $name= $player['name'];
    $name = trim($name);
    $tutorial=$player['tutorial']?'Y':'';
    $echo_data['account'].='<tr><td>'. $name .'</td><td>'.$player['dp'].'</td><td>'.$player['active'].'</td></tr>'."\r\n";
}
unset($accounts);

$totals_sort=array();
foreach ($totals as $name => $total)
    $totals_sort[]=array($name,$total);
usort($totals_sort,'sort_alpha');

$grand=0;
foreach ($totals_sort as $total)
{
    $echo_data['totals'].="<tr><td>".$total[0]."</td><td>".$total[1]."</td><td></td></tr>\r\n";
    $grand+=$total[1];
}
$echo_data['total']=$grand;
echo template_simple($echo_data,'template/kc2_dead.html');
?>
