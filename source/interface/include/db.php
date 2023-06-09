<?php

class account

{

    public $name;

    public $password;

    public $world;

    public $android;

    public $nickname;

    public $user_id=0;

    public $batgig=false;

    public $bank=false;

    public $cookie;

    public $uuid;

    public $skcc=0;



    public $auto=false;

    public $auto_arena=false;

    //1:wins 2:rank;

    public $arena_mode=2;

    public $auto_login=15;



    public $campain;

    public $tutorial=false;

    public $day;

    public $cp;

    public $dp;

    public $wood;

    public $stone;

    public $iron;



    public function __construct($name,$password,$world,$android,$day=0)

    {

        //echo 'androis '.$android;

        //exit;

        $this->name=$name;

        $this->password=$password;

        $this->world=$world;

        if (strpos($android,'ndroid')>0 || (is_bool($android) && $android))

            $this->android=true;

        elseif (strpos($android,'pple')>0  || (is_bool($android) && !$android))

            $this->android=false;

        else

            throw new Exception('Contact author code (1)');

        $this->day=$day;

    }



    public static function is_batgig_user($name)

    {

        switch ($name)

        {

            case 'LordMacNiell' : //path

            case '1212' : //Kate

            case 'b13798528950' : //me

            case '2223': //hank

            case '11894': //hank alt

            case 'Zakarim': //Shar

            case 'Comwine'://Given to Book

            case 'OkSmall1'://Path KC1  - 15k cp drawn

            case 'OkSmall'://KC1 Account - 15k cp drawn

            case 'Numerous'://KC1 Account

            case 'Jimmybean': //KC1 Account

            case 'IgnoreHim': //KC1 Account

            case 'k025937':

            case 'K025937':

            case 'a13798528950':

            case '121212':

            case 'a66711515':

            case 'b66711515':

            case 'a98421771':

            case 'b98421771': //^Sale alts

            case 'IgnoreHim1':

            case 'IgnoreHim2':

            case 'IgnoreHim3': //given to shar friend ^^ * 3

            //case 'IgnoreHim4': //Shar KC1 transfer

            /*

            case 'k025937':

            case '1212' :

            case 'a98421771':

            case 'b98421771':

            case 'a66711515':

            case 'b66711515':

            case 'a13798528950':

            */

                return true;

            default:

                return false;

        }

    }



    public static function no_list_cards($name)

    {

        switch ($name)

        {

            case 'LordMacNiell' : //path

            case '1212' : //Kate

            case 'b13798528950' : //me

            case '2223': //hank

            case '11894': //hank alt

            case 'Zakarim': //Shar

            //case 'Comwine'://Given to Book ? Err talk to book

            case 'OkSmall1'://Path KC1

                return true;

        }

        return false;

    }



    public function my_hash ()

    {

        return hash('ripemd160', $this->name . $this->world);

    }



    public static function get_auto_accounts(db $db)

    {

        $ret=array();

        $accounts = $db->get_auto_accounts();

        foreach ($accounts as $account)

        {

            $na = new account($account['name'],$account['password'],$account['world'],$account['android'],$account['day']);

            $na->cookie = $account['cookie'] == ''?null:$account['cookie'];

            $na->auto_arena = $account['auto_arena'];

            $ret[]=$na;

        }

        return $ret;

    }



}



//Needed for display of data

class card

{

    public $u_id;

    public $m_id;

    public $name;

    public $account;

    public $world;

    public $edition;

    public $awake;



    public function __construct($u_id,$name,$account,$world,$m_id=0,$edition=0,$awake=0)

    {

        $this->u_id=$u_id;

        $this->name=$name;

        $this->account=$account;

        $this->world=$world;

        $this->m_id=$m_id;

        $this->edition = $edition;

        $this->awake=$awake;

    }



}



/*



TODO:



Fix Bug causing this



INSERT INTO accounts (hash,name,password,world,user_id,batgig,cookie,uuid,android,day ,dp,cp,nickname) VALUES ('8ae5d84c2b205ba2b93065b2f0aa98ae31bd91b0','k025937','Hellarse1',45,8594,0,'FFBA4D6308A8E775EC189EE72D17BB21.tomcat1','',1,21 ,135438,25,'CrustyKaba');<BR />MySQL server has gone away



Note should not be INSERT buy update!



Also error when clicking bule crystals (caused by sending data twice no doubt)



{"res_code":20021,"error_msg":"This facility cannot be tapped yet.<BR>Please wait.<BR>(20021)"}



*/

class db

{



    private $link;

    private $core_data;

    private $monster_names = array();



    public function __construct()

    {

        $this->connect();

    }



    public function __destruct()

    {

        $this->close();

    }



    private function load_core_data()

    {

        if ($this->core_data!==null) return null;

        if (kc::$core_data !== null)

            $this->core_data = kc::$core_data;

        elseif (file_exists('data/core.json'))

            $this->core_data = json_decode(file_get_contents('data/core.json'));

        elseif (file_exists('interface/data/core.json'))

            $this->core_data = json_decode(file_get_contents('interface/data/core.json'));

        foreach ($this->core_data->monsters as $monster)

            $this->monster_names[$monster->id] = $monster->name;

    }



    private function connect()

    {

        $this->link = mysqli_connect('localhost', 'XXXXXXX', 'XXXXXXXXXX','XXXXXXXXXX');

        if (mysqli_connect_errno())

            throw new Exception('MySQL Error:'.mysqli_connect_error());

    }



    public function close()

    {

        //echo var_dump($this->link);

        if (isset($this->link)) {

            mysqli_close($this->link);

            unset($this->link);

        }

    }



    private function query($query,$return_array=false)

    {

        $ret=array();

        $result=mysqli_query($this->link,$query);

        //echo $query . '<br />';

        if (!$result && mysqli_error($this->link) == 'MySQL server has gone away') {

            $this->close();

            $this->connect();

            return $this->query($query);

        }

        elseif (!$result)

            throw new Exception($query .'<BR />'. mysqli_error($this->link));



        if (is_bool($result))

            return $result;

            /*

            echo 'Query '.$query.'<br/>';

            echo 'Error '.mysqli_error($this->link).'<br/>';

            echo 'Error '.$result.'<br/>';

            throw new Exception($query);

            */

        while($row = mysqli_fetch_array($result))

            $ret[]=$row;

        mysqli_free_result($result);

        if (count($ret)==0)

            return null;

        elseif (count($ret)==1 && !$return_array)

            return $ret[0];

        else

            return $ret;

    }



    public function list_all_cards($world,$all_cards=false,$awake=false,$return_sql_result=false,$skcc=false)

    {

        $exta_sql='';

        $exta_sql.=$awake?' AND awake > 0':' AND cards.batgig=1';

        $exta_sql.=$skcc?' AND cards.skcc > 0':' AND cards.skcc = 0';

        $query = $all_cards?

            "SELECT id,skill_id,u_id,edition,account_name,awake,tutorial,skill_xp FROM cards INNER JOIN accounts ON cards.account_key = accounts.key WHERE status=0 AND account_world=$world $exta_sql ORDER BY id,tutorial,edition,awake DESC,account_name":

            "SELECT id,skill_id,u_id,edition,account_name,awake,tutorial,skill_xp FROM cards INNER JOIN accounts ON cards.account_key = accounts.key WHERE status=0 AND account_world=$world AND right(id, 1)=3 $exta_sql ORDER BY id,tutorial,edition DESC,awake DESC,account_name";



        //$this->load_core_data();

        //$cards=array();

        if ($return_sql_result)

            return mysqli_query($this->link,$query);

        else

            return $this->query($query);

/*

        foreach($rows as $row)

            if(account::no_list_cards($row['account_name']))

                continue;

            else

                $cards[]=new card($row['u_id'],$this->monster_names[$row['id']],$row['account_name'],$row['account_world'],$row['id'],$row['edition'],$row['awake']);

        return $cards;

*/

    }



    public function list_all_sales($world,

                                   $bid=null,

                                   $alt_seller=false,

                                   $batgig=true,

                                   $max_price=999999,

                                   $rarity=null,

                                   $order_desc=true,

                                   $skcc=false)//,$seller_account=null

    {

        $game_time = round(microtime(true) - utils::load_data('data/birth.txt')[$world]);

        $query_add='';

        $query_add.=$bid===null?null:" AND bid = $bid";

        $query_add.=$alt_seller?' AND RIGHT(seller,3) = "Alt"':null;

        $query_add.=$batgig?' AND batgig=1':null;

        $query_add.=$skcc?' AND skcc>0':' AND skcc=0';

        //$query_add.=$seller_account!==null?" AND LEFT(sell_account,".strlen($seller_account).")='$seller_account'":null;

        if ($rarity!==null && !is_numeric($rarity))

            $rarity=utils::get_key($rarity,kc::$rarity_ids);

        $query_add.=$rarity===null?null:" AND RIGHT(id,1)=$rarity";

        $order=$order_desc?'DESC':'ASC';

        //TODO: FROM_UNIXTIME(0) is '1970,1,1 02:00:00' ?? Something I don't understand here, now it's 1970,1,1 01:00:00, as expected

        $query="SELECT *,TIME_FORMAT(TIMEDIFF(FROM_UNIXTIME(deadline_gt - $game_time),'1970,1,1 01:00:00'),'%k:%i') AS remaining FROM cards WHERE status=4 AND account_world=$world AND sale_price < $max_price $query_add ORDER BY auction_id $order, id";

        return $this->query($query);

    }



    public function list_cards($account)

    {

        $this->load_core_data();

        $name = $account->name;

        $world = $account->world;

        $query = "SELECT * FROM cards WHERE account_name='$name' AND account_world=$world AND status<>9";

        $cards=array();

        $rows=$this->query($query);

        foreach ($rows as $row)

            $cards[]=new card($row['u_id'],$this->monster_names[$row['id']],

                              $row['account_name'],

                              $row['account_world']);

        return $cards;



    }



    public function list_card_count($world,$rarity)

    {

        $query="SELECT id,COUNT(*) AS count FROM cards WHERE status=0 AND account_world=$world AND RIGHT(id,1)=$rarity GROUP BY id";

        return $this->query($query);

    }



    public function get_uids($world,$state,$id,$random=false,$edition=0,$awake=0,$is_auction=true,$batgig=1) //,$max=10

    {

        if (count(kc::$monster_obj)==0) kc::load_core_data_static();

        $batgig=$batgig?1:0;

        if($edition===null) $edition=0;

        $query = '';

        switch ($id)

        {

            case 0:

            case 1:

            case 2:

            case 3:

                $query="SELECT u_id,id,account_name FROM cards WHERE account_world=$world AND RIGHT(id,1)=$id AND status=$state AND batgig=$batgig AND edition=$edition AND awake=$awake";

                break;

            default:

                $query="SELECT u_id,id,account_name FROM cards WHERE account_world=$world AND id=$id AND status=$state AND batgig=$batgig AND edition=$edition AND awake=$awake";

        }

        $rows=$this->query($query);

        foreach ($rows as $row)

            if ($is_auction && kc::$monster_obj[$row['id']]->is_auction == 0 || account::no_list_cards($row['account_name']))

                continue;

            else

                $ret[] = $row;

        if ($random) shuffle($ret);

        return $ret;

    }



    public function get_card($u_id)

    {

        $query="SELECT * FROM cards WHERE u_id=$u_id";

        return $this->query($query);

    }



    public function get_auction($a_id)

    {

        $query="SELECT * FROM cards WHERE auction_id=$a_id";

        return $this->query($query);

    }



    public function cancel_auction($a_id,$world)

    {

        $query="UPDATE cards SET status=0, seller='', sell_account='',auction_id=0,bid=0,deadline_gt=0 WHERE auction_id=$a_id AND account_world=$world";

        return $this->query($query);

    }



    public function card_drawn(account $account,$m_id,$u_id)

    {

        if (account::no_list_cards($account->name))

            return false;

        $name = $account -> name;

        $world = $account -> world;

        if ($u_id == 0)

            return false;

        $hash = $world . $u_id;

        $account_key=$this->get_account($account)['key'];

        $query = "REPLACE INTO cards (u_id,id,account_name,account_world,hash,account_key) VALUES ($u_id,$m_id,'$name',$world,$hash,$account_key)";

        return $this->query($query);

    }



/*

    private function make_uid()

    {

        $query = 'SELECT u_id from cards ORDER BY u_id ASC LIMIT 1';

        $result = mysqli_query($this->link,$query);

        return $this->query($query)['u_id'] - 1;

    }

*/



    private function card_exists($u_id)

    {

        $query = "SELECT COUNT(*) AS count FROM cards WHERE u_id=$u_id";

        return $this->query($query)['count']==1;

    }



    public function account_update(account $account,$unit_data,$batgig=true)

    {

        //if ($account->is_batgig_user()) return;

        $batgig = account::is_batgig_user($account->name) ? false : $batgig;

        $this->add_account($account);

        foreach ($unit_data->monsters as $monster)

        {

            //Add or update all cards for this account

            $skill_xp = $monster->skills[0]->exp + $monster->skills[1]->exp + $monster->skills[2]->exp;

            $awake=$monster->uniq_data->awake_exp;

            $u_id=$monster->uniq_data->u_id;

            $m_id=$monster->m_id;

            $name=$account->name;

            $world=$account->world;

            $status=$monster->uniq_data->state;

            $batgig=$batgig?1:0;

            $edition=isset($monster->edition)?$monster->edition->type:0;

            $hash=$world.$u_id;

            $skcc=$account->skcc;

            $skill_id=$monster->skills[0]->id;



            $query='';

            if ($this->card_exists($u_id))

                $query = "UPDATE cards SET skill_id=$skill_id,edition=$edition,account_name='$name',status=$status,awake=$awake,skill_xp=$skill_xp,batgig=$batgig,skcc=$skcc WHERE u_id=$u_id";

            elseif (account::no_list_cards($name))

                return false;

            else {

                $account_key=$this->get_account($account)['key'];

                $query = "INSERT INTO cards (u_id,id,skill_id,account_name,account_world,status,awake,skill_xp,batgig,edition,hash,account_key,skcc) VALUES ($u_id,$m_id,$skill_id,'$name',$world,$status,$awake,$skill_xp,$batgig,$edition,$hash,$account_key,$skcc)";

            }



            $this->query($query);

        }



        $query = 'SELECT * FROM cards WHERE status <> 9 AND account_name=\''.$account->name.'\' AND account_world='.$account->world;

        $rows = $this->query($query);

        foreach ($rows as $row)

        {

//            if ($row['id']==10603503) {$a=1;}

            //Set as not found if in db but not in the pool

            $found=false;

            foreach ($unit_data->monsters as $monster)

                if ($monster->uniq_data->u_id==$row['u_id'] && $monster->m_id == $row['id']) {

                    $found = true;

                    break;

                }

            if (!$found)

                $this->card_not_present($row);

        }

    }



    /*

    public function card_update($account,$card)

    {

        //TODO This doesnt record auction

        $this->add_account($account);

        $query = 'REPLACE INTO cards (u_id,id,account_name,account_world) VALUES ('.$card->uniq_data->u_id.','.$card->m_id.',\''.$account->name.'\','.$account->world.')';

        mysqli_query($this->link,$query) or die('Query failed: ' . mysqli_error($this->link));

    }

    */



    public function card_sold($card_id,$seller=null,$price=0,$sell_account=null,$game_time=null)

    {

        $seller = $seller==null?null:",seller='$seller'";

        //$deadline = 60 * 60 * 36;

        $price = $price==0?null:",sale_price=$price";

        $sell_account = $sell_account==null?null:",sell_account='$sell_account'";

        $deadline_gt=null;

        if ($game_time!==null) {

            $game_time = 129600 + $game_time; //60 * 60 * 36

            $deadline_gt = ",deadline_gt=$game_time";

        }

        $query = "UPDATE cards SET bid=0,status=4 $seller $price $sell_account $deadline_gt WHERE u_id=$card_id";

        return $this->query($query);

    }



    public function card_bid_price($auction_id,$bid)

    {

        $query="UPDATE cards SET bid=$bid WHERE auction_id=$auction_id AND bid<$bid";

        return $this->query($query);

    }



    public function get_auction_bid($auction_id)

    {

        $query="SELECT bid FROM cards WHERE auction_id=$auction_id";

        $auction_bid=$this->query($query);

        if (!isset($auction_bid['bid']))

            return 0;

        else

            return $auction_bid['bid'];

    }



    public function get_auction_sale_count($world)

    {

        $query = "SELECT COUNT(*) AS count FROM cards WHERE status=4 AND account_world=$world";

        return $this->query($query)['count'];

    }



    public function get_units(account $account)

    {

        $hash = $account->my_hash();

        $query="SELCT * FROM units WHERE account_hash='$hash'";

        return $this->query($query);

    }



    public function get_auto_accounts()

    {

        $query='SELECT * FROM accounts WHERE auto=1';

        return $this->query($query);

    }



    public function auto_save_settings(account $account)

    {

        $name=$account->name;

        $world=$account->world;

        $auto=(int)$account->auto;

        $auto_arena=(int)$account->auto_arena;

        $extra_sql='';

        $extra_sql.=$account->cookie===null?null:',cookie="'.$account->cookie.'"';

        $extra_sql.=$account->auto_login===null?null:',auto_login="'.$account->auto_login.'"';

        $query="UPDATE accounts SET auto=$auto,auto_arena=$auto_arena $extra_sql WHERE name='$name' AND world=$world";

        return $this->query($query);

    }



    public function get_monsters(account $account)

    {

        $name=$account->name;

        $world=$account->world;

        $query="SELECT * FROM cards WHERE account_name='$name' AND world=$world AND status < 9";

        return $this->query($query);

    }



    public function set_card_status($id,$status,$world)

    {

        utils::check_args(array($id,$status,$world));

        $auction=$status==4?null:',auction_id=0';

        $query = "UPDATE cards SET status=$status $auction WHERE u_id=$id AND account_world=$world";

        return $this->query($query);

    }



    public function card_not_present($db_row)

    {

        if ($db_row['status']==4) {

            $auction_id = $db_row['auction_id'];

            $query = "UPDATE cards SET status=9,auction_id=0 WHERE auction_id > 0 AND auction_id < $auction_id AND bid > 0";

            $this->query($query);

        }

        $u_id = $db_row['u_id'];

        $query = "UPDATE cards SET status=9 WHERE u_id=$u_id";

        return $this->query($query);

    }



    public function update_card($card,$account=null)

    {

        $id = $card->m_id;

        $skill_id=$card->skills[0]->id;

        $u_id = $card->uniq_data->u_id;

        $awake = $card->uniq_data->awake_exp;

        $state = $card->uniq_data->state;

        $username = $account===null?null:$account->name;

        $world = $account===null?null:$account->world;



        $hash = $world . $u_id;



        $query = "SELECT * FROM cards WHERE u_id=$u_id";

        $result = mysqli_query($this->link,$query);



        if (mysqli_num_rows($result) == 0){

            $account_key=$this->get_account($account)['key'];

            $query = "INSERT INTO cards (u_id,id,skill_id,status,awake,account_name,account_world,hash,account_key) VALUES ($u_id,$id,$skill_id,$state,$awake,'$username',$world,$hash,$account_key)";

        } else

            $query = "UPDATE cards SET status=$state,awake=$awake,skill_id=$skill_id WHERE u_id=$u_id";

        return $this->query($query);

    }



    public function auction_ended($id)

    {

        $id++;

        $query = "UPDATE cards SET status=9 WHERE auction_id>0 AND auction_id<$id AND status=4";

        return $this->query($query);

    }



    public function update_auctions($game_time,$world)

    {

        $query="UPDATE cards SET status = 9 WHERE status = 4 AND bid > 0 AND deadline_gt < $game_time";

        $this->query($query);

    }



    public function update_auction($auction_obj,account $account,$game_time)

    {

        $this->update_card($auction_obj->monster,$account);

        $a_id=$auction_obj->auction_id;

        $mu_id = $auction_obj->monster->uniq_data->u_id;

        $bid = $auction_obj->highest_price;

        $start_price=$auction_obj->start_price;

        $deadline_gt = $auction_obj->deadline_tm;

        $world = $account->world;



        /*

        $deadline = $auction_obj->deadline_tm - $game_time - 360; //hack for some reason time is off by +1 hour

        $time = date("H:i", $deadline);

        if ($deadline / 60 / 60 > 23)

           $time = '1.' . $time;

        $sale_at =  $auction_obj->deadline_tm - (60 * 60 * 36);

        */

        $query = "UPDATE cards SET status=4, auction_id=$a_id, bid=$bid, sale_price=$start_price, deadline_gt=$deadline_gt WHERE u_id=$mu_id AND account_world=$world";

        return $this->query($query);

    }



    public function update_auction_seen($auction_obj,$game_time)

    {

        /*

        $deadline = $auction_obj->deadline_tm - $game_time;

         $time = date("H:i", $deadline);

         if ($deadline / 60 / 60 > 23)

            $time = '1.' . $time;

         */

         $deadline_gt=$auction_obj->deadline_tm;

         $auction_id=$auction_obj->auction_id;

        $sale_price=$auction_obj->start_price;

        $u_id=$auction_obj->monster->uniq_data->u_id;

        $query="UPDATE cards SET status=4,auction_id=$auction_id,sale_price=$sale_price,deadline_gt=$deadline_gt WHERE u_id=$u_id";

        return $this->query($query);



    }



    public function add_account(account $account)

    {

        $android=$account->android?1:0;

        $username = $account->name;

        $password = $account->password;

        $world = $account->world;

        $day = $account->day;

        $hash = $account->my_hash();

        $nickname = $account->nickname;

        $user_id = $account->user_id;

        $batgig = $account->batgig && !account::is_batgig_user($account->name) ?1:0;

        $cookie = $account->cookie;

        $campain = $account->campain ? '1' : '0';

        $skcc = $account->skcc;



        $var_name = '';

        $var_value = '';



        $var_name .= isset($account->dp) ? ',dp' : null;

        $var_name .= isset($account->cp)  ? ',cp' : null;

        $var_name .= $account->nickname !== null  ? ',nickname' : null;

        $var_value .= isset($account->dp) ? ','.$account->dp : null;

        $var_value .= isset($account->cp) ? ','.$account->cp : null;

        $var_value .= $account->nickname !== null  ? ",'".$account->nickname."'" : null;



        $update_sql='';

        $update_sql.=isset($account->dp) ? ',dp='.$account->dp : null;

        $update_sql.=isset($account->cp) ? ',cp='.$account->cp : null;

        $update_sql.=$account->user_id>0 ? ',user_id='.$account->user_id : null;

        $update_sql.=$account->uuid!==null ? ',uuid="'.$account->uuid.'"': null;

        $update_sql.=$account->tutorial?',tutorial=1':',tutorial=0';

        $update_sql.=$account->bank?',bank=1':',bank=0';

        $query="SELECT COUNT(*) AS count FROM accounts WHERE name='$username' AND world=$world";

        $result = $this->query($query);

        if ($result['count']==0) //tried mysqli_num_rows() but that returns 0 when its not 0

            $query = "INSERT INTO accounts (hash,name,password,world,user_id,batgig,cookie,uuid,android,day,campain,skcc $var_name) VALUES ('$hash','$username','$password',$world,$user_id,$batgig,'$cookie','$uuid',$android,$day,$campain,$skcc $var_value);";

        else

            $query = "UPDATE accounts SET password='$password',batgig=$batgig,cookie='$cookie',day=$day,campain=$campain,skcc=$skcc $update_sql WHERE name='$username' AND world=$world;";

        return $this->query($query);

    }



    public function add_proxies(array $proxies)

    {

        foreach ($proxies as $proxy)

        {

            $query='SELECT COUNT(*) AS count FROM proxies WHERE host="'.$proxy['host'].'"';

            $count = $this->query($query)['count'];

            if ($count==0)

            {

                $host=$proxy['host'];

                $port=$proxy['port'];

                $this->query("INSERT INTO proxies VALUES ('$host',$port,0,NOW())");

            }

            else

            {

                $host=$proxy['host'];

                $this->query("UPDATE proxies SET health=0 WHERE host='$host' AND health < 0");

            }

        }

    }



    public function get_proxies($including_slow=false)

    {

        $query = $including_slow ?

            'SELECT * FROM proxies WHERE health > -4 ORDER BY health DESC':

            'SELECT * FROM proxies WHERE health > -2 ORDER BY health DESC';

        return $this->query($query);

    }



    public function update_proxy($proxy,$mod)

    {

        $host=$proxy['host'];

        $query = "UPDATE proxies SET health=health $mod WHERE host='$host'";

        return $this->query($query);

    }



    private function card_name($card)

    {

        foreach (kc::$core_data->monsters as $monster)

            if ($monster->id == $card->m_id)

                return $monster->name;

    }



    public function draw_hack($card_id,$rand,$range,$game_time=null)

    {

        throw new Exception('This function does not work do not use');

        if ($card_id=='')

            throw new Exception('No m_id passed to draw_hack');

        $query = $game_time === null ? "INSERT INTO draw VALUES ($card_id,$rand,$range,0,NOW())" : "INSERT INTO draw VALUES ($card_id,0,0,$game_time,NOW())";

        return $this->query($query);

    }



    public function list_accounts($world,$batgig,$sort='')

    {

        $batgig=$batgig?1:0;

        $query="SELECT accounts.name,accounts.dp,accounts.cp,accounts.tutorial,sum(cards.skill_xp) AS total_xp, count(*) AS total_cards FROM accounts

                LEFT JOIN cards ON cards.account_key = accounts.key

                WHERE cards.status=0 AND world=$world AND accounts.batgig=$batgig

                GROUP BY accounts.name

                $sort";

        //$query="SELECT * FROM accounts WHERE world=$world AND batgig=$batgig ORDER BY dp DESC";

        return $this->query($query);

    }



    public function get_account($account,$cookie=null)

    {

        $query='';

        if ($cookie==null)

        {

            $world = $account->world;

            $kcid = $account->name;

            $query = "SELECT *,UNIX_TIMESTAMP(edit) AS timestamp FROM accounts WHERE world=$world AND name='$kcid'";

        } else {

            $query = "SELECT *,UNIX_TIMESTAMP(edit) AS timestamp FROM accounts WHERE cookie='$cookie'";

        }

        return $this->query($query);

    }



    public function is_alt($user_id,$world)

    {

        $query = "SELECT COUNT(*) AS count FROM accounts WHERE world=$world AND user_id=$user_id";

        return $this->query($query)['count']==1;

    }



    //duel stuff

    public function duel_record(account $account,$duel_obj)

    {

        $name = $account->name;

        $world = $account->world;

        $rank = $duel_obj->rank_num;

        $player_name = $duel_obj->alliance_name;

        $player_id = $duel_obj->user_id;

        $result = $duel_obj->result;

        $hash = $account->world.$account->name.$duel_obj->user_id;

        $query="REPLACE INTO duels (name,world,player_name,player_id,rank,result,hash) VALUES ('$name',$world,'$player_name',$player_id,$rank,$result,'$hash')";

        return $this->query($query);

    }



    //TODO:Clear the duel log when there is no arena on.

    public function duel_results(account $account)

    {

        $name = $account->name;

        $query="SELECT * FROM duels WHERE name='$name'";

        return $this->query($query);

    }



    public function get_sell_orders($for_page=true)

    {

        $query = $for_page ? "SELECT * FROM sell_order WHERE complete=0 AND (LEFT(status,7) = 'Auction' OR status='Placed')":

                             "SELECT * FROM sell_order WHERE complete=0 AND status='Placed'";

        return $this->query($query,true);

    }



    public function place_sell_order($by,$world,$m_id,$edition,$awake,$price)

    {

        $query = "INSERT INTO sell_order VALUES (0,'$by',$world,$m_id,0,$edition,$awake,$price,'Placed',0,NOW())";

        return $this->query($query);

    }



    public function update_sell_order($id,$u_id,$status)

    {

        $query ="UPDATE sell_order SET m_uid=$u_id,status='$status' WHERE id=$id";

        return $this->query($query);

    }



    public function maintance()

    {

        $query = 'DELETE FROM cards WHERE status = 9';

        return $this->query($query);

    }



    public function battle_log($id,$attacker,$defender,$epoch_s)

    {

        $query="REPALCE INTO logs ($id,$attacker,$defender,$epoch_s)";

        $this->query($query);

    }



    public function skcc_record_user($user_id,$world,$name,$rank)

    {

        $query="REPLACE INTO skcc (user_id,world,name,rank) VALUES ($user_id,$world,'$name',$rank)";

        return $this->query($query);

    }



    public function skcc_remove_known($world)

    {

        $query="SELECT * FROM accounts WHERE world=$world";

        $accounts=$this->query($query);

        foreach ($accounts as $account)

        {

            if (!$account['user_id']) continue;

            $query='UPDATE skcc SET checked=1 WHERE user_id='.$account['user_id'];

            $this->query($query);

        }

    }



    public function skcc_get_unchecked($world)

    {

        $query="SELECT * FROM skcc WHERE checked=0 AND world=$world ORDER BY name ASC";

        return $this->query($query);

    }



    public function skcc_record_present($user_id,$monster,$awake,$skills)

    {

        $query="INSERT INTO presents (user_id,monster,awake,skills) VALUES ($user_id,'$monster',$awake,'$skills')";

        return $this->query($query);

    }



    public function skcc_set_data($world,$user_id,$historybook,$active)

    {

        $query="UPDATE skcc SET history=$historybook, active='$active', checked=1 WHERE user_id=$user_id AND world=$world";

        return $this->query($query);

    }



    public function skcc_get_presents($world)

    {

        $query="SELECT monster,awake,skills,skcc.user_id AS user_id,skcc.name AS name,rank,history,DATE_FORMAT(active,'%b %d') as active,cp,dp,tutorial

                FROM presents

                INNER JOIN skcc ON presents.skcc = skcc.skcc

                INNER JOIN accounts ON presents.skcc = accounts.skcc

                WHERE active < DATE_SUB(curdate(), INTERVAL 3 MONTH) AND skcc.world=$world;";

        return mysqli_query($this->link,$query);

    }



    public function skcc_get_cards($world)

    {

        $query="SELECT id,edition,awake,skcc.user_id AS user_id,skcc.name AS name,rank,history,DATE_FORMAT(active,'%b %d') as active,tutorial

                FROM cards

                INNER JOIN skcc ON cards.skcc = skcc.skcc

                INNER JOIN accounts ON cards.skcc = accounts.skcc

                WHERE active < DATE_SUB(curdate(), INTERVAL 3 MONTH) AND skcc.world=$world";

        return mysqli_query($this->link,$query);

    }



    public function skcc_get_accounts($world)

    {

        $query="SELECT skcc.user_id AS user_id,skcc.name AS name,rank,history,DATE_FORMAT(active,'%b %d') as active,cp,dp,tutorial

                FROM skcc

                INNER JOIN accounts ON skcc.skcc = accounts.skcc

                WHERE active < DATE_SUB(curdate(), INTERVAL 1 MONTH) AND skcc.world=$world";

        return mysqli_query($this->link,$query);

    }

    

    public function skcc_get_id($user_id,$world)

    {

        $res = $this->query("SELECT skcc FROM skcc WHERE user_id=$user_id");

        if (isset($res['skcc']))

            return (int)$res['skcc'];

        return 0;

    }



}



?>
