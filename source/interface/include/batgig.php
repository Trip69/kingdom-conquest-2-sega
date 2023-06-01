<?php
require_once('include/kc.php');

class monster
{
    public $u_id;
    public $build_to;
    public $monster_obj;
    public $position;

    public function __construct(array $monster)
    {
        $this->u_id = $monster['u_id'];
        $this->build_to = $monster['build_to'];
    }
    
    public static function get_monster(kc $kc)
    {
        $monsters = $kc->db->get_monsters($kc->account);
        $ret=array();
        foreach ($monsters as $monster)
            $ret[]=new monster($monster);
        return $ret;
    }

}

class unit
{
    public $field_id;
    public $unit_num;
    public $commander;
    public $commander_present;
    public $train_level;
    public $ruin_level;
    public $ruin_a_level;
    public $monsters = array();
    public $unit_obj;
    
    public function __construct(array $unit)
    {
        $this->field_id = (int) substr($unit['hash'],0,strlen($unit['hash']-1));
        $this->unit_num = (int) substr($unit['hash'],-1);
        $this->commander = $unit['commander'];
        $this->train_level = $unit['train'];
    }

    public static function get_units(kc $kc)
    {
        $units = $kc->db->get_units($kc->account);
        $res = array();
        foreach ($units as $unit)
            $res[] = new unit($unit);
        return $res;
    }
    
    public static function assign_monsters(array &$units,array &$monsters,kc $kc)
    {
        if ($kc->my_monsters_obj == null)
            $kc->get_units($kc->homebase_id);
        foreach($monsters as $monster)
            foreach ($kc->my_monsters_obj as $monster_obj)
                if ($monster->u_id == $monster_obj->uniq_data->u_id)
                {
                    $monster->monster_obj = $monster_obj;
                    break;
                }
                
        foreach($kc->$unit_data as $unit_obj)
            foreach($units as $unit)
            {
                $unit->monsters = array();
                if ($unit_obj->field_id==$uint->field_id && $unit_obj->deck_idx == $unit->$unit_num)
                {
                    if ($unit_obj->commander->uniq_id !== 0 && $unit_obj->commander->uniq_id !== $unit->commander)
                        $unit->commander == $unit_obj->commander->uniq_id;
                    $unit->commander_present = $unit_obj->commander->uniq_id > 0;
                    $uint->unit_obj = $unit_obj;
                    foreach ($uint->unit_obj->monsters as $unit_monster)
                    {
                        if ($unit_monster->uniq_id==0) continue;
                        foreach ($monsters as $monster)
                            if ($monster->u_id == $unit_monster->uniq_id)
                            {
                                $monster->position = $unit_monster->posmask;
                                $uint->monsters[] = $monster;
                                break;
                            }
                    }
                    break;
                }
            }
    }
}

class base
{
    public $field_id;
    public $is_military;
    public $save_wood;
    public $save_stone;
    public $save_iron;
}

class batgig
{
    private $accounts = array();
    public $echo = array();
    
    public function __construct($all = true)
    {
        if ($all) $this->accounts = account::get_auto_accounts(new db);
    }
    
    public function refresh_all(kc $kc=null)
    {
        if ($kc!==null) $this->accounts=array($kc->account);
        foreach($this->accounts as $account)
        {
            $kc = $kc === null ? new kc($account->name,$account->password,$account->android,$account->world,$account->cookie,null,false) : $kc;
            if ($account->cookie == null)
                $kc->logon();
            $kc->get_town(0);
            $kc->get_units($kc->homebase_id);
        }
    }
    
    public function do_all(kc $kc=null)
    {
        if ($kc!==null) $this->accounts=array($kc->account);
        foreach($this->accounts as $account)
        {
            $kc = $kc === null ? new kc($account->name,$account->password,$account->android,$account->world,$account->cookie,null,false) : $kc;
            if ($account->auto_arena) $this->do_arena($kc);
        }
    }
    
    public function do_arena(kc $kc,$force=false)
    {
        $kc->get_arena();
        if (!$kc->arena_data->duel_info->is_open) return;
        if ($kc->arena_data->duel_info->free_num == 0) return;
        $kc->get_duel_page();
        if (!$kc->duel_data->is_regist)
            throw new Exception('You are not registered for duel');
        $hour=date('G');
        if (!$force && !($hour==8 || $hour==9)) return;

        $my_rank=$kc->duel_data->rank;
        $results=$kc->db->duel_results($kc->account);
        $pages_viewed=0;
        do
        {
            $kc->get_duel_page();
            $pages_viewed++;
            $target=array('id'=>0,'rank'=>0);
            foreach ($kc->duel_data->duel_monsters as $duel)
            {
                if ($duel->result>0)
                {
                    $kc->db->duel_record($kc->account,$duel);
                    continue;
                }
                $fought=false;
                $loss=false;
                foreach ($results as $result)
                    if (    $result['player_id']==$duel->user_id && 
                            $result['result']==1 && 
                            ($kc->account->arena_mode == 1 || ($kc->account->arena_mode == 2 && $duel->rank_num <= $my_rank - 30))
                       ) //win against player & playing for win or player is 30 or more higher
                    {
                        //$kc->duel($duel->user_id);
                        $fought=true;
                        break;
                    }
                    elseif ($result['result']>1) //loss or draw
                    {
                        $loss=true;
                        break;
                    }
                
                if ($fought) break;
                if ($loss || $duel->rank_num < $target['rank']) continue; //if rank is less than target it will be harder whatever style we play

                $fight=false;
                switch($kc->account->arena_mode)
                {
                    case 1: //wins
                        if ($duel->rank_num >= $my_rank + 30)
                            $target=array('id'=>$duel->user_id,'rank'=>$duel->rank_num);
                            break;
                    case 2: //rank
                        if ($duel->rank_num <= $my_rank - 30 && $duel->rank_num >= $my_rank - 55) //between 30 and 70 higher, 500 points
                            $target=array('id'=>$duel->user_id,'rank'=>$duel->rank_num);
                        elseif ($pages_viewed > 15 && $duel->rank_num <= $my_rank - 30) //30 or more higher,500 points
                            $target=array('id'=>$duel->user_id,'rank'=>$duel->rank_num);
                        elseif ($pages_viewed > 20 && $duel->rank_num <= $my_rank + 30) //equal rank. 200 ponts
                            $target=array('id'=>$duel->user_id,'rank'=>$duel->rank_num);
                        break;
                }
            }
            if ($target['id']>0)
            {
                echo 'Duel verses player ranked '.$target['rank'].'<br />';
                //$kc->duel($target['id']);
            }
        } while ($kc->duel_data->remain > 0);
    }
}
?>
