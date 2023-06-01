<?php
echo phpinfo();exit();


require('include/kc.php');

function the_time()
{
    return date('l jS \of F Y h:i:s A');
}

$alts=array(
    0=>array('username'=>'Jimmybean','password'=>'Jimmybean1','world'=>45,'device'=>'android'),
    1=>array('username'=>'Numerous','password'=>'Numerous1','world'=>45,'device'=>'android'),
    2=>array('username'=>'Comwine','password'=>'Comwine1','world'=>45,'device'=>'apple'),
    3=>array('username'=>'IgnoreHim','password'=>'IgnoreHim1','world'=>45,'device'=>'android'),
    4=>array('username'=>'OkSmall','password'=>'OkSmall1','world'=>45,'device'=>'android'),
    5=>array('username'=>'Jimmybean','password'=>'Jimmybean1','world'=>45,'device'=>'android'),
    6=>array('username'=>'TimeWill','password'=>'TimeWill1','world'=>45,'device'=>'android'),
    7=>array('username'=>'ArghDam','password'=>'ArghDam1','world'=>45,'device'=>'android'),
    8=>array('username'=>'Tomfoolery','password'=>'Tomfoolery1!','world'=>45,'device'=>'apple'),
    9=>array('username'=>'TomdickHary','password'=>'Tomdick!1','world'=>45,'device'=>'apple'),
    10=>array('username'=>'Joesmith','password'=>'Joesmith*2','world'=>45,'device'=>'android'),
    11=>array('username'=>'IgnoreMe','password'=>'IgnoreMe1','world'=>45,'device'=>'android'),
    12=>array('username'=>'WorldOne','password'=>'WorldOne1','world'=>45,'device'=>'android'),
    13=>array('username'=>'Carol03','password'=>'Dade2254','world'=>45,'device'=>'android'),
    14=>array('username'=>'SameSame','password'=>'SameSame1','world'=>45,'device'=>'android'),
    15=>array('username'=>'Youtube1','password'=>'Youtube2','world'=>45,'device'=>'android')
);
$test=array();

class batch extends Thread
{
    
    public $username;
    public $password;
    public $world;
    public $device;
    
    public function __construct($username,$password,$world,$device)
    {
        $this->username = $username;
        $this->password = $password;
        $this->world = $world;
        $this->device = $device;
    }
    
    public function start()
    {
        file_put_contents('cron_complete.txt',the_time(). ' ' . $this->username ." batch started\r\n",FILE_APPEND);
        for ($a=1;$a<51;$a++)
        {
            $username = $this->username . $a;
            $continue=false;
            $break=false;
            
            switch ($username)
            {
                case 'IgnoreHim20':
                    $continue=true;break;
                case 'TomdickHary10':
                case 'Tomfoolery22':
                    $break=true;break;
            }
            file_put_contents('cron_complete.txt',the_time().' '.$username." Start\r\n",FILE_APPEND);
            if ($continue) continue;
            if ($break) break;
            
            $kc = new kc($username,$this->password,$this->device,$this->world,null,null,true);
            try
            {
                file_put_contents('cron_complete.txt',the_time(). ' ' . $username . ' Logon' . "\r\n",FILE_APPEND);
                $kc->logon();
                //$test[]=$kc->cookie;
                file_put_contents('cron_complete.txt',the_time(). ' ' . $username . ' Duel' . "\r\n",FILE_APPEND);
                $kc->duel_random();
                file_put_contents('cron_complete.txt',the_time(). ' ' . $username . ' Draw' . "\r\n",FILE_APPEND);
                $kc->draw_login_rewards();
                file_put_contents('cron_complete.txt',the_time(). ' ' . $username . ' Complete' . "\r\n",FILE_APPEND);
            }
            catch (Exception $e)
            {
                $error=the_time() ." Error ". $username . "\r\n" . $e->getMessage()."\r\n";
                file_put_contents('cron_complete.txt',$error,FILE_APPEND);
            }
            /*
            if ($a>2)
            {
                echo 'Debug Complete.';
                exit();
            }
            */
            unset($kc);
        } //end for
        file_put_contents('cron_complete.txt',the_time(). ' ' . $this->username ." batch completed\r\n",FILE_APPEND);
    }
}


try
{
    unlink('cron_complete.txt');
    file_put_contents('cron_complete.txt',"-------------------------------------\r\n".the_time()." Daily Started\r\n",FILE_APPEND);
    foreach ($alts as $alt)
    {
        $batch = new batch($alt['username'],$alt['password'],$alt['world'],$alt['device']);
        $batch->start();
    }
    file_put_contents('cron_complete.txt',the_time(). " Daily Complete\r\n",FILE_APPEND);
}
catch (Exception $e)
{
    $error=the_time() ."\rFaital Error\r\n" . $e->getMessage()."\r\n";
    file_put_contents('cron_complete.txt',$error,FILE_APPEND);
}
echo 'Complete.';
?>
