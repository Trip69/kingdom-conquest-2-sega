<?php
require('include/kc.php');

$core_data=utils::load_json('data/core.json');
$mon_arr = utils::make_monster_array($core_data);

function template_simple($args,$template)
{
    $return=file_get_contents($template);
    foreach ($args as $key => $value)
        $return = str_replace('X'.$key.'X',$value,$return);
    return $return;
}


$by=isset($_POST['by'])?$_POST['by']:null;
$world=isset($_POST['world'])?$_POST['world']:null;
$monster=isset($_POST['monster'])?$_POST['monster']:null;
$rarity=isset($_POST['rarity'])?$_POST['rarity']:null;
$edition=isset($_POST['edition'])?$_POST['edition']:0;
$awake=isset($_POST['awake'])?$_POST['awake']:0;
$price=isset($_POST['price'])?$_POST['price']:10;

$db = new db();
$echo_data=array('orders'=>'','echo'=>'');

if (!($by == null || $monster==null || !is_numeric($awake) || !is_numeric($price)))
    try
    {
        $m_id=null;
        foreach ($mon_arr as $key => $mon)
            if ($mon->name == $monster)
                $m_id = $key;
        if ($m_id==null) throw new Exception('Monster not known, Check spelling / case');
        $db_ids=$db->get_uids($world,0,$m_id,false,$edition,$awake,true,false,$price);
        if (count($db_ids)==0)
            throw new Exception("No $monster, awake $awake found.");
        $db->place_sell_order($by,$world,$m_id,$edition,$awake,$price);
        $echo_data['echo']='<h1>Order Placed</h1>';
    }
    catch (Exception $e)
    {
        $echo_data['echo'] = $e->getMessage();
        $echo_data['username'] = '';
        echo template_simple($echo_data,'template/kc2_err.html');
        exit();
    }

$orders=$db->get_sell_orders();
if ($orders!==null)
    foreach ($orders as $order)
    {
        $by=$order['by'];
        $name=$mon_arr[$order['m_id']]->name;
        $price=$order['price'];
        $world=$order['world'];
        $status=$order['status'];
        $echo_data['orders'].="<tr><td>$by</td><td>$name</td><td>$price</td><td>$world</td><td>$status</td></tr>\r\n";
    }
        
if (count($orders)==0)
    $echo_data['orders']='<tr><td colspan="6" id="center">No Sell Orders</td></tr>';
echo template_simple($echo_data,'template/sell_order.html');
?>
