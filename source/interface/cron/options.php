<?php
if (isset($_GET['enable']))
{
    if ($_GET['enable']=='true')
        file_put_contents('../enabled.txt','true');
    elseif ($_GET['enable']=='false')
        unlink('../enabled.txt');
}
if (isset($_GET['cron']))
{
    if ($_GET['cron']=='true')
        file_put_contents('cron.txt','true');
    elseif ($_GET['cron']=='false')
        unlink('cron.txt');
}
if (isset($_GET['clear']))
{
    switch ($_GET['clear'])
    {
        case 'all':
            unlink('../proxy_failed.txt');
            unlink('../error_log');
            unlink('cron_complete.txt');
            unlink('out.txt');
    }
}
echo 'Done';
?>
