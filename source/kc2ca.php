<?php
 require('functions.php');
 
 if (isset($_GET['username'])){
    $saveuser=$_GET['username'];
    $username = $_GET['username'].$_GET['current'];
    $password = $_GET['password'];
    $nick = get_name();
    $version = $versions[$_GET['device']];
    $client=$clients[$_GET['device']];
    $device=$_GET['device'];
    $world=$_GET['world'];
    $invite=$_GET['invite'];
    $current=$_GET['current'];
    $nokcid=$_GET['nokcid'];
 } else {
    $username = $_POST['username'];
    $saveuser =  $_POST['username'];
    $password = $_POST['password'];
    $nick = $_POST['nick'];
    $version = $versions[$_POST['device']];
    $client=$clients[$_POST['device']];
    $device=$_POST['device'];
    $world=$_POST['world'];
    $nokcid=isset($_POST['nokcid'])?$_POST['nokcid']:false;
    if (isset($_POST['invite']))
        $invite=$_POST['invite'];
    else
        $invite='';
    if (isset($_POST['batch']))
        $current=0;
    else
        $current=50;
 }

 if (isset($_COOKIE['created']) && $_COOKIE['created']>50) {
   echo template("Account creation limit reached",'kc2_ac.html',$data);
   exit;
 }
 
 $serial='';
 //$invite='8b5916d4';

$secure='https';

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

 try {
     if (!$nokcid && ( strlen($username) == 0 || strlen($password) == 0 || strlen($world) == 0 || strlen($device) == 0 ) )
        throw new Exception('Please fill in all details',196);
     if (strlen($password)<7 || strlen($password)>11)
        throw new Exception('Password must be more than 6 characters and less than 12',197);
     if (strlen($nick)>10)
        throw new Exception('Please enter a nickname of 10 characters or less.',197);
     if (strlen($username)>12)
        throw new Exception('Please enter a username of 12 characters or less.',197);
     if (!preg_match('(^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$)', $password))
        throw new Exception('Password must be alpha numeric and have different cases',195);
        $uuid = makehash($device);

    if ($invite!=''){
         $url = "$secure://pre.sega-net.jp/invite/access?cid=kc2_invite_en&lang=en&code=$invite";
         $serial=senddata($client,$url,$data);
         $invite='kc2_invite_en';
    }
     
     //https://pre.sega-net.jp/invite/access?cid=kc2_invite_en&lang=en&code=8b5916d4
     $epoch = unixepoch();
     $url = "$secure://common.kingdom-conquest2.com/socialsv/common/StartUpCheck.do";
     $data = "language=1&check_code=$version&_tm_=$epoch";
     senddata($client,$url,$data);

     if (!$nokcid) {
         $epoch = unixepoch();
         $url = "$secure://common.kingdom-conquest2.com/socialsv/common/KCIDInput.do";
         $data = "uuid=$uuid&_tm_=$epoch&type=0&kc_id=$username&password=$password&language=1&cid=$invite&serial=$serial";
         senddata($client,$url,$data);
     }

     $epoch = unixepoch();
     $url = "$secure://common.kingdom-conquest2.com/socialsv/common/Auth.do";
     $data = "world_id=10$world&kc_id=$username&password=$password&language=1&check_code=$version&cid=&serial=&_tm_=$epoch";
     $userid = senddata($client,$url,$data);

/*
         $epoch = unixepoch();
         $url = "$secure://w10$world-sim.kingdom-conquest2.com/socialsv/Login.do";
         $data = "user_id=$userid&language=1&device_type=0&uuid=$uuid&_tm_=$epoch";
         senddata($client,$url,$data);      
*/     
     
     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/OpeningActplay.do";
     $data = "world_id=10$world&kc_id=$username&password=$password&language=1&_tm_=$epoch";
     senddata($client,$url,$data);

     
     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/OpeningTown.do";
     $data = "world_id=10$world&kc_id=$username&password=$password&language=1&_tm_=$epoch";
     senddata($client,$url,$data);
          
     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/RegistAccount.do";
     $data = "serial_id=&world_id=10$world&kc_id=$username&password=$password&confirm_password=$password&language=1&device_type=0&_tm_=$epoch";
     $userid = senddata($client,$url,$data);

     $try = 0;
     $ok = false;
     $trynick = $nick;
     if ($trynick=='')
        $trynick = get_name();
     while(!$ok){
         try {
             if ($try > 0)
                $trynick = get_name();
             $epoch = unixepoch();
             $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/RegistUser.do";
             $data = "serial_id=$invite&user_id=$userid&name=$trynick&language=1&_tm_=$epoch";
             senddata($client,$url,$data);
             $nick=$trynick;
             $ok = true;
         } catch (Exception $e) {
             $try ++;
         }         
     }

     //Unknown
     $epoch = unixepoch();
     $url = "$secure://w10$world-sim.kingdom-conquest2.com/socialsv/Login.do";
     $data = "user_id=$userid&language=1&device_type=0&uuid=$uuid&_tm_=$epoch";
     senddata($client,$url,$data);

     
     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/CastleBuildAreaMenu.do";
     $data = "is_first=1&_tm_=$epoch";
     $reply = senddata($client,$url,$data);

     $areas = array();
     foreach($reply->areas as $area){
         if ($area->level < 2)
            $areas[]=$area->id;
     }
     $areas = array_values($areas);
     $select = $areas[rand(0,count($areas)-1)];
     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/CastleBuildAreaSelect.do";
     $data = "area=$select&_tm_=$epoch";
     senddata($client,$url,$data);

     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/ActplayJobChangeExec.do";
     $data = "job_id=1&_tm_=$epoch";
     senddata($client,$url,$data);
     
     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/JobDecision.do";
     $data = "_tm_=$epoch";
     senddata($client,$url,$data);
     
     try
     {
         $epoch = unixepoch();
         $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/OpeningGachaExec.do";
         $data = "_tm_=$epoch";
         senddata($client,$url,$data);
     } catch (Exception $e) {}

     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/MakeCastle.do";
     $data = "_tm_=$epoch";
     senddata($client,$url,$data);

     $epoch = unixepoch();
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/LoginStamp.do";
     $data = "_tm_=$epoch";
     senddata($client,$url,$data);
     
     if ($current < 50) {
        $current++;
        //www.somedodgywebsite.com/images/kc
        $data = "http://www.somedodgywebsite.com/images/kc/kc2ca.php?username=$saveuser&password=$password&device=$device&world=$world&invite=$invite&current=$current&nokcid=$nokcid";
        echo template("Account created<BR /><BR />Username: $username<BR />Password: $password<BR />Nickname: $nick",'kc2_ac.html',$data);
     } else {
         date_default_timezone_set('UTC');
         $now = date('l jS \of F Y h:i:s A');
         file_put_contents('alts_used.txt',"$now Account Created User:$username,Pass:$password,client:$client,world:$world\r\n",FILE_APPEND);

         if (isset($_COOKIE["created"]))
            setcookie("created",$_COOKIE["created"]+1,time()+60*60*24*30);
         else
            setcookie("created",1,time()+60*60*24*30);
         $data = "http://www.somedodgywebsite.com/images/kc/kc2login.html";
         echo template("Account created<BR /><BR />Username: $username<BR />Password: $password<BR />Nickname: $nick<BR />",'kc2_ac.html',$data);
     }
     
     } catch (Exception $e) {
         $err = $e->getMessage();
         echo template("$err<BR /><BR />Username: $username<BR />Password: $password<BR />",'kc2_err.html','');
   }
?>