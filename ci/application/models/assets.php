<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Assets extends CI_Model
    {
        public function get_assets($section = 'general', $id = null, $where = true)
        {
            if($section != 'general' && !is_null($id))
                return $this->db_model->get('asset a, '. $section .'asset sa, assettype at',
                                            "sa.". $section."_id = $id AND a.asset_id = sa.asset_id AND a.type_id = at.assettype_id AND $where ORDER BY title",
                                            ($section == 'task' ? 'local, a.description,' : '') . 'a.asset_id, a.title,'. ($section == 'project' ? '' : 'approved,').' type_name, path, global');
            
            return $this->db_model->get('asset', "global = true AND $where ORDER BY title");
        }
        
        public function get_all_projectassets($section, $id, $where = true, $returnIds = false)
        {
            $query = $this->db_model->get_single("$section", $section."_id = $id", 'project_id');
            $project_id = $query['project_id'];
            
            $pa_ids = array_map(function($el){ return $el['asset_id']; }, $this->db_model->get( 'projectasset',
                                                                                                "project_id = $project_id",
                                                                                                'asset_id'));
            
            $sca_ids = array_map(function($el){ return $el['asset_id']; }, $this->db_model->get('scene sc, sceneasset sca',
                                                                                                "sca.scene_id = sc.scene_id AND sc.project_id = $project_id",
                                                                                                'asset_id'));
                                            
            $sha_ids = array_map(function($el){ return $el['asset_id']; }, $this->db_model->get('shot sh, shotasset sha',
                                                                                                "sha.shot_id = sh.shot_id AND sh.project_id = $project_id",
                                                                                                'asset_id'));
                                            
            $ta_ids = array_map(function($el){ return $el['asset_id']; }, $this->db_model->get( 'task t, taskasset ta',
                                                                                                "ta.task_id = t.task_id AND t.project_id = $project_id",
                                                                                                'asset_id'));
                                                                                                
            $asset_ids = array_merge($pa_ids, $sca_ids, $sha_ids, $ta_ids);

            if($returnIds)
                return $asset_ids;
            
            return empty($asset_ids) ? array() : $this->db_model->get('asset', 'asset_id IN ('.implode(',',$asset_ids).") AND $where ORDER BY title");
        }

        public function get_linkable_assets($section, $id, $where = true)
        {
            $glob_assets = $this->get_assets('general', null, "$where");
            $pro_assets = $this->get_all_projectassets($section, $id, "$where");
            
            // Duplikate entfernen
            for($i = 0; $i < count($pro_assets); $i++)
            {
                foreach($glob_assets as $asset)
                {
                    if($asset['asset_id'] == $pro_assets[$i]['asset_id'])
                    {
                        unset($pro_assets[$i]);
                        break;
                    }
                }
            }

            return array_merge($glob_assets, $pro_assets);
        }
        
        public function get_my_assets($username)
        {
            return $this->db_model->get('asset', "author = $username ORDER BY title");
        }
        
        public function get_used_in($asset_id)
        {
            $query = $this->db_model->get(  'task t, taskasset ta',
                                            "ta.asset_id = $asset_id AND ta.task_id = t.task_id
                                            UNION SELECT sh.project_id FROM shot sh, shotasset sha WHERE sha.asset_id = $asset_id AND sha.shot_id = sh.shot_id
                                            UNION SELECT sc.project_id FROM scene sc, sceneasset sca WHERE sca.asset_id = $asset_id AND sca.scene_id = sc.scene_id
                                            UNION SELECT project_id FROM projectasset WHERE asset_id = $asset_id",
                                            't.project_id');
            
            $proj_ids = array_unique(array_map(function($el){ return $el['project_id']; }, $query));
            
            if(empty($proj_ids))
                return array();
            
            
            $projects = $this->db_model->get('project', 'project_id IN ('.implode(',',$proj_ids).')', 'title, project_id');
            return array_map(function($el) { return '<a href="'. base_url('projects/view/' . $el['project_id']).'">'.$el['title'].'</a>';}, $projects);
        }
        
        public function is_Linked($id)
        {
            $tables = array('projectasset', 'sceneasset', 'shotasset', 'taskasset');
            $count = 0;
            
            foreach($tables as $table)
            {
                $q = $this->db_model->get_single($table, "asset_id = $id", 'COUNT(*) AS value');
                $count += $q['value'];
                
                if($count > 1)
                    break;  
            }
             
            return $count > 1;
        }
        
        public function get_type_names()
        {
            return array_map(function($el){ return $el['type_name']; }, $this->db_model->get('assettype', null, 'type_name'));
        }
        
        public function get_name($author)
        {
            $query = $this->db_model->get_single('user', "username = '$author'", 'firstname, lastname');
            return implode(' ', $query);
        }
        
        private function get_usernames($firstname, $lastname = null)
        {
            return $this->db_model->get('user', "firstname = '$firstname' OR lastname = ".(is_null($lastname) ? "'$firstname'" : "'$lastname'"), 'username');
        }
        
        public function convert_name_id($toConvert, $isID = false)
        {
            $convert = $isID ?  $this->db_model->get_single('assettype', "assettype_id = '$toConvert'", 'type_name') :
                                $this->db_model->get_single('assettype', "type_name = '$toConvert'", 'assettype_id');
                                
            return ($isID ? $convert['type_name'] : $convert['assettype_id']);
        }
        
        public function filter($field = null, $filter = null)
        {
            if(is_null($filter) || $filter == '' || $field == 'No_Select')
                return;

            $filter_terms = array_filter(array_unique(array_map('trim', preg_split( "/(:|,)/", $filter, null, PREG_SPLIT_NO_EMPTY))));
            
            if(empty($filter_terms))
                return $this->get_assets('general');

            $where = '';
            if(strpos(strtolower($filter),'my:') !== false)
            {
                $session = $this->session->userdata('logged_in');
                if(count($filter_terms) == 1)
                    return $this->get_assets('general', null, 'author = \''.$session['user'].'\'');
                
                $session = $this->session->userdata('logged_in');
                $where = ' AND author = \''.$session['user'].'\'';
            }

            switch($field)
            {
                case 'type':
                    foreach ($filter_terms as $filter_term)
                    {
                        $type = $this->db_model->get('assettype', "type_name LIKE '%$filter_term%'");
                        foreach ($type as $type_item)
                            $filter_conditions[] = $type_item['assettype_id'];
                    }
                    return empty($filter_conditions) ? array() : $this->get_assets('general', null, 'type_id IN ('.implode(',', $filter_conditions).") $where");
                    
                case 'author':
                    foreach ($filter_terms as $filter_term)
                    {
                        $fullname = explode(' ', $filter_term);
                        $usernames = count($fullname) <= 1 ? $this->get_usernames($filter_term) : $this->get_usernames($fullname[0], $fullname[1]);
                        foreach ($usernames as $username)
                            $filter_conditions[] = $username['username'];
                    }
                    break;
                    
                case 'extension':
                    $query = array();
                    foreach ($filter_terms as $filter_term)
                        $query[] = "path LIKE '%$filter_term'";
                    return $this->get_assets('general', null, implode(' OR ', $query).$where);
                    
                case 'uploaddate':
                    $query = array();
                    foreach ($filter_terms as $filter_term)
                        $query[] = "uploaddate LIKE '$filter_term%'";
                    return $this->get_assets('general', null, implode(' OR ', $query).$where);
                    
                case 'used_in':
                    $filter_terms = array_map(function($el){ return '\''.$el.'\''; }, $filter_terms);
                    $projects = $this->db_model->get('project', 'title IN ('.implode(',', $filter_terms).')', 'project_id');
                    
                    $asset_ids = array();
                    foreach($projects as $project)
                        $asset_ids = array_merge($asset_ids, $this->get_all_projectassets('project', $project['project_id'], "global = true", true));

                    return empty($asset_ids) ? array() : $this->get_assets('general', null, 'asset_id IN ('.implode(',', $asset_ids).") $where");
                    
                default:
                    $filter_conditions = $filter_terms; break;
            }

            return empty($filter_conditions) ? array() : $this->get_assets('general', null, "$field REGEXP '" . implode('(.*)|(.*)', $filter_conditions) . "' $where");
        }

        function link_asset($section, $section_id, $asset_id)
        {
            $asset = $this->db_model->get_single($section.'asset', array($section.'_id' => $section_id, 'asset_id' => $asset_id));
            if(!$asset)
                $this->db_model->insert($section.'asset', array($section.'_id' => $section_id, 'asset_id' => $asset_id));
        }
        
        function unlink_asset($section, $section_id, $asset_id)
        {
            $this->db_model->delete($section.'asset', array($section.'_id' => $section_id, 'asset_id' => $asset_id), true);
        }
        
        function edit($id, $field, $value)
        {
            if(!isset($id) || !isset($field) || !isset($value))
                return;
            if($field == 'type_id')
            {
                $asset = $this->db_model->get_single('asset', "asset_id = $id");
                $type_names = $this->get_type_names();
                @rename('media/' . $type_names[$asset['type_id']] . '/' . $asset['path'], 'media/' . $type_names[$value] . '/' . $asset['path']);
            }

            $data[$field] = $value;
            $this->db_model->update('asset', "asset_id = $id", $data);
        }
        
        function destroy($asset_id)
        {
            $type_names = $this->get_type_names();
            $asset = $this->db_model->get_single('asset', "asset_id = $asset_id");
                        
            $this->db_model->destroy('projectasset', "asset_id = '$asset_id'", true);
            $this->db_model->update('project', "logo = '$asset_id'", 'logo = NULL');
            $this->db_model->destroy('sceneasset', "asset_id = '$asset_id'", true);
            $this->db_model->update('scene', "logo = '$asset_id'", 'logo = NULL');
            $this->db_model->destroy('shotasset', "asset_id = '$asset_id'", true);
            $this->db_model->update('shot', "logo = '$asset_id'", 'logo = NULL');
            $this->db_model->destroy('taskasset', "asset_id = '$asset_id'", true);
            $this->db_model->destroy('asset', "asset_id = '$asset_id'", true);
            
            if($asset['type_id'] != LINK)
            {
                $path = urldecode(strtolower($type_names[$asset['type_id']]) . '/' . $asset['path']);
                
                if(file_exists('media/' . $path))
                    unlink('media/' . $path) or die('failed deleting: ' . $path);
            }
        }
    }
?>