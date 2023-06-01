<?php
 require('functions.php');

 $secure='https';

 $current=0;
 $allcards=array();
 
 if (isset($_GET['username'])){
     $username = $_GET['username'];
     $savedname = $_GET['username'];
     $password = $_GET['password'];
     $version = $versions[$_GET['device']];
     $device=$_GET['device'];
     $client=$clients[$_GET['device']];
     $world=$_GET['world'];
     $current=$_GET['current'];
     $current++;
     $username=$username.$current;
 } else {
     $username = $_POST['username'];
     $password = $_POST['password'];
     $version = $versions[$_POST['device']];
     $device=$_POST['device'];
     $client=$clients[$_POST['device']];
     $world=$_POST['world'];
     if(isset($_POST['batch'])) {
        //echo 'key exisits';
        $savedname = $_POST['username'];
        $current=1;
        $username = $username . '1';
     } else {
         $current=51;
     }
 }

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
*/

$debug=false; 

 try {

    $uuid = makehash($device);
     //echo $uuid; exit();

     $epoch = unixepoch();
     $url = "$secure://common.kingdom-conquest2.com/socialsv/common/StartUpCheck.do";
     $data = "language=1&check_code=$version&_tm_=$epoch";
     senddata($client,$url,$data);
     
     if ($debug) echo '1';
     
     $epoch = unixepoch();
     $url = "$secure://common.kingdom-conquest2.com/socialsv/common/Auth.do";
     $data = "world_id=10$world&kc_id=$username&password=$password&language=1&check_code=$version&cid=&serial=&_tm_=$epoch";
     $userid = senddata($client,$url,$data);

     if ($debug) echo '2';
      
     $epoch = unixepoch();
     $url = "$secure://w10$world-sim.kingdom-conquest2.com/socialsv/Login.do";
     $data = "user_id=$userid&language=1&device_type=0&uuid=$uuid&_tm_=$epoch";
     $gooddata = senddata($client,$url,$data);
     
     if ($debug) echo '3';

     global $allcards;
     foreach ($gooddata->monsters as $mon) {
         $allcards[$mon->id] = $mon->name;
     }
     
     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/LoginStamp.do";
     $data = "_tm_=$epoch";
     $loginstamp=senddata($client,$url,$data);

     if ($debug) echo '4';

     $tickets='';
     if (isset($_POST['tickets']))
        $tickets=get_tickets();
     
     date_default_timezone_set('UTC');
     $now = date('l jS \of F Y h:i:s A');
     
     //echo "current : $current<BR/>";
     $day=$loginstamp->day_of_stamp;

     $presents='';
     if (isset($_POST['presents']))
         $presents=get_presents();

     $card='';
     $cardecho='';
     if($day == 21 &! isset($_POST['tickets']) && $current < 51)
     {
         $card = draw_packs();
         $cardecho = $card;
         if ($card!='') {
             $card=str_replace('<BR />',',',$card);
             $card=str_replace('<bold>','',$card);
             $card=str_replace('</bold>','',$card);
             file_put_contents('alts_sr.txt',"$now User:$username,Pass:$password,client:$client,world:$world,Card:$card\r\n\r\n",FILE_APPEND);
         }
     }

     if($current<50)
     {
     // http://www.somedodgywebsite.com/images/kc
        $data = "/images/kc/kc2.php?username=$savedname&password=$password&device=$device&world=$world&current=$current";
        echo template("Account Logged On<BR />Bonus $day/21<BR /><BR />Username: $username<BR />Password: $password<BR />$cardecho<BR />$tickets<BR />$presents",'kc2_logon.html',$data);
     } else {
         file_put_contents('alts_used.txt',"$now User:$username,Pass:$password,client:$client,world:$world,day:$day\r\n\r\n",FILE_APPEND);
         $data = "http://www.somedodgywebsite.com/images/kc/kc2login.html";
         if (isset($_POST['showcook']))
            echo template("Reset Complete<BR />Bonus $day/21<BR /><BR />You may now log on with a different account on your device.<BR />$cardecho<BR />$cookie<BR /><BR />$tickets<BR />$presents",'kc2_logon.html',$data);
         else
            echo template("Reset Complete<BR />Bonus $day/21<BR /><BR />You may now log on with a different account on your device.<BR />$cardecho<BR />$tickets<BR />$presents",'kc2_logon.html',$data);
     }
 } catch (Exception $e) {
     $err = $e->getMessage();
     echo template("$err<BR /><BR />Username: $username<BR />Password: $password<BR />",'kc2_err.html','');
 }

?>