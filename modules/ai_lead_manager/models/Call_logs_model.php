<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Call_logs_model extends App_Model
{
    protected $table = 'alm_call_logs';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get call logs
     * @param  string $id Optional - call log id
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('*');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . $this->table)->row();
        }

        return $this->db->get(db_prefix() . $this->table)->result_array();
    }

    public function add($data)
    {
        $this->db->insert(db_prefix() . $this->table, $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }
}
