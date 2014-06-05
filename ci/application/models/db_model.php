<?php
	/**
	 * Handles database operations
	 */
	 
	 /**
	  * Interface of the database
	  * 
	  * @version 1.0
	  *  
	  */
    class DB_Model extends CI_Model
    {
    	/**
		 * constructor of the database_model 
		 * makes the database available for this class
		 * 
		 * @version 1.0
		 */
        public function __construct()
        {
            parent::__construct();
            $this->load->database();
        }
        
        public function get($from, $where = null, $select = null)
        {
            if(!is_null($select))
                $this->db->select($select);

            $query = isset($where) ? $this->db->get_where($from, $where) : $this->db->get($from);   
            return $query->result_array();
        }
		
		public function get_special($from, $where = null, $select = null, $data)
		{
			if(isset($data['distinct']))
				$this->db->distinct();
			if(isset($data['order_by']))
				$this->db->order_by($data['order_by']);
			if(isset($data['group_by']))
				$this->db->group_by($data['group_by']);
            if(isset($data['join']))
                $this->db->join($data['join'][0], $data['join'][], $data['join'][2]);
			
			return $this->get($from, $where, $select);
		}		
		
		
		public function get_distinct($from, $where = null, $select = null)
        {
            if(!is_null($select))
                $this->db->select($select);
			
			$this->db->distinct();
            $query = isset($where) ? $this->db->get_where($from, $where) : $this->db->get($from);   
            return $query->result_array();
        }
        
        public function get_single($from, $where = null, $select = null)
        {
            $query = $this->get($from, $where, $select);
            return empty($query) ? false : $query[0];
        }
        
		/**
		 * inserts the $data into $table
		 * 
		 * @version 1.0
		 * 
		 * @param String $table		table to insert the data into
		 * @param Array data		data to insert into the table
		 */
        public function insert($table, $data, $batch = false)
        {
            if($batch === false)
                $this->db->insert($table, $data);
            else
                $this->db->insert_batch($table, $data);
        }
        
		/**
		 * updates a field in the database
		 * 
		 * @version 1.0
		 *  
		 */
        public function update($table, $where, $data, $set = false)
        {
        	$this->db->where($where);
			
        	if($set)
			{
				$this->db->set($data[0], $data[1], FALSE);
				$this->db->update($table);
			}
			else
            	$this->db->update($table, $data);
        }
        
        public function destroy($table, $where)
        {
            $this->db->delete($table, $where);
        }
        
		/**
		 * returns 
		 * 
		 * @version 1.0
		 * 
		 * @param String $table			table to draw data from
		 * @param Integer/String $id	name or id ..
		 * 
		 * @return 
		 */
        public function get_assignment($table, $id, $single = true)
        {
            $where = is_numeric($id) ? $table . "_id = '$id'" : "username = '$id'";
            return $single ? $this->get_single('user' . $table, $where) : $this->get('user' . $table, $where);
        }
		
		/**
		 *  assigns the user ..
		 * 
		 * @version 1.0
		 * 
		 * @param String $table						..
		 * @param array $data						..
		 */
        public function assign_user($table, $data)
        {
            $query = $this->get_assignment($table, $data[$table . '_id']);
            
            if($query)
                $this->update('user' . $table, $table . '_id', $data);
            else
                $this->insert('user' . $table, $data);
        }
        
		/**
		 * unassigns the user in the table
		 * 
		 * @version 1.0
		 * 
		 * @param String $table name of the table to unassign the user from
		 * @param Integer $id id of the user to unassign from the table
		 */
        public function unassign_user($table, $id)
        {
            $this->db->delete('user' . $table, array($table . '_id' => $id));
        }
    }
?>
