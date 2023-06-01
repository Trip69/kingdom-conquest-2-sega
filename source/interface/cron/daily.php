<?php
/*
//Send text to user but carry on with the script
ob_end_clean();
header("Connection: close");
ignore_user_abort(true); // just to be safe
ob_start();
echo('<h1>Alt logon started.</h1><p><a href="cron/cron_complete.txt">Check progress</a></p>');
$size = ob_get_length();
header("Content-Length: $size");
ob_end_flush(); // Strange behaviour, will not work
flush(); // Unless both are called !
*/
chdir('..');
require('include/kc.php');
require('include/tutorial.php');

const daily_version = 'v1.2.7';

//This is run by cron 9:10am and 9:10pm GMT


class auto_account
{
    /*
    10101103:Cockatrice
    10702103:High Salamander
    10001903:Cannon Beetle
    10000703:Queen Ant
    10300403:Goblin Lord
    10501003 : liche
    10301803:Lizard Trooper
    10703903:mab
    */
    public static $sr_crap=array(10101103,10702103,10001903,10000703,10300403,10501003,10301803,10703903);

    public static $buys = array(
        0=>array('type'=>'name','search'=>'Hippocampus','price'=>1666,'account'=>'a66711515'), //Mirage
        1=>array('type'=>'name','search'=>'Minotaur','price'=>666,'account'=>'a66711515','rarity'=>'uncommon'), //Stal
        //2=>array('type'=>'name','search'=>'Armored Skeleton','price'=>366,'account'=>'a66711515'), //Ageis
        2=>array('type'=>'name','search'=>'Bandersnatch','price'=>666,'account'=>'a66711515'), //
        10=>array('type'=>'name','search'=>'Terror','price'=>3666,'account'=>'b66711515'), //Dual Savage Claw
        11=>array('type'=>'name','search'=>'Cuelebre','price'=>1166,'account'=>'b66711515'), // massive strikes
        12=>array('type'=>'name','search'=>'Centaurus Centurion','price'=>3666,'account'=>'b66711515'), //Valiant Spirit
        13=>array('type'=>'name','search'=>'Vampire Servant','price'=>666,'account'=>'b66711515'), //Vital Drain
        20=>array('type'=>'name','search'=>'Unseelie','price'=>3166,'account'=>'a13798528950'), //Mind Cross
        21=>array('type'=>'name','search'=>'Nyuni','price'=>66,'account'=>'a13798528950'),
        //21=>array('type'=>'name','search'=>'Incubus','price'=>3666,'account'=>'a13798528950'), //Lewd Nightmare
        //22=>array('type'=>'name','search'=>'Scolopendra','price'=>666,'account'=>'a13798528950'), //Speed Spirt
        30=>array('type'=>'name','search'=>'Banshee','price'=>2666,'account'=>'121212'), //Mind Cross
        31=>array('type'=>'name','search'=>'Archdemon','price'=>1666,'account'=>'121212'), //Vig Domain
        40=>array('type'=>'name','search'=>'Troll Chemist','price'=>3266,'account'=>'k025937'),
        41=>array('type'=>'name','search'=>'Deathscythe','price'=>366,'account'=>'k025937'), //Bust Range
        42=>array('type'=>'name','search'=>'Yeti','price'=>666,'account'=>'k025937'),
        43=>array('type'=>'name','search'=>'Humbaba','price'=>866,'account'=>'k025937'),
        //41=>array('type'=>'name','search'=>'Armored Skeleton','price'=>766,'account'=>'k025937'), //Ageis
        60=>array('type'=>'name','search'=>'Alraune','price'=>2966,'account'=>'a98421771'), //Screaming hold
        61=>array('type'=>'name','search'=>'Yowie','price'=>1666,'account'=>'a98421771'), //Hypnotic Hold
        62=>array('type'=>'name','search'=>'Fomoire Highlander','price'=>3666,'account'=>'a98421771'), //Drain imbue
        70=>array('type'=>'name','search'=>'Stoorworm','price'=>4110,'account'=>'b98421771'), //Fast growth
        71=>array('type'=>'name','search'=>'Blood Mummy','price'=>2666,'account'=>'b98421771'), //C Spike
        80=>array('type'=>'name','search'=>'Scitalis','price'=>2666,'account'=>'Numerous'), //Massive Hold
        81=>array('type'=>'name','search'=>'Ammit','price'=>3666,'account'=>'Numerous'), //Soul eater
        82=>array('type'=>'name','search'=>'Orc Ballista','price'=>766,'account'=>'Numerous'), //Brute Snipe
        90=>array('type'=>'name','search'=>'Lamia','price'=>2666,'account'=>'Jimmybean'), //Stun Resit
        91=>array('type'=>'name','search'=>'Fomoire Raider','price'=>1666,'account'=>'Jimmybean'), //Abush Tom..
        93=>array('type'=>'name','search'=>'Skemaend','price'=>2666,'account'=>'Jimmybean'), //Valiant Cross.
        94=>array('type'=>'name','search'=>'Paladin Lizardman','price'=>3666,'account'=>'Jimmybean'), //Chive Stance
        100=>array('type'=>'name','search'=>'Hydra Larva','price'=>3666,'account'=>'OkSmall'), //Blocking Jammer
        //101=>array('type'=>'name','search'=>'Succubus','price'=>2666,'account'=>'OkSmall'), //Immoral Nightmare
        110=>array('type'=>'name','search'=>'Mushussu','price'=>1666,'account'=>'IgnoreHim','rarity'=>'rare'), //Mute breath
        111=>array('type'=>'name','search'=>'Axe Beak','price'=>1666,'account'=>'IgnoreHim'), //Chop Attacks
        112=>array('type'=>'name','search'=>'Dwarf Axeman','price'=>666,'account'=>'IgnoreHim') //Protect Spirt
    );
    public static $debug = false;
    private $kc;
    
    public static $auctions_bid_on=array();
    
    private function is_feeder()
    {
        if ($this->kc == null ||
            auto_account::is_bank($this->kc->username) ||
            strpos($this->kc->username,'Jimmybean')===0 ||
            account::is_batgig_user($this->kc->username))
            return false;
        return true;
    }
    
    public static function output($text)
    {
        file_put_contents('cron/cron_complete.txt',date('h:i A') . " $text\r\n",FILE_APPEND);
    }
    
    //return unix epoch in SECONDS for last logon
    public function get_last_logon()
    {
        $row = $this->kc->db->get_account($this->kc->account);
        if ($row) return $row['timestamp'];
        return null;
    }
    
    public function __construct(kc $kc)
    {
        $this->kc = $kc;
        $this->kc->account->bank = auto_account::is_bank($kc->username);
    }
    
    public static function is_bank($username)
    {
        global $banks;
        foreach ($banks as $bank)
            if ($bank['username'] == $username)
                return true;
        
        return account::is_batgig_user($username);
    }
    
    public function do_all($override=false)
    {
        $last_logon = $this->get_last_logon();
        if (!$override && $last_logon > time() - (3 * 60 * 60)) //Skip if less than 3 hours since last logon
        {
            auto_account::output($this->kc->username . " skipped.");
            return false;
        }
        $this->logon();

        //Test Area
        //$this->slow_release();
        //$this->tutorial();
        //$this->transfer();
        //$this->kc->find_auctions('balls');
        //$this->transfer();return;
        //$this->kc->synth_commons();
        //$this->buy();
        //$this->sell();
        //$this->kc->draw_login_rewards();
        //$this->kc->collect_blue_crystals();
        //Test Area END

        if ($this->kc->duel_random()===false)
            auto_account::output($this->kc->username . " can't duel");
        $this->kc->complete_quests(false);
        $this->get_presents();
        $this->kc->draw_login_rewards();
        $this->draw_cp_pack();
        if ($this->is_feeder())
            $this->kc->synth_commons(true);
            
        
        if (!isset($this->kc->unit_data->monsters))
            $this->kc->get_units($this->kc->homebase_id);
        if (count($this->kc->unit_data->monsters)<50)
            auto_account::output($this->kc->username . " < 50 m");
        elseif (count($this->kc->unit_data->monsters)>140)
            auto_account::output($this->kc->username . " > 140 m");
        if (count($this->kc->unit_data->monsters)>120)
        {
            $this->kc->synth_commons();
            if (count($this->kc->unit_data->monsters)>=140 && !$this->kc->is_tutorial) //Can't awaken during tutorial
                for ($a=0;$a<9;$a++)
                {
                    $monsters = utils::return_monsters(kc::$monster_obj,$this->kc->my_monsters_obj,$a,'rare','inpool',100,1,null,null,true,-1);
                    $synth_ids=array();
                    foreach ($monsters as $monster)
                        if (!in_array($monster->m_id,$synth_ids))
                            $synth_ids[]=$monster->m_id;
                    foreach($synth_ids as $id)
                        $this->kc->awaken_card($id);
                    if(count($this->kc->unit_data->monsters)<=100) break;
                }
        }

        //break here, don't automate any world other than 45 (S4W1)
        if ($this->kc->world<>45) return null;

        //Updates all rare auctions with data from server
        //if ($this->kc->username=='Jimmybean1') $rare_auctions=$this->kc->find_auctions(null,null,150,999999,'rare','finalbid','up');
            //TODO:remove rare auctions that are not in $rare_auctions
        $this->kc->mail_delete_auction();
        if ($this->kc->account->bank)
            $this->buy();
        elseif (strpos($this->kc->username,'Jimmybean')===false)
            $this->transfer();
        $this->sell_order();
        $this->sell();
        $this->tutorial();
            
        //last cause sometimes errors
        $this->kc->collect_blue_crystals();
        unset($this->kc);
    }
    
    public function logon()
    {
        auto_account::output($this->kc->username);
        $this->kc->logon();
    }
    
    public function get_presents()
    {
        $this->kc->get_presents_named(-1,array('Compensation','Duel','Soul','Orb','Gold','Listener','Beginner','Transfer Reward','Bravery Reward','Welcome','Gold Ticket','Crystals','Premium R', 'MWC2014','Ashes'));
        if (count($this->kc->unit_data->monsters)<40)
            $this->kc->get_present_monsters(-1,true);
    }
    
    public function draw_cp_pack()
    {
        if ($this->kc->cp < 100 || auto_account::is_bank($this->kc->username))
            return false;
        if ($this->kc->shop_data==null)
            $this->kc->get_shop();
        $pack_id=501900; //Good spirit, 584700 for re act dragon. Failed :(, 501900 Grenade pack
        foreach ($this->kc->shop_data->packs as $pack)
            if ($pack->id == $pack_id && strpos($pack->message,'discount'))
                $this->kc->draw_pack($pack->id,1,1);
    }
    
    public static $max_sells = 2500;    
    public function sell()
    {
        //Don't sell if in tutorial
        if ($this->kc->is_tutorial)
        {
            auto_account::output($this->kc->username . " tutorial");
            return false;
        }
        if ($this->kc->world<>45) return false;
        if (!(auto_account::is_bank($this->kc->username) || strpos($this->kc->username,'Jimmybean')===0) && $this->kc->db->get_auction_sale_count($this->kc->world) > auto_account::$max_sells)
            return false;
        
        $this->kc->get_auction_sell_data();
        if ($this->kc->current_sales==10) return 10;
        
        $this->slow_release();
        
        $monsters = utils::return_monsters(kc::$monster_obj,$this->kc->unit_data->monsters,null,null,'inpool',null,null,0,null,true,0,null,null,3);
        //$test=array();
        $remove_common = !(auto_account::is_bank($this->kc->username) || strpos($this->kc->username,'Jimmybean')===0);
        foreach($monsters as $key => $monster)
        {
            //remove non sr crap
            if (kc::$monster_obj[$monster->m_id]->rarity == 3 && !in_array($monster->m_id,auto_account::$sr_crap)) { //kc::$monster_obj[$monster->m_id]->rarity == 0 || 
                unset($monsters[$key]);
                continue;
            }
            if ($remove_common && kc::$monster_obj[$monster->m_id]->rarity == 0)
            {
                unset($monsters[$key]);
                continue;
            }
            //remove buy order
            foreach(auto_account::$buys as $buy_order)
                if ($buy_order['account']==$this->kc->username && $buy_order['search'] == kc::$monster_obj[$monster->m_id]->name)
                    unset($monsters[$key]);
            //$test[]=kc::$monster_obj[$monster->m_id]->name;
        }
            
        //sell uc edition cards
        if (count($monsters)<10) {
            $uc_edition = utils::return_monsters(kc::$monster_obj,$this->kc->unit_data->monsters,null,'uncommon','inpool',null,null,0,null,true,-1,null,null,3);
            $monsters = array_merge($monsters,$uc_edition);
        }
            
        //sell r edition if abundant
        if (count($monsters)<10) {
            $r_edition = utils::return_monsters(kc::$monster_obj,$this->kc->unit_data->monsters,null,'rare','inpool',null,null,0,null,true,-1,null,null,3);
            if (count($r_edition)>10)
                $monsters = array_merge($monsters,$r_edition);
        }
        
        //remove first of each higher tier monsters to keep for synthing to
        $upper=array(5=>0,6=>0,7=>0,8=>0);
        foreach ($monsters as $key => $monster) {
            if (kc::$monster_obj[$monster->m_id]->race > 4) {
                $upper[kc::$monster_obj[$monster->m_id]->race]++;
                if ($upper[kc::$monster_obj[$monster->m_id]->race] == 1)
                    unset($monsters[$key]);
            }
        }
        
        //add back common
        if (count($monsters)<10 && $remove_common) {
            $commons = utils::return_monsters(kc::$monster_obj,$this->kc->unit_data->monsters,null,'common','inpool',null,null,0,null,true,-1,null,null,3);
            $monsters = array_merge($monsters,$commons);
        }
        
        if (count($monsters)<10 && $this->kc->crystals >= 2000)
        {
            $this->kc->draw_pack(301000,1,1);
            $this->kc->get_units($this->kc->homebase_id);
            $commons = utils::return_monsters(kc::$monster_obj,$this->kc->unit_data->monsters,null,'common','inpool',null,null,0,null,true,-1,null,null,3);
            $monsters = array_merge($monsters,$commons);
        }
        
        utils::remove_dups($monsters);
        
        if (count($monsters)==0)
        {
            auto_account::output($this->kc->username . " no cards");
            $this->kc->get_present_monsters(-1,true);
            return 0;
        }
        shuffle($monsters);
        $sr_sell=0;
        $r_sell=0;
        $uc_sell=0;
        $c_sell=0;
        $sell_max = auto_account::is_bank($this->kc->username) ? 9 : 10;
        for ($a=0;$this->kc->current_sales < $sell_max && $a < count($monsters) && !$this->kc->is_tutorial;$a++)
        {
            try
            {
                $price = kc::$monster_obj[$monsters[$a]->m_id]->race > 4 ? 200 : 0;
                switch (substr($monsters[$a]->m_id,-1))
                {
                    case 0:
                        $this->kc->ah_sell($monsters[$a]->uniq_data->u_id,10,'Trip Alt',$this->kc->username);
                        $c_sell++;
                        break;
                    case 1:
                        $price+=rand(0,5)*15+10;
                        $skill_level = $monsters[$a]->skills[0]->lv + $monsters[$a]->skills[1]->lv + $monsters[$a]->skills[2]->lv;
                        $skill_price = ($skill_level - 1) * 50;
                        if ($skill_price > 0) $price = $price + $skill_price;
                        $this->kc->ah_sell($monsters[$a]->uniq_data->u_id,$price,'Trip Alt',$this->kc->username);
                        $uc_sell++;
                        break;
                    case 2:
                        $price+=rand(0,10)*15+100;
                        $skill_level = $monsters[$a]->skills[0]->lv + $monsters[$a]->skills[1]->lv + $monsters[$a]->skills[2]->lv;
                        $skill_price = ($skill_level - 1) * 100;
                        if ($skill_price > 0) $price = $price + $skill_price;
                        $this->kc->ah_sell($monsters[$a]->uniq_data->u_id,$price,'Trip Alt',$this->kc->username);
                        $r_sell++;
                        break;
                    case 3:
                        $price+=rand(0,20)*100+2500;
                        $this->kc->ah_sell($monsters[$a]->uniq_data->u_id,$price,'Trip Alt',$this->kc->username);
                        $sr_sell++;
                        break;
                }
            } catch (Exception $ex)
            {
                switch ($ex->getCode())
                {
                    case 25023://At ten auctions
                        $this->kc->current_sales=10;
                        break;
                    case 25008://Already in ah
                        $this->kc->db->set_card_status($monsters[$a]->uniq_data->u_id,4,$this->kc->world);
                        break;
                    case 70005://in tutorial
                        $this->kc->is_tutorial=true;
                        break;
                    default:
                        $this->kc->db->set_card_status($monsters[$a]->uniq_data->u_id,9,$this->kc->world);
                        break;
                }
            }
        }
        auto_account::output($this->kc->username . " sr:$sr_sell r:$r_sell uc:$uc_sell c:$c_sell");
        return $this->kc->current_sales;
    }
    
    private static $slow_release=array(
                                       array('id'=>10700903,'price'=>17500,'max'=>7,'name'=>'Sylph'),
                                       array('id'=>10502803,'price'=>4000,'max'=>5,'name'=>'Draug'),
                                       array('id'=>10001003,'price'=>6000,'max'=>5,'name'=>'Oistros'),
                                       array('id'=>10102003,'price'=>7500,'max'=>5,'name'=>'Najash'),
                                       array('id'=>10403103,'price'=>9000,'max'=>5,'name'=>'Troll General'),
                                       array('id'=>10203303,'price'=>8500,'max'=>5,'name'=>'Thunderbird'),
                                       array('id'=>10500303,'price'=>8000,'max'=>5,'name'=>'Dragon Zombie'),
                                       array('id'=>10503403,'price'=>8000,'max'=>5,'name'=>'Gwyn ap Nudd'),
                                       array('id'=>10202503,'price'=>5000,'max'=>5,'name'=>'Manticore'),
                                       array('id'=>10303003,'price'=>5000,'max'=>5,'name'=>'Orc Chariot'),
                                       array('id'=>10101203,'price'=>4500,'max'=>5,'name'=>'War Elephant')
                                       );
    public static $numerous_sr = array();
    public function slow_release()
    {
        if ($this->kc->is_tutorial) return false;
        $sold=0;
        foreach(auto_account::$slow_release as $slow)
        {
            if (!in_array($slow['id'],auto_account::$numerous_sr)) continue;
            $monsters = utils::return_monsters(kc::$monster_obj,$this->kc->my_monsters_obj,null,null,'inpool',null,null,null,$slow['id'],true,-1,null,null,4);
            if (count($monsters)==0) continue;
            $auctions = $this->kc->find_auctions(kc::$monster_obj[$slow['id']]->name,null,999999,20,kc::$monster_obj[$slow['id']]->rarity);
            if (count($auctions)>=$slow['max']) continue;
            
            $price=($monsters[0]->uniq_data->awake_exp + 1) * $slow['price'];
            $price+=isset($monsters[0]->edition)?round($slow['price']*0.5):0;
            $price+=rand(0,50)*100;
            $price+=(count($auctions)-20)*-(round($slow['price']/100));
            $this->kc->ah_sell($monsters[0]->uniq_data->u_id,$price,'Slow Rls',$this->kc->username);
            auto_account::output($this->kc->username . ' ' . kc::$monster_obj[$slow['id']]->name . ' sold');
            $sold++;
            if ($this->kc->current_sales==10) return $sold;
        }
        return $sold;
    }

    public function sell_order()
    {
        if ($this->kc->is_tutorial) return false;
        $orders = $this->kc->db->get_sell_orders(false);
        if ($orders!==null)
            foreach ($orders as $order)
            {
                $mons = utils::return_monsters(kc::$monster_obj,$this->kc->my_monsters_obj,null,null,0,null,null,$order['awake'],$order['m_id'],true,$order['edition']);
                if (count($mons)==0) continue;
                try {$this->kc->ah_sell($mons[0]->uniq_data->u_id,$order['price'],$order['by'],$this->kc->username);}
                catch (Exception $e)
                {return false;}
                $this->kc->get_auction_sell_data(true);
                $auction_id=null;
                foreach ($this->kc->auction_sell_data->monsters as $auction_item)
                    if ($auction_item->monster->uniq_data->state==4)
                        if ($auction_item->monster->uniq_data->u_id == $mons[0]->uniq_data->u_id)
                            $auction_id = $auction_item->auction_id;
                if ($auction_id==null) throw new Exception('check sell_order code');
                $this->kc->db->update_sell_order($order['id'],$mons[0]->uniq_data->u_id,"Auction $auction_id");
            }
    }
    
    public function buy()
    {
        if ($this->kc->world<>45)
            return false;

        if (!auto_account::is_bank($this->kc->username))
            return false;

        $my_buy_orders=array();
        foreach (auto_account::$buys as $buy)
            if ($buy['account'] == $this->kc->username || $buy['account'] == '*')
                $my_buy_orders[]=$buy;
        if (count($my_buy_orders)==0)
            return false;
        
        $bids_made=$this->kc->get_ah_current_bids();
        $dp=$this->kc->dp;
        auto_account::output($this->kc->username . " $bids_made bids $dp");
        foreach($this->kc->auction_bid_data->bid_monsters as $bid)
            if (!in_array($bid->auction_id,auto_account::$auctions_bid_on))
                auto_account::$auctions_bid_on[]=$bid->auction_id;
            
        foreach ($my_buy_orders as $buy)
        {
            $rarity=isset($buy['rarity'])?$buy['rarity']:-1;
            
            //awaken existing
            $awaken_card=utils::get_card($buy['search'],$rarity,true);
            do
            {
                $awaken_count=0;
                if ($buy['type'] == 'name')
                    $awaken_count=$this->kc->awaken_card($awaken_card->id);
                if ($awaken_count>0)
                    auto_account::output($buy['search']." awake +$awaken_count");
            } while ($awaken_count>0);
            $rare=isset($buy['rarity'])?$buy['rarity']:null;
            //don't buy if you have 10 or more awake already
            if (count(utils::return_monsters(kc::$monster_obj,$this->kc->my_monsters_obj,null,$rare,'inpool',null,null,10,$awaken_card->id,true,-1,null,null,20))>=10) {
                auto_account::output($this->kc->username . ' ' . $buy['search'] . ' skipped');
                continue;
            }
                
            if ($this->kc->dp < 1000 || $bids_made == 10)
                break;
            if ($this->kc->dp > 50000 && $bids_made >= 9)
                break;
            $auctions = $buy['type'] == 'name' ? 
                $this->kc->find_auctions($buy['search'],null,$buy['price'],10-$bids_made,$rarity):
                $this->kc->find_auctions(null,$buy['search'],$buy['price'],10-$bids_made,$rarity);
            $bids_on_item=0;
            $at_cap=false;
            foreach ($auctions as $auction)
            {
                if (in_array($auction->auction_id,auto_account::$auctions_bid_on))
                    continue;
                elseif ($this->kc->dp < $auction->start_price)
                    continue;
                elseif ($this->kc->dp < $buy['price'])
                    continue;
                elseif ($auction->bid_state==utils::get_key('normal',kc::$auction_ids) && $this->kc->db->get_auction_bid($auction->auction_id) >= $buy['price'])
                    //the bid price is what I was going to bid or greater. Another alt/user bid?
                    continue;
                elseif ($auction->bid_state==utils::get_key('bin',kc::$auction_ids)) {
                    $result = $this->kc->ah_bid(1,$auction->auction_id,$auction->start_price);
                    $at_cap = is_numeric($result) && $result == -1;
                }
                elseif ($auction->bid_state==utils::get_key('normal',kc::$auction_ids))
                {
                    $bid_result = $this->kc->ah_bid(0,$auction->auction_id,$buy['price']);
                    $at_cap = is_integer($bid_result) && $bid_result == -1;
                    $bids_made++;
                    $bids_on_item++;
                }
                else continue;
                if ($bids_on_item >= round(10/count($my_buy_orders)))
                    break;
                auto_account::$auctions_bid_on[]=$auction->auction_id;
                if ($bids_made == 10)
                    break;
                if ($at_cap)
                {
                    auto_account::output($this->kc->username . ' cant bid as at monster cap.');
                    break;
                }
            }
            $name=$buy['search'];
            $dp=$this->kc->dp;
            auto_account::output($this->kc->username . " $bids_on_item bids $name $dp");
            if ($bids_made==10 || $at_cap)
                break;
        }
    }
    
    private static $min_dp = 1200;
    private static $bank_dp = 1000;
    private static $max_transfer = 1750;
    
    public function transfer()
    {
        //Collect on Jimmybean & banks
        if (strpos($this->kc->username,'Jimmybean')===0) return false;
        elseif (auto_account::is_bank($this->kc->username)) return false;
        
        if ($this->kc->dp<auto_account::$min_dp) return false;
        if ($this->kc->world<>45) return false;

        //Get all auctions with no bids being sold by XXX Alt ending soonest first.
        //($world,$bid=null,$alt_seller=false,$batgig=true,$max_price=999999,$rarity=null,$order_desc=true)
        $alt_auctions_no_bid=$this->kc->db->list_all_sales($this->kc->world,0,true,false,999999,null,false); //,'Jimmybean'
        if (count($alt_auctions_no_bid)==0) return false;

        foreach ($alt_auctions_no_bid as $key => $auction)
            if (    ! (auto_account::is_bank($auction['account_name']) ||
                       strpos($auction['account_name'],'Jimmybean')===0) ||
                    $auction['auction_id']==0 || 
                    $auction['sale_price']==0 /* ||
                    $auction['remaining']===null || //will be null for bin
                    //anything less than 3 hours is not bid on
                    explode(':',$auction['remaining'])[0] < 1 */
                    )
                unset($alt_auctions_no_bid[$key]);
        //shuffle as multiple alts are logging on at the same time, help not double bid
        shuffle($alt_auctions_no_bid);
        
        $bids=0;
        foreach ($alt_auctions_no_bid as $auction)
        {
            
            //don't buy anything that is set to auto bid
            $continue=false;
            foreach (auto_account::$buys as $buy)
            {
                $rarity=isset($buy['rarity'])?$buy['rarity']:null;
                if ($buy['type']=='name' && utils::get_card($buy['search'],$rarity)->id == $auction['id'])
                    $continue=true;
            }
            if ($continue) continue;
            
            //record dp as the function updates the kc->dp property
            //static $min_dp is kept for synthing / buying cards to syth orbs on.
            $dp=$this->kc->dp - auto_account::$bank_dp > auto_account::$max_transfer ? auto_account::$max_transfer : $this->kc->dp - auto_account::$bank_dp;

            if ($auction['sale_price'] > $dp || $auction['bid'] > $dp)
                continue;

            try{
                if ($auction['remaining']===null)
                    $this->kc->ah_bid(1,$auction['auction_id'],$auction['sale_price']);
                else
                    $this->kc->ah_bid(0,$auction['auction_id'],$dp);
                $bids++;
            }
            catch (Exception $e)
            {
                switch ($e->getCode())
                {
                    case 25026: //bin
                        $this->kc->ah_bid(1,$auction['auction_id'],['sale_price']);
                        break;
                    case 25013: //max bids
                        $bids=10;
                        break;
                    default:
                        throw $e;
                }
            }
            auto_account::output($this->kc->username . " bid $dp id ".$auction['auction_id'] . ' ' . $auction['account_name']);
            if ($this->kc->dp <= auto_account::$min_dp)
                return $bids;
            if ($bids==10)
                return 10;
        }
        return $bids;
    }
    
    public function tutorial()
    {
        $tutorial = new tutorial($this->kc);
        $stage = null;
        try {
            $stage = $tutorial->get_stage();
            if ($stage==8)
                auto_account::output($this->kc->username . ' needs dungeon');
            $tutorial->do_stage($stage);
        } catch (Exception $ex) {
            auto_account::output($this->kc->username . ' tutorial error stage '.$stage. ' '.$ex->getMessage());
        }
    }

}

function user_canceled($name)
{
    if (!file_exists('cron/cron.txt'))
    {
        auto_account::output('...' . $name . ' Canceled');
        return true;
    }
    return false;
}

$alts=array(
    
    0=>array('username'=>'Jimmybean','password'=>'Jimmybean1','world'=>45,'device'=>'android'),
    1=>array('username'=>'Numerous','password'=>'Numerous1','world'=>45,'device'=>'android'),
    2=>array('username'=>'Comwine','password'=>'Comwine1','world'=>45,'device'=>'apple'),
    3=>array('username'=>'IgnoreHim','password'=>'IgnoreHim1','world'=>45,'device'=>'android'),
    4=>array('username'=>'OkSmall','password'=>'OkSmall1','world'=>45,'device'=>'android'),
    5=>array('username'=>'TimeWill','password'=>'TimeWill1','world'=>45,'device'=>'android'),
    6=>array('username'=>'ArghDam','password'=>'ArghDam1','world'=>45,'device'=>'android'),
    7=>array('username'=>'Tomfoolery','password'=>'Tomfoolery1!','world'=>45,'device'=>'apple'),
    8=>array('username'=>'TomdickHary','password'=>'Tomdick!1','world'=>45,'device'=>'apple'),
    9=>array('username'=>'Joesmith','password'=>'Joesmith*2','world'=>45,'device'=>'android'),
    10=>array('username'=>'IgnoreMe','password'=>'IgnoreMe1','world'=>45,'device'=>'android'),
    11=>array('username'=>'WorldOne','password'=>'WorldOne1','world'=>44,'device'=>'android'),
    12=>array('username'=>'Carol03','password'=>'Dade2254','world'=>44,'device'=>'android'),
    13=>array('username'=>'SameSame','password'=>'SameSame1','world'=>44,'device'=>'android'),
    14=>array('username'=>'Youtube1','password'=>'Youtube2','world'=>44,'device'=>'android'),
    //These have been created by someone else but not used since 14/8
    15=>array('username'=>'Delta1','password'=>'Delta001','world'=>45,'device'=>'android'),
    16=>array('username'=>'DynastyA','password'=>'Delta001','world'=>45,'device'=>'android'),
    17=>array('username'=>'DynastyC','password'=>'Delta001','world'=>45,'device'=>'android'),
    18=>array('username'=>'DynastyD','password'=>'Delta001','world'=>45,'device'=>'android'),
    19=>array('username'=>'DynastyE','password'=>'Delta001','world'=>45,'device'=>'android'),
    20=>array('username'=>'Uhg','password'=>'Asdfghjk1','world'=>45,'device'=>'apple')
);

$banks=array(

    0=>array('username'=>'a66711515','password'=>'Hellarse1','world'=>45,'device'=>'android'),
    1=>array('username'=>'b66711515','password'=>'Hellarse1','world'=>45,'device'=>'android'),
    2=>array('username'=>'a13798528950','password'=>'Hellarse1','world'=>45,'device'=>'android'),
    3=>array('username'=>'121212','password'=>'Hellarse1','world'=>45,'device'=>'android'),
    4=>array('username'=>'k025937','password'=>'Hellarse1','world'=>45,'device'=>'android'),
    //5=>array('username'=>'K025937','password'=>'K025937','world'=>45,'device'=>'android'), //Someone changed the pw? Ask yp
    6=>array('username'=>'a98421771','password'=>'Hellarse1','world'=>45,'device'=>'android'),
    7=>array('username'=>'b98421771','password'=>'Hellarse1','world'=>45,'device'=>'android'),
    //8=>array('username'=>'1212','password'=>'Sharks22','world'=>45,'device'=>'android'), //Lipkix
    9=>array('username'=>'Jimmybean','password'=>'Jimmybean1','world'=>45,'device'=>'android'), //KC1 code
    10=>array('username'=>'Numerous','password'=>'Numerous1','world'=>45,'device'=>'android'), //KC1 code
    //11=>array('username'=>'Comwine','password'=>'Comwine1','world'=>45,'device'=>'apple'), //KC1 code
    12=>array('username'=>'IgnoreHim','password'=>'IgnoreHim1','world'=>45,'device'=>'android'), //KC1 code
    13=>array('username'=>'OkSmall','password'=>'OkSmall1','world'=>45,'device'=>'android'), //KC1 code
    14=>array('username'=>'TimeWill','password'=>'TimeWill1','world'=>45,'device'=>'android'),
    15=>array('username'=>'ArghDam','password'=>'ArghDam1','world'=>45,'device'=>'android'),
    16=>array('username'=>'Tomfoolery','password'=>'Tomfoolery1!','world'=>45,'device'=>'apple'),
    17=>array('username'=>'TomdickHary','password'=>'Tomdick!1','world'=>45,'device'=>'apple'),
    18=>array('username'=>'Joesmith','password'=>'Joesmith*2','world'=>45,'device'=>'android'),
    19=>array('username'=>'IgnoreMe','password'=>'IgnoreMe1','world'=>45,'device'=>'android')
);

/*
//Test Area
try {
    $kc = new kc('Jimmybean','Jimmybean1','android',45,null,null,true,true);
    //$kc->logon();
    //$kc->awaken_card(utils::get_key('Armored Skeleton',kc::$monsters));
    auto_account::$debug = true;
    $automat = new auto_account($kc);
    $automat->do_all(true);
} catch (Exception $e) {}
exit();
//*/

$force=isset($_POST['force']) || !isset($_POST['logon']);
$is_cron=!isset($_POST['logon']);
$failures = array();
$db=new db();
$numerous_sr=$db->list_card_count(45,3);
foreach ($numerous_sr as $sr)
    if ($sr['count'] > 75)
        auto_account::$numerous_sr[]=(int)$sr['id'];

$db->close();
if(isset($_POST['username'])&&isset($_POST['password'])&&isset($_POST['world'])&&isset($_POST['device']))
{
    $kc=null;
    echo '<a href="http://www.somedodgywebsite.com/images/kc/interface/cron/out.txt">Out</a><br />'."\r\n";
    echo '<a href="http://www.somedodgywebsite.com/images/kc/interface/cron/cron_complete.txt">Complete</a><br />'."\r\n";
    echo '<a href="http://www.somedodgywebsite.com/images/kc/interface/cron/cron_running.txt">Running</a><br />'."\r\n";
    echo '<a href="http://www.somedodgywebsite.com/images/kc/interface/kc_error.txt">KC Error</a><br />'."\r\n";
    echo '<a href="http://www.somedodgywebsite.com/images/kc/interface/kc_debug.txt">KC Debug</a><br />'."\r\n";
    echo "<br />\r\n";
    try
    {
        $kc = new kc($_POST['username'],$_POST['password'],$_POST['device'],$_POST['world'],null,null,true,$is_cron);
        //auto_account::$debug = true;
        $automat = new auto_account($kc);
        $automat->do_all(true);
    } catch (Exception $e)
    {
        echo $e->getMessage()."<br />\r\n";
    }
    echo (string) $kc;
    exit();    
}

if(isset($_POST['batch'])) $batch = $_POST['batch'];

try
{
    //unlink('cron/cron_complete.txt');
    //file_put_contents('cron/cron.txt','true'); //This is handled by bean.php
    global $batch;
    global $is_cron;
    global $failures;
    if (!isset($batch))
    {
        auto_account::output('No batch specified');
        exit();
    }
    
    //alt banks
    if ($batch==='bank')
    {
        auto_account::output('Bank batch '.daily_version);
        foreach ($banks as $bank)
        {
            if (user_canceled($bank['username'])) exit();
            $kc = new kc($bank['username'],$bank['password'],$bank['device'],$bank['world'],null,null,false,$is_cron);
            $automat = new auto_account($kc);
            try
            {
                $automat->do_all($force);
            }
            catch (Exception $e)
            {
                auto_account::output("Error ". $kc->username . ' ' . $e->getMessage());
                $failures[] = array('username'=>$bank['username'],'password'=>$bank['password'],'device'=>$bank['device'],'world'=>$bank['world']);
            }
        }
    auto_account::output('*** Banks Complete');
    echo "Banks Complete\r\n";
    }
    
/*    
    if (strpos($batch,'bean')===0)
    {
        $start=0;
        $finish=50;
        if(is_numeric(substr($batch,-1)))
        {
            $start=substr($batch,-1);
            $finish=$start+10;
        }
        for($a=$start;$a<$finish;$a++)
        {
            if ($a==0) continue;
            $username = $alts[0]['username'] . $a;
            if (user_canceled($username)) exit();
            $kc = new kc($username,$alts[0]['password'],$alts[0]['device'],$alts[0]['world'],null,null,true,true);
            $automat = new auto_account($kc);
            try
            {
                $automat->do_all($force);
            }
            catch (Exception $e)
            {
                $error=" Error ". $username . "\r\n" . $e->getMessage()."\r\n";
                file_put_contents('cron/cron_complete.txt',$error,FILE_APPEND);
            }
        }
        echo "Jimmybean$start Complete\r\n";
    }
*/

    //alts
    $alt=null;
    if (is_numeric($batch))
    {
        $alt = $alts[$batch];
        auto_account::output($alt['username'].' batch '.daily_version);
    }
    for ($a=1;$a<51 && is_numeric($batch);$a++)
    {
        if (user_canceled($alt['username'])) exit();
        
        $username = $alt['username'] . $a;
        $continue=false;
        $break=false;
        
        switch ($username)
        {
            case  'Uhg':
            case  'Uhg1':
            case  'Uhg2':
            case  'Uhg3':
            case  'Uhg4':
            case  'Uhg5':
            case  'Uhg6':
            case  'Uhg7':
            case  'Uhg8':
            case  'Uhg9':
            case  'Uhg10':
            case 'Comwine1':
            case 'OkSmall1':
            case 'OkSmall11': //Shar g+
            case 'OkSmall34': //Shar g+
            case 'OkSmall36': //Shar g+
            case 'IgnoreHim1':
            case 'IgnoreHim2':
            case 'IgnoreHim3':
            case 'IgnoreHim21':
                $continue=true;break;
            case 'TomdickHary10':
            case 'Tomfoolery22':
            case 'Delta148':
            case 'DynastyA31':
                $break=true;break;
        }
        if ($continue) continue;
        if ($break) break;
        
        //Alts from Rey, split dp with him.
        //$batgig=strpos($alt['username'],'Uhg')===false?true:false;
        $kc = new kc($username,$alt['password'],$alt['device'],$alt['world'],null,null,true,$is_cron);
        $automat = new auto_account($kc);
        try
        {
            $automat->do_all($force);
        }
        catch (Exception $e)
        {
            if ($e->getCode()==-4) throw $e; //Main throw critical error
            $error=" Error ". $username . " " . $e->getMessage()."\r\n";
            file_put_contents('cron/cron_complete.txt',$error,FILE_APPEND);
            $failures[] = array('username'=>$username,'password'=>$alt['password'],'device'=>$alt['device'],'world'=>$alt['world']);
        }
        /*
        if ($a>2)
        {
            echo 'Debug Complete.';
            exit();
        }
        */
    }
    if (is_numeric($batch))
    {
        auto_account::output('*** '.$alt['username'].' Complete');
        echo $alt['username']." Complete\r\n";
    }
    
    file_put_contents('cron/cron_complete.txt',"Batch $batch -> ".count($failures)." retries\r\n",FILE_APPEND);
    $try_count=0;
    while (count($failures) > 0)
    {
        foreach($failures as $key => $failure)
        {
            $kc = new kc($failure['username'],$failure['password'],$failure['device'],$failure['world'],null,null,$batgig,$is_cron);
            $automat = new auto_account($kc);
            try
            {
                $automat->do_all(true);
                unset($failures[$key]);
            }
            catch (Exception $e)
            {
                if ($e->getCode()==-4) throw $e; //Main throw critical error
                $error=" Retry Error ". $failure['username'] . " " . $e->getMessage()."\r\n";
                file_put_contents('cron/cron_complete.txt',$error,FILE_APPEND);
            }
        }
        $try_count++;
        if ($try_count > 4) {
            file_put_contents('cron/cron_complete.txt',"*** Batch $batch retries failed 5 times !!! ***\r\n",FILE_APPEND);
            break;
        }
    }
}
catch (Exception $e)
{
    auto_account::output("!!!!!! Faital Error. " . $e->getMessage());
}
auto_account::output("* $batch Comeplete\r\n");
?>