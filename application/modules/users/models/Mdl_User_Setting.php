<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_User_Setting extends MY_Model
{
    function __construct()
    {
        $this->table = 'user_setting';
        $this->keyAttr = 'id';
    }

    function updateUserSetting($userID, $name, $attr, $value){
        $this->db->where('user_id', $userID);
        $this->db->where('name', $name);
        $this->db->set($attr, $value);
        $this->db->update($this->table);
    }

    function getUserSetting($userID){

        $this->db->where('user_id', $userID);
         $query = $this->db->get($this->table);
         $result = array();
         $settings = $query->result_array();
        foreach ($settings as $row)
        {
            $result[$row['name']] = ($row['strval'] == '')? $row['intval'] : $row['strval'];
        }
        return $result;
    }

    function createUserSettings($userID){
        $settings = $this->getArrByAttr('user_id', 1);

        for($i = 0, $count = count((array)$settings); $i < $count; $i++){
            $settings[$i]['user_id'] = $userID;
            unset( $settings[$i][$this->keyAttr]);
        }
        if(!empty($settings)){
            $this->db->insert_batch($this->table, $settings);
        }

    }
}