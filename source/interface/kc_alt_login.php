<?php
    require('include/kc.php');
    require('include/template.php');

    $my_cookie = new cookie(isset($_POST['reset_cookie']));
    $current=$my_cookie->current;
    
    //load cookie vars from cookie
    $my_cookie->get_cookie();
    
    $username = $my_cookie->username;
    $password = $my_cookie->password;
    $version = kc::$versions[$my_cookie->device];
    $device=$my_cookie->device;
    $client=kc::$clients[$my_cookie->device];
    $world=$my_cookie->world;
    $session=$my_cookie->session;
    
    $arena=$my_cookie->arena;
    $streak=$my_cookie->streak;
    
    $batgig=$my_cookie->batgig;
    $batch=$my_cookie->batch;
    $draw=$my_cookie->draw;
    $tickets=$my_cookie->tickets;

    $savedname='';
    if ($batch)
    {
        if ($current==-1)
            $my_cookie->set_var('saved_name',$username);
        $current++;
        $username=$my_cookie->saved_name.$current;
        $savedname = $my_cookie->saved_name;
        
    } else
        $current=51;

    $my_cookie->current = $current;
    $my_cookie->set_cookie();
    
    if ($current==0)
    {
          echo template::basic("<h1>Batch Logon Started</h1>",'template/kc2_logon_multi.html',"/images/kc/interface/kc_alt_login.php?current=$current&session=$session");
          exit();
    }
    
    try
    {
     
        $cid=isset($_POST['cid'])?$_POST['cid']:null;
        $serial=isset($_POST['serial'])?$_POST['serial']:null;
        $kc=new kc($username,$password,$device,$world,null,null,$batgig);
        $kc->logon($cid,$serial);
        $cookie=utils::get_needed_cookie($kc->cookie);
        
        $day=$kc->login_stamp_data->day_of_stamp;
        
        //Tickets
        if ($tickets)
            $tickets=$kc->get_tickets();
        
        //Presents
        $present_echo='';
        if ($my_cookie->presents)
            $present_echo=$kc->get_present_list();

        //Arena
        $streak_echo='';
        if ($batgig && ($world==45 || ($world==44 && ($arena==618 || $arena==951))) && $arena > 0 && $streak < 100)
        {
            //echo 'Hit';exit();
            $cancel_arena = false;
            if (!$kc->get_duel_page()->is_regist) {
                if ($kc->check_attack_ready($kc->homebase_id,0))
                    $kc->duel_register($kc->homebase_id,0);
                elseif ($kc->set_random_unit($kc->homebase_id,0))
                    $kc->duel_register($kc->homebase_id,0);
                else {
                    $streak_echo .= 'Unit not valid for arena<br />';
                    $cancel_arena=true;
                }
                if (!$cancel_arena)
                    $kc->get_duel_page();
            }
            if (!$cancel_arena && $kc->duel_data->remain === 0)
                $streak_echo .= 'No arena points<br />';
            
            if (!$cancel_arena && $kc->duel_data->remain > 0)
            {
                set_time_limit(5 * 60);
                try {
                    $player=$kc->get_duel_user($arena);
                    if ($player!==null && $player->result==0) {
                        $kc->duel($arena);
                        $streak++;            
                    } else {
                        $tries = $kc->duel_data->remain;
                        while ($kc->duel_data->remain > 0)
                        {
                            $kc->get_duel_page();
                            
                            $my_rank = $kc->duel_data->rank;
                            foreach ($kc->duel_data->duel_monsters as $random_ply)
                            {
                                if ($random_ply->result<>0) continue;
                                if ($my_rank + 30 < $random_ply->rank_num) //50 + 30 < 500
                                $kc->duel($random_ply->user_id);
                                $tries--;
                                break;
                            }
                            if ($tries==0)
                                break;
                        }
                        $streak_echo.= $player==null?'Player not found<br />':'Alt has already battled you in the arena<br />';
                        $streak_echo.= 'Used all goes attacking random ppl<br />';
                    }
                } catch (Exception $e) {
                    $streak_echo.='Some error attacking<br />';
                }                
            }
        }

        if (isset($streak) && $streak>0)
        {
            $streak_echo.='Arena Streak : '.$streak;
            $my_cookie->streak = $streak;
            $my_cookie->set_cookie();
        }

        //Draw
        $card='';
        $cardecho='';
        if($draw || ($batgig && $day == 21 && $current < 51))
        {
            //Get any comp tickets data:sort_key=0&sort_type=1&filter_key=200&filter_rarity=-1
             $kc->get_present_list(200,-1);
             $presents=array();
             foreach ($kc->present_data as $present)
                //if ($present->title=='Compensation Gift')
                    $presents[]=$present->id;
             
             if (count($presents) > 0)
                 $kc->get_presents($presents);


            //Draw
            $cardecho = $kc->draw_login_rewards();
            
            //Delete Commons 
            if ($batgig && count($kc->unit_data->monsters) > 100)
                $kc->delete_commons(count($kc->unit_data->monsters) - 100);
        }

        
        //Set the template for batgig or not
        $template=$current<50?'template/kc2_logon_multi.html':'template/kc2_logon.html';
        if($current<50)
        {
            // http://www.somedodgywebsite.com/images/kc
            $data = "/images/kc/interface/kc_alt_login.php?current=$current&session=$session";
            echo template::basic("<h1>Account Logged On</h1><BR />Bonus $day/21<BR /><BR />Username: $username<BR />Password: $password<BR /><BR />$streak_echo<BR />$cardecho<BR />$tickets<BR />$present_echo",
                                 $template,$data);
        } else {
            //This is single login
            
            //This is the refresh
            $data = $batgig?'/images/kc/kc2loginXXX.html':'/images/kc/kc2login_private.html';
            if ($batgig)
                //echo the cookie
                echo template::basic("Reset Complete<BR />Bonus $day/21<BR /><BR />You may now log on with a different account on your device.<BR /><BR />$streak_echo<BR />$cardecho<BR />$cookie<BR />$tickets<BR />$present_echo",
                                     $template,$data);
            else
                echo template::basic("Reset Complete<BR />Bonus $day/21<BR /><BR />You may now log on with a different account on your device.<BR />$cardecho<BR />$tickets<BR />$present_echo",
                                     $template,$data);
        }
    } catch (Exception $e)
    {
        $err = $e->getMessage();
        $echo_data=array('echo' => "$err<BR /><BR />Username: $username<BR />Password: $password<BR />", 'username' => $username);
        echo template::simple($echo_data,'template/kc2_err.html');
    }

?>