<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_Action extends MY_Model
{
    function __construct()
    {
        $this->table = 'epanel_actions';
        $this->keyAttr = 'action_id';
    }

    function isActionExist($type, $container, $link){
        //try to git exactly match
        $this->db->where('link', $link);
        $this->db->where('container', $type);
        $this->db->where('container_id', $container);
        $this->db->where('active', '1');
        $this->db->join('action_container', ' action_container.action_id = '.$this->table.'.'.$this->keyAttr, 'left');
        $query = $this->db->get($this->table);
        return ($query->row_array())? true : false;
    }

    function learnAction($type, $container, $link){
        $this->db->where('link', $link);
        $query = $this->db->get($this->table);
        $result = $query->row_array();
        if($result){
            if ($result['active'] == 0) return false;
            $data = [
                'container' => $type,
                'container_id' => $container,
                'action_id' => $result['action_id']
            ];
            $this->db->insert('action_container', $data);
        }else{
            $data = [
                'name' => $link,
                'link' => $link,
                'active' => 1,
            ];
            $linkID = $this->addByData($data);
            $data = [
                'container' => $type,
                'container_id' => $container,
                'action_id' => $linkID
            ];
            $this->db->insert('action_container', $data);
        }
    }

}