<?php
require('functions.php');
$race=$_POST['race'];
$rarity=$_POST['rarity'];
$price=$_POST['price'];
$cookie=$_POST['cookie'];
$dp=$_POST['dp'];
$name=ucfirst($_POST['name']);
if ($name='')
    $serchtype=-1;
else
    $serchtype=1;
$world=$_POST['world'];
$device=$_POST['device'];
$client=$clients[$_POST['device']];

$page=0;
$found=false;

try {
    while (!$found) {
        $found++;        
        $epoch = unixepoch();
        $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/AuctionMonsterSellList.do";
        $data = "sort_key1=1&sort_type1=2&filter_race=$race&filter_rarity=$rarity&page=$page&search_type=$serchtype&keyword=$name&_tm_=$epoch";
        $auction = senddata($client,$url,$data);
        foreach ($auction->sell_monsters as $item) {
            if ($item->start_price == $price) {
                $found=true;
                global $auctionitem;
                $auctionitem = $item;
            }
        }
        if ($auction->page_max == $page)
            break;
    }
    
    if (!$found) {
        echo template("Auction Item Not Found<BR /><BR />",'kc2_err.html','');
        exit;
    }
    
    $url='';
    switch ($auctionitem->bid_state) {
        case 1:
            echo template("Already Bid<BR />",'kc2_err.html','');
            break;
        case 5:
            echo template("At max bids<BR />",'kc2_err.html','');
            break;
        case 6:
            echo template("Too expensive<BR />",'kc2_err.html','');
            break;
        case 4:
            $url='AuctionBid';
            break;
        case 7:
            $url='AuctionImediateKnockDown';
            break;
    }
    
    if ($url!='') {
        $epoch = unixepoch();
        $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/$url.do";
        $data = "auction_id=".$auctionitem->auction_id."&bid_price=$dp&_tm_=$epoch";
        senddata($client,$url,$data);
        $textout[0]='Success';
        $textout[1]='Bid Placed';
        echo template_better($textout,'kc2.html');
    }
    
} catch (Exception $e) {
    $err = $e->getMessage();
    echo template("$err<BR /><BR />",'kc2_err.html','');
}

?>
