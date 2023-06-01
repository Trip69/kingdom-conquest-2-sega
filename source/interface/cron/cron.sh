rm /home/boy/public_html/images/kc/interface/cron/cron_complete.txt
rm /home/boy/public_html/images/kc/interface/proxy_failed.txt
rm /home/boy/public_html/images/kc/interface/cron/out.txt
rm /home/boy/public_html/images/kc/interface/error_log
echo "running" > /home/boy/public_html/images/kc/interface/cron/cron.txt
php -q /home/boy/public_html/images/kc/interface/cron/bean.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/bank.php >> /home/boy/public_html/images/kc/interface/cron/out.txt
php -q /home/boy/public_html/images/kc/interface/cron/1.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/2.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/3.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/4.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/5.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/6.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/7.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/8.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/9.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/10.php >> /home/boy/public_html/images/kc/interface/cron/out.txt
php -q /home/boy/public_html/images/kc/interface/cron/11.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/12.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/13.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/14.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/15.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/16.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/17.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/18.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/19.php >> /home/boy/public_html/images/kc/interface/cron/out.txt &
php -q /home/boy/public_html/images/kc/interface/cron/20.php >> /home/boy/public_html/images/kc/interface/cron/out.txt
echo "waiting" > /home/boy/public_html/images/kc/interface/cron/cron.txt