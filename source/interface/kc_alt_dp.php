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

$db=new db();
if (!isset($_GET['world'])) $_GET['world'] = 45;
$sort='ORDER BY dp DESC';
if (isset($_GET['sort']))
    switch ($_GET['sort'])
    {
        case 'account':
            $sort='ORDER BY name ASC';
            break;
        case 'cp':
            $sort='ORDER BY cp DESC';
            break;
        case 'sxp':
            $sort='ORDER BY total_xp DESC';
            break;
        case 'cards':
            $sort='ORDER BY total_cards DESC';
            break;
    }
$result=$db->list_accounts($_GET['world'],true,$sort);
$echo_data=array('echo'=>'','total_dp'=>0,'total_cp'=>0,'world'=>$_GET['world']);
if ($_GET['world']==45)
{
    $echo_data['rema']='';
    $echo_data['remb']='';
} else {
    $echo_data['rema']='<!---';
    $echo_data['remb']='-->';
}
foreach ($result as $row)
{
    if ($row['name']=='LordMacNiell'||$row['name']=='a66711515'||$row['name']=='b66711515')
        continue;
    $tutorial=$row['tutorial']==1?'Yes':'';
    $echo_data['echo'].='<tr><td>'.$row['name'].'</td><td>'.$row['dp'].'</td><td>'.$row['cp'].'</td><td>'.$row['total_xp'].'</td><td>'.$row['total_cards'].'</td><td>'.$tutorial.'</td></tr>'."\r\n";
    $echo_data['total_cp'] += $row['cp'];
    $echo_data['total_dp'] += $row['dp'];
}
echo template_simple($echo_data,'template/alt_dp.html');

$db->close();
?>