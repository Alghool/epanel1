<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_Log extends MY_Model
{
    function __construct() {
        $this->table = 'log';
        $this->keyAttr = 'log_id';
    }

    function getLatestLogs($limit = 30){
        $this->db->select($this->table.'.*, users.name as username');
        $this->db->join('users', $this->table.'.user_id = users.user_id', 'left');
        $this->db->order_by('date', 'DESC');
        $this->db->limit($limit);
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    function getTableLog($table, $itemID = 0, $limit = 50){
        $this->db->select('log.date, log.text, users.name as username');
        $this->db->join('users', $this->table.'.user_id = users.user_id', 'left');
        $this->db->where('link_type', 'table');
        $this->db->where('link', $table);

        if($itemID != 0){
            $this->db->where('link_id', $itemID);
        }
        if($limit > 100000){
            $this->db->where('date >', $limit);
        }else{
            $this->db->limit($limit);
        }
        $this->db->order_by('date', 'DESC');


        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    function removeTable($table, $itemID){
        $this->db->where('link', $table);
        $this->db->where('link_type', 'table');
        $this->db->where('link_id', $itemID);

        $data = [
          'link' => '',
          'link_type' => '',
          'link_id' => 0
        ];

        return $this->db->update($this->table, $data);
    }

    function changeTable($oldTable, $oldItemID, $newTable, $newItemID){
        $this->db->where('link', $oldTable);
        $this->db->where('link_type', 'table');
        $this->db->where('link_id', $oldItemID);

        $data = [
            'link' => $newTable,
            'link_id' => $newItemID
        ];

        return $this->db->update($this->table, $data);
    }

}