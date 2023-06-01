<?php
$url = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'])['host']:null;
if (isset($_SERVER['HTTP_REFERER']) &! ($url == 'www.somedodgywebsite.com' || $url=='localhost' || $url=='goodsex'))
{
    echo file_get_contents('kc2login.html');
    exit();
}
require('httpful.phar');

//$account = new account($username,$world);
$cookie = "";

$versions = array('android' => '1.4.2.0', 'apple' => '1.4.2');
$clients = array('android' => 'SEGA Web Client for KC2-Android 2012', 'apple' => 'SEGA Web Client for KC2-iOS 2012');
set_time_limit(60);

$db=null;

  function unixepoch()
  {
      return time() . rand(0,999);
  }
  
  function makehash($type)
          {
              $ret='';
              switch ($type) {
                  case 'android':
                  case 1:
                      while (strlen($ret)<32)
                          $ret .= dechex(rand(0,15));
                      $ret = substr_replace($ret, '-', 8, 0);
                      $ret = substr_replace($ret, '-', 13, 0);
                      $ret = substr_replace($ret, '-', 18, 0);
                      $ret = substr_replace($ret, '-', 23, 0);
                      $ret = strtolower($ret);
                      break;
                  case 'apple':
                  case 2:
                      while (strlen($ret)<32)
                          $ret .= dechex(rand(0,15));
                      $ret = strtoupper($ret);          
                      break;
              }
              return $ret;
          }
  
  function senddata($client,$url,$data){
      /*$url='http://www.test.com';*/
      /*$logon -> autoParse(false);*/
      
      //echo "<BR>$url<BR>$data<BR>";

      global $logon, $response;
      
       if(strpos($url,'invite'))
      {
        $logon = \Httpful\Request::get($url);
        $logon -> addHeader('User-Agent',"Mozilla/5.0 (Linux; Android 4.1.2; SM-T311 Build/JZO54K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.122 Safari/537.36");
      }
      else 
      {
        $logon = \Httpful\Request::post($url);
        $logon -> addHeader('User-Agent',$client);
        $logon -> mime('form');
        $logon -> body($data);
      }
      global $cookie;
      if ($cookie!=''){
        $logon -> addHeader('Cookie','JSESSIONID='.$cookie);
        //echo "Cookie Used: JSESSIONID=$cookie<BR>";
      }
      
      if (strpos($url,'.do')>0)
        $logon -> expectsJson();
      try {
          global $logon, $response;
          $response = $logon -> send();
      }  catch (Exception $e) {
          $logon ->  expectsHtml();
          $response = $logon -> send();
          $result = $response -> raw_body;
          throw new Exception($result);
      }
      global $cookie;
      if (strpos($url,'Login')>0){
          //The server definatly sends cookies that are invalid
          getcookie($response -> raw_headers);
      }
      //getcookie($response -> raw_headers);
      $result = $response -> raw_body;
      $error = errorcode($result);
      if ($error != 0) {
          switch ($error)
          {
            case -3;
                throw new Exception('Server Timeout',$error);
                break;
                
            case 26301;
                throw new Exception('Castle start position invalid.',$error);
                break;
            case 12006;
                throw new Exception('Account Already Exists',$error);
                break;
            case 12000;
                throw new Exception('Login error',$error);
                break;
            case 12007;
                throw new Exception('Bad username / password',$error);
                break;
            case 12013;
                throw new Exception('Wrong device specified',$error);
                break;
            case 61001;
                throw new Exception('Illegal world ID',$error);
                break;
            case 61020:
                throw new Exception('Nickname in use',$error);
                break;
            case 62000:
                throw new Exception('Out of date, please contact the author',$error);
                break;
            default;
                throw new Exception($result,$error);
                break;
          }
      }
      if (strpos($result,'user_id') > 0)
      {          
          $startjson=strpos($result,'{');
          if ($startjson===false)
            $startjson=0;
          $start = strpos($result,'user_id',$startjson) + 9;
          $stop =  strpos($result,',',$start);
          if ($stop - $start > 5)
            throw new Exception('Application error, contact author',0);
          return substr($result,$start,$stop-$start);
      } elseif (strpos($result,'serial=') > 0) {
          $start = strpos($result,'serial=') + 7;
          $stop =  strpos($result,'"',$start);
          return substr($result,$start,$stop-$start);
      }
      else {
          return $response->body;
      }
  }
  
  function errorcode($data){
      $start = strpos($data,'res_code":') + 10;
      if ($start==10) return false;
      $stop =  strpos($data,',',$start);
      return substr($data,$start,$stop-$start);
  }
  
  function getcookie($data) {
      $start = strpos($data,'JSESSIONID=');
      if ($start === false) return false;
      $start +=  11;
      $stop = strpos($data,';',$start);
      global $cookie;
      $cookie = substr($data,$start,$stop-$start);
      //echo "Cookie Set: $cookie<BR>";
  }
  
  function template($echo,$page,$data) {
      $return = file_get_contents($page);
      $return = str_replace('XechoX',$echo,$return);
      $return = str_replace('XdataX',$data,$return);
      if(strpos($data,'kc2login')>0){
          $return = str_replace('XtimeX','60',$return);
          $return = str_replace('<p>Login on next one in 5 seconds</p>','',$return);
          $return = str_replace('<p>Creating next one in 5 seconds</p>','',$return);          
      } else {
          $return = str_replace('XtimeX','5',$return);
      }
      return $return;
  }
  
  function template_better($echo,$page) {
      //$echo = array_values($echo);
      $return=file_get_contents($page);
      for ($a=0;$a<count($echo);$a++){
          $return = str_replace('X'.$a.'X',$echo[$a],$return);
      }
      return $return;
  }
  
  function get_name () {
      $names = file_get_contents('interface/data/names.txt');
      $names = explode(' ',$names);
      $names = array_values($names);
      $return = '';
      $sanity=0;
      do {
          $sanity++;
          $return=$names[rand(0,count($names)-1)];
          if ($sanity>50) break;
          //echo $return.'<BR>';
      } while (strlen($return)>10);
      return ucfirst(strtolower($return));
  }
  
  function iif($test,$true,$false) {
      return $test?$true:$false;
  }

  function get_tickets(){
      
      global $world;
      global $client;
      
      $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/Gacha.do";
      $data = '_tm_='.unixepoch();
      $gatcha = senddata($client,$url,$data);
      
      $ret='';
      
      foreach ($gatcha -> packs as $pack)
      {
          switch ($pack->name) {
              case 'Premium Pack':       //id 801000
              case 'Premium SR Pack':    //id 802000
              case 'Gold Pack':          //id 303000
              case 'Orb':                //id 901000
                 $ret .= $pack->name .' '.$pack->ticket_num.'<BR />';
                 break;
              case 'Crystal Pack':
                 $ret .= $pack->name .' '.$gatcha->resource->crystals.'<BR />';
                 break;
          }
      }
      return $ret;
  }
  
  function draw_pack($id,$count) {
      if ($count==0) return '';
      global $world;
      global $client;
      global $username;
      
      global $allcards;
      $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/GachaExec.do";
      $ret = '';
      
      /*
      if($db==null) {
          require('interface/include/db.php');
          $db = new db();
      }
      */
      
      for ($a=0;$a<$count;$a++){
          $data = "id=$id&idx=1&_tm_=" . unixepoch();
          $winnings = senddata($client,$url,$data);
          $card = '';
          //$db->card_drawn($account,$winnings);
          if (substr($winnings->monster->m_id, -1)=='3') 
            $card = '<bold>SR</bold> ';
          $card .= $allcards[$winnings->monster->m_id];
          $ret .= "$card<BR />";
      }
      return $ret;
  }
  
  function draw_packs(){
     $ret='';
     global $world,$client; 
     $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/Gacha.do";
     $data = '_tm_='.unixepoch();
     $gatcha = senddata($client,$url,$data);
     foreach ($gatcha -> packs as $pack)
     {
        if ($pack->name=='Gold Pack' && $pack->ticket_num > 0)
            $ret.='Gold Pack Draws<BR />'.draw_pack(303000,floor($pack->ticket_num/20));
        if ($pack->name=='Premium Pack Draws' && $pack->ticket_num > 0)
            $ret.='Premium R Pack Draws<BR />'.draw_pack(801000,$pack->ticket_num);
        if ($pack->name=='Premium SR Pack' && $pack->ticket_num > 0)
            $ret.='Premium SR Pack Draw<BR />'.draw_pack(802000,$pack->ticket_num);
     }
     return $ret;
  }
  
  function get_presents(){
      global $world;
      global $client;
      $url = "http://w10$world-sim.kingdom-conquest2.com/socialsv/PresentList.do";
      $lastpage=1;
      $ret = '';
      for ($page=1;$page<=$lastpage;$page++) {
          $data = "page=$page&sort_key=0&sort_type=1&filter_key=-1&filter_rarity=-1&_tm_=" . unixepoch();
          $present_page=senddata($client,$url,$data);
          foreach ($present_page->presents as $item) {
              $ret .= $item->desc.'<BR />';
          }
          $lastpage=$present_page->page_max;
      }                                         
      return $ret;
  }

  /*
    echo $url;
    exit;
*/
  ?>
