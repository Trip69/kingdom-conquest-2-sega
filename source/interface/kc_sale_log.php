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

$world=isset($_GET['world'])?$_GET['world']:45;
$db=new db();

$sales = $db->list_all_sales($world,null,false,false);
$echo_data=array();
$echo_data['sell_data']='';

$all = $_GET['what']=='all';
$ah = $_GET['what']=='ah';
$srr = $_GET['what']=='srr';
$r = $_GET['what']=='r';
$uc = $_GET['what']=='uc';
$c = $_GET['what']=='c';
$sr = $_GET['what']=='sr';
$skill = $_GET['what']=='skill';
$awake_view = $_GET['what']=='awake';
$non_alt = $_GET['what']=='noalt';

$echo_data['listings']=0;
$echo_data['total_dp']=0;

foreach ($sales as $sale)
{
    if (!$all)
    {
        if ($ah && $mon_arr[$sale['id']]->is_auction == 1) continue;
        elseif (!$ah && $mon_arr[$sale['id']]->is_auction == 0) continue;
        elseif ($srr && $mon_arr[$sale['id']]->rarity < 2) continue;
        elseif ($sr && $mon_arr[$sale['id']]->rarity != 3) continue;
        elseif ($r && $mon_arr[$sale['id']]->rarity != 2) continue;
        elseif ($uc && $mon_arr[$sale['id']]->rarity != 1) continue;
        elseif ($c && $mon_arr[$sale['id']]->rarity != 0) continue;
        elseif ($skill && $sale['skill_xp'] < 500) continue;
        elseif ($awake_view && $sale['awake'] == 0) continue;
        elseif ($non_alt && strpos($sale['seller'],'Alt')) continue;
    }

    $awake=$sale['awake']>=10?'+':null;
    if (substr($sale['id'],-1)==3)
        $card='<b>'.$mon_arr[$sale['id']]->name.$awake.'</b>';
    else
        $card=$mon_arr[$sale['id']]->name.$awake;
    $echo_data['listings']++;
    $seller=$sale['seller'];
    $account=$sale['account_name'];
    $a_id=$sale['auction_id']==0?null:$sale['auction_id'];
    $price=$sale['sale_price']==0?null:$sale['sale_price'];
    $time=$sale['remaining']==''?'BIN':$sale['remaining'];
    $bid=$sale['bid']==0?null:$sale['bid'];
    $echo_data['total_dp']+=$sale['bid'];
    $edition=kc::$edition_ids[$sale['edition']];
    $awake=$sale['awake']>0?$sale['awake']:'';
    $skill_xp=$sale['skill_xp']>100?$sale['skill_xp']:'';
    
    $echo_data['sell_data'].="<tr><td>$edition $card</td><td>$awake</td><td>$skill_xp</td><td>$seller</td><td>$account</td><td>$a_id</td><td>$price</td><td>$bid</td><td>$time</td><tr>\r\n";
}
echo template_simple($echo_data,'template/kc2_sale_log.html');
$db->close();

?>
