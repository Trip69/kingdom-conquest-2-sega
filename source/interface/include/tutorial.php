<?php

set_time_limit(90);

    class tutorial
    {
        private $kc;
        public $stage = -2;
        public $wait_time = 30000;
        
        function __construct($kc)
        {
            $this->kc=$kc;
        }
        
        private function produce_monsters($max)
        {
            $this->kc->get_units($this->kc->homebase_id);
            $build_echo='';
            $monster_list=utils::return_monsters(kc::$monster_obj,
                                                 $this->kc->unit_data->monsters,
                                                 null,
                                                 null,
                                                 'idle',null,null,null,null,null,-1,0);
            $build_echo='';
            for ($a=0;$a<count($monster_list);$a++) {
                $current = $monster_list[$a]->uniq_data->hc;
                $needed = $max - $current < 1 ? 0 : $max - $current;
                $max = $this->kc->max_production($monster_list[$a]->uniq_data->u_id);
                
                $build_amount = $needed > $max ? $max : $needed;
                
                if ($build_amount>0) {
                    //echo $build_echo; exit();
                    $this->kc->produce_monster($monster_list[$a]->uniq_data->u_id,$build_amount);
                    $build_echo .= $build_amount . ' ' . kc::$monsters[$monster_list[$a]->m_id] . ' building.<BR />';
                    $this->kc->get_units($base_id);
                }
            }
            return $build_echo;
        }
        
        public function get_stage()
        {
            //complete_quests gets town data
            //$this->kc->get_town(0);
            while($this->kc->complete_quests()) {}
            
            if ($this->kc->construction_queue(0))
                //Error that something is being built
                $this->stage=0;
            elseif ($this->kc->check_quest_exists(30000100)
                    &! $this->kc->check_tile_exists(0,'Ironworks',60,49))
                //Build 3 resources - 30000100 is sawmill quest
                $this->stage=1;
            elseif ($this->kc->check_quest_exists(30000400) &! $this->kc->check_tile_exists(0,'Warehouse',60,57))
                //Build warehouses - 30000400 is warehouse quest
                $this->stage=2;
            elseif ($this->kc->check_quest_exists(30000500) &&
                    $this->kc->check_tile_exists(0,'Charred Field',52,49) && 
                    $this->kc->check_tile_exists(0,'Charred Field',52,49)->lev==1)
                        //build charred field - 30000500 is charred field quest
                        $this->stage=3;
            elseif (( $this->kc->check_quest_exists(30000500) || $this->kc->check_quest_exists(30000600) ) &!
                    $this->kc->check_tile_exists(0,'Tower of Training',57,57))
                //build tower of training - 30000600 tower of training
                $this->stage=4;
            elseif($this->kc->check_tile_exists(0,'Charred Field',54,48))
                //set commander,monster and attack and read report
                $this->stage=$this->kc->check_tile_exists(0,'Charred Field',54,48)->lev==1?5:6;
            elseif(!$this->kc->check_tile_exists(0,'Ironworks',60,49))
                throw new Exception('Tutorial has not been followed. Abandon recommended');
            elseif($this->kc->check_tile_exists(0,'Ironworks',60,49)->lev==1)
                //collect dungeon quests and synth monster, build res
                //30001200 - dungeon quest
                $this->stage=7;
            elseif($this->kc->check_quest_exists(30001200))
                //dungeon run needed still
                $this->stage=8;
            elseif($this->kc->check_tile_exists(0,'Ironworks',60,49)->lev==2 &!
                   $this->kc->check_tile_exists(0,'Ironworks',60,52))
                //build 2nd resource and lay roads
                $this->stage=9;
            elseif($this->kc->check_tile_exists(0,'Ironworks',60,52) &&
                   $this->kc->check_tile_exists(0,'Ironworks',60,52)->lev==1)
                //build 2nd resource to level 2
                $this->stage=10;
            elseif(($this->kc->check_quest_exists(30001600) ||
                    $this->kc->check_quest_exists(30001400)) &&
                    $this->kc->check_tile_exists(0,'Ironworks',60,52) &&
                    $this->kc->check_tile_exists(0,'Ironworks',60,52)->lev==2)
                //30001600 : build unit to 700 Preparing for battle
                //30001400 : expand teritory 1
                $this->stage=11;
            elseif($this->kc->check_quest_exists(30006400) && !($this->kc->check_tile_exists(0,'Warehouse',60,57)->lev == 2))
                $this->stage=12;
            elseif($this->kc->check_quest_exists(30007500) && ($this->kc->check_tile_exists(0,'Old Castle',53,53)->lev == 1))
                $this->stage=13;
            elseif($this->kc->check_quest_exists(30008300))
                //Production Development 3
                $this->stage=14;
            elseif($this->kc->check_quest_exists(30001700))
                //Monster Nest
                $this->stage=15;
            elseif($this->kc->check_quest_exists(30010200))
                //recruiting commanders
                $this->stage=16;
            elseif($this->kc->check_quest_exists(30008400))
                //Warehouse Expansion 3
                $this->stage=17;
            elseif($this->kc->check_quest_exists(30008900))
                //boosting stats
                $this->stage=18;
            elseif($this->kc->check_quest_exists(30001900) && $this->kc->check_tile_exists(0,'Purple Prison',41,42)->lev == 2)
                //Prison break quest & purple prison built
                $this->stage=19;
            elseif(count($this->kc->get_tiles('Charred Field',$this->kc->homebase_id,39,61,34,50)) > 0)
                $this->stage=20;
            elseif($this->kc->check_quest_exists(30008800) || $this->kc->check_quest_exists(30010700)) //research, profile
                $this->stage=21;
            elseif($this->kc->check_quest_exists(30011300) || $this->kc->check_quest_exists(30001890)) //skill learn, doom guild
                $this->stage=22;
            $this->kc->tutorial_stage = $this->stage;
            return $this->stage;    
        }
        
        public function do_stage($stage)
        {
            switch ($stage)
            {
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                    return $this->do_stage1to5($stage);
                case 6:
                case 7:
                case 8:
                case 9:
                case 10:
                    return $this->do_stage6to10($stage);
                case 11:
                case 12:
                case 13:
                case 14:
                case 15:
                    return $this->do_stage11to15($stage);
                case 16:
                case 17:
                case 18:
                case 19:
                case 20:
                    return $this->do_stage16to20($stage);
                case 21:
                case 22:
                case 23:
                case 24:
                case 25:
                    return $this->do_stage21to25($stage);
            }
        }
        
        private function do_stage1to5($stage)
        {
            $base_id=$this->kc->homebase_id;
            switch ($stage)
            {
                case 1:
                    $this->kc->build_if_less_than($base_id,'Sawmill',47,49,1);
                    $this->kc->build_if_less_than($base_id,'Stoneworks',47,58,1);
                    $this->kc->build_if_less_than($base_id,'Ironworks',60,49,1);
                    return 'Building, initial resources, Please wait 30 seconds';
                case 2:
                    $this->kc->build_if_less_than($base_id,'Warehouse',60,60,1);
                    $this->kc->build_if_less_than($base_id,'Warehouse',57,60,1);
                    $this->kc->build_if_less_than($base_id,'Warehouse',60,57,1);
                    $this->wait_time=45000;
                    return 'Building, initial storehouses, Please wait 45 seconds';
                case 3:
                    //sawmill,stoneworks,warehouse
                    $this->kc->complete_quests();
                    $this->kc->complete_quests();
                    $this->kc->complete_quests();
                    //one for luck...
                    $this->kc->complete_quests();
                    $this->kc->build($base_id,'Charred Field',52,49);
                    return 'Quests completed<BR />Removing charred field 1, Please wait 30 seconds';
                case 4:
                    $this->kc->remove_tile($base_id,52,49);
                    $this->kc->complete_quests();
                    $this->kc->build($base_id,'Tower of Training',57,57);
                    return 'Building Tower of Training, Please wait 30 seconds';
                case 5:
                    $this->kc->complete_quests();
                    if (count($this->kc->get_tiles('Tower of Training',$this->kc->homebase_id))==0)
                    { //?This should not happen but does
                        $this->kc->build($base_id,'Tower of Training',57,57);
                        $this->wait_time=35000;
                        return 'Building Tower of Training, Please wait 30 seconds';
                    }
                    //Set monster in a uint
                    $this->kc->get_units($base_id);
                    
                    //check for commander
                    if ($this->kc->unit_data->units[0]->commander->uniq_id==0) {
                        $com_id=$this->kc->unit_data->commanders[0]->uniq_data->u_id;
                        $this->kc->set_commander($com_id,$base_id,0);
                    }

                    //check for monster
                    $found_mon=false;
                    foreach ($this->kc->unit_data->units[0]->monsters as $monster)
                        if ($monster->uniq_id>0)
                            $found_mon=true;
                            
                    if (!$found_mon) {
                        $mon_id=null;
                        foreach($this->kc->unit_data->monsters as $monster)
                            if (substr($monster->m_id,-1)==1 && $monster->uniq_data->state == 0) {
                                $mon_id=$monster->uniq_data->u_id;
                                break;
                            }
                        if(!$mon_id) throw new Exception('Tutorial: Setting finding a monster to set in a unit failed');
                        $this->kc->set_monster($base_id,0,'FM',$mon_id);
                        $this->kc->complete_quests();
                    }

                    if ($this->produce_monsters(100) != '')
                        return 'Building monsters to 100';
                    
                    $mapinfo = $this->kc->view_map($base_id);
                    $this->kc->complete_quests();
                    
                    $map_sqr=utils::tutorial_attack_square($mapinfo,$this->kc->town->field_x,$this->kc->town->field_y);
                    //echo $map_sqr->x.','.$map_sqr->y.'<BR />';
                    //exit();
                    $map_id=$map_sqr->id;
                    if (!$map_id)
                        throw new Exception('Tutorial: First Attack: No map square found');

                    $this->kc->attack_id($base_id,0,$map_id);
                    $this->kc->build($base_id,'Charred Field',54,48);
                    $this->wait_time=30000;
                    return 'First attack sent.<BR />Removing charred field 2<BR /><BR />Will keep checking every 30 seconds.';
            }
        }
        
        private function do_stage6to10($stage)
        {
            $base_id=$this->kc->homebase_id;
            switch ($stage)
            {
                case 6:
                    $this->kc->complete_quests();
                    $reports=null;
                    $reports=$this->kc->view_personal_report(0,1);
                    if (count($reports->reports)==0)
                        return 'Waiting for attack report';
                    $this->kc->view_personal_report_detail($reports->reports[0]->report_id);
                    $this->kc->complete_quests();
                    $this->kc->remove_tile($base_id,54,48);
                    $this->kc->change_class(3);
                    $this->wait_time=10 * 60 * 1000;
                    return 'You need to run the dungeon';
                    //Dungeon                    
                case 7:
                    $this->kc->get_shop();
                    foreach ($this->kc->shop_data->packs as $pack)
                        if ($pack->id==101000 && $pack->ticket_num>0)
                        {
                            $this->kc->draw_pack(101000,1,$pack->ticket_num);
                            $this->kc->complete_quests();
                        }
                    if ($this->kc->check_quest_exists(30001200,false)) {
                        $this->wait_time=10 * 60 * 1000;
                        return 'You need to run the dungeon';
                    }
                        
                    //Monster Synth
                    $this->kc->get_units(0);
                    
                    $monster_list=null;
                    $rarity = 'common';
                    $drawn = false;
                    for($race=0;$race<9;$race++)
                    {
                        $monster_list=utils::return_monsters(kc::$monster_obj,
                                                             $this->kc->unit_data->monsters,
                                                             $race,
                                                             $rarity,
                                                             'inpool',null,null,null,null,true,-1,null,null,9);
                        if (count($monster_list)>1)
                            break;
                        elseif ($race==4 && $rarity == 'common') {
                            $rarity = 'uncommon';
                            $race=0;
                        }
                        elseif ($race==4 && $rarity == 'uncommon') {
                            $rarity = 'rare';
                            $race=0;
                        }
                        elseif ($race==8 && count($monster_list)<2 && $this->kc->crystals >= 2000 && !$drawn)
                        {
                            $this->kc->draw_pack(301000,1,1);
                            $this->kc->get_units($this->kc->homebase_id);
                            $drawn=true;
                            $race=0;
                            $rarity='common';
                        }
                    }
                    if (count($monster_list)<2)
                        throw new Exception('Failed to find common or uncommon monsters to synth');

                    $this->kc->skill_enhance(   $monster_list[0]->uniq_data->u_id,
                                                $monster_list[0]->skills[0]->id,
                                                0,
                                                $monster_list[1]->uniq_data->u_id);
                    $this->kc->complete_quests();
                    
                    //Level 2 sawmill,stone,iron
                    $this->kc->build_if_less_than($base_id,'Sawmill',47,49,2);
                    $this->kc->build_if_less_than($base_id,'Stoneworks',47,58,2);
                    $this->kc->build_if_less_than($base_id,'Ironworks',60,49,2);
                    
                    $this->wait_time=90000;
                    return 'Syth Quest Complete<BR />Build Level 2 resources<BR /><BR />Please wait 90 seconds.';
                case 8:
                    $this->kc->get_shop();
                    foreach ($this->kc->shop_data->packs as $pack)
                        if ($pack->id==101000 && $pack->ticket_num>0)
                        {
                            $this->kc->get_shop();
                            $this->kc->draw_pack(101000,1);
                            $this->kc->complete_quests();
                        }
                    if ($this->kc->check_quest_exists(30001200,false))
                        return 'You need to run the dungeon';
                    return 'Dungeon Quest Completed';                    
                case 9:
                    $this->kc->complete_quests();
                    if (!$this->kc->check_tile_exists(0,'Sawmill',47,52))
                        $this->kc->build($base_id,'Sawmill',47,52);
                    if (!$this->kc->check_tile_exists(0,'Stoneworks',47,55))
                        $this->kc->build($base_id,'Stoneworks',47,55);
                    if (!$this->kc->check_tile_exists(0,'Ironworks',60,52))
                    $this->kc->build($base_id,'Ironworks',60,52);
                    //'
                    $roads='&add=5252&add=5152&add=5052&add=5051&add=5058&add=5056&add=5057&add=5054&add=5053&add=5055&add=5059&add=5752&add=5852&add=5952&add=5950&add=5951';
                    $this->kc->build_road(0,$roads);
                    $this->wait_time=45000;
                    return 'Second resource buildings started<BR />Roads laid<BR /><BR />Please wait 45 seconds';
                case 10:
                    $this->kc->complete_quests();
                    if ($this->kc->check_tile_exists(0,'Sawmill',47,52)->lev==1)
                        $this->kc->build($base_id,'Sawmill',47,52);
                    if ($this->kc->check_tile_exists(0,'Stoneworks',47,55)->lev==1)
                        $this->kc->build($base_id,'Stoneworks',47,55);
                    if ($this->kc->check_tile_exists(0,'Ironworks',60,52)->lev==1)
                        $this->kc->build($base_id,'Ironworks',60,52);
                    $this->wait_time=90000;
                    return 'Second resource structures building to level 2<BR /><BR />Please wait 90 seconds';
            }
        }
        
        private function do_stage11to15($stage)
        {
            $base_id=$this->kc->homebase_id;
            switch ($stage)
            {
                case 11:
                    //Open area
                    $this->kc->complete_quests();
                    if ($this->kc->town->resource->item_area_release_0 >= 3)
                        {
                            if ($this->kc->town->areas[1]==0) {
                                $this->kc->open_area(1);
                                $this->kc->complete_quests();
                            }
                            if ($this->kc->town->areas[2]==0)
                                $this->kc->open_area(2);
                        }
                    $this->kc->complete_quests();
                    
                    //Set monster 2 in unit & build to 350
                    $this->kc->get_units($base_id);
                    
                    foreach (array('FM','BM') as $position)
                        if ($this->kc->position_empty($base_id,0,$position))
                        {
                            $monster_list=utils::return_monsters(kc::$monster_obj,
                                                                 $this->kc->unit_data->monsters,
                                                                 null,
                                                                 null,
                                                                 'inpool',
                                                                 100,null,null,null,null,-1,0);
                            if (count($monster_list)==0)
                                throw new Exception('No rare monster found to put in the unit');
                            
                            //shuffle list as there will be errors sometimes as there is no cost check
                            shuffle($monster_list);
                            $this->kc->set_monster($base_id,0,$position,$monster_list[0]->uniq_data->u_id);
                            $this->kc->get_units($base_id);
                        }

                    $build_echo = $this->produce_monsters(350);
                    
                    $this->wait_time=10 * 60 * 1000;
                    if ($build_echo=='')
                        $build_echo = '<BR />Nothing produced<BR />';
                    $this->kc->complete_quests();
                    return $this->kc->town->resource->cp_free . " CP<BR /><BR />Area Open<BR />$build_echo<BR />Waiting for unit built to 700mc.";
                case 12:
                    $this->kc->build_if_less_than($base_id,'Warehouse',60,60,2);
                    $this->kc->build_if_less_than($base_id,'Warehouse',57,60,2);
                    $this->kc->build_if_less_than($base_id,'Warehouse',60,57,2);
                    $this->wait_time=9 * 60 * 1000;
                    return 'Warehouses building to level 2<BR /><BR />Please wait 9 minutes';
                case 13:
                    $this->kc->build_if_less_than($base_id,'Old Castle',53,53,2);
                    $this->kc->build($base_id,'Residence',57,53);
                    $this->kc->build($base_id,'Shop',51,48);
                    $this->wait_time=60 * 1000;
                    return 'Building shop, residence and castle<BR /><BR />Please wait 1 minute';
                case 14:
                    switch ($this->kc->check_tile_exists(0,'Sawmill',47,49)->lev)
                    {
                            case 2:
                                $this->kc->build_if_less_than($base_id,'Sawmill',47,49,3);
                                $this->kc->build_if_less_than($base_id,'Sawmill',47,52,3);
                                $this->kc->build_if_less_than($base_id,'Stoneworks',47,55,3);
                                $this->wait_time=3 * 60 * 1000;
                                return 'Building resources part 1 of 8<BR /><BR />Please wait 3 minutes';
                            case 3:
                                $this->kc->build_if_less_than($base_id,'Sawmill',47,49,4);
                                $this->kc->build_if_less_than($base_id,'Sawmill',47,52,4);
                                $this->kc->build_if_less_than($base_id,'Stoneworks',47,55,4);
                                $this->wait_time=9 * 60 * 1000;
                                return 'Building resources part 2 of 8<BR /><BR />Please wait 9 minutes';
                            default:
                            case 4:
                                switch ($this->kc->check_tile_exists(0,'Stoneworks',47,58)->lev)
                                {
                                    case 2:
                                        $this->kc->build_if_less_than($base_id,'Stoneworks',47,58,3);
                                        $this->kc->build_if_less_than($base_id,'Ironworks',60,52,3);
                                        $this->kc->build_if_less_than($base_id,'Ironworks',60,49,3);
                                        $this->wait_time=3 * 60 * 1000;
                                        return 'Building resources part 3 of 8<BR /><BR />Please wait 3 minutes';
                                    case 3:
                                        $this->kc->build_if_less_than($base_id,'Stoneworks',47,58,4);
                                        $this->kc->build_if_less_than($base_id,'Ironworks',60,52,4);
                                        $this->kc->build_if_less_than($base_id,'Ironworks',60,49,4);
                                        $this->wait_time=9 * 60 * 1000;
                                        return 'Building resources part 4 of 8<BR /><BR />Please wait 9 minutes';
                                    default:
                                    case 4:
                                        if (!$this->kc->check_tile_exists(0,'Stoneworks',50,60))
                                        {
                                            $this->kc->build_if_less_than($base_id,'Stoneworks',50,60,1);
                                            $this->kc->build_if_less_than($base_id,'Sawmill',51,57,1);
                                            $this->kc->build_if_less_than($base_id,'Ironworks',54,57,1);
                                            $this->wait_time=30 * 1000;
                                            return 'Building resources part 5 of 8<BR /><BR />Please wait 30 seconds';
                                        } else {
                                            $this->kc->build_if_less_than($base_id,'Stoneworks',50,60,4);
                                            $this->kc->build_if_less_than($base_id,'Sawmill',51,57,4);
                                            $this->kc->build_if_less_than($base_id,'Ironworks',54,57,4);
                                            switch ($this->kc->check_tile_exists(0,'Stoneworks',50,60)->lev)
                                            {
                                                case 1:
                                                    $this->wait_time=45 * 1000;
                                                    return 'Building resources part 6 of 8<BR /><BR />Please wait 45 seconds';                                                    
                                                case 2: 
                                                    $this->wait_time=3 * 60 * 1000;
                                                    return 'Building resources part 7 of 8<BR /><BR />Please wait 3 minutes';
                                                case 3:
                                                    $this->wait_time=9 * 60 * 1000;
                                                    return 'Building resources part 8 of 8<BR /><BR />Please wait 9 minutes';
                                                default:
                                                    //Something went wrong
                                                    $build=false;
                                                    foreach($this->kc->town->tiles as $building)
                                                    {//build($field_id,$tile,$x,$y)  
                                                        $building_name=kc::$tiles[$building->tile];
                                                        if (($building_name=='Sawmill'||$building_name=='Stoneworks'||$building_name=='Ironworks')&&$building->lev<4)
                                                            {
                                                                $this->kc->build($this->kc->homebase_id,$building->tile,$building->x,$building->y);
                                                                $build=true;
                                                            }
                                                    }
                                                    $this->wait_time=5 * 60 * 1000;
                                                    if ($build)
                                                        return 'Found something to build please wait.';
                                                    else
                                                        return 'Still some error in stage 14 code';
                                                        
                                            }
                                        }
                                }
                                
                    }
                    return 'Error in stage 14 code';
                case 15:
                    $mc=$this->kc->check_attack_ready($this->kc->homebase_id,0);
                    if ($mc>=700)
                    {
                        $this->kc->attack_nest(0,1);
                        $this->wait_time=10 * 1000;
                        return 'Attacking Nest<BR /><BR />Please wait 10 seconds';
                    } else {
                        //Someone wiped the alt.
                        //$this->do_stage11to15(11);
                        //$this->wait_time=10 * 60 * 1000;
                        return $this->do_stage11to15(11);
                        //return 'MC is less than 700, building.';
                    }
             }
        }
        
        private function do_stage16to20($stage)
        {
            $base_id=$this->kc->homebase_id;
            switch ($stage)
            {
                case 16:
                    if ($this->kc->town->areas[3]==0)
                        $this->kc->open_area(3);
                    if ($this->kc->town->areas[4]==0)
                        $this->kc->open_area(4);
                    if ($this->kc->town->areas[5]==0)
                        $this->kc->open_area(5);
                    if ($this->kc->check_tile_exists(0,'Charred Field',56,49)->lev==1)
                        $this->kc->build($base_id,'Charred Field',56,49);
                    if ($this->kc->check_tile_exists(0,'Charred Field',60,43)->lev==1)
                        $this->kc->build($base_id,'Charred Field',60,43);
                    if ($this->kc->check_tile_exists(0,'Charred Field',59,40)->lev==1)
                        $this->kc->build($base_id,'Charred Field',59,40);
                    
                    $com_data = $this->kc->get_commander_hire();
                    $name_id='';
                    $com_id='';
                    foreach ($com_data->commanders as $commander)
                        if ($commander->price==0 && substr($commander->c_id,-1) == 0) {
                            $name_id=$commander->n_id;
                            $com_id=$commander->c_id;
                            break;
                        }
                    
                    if ($name_id=='')
                        throw new Exception('Unable to find commander data');
                    
                    $this->kc->hire_commander($com_id,$name_id);
                    $this->wait_time=45 * 1000;
                    return 'Removing charred fileds.<BR /><BR />Please wait 45 seconds';

                case 17:
                    $this->kc->build_if_less_than($base_id,'Warehouse',60,60,3);
                    $this->kc->build_if_less_than($base_id,'Warehouse',57,60,3);
                    $this->kc->build_if_less_than($base_id,'Warehouse',60,57,3);

                    if ($this->kc->check_tile_exists($base_id,'Charred Field',56,49))
                        $this->kc->remove_tile($base_id,56,49);
                    if ($this->kc->check_tile_exists($base_id,'Charred Field',60,43))
                        $this->kc->remove_tile($base_id,60,43);
                    if ($this->kc->check_tile_exists($base_id,'Charred Field',59,40))
                        $this->kc->remove_tile($base_id,59,40);
                    
                    //touch house
                    //$this->kc->touch_tile($base_id,57,53);
                    
                    //touch market
                    //$this->kc->touch_tile($base_id,48,55);
                    $this->kc->collect_blue_crystals();
                    
                    $this->wait_time=15 * 60 * 1000;
                    return 'Building Lv 3 warehouse.<BR /><BR />Please wait 15 minutes';
                case 18:
                    $this->kc->get_units($this->kc->homebase_id);
                    $card=null;
                    foreach ($this->kc->unit_data->monsters as $monster)
                        if ($monster->status_point->remain>0) //status_point->remain
                            $card = $monster;
                    if ($card==null)
                    {
                        $this->do_stage1to5(5);
                        return 'No monster for > 1 level to increase stats so sending another attack';
                    }
                    $this->kc->add_stats_points($card->uniq_data->u_id,1);

                    $this->kc->send_mail('ALLA',null,null);
                    
                    //barracks
                    $this->kc->build_if_less_than($base_id,'Monster Barracks',55,48,1);
                    //Purple prison,monster 
                    $this->kc->build_if_less_than($base_id,'Purple Prison',41,42,2);
                    //withered forrest
                    $this->kc->build_if_less_than($base_id,'Withered Forest',48,40,2);
                    
                    //crystal pack
                    if ($this->kc->crystals>=2000)
                        $this->kc->draw_pack(301000,1);
                    $this->wait_time=213 * 60 * 1000;
                    return 'Building barracks withered forrect and purple prison.<BR /><BR />Please wait 3 1/2 hours';
                case 19:
                    //remove purple prison
                    if ($this->kc->check_tile_exists($base_id,'Purple Prison',41,42)->lev==2)
                        $this->kc->remove_tile($base_id,41,42);
                    //remove withered forrest
                    if ($this->kc->check_tile_exists($base_id,'Withered Forest',48,40)->lev==2)
                        $this->kc->remove_tile($base_id,48,40);
                    $this->wait_time=10 * 1000;
                    return 'Purple prison removed. Quests completed';
                case 20:
                    //collect blue crystals
                    $this->kc->collect_blue_crystals();
                    
                    //For some reason I have found these areas to not be open but the tutorial stage to be at this level
                    if ($this->kc->town->areas[3]==0)
                        $this->kc->open_area(3);
                    if ($this->kc->town->areas[4]==0)
                        $this->kc->open_area(4);
                    if ($this->kc->town->areas[5]==0)
                        $this->kc->open_area(5);

                    $count=0;
                    if($this->kc->build_if_less_than($base_id,'Purple Prison',41,42,2)!==null)
                        $count++;
                    if($this->kc->build_if_less_than($base_id,'Withered Forest',48,40,2) !== null)
                        $count++;
                    if ($this->kc->check_tile_exists($base_id,'Purple Prison',41,42)->lev==2)
                        $this->kc->remove_tile($base_id,41,42);
                    if ($this->kc->check_tile_exists($base_id,'Withered Forest',48,40)->lev==2)
                        $this->kc->remove_tile($base_id,48,40);

                    $cfields = $this->kc->get_tiles('Charred Field',$base_id,39,61,34,50);
                    $just_charred=$count==0;
                    while(count($cfields)>0)
                    {
                        foreach ($cfields as $cfield)
                            if($cfield->lev==1)
                                {
                                    $this->kc->build_if_less_than($base_id,'Charred Field',$cfield->x,$cfield->y,2);
                                    $count++;
                                    if ($count>=3)
                                        break;
                                }
                            elseif ($cfield->lev==2) {
                                //TODO:This is trying to remove charred files in areas that are not open.
                                $this->kc->remove_tile($base_id,$cfield->x,$cfield->y);
                            }
                        if (!$just_charred) break;
                        sleep(60);
                        $cfields = $this->kc->get_tiles('Charred Field',$base_id,39,61,34,50);
                    }
                    if ($just_charred) return $this->do_stage21to25(21);
                    $this->wait_time=60 * 1000;
                    return 'Removing charred fields<BR /><BR />Please wait 1 minute';
            }
        }
        
        private function do_stage21to25($stage)
        {
            $base_id=$this->kc->homebase_id;
            switch ($stage)
            {
                case 21:
                    //Build research
                    $this->kc->build_if_less_than($base_id,'Undead Research Lab',56,35,1);
                    //build monster nest
                    $this->kc->build_if_less_than($base_id,'Monster Nest',49,37,2);
                    if ($this->kc->check_tile_exists(0,'Monster Nest',49,37)->lev==2)
                        $this->kc->remove_tile($base_id,49,37);
                    //Set null comment
                    $this->kc->profile_comment('');
                    //Monster barracks
                    $this->kc->build_if_less_than($base_id,'Monster Barracks',55,48,2);
                    $this->kc->complete_quests();
                    $this->kc->get_ranking(1,1,0);
                    $this->kc->complete_quests();
                    $this->kc->profile_territory(1);
                    
                    //Build some monsters as quests give resource
                    $monster_list=utils::return_monsters(kc::$monster_obj,$this->kc->unit_data->monsters,null,null,'idle',null,null,null,null,null,-1,null,null,20);
                    for ($a=0;$a<(count($monster_list)>2?2:count($monster_list));$a++) {
                        $current = $monster_list[$a]->uniq_data->hc;
                        $needed = 700 - $current < 1 ? 0 : 700 - $current;
                        $max = $this->kc->max_production($monster_list[$a]->uniq_data->u_id);
                        $build_amount = $needed > $max ? $max : $needed;
                        if ($build_amount>0) {
                            $this->kc->produce_monster($monster_list[$a]->uniq_data->u_id,$build_amount);
                            $this->kc->get_units($base_id);
                        }
                    }
                    $this->kc->complete_quests();
                    //bid on auction is going to be done already
                    $this->kc->complete_quests();
                    //List an auction item.... is going to have been done.
                    $this->wait_time=2 * 60 * 60 * 1000;
                    return $build_echo.'Removing monster nest<BR /><BR />Please wait 2 hours';
                case 22:
                    //skill learn
                    if ($this->kc->check_quest_exists(30011300))
                    {
                        if ($this->kc->dp < 100)
                            throw new Exception('Less than 100 DP, cant try learn synth');
                        $monster_list=utils::return_monsters(kc::$monster_obj,$this->kc->unit_data->monsters,null,null,'idle',null,null,null,null,null,-1,null,null,20);
                        if (count($monster_list)==0)
                            throw new Exception('No monster in a unit to learn a skill');
                        shuffle($monster_list);
                        $teach = $monster_list[0];
                        $monster_learn=utils::return_monsters(kc::$monster_obj,$this->kc->unit_data->monsters,null,'common','inpool',null,null,null,null,null,-1,null,null,20);
                        if (count($monster_learn)==0)
                            $monster_learn=utils::return_monsters(kc::$monster_obj,$this->kc->unit_data->monsters,null,'uncommon','inpool',null,null,null,null,null,-1,null,null,20);
                        if (count($monster_learn)==0)
                            $monster_learn=utils::return_monsters(kc::$monster_obj,$this->kc->unit_data->monsters,null,'uncommon','rare',null,null,null,null,null,-1,null,null,20);
                        foreach ($monster_learn as $key => $monster)
                            if ($monster->uniq_data->u_id == $teach->uniq_data->u_id)
                                unset($monster_learn[$key]);
                        if (count($monster_learn)==0)
                            throw new Exception('No monster to lear a skill from');
                        shuffle($monster_learn);
                        $this->kc->skill_learn($teach->uniq_data->u_id,$monster_learn[0]->uniq_data->u_id,0);
                        $this->kc->complete_quests(true);
                    }
                    if ($this->kc->resource_data->item_area_release_0 >= 4 && $this->kc->town->areas[8]==0)
                        $this->kc->open_area(8);
                    $this->kc->build_if_less_than($base_id,'Withered Forest',37,51,2);
                    if ($this->kc->town->areas[8]==1 && $this->kc->check_tile_exists(0,'Withered Forest',37,51,2)->lev==2)
                        $this->kc->remove_tile($base_id,37,51);
                    $this->kc->build_if_less_than($base_id,'Charred Field',35,47,2);
                    if ($this->kc->town->areas[8]==1 && $this->kc->check_tile_exists(0,'Charred Field',35,47,2)->lev==2)
                        $this->kc->remove_tile($base_id,35,47);
                    //For some reason Monster Barracks has never been built
                    $this->kc->build_if_less_than($base_id,'Monster Barracks',55,48,1);
                    $this->kc->build_if_less_than($base_id,'Monster Barracks',55,48,2);
                    $this->kc->complete_quests();
                    return 'Skill Learn done, Doom guild and building withered forrest and charred field';
            }
        }
    }
?>