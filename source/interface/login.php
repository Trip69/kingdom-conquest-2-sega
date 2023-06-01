<?php
/*
    $_POST['device']='apple';
    $_POST['username']='Comwine';
    $_POST['password']='Comwine1';
    $_POST['world']=45;
//*/

    require('include/kc.php');
    require('include/template.php');
    $echo = array();
    $kc=new kc($_POST['username'],$_POST['password'],$_POST['device'],$_POST['world'],null,null,false);
    try {
        $kc->logon();
        
        $echo['cookie'] = $kc->cookie;
        $echo['world'] = $kc->world;
        $echo['device'] = $kc->device_type;
        
        if(isset($_POST['tutorial']))
            $echo['auto_tutorial'] = 'true';
        else
            $echo['auto_tutorial'] = 'false';
        
        $echo['user_id'] = $kc->user_id;
        $echo['field_id'] = $kc->homebase_id;
        $echo['username'] = $_POST['username'];
        $echo['password'] = $_POST['password'];
        $echo['auto'] = $kc->account->auto ? 'checked':'';
        $echo['auto_arena'] = $kc->account->auto_arena ? 'checked':'';
        $echo['auto_login'] = $kc->account->auto_login;
        echo template::simple($echo,'kc2_main_page.html');
        
    } catch (Exception $e) {
        $err = $e->getMessage();
        
        $echo['echo'] = "$err<BR />";
        echo template::simple($echo,'template/kc2_err.html');
    }
?>
