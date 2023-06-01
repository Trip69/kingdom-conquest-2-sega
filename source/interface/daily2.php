<?php
//Send text to user but carry on with the script
/*
ob_end_clean();
header("Connection: close");
ignore_user_abort(true); // just to be safe
ob_start();
echo('<h1>Alt logon started.</h1><p><a href="cron_complete.txt">Check progress</a></p>');
$size = ob_get_length();
header("Content-Length: $size");
ob_end_flush(); // Strange behaviour, will not work
flush(); // Unless both are called !
*/
require('include/kc.php');

//This is run by cron 10pm and 9am UTC

function the_time()
{
    return date('l jS \of F Y h:i:s A');
}

$auctions_bid_on=array();
function process_account(kc $kc, $auction)
{
        //file_put_contents('cron_complete.txt',the_time(). ' ' . $username . ' Duel' . "\r\n",FILE_APPEND);
        file_put_contents('cron_complete.txt',the_time(). ' ' . $kc->username . "\r\n",FILE_APPEND);

        $kc->logon();
        //$kc->duel_random();
        //$kc->get_presents_named(200,array('Compensation Gift','Duel Ticket','Soul','Orb Ticket','Gold'));
        //$kc->draw_login_rewards();
		$kc->get_units(0);

		//Bank accounts sell
		if($auction == 'sell')
		{
			$count_auctions = 0;
			foreach ($kc->unit_data->monsters as $card)
			{
				if ($card->uniq_data->state==4)
					$count_auctions++;
			}

			if ($count_auctions < 10)
				$uids = $kc->db->get_uids($kc->world,0,0);

			for ($i=$count_auctions; $i<10; $i++ )
			{
				try {
					$kc->ah_sell($uids[$i]['u_id'],10,$kc->username);
					file_put_contents('cron_complete.txt',$kc->username . "Auctioned item\r\n",FILE_APPEND);
				} catch (Exception $ex) {
					if($ex->getCode() == 25023) //At ten auctions
						{break;}
					elseif ($ex->getCode() == 25008) //Already in ah
						$kc->db->set_card_status($uids[$i]['u_id'],4);
					else throw $ex;
				}
			}

		}
		//Alt accounts bid
		else if ($auction == 'buy')
		{
			$bid_count = $kc->get_ah_current_bids();
			if ($bid_count ==0)
			{
				$buy_from = substr($kc->username, 0, -1);

				$cards = $kc->db->list_all_sales($kc->world." AND seller='".$buy_from."' AND bid=0");

				foreach ($cards as $card)
				{
					$kc->ah_bid(0,$card['a_id'],$kc->dp);
					file_put_contents('cron_complete.txt',$kc->username . "Bid on item\r\n",FILE_APPEND);
					exit;
				}
				//print_r($cards);
			}
		}


}

function user_canceled()
{
    if (!file_exists('data/cron.txt'))
    {
        file_put_contents('cron_complete.txt',the_time(). " canceled\r\n",FILE_APPEND);
        return true;
    }
    return false;
}

$alts=array(

    0=>array('username'=>'Slidell','password'=>'Slidell123','world'=>43,'device'=>'android'),
//    1=>array('username'=>'Numerous','password'=>'Numerous1','world'=>45,'device'=>'android'),
//    2=>array('username'=>'Comwine','password'=>'Comwine1','world'=>45,'device'=>'apple'),
//    3=>array('username'=>'IgnoreHim','password'=>'IgnoreHim1','world'=>45,'device'=>'android'),
//    4=>array('username'=>'OkSmall','password'=>'OkSmall1','world'=>45,'device'=>'android'),

//    5=>array('username'=>'TimeWill','password'=>'TimeWill1','world'=>45,'device'=>'android'),
//    6=>array('username'=>'ArghDam','password'=>'ArghDam1','world'=>45,'device'=>'android'),

//    7=>array('username'=>'Tomfoolery','password'=>'Tomfoolery1!','world'=>45,'device'=>'apple'),
//    8=>array('username'=>'TomdickHary','password'=>'Tomdick!1','world'=>45,'device'=>'apple'),
//    9=>array('username'=>'Joesmith','password'=>'Joesmith*2','world'=>45,'device'=>'android'),
//    10=>array('username'=>'IgnoreMe','password'=>'IgnoreMe1','world'=>45,'device'=>'android'),
//    11=>array('username'=>'WorldOne','password'=>'WorldOne1','world'=>44,'device'=>'android'),
//    12=>array('username'=>'Carol03','password'=>'Dade2254','world'=>44,'device'=>'android'),
//    13=>array('username'=>'SameSame','password'=>'SameSame1','world'=>44,'device'=>'android'),
//    14=>array('username'=>'Youtube1','password'=>'Youtube2','world'=>44,'device'=>'android')
);




//$kc = new kc('Slidell23','Slidell123','android',43,null,null,true);
//process_account($kc,'buy');
//exit();


try {
    unlink('cron_complete.txt');
    file_put_contents('data/cron.txt','true');
    file_put_contents('cron_complete.txt',"-------------------------------------\r\n".the_time()." Daily Started\r\n",FILE_APPEND);



    //alts
    foreach ($alts as $alt)
    {
        //if ($alt['username'] == 'Jimmybean' || $alt['username'] == 'Numerous' || $alt['username'] == 'Comwine') continue;
        file_put_contents('cron_complete.txt',the_time(). ' ' . $alt['username']." batch\r\n",FILE_APPEND);
        for ($a=1;$a<51;$a++)
        {
            if (user_canceled()) exit();

			if ($a > 0 && $a < 10) {
				$auction = "sell";
			} else {
				$auction = "buy";
			}

            $username = $alt['username'] . $a;
            $continue=false;
            $break=false;

            switch ($username)
            {
                case 'Slidell18':
                    $continue=true;break;
                case 'TomdickHary10':
                case 'Tomfoolery22':
                    $break=true;break;
            }
            //file_put_contents('cron_complete.txt',the_time().' '.$username." Started\r\n",FILE_APPEND);
            if ($continue) continue;
            if ($break) break;

            $kc = new kc($username,$alt['password'],$alt['device'],$alt['world'],null,null,true);
            try
            {
                process_account($kc,$auction);
            }
            catch (Exception $e)
            {
                $error=the_time() ." Error ". $username . "\r\n" . $e->getMessage()."\r\n";
                file_put_contents('cron_complete.txt',$error,FILE_APPEND);
            }
            /*
            if ($a>2)
            {
                echo 'Debug Complete.';
                exit();
            }
            */
            unset($kc);
        }
    }

    file_put_contents('cron_complete.txt',the_time(). " Daily Complete\r\n",FILE_APPEND);
}
catch (Exception $e)
{
    $error=the_time() ."\rFaital Error: ". $username . "\r\n" . $e->getMessage()."\r\n";
    file_put_contents('cron_complete.txt',$error,FILE_APPEND);
}

//sleep(30);
?>
