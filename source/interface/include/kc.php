<?php
$ref = isset($_SERVER['HTTP_REFERER'])?parse_url($_SERVER['HTTP_REFERER']):null;
$ref = $ref !== null?$ref['host']:null;
// || parse_url($_SERVER['HTTP_REFERER'])['host'] == 'localhost'
if ($ref !== null &! ($ref == 'www.somedodgywebsite.com'|| $ref == 'somedodgywebsite.com' || $ref == 'localhost' || $ref == 'goodsex'))
{
    echo file_get_contents('../kc2login.html');
    exit();
}

//if (!isset($my_cookie)) $my_cookie = new cookie(true);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('zlib.output_compression', 'On');
ini_set('zlib.output_compression_level',6);
//date_default_timezone_set('London');
//$now = date('h:i:s A');
//exit();

const use_proxy = true;

require('include/db.php');
require('include/httpful.phar');
//require('include/random.php');
        
    class utils
    {
        //this return MS from unix epoch. True epoch should be in S
        static function unix_epoch()
        {
          return (int) time() . rand(100,999);
        }

        static function make_hash($type)
        {
          $ret='';
          switch ($type) {
              case 'android':
              case 1:
                  //$ret='f7cebc2c';
                  while (strlen($ret)<32)
                      $ret .= dechex(rand(0,15));
                  $ret = substr_replace($ret, '-', 8, 0);
                  $ret = substr_replace($ret, '-', 13, 0);
                  $ret = substr_replace($ret, '-', 18, 0);
                  $ret = substr_replace($ret, '-', 23, 0);
                  $ret = strtolower($ret);
                  
                  //f7cebc2c-0130-610f-e85f-0cb4b0fa9aa1
                  //$ret = '12312314-2342-4656-6687-ffffffffffff';
                  
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

        static function error_code($data)
        {
          $start = strpos($data,'res_code":') + 10;
          if ($start==10) return false;
          $stop =  strpos($data,',',$start);
          return substr($data,$start,$stop-$start);
        }

        static function get_cookie($data)
        {
          //NOTE : Comment out while to return just the needed cookie
          $start=strpos($data,'Set-Cookie:');
          if (!$start |! strpos($data,'JSESSIONID')) return null;
          $ret='';
          while ($start) {
              $start += 12;
              $stop=strpos($data,';',$start);
              $ret.=substr($data,$start,$stop-$start);
                                
              $start=strpos($data,'Set-Cookie:',$stop);
              if ($start) $ret.=';';
          }
          return $ret;
        }

        static function get_needed_cookie($cookie)
        {
          utils::check_args($cookie);
          $start=strpos($cookie,'JSESSIONID=');
          if ($start!==false)
            $cookie=substr($cookie,$start+11);
          if (strpos($cookie,';')>0)
            $cookie = substr($cookie,0,strpos($cookie,';'));
          if (strlen($cookie)<>40) return null;
          return $cookie;
        }

        static function map_id($x,$y)
        {
          return 2882401 + $x + ($y * 2401);
        }

        /**
         * Return Monster List
         * @return array monsters
         * @param array $monster_data All monsters from core.json kc::$monster_obj
         * @param array $monster_list The accounts monsters $kc->my_monsters_obj
         * @param mixed $race The race wanted either string or int from kc::$race_ids, null for all
         * @param mixed $rarity The rarity wanted either string or int from kc::$rarity_ids, null for all
         * @param mixed $state The state wanted either string or int from kc::$state_ids, null for all
         * @param number $count The count wanted, null for all
         * @param number $level The level wanted, null for all
         * @param number $awake The awakelevel wanted, null for all
         * @param number $m_id The Monster ID wanted, null for all
         * @param bool $is_auction If the card can be sold. null for all.
         * @param number $edition The edition wanted. 0 for standard, -1 for all.
         * @param number $size The size wanted. 0 small, 1 large, null for all
         * @param number $max_cost The MAX cost wanted. null for all.
         * @param number $skills_max_level The MAX skill level wanted. 20 for all.
         */
        static function return_monsters(
                                            array $monster_data,
                                            array $monster_list,
                                            $race=null,
                                            $rarity=null,
                                            $state=null,
                                            $count=null,
                                            $level=null,
                                            $awake=null,
                                            $m_id=null,
                                            $is_auction=true,
                                            $edition=0,
                                            $size=null,
                                            $max_cost=null,
                                            $skills_max_level=1
                                       )
        {
          if (!is_int($race)) $race = utils::get_key($race,kc::$race_ids);
          if (!is_int($rarity)) $rarity = utils::get_key($rarity,kc::$rarity_ids);
          if (!is_int($state)) $state = utils::get_key($state,kc::$state_ids);
          if (!is_int($edition)) $edition = utils::get_key($edition,kc::$edition_ids);
          $ret=array();
          
          //check and correct passing of not jist the monster array
          if (isset($monster_list->monsters))
            $monster_list = $monster_list->monsters;
          
          foreach ($monster_list as $monster)
            $ret[]=$monster;

          if ($rarity!==null)
            foreach ($ret as $key => $value)
                if ($monster_data[$value->m_id]->rarity != $rarity)
                    unset($ret[$key]);
          
          if ($race!==null)
            foreach ($ret as $key => $value)
                if ($monster_data[$value->m_id]->race != $race)
                    unset($ret[$key]);
                       
          if ($state!==null)
            foreach ($ret as $key => $value)
                if ($value->uniq_data->state != $state)
                    unset($ret[$key]);
                    
          if ($count!==null)
            foreach ($ret as $key => $value)
                if ($value->uniq_data->hc !== $count)
                    unset($ret[$key]);

          if ($level!==null)
            foreach ($ret as $key => $value)
                if ($value->uniq_data->level != $level)
                    unset($ret[$key]);
          
          if ($awake!==null)
            foreach ($ret as $key => $value)
                if ($value->uniq_data->awake_exp != $awake)
                    unset($ret[$key]);
          
          if ($m_id!==null)
            foreach ($ret as $key => $value)
                if ($value->m_id != $m_id)
                    unset($ret[$key]);
                    
          if ($is_auction!==null)
              foreach ($ret as $key => $value)
                if (kc::$monster_obj[$value->m_id]->is_auction != $is_auction)
                    unset($ret[$key]);
          
          if ($edition!==-1)
              foreach ($ret as $key => $value)
                  if ((isset($value->edition) && $value->edition->type !== $edition) ||
                      ($edition == 0 && isset($value->edition)))
                        unset($ret[$key]);

          if ($size!==null)
              foreach ($ret as $key => $value)
                  if (kc::$monster_obj[$value->m_id]->size !== $size)
                        unset($ret[$key]);

          if ($max_cost!==null)
            foreach ($ret as $key => $value)
                if (kc::$monster_obj[$value->m_id]->cost > $max_cost)
                    unset($ret[$key]);
          
        foreach ($ret as $key => $value)
            if (($value->skills[0]->lv > $skills_max_level) || ($value->skills[1]->lv > $skills_max_level) || ($value->skills[2]->lv > $skills_max_level))
                unset($ret[$key]);

          return array_values($ret);
        }
        
        static function remove_dups(array &$monster_list)
        {
            foreach($monster_list as $key => $monster)
            {
                $count=0;
                $u_id=$monster->uniq_data->u_id;
                foreach ($monster_list as $test)
                {
                    if ($test->uniq_data->u_id == $u_id) {
                        $count++;
                    if ($count > 1)
                        unset($monster_list[$key]);
                    }
                }
            }
            return $monster_list;
        }
        
        static function name_monsters(array $list)
        {
            $ret=array();
            foreach ($list as $item)
                $ret[]=kc::$monster_obj[$item->m_id]->name;
            return $ret;
        }
        
        static function save_val($filename,$val)
        {
            file_put_contents($filename,$val);
        }
        
        static function load_val($filename)
        {
            if (!file_exists($filename))
                return null;
            return file_get_contents($filename);
        }
        
        static function save_data($filename,$argh,$overwrite=false)
        {
          if (!$overwrite && file_exists($filename) && filemtime($filename) > time() - 86400) //60 * 60 * 24 (One Day) time is SECONDS from epoch
            return;
          $out='';
          foreach ($argh as $key => $value)
            $out.="$key,$value\r\n";
          file_put_contents($filename,$out);
        }

        static function load_data($filename)
        {
          $ret = null;
          $data=file_get_contents($filename);
          $data=explode("\r\n",$data);
          foreach($data as $item)
          {
              $pair=explode(',',$item);
              if (!isset($pair[1])) break;
              $ret[$pair[0]] = $pair[1];
          }
          return $ret;
        }

        static function is_json($string)
        {
            if ($string==null) return false;
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }
        
        static function save_json($json,$filename)
        {
          if (file_exists($filename) && filemtime($filename) > time() - 86400) //60 * 60 * 24 (One Day)
            return;
          file_put_contents($filename,json_encode($json,true));
        }

        static function load_json($filename)
        {
          return json_decode(file_get_contents($filename));
        }

        static function make_monster_array($json)
        {
          $ret = array();
          foreach ($json->monsters as $monster)
              $ret[$monster->id] = $monster;
          return $ret;
        }

        static function make_skill_array($json)
        {
          $ret = array();
          foreach ($json->skills as $skill)
              $ret[$skill->id] = $skill;
          return $ret;
        }
        
        public static function make_skill_string($skills)
        {
            $ret='';
            foreach($skills as $skill)
                if ($skill->id>0)
                    $ret.=kc::$skills_obj[$skill->id]->name . ' (' .$skill->lv . ') ';
            return $ret;
        }

        public static function get_key($name,$type)
        {
          foreach ($type as $key=>$value)
            if ($value == $name)
                return $key;
        }
        
        public static function get_card($name,$rarity=null,$is_auction=true)
        {
            if ($rarity==-1) $rarity=null;
            if ($rarity !== null && !is_int($rarity)) $rarity = utils::get_key($rarity,kc::$rarity_ids);
            foreach (kc::$monster_obj as $card)
                if ($card->name==$name && $card->is_auction == $is_auction && ($rarity==null || $card->rarity == $rarity))
                    return $card;
            throw new Exception('get_card: Card not found');
        }

        public static function check_args($args)
        {
          if (count($args)>1)
              foreach ($args as $arg)
                if ($arg===null)
                    throw new Exception('Argh not passed to function<BR />'.var_dump(debug_backtrace()));
          else
                if ($args===null)
                    throw new Exception('Argh not passwed to function<BR />'.var_dump(debug_backtrace()));
        }

        //returns sorted array low to high
        public static function tutorial_attack_square($map_info,$start_x,$start_y)
        {
          //echo "Start $start_x,$start_y<BR />";
          $distance=null;
          $ret=null;
          foreach ($map_info->fields as $field) 
          {
              if (!$ret) {
                $ret=$field;
                $distance=utils::distance_to($start_x,$start_y,$field->x,$field->y);
                //echo "distance $distance<BR />";
              }
              elseif ($field->user_id==0 && $field->type == 0 &&
                    ((utils::distance_to($start_x,$start_y,$field->x,$field->y) < $distance && $field->lev <= $ret->lev) || 
                    ($field->lev==1 && $distance < 2) ) ) {
                        $ret=$field;
                        //echo $ret->x.','.$ret->y.' Picked <br />';
                        $distance=utils::distance_to($start_x,$start_y,$field->x,$field->y);
                        //echo "distance $distance<BR />";
                    }
          }
          return $ret;
        }      
        
        public static function sort_by_m_id($a,$b)
        {
            if ($a->m_id > $b->m_id) return 1;
            elseif ($a->m_id < $b->m_id) return -1;
            else return 0;
        }

        //returns sorted array high to low
        public static function sort_by_skill_xp($a,$b)
        {
            $a_skill_xp = utils::skill_xp($a);
            $b_skill_xp = utils::skill_xp($b);
            if ($a_skill_xp < $b_skill_xp) return 1;
            elseif ($a_skill_xp > $b_skill_xp) return -1;
            else return 0;
        }
        
        //returns array sorted race then name
        public static function sort_by_RnN($a,$b)
        {
            if (kc::$monster_obj[$a->m_id]->race < kc::$monster_obj[$b->m_id]->race) return 1;
            if (kc::$monster_obj[$a->m_id]->race > kc::$monster_obj[$b->m_id]->race) return -1;
            if (substr(kc::$monster_obj[$a->m_id]->name,0,1) > substr(kc::$monster_obj[$b->m_id]->name,0,1)) return 1;
            if (substr(kc::$monster_obj[$a->m_id]->name,0,1) < substr(kc::$monster_obj[$b->m_id]->name,0,1)) return -1;
            if (substr(kc::$monster_obj[$a->m_id]->name,1,1) > substr(kc::$monster_obj[$b->m_id]->name,1,1)) return 1;
            if (substr(kc::$monster_obj[$a->m_id]->name,1,1) < substr(kc::$monster_obj[$b->m_id]->name,1,1)) return -1;
            return 0;
        }
        
        public static function skill_xp($monster)
        {
            return  $monster->skills[0]->exp + $monster->skills[1]->exp + $monster->skills[2]->exp;
        }

        public static function distance_to($start_x,$start_y,$finish_x,$finish_y)
        {
          return sqrt( pow(($start_x - $finish_x),2)  +  pow(($start_y - $finish_y),2));
          //echo "($start_x,$start_y) ($finish_x,$start_y) $ret <BR />";
        }

        public static function iif($test,$true,$false)
        {
          return $test?$true:$false;
        }
        
        //return float ping time in ms
        public static function pingDomain($domain)
        {
            try
            {
                $starttime = microtime(true);
                $file      = fsockopen ($domain, 80, $errno, $errstr, 10);
                $stoptime  = microtime(true);
                $status    = 0;

                if (!$file) $status = -1;  // Site is down
                else {
                    fclose($file);
                    $status = ($stoptime - $starttime) * 1000;
                    //$status = floor($status);
                }
                return $status;
            } catch (Exception $ex)
            {
                //unable to connect
                return 100;
            }
        }
        
        private static $error_msg = array ('Unable to parse response code from HTTP response due to malformed response' => true,
                                           'Connection refused' => true,
                                           'Unable to connect' => false,
                                           'Connection timed out' => false,
                                           'Recv failure: Connection reset by peer' => false,
                                           'Empty reply from server' => false,
                                           'Unable to parse response as JSON' => false);
        
        public static function is_fatal($error_message)
        {
            foreach (utils::$error_msg as $message => $fatal)
                if (strpos($error_message,$message)!==false) return $fatal;
            throw new Exception("Unlisted error $error_message");
        }
        
        public static function get_page($url,$get=true,$cookie=null,$data=null,$proxy=null,$speed_test=false,$timeout=null)
        {
            $sanity=0;
            do 
            {
                try
                {
                    $request=$get?\Httpful\Request::get($url):\Httpful\Request::post($url);
                    $request -> addHeader('User-Agent','Mozilla/5.0 (Linux; Android 4.1.2; GT-I8190 Build/JZO54K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.131 Mobile Safari/537.36');
                    if ($cookie!==null)
                        $request -> addHeader('Cookie',$cookie);
                    $request -> mime('form');
                    $request -> body($data);
                    if ($proxy!==null)
                        $request -> useProxy($proxy['host'],$proxy['port']); 
                    if ($timeout!==null)
                        $request -> timeout($timeout);
                    return $request -> send();
                }
                catch (Exception $ex)
                {
                    if ($speed_test|| utils::is_fatal($ex->getMessage()))
                        return $ex;
                    $sanity++;
                }
            }
            while (!$speed_test && $sanity<5);
            return null;
        }
        
        private static function calc_port($xor,$vars)
        {
            $xor = str_replace('(',null,$xor);
            $xor = str_replace(')',null,$xor);
            if (substr($xor,0,1)=='+')
                $xor = substr($xor,1);
            $parts=explode('+',$xor);
            for ($a=0;$a<count($parts);$a++)
            {
                $bits=explode('^',$parts[$a]);
                $result='';
                $found=false;
                foreach($bits as &$bit)
                    if (!is_numeric($bit)) {
                        $bit=$vars[$bit];
                        $found=true;
                    }
                $parts[$a] = implode('^',$bits);
                if ($found) $a--;
            }
            $res=null;
            foreach ($parts as $part)
            {
                $number = explode('^',$part);
                $res .= $part[0] xor $part[1] xor $part[2];
            }
            return $res;
        }

        
        public static function get_proxies(db $db,$random=true)
        {
            $from_db=$db->get_proxies();
            if ($random && count($from_db)>1) shuffle($from_db);
            if (count($from_db)>=20) return $from_db;
            if (count($from_db)==0)
                $from_db = $db->get_proxies(true);
            $a=0;
            for ($xf1=1;$xf1<5;$xf1++)
                for ($xf2=1;$xf2<3;$xf2++)
                    for ($xf4=1;$xf4<4;$xf4++)
                    {
                        do {
                            $page=utils::get_page('http://spys.ru/en/http-proxy-list/',false,null,"xpp=3&xf1=$xf1&xf2=$xf2&xf4=$xf4",$from_db[$a]); //$from_db[$a] xf2=0 All xf2=1 HTTPS xf2=2 HTTP
                            $a++;
                            if ($a == count($from_db))
                                break;
                        } while (strpos(get_class($page),'Exception') !== false || strlen($page->raw_body) < 1000 || strpos($page->raw_body,'403 Forbidden') > 0);
                        if ($page === null || strlen($page->raw_body) < 1000)
                            throw new Exception('Error getting proxies from server.');
                        
                        $search='type="text/javascript">';
                        $start=0;
                        for ($a=0;$a<3;$a++) {
                            $start=strpos($page,$search,$start)+strlen($search);
                        }
                        $length=strpos($page,'</script>',$start)-$start;
                        $vars_string=substr($page,$start,$length);
                        $vars=array();
                        $vars_string=explode(';',$vars_string);
                        foreach ($vars_string as $var)
                            $vars[explode('=',$var)[0]]= explode('=',$var)[1];

                        $frags=explode('<font class=spy1',$page->raw_body);
                        $matchA=null;
                        $matchB=null;
                        $last=null;
                        foreach($frags as $frag)
                        {
                            $valid = preg_match('/.(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $frag,$matchA);
                            preg_match('/.(\([a-z.0-9,^,(,),+]+)/', $frag,$matchB);
                            if ($valid && $last !== $matchA[1] && count($matchB)>0) {
                                $last = $matchA[1];
                                $port=utils::calc_port($matchB[1],$vars);
                                $proxies[]=array('host'=>$matchA[1],'port'=>$port,'health'=>0);
                            }
                        }


                        if (count($proxies)==0) throw new Exception('No proxies found');
                        elseif (count($proxies)>0)
                            $db->add_proxies($proxies);
                        
                    }
            $proxies = $db->get_proxies();
            if ($random) shuffle($proxies);
            return $proxies;
        }
        
        public static function is_enabled()
        {
            if (substr(getcwd(),-9)=='interface' && !file_exists('enabled.txt')) {
                echo 'Site Disabled';
                return false;
            }
                
            return true;
        }
    }
    
    class cookie
    {
        public $session;
        
        public $username;
        public $password;
        public $device;
        //public $device_type;
        public $world;
        public $user_id;
        
        public $expires_in =  3600; //1 hr
        
        public $arena;
        public $batch;
        public $current;
        public $streak;
        public $draw;
        public $tickets;
        public $presents;

        public $batgig;
        
        public function __construct($reset=false)
        {
            foreach ($this as $key => $value) $this -> $key = $this -> load_var($key);

            if ($reset)
            {
                $this->current=-1;
                $this->streak=0;
                //$this->delete_cookies();
                $this->session=isset($_COOKIE['kc&sessions'])?$_COOKIE['kc&sessions']+1:1;
                setcookie('kc&sessions',$this->session,time()+(60*60*2));
                
                unset($_POST['reset_cookie']);
            }
            
            if (isset($_GET['session']))
                $this->session=$_GET['session'];
                

        }
        
        public function is_global($var)
        {
            return (isset($_POST[$var]) || isset($_GET[$var]));
        }
        
        public function set_if_unset($varname,$value,$search)
        {
            if (!$this->is_global($search))
                $this->$varname=$value;
        }
        
        public function set_var($var_name,$var_value)
        {
            $this->$var_name = $var_value;
        }
        
        public function set_cookie()
        {
            $session = $this->session;
            foreach ($this as $key => $value)
                setcookie("kc&$session&$key", $value, time()+(60*60*2));
        }
        
        public function set_post_get()
        {
            foreach ($this as $key => $value)
            {
                $_POST[$key] = $value;
                $_GET[$key] = $value;
            }
        }
        
        public function exists ()
        {
            foreach ($_COOKIE as $key => $value)
            {
                if (strlen($key) < 6 || substr($key,0,3) !== 'kc_')
                    continue;
                if ($value !== null)
                    return true;
            }
            return false;
        }
        
        public function get_cookie()
        {
            foreach ($_COOKIE as $key => $value)
            {
                if (strlen($key) < 6 || substr($key,0,3) !== 'kc&')
                    continue;
                if (substr($key,3,strpos($key,'&',3)-3) !== $this->session)
                    continue;
                $key = substr($key,strpos($key,'&',4)+1);
                if (!isset($this -> $key) || (isset($this -> $key) && $this -> $key === null))
                {
                    if ($value==='true') $value = true;
                    elseif ($value==='false') $value = false;
                    $this -> $key = $value;
                }
            }
            return $this;
        }
        
        public function delete_cookies()
        {
            foreach ($_COOKIE as $key)
                if (substr($key,0,3) == 'kc&' && $key !== 'kc&session')
                    setcookie($key, null, time()-1);
        }
        
        private function load_var($var)
        {
            $ret=null;
            if (isset($_GET[$var]))
                $ret=$_GET[$var];
            if (isset($_POST[$var]))
                $ret=$_POST[$var];
            if ($ret==='true')
                $ret=true;
            elseif ($ret==='false')
                $ret=false;
            return $ret;
        }
    }
    
    class timer
    {
        private $start=0;
        private $stop=0;
        private $count=0;
        
        public function __construct($start=false)
        {
            if ($start) $this->start = microtime(true);
        }
        
        public function start()
        {
            $this->start = microtime(true);
        }
        
        //returns count as float ms
        public function stop()
        {
            $this->stop = microtime(true);
            $this->count = ($this->stop - $this->start) * 1000;
            return $this->count;
        }
        
        public function restart()
        {
            $this->count=0;
            $this->start();
        }
        
        //returns ms leaving timer running
        public function get_time()
        {
            return (microtime(true) - $this->start) * 1000;
        }
    }

    class kc
    {
        public static $versions = array('android' => '1.4.8.0', 'apple' => '1.4.8');
        public static $clients = array('android' => 'SEGA Web Client for KC2-Android 2012', 'apple' => 'SEGA Web Client for KC2-iOS 2012');
        
        public $proxies = array();
        public $proxies_number=0;
        
        public static $pos_masks = array (1 => 'FL',
                                          2 => 'FM',
                                          4 => 'FR',
                                          8 => 'BL',
                                          16 => 'BM',
                                          32 => 'BR',
                                          9 => 'FLBL',
                                          18 => 'FMBM',
                                          36 => 'FRBR');
        public static $pos_masks_set = array (0 => 'FL',1 => 'FM',2 => 'FR',3 => 'BL',4 => 'BM',5 => 'BR');

        public static $race_ids = array(0=>'bug',1=>'beast',2=>'dhuman',3=>'undead',4=>'spirit',5=>'cryptid',6=>'giant',7=>'demon',8=>'dragon',9=>'enhance',10=>'?');
        public static $rarity_ids = array(0=>'common',1=>'uncommon',2=>'rare',3=>'superrare');
        public static $state_ids = array(0=>'inpool',1=>'idle',2=>'fighting',3=>'returning',4=>'auction',5=>'reinforcing',6=>'building');
        public static $auction_ids = array(0=>'unknown',1=>'already_bid',2=>'unknown',3=>'unknown',4=>'normal',5=>'maxbids',6=>'tooexpensive',7=>'bin');
        public static $edition_ids = array(0=>'',1=>'Σ',2=>'Ψ',3=>'I');
        public static $auction_sort_key=array(1=>'finalbid',2=>'bids',3=>'billingperiod',4=>'name',5=>'level',6=>'skilllv',7=>'atk',8=>'def',9=>'int',10=>'cost',11=>'rarity',12=>'spd',13=>'siege',14=>'range',15=>'edition');
        public static $auction_sort_type=array(1=>'down',2=>'up');

        public static $campain_codes = array('MWC2014'=>'NM66ej6jBahzaX2x');
        //this gets filed when the shop data gets read.
        public static $pack_ids = array(301000=>'crystal');
        //TODO:Complete this list
        public static $present_ids = array(9=>'monsters',200=>'other');
        public static $core_data;
        //Array of ID,Name
        public static $tiles = array();
        public static $blue_crystals = array('Residence','Mansion','Shop','Great Shop','Cemetery'); //Dispensary ?
        public static $monsters = array();
        public static $monster_obj = array();
        public static $skills_obj = array();
        
        public static $server_time;
        public static $server_time_calc;
        public static $my_time;
        public static $game_time;
        public static $game_time_calc;
        public static $travel_time;
        public static $time_offset;
                
        public $cookie;
        public $login_cookie;
        public $db;
        
        public $batgig=true;
        public $is_cron;

        public $ingame_name;
        public $town;
        public $unit_data;
        public $shop_data;
        public $resource_data;
        public $commander_data;
        public $commander_hire_data;
        public $login_stamp_data;
        public $present_data;
        public $present_page_max;
        public $duel_data;
        public $arena_data;
        public $auction_sell_data;
        public $auction_bid_data;

        public $my_monsters = array();
        public $my_monsters_obj = array();
        public $debug_array = array();
        
        public $homebase_id=0;
        public $login_day='?';
        public $is_tutorial='?';
        public $tutorial_stage='?';
        public $max_listings='?';
        
        public $cp='?';
        public $dp='?';
        public $current_bids='?';
        public $current_sales='?';
        
        public $wood='?';
        public $stone='?';
        public $iron='?';
        public $crystals='?';
        
        public $constuct_max = '?';
        public $constuct_current = '?';
        
        public $username;
        public $password;
        public $device;
        public $device_type;
        public $version;
        public $world;
        public $user_id;
        public $account;
        public $proxy;
        
        function __construct($username,$password,$device,$world,$cookie=null,$user_id=null,$batgig=true,$is_cron=false)
        {
            $this->connect_db();
            $account = null;
            if ($cookie !== null)
            {
                $cookie=utils::get_needed_cookie($cookie);
                if (strlen($cookie) != 40)
                    throw new Exception('Invalid Cookie');
                $account = $this->db->get_account(null,$cookie);
                if (isset($account['name']))
                {
                    $username=$account['name'];
                    $password=$account['password'];
                    $device=$account['android']=1?'android':'apple';
                    $world=$account['world'];
                }
            }
            
            set_time_limit(1200);
            $this->batgig = $batgig;
            $this->is_cron=$is_cron;
            $this->username=$username;
            $this->password=$password;
            $this->device=kc::$clients[$device];
            $this->version=kc::$versions[$device];
            $this->device_type=$device;
            $this->world=$world;
            $this->cookie=$cookie;
            $this->user_id=$user_id;
            
            kc::$my_time = (microtime(true) * 1000);

            $this->proxies=utils::get_proxies($this->db,$is_cron);
            if (count($this->proxies)==0)
                throw new Exception('No Proxies');
            $this->proxy = $this->proxies[$this->proxies_number];
            
            $this->account = new account($username,$password,$world,$device);
            $this->account->batgig = $batgig;
            $db_account = $account === null ? $this->db->get_account($this->account) : $account;
            if ($db_account===null)
                $this->account->auto=false;
            $this->account->cookie = $this->cookie;
            $this->account->user_id=$user_id;
            
            if ($cookie == null && ($username == null && $password == null))
                throw new Exception('You need either a cookie or username and password to create this object');
            if ($device == null || $world == null)
                throw new Exception('Device or World is null');
            
            if (isset($db_account['name'])) //get details from db
            {
                $this->account->auto=(bool)$db_account['auto'];
                $this->account->auto_login=$db_account['auto_login'];
                $this->account->auto_arena=(bool)$db_account['auto_arena'];
                
                $this->account->name=$db_account['name'];
                $this->account->password=$db_account['password'];
                $this->account->world=$db_account['world'];
                $this->account->nickname=$db_account['nickname'];
                $this->account->user_id=$db_account['user_id'];
                $this->account->uuid=$db_account['uuid'];
                $this->account->tutorial=$db_account['tutorial'];
                $this->account->campain = (bool)$db_account['campain'];
                $this->day=$db_account['day'];
                $this->cp=$db_account['cp'];
                $this->dp=$db_account['dp'];
            }

            if ($cookie || $user_id)
                $this->load_core_data();
        }
        
        public function __destruct()
        {
            $this->db->close();
        }
        
        public function send_data($url,$data='')
        {

            $response=null;
            $world=$this->world;
            $epoch=utils::unix_epoch();
            $sent_url=$url;
            if ($url=='StartUpCheck.do' || $url=='Auth.do')
                $url="http://common.kingdom-conquest2.com/socialsv/common/$url";
            elseif ($url=='Logon.do')
                $url="http://w10$world-sim.kingdom-conquest2.com/socialsv/Login.do";
            else
                $url="http://w10$world-sim.kingdom-conquest2.com/socialsv/$url";
            if($data!='') $data.='&';
            $data.='_tm_='.$epoch;
            $sanity_null=0;
            //Loop to get around null data being sent
            do
            {
                $request = \Httpful\Request::post($url);
                $request -> addHeader('User-Agent',$this->device);
                $request -> mime('form');
                $request -> body($data);
                if ($this->cookie!='')
                    $request -> addHeader('Cookie','JSESSIONID='.$this->cookie); //'JSESSIONID='.
                $request -> expectsJson();
                $error=true;
                $error_msg='';
                //Loop to address connection issues
                $sanity_connection=0;
                while ($error)
                {
                    if (!utils::is_enabled()) exit();
                    
                    if (use_proxy && $this->proxy===null)
                        throw new Exception('No Proxy');
                    elseif (use_proxy)
                        $request -> useProxy($this->proxy['host'],$this->proxy['port']);

                    $wait_add=$this->proxy['health']>5?30:0;
                    switch ($sent_url)
                    {
                        case 'StartUpCheck.do':
                            $request -> timeout(20+$wait_add);
                            break;
                        case 'Login.do':
                            $request -> addHeader('Cookie',$this->login_cookie);
                        case 'Auth.do':
                            $request -> timeout(60+$wait_add);
                            break;
                        default:
                            $request -> timeout(45+$wait_add);
                    }
                    try
                    {
                        $this->debug_array[]=date('h:i:s A'). ' '. $this->proxy['host'] . ':' . $this->proxy['port'] . " $url $data";
                        file_put_contents('last_message.txt',$this->username."\r\n". implode("\r\n",$this->debug_array));

                        //echo 'start';
                        $sanity_connection++;
                        $timer = new timer(true);
                        
                        $response = $request -> send();
                        
                        $resp_time = $timer->stop();
                        $health_add=0;
                        if ($resp_time < 2500)
                            $health_add=3;
                        elseif ($resp_time < 5000)
                            $health_add=2;
                        else
                            $health_add=1;
                        $sanity_connection--;
                        $error=false;
                        $this->proxy['health'] = $this->proxy['health'] + $health_add;
                        $this->db->update_proxy($this->proxy,'+'.$health_add);
                        
                    }  catch (Exception $e) {
                        //ERROR
                        $health_mod=-1;
                        if ($this->proxy['health']>1000)
                            $health_mod = -50;
                        elseif ($this->proxy['health']>500)
                            $health_mod = -25;
                        elseif ($this->proxy['health']>100)
                            $health_mod = -5;
                            
                        $error_msg = $e->getMessage();
                        
                        if ($error_msg=='Unable to parse response as JSON')
                        {
                            $request -> expectsHtml();
                            try {$response = $request -> send();}
                            catch (Exception $ex)
                            {
                                if(utils::is_fatal($ex->getMessage()))
                                    $this->proxy_failed(true);
                                else
                                    throw new ErrorException("$url $data ".$ex->getMessage());
                            }
                        }
                        elseif (utils::is_fatal($error_msg))
                            $this->proxy_failed(true);
                        else
                            $this->proxy_failed(false,$health_mod);
                    }
                    if ($sanity_connection>30)
                        throw new Exception("Can't Connect"); //$url with $data $error_msg
                    elseif (use_proxy && $sanity_connection>25)
                    {
                        $good_proxies = utils::get_proxies($this->db,false);
                        if (count($good_proxies) < 5)
                            throw new Exception("No good proxies"); // for $url with $data $error_msg
                        $good_proxies = array ($good_proxies[0],$good_proxies[1],$good_proxies[2],$good_proxies[3],$good_proxies[4]);
                        shuffle($good_proxies);
                        $this->proxy=$good_proxies[0];
                    }
                } 

                //Get user ID
                if ($sent_url=='Auth.do' && utils::error_code($response -> raw_body) == 0) {
                    $this->user_id=$response->body->user_id;
                    $this->account->user_id=$response->body->user_id;
                    $this->login_cookie=substr($response->headers['set-cookie'],0,strpos($response->headers['set-cookie'],';')+1);
                }
                    
                //The server sends cookies that are invalid. Login.do is valid Auth.do takes user password Login just user id.
                if ($sent_url=='Login.do')
                    $this->cookie = utils::get_needed_cookie($response->raw_headers);

                //Throw error is there is an error
                $error = utils::error_code($response -> raw_body);
                if ($error != 0) {
                  switch ($error)
                  {
                    case -3:
                        $this->account->cookie='';
                        $this->db->add_account($this->account);
                        throw new Exception('Server Timeout',$error);
                    case -4:
                        $this->account->cookie='';
                        $this->db->add_account($this->account);
                        throw new Exception('Maintenance',$error);
                    case -6:
                        $this->account->cookie='';
                        $this->db->add_account($this->account);
                        throw new Exception('Login status voided. Please log in again.',$error);
                    case 26301:throw new Exception('Castle start position invalid.',$error);
                    case 12006:throw new Exception('Account Already Exists',$error);
                    case 12000:throw new Exception('Login error',$error);
                    case 12007:throw new Exception('Bad username / password',$error);
                    case 12013:throw new Exception('Wrong device specified',$error);
                    case 61001:throw new Exception('None existant world ID',$error);
                    case 61020:throw new Exception('Nickname in use',$error);
                    case 62000:throw new Exception('Out of date, please contact the author',$error);
                    case 25023:
                        $this->max_listings=true;
                        throw new Exception('Up to ten monsters can be auctioned at  the same time. (25023)',$error);
                    case 12008:
                        $this->account->uuid=utils::make_hash($this->device_type);
                        $this->db->add_account($this->account);
                        throw new Exception('Account Locked. Try again in a couple of hours. (12008)',$error);
                    default:
                        $this->write_debug($response -> raw_body);
                        throw new Exception($response -> raw_body,(int)$error);
                  }
                }
                
                /*
                When I was coding batgig I found that the server sometimes
                returns null data. The client must handle this and send the request
                again. This is my implementation of that. I guess the httpful class
                returns a stdObject with no properties in this eventuality
                get_object_vars() returns null if the var is not an object ref or
                an array of name and values if it is.
                */
                $json_props = $response->body == null ?null:get_object_vars($response->body);
                if ($json_props === null || count($json_props) == 0)
                {
                    //file_put_contents('kc_error.txt',"send_data $url $data\r\nBody<" . var_dump($response->body).">\r\n",FILE_APPEND);
                    $this->proxy_failed(true); //it didn't but the kc server got suspisious so move to another proxy
                    $sanity_null++;
                }
                if ($sanity_null > 4) throw new Exception($this->proxy['host'] ." $url $data\r\nServer sending null data");
            } while ($response->raw_body === null || $json_props === null || count($json_props) == 0 || !utils::is_json($response->raw_body));
            return $response->body;
        }
        
        private function proxy_failed($force=false,$health_mod=-1)
        {
            //file_put_contents('proxy_failed.txt',date('h:i:s A').' '.$this->proxy['host'] .' '. $this->username ."\r\n",FILE_APPEND);
            $reduce=$force?-50:$health_mod;
            $this->proxy['health']-=$reduce;
            $this->db->update_proxy($this->proxy,$reduce);
            //if ($this->proxy['health'] > 0 && !$force) return $this->proxy;
            $this->proxies_number++;
            if (!isset($this->proxies[$this->proxies_number]) || $this->proxies_number >= count($this->proxies))
            {
                //echo 'proxy failed<br>';
                $this->proxies=utils::get_proxies($this->db,$this->is_cron);
                $this->proxies_number=0;
            }
            $this->proxy = $this->proxies[$this->proxies_number];
            return $this->proxy;
        }
        
        private static $remove_html = array('<br />','<BR />','<BR>','<br>');
        private function write_debug($error_string)
        {
            foreach (kc::$remove_html as $remove)
                $error_string = str_replace($remove,' ',$error_string);
            $put='';
            foreach ($this->debug_array as $step)
                $put .= $step."\r\n";
            file_put_contents('kc_debug.txt',date('h:i A').' Debug trace '.$this->username."\r\n$error_string\r\n$put\r\n",FILE_APPEND);
        }
        
        public function __toString()
        {
            $ret='';
            $ret.='Username: '.$this->username."<br />\r\n";
            $ret.='Password: '.$this->password."<br />\r\n";
            $ret.='Device: '.$this->device."<br />\r\n";
            $ret.='World: '.$this->world."<br />\r\n";
            $ret.='CP: '.$this->cp."<br />\r\n";
            $ret.='DP: '.$this->dp."<br />\r\n";
            $ret.='Bids: '.$this->current_bids."<br />\r\n";
            $max_listings = $this->max_listings ? 'True' : 'False';
            $ret.='Max Listings: '.$max_listings."<br />\r\n";
            $arena_goes=isset($this->duel_data->remain)?$this->duel_data->remain:'?';
            $ret.="Arena Goes: $arena_goes<br />\r\n";
            $tutorial = $this->is_tutorial ? 'True' : 'False';
            $ret.='Tutorial: '.$tutorial."<br />\r\n";
            $ret.='Tutorial Stage: '.$this->tutorial_stage."<br />\r\n";
            $ret.='WSI: '.$this->wood.' '.$this->stone.' '.$this->iron.' '."<br />\r\n";
            if (count($this->my_monsters) > 0) {
                $ret.="<br />\r\nMonster Count: ".count($this->my_monsters)."<br />\r\n";
                //can't sort the index is the u_id
                //usort($this->my_monsters_obj,'utils::sort_by_m_id');
                foreach($this->my_monsters as $u_id => $monster_id)
                    $ret.=kc::$monster_obj[$monster_id]->name.' '.$this->my_monsters_obj[$u_id]->uniq_data->awake_exp."<br />\r\n";
            }
            return $ret;
        }
        
/*        
        private function bug_killer($obj,$test)
        {
            //this is disabled
            return true;
            if (!isset($obj->$test))
            {
                file_put_contents('kc_error.txt',"bug_kill\r\n" . var_dump($obj)."\r\nEnd\r\n",FILE_APPEND);
                return false;
            }
            return true;
        }
*/

        public function connect_db()
        {
            if ($this->db==null)
                $this->db = new db();
            return $this->db;
        }

        public function logon($cid=null,$serial=null)
        {
            
            $username=$this->username;
            $password=$this->password;
            $version=$this->version;
            
            //$travel_time=$this->get_travel_time();
            kc::$my_time = (microtime(true) * 1000);// + $travel_time;
            
            if (!$this->account->campain)
            {
                
                //This code is to collect the serial number from campains
                
                /*
                //$data="keyword=MWC2014&cid=kc2_keyword&lang=ja&ap_ref=";
                //$data="campaign_code=6751246232461435&cid=kc2_sgnfanbook&lang=ja";
                //$page=utils::get_page('http://pre.sega-net.jp/keyword/input?cid=kc2_keyword&lang=ja',false,null,$data);
                $page=utils::get_page('http://pre.sega-net.jp/page/access?campaign_code=6751246232461435&cid=kc2_sgnfanbook&lang=ja',true,null,null,$this->proxy);
                $cookie=$page->headers['set-cookie'];
                //$page=utils::get_page('https://pre.sega-net.jp/page/access?campaign_code=NM66ej6jBahzaX2x&cid=kc2_niconam_02&lang=ja',true,$cookie);
                $start=strpos($page->raw_body,'serial=')+7;
                $finish=strpos($page->raw_body,'"',$start);
                $serial=substr($page->raw_body,$start,$finish-$start);
                */
                
                //Beginner event                
                //$cid='kc2_sgnfanbook';
                //$serial='46330cc9bff83600d7f763461070bba0640e322c';

                //Lister mwc final
                //$cid='kc2_mwc2014nico';
                //$serial='c0b45966068353332733054cdce2cd21944b3391';
                
                //the event "Listener" used the same serial and cid for all.
                //$serial='bcd89bee93c9bf9ee3eaebaa5ed3b4dc3db46797';
                //$cid='kc2_niconam_02';
                //*/
                
                //Wizardry Schema
                $cid='kc2_wizs_01';
                $serial='80b8abf111538aa130f6a47edcd61018a8de871b';
            }

            $db_account=$this->db->get_account($this->account);
            if (isset($db_account['timestamp']) && $db_account['timestamp'] > time()-60*15 && $db_account['cookie']!=='' && $serial===null)
            {
                $this->load_core_data();
                $this->cookie = $db_account['cookie'];
            }
            else
            {
                $data = "language=1&check_code=$version";
                $this->send_data('StartUpCheck.do',$data);

                $world=$this->world;
                try {
                    $data = "world_id=10$world&kc_id=$username&password=$password&language=1&check_code=$version&cid=$cid&serial=$serial";
                    $this->send_data('Auth.do',$data);
                    $this->account->campain = true;
                } catch (Exception $e) {
                    if ($e->getCode() == 12612) {
                        $data = "world_id=10$world&kc_id=$username&password=$password&language=1&check_code=$version&cid=&serial=";
                        $this->send_data('Auth.do',$data);
                        $this->account->campain = true;
                        $this->db->add_account($this->account);
                        $error=true;
                    }
                    else throw $e;
                }
                
                if (!$this->account->campain && $cid != null && $serial != null)
                    $this->account->campain = true;
                
                $uuid=null;
                if ($db_account==null || $db_account['uuid'] == null)
                    $uuid=utils::make_hash($this->device_type);
                else
                    $uuid=$db_account['uuid'];
                $this->account->uuid = $uuid;

                $userid=$this->user_id;
                $this->account->user_id=$userid;
                $data = "user_id=$userid&language=1&device_type=0&uuid=$uuid";
                kc::$core_data=$this->send_data('Login.do',$data);
                $this->fill_core_data();
                
                
                $this->login_stamp_data=$this->send_data('LoginStamp.do','');            
                $this->login_day=$this->login_stamp_data->day_of_stamp;
                $this->account->day = $this->login_day;
                
            }
            
            $this->get_town(0);
            $this->account->dp = $this->dp;
            $this->account->cp = $this->cp;
            $this->account->cookie = $this->cookie;

            $this->db->add_account($this->account);

            if ($this->batgig)
            {
                //$this->hack_draw();
                $this->get_units($this->homebase_id);
            }
        }

/*
        private function logon_quick()
        {
            //do not use this it exposes the hack.
            throw new Exception('This function is no longer used');
            $userid=$this->user_id;
            $world=$this->world;
            $uuid=utils::make_hash($this->device_type);


            $data = "user_id=$userid&language=1&device_type=0&uuid=$uuid";
            kc::$core_data=$this->send_data('Login.do',$data);

            $this->load_core_data();
            $this->fill_core_data();
        }
*/
                                    
        public function fill_core_data($loaded=false)
        {
            if ($loaded)
            {
                kc::$time_offset = (float) utils::load_val('data/offset.txt');
                kc::$server_time_calc = microtime(true) * 1000 - kc::$time_offset;
                kc::$server_time = kc::$server_time_calc;
                
                kc::$game_time_calc = round(microtime(true)) - utils::load_data('data/birth.txt')[$this->world];
                kc::$game_time = kc::$game_time_calc;
            }
            else
            {
                kc::$server_time_calc = kc::$core_data->real_tm_msec;
                kc::$server_time = kc::$core_data->real_tm_msec;
                
                kc::$time_offset = microtime(true) * 1000 - kc::$server_time;
                utils::save_val('data/offset.txt',kc::$time_offset);
                
                kc::$game_time_calc  = kc::$core_data->game_tm_sec;
                kc::$game_time  = kc::$core_data->game_tm_sec;
                
                $birth = utils::load_data('data/birth.txt');
                $birth[$this->world] = round(microtime(true) - kc::$game_time);
                utils::save_data('data/birth.txt',$birth,true);
                
                $this->ingame_name = kc::$core_data->user_name;
                $this->account->nickname = kc::$core_data->user_name;
                
                $this->db->update_auctions(kc::$game_time,$this->world);
            }

            foreach (kc::$core_data->tile_type as $tile)
                kc::$tiles[$tile->id] = $tile->name;
            utils::save_data('data/tiles.txt',kc::$tiles);
            
            foreach (kc::$core_data->monsters as $monster)
            {
                kc::$monsters[$monster->id] = $monster->name;
                kc::$monster_obj[$monster->id] = $monster;
            }
            
            foreach (kc::$core_data->skills as $skill)
                kc::$skills_obj[$skill->id] = $skill;

            utils::save_data('data/monsters.txt',kc::$monsters);
            
            utils::save_json(kc::$core_data,'data/core.json');
            
        }
        
        public function load_core_data()
        {
            //kc::$tiles = utils::load_data('data/tiles.txt');
            //kc::$monsters = utils::load_data('data/monsters.txt');
            kc::$core_data = utils::load_json('data/core.json');
            $this->fill_core_data(true);
        }
        
        public static function load_core_data_static()
        {
            kc::$core_data = utils::load_json('data/core.json');
            foreach (kc::$core_data->tile_type as $tile)
                kc::$tiles[$tile->id] = $tile->name;
            utils::save_data('data/tiles.txt',kc::$tiles);
            
            foreach (kc::$core_data->monsters as $monster)
            {
                kc::$monsters[$monster->id] = $monster->name;
                kc::$monster_obj[$monster->id] = $monster;
            }
            
            foreach (kc::$core_data->skills as $skill)
                kc::$skills_obj[$skill->id] = $skill;
        }
        
        //Hack Draw
        public function hack_draw()
        {
            if (PHP_INT_MAX == 2147483647)
                return false;
            if ($this->shop_data===null)
                $this->get_shop();
            $crystal_pack=null;
            //for some rason $this->shop_data->packs can be null and this causes this itteration to time out the script
            if (!isset($this->shop_data->packs))
                return false;
            foreach ($this->shop_data->packs as $pack)
                if ($pack->id == 301000 && $pack->g1_point == 0)
                    $crystal_pack = $pack;
            if ($crystal_pack === null) return false;
            
            $average = $this->get_travel_time();
            
            $future = 700;
            $range = 10000;
            $min=1;
            $max=50;
            $random_wait=5;
            
            //$timer = new timer(true);
            
            $rand = new search_seeds($range,$max,$min,$this->server_time() + $future,2);
            $hits = $rand -> search();
            
            /*
            echo 'Search took '.$timer->stop().' ms. got '.count($hits).'hits<br>Av travel time '.$average.','.kc::$travel_time.'<br>Server Time '.$this->server_time().'<br>';
            foreach ($hits as $ms => $value)
                echo "epoch $ms, $value<br>";
            exit();
            */
            
            if (count($hits) == 0)
                return false;
            $drawn=false;
            foreach ($hits as $ms => $value)
            {
                $ms -= $average;
                if ($random_wait>0)
                {
                    $ms -= $random_wait;
                    $ms += rand(0,$random_wait*2);
                }
                //echo "ms $ms val $value<br>";
                
                while ($ms > $this->server_time())
                {
                    $server_time = $this->server_time();
                    $wait_time = $ms - $server_time;
                    if ($wait_time < $random_wait) break;
                    if ($wait_time > 5)
                        usleep(1000);
                }
                $winnings=$this->draw_pack(301000,1,1,true);
                //$this->db->draw_hack($winnings->monster->m_id,$value,$range);
                $drawn=true;
                //echo "ms $ms val $value drawn<br>";
            }
            if ($drawn) {
                $this->get_units($this->homebase_id);
                //TODO: add auto delete common on count > 150
            }
            
        }
        
        private function get_travel_time()
        {
            if (file_exists('data/ping.txt') && filemtime('data/ping.txt') > time() - 600) //ten mins
                return file_get_contents('data/ping.txt');
            $url = 'w10'.$this->world.'-sim.kingdom-conquest2.com';
            $travel = array();
            for ($a=0;$a<50;$a++)
                $travel[]=utils::pingDomain($url) / 2;
            $average = array_sum($travel) / count($travel);
            utils::save_val('data/ping.txt',$average);
            return $average;
        }
        
        //returns the predicted unix epoch of the server in ms
        public function server_time()
        {
            return kc::$server_time_calc + round((microtime(true) * 1000) - kc::$my_time);
        }
        
        //returns predicted game time. game time is in seconds
        public function game_time()
        {
            return kc::$game_time_calc + round(microtime(true) - (kc::$my_time/1000));
        }
        
        public function get_town($field_id)
        {
            $field_id = !$field_id?0:$field_id;
            $data = "field_id=$field_id";
            $this->town=$this->send_data('Town.do',$data);
            //$this->bug_killer($this->town,'resource');
            
            $this->process_res_data($this->town->resource);
                
            $this->homebase_id=$this->town->field_id;
            
            $this->constuct_max = $this->town->build_sametime + $this->town->build_reserve_free;
            $this->constuct_current = $this->town->now_build_sametime + $this->town->now_build_reserve;
            
            $this->is_tutorial = $this->town->tutorial_val + $this->town->tutorial_mode > 0;
            $this->account->tutorial=$this->is_tutorial;
        }
        
        private function process_res_data($object)
        {
            $this->resource_data=$object;
            $this->cp=$object->cp;
            $this->account->cp=$object->cp;
            $this->dp=$object->dp;
            $this->account->dp=$object->dp;
            
            $this->wood=$object->wood->now;
            $this->stone=$object->stone->now;
            $this->iron=$object->iron->now;
            $this->crystals=$object->crystal;
        }
        
        public function get_tiles($named,$field_id=0,$xa=0,$xb=0,$ya=0,$yb=0)
        {
            $ret=array();
            if ($this->town == null || $field_id > 0)
                $this->get_town($this->town == null?$this->homebase_id:$field_id);
            foreach($this->town->tiles as $tile)
                if (kc::$tiles[$tile->tile]==$named)
                    if($xa*$xb*$ya*$yb==0 || ($tile->x > $xa && $tile->x < $xb && $tile->y > $ya && $tile->y < $yb ))
                        $ret[]=$tile;
            return $ret;
        }
        
        public function collect_blue_crystals()
        {
            if ($this->town==null)
                $this->get_town($this->homebase_id);
            $touched=false;
            foreach($this->town->tiles as $tile)
                if (in_array(kc::$tiles[$tile->tile],kc::$blue_crystals))
                    if ($tile->t1 < $this->game_time()) {
                        $this->touch_tile($this->homebase_id,$tile->x,$tile->y);
                        $touched = true;
                    }
            if ($touched)
                $this->get_town($this->homebase_id);
        }
        
        public function check_tile_exists($base_id,$tile_id,$x,$y)
        {
            utils::check_args(array($tile_id,$x,$y));
            if (!$this->town)
                $this->get_town($base_id?$base_id:0);
            if (!is_int($tile_id)) $tile_id = utils::get_key($tile_id,kc::$tiles);
            foreach ($this->town->tiles as $building)
                if ($building->tile==$tile_id && $building->x == $x && $building->y == $y)
                    return $building;
                return false;
        }
        
        public function construction_queue($base_id)
        {
            $this->get_town($base_id?$base_id:$this->homebase_id);
            $ret=null;
            foreach ($this->town->task_build as $con)
                $ret[]=kc::$tiles[$con->tile];
            return $ret;
        }
        
        public function change_class($class_id)
        {
            $data = "job_id=$class_id";
            $this->send_data('ActplayJobChangeExec.do',$data);
        }
        
        public function check_quest_exists($quest_id,$complete=false)
        {
            utils::check_args(array($quest_id));
            foreach($this->town->quest->quest_list as $quest)
                if ($quest->quest_id == $quest_id)
                    return $quest->complete_flg == $complete;
            return false;
        }
        
        public function check_commander_present($field_id,$unit_id,$commander=null)
        {
            utils::check_args(array($unit_id));
            $field_id=$field_id?$field_id:$this->homebase_id;
            if ($this->unit_data==null)
                $this->get_units($field_id);
                
            $commander_uid=null;
            if ($commander !== null && is_string($commander))
            {
                $commander_obj = $this->get_commander(null,$commander);
                if ($commander_obj==null)
                    throw new Exception("Commander $commander not found");
                $commander_uid = $commander_obj->uniq_data->u_id;
            }
            
            if ($commander !== null && is_numeric($commander))
            {
                $commander_obj = $this->get_commander(null,null,$commander);
                if ($commander_obj==null)
                    $commander_uid = $commander;
                else
                    $commander_uid = $commander_obj->uniq_data->u_id;
            }
            
            if (is_object($commander) && $commander_obj->uniq_id)
                $commander_uid = $commander_obj->uniq_data->u_id;

            if ($commander_uid==null)
                throw new Exception("Unable to loocate commander");

            foreach ($this->unit_data->units as $unit)
                if ($unit->field_id == $field_id && $unit->deck_idx == $unit_id && $unit->commander->uniq_id == $commander_uid)
                    return true;
            
            return false;
        }
        
        public function check_attack_ready($field_id,$unit_id)
        {
            $field_id=$field_id?$field_id:$this->homebase_id;
            utils::check_args(array($unit_id));
            if ($this->unit_data==null)
                $this->get_units($field_id);
            
            $unit_to_check=null;
           
            foreach ($this->unit_data->units as $unit)
                if ($unit->field_id == $field_id && $unit->deck_idx == $unit_id)
                    {$unit_to_check = $unit;break;}
            if ($unit_to_check==null)
                return false;
                //throw new Exception('check_attack_ready<br/><br/>Cant find unit');

            if ($unit_to_check->commander->uniq_id==0)
                return false;
           
            $count=0;
            foreach ($unit_to_check->monsters as $mon) {
                if ($mon->uniq_id == 0)
                    continue;
                $count += $this->my_monsters_obj[$mon->uniq_id]->uniq_data->hc;
                if ($this->my_monsters_obj[$mon->uniq_id]->uniq_data->state !== 1)
                    return false;
            }
            return $count;
        }
        
        public function complete_quests($force=true)
        {
            if ($force) $this->get_town(0);
            $completed_ids=array();
            foreach($this->town->quest->quest_list as $quest)
                if ($quest->complete_flg==1)
                    $completed_ids[]=$quest->quest_id;

            $quest_complete=false;
            foreach ($completed_ids as $id) {
                $data = "quest_id=$id";
                $this->send_data('QuestGetPrize.do',$data);
                $quest_complete = true;
            }
            if ($force && $quest_complete)
                $this->get_town(0);
            return $quest_complete;
        }
        
        public function get_arena()
        {
            $this->arena_data = $this->send_data('Arena.do','');
            return $this->arena_data;
        }
        
        public function duel_register($field_id,$unit)
        {
            utils::check_args(array($unit));
            
            //!! - NOTE - The below has to be done once for registering to work, some server side setup must be needed
            $this->get_arena();
            
            if ($field_id==0) $this->get_town(0);
            $data = "field_id=$field_id&unit_no=$unit&regulation_id=0";
            return $this->send_data('ArenaUnitEntry.do',$data);
        }
        
        public function get_duel_page()
        {
            $this->duel_data=$this->send_data('ArenaDuelList.do','');
            return $this->duel_data;
        }
        
        public function get_duel_user($id) //,$state=0
        {
            $sanity=0;
            while (true)
            {
                $page = $this->get_duel_page();
                foreach ($page->duel_monsters as $player)
                    if ($player->user_id == $id) // && $player->result == $state
                            return $player;
                $sanity++;
                if ($sanity>10)
                    return null;
            }

        }

        public function duel($user_id)
        {
            utils::check_args(array($user_id));
            $data = "to_user_id=$user_id&cp_flag=0";
            return $this->send_data('ArenaBattle.do',$data);
        }
                
        public function duel_random()
        {
            if ($this->get_arena()->duel_info->is_open==0)
                return null;
            $this->get_duel_page();
            if (!$this->duel_data->is_regist)
            {
                if ($this->check_attack_ready($this->homebase_id,0))
                    $this->duel_register($this->homebase_id,0);
                elseif (count($this->get_tiles('Tower of Training',$this->homebase_id))==0)
                    return false;
                elseif ($this->set_random_unit($this->homebase_id,0))
                    $this->duel_register($this->homebase_id,0);
                else
                    return false;
                $this->get_duel_page();
            }
            if ($this->duel_data->remain == 0) return 0;
            
            $sanity=0;
            $my_rank = $this->duel_data->rank;
            while ($this->duel_data->remain > 0)
            {
                foreach ($this->duel_data->duel_monsters as $random_ply)
                {
                    if ($random_ply->result<>0)
                        continue;
                    $attack=false;
                    if ($my_rank==999999 && $random_ply->rank_num==999999 && $this->db->is_alt($random_ply->user_id,$this->world))
                        $attack=true;
                    elseif ($my_rank < $random_ply->rank_num) //attack players with a numerically higher rank
                        $attack=true;
                    elseif ($sanity > 20) //10 pages get viewed if you attack one player on each page, > 20 means 10 pages were viewed and nothing found
                        $attack=true;
                    if ($attack) {
                        $this->duel($random_ply->user_id);
                        break;
                    }
                }
                $this->get_duel_page();
                $sanity++;
            }
            return true;
        }
        
        public function get_shop()
        {
            unset($this->shop_data);
            $this->shop_data=$this->send_data('Gacha.do','');
            //$this->bug_killer($this->shop_data,'packs');
            if (count(kc::$pack_ids)==1)
                foreach($this->shop_data->packs as $pack)
                    kc::$pack_ids[] = array($pack->id,$pack->name);
            $this->process_res_data($this->shop_data->resource);
            return $this->shop_data;
        }
        
        public function get_tickets()
        {
            $ret='';
            
            if ($this->shop_data==null)
                $this->get_shop();

            foreach ($this->shop_data->packs as $pack)
            {
                switch ($pack->name) {
                    case 'Premium Pack':       //id 801000
                    case 'Premium SR Pack':    //id 802000
                    case 'Gold Pack':          //id 303000
                    case 'Orb':                //id 901000
                        $ret .= $pack->name .' '.$pack->ticket_num.'<BR />';
                        break;
                    case 'Crystal Pack':
                        $ret .= $pack->name .' '.$this->shop_data->resource->crystal.'<BR />';
                        break;
                }
            }
            return $ret;
        }
        
        public function draw_pack($pack_id,$button,$count=1,$return_obj=false)
        {
            utils::check_args(array($pack_id,$button));
            $data = "id=$pack_id&idx=$button";
            $ret='';
            for ($a=0;$a<$count;$a++)
            {
                $winnings=$this->send_data('GachaExec.do',$data);
                //$this->db->draw_hack($winnings->monster->m_id,0,0,$server_time);
                //u_id not given if draw goes to presents
                foreach ($winnings->array as $win)
                {
                    if ($win->add_u_id > 0)
                    {
                        $this->db->card_drawn($this->account,$win->monster->m_id,$win->add_u_id);
                        $this->my_monsters_obj[$win->add_u_id] = $win->monster;
                        $this->my_monsters_obj[$win->add_u_id]->uniq_data->u_id = $win->add_u_id;
                        $this->my_monsters[$win->add_u_id]=$win->monster->m_id;
                    }
                    if (substr($win->monster->m_id, -1)=='3')
                        $ret.= '<b>'.kc::$monsters[$win->monster->m_id].'</b><BR />';
                    else
                        $ret.= kc::$monsters[$win->monster->m_id].'<BR />';
                }
            }
            return $return_obj?$winnings:$ret;
        }

        function draw_login_rewards()
        {
            $this->get_shop();
            $ret='';
            
            foreach ($this->shop_data->packs as $pack)
            {

                $name = $pack->name;
                /*
                if ($pack->name == 'Crystal Pack')
                {
                    $this->draw_pack($pack->id,1,1);
                }*/
                
                if (($pack->g1_ticket_num == 0 || (strpos($pack->name,'Soul') && $pack->g1_point < 150) ) && $pack->name !== 'Crystal Pack')
                    continue;

                if (strpos($pack->message,'discount') && ($pack->g1_ticket_num > $pack->g1_point || $pack->id == 301000))
                {
                    $ret.="<BR /><u>$name Discount</u><BR />".$this->draw_pack($pack->id,1,1);
                    $pack->g1_ticket_num -= $pack->g1_point;
                    $ret.=$this->draw_login_rewards();
                    return $ret;
                }

                $ticket_num = $pack->g1_ticket_num;
                $multi = $pack->g1_multi_num > 0 ? $pack->g1_multi_num : 1;
                $cost = $pack->g1_point_base * $multi;
                $draws = floor($ticket_num/$cost);

                if ($draws==0)
                    continue;
                $ret.="<BR /><u>$name</u><BR />".$this->draw_pack($pack->id,1,$draws);
/*
                if ($pack->name=='Gold Pack' && $pack->ticket_num > 0)
                    $ret.='<BR /><u>Gold Pack Draws</u><BR />'.$this->draw_pack(303000,1,floor($pack->ticket_num/20));
                if ($pack->name=='Premium Pack' && $pack->ticket_num > 0)
                    $ret.='<BR /><u>Premium R Pack Draws</u><BR />'.$this->draw_pack(801000,1,$pack->ticket_num);
                if ($pack->name=='Premium SR Pack' && $pack->ticket_num > 0)
                    $ret.='<BR /><u>Premium SR Pack Draw</u><BR />'.$this->draw_pack(802000,1,$pack->ticket_num);
*/
            }
            if ($ret != '')
                $this->get_units($this->homebase_id);
            else
                $ret='<BR />No tickets to draw<BR />';
            return $ret;
        }
        
        public function get_presents_named($type,$names) //200 = Other
        {
             $this->get_present_list($type,-1);
             $presents=array();
             foreach ($this->present_data as $present)
                foreach ($names as $name)
                    if (strpos($present->title,$name) !== false || strpos($present->desc,$name) !== false) //'Compensation Gift'
                        if ($present->type==1)
                            $presents[]=$present->id;
                        elseif ($present->type==2)
                            $this->get_present($present->id,2);
             if (count($presents) > 0)
                 return $this->get_presents($presents);
        }
        
        public function get_present_monsters($rarity,$is_auction=true) //-1 for all.
        {
            if (!is_numeric($rarity))
                $rarity = utils::get_key($rarity,kc::$rarity_ids);
            $this->get_present_list(9,$rarity);
            $presents=array();
            foreach ($this->present_data as $present)
                if ($is_auction == kc::$monster_obj[$present->monster->m_id]->is_auction)
                    $presents[]=$present->id;
             if (count($presents) > 0)
                 return $this->get_presents($presents);
        }
        
        public function get_present_list($filter=-1,$rarity=-1,$just_page_count=false)
        {
            $lastpage=1;
            $ret = '';
            $this->present_data = array();
            for ($page=1;$page<=$lastpage;$page++)
            {
                $data = "page=$page&sort_key=0&sort_type=1&filter_key=$filter&filter_rarity=$rarity";
                $present_page=$this->send_data('PresentList.do',$data);
                $this->present_page_max = $present_page->page_max;
                if ($just_page_count)
                    return $lastpage;
                $lastpage=$present_page->page_max;
                foreach ($present_page->presents as $present)
                {
                    $this->present_data[] = $present;
                    $ret .= $present->desc.'<BR />';
                }
            }                                         
            return $ret;
        }
        
        public function get_present($id,$type)
        {
            utils::check_args($id,$type);
            $data = "type=$type&present_id=$id";
            return $this->send_data('PresentGet.do',$data);
        }
        
        public function get_presents($ids)
        {
            utils::check_args($ids);
            
            $draw_string='';
            foreach ($ids as $id)
                $draw_string.='&present_id='.$id;
            $data = "type=1$draw_string";
            if ($draw_string!='')
                 return $this->send_data('PresentMultiGet.do',$data);
        }
        
        public function get_commander_hire()
        {
            $world=$this->world;
            $this->commander_hire_data=$this->send_data('CommanderAddMenu.do','');
            return $this->commander_hire_data;
        }
        
        public function hire_commander($commander_id,$name_id)
        {
            utils::check_args(array($commander_id,$name_id));
            $data = "commander_id=$commander_id&name_id=$name_id";
            $this->send_data('CommanderAddExec.do',$data);
        }
       
        public function skill_enhance($base_monster,$skill_id,$cp_flag,$add_monster_ids)
        {
            utils::check_args(array($base_monster,$skill_id,$cp_flag,$add_monster_ids));
            //get_town fills in dp property
            if ($this->dp == '?')
                get_town(0);
            if ($this->dp<count($add_monster_ids)*4) return false; //this is the dp cost for sr. Can't be bothered to calc it correctly
            $monster_ids='';
            
            if (count($add_monster_ids)>1)
                foreach ($add_monster_ids as $id) {
                    if ($id==null || $id===0) return false; //there is a bug in the draw code that gives 0 for u_id this then gets used to enhance.
                    $monster_ids .= "&add_monster_ids=$id";
                }
            elseif (is_array($add_monster_ids))
                $monster_ids = "&add_monster_ids=".$add_monster_ids[0];
            else
                $monster_ids = "&add_monster_ids=".$add_monster_ids;
            $data = "base_monster_id=$base_monster&skill_id=$skill_id&use_cp_flg=$cp_flag".$monster_ids;
            $result = $this->send_data('SkillUpExec.do',$data);
            $this->get_units(null,$result);
            $this->dp = $result->user_dp;
            $this->cp = $result->user_cp;
            return $result;
        }
        
        public function skill_learn($base_monster,$learn_from_mon_id,$cp_flag)
        {
            utils::check_args(array($base_monster,$learn_from_mon_id));
            if ($base_monster==$learn_from_mon_id)
                return false;
            if (isset($this->my_monsters_obj[$base_monster]) && isset($this->my_monsters_obj[$learn_from_mon_id]))
            {
                if ($this->my_monsters_obj[$base_monster]->skills[1]->id > 0 && $this->my_monsters_obj[$base_monster]->skills[2]->id > 0)
                    return false;
                elseif (strpos(kc::$skills_obj[$this->my_monsters_obj[$learn_from_mon_id]->skills[0]->id]->desc,'synth')) //cannot be aquired through synthesis
                    return false;
            }
            //base_monster_id=4402616&use_cp_flg=0&add_monster_id=4402617&is_again=0&_tm_=1414400669187
            $data="base_monster_id=$base_monster&use_cp_flg=$cp_flag&add_monster_id=$learn_from_mon_id&is_again=0";
            $result = $this->send_data('SkillLearnExec.do',$data);
            unset($this->my_monsters[$base_monster]);
            unset($this->my_monsters_obj[$base_monster]);
            return $result;
        }
        
        public function awaken_card($m_id)
        {
            utils::check_args($m_id);
            if ($this->unit_data==null)
                $this->get_units($this->homebase_id);
            if ($this->is_tutorial)
                return false;
            //get all cards with the same id that are in pool
            $sacrifice=utils::return_monsters(kc::$monster_obj,$this->my_monsters_obj,null,null,'inpool',null,null,null,$m_id,true,-1,null,null,3);
            foreach ($sacrifice as $key => $card )
                if ($card->uniq_data->awake_exp>=10)
                    unset($sacrifice[$key]);
            $sacrifice = array_values($sacrifice);

            if (count($sacrifice)<2)
                return false;

            $awake_me=$sacrifice[0];
            unset($sacrifice[0]);
            
            foreach ($sacrifice as $key => $card)
                if ($card->uniq_data->awake_exp > $awake_me->uniq_data->awake_exp)
                {
                    $sacrifice[] = $awake_me;
                    $awake_me=$card;
                    unset($sacrifice[$key]);
                }
            
            $current_level=$awake_me->uniq_data->awake_exp;
            $awake_ids=array();
            foreach ($sacrifice as $key => $card)
                    if ($current_level + $card->uniq_data->awake_exp + 1 <= 10)
                    {
                        $current_level += $card->uniq_data->awake_exp + 1;
                        $awake_ids[] = $card->uniq_data->u_id;
                    }
            
            if (count($awake_ids)==0)
                return false;
            
            $result = $this->awaken($awake_me->uniq_data->u_id,$awake_ids);
            return count($awake_ids);
        }
        
        public function awaken($base_monster_u_id,$with_u_ids)
        {
            //base_monster_id=2467911&add_monster_ids=2599419&_tm_=1408361761831
            utils::check_args(array($base_monster_u_id,$with_u_ids));
            $ids='';
            foreach ($with_u_ids as $id)
                $ids.="&add_monster_ids=$id";
            $data="base_monster_id=$base_monster_u_id".$ids;
            $result = $this->send_data('MonsterAwakeExec.do',$data);
            $this->get_units($this->homebase_id);
            return $result;
        }
        
        public function add_stats_points($monster_id,$att=0,$def=0,$int=0,$spd=0)
        {
            utils::check_args(array($monster_id));
            $data = "monster_id=$monster_id&attack=$att&defense=$def&intelli=$int&agility=$spd";
            return $this->send_data('MonsterStatusUp.do',$data);
        }

        public function build($field_id,$tile,$x,$y)
        {
            utils::check_args(array($tile,$x,$y));
            $field_id=$field_id?$field_id:$this->homebase_id;
            if (!is_numeric($this->constuct_current))
                $this->get_town($field_id);
            if ($this->constuct_current == $this->constuct_max)
                return false;
            if (!is_int($tile)) $tile = utils::get_key($tile,kc::$tiles);
            $data = "field_id=$field_id&tx=$x&ty=$y&tile_id=$tile";
            //"field_id=2858313&tx=47&ty=49&tile_id=10100"
            $res=$this->send_data('TileBuild.do',$data);
            $this->constuct_current++;
            return $res;
        }
        
        public function build_if_less_than($field_id,$tile,$x,$y,$level)
        {
            utils::check_args(array($tile,$x,$y,$level));
            if ($this->check_tile_exists(0,$tile,$x,$y) && $this->check_tile_exists(0,$tile,$x,$y)->lev < $level)
                return $this->build($field_id,$tile,$x,$y);
            if ($level == 1 && !$this->check_tile_exists($field_id,$tile,$x,$y))
                return $this->build($field_id,$tile,$x,$y);
            return null;
        }
        
        public function build_with_string($build_string)
        {
            //field_id=1494873&tx=57&ty=53&tile_id=11100&_tm_=1404335494642
            //php :"field_id=1494873&tx=61&ty=47&tile_id=1200"
            utils::check_args(array());
            $this->send_data('TileBuild.do',$build_string);
        }
        
        public function build_cancel($field_id,$id)
        {
            utils::check_args(array($id));
            $field_id=$field_id?$field_id:$this->homebase_id;
            $data = "id=$id&field_id=$field_id";
            $this->send_data('TileBuildCancel.do',$build_string);
        }

        public function build_road($field_id,$locs)
        {
            $field_id=$field_id?$field_id:$this->homebase_id;
            utils::check_args(array($locs));
            
            $loc_string='';
            if (count($locs)<2)
                $loc_string=$locs;
            else
                foreach ($locs as $loc)
                    $loc_string.="&add=$locs";
                    
            $data = "field_id=$field_id".$loc_string;
            //echo $url.'<BR />'.$data; exit();
            $this->send_data('RoadBuild.do',$data);
            
        }
        
        public function open_area($area)
        {
            utils::check_args(array($area));
            $data = "area=$area";
            $result = $this->send_data('TownAreaOpen.do',$data);
            $this->kc->town->areas[$area]=1;
            return $result;
        }
        
        public function remove_tile($field_id,$x,$y)
        {
            utils::check_args(array($x,$y));
            $field_id=$field_id?$field_id:$this->homebase_id;
            $data = "field_id=$field_id&tx=$x&ty=$y";
            $this->send_data('TileLeave.do',$data);
        }
        
        public function touch_tile($field_id,$x,$y)
        {
            $field_id = $field_id?$field_id:$this->homebase_id;
            utils::check_args(array($x,$y));
            $data = "field_id=$field_id&tx=$x&ty=$y";
            return $this->send_data('TileTouch.do',$data);
        }
        
        public function get_units($field_id,$monster_obj=null)
        {
            if ($monster_obj==null) {
                unset($this->unit_data);
                $field_id = $field_id?$field_id:0;
                $data = "to_field_id=$field_id&reinforcement=0&small=0";
                $this->unit_data=$this->send_data('MonsterView.do',$data);                
            } else {
                unset($this->unit_data);
                $this->unit_data = $monster_obj;
            }
            
            unset($this->my_monsters);
            $this->my_monsters=array();
            unset($this->my_monsters_obj);
            $this->my_monsters_obj=array();
            
            $for_sale = false;
            foreach ($this->unit_data->monsters as $mons) 
            {
                $this->my_monsters[$mons->uniq_data->u_id]=$mons->m_id;
                $this->my_monsters_obj[$mons->uniq_data->u_id]=$mons;
                if ($mons->uniq_data->state == 4)
                    $for_sale = true;
            }
            
            if (isset($this->unit_data->resource))
                $this->process_res_data($this->unit_data->resource);
            if (isset($this->unit_data->commanders))
                $this->commander_data=$this->unit_data->commanders;
            
            //$this->connect_db();
            $this->db->account_update($this->account,$this->unit_data,$this->batgig);
            
            if ($for_sale)
                $this->get_auction_sell_data();
            return $this->unit_data;
        }
        
        public function delete_commons()
        {
            if ($this->username=='1212') //Lipkix
                return false;
            if ($this->unit_data == null)
                $this->get_units($this->homebase_id);
            $commons = utils::return_monsters(kc::$monster_obj,$this->my_monsters_obj,null,'common','inpool',null,1,0,null,null,-1,0,null,2);
            foreach ($commons as $monster)
                $this->delete_card($monster->uniq_data->u_id);
        }
        
        //also synths uncommon
        public function synth_commons($only_common=false)
        {
            if ($this->unit_data == null)
                $this->get_units($this->homebase_id);
            $synthed=0;
            for ($rarity=0;$rarity<2;$rarity++)
            {
                if ($only_common && $rarity>0)
                    break;
                for ($race=0;$race<5;$race++)
                {
                    //0=>'bug',1=>'beast',2=>'dhuman',3=>'undead',4=>'spirit'
                    $commons = utils::return_monsters(kc::$monster_obj,$this->my_monsters_obj,$race,$rarity,'inpool',null,1,0,null,true,-1);
                    if (count($commons)==0)
                        continue;
                    $enhance = utils::return_monsters(kc::$monster_obj,$this->my_monsters_obj,$race,'rare','inpool',null,null,null,null,true,-1,0,null,9);
                    if (count($enhance)==0)
                        $enhance = utils::return_monsters(kc::$monster_obj,$this->my_monsters_obj,$race,'uncommon','inpool',null,null,null,null,true,-1,0,null,9);
                    usort($enhance,'utils::sort_by_skill_xp');
                    if (count($enhance)==0) continue;
                    $ids=array();
                    foreach ($commons as $common)
                        if ($enhance[0]->uniq_data->u_id !== $common->uniq_data->u_id)
                            $ids[]=$common->uniq_data->u_id;
                    if (count($ids) == 0) continue;
                    $this->skill_enhance($enhance[0]->uniq_data->u_id,$enhance[0]->skills[0]->id,0,$ids);
                    $synthed+=count($ids);
                }
            }
            if ($synthed>0) $this->get_units($this->homebase_id);
            return $synthed;
        }
        
        public function delete_card($id)
        {
            if ($this->is_tutorial===true)
                return false;
            $data="monster_id=$id";
            return $this->unit_data=$this->send_data('MonsterDelete.do',$data);
            
        }
        
        public function get_commander($u_id = null,$name = null,$id = null)
        {
            if ($this->unit_data==null)
                $this->get_units($field_id);

            if ($u_id !== null)
                foreach ($this->commander_data as $commander)
                    if ($commander->uniq_data->u_id == $u_id)
                        return $commander;

            if ($name !== null)
                foreach ($this->commander_data as $commander)
                    if ($commander->name == $name)
                        return $commander;

            if ($id !== null)
                foreach ($this->commander_data as $commander)
                    if ($commander->c_id == $id)
                        return $commander;
                        
            return null;
        }

        public function get_monster($u_id)
        {
            return $this->my_monsters_obj[$u_id];
        }
        
        public function set_commander($commander_id,$field_id,$unit_num)
        {
            utils::check_args(array($commander_id,$field_id,$unit_num));
            $data = "field_id=$field_id&unit_idx=$unit_num&commander_id=$commander_id";
            $this->units=$this->send_data('CommanderUnitSet.do',$data);
        }

        public function remove_commander($field_id,$unit_num)
        {
            utils::check_args(array($field_id,$unit_num));
            $data = "field_id=$field_id&unit_idx=$unit_num";
            $this->send_data('CommanderUnitClear.do',$data);
        }
        
        public function position_empty($field_id,$unit_num,$position)
        {
            utils::check_args(array($field_id,$unit_num,$position));
            if (!is_int($position)) $position = utils::get_key($position,kc::$pos_masks);
            
            foreach ($this->unit_data->units as $unit)
                if ($unit->field_id == $field_id && $unit->deck_idx == $unit_num)
                    foreach ($unit->monsters as $monster)
                        if ($monster->posmask == $position && $monster->uniq_id > 0)
                            return false;
            return true;
        }
        
        public function set_monster($field_id,$unit_num,$position,$monster_id)
        {
            utils::check_args(array($field_id,$unit_num,$position,$monster_id));
            if (!is_int($position)) $position = utils::get_key($position,kc::$pos_masks_set);
            $data = "field_id=$field_id&unit_idx=$unit_num&position=$position&monster_id=$monster_id";
            $this->send_data('MonsterUnitSet.do',$data);
        }
        
        public function set_random_unit($field_id,$unit_num)
        {
            utils::check_args(array($field_id,$unit_num));
            if ( ! $this->check_commander_present($field_id,$unit_num,'Iris'))
                $this->set_commander($this->get_commander(null,'Iris')->uniq_data->u_id,$field_id,$unit_num);

            $monsters = utils::return_monsters(kc::$monster_obj,$this->unit_data->monsters,null,null,'inpool',100,null,0,null,null,-1,0,4,4);
            do
            {
                try
                {
                    shuffle($monsters);
                    if (count($monsters)>1)
                    {
                        $this->set_monster($field_id,$unit_num,'FM',$monsters[0]->uniq_data->u_id);
                        $this->set_monster($field_id,$unit_num,'BM',$monsters[1]->uniq_data->u_id);
                        return true;
                    }
                    return false;
                } catch (Exception $e) {
                    if ($e->getCode()==23029) //cost limit
                        continue;
                    throw $e;
                }
            } while (true);
        }
        
        public function remove_monster($field_id,$unit_num,$monster_id)
        {
            utils::check_args(array($field_id,$unit_num,$monster_id));
            $data = "field_id=$field_id&unit_idx=$unit_num&monster_id=$monster_id";
            $this->send_data('MonsterUnitClear.do',$data);
        }

        public function max_production($monster_uid)
        {
            utils::check_args(array($monster_uid));
            $this->get_units(0);
            $monster=$this->my_monsters_obj[$monster_uid];

            if (!isset($monster->create))
                throw new Exception('The monster is not set in a unit');
            if ($monster->uniq_data->state != utils::get_key('idle',kc::$state_ids))
                throw new Exception('The monster is not idle');

            $wood = $this->resource_data->wood->now;
            $stone = $this->resource_data->stone->now;
            $iron = $this->resource_data->iron->now;
            
            //TODO : Barracks space needs to be checked.
            
            $max=$monster->uniq_data->awake_level < 5?9999:12000;
            if ($monster->create->wood_m1000>0)
                $max = floor($wood / ($monster->create->wood_m1000 / 1000)) < $max ? floor($wood / ($monster->create->wood_m1000 / 1000)) : $max;
            if ($monster->create->stone_m1000>0)
                $max = floor($stone / ($monster->create->stone_m1000 / 1000)) < $max ? floor($stone / ($monster->create->stone_m1000 / 1000)) : $max;
            if ($monster->create->iron_m1000>0)
                $max = floor($iron / ($monster->create->iron_m1000 / 1000)) < $max ? floor($iron / ($monster->create->iron_m1000 / 1000)) : $max;

            return $max;
        }
        
        public function produce_monster($monster_id,$count)
        {
            utils::check_args(array($monster_id,$count));
            $data = "monster_uniq_id=$monster_id&num=$count";
            $this->send_data('MonsterCreate.do',$data);
        }
        
        public function view_map($field_id)
        {
            $field_id=$field_id?$field_id:$this->homebase_id;
            $data = "field_id=$field_id";
            return $this->send_data('FieldMap.do',$data);
        }
        
        public function attack_xy($field_id,$unit_num,$x,$y)
        {
            utils::check_args(array($field_id,$unit_num,$x,$y));
            $this->attack_id($field_id,$unit_num,utils::map_id($x,$y));
        }

        public function attack_id($field_id,$unit_num,$to_field_id)
        {
            //type=0&from_field_id=2856246&unit_idx=1&to_field_id=2856247&_tm_=1403366962307
            utils::check_args(array($field_id,$unit_num,$to_field_id));
            $data = "type=0&from_field_id=$field_id&unit_idx=$unit_num&to_field_id=$to_field_id";
            $this->send_data('BattleMarch.do',$data);
        }
        
        public function attack_nest($unit_num,$nest_num)
        {
            utils::check_args(array($unit_num,$nest_num));
            $field_id = $this->homebase_id;
            $x = null;
            $y = null;
            switch ($nest_num)
            {
                case 1:
                    $x = 49;
                    $y = 37;
                    break;
            }
            //field_id=1305128&tx=49&ty=37&unit_idx=0&_tm_=1404311276522
            $data = "field_id=$field_id&tx=$x&ty=$y&unit_idx=$unit_num";
            $this->send_data('MonsterLairBattle.do',$data);            
        }
        
        public function view_personal_report($type=0,$page=1)
        {
            $data = "type=$type&page=$page";
            return $this->send_data('UserReportList.do',$data);
        }

        public function view_personal_report_detail($id)
        {
            utils::check_args(array($id));
            $data = "id=$id&list_type=0type=0";
            return $this->send_data('ReportBattleDetail.do',$data);
        }
        
        public function ah_sell_ids($uids,$price=10,$seller=null,$random=0,$random_mul=0,$sell_account=null)
        {
            if (count($uids)==0) return 0;
            if ($seller===null) $seller = $this->username;
            $count = count($uids) > 10 ? 10 : count($uids);
            $ah_cap = false;
            $sales=0;
            $rand=0;
            for ($a=0;$a<$count && !$ah_cap;$a++)
            {
                if ($random>0)
                    $rand=rand(0,$random) * $random_mul;
                try {
                    $this->ah_sell($uids[$a]['u_id'],$price+$rand,$seller,$sell_account);
                    $sales++;
                    sleep(5+rand(0,5));
                } catch (Exception $ex) {
                    switch ($ex->getCode())
                    {
                        case 25023: //At ten auctions
                            $ah_cap = true;
                            break;
                        case 25008: //Already in ah
                            $this->db->set_card_status($uids[$a]['u_id'],4,$this->world);
                            break;
                        case 25001: //An error occurred. Card destroyed? Previously this would have been causing a lot of errors as the get u_id search was not checking if a card can be auctioned
                            $this->db->set_card_status($uids[$a]['u_id'],9,$this->world);
                            file_put_contents('kc_error.txt',"Auction error u_id=".$uids[$a]['u_id']."\r\n",FILE_APPEND);
                            break;
                        default:
                            throw $ex;
                    }
                }
            }
            return $sales;
        }
        
        //NOTE: This function checks the fee and does a refresh of sales after, so exactly as the client.
        public function ah_sell($card_id,$price,$seller=null,$sell_account=null)
        {
            utils::check_args(array($card_id,$price));

            if ($this->is_tutorial===true || $this->max_listings===true)
                return false;
            //checks if card can be sold. This is done to avoid errors from batgig webpages.
            $from_db=$this->db->get_card($card_id);
            $monster_id = isset($this->my_monsters[$card_id])?
                kc::$monster_obj[$this->my_monsters[$card_id]]->id:null;
            $monster_id = $monster_id == null && isset($from_db['id']) ? $from_db['id'] : $monster_id;
            //batgig users cards are not listed in the db and so u_id db search will
            if ($monster_id > 0 && kc::$monster_obj[$monster_id]->is_auction==0)
                return false;
            $this->ah_check_sell_fee($price);
            $data = "uniq_id=$card_id&price=$price";
            try {$this->send_data('AuctionSell.do',$data);}
            catch (Exception $ex)
            {
                if ($ex->getCode()==70005)
                    $this->is_tutorial=true;
                elseif ($ex->getCode()==25023) {
                    $this->max_listings=true;
                    $this->current_sales=10;
                }
                    
                    
                throw $ex;
            }
            $this->db->card_sold($card_id,$seller,$price,$sell_account,$this->game_time());
            if (isset($this->my_monsters_obj[$card_id]->uniq_data->state))
                $this->my_monsters_obj[$card_id]->uniq_data->state = 4;
            return $this->get_auction_sell_data(true);
        }
        
        public function ah_check_sell_fee($price)
        {
            utils::check_args($price);
            return $this->send_data('AuctionCommissionFee.do','price='.$price);
        }
        
        public function ah_cancel($id)
        {
            utils::check_args($id);
            $data="auction_id=$id";
            $ret=$this->send_data('AuctionSellCancel.do',$data);
            $this->db->cancel_auction($id,$this->world);
            if (isset($this->my_monsters_obj[$id]->uniq_data->state))
                $this->my_monsters_obj[$id]->uniq_data->state = 0;
            return $ret;
        }
        
        public function get_auction_sell_data($force=false)
        {
            if ($this->auction_sell_data !== null && !$force) return $this->auction_sell_data;
            $data='sort_key1=0&sort_type1=1&filter_race=-1&filter_rarity=-1&search_type=-1&keyword=&page=1';
            $this->auction_sell_data=$this->send_data('AuctionUserMonster.do',$data);
            kc::$game_time = $this->auction_sell_data->server_tm;
            $this->current_sales=0;
            
            $this->connect_db();
            foreach ($this->auction_sell_data->monsters as $auction_item)
                if ($auction_item->monster->uniq_data->state==4)
                {
                    $this->db->update_auction($auction_item,$this->account,$this->game_time());
                    $this->current_sales++;
                }
            $this->max_listings = $this->current_sales == 10;
            return $this->auction_sell_data;
        }
        
        public function get_auction_sell_item($mon_uid)
        {
            if ($this->auction_sell_data==null)
                $this->get_auction_sell_data();
            foreach($this->auction_sell_data->monsters as $auction_item)
                if ($auction_item->monster->uniq_data->u_id == $mon_uid)
                    return $auction_item;
            return null;
        }
        
        public function find_auction_item($race_id=-1,$rarity_id=-1,$name='',$price=-1,$no_bids=true,$bin=false)
        {
            if ($price == null || $price == -1)
                throw new Exception('A search price is required');
            
            $url = 'AuctionMonsterSellList.do';
            $page = 0;
            $page_max = 1;
            $searchtype = $name==''?-1:1; //moster = 1, skill = 2, all = -1 

            $ret=array();
            while ($page < $page_max)
            {
                $page++;
                $data = "sort_key1=1&sort_type1=2&filter_race=$race_id&filter_rarity=$rarity_id&page=$page&search_type=$searchtype&keyword=$name";
                $auction = null;
                try {$auction = $this->send_data($url,$data);} catch (Exception $ex) {if ($ex->getCode()==25020)return null;else throw $ex;}
                foreach ($auction->sell_monsters as $item)
                    if ($item->start_price != $price)
                        continue;
                    elseif ($no_bids && $item->bid_num > 0)
                        continue;
                    elseif ($bin && $item->bid_state == 4)
                        continue;
                    else
                        $ret[] = $item;
                $page_max = $auction->page_max;
                kc::$server_time = $auction->server_tm;
            }
            if (count($ret)>0)
                return $ret;
            else
                return null;
        }
        
        //returns list ending sooner first, by default
        public function find_auctions($name=null,$skill=null,$max_price=999999,$max_results=10,$rarity=-1,$sort_key=3,$sort_type=2,$race=-1,$bin=null)
        {
            if ($name==null && $skill==null && $max_results!==999999 && $race==-1)
                throw new Exception('Name and Skill not specified');
            $rarity = $rarity === null ? -1 : $rarity;
            $url = 'AuctionMonsterSellList.do';
            if (!is_numeric($sort_key)) $sort_key = utils::get_key($sort_key,kc::$auction_sort_key);
            if (!is_numeric($sort_type)) $sort_type = utils::get_key($sort_type,kc::$auction_sort_type);
            $page = 0;
            $page_max = 1;
            if ($name!==null)
                $searchtype = 1;
            elseif ($skill!==null)
                $searchtype = 2;
            else
                $searchtype = -1;
            $search = $name == null ? $skill : $name;
            $rarity=is_numeric($rarity)?$rarity:utils::get_key($rarity,kc::$rarity_ids);
            $return=array();

            while ($page < $page_max && count($return) < $max_results)
            {
                $page++;
                //sort_key1=0&sort_type1=1&filter_race=-1&filter_rarity=-1&page=1&search_type=-1&keyword=&_tm_=1410791514831
                $data = "sort_key1=$sort_key&sort_type1=$sort_type&filter_race=$race&filter_rarity=$rarity&page=$page&search_type=$searchtype&keyword=$search";
                
                $auction=null;
                try {
                    $auction = $this->send_data($url,$data);
                } catch (Exception $ex)
                    {
                        switch ($ex->getCode())
                        {
                            case 25017: //No monsters found for auction that meet the search criteria.
                            case 25018: //No Skills
                            case 25019: //?
                            case 25020: //No monsters found for auction<BR>that meet the search criteria--specify other terms.
                                return $return;
                            default:
                                throw $ex;
                        }
                    } 
                foreach ($auction->sell_monsters as $item)
                {
                    if ($sort_key==1&&$sort_type==2 && $item->start_price > $max_price) break;
                    if ($sort_key==3 && $sort_type==2 && $bin && $item->bid_state == utils::get_key('normal',kc::$auction_ids))
                        return $return;
                    if ($item->start_price < $max_price && $item->bid_state !== utils::get_key('already_bid',kc::$auction_ids))
                        $return[]=$item;
                    $this->db->update_auction_seen($item,$auction->server_tm); //$auction->server_tm is game time, not server time unix timestamp of server
                }
                $page_max = $auction->page_max;
                kc::$server_time = $auction->server_tm;
            }
            return $return;
        }
        
        public function get_ah_current_bids()
        {
            $this->auction_bid_data = $this->send_data('AuctionBiddingList.do','');
            $this->current_bids = count($this->auction_bid_data->bid_monsters);
            return $this->current_bids;
        }
        
        //type = 0 or 1, 0 normal. 1 bin
        //This function updates the dp if bid is a success
        public function ah_bid($type,$id,$amount)
        {
            utils::check_args($type,$id,$amount);
            $url=$type==0?'AuctionBid.do':'AuctionImediateKnockDown.do';
            $data="auction_id=$id&bid_price=$amount";
            try
            {
                $result=$this->send_data($url,$data);
                if ($type==0) $this->current_bids ++;
                $this->dp -= $amount;
                $this->db->card_bid_price($id,$amount);
                if ($type==1 && $this->batgig)
                    $this->get_units($this->homebase_id);
                $this->account->dp = $this->dp;
                if ($this->account->name != null && $this->account->world != null)
                    $this->db->add_account($this->account);
                return $result;
            }
            catch (Exception $e)
            {
                $card=$this->db->get_auction($id);
                $u_id=isset($card['u_id'])?$card['u_id']:null;
                if ($u_id!==null)
                    switch ($e->getCode())
                    {
                        case 25012: //Auction has been cancled
                            $this->db->set_card_status($u_id,0,$this->world);
                            return false;
                        case 25011: //Auction has ended / has been won
                            $this->db->set_card_status($u_id,9,$this->world);
                            $this->db->auction_ended($id);
                            return false;
                        case 25026: //Action is now bin
                            $this->ah_bid(1,$id,$card['sale_price']);
                            break;
                        case 25022: //You have reached the limit for the number of monsters and cannot  place any further bids
                            return -1;
                        default:throw $e;
                    }
                else throw $e;
            }
        }
        
        public function send_mail($to,$subject='',$body='')
        {
            utils::check_args(array($to));
            //to_name=ALLA&subject=&body=&source_mail_box_id=-1&source_mail_id=-1&_tm_=1404591648499
            // TODO: the client replaces all spaces with some char, cant remember what, use wireshark to check
            $data = "to_name=$to&subject=$subject&body=$body&source_mail_box_id=-1&source_mail_id=-1";
            return $this->send_data('MailSend.do',$data);
        }
        
        public function mail_get($auction=0)
        {
            $data = "auction=$auction";
            return $this->send_data('MailInBox.do',$data);
        }
        
        public function mail_delete($auction,array $ids)
        {
            $ids_echo="mail_box_id=1";
            foreach ($ids as $id)
                $ids_echo.="&delete_mail_id=$id";
            $ids_echo.="&auction=$auction";
            return $this->send_data('MailDel.do',$ids_echo);
        }
        
        public function mail_delete_auction()
        {
            $aution_mail=$this->mail_get(1);
            $ids=array();
            foreach($aution_mail->mail_list as $mail)
                $ids[]=$mail->mail_id;
            if (count($ids)>0)
                $this->mail_delete(1,$ids);
            return count($ids);
        }
        
        public function profile_comment($text)
        {
            $data = "comment=$text";
            return $this->send_data('UserProfileCommentSet.do',$data);
        }
        
        public function alliance_memebers($alliance_id)
        {
            utils::check_args(array($alliance_id));
            $data="alliance_id=$alliance_id";
            return $this->send_data('AllianceMember.do',$data);
        }
        
        //type=1&page=1&filter_name_type=0&filter_name=&filter_coord_type=0&filter_coord_x=0&filter_coord_y=0
        public function alliance_log($type=0,$page=1,$filter_name_type=0,$filter_name=null,$filter_coord_type=0,$filter_coord_x=0,$filter_coord_y=0)
        {
            $data="type=$type&page=$page&filter_name_type=$filter_name_type&filter_name=$filter_name&filter_coord_type=$filter_coord_type&filter_coord_x=$filter_coord_x&filter_coord_y=$filter_coord_y";
            return $this->send_data('AllianceReportList.do',$data);
        }
        
        //TODO:the user_id can not be set if the class was created with just a cookie, but the user_id would be in the db so could be retried.
        public function profile_territory($id=0)
        {
            $data='id='.($id==0 && $this->user_id !==null?$this->user_id:$id);
            return $this->send_data('UserLand.do',$data);
        }
        
        public function get_ranking($type,$page,$nation_type)
        {
            $data="type=$type&page=$page&nation_type=$nation_type";
            return $this->send_data('Ranking.do',$data);
        }

    }
?>