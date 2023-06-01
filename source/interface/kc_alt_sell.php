<?php
require_once('include/kc.php');
require_once('include/template.php');

$username = $_POST['username'];
$password = $_POST['password'];
$device=$_POST['device'];
$world=$_POST['world'];

$kc=new kc($username,$password,$device,$world,null,null,true);

 try {

     $kc->logon();
     $day=$kc->login_day;
     
     //TODO:write up the ids for the filters in kc class
     //Draws all sr cards from presents
     /*
     $kc->get_present_list(9,3);
     $presents=array();

     foreach ($kc->present_data as $present)
        if (isset($present->monster->m_id) && kc::$monster_obj[$present->monster->m_id]->is_auction)
            $presents[]=$present->id;
     
            
     if (count($presents) > 0)
         $kc->get_presents($presents);
     */
     if ($kc->unit_data == null)
        $kc->get_units($kc->homebase_id);
     
     $textout['echo']='';
     $textout['cookie']=$kc->cookie;
     $textout['world']=$world;
     $textout['device']=$device;
     $textout['dp']=$kc->town->resource->dp;
     $textout['cp']=$kc->town->resource->cp;
     $textout['username']=$username;
     
     $cards=$kc->unit_data->monsters;
     usort($cards,'utils::sort_by_RnN');
     foreach ($cards as $card){
         $onclick='onclick="uniqueid('.$card->uniq_data->u_id.')"';
         $m_state = ucfirst(kc::$state_ids[$card->uniq_data->state]);
         $name = kc::$monsters[$card->m_id];
         $edition=isset($card->edition)?kc::$edition_ids[$card->edition->type]:'';
         $name = substr($card->m_id,-1)=='3'?"<b>$edition $name</b>":$edition.' '.$name;
//         if ($edition && $name == 'Dantalion') { $a=1; }
         $u_id = $card->uniq_data->u_id;
         $awake = $card->uniq_data->awake_exp>0?$card->uniq_data->awake_exp:'';
         $skill_xp = $card->skills[0]->exp + $card->skills[1]->exp + $card->skills[2]->exp;
         $skill_lv = $card->skills[0]->lv;
         $skill_xp=$skill_xp>100?"$skill_lv ($skill_xp)":'';
         $textout['echo'].="<tr><td><input type='radio' $onclick></td><td>$name</td><td>$m_state</td><td class='center'>$awake</td><td class='center'>$skill_xp</td><td>$u_id</td>";
         if ($card->uniq_data->state==4)
         {
             if ($kc->auction_sell_data === null)
                $kc->get_auction_sell_data();
             $auc = $kc->get_auction_sell_item($card->uniq_data->u_id);
             
             $id = $auc->auction_id;
             $bid_num = $auc->bid_num;
             $highest_bid = $auc->highest_price;
             $start = $auc->start_price;
             $deadline = $auc->deadline_tm - $kc->game_time();
             
            $dt = new DateTime();
            if ($deadline > 0) {
                $dt->add(new DateInterval('PT'.$deadline.'S'));
                $diff = $dt->diff(new DateTime());
                $deadline =  $diff->d * 24 + $diff-> h . ':' . str_pad($diff->i,2,'0',STR_PAD_LEFT);
            } else
                $deadline =  '0:00';
             /*$deadline = $auc->deadline_tm - kc::$server_time -(60* 60); //hack for some reson the ah time left is +1 hour
             $minutes = date("H:i", $deadline);
             if ($deadline / 60 / 60 > 23)
                $minutes = '1.' . $minutes;*/
             //from the sell data it is impossible to know if an item is buy it now or standard
             //$state = gmdate("H",$auc->deadline_tm - kc::$server_time) < 24 ? 'BIN' : 'S';
             $button = $highest_bid==0?"<button type='button' onclick='cancel_auction($id);'>Cancel</button>":null;
             
             $textout['echo'].="<td>$id</td><td>$start</td><td>$highest_bid</td><td>$deadline</td><td>$button</td>";
             
             //Update bid price
             $kc->db->card_bid_price($id,$highest_bid);
         }
         $textout['echo'].="</tr>\r\n";
     }
     if ($textout['echo']=='')
        $textout['echo']='No SR/R :(<BR />';
     else
        $textout['echo'].='</table><BR />';

     echo template::simple($textout,'template/kc2_alt_dp_sell.html');
     
     } catch (Exception $e) {
         $err = $e->getMessage();
         $textout['echo'] = "$err<BR /><BR />Username: $username<BR />Password: $password<BR />";
         echo template::simple($textout,'template/kc2_err.html');
     }

?>