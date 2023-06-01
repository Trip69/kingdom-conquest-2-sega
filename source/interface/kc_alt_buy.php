<?php
require('include/kc.php');
require('include/template.php');

$world=$_POST['world'];
$device=$_POST['device'];
$cookie=$_POST['cookie'];

$race=$_POST['race'];
$rarity=$_POST['rarity'];
$price=$_POST['price'];
$dp=$_POST['dp']-50;
$bin=isset($_POST['bin'])?true:false;

$name='';
$name_split=explode(' ',$_POST['name']);
for ($a=0;$a<count($name_split);$a++)
    $name.=ucfirst($name_split[$a]).utils::iif(isset($name_split[$a+1]),' ',null);

$page=0;
$found=false;

try
{
    if ($price > $dp)
        throw new Exception("This acccount only has $dp dp.");
    
    $kc = new kc(null,null,$device,$world,$cookie,null,true);
    if (isset($_POST['auction_id']) && is_numeric($_POST['auction_id']))
    {
        try
        {
            $kc->ah_bid(0,$_POST['auction_id'],$dp);
            echo template::simple(array('echo' => $dp.' bid on auction id '.$_POST['auction_id'],'username' => ''),'template/kc2_ok.html');
        } catch (Exception $ex)
        {
            if ($ex->getCode()==25026)
            {
                //Auction is now buy it now
                $kc->ah_bid(1,$_POST['auction_id'],$dp);
                echo template::simple(array('echo' => $dp.' transfered to auction id '.$_POST['auction_id'],'username' => ''),'template/kc2_ok.html');
            }
            else throw $ex;
        }
        exit;
    }
    
    if ($name=='')
        throw new Exception('No card name entered');
    $auction = $kc->find_auction_item($race,$rarity,$name,$price,true,$bin);
    if (count($auction)>1)
        throw new Exception("There are more than 1 $name listed at $price");
    if ($auction === null)
        throw new Exception('No cards found at that price with no bids and bin:'.$bin);
    $auction=$auction[0];
    
    //$monster = kc::$monsters[$auction->monster->m_id];
    switch ($auction->bid_state)
    {
        case 1:
            throw new Exception('Alreay bid');
        case 5:
            throw new Exception('At max bids');
        case 6:
            throw new Exception('Too expensive');
        case 4:
            $kc->ah_bid(0,$auction->auction_id,$dp);
            $textout['echo']="$dp placed on a $name listed at $price.";
            break;
        case 7:
            $kc->ah_bid(1,$auction->auction_id,$dp);
            $textout['echo']="$dp transfered via a $name listed at $price.";
            break;
    }
    
    $textout['username']='';
    echo template::simple($textout,'template/kc2_ok.html');
}
catch (Exception $e)
{
    $err = $e->getMessage();
    $echo=array();
    $echo['echo'] = "$err<BR />";
    $echo['username'] = '';
    echo template::simple($echo,'template/kc2_err.html');
}

?>
