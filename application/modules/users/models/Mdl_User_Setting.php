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
        $oldValue = $this->getUserSetting($userID);
        if($oldValue[$name] != $value){
            $this->db->where('user_id', $userID);
            $this->db->where('name', $name);
            $this->db->delete($this->table);
            $this->addByData(['user_id' => $userID, 'name' => $name, $attr => $value]);
        }
    }

    function getUserSetting($userID){
        $result = [];
        //get default first
        if($userID != 1){
            $result = $this->getUserSetting(1);
        }

        $this->db->where('user_id', $userID);
         $query = $this->db->get($this->table);
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