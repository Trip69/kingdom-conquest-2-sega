<?php
copy('/home/boy/public_html/images/kc/interface/cron/cron_complete.txt','/home/boy/public_html/images/kc/interface/cron/cron_complete_last.txt');
copy('/home/boy/public_html/images/kc/interface/kc_debug.txt','/home/boy/public_html/images/kc/interface/kc_debug_last.txt');
unlink('/home/boy/public_html/images/kc/interface/cron/cron_complete.txt');
unlink('/home/boy/public_html/images/kc/interface/kc_debug.txt');
unlink('/home/boy/public_html/images/kc/interface/cron/out.txt');
unlink('/home/boy/public_html/images/kc/interface/error_log');
unlink('/home/boy/public_html/images/kc/interface/proxy_failed.txt');
$batch=0;
require('daily.php');
?>
