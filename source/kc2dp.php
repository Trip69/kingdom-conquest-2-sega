<?php
 require('functions.php');

 $secure='https';
 $current=0;
 $allcards=array();

/* 
 $username = 'K025937';
 $password = 'K025937';
 $version = $versions["apple"];
 $client = $clients["apple"];
 $world= '40';

 
 echo "user $username ";
 echo "pass $password ";
 echo "ver $version ";
 echo "client $client ";
 echo "world $world ";

 
$_POST['device']='apple';
$_POST['username']='Comwine40';
$_POST['password']='Comwine1';
$_POST['world']=45;

*/ 

$username = $_POST['username'];
$password = $_POST['password'];
$version = $versions[$_POST['device']];
$device=$_POST['device'];
$client=$clients[$_POST['device']];
$world=$_POST['world'];

 try {

    $uuid = makehash($device);

     $epoch = unixepoch();
     $url = "$secure://common.kingdom-conquest2.com/socialsv/common/StartUpCheck.do";
     $data = "language=1&check_code=$version&_tm_=$epoch";
     senddata($client,$url,$data);
     
     $epoch = unixepoch();
     $url = "$secure://common.kingdom-conquest2.com/socialsv/common/Auth.do";
     $data = "world_id=10$world&kc_id=$username&password=$password&language=1&check_code=$version&cid=&serial=&_tm_=$epoch";
     $userid = senddata($client,$url,$data);
      
     $epoch = unixepoch();
     $url = "$secure://w10$world-sim.kingdom-conquest2.com/socialsv/Login.do";
     $data = "user_id=$userid&language=1&device_type=0&uuid=$uuid&_tm_=$epoch";
     $gooddata = senddata($client,$url,$data);
     
     global $allcards;
     foreach ($gooddata->monsters as $mon) {
         $allcards[$mon->id] = $mon->name;
     }
     
     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/LoginStamp.do";
     $data = "_tm_=$epoch";
     $loginstamp=senddata($client,$url,$data);

     //echo "current : $current<BR/>";
     $day=$loginstamp->day_of_stamp;

     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/Town.do";
     $data = "_tm_=$epoch";
     $town = senddata($client,$url,$data);
     
     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/PresentList.do";
     $data = "sort_key=0&sort_type=1&filter_key=9&filter_rarity=3&_tm_=$epoch";
     $presents = senddata($client,$url,$data);
     
     $draw_string='';
     foreach ($presents->presents as $present)
        $draw_string.='&present_id='.$present->id;
     if ($draw_string!='') {
        //type=1&present_id=5822271&present_id=5822189&_tm_=1403960370069
         $epoch = unixepoch();
         $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/PresentMultiGet.do";
         $data = "type=1$draw_string&_tm_=$epoch";
         senddata($client,$url,$data);
     }
     
     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/MonsterView.do";
     $data = "to_field_id=0&reinforcement=0&_tm_=$epoch";
     $units=senddata($client,$url,$data);
     global $allcards;

     require ('interface/include/db.php');
     $db=new db();
     $account = new account($username,$password,$world,$device,$kc->login_day);
     $db->account_update($account,$units);
     $textout[0]='<table align="center">';
     
     foreach ($units->monsters as $card){
         if (substr($card->m_id,-1)>1){
             $onclick='onclick="uniqueid('.$card->uniq_data->u_id.')"';
             $textout[0].="<tr $onclick><td>".iif(substr($card->m_id,-1)=='3','SR ','').$allcards[$card->m_id].'</td><td>'.$card->uniq_data->u_id.'</td>';
             if ($card->uniq_data->state==4)
                $textout[0].='<td>Auctioned</td>';
             $textout[0].='</tr>';
         }
     }
     if ($textout[0]=='<table align="center">')
        $textout[0]='No SR/R :(<BR />';
     else
        $textout[0].='</table><BR />';
     global $cookie;
     //$textout[0].="<BR />Cookie : $cookie<BR />";
     $textout[1]=$cookie;
     
     $textout[0].='DP: '.$town->resource->dp.'<BR />';
     $textout[2]=$town->resource->dp;
     $textout[3]=$world;
     $textout[4]=$device;

     echo template_better($textout,'kc2_dpout.html');
     } catch (Exception $e) {
         $err = $e->getMessage();
         echo template("$err<BR /><BR />Username: $username<BR />Password: $password<BR />",'kc2_err.html','');
     }

?>