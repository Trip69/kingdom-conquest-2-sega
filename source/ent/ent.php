<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('UCT');

class db
{

    public function __construct()
    {
        $this->connect();
    }
    
    private function connect()
    {
        $this->link = mysqli_connect('localhost', 'boy_kcent', 'kcent4231','boy_kc_ent');
        if (mysqli_connect_errno())
          echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    public function get_players()
    {
        $query='UPDATE players SET ent_start=0 WHERE ent_start<DATE_SUB(NOW(), INTERVAL 13 HOUR)';
        mysqli_query($this->link,$query);
        
        $query='SELECT name,UNIX_TIMESTAMP(ent_start) AS ent_start FROM players ORDER BY name';
        $result=mysqli_query($this->link,$query);
        if (!$result) throw new Exception($query .'<BR />'. mysqli_error($this->link));
        return $result;
    }
    
    public function set_player($ent,$name)
    {
        $query=$ent?"INSERT INTO players (name, ent_start) VALUES('$name', NOW()) ON DUPLICATE KEY UPDATE ent_start=IF(UNIX_TIMESTAMP(ent_start)=0,NOW(),ent_start)":
                    "INSERT INTO players (name, ent_start) VALUES('$name', 0) ON DUPLICATE KEY UPDATE ent_start=0";
        $result=mysqli_query($this->link,$query);
        if (!$result) throw new Exception($query .'<BR />'. mysqli_error($this->link));
    }
    
}

class template
{
    public static function simple($args,$template)
    {
        $return=file_get_contents($template);
        foreach ($args as $key => $value)
            $return = str_replace('X'.$key.'X',$value,$return);
        return $return;
    }
}

$db = new db();
$action=isset($_POST['action'])?$_POST['action']:null;
$echo_data=array();
$lang_arr = array(1=>'en',2=>'jp',3=>'ch');
$trans = array('No name entered'=>array(1=>'No name entered'),
               'Entrenched for'=>array(1=>'Entrenched for'),
               'Cooldown for'=>array(1=>'Cooldown for'),
               'Can entrench'=>array(1=>'Can entrench'));
$lang = isset($_GET['lang'])?$_GET['lang']:1;
require('lang_jp.php');
require('lang_ch.php');
switch ($action)
{
    case 'set_ent':
        if (!isset($_POST['name'])||$_POST['name']=='')
        {
            echo $trans['No name entered'][$lang];
            exit();
        }    
        $set = $_POST['set']=='true'?true:false;
        $db->set_player($set,$_POST['name']);
    case null:
        $result = $db->get_players();
        $echo_data['ent']='';
        while($row = mysqli_fetch_array($result))
        {
            $display=true;
            $ent=$row['ent_start'];
            $status='';
            if ($ent + (60 * 60 * 5) > time())
                $status = $trans['Entrenched for'][$lang] . '</td><td>' . date('G:i:s',60 * 60 * 5 + $ent - time()) . '</td>';
            elseif ($ent + (60 * 60 * 13) > time())
                $status = $trans['Cooldown for'][$lang]. '</td><td>' . date('G:i:s',60 * 60 * 13 + $ent - time()) . '</td>';
            else
            {
                $display = false;
                $status = $trans['Can entrench'][$lang].'</td><td></td>';
            }
            $name=$row['name'];
            $cancel="<input type='radio' name='name' value='$name' />";
            if ($display) $echo_data['ent'].= "<tr><td>$name</td><td>$status</td><td>$cancel</td></tr>\r\n";
        }
        $filex = isset($_GET['lang']) ? $lang_arr[$_GET['lang']] : 'en';
        echo template::simple($echo_data,"rench_$filex.html");
        break;
}
?>
