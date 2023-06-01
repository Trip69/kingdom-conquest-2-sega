<?php
    require('include/kc.php');
    require('include/template.php');
 
/*    
    $_POST['cookie']='JSESSIONID=5E321A1BD4C2C074F6E5EE3EA452D61C.tomcat3';
    $_POST['device']='android';
    $_POST['world']=44;
    $_POST['tutorial']='true';
    $_POST['user_id']='2814'; //
//*/

    function require_page($page,kc $kc)    
    {
        $_POST['username']=$kc->username;
        $_POST['password']=$kc->password;
        $_POST['device']=$kc->device_type;
        $_POST['world']=$kc->world;
        $_POST['cookie']=$kc->cookie;
        require($page);
    }

    try {
        if (!isset($_POST['world']) && $_POST['cookie']==null)
            throw new Exception('World Unknown');

        $echo=array();
        $echo=template::fill_commmon_data();

        $tutorial=null;
        $kc = null;
        
        $world=isset($_POST['world'])?$_POST['world']:null;
        $device=isset($_POST['device'])?$_POST['device']:null;
        $username=isset($_POST['username'])?$_POST['username']:null;
        $password=isset($_POST['password'])?$_POST['password']:null;
        $cookie=isset($_POST['cookie'])?$_POST['cookie']:null;
        $user_id=isset($_POST['user_id'])?$_POST['user_id']:null;
        
        $kc = new kc($username,$password,$device,$world,$cookie,$user_id,false);
        
        foreach ($_POST as $key => $value)
            switch ($key) {
                case 'town':
                    $kc->get_town(0);
                    echo template::echo_town($kc->town);
                    break;
                case 'build':
                //this is broken, the form is screwed up, need to re think
                    $echo['echo'] = 'Building';
                    $kc->build_with_string($_POST['build_string']);
                    $echo['title']='Build';
                    echo template::simple($echo,'template/kc2_ok.html');
                    break;
                case 'build_cancel':
                    $kc->build_cancel(0,$_POST['build_id']);
                    $echo['echo'] = 'Canceled Build';
                    $echo['title']='Build Cancelled';
                    echo template::simple($echo,'template/kc2_ok.html');
                    break;
                case 'units':
                    $kc->get_units(0);
                    echo template::echo_units($kc->unit_data,$kc);
                    break;
                case 'monster_remove_u_id':
                    if ($_POST['monster_remove_u_id']==0) continue;
                    $kc->remove_monster($_POST['monster_remove_field_id'],$_POST['monster_remove_unit'],$_POST['monster_remove_u_id']);
                    $echo['echo'] = 'Moster Removed';
                    $echo['title']='Monster Removed';
                    echo template::simple($echo,'template/kc2_ok.html');
                    break;
                case 'commander_remove_field_id':
                    if ($_POST['commander_remove_field_id']==0) continue;
                    $kc->remove_commander($_POST['commander_remove_field_id'],$_POST['commander_remove_unit']);
                    $echo['echo'] = 'Commander Removed';
                    $echo['title']='Removed Commander';
                    echo template::simple($echo,'template/kc2_ok.html');
                    break;
                case 'auction_cancel':
                    $kc->ah_cancel($_POST['auction_id']);
                    $echo['echo'] = 'Auction Canceled';
                    $echo['title']='Action Cancel';
                    echo template::simple($echo,'template/kc2_ok.html');
                    break;
                case 'auction_bid':
                    $ah_type=$_POST['auction_type']=='bin'?1:0;
                    $kc->ah_bid($ah_type,$_POST['auction_id'],$_POST['bid']);
                    $echo['echo'] = $_POST['auction_type'] == 'bin' ? $_POST['bid'] . ' transfered': $_POST['bid'] . ' Bid Placed';
                    $echo['title']='Bid Placed';
                    echo template::simple($echo,'template/kc2_ok.html');
                    break;
                case 'bulk_sell':
                    if (!isset($_POST['seller']) || $_POST['seller']=='')
                        throw new Exception('No seller specified');
                    $kc->connect_db();
                    $random = $_POST['bulk_sell'] < 3;
                    $uids = $kc->db->get_uids($world,0,$_POST['bulk_sell'],$random);
                    $a=0;
                    if (count($uids)==0)
                        throw new Exception('There are no '.kc::$monster_obj[$_POST['bulk_sell']]->name.' to sell.');
                    $count = count($uids) > 10 ? 10 : count($uids);
                    $ah_cap = false;
                    for ($a=0;$a<$count;$a++)
                    {
                        try {
                            $kc->ah_sell($uids[$a]['u_id'],$_POST['bulk_price'],$_POST['seller']);
                        } catch (Exception $ex) {
                            if($ex->getCode() == 25023) //At ten auctions
                                {$ah_cap = true;break;}
                            elseif ($ex->getCode() == 25008) //Already in ah
                                $kc->db->set_card_status($uids[$a]['u_id'],4,$kc->world);
                            else throw $ex;
                        }
                    }
                    $card_type='';
                    switch ($_POST['bulk_sell'])
                    {
                        case 0:
                        case 1:
                        case 2:
                        case 3:
                            $card_type=ucfirst(kc::$rarity_ids[$_POST['bulk_sell']]);
                            break;
                        default:
                            $card_type=kc::$monster_obj[$_POST['bulk_sell']]->name;
                    }
                    $cap = ($ah_cap || $a == 10) ? 'At max listings':null;
                    $a=$a==0?'No':$a;
                    $echo['echo'] = "$a $card_type put for auction at ".$_POST['bulk_price'];
                    $echo['echo'] .= "<BR /><BR />$cap";
                    $echo['title']='Bulk Sell';
                    echo template::simple($echo,'template/kc2_ok.html');
                    break;
                case 'tutorial':
                    require('include/tutorial.php');
                    $echo=array();

                    //$kc->get_town(0);
                    $tutorial=new tutorial($kc);
                    $stage=null;
                    if (isset($_POST['tutorial_stage']) &&  $_POST['tutorial_stage'])
                        $stage = $_POST['tutorial_stage'];
                    else
                        $stage=$tutorial->get_stage();
                    //echo $stage;exit();
                            $echo['username'] = $_POST['username'];
                    $echo['username'] = $_POST['username'];
                    if ($stage==0) {
                        $echo['echo'] = 'Something in construction please wait<BR />';
                        echo template::simple($echo,'template/kc2_err.html');
                    } elseif ($stage==-1) {
                        $echo['echo'] = 'Dungeon needs to be run<BR />';
                        echo template::simple($echo,'template/kc2_err.html');
                    } elseif ($stage==-2) {
                        $echo['echo'] = 'Unable to determin progess through tutorial<BR />';
                        echo template::simple($echo,'template/kc2_err.html');
                    } else {
                        $echo['echo'] = "Stage $stage<BR /><BR />";
                        $echo['echo'] .= $tutorial->do_stage($stage);
                        $echo['wait_time'] = $tutorial->wait_time;
                        $echo['title'] = "Tutorial: Stage $stage";
                        echo template::simple($echo,'template/kc2_ok.html');
                    }
                    break;
                case 'batgig_settings':
                    $account=$kc->account;
                    $account->auto=isset($_POST['auto']);
                    $account->auto_arena=isset($_POST['auto_arena']);
                    $account->cookie=isset($_POST['auto_cookie'])&&$_POST['auto_cookie']!==''?utils::get_needed_cookie($_POST['auto_cookie']):null;
                    $account->auto_login=isset($_POST['auto_login'])?$_POST['auto_login']:null;
                    $kc->db->auto_save_settings($account);
                    $echo['title'] = "Batgig: Saved Settings";
                    $echo['echo'] = "Saved Settings";
                    echo template::simple($echo,'template/kc2_ok.html');
                    break;
                case 'auto_check_now':
                    require('include/batgig.php');
                    $batgig = new batgig(false);
                    $batgig -> refresh_all($kc);
                    $batgig -> do_all($kc);
                    break;
                case 'auto_buy':
                    $echo['title'] = "Account Details";
                    $kc->get_town(0);
                    $cash = $kc->dp > 1000 ? 1000 : $kc->dp;
                    $auctions=$kc->find_auctions(null,null,$cash,50,null,3,2,$_POST['race'],true);
                    if (count($auctions)==0) {
                        $echo['echo'] = 'No auctions BIN for a ' . kc::$race_ids[$_POST['race']] . ' below ' . $cash;
                        echo template::simple($echo,'template/kc2_ok.html');
                        break;
                    }
                    $search=array('id'=>0,'price'=>999999);
                    foreach ($auctions as $auction)
                        if ($auction->start_price < $search['price'])
                            $search = array('id'=>$auction->auction_id,'price'=>$auction->start_price);
                        //TODO: Write buy code and pass sell page back to client
                    $kc->ah_bid(1,$search['id'],$search['price']);
                    require_page('kc_alt_sell.php',$kc);
                    break;
                case 'auto_enhance':
                    utils::check_args(array($_POST['id'],$_POST['level']));
                    $kc->get_units(0);
                    if (!isset($kc->my_monsters_obj[$_POST['id']]))
                        throw new Exception('Monster id is not present on account');
                    $to_enhance=$kc->my_monsters_obj[$_POST['id']];
                    if ($to_enhance->skills[0]->lv >= $_POST['level'])
                        throw new Exception('Skill is already level '.$_POST['level']);
                    $monster_list=array();
                    for ($rarity=0;$rarity<3;$rarity++) {
                        $monsters=utils::return_monsters(kc::$monster_obj,
                                                             $kc->unit_data->monsters,
                                                             kc::$monster_obj[$to_enhance->m_id]->race,
                                                             $rarity,'inpool',null,null,0,null,null,0,0,null,8);
                        $monster_list = array_merge($monster_list,$monsters);
                        //$monster_list = all awake 0, small,standard edition cards with a max skill lv of 8 of the same race
                    }
                    if (isset($_POST['sr'])) {
                        //race all enhance
                        $sr_orbs=utils::return_monsters(kc::$monster_obj,
                                                         $kc->unit_data->monsters,
                                                         'enhance',null,'inpool',null,null,0,null,false,0,0,null,10);
                        $monster_list = array_merge($monster_list,$sr_orbs);
                        //race specific
                        $sr_orbs=utils::return_monsters(kc::$monster_obj,
                                                         $kc->unit_data->monsters,
                                                         kc::$monster_obj[$to_enhance->m_id]->race,3,'inpool',null,null,0,null,false,0,0,null,10);
                        foreach ($sr_orbs as $key => $monster)
                            if (strpos(kc::$monster_obj[$monster->m_id]->name,'Orb')===false)
                                unset($sr_orbs[$key]);
                        $monster_list = array_merge($monster_list,$sr_orbs);
                    }
                    //add edition
                    if (isset($_POST['edition'])) {
                        for ($a=1;$a<3;$a++)
                        {
                            $edition=utils::return_monsters(kc::$monster_obj,
                                                             $kc->unit_data->monsters,
                                                             kc::$monster_obj[$to_enhance->m_id]->race,null,'inpool',null,null,0,null,null,$a,0,null,10);
                            foreach ($edition as $key => $card)
                                if (kc::$monster_obj[$card->m_id]->rarity == 3)
                                    unset($edition[$key]);
                            $monster_list = array_merge($monster_list,$edition);
                        }
                    }
                    //remove all non auction not called orb (star,diamond,fetaured cards)
                    foreach ($monster_list as $key => $monster)
                        if (kc::$monster_obj[$monster->m_id]->is_auction==0 && strpos(kc::$monster_obj[$monster->m_id]->name,'Orb')===false)
                            unset($monster_list[$key]);
                    
                    //remove all non common and non upgraded cards & the synth card if cards not checked
                    if (!isset($_POST['cards']))
                        foreach ($monster_list as $key => $monster)
                            if ((kc::$monster_obj[$monster->m_id]->rarity > 0 && $monster->skills[0]->exp == 0) || $to_enhance->uniq_data->u_id == $monster->uniq_data->u_id)
                                unset($monster_list[$key]);
                    
                    utils::remove_dups($monster_list);
                    usort($monster_list,'utils::sort_by_skill_xp');
                    foreach ($monster_list as $monster)
                    {
                        $use_cp = isset($_POST['cp']) && $kc->cp >= 5 && $monster->skills[0]->exp >= 750 ? 1 : 0;
                        $result = $kc->skill_enhance($to_enhance->uniq_data->u_id,$to_enhance->skills[0]->id,$use_cp,$monster->uniq_data->u_id);
                        if ($result->base_monster->skills[0]->lv >= $_POST['level'])
                            break;
                    }
                    require_page('kc_alt_sell.php',$kc);
                    break;
                case 'auto_awaken':
                    utils::check_args(array($_POST['id']));
                    $kc->get_units(0);
                    if (!isset($kc->my_monsters_obj[$_POST['id']]))
                        throw new Exception('Monster id is not present on account.');
                    $awaken=$kc->my_monsters_obj[$_POST['id']];
                    if ($awaken->uniq_data->state==4)
                        throw new Exception('Monster is in auction.');
                    $with=utils::return_monsters(kc::$monster_obj,
                                                     $kc->unit_data->monsters,
                                                     null,null,'inpool',null,null,null,$awaken->m_id,null,-1,null,null,4);
                    foreach($with as $key => $monster)
                        if ($monster->uniq_data->u_id == $awaken->uniq_data->u_id)
                            unset($with[$key]);
                    $max=isset($_POST['bt'])?20:10;
                    $ids=array();
                    foreach($with as $monster)
                    {
                        if ($awaken->uniq_data->awake_exp + 1 + $monster->uniq_data->awake_exp <= $max) {
                            $awaken->uniq_data->awake_exp = 1 + $monster->uniq_data->awake_exp;
                            $ids[]=$monster->uniq_data->u_id;
                        } elseif ($awaken->uniq_data->awake_exp == $max)
                            break;
                    }
                    if (count($ids)>0)
                        $kc->awaken($awaken->uniq_data->u_id,$ids);
                    require_page('kc_alt_sell.php',$kc);
                    break;
            }
    } catch (Exception $e) {
        $err='';
        if (isset($tutorial) && $tutorial !== null)
            $err.='Tutorial Stage '.$tutorial->stage.'<BR /><BR />';
        $err .= $e->getMessage();
        $echo=array();
        $echo['echo'] = "$err<BR />";
        $echo['username'] = isset($_POST['username'])?$_POST['username']:'';
        echo template::simple($echo,'template/kc2_err.html');
    }
    
?>
