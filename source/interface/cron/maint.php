<?php
set_time_limit(60 * 60 * 60 * 6);
chdir('..');
require('include/kc.php');

$db = new db();
$db->maintance();

$proxies = utils::get_proxies($db,false);
for ($a=0;$a<2;$a++)
    foreach ($proxies as $proxy)
    {
        $timer = new timer(true);
        $page = utils::get_page('http://www.google.com',true,null,null,$proxy,true,30);
        $resp_time = $timer->stop();
        
        $health_mod=0;
        if (strpos(get_class($page),'Exception')!==false)
        {
            if ($proxy['health']>1000)
                $health_mod = -50;
            elseif ($proxy['health']>500)
                $health_mod = -25;
            elseif ($proxy['health']>100)
                $health_mod = -5;
            else
                $health_mod = -1;
            $error_msg = $page->getMessage();
            if (utils::is_fatal($error_msg))
                $health_mod = -1000;
        }
        else
            if ($resp_time < 2500)
                $health_mod=3;
            elseif ($resp_time < 5000)
                $health_mod=2;
            else
                $health_mod=1;
        $health_mod=$health_mod>=0?'+'.$health_mod:$health_mod;
        $db->update_proxy($proxy,$health_mod);
    }
?>
