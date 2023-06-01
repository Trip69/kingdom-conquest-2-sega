<?php
chdir('..');
require('include/kc.php');

$world=45;
$kc = new kc('1212','Sharks22','android',$world); //This login detail is only used to fill user_id from rankings table

function record_userid($start=3000)
{
    global $kc;
    $kc->logon();
    $start = round($start/20);
    $end=999;
    for ($page=$start;$page<=$end;$page++)
    {
        set_time_limit(60);
        $rank_page=$kc->get_ranking(1,$page,0);
        $end = $rank_page->page_max;
        foreach ($rank_page->ranking as $player)
            $kc->db->skcc_record_user($player->id,$world,$player->name,$player->rank);
    }
}

//record_userid();
$kc->db->skcc_remove_known($world);
$not_checked=$kc->db->skcc_get_unchecked($world);
shuffle($not_checked);
file_put_contents('cron/skcc.txt','Searching....');
foreach ($not_checked as $account)
{
    $used = 0;
    $checked=false;
    $profile=null;
    try
    {
        /*
        //1163 Some Alt
        $account['user_id']=3807;
        $account['name']='Test';
        */
        
        set_time_limit(1200);
        
        $skcc = $kc->db->skcc_get_id($account['user_id'],$world);
        
        $kc->account = new account($account['name'],$account['user_id'],$world,true);
        $kc->account->skcc = $skcc;
        $kc->user_id=$account['user_id'];
        $kc->username=$account['name'];
        $kc->password=$account['user_id'];
        
        
        $userid=$account['user_id'];
        $uuid=utils::make_hash(rand(0,1));
        $data = "user_id=$userid&language=1&device_type=0&uuid=$uuid";
        kc::$core_data=$kc->send_data('Login.do',$data);
        $kc->fill_core_data();
        
        $kc->get_town(0);
        if ($kc->constuct_current > 0)
            $rec=utils::unix_epoch();
        $kc->get_units($kc->homebase_id);

        $kc->account->nickname='Hacked';
        $kc->db->add_account($kc->account);
        
        $pack_history=$kc->send_data('PackHistory.do',null);
        if (isset($pack_history->monsters[0]) && $pack_history->monsters[0]->timestamp > $used)
            $used = $pack_history->monsters[0]->timestamp;
        
        $ah_mail=$kc->send_data('MailInBox.do','auction=1');
        if (isset($ah_mail->mail_list[0]) && $ah_mail->mail_list[0]->timestamp > $used)
            $used = $ah_mail->mail_list[0]->timestamp;
            
        $last_attack=$kc->send_data('UserReportList.do','type=0&page=1&filter_name_type=0&filter_name=&filter_coord_type=0&filter_coord_x=0&filter_coord_y=0');
        if (isset($last_attack->reports[0]) && $last_attack->reports[0]->timestamp > $used)
            $used = $last_attack->reports[0]->timestamp;
        
        $kc->get_present_list(9,3); //fills $kc->present_data
        if (is_array($kc->present_data))
            foreach ($kc->present_data as $present)
                $kc->db->skcc_record_present($kc->user_id,$present->desc,$present->monster->uniq_data->awake_exp,utils::make_skill_string($present->monster->skills));
                
        $profile = $kc->send_data('UserProfile.do','id='.$userid);
        $checked=true;
        
    } catch (Exception $ex)
    {
        switch ($ex->getCode())
        {
            case -6: //Login Void
                $used=utils::unix_epoch();
                $checked=true;
                file_put_contents('cron/skcc.txt','Stopped ('.$kc->username.')');
                die();
                break;
        }
    }
    if ($checked)
        $kc->db->skcc_set_data($kc->world,$kc->user_id,count($profile->history_books),date('Y-m-d H:m:s', round($used / 1000)));
    //die();
}
file_put_contents('cron/skcc.txt','Stopped (All Done)');
/*
I'll have it record deck cards and sr filtered presents, dp, cp, last active date the most resent from the few we have noted, building count, tutorial statu
*/
?>
