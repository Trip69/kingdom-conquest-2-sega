<?php
    class template
    {
        public static $echo_data = array();
        
        public static function basic($echo,$page,$data)
        {
            $return = file_get_contents($page);
            $return = str_replace('XechoX',$echo,$return);
            $return = str_replace('XdataX',$data,$return);
            if(strpos($data,'kc2login')>0){
                $return = str_replace('XtimeX','60',$return);
                $return = str_replace('<p>Login on next one in 5 seconds</p>','',$return);
                $return = str_replace('<p>Creating next one in 5 seconds</p>','',$return);          
            } else {
                $return = str_replace('XtimeX','5',$return);
            }
            return $return;
        }

        
        public static function simple($args,$template)
        {
            $return=file_get_contents($template);
            foreach ($args as $key => $value)
                $return = str_replace('X'.$key.'X',$value,$return);
            return $return;
        }
        
        public static function fill_commmon_data()
        {
            template::$echo_data['username']=isset($_POST['username'])?$_POST['username']:null;
            template::$echo_data['world']=isset($_POST['world'])?$_POST['world']:null;
            template::$echo_data['device']=isset($_POST['device'])?$_POST['device']:null;
            template::$echo_data['cookie']=isset($_POST['cookie'])?$_POST['cookie']:null;
            template::$echo_data['user_id']=isset($_POST['user_id'])?$_POST['user_id']:null;
            template::$echo_data['username']=isset($_POST['username'])?$_POST['username']:null;
            template::$echo_data['field_id']=isset($_POST['field_id'])?$_POST['field_id']:null;
            
            return template::$echo_data;
        }

        public static function echo_town($town)
        {
            template::fill_commmon_data();
            
            template::$echo_data['username']=$_POST['username'];
            template::$echo_data['town_name']=$town->field_name;
            template::$echo_data['town_concurrent']=$town->build_sametime;
            template::$echo_data['town_concurrent_now']=$town->now_build_sametime;
            template::$echo_data['town_queue']=$town->build_reserve_free;
            template::$echo_data['town_queue_now']=$town->now_build_reserve;
            template::$echo_data['town_xy']=$town->field_x.','.$town->field_y;
            template::$echo_data['town_monster_max']=$town->monster_max;
            template::$echo_data['town_monster_now']=$town->headcount_now;
            template::$echo_data['cp']=$town->resource->cp_free + $town->resource->cp_buy;
            template::$echo_data['dp']=$town->resource->dp;
            template::$echo_data['wood']=$town->resource->wood->now;
            template::$echo_data['iron']=$town->resource->iron->now;
            template::$echo_data['stone']=$town->resource->stone->now;
            template::$echo_data['commander_point']=$town->resource->commander_point;
            template::$echo_data['actplay_free']=$town->resource->actplay_free;
            template::$echo_data['actplay_cp']=$town->resource->actplay_cp;
            
            template::$echo_data['quests']='';
            foreach ($town->quest->quest_list as $quest)
            {
                template::$echo_data['quests'].='<tr><td>'.$quest->title.
                                    ' (' . $quest->complete_flg . ')' .
                                    '</td><td>'.$quest->quest_id . "</td></tr>\r\n";
            }
            
            template::$echo_data['training']='';
            foreach ($town->task_create as $mon)
            {
                template::$echo_data['training'].='<tr><td>'.kc::$monsters[$mon->mons_id].'</td><td>'.$mon->num.'</td><td>';
            }

            template::$echo_data['build']='';
            foreach ($town->task_build as $con)
            {
                template::$echo_data['build'].='<tr><td>'.kc::$tiles[$con->tile].'</td><td>'.$con->tx.','.$con->ty."</td><td>".$con->lev."</td><td><input type=\"hidden\" name=\"build_id\" value=\"".$con->id."\"><input type=\"submit\" name=\"build_cancel\" value=\"Cancel\"></td></tr>\r\n";
            }

            template::$echo_data['buildings']='';
            foreach ($town->tiles as $building)
            {
                template::$echo_data['buildings'].='<tr><td>'.kc::$tiles[$building->tile].'</td><td>'.$building->lev.'</td><td>'.$building->x.','.$building->y."</td><td><input type=\"hidden\" name=\"build_string\" value=\"".template::make_build_string($building)."\"><input type=\"submit\" name=\"build\" value=\"Build\"></td></tr>\r\n";
            }
            
            return template::simple(template::$echo_data,'template/kc2_town.html');
        }
        
        private static function make_build_string($build_obj)
        {
            $ret = 'field_id=' . $_POST['field_id'];
            $ret .= '&tx=' . $build_obj->x;
            $ret .= '&ty=' . $build_obj->y;
            $ret .= '&tile_id=' . $build_obj->tile;
            return $ret;
        }
        
        public static function echo_units($unit_data,$kc)
        {
            template::fill_commmon_data();
            template::$echo_data['unit']='';

            foreach ($unit_data->units as $unit) {
                $remove_commander='<button type="button" onclick="remove_commander('.$unit->field_id.','.$unit->deck_idx.');">Remove</button>';
                $mon_count=0;
                if ($unit->commander->uniq_id)
                    template::$echo_data['unit'].='<tr><td rowspan="XspanX">'. $kc->get_commander($unit->commander->uniq_id)->name  ."$remove_commander</td>";
                else
                    template::$echo_data['unit'].='<tr><td rowspan="XspanX"></td>';
                $row_one=true;
                foreach ($unit->monsters as $monster) {
                    if ($monster->uniq_id>0) {
                        if ($row_one)
                            $row_one=false;
                        else
                            template::$echo_data['unit'].='<tr>';
                        $mon=kc::$monsters[$kc->my_monsters[$monster->uniq_id]];
                        $mon_mc=$kc->get_monster($monster->uniq_id) -> uniq_data ->hc;
                        $mon_state=kc::$state_ids[$kc->get_monster($monster->uniq_id) -> uniq_data ->state];
                        $pos=kc::$pos_masks[$monster->posmask];
                        $mon_count++;
                        $remove_button='<button type="button" onclick="remove_monster('.$unit->field_id.','.$unit->deck_idx.','.$monster->uniq_id.');">Remove</button>';
                        template::$echo_data['unit'].="<td>$pos</td><td>$mon</td><td>$mon_mc</td><td>$mon_state</td><td>$remove_button</td></tr>\r\n";
                    }
                }
                template::$echo_data['unit']=str_replace('XspanX',$mon_count,template::$echo_data['unit']);
            }
            return template::simple(template::$echo_data,'template/kc2_units.html');
        }
    }
?>
