<?php
chdir('..');
require('include/batgig.php');
$batgig = new batgig();
$batgig->refresh_all();
$batgig->do_all();
?>
