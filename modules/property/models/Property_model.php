<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Property_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = '')
    {
        if ($id) {
            return $this->db->get_where(db_prefix() . 'property', ['id' => $id])->row();
        }
        return $this->db->get(db_prefix() . 'property')->result_array();
    }

    public function add($data)
    {
        $this->db->insert(db_prefix() . 'property', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'property', $data);
        return $this->db->affected_rows() > 0;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'property');
        return $this->db->affected_rows() > 0;
    }
}
