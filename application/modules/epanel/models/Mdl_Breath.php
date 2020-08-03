<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_Breath extends MY_Model
{
    function __construct()
    {
        $this->table = 'epanel_breath';
        $this->keyAttr = 'id';
    }

    public function isUserBreath($userID, $sessionKey, $minTime, $ipaddress = 0){
        $this->db->where('user_id', $userID);
        $this->db->where('session', $sessionKey);
        if($ipaddress != 0){
            $this->db->where('ipaddress', $ipaddress);
        }
        $this->db->where('last_time >', $minTime);

        $query = $this->db->get($this->table);
        if($query->row_array()){
            $this->db->where('user_id', $userID);
            $this->db->where('session', $sessionKey);
            $this->db->where('last_time >', $minTime);
            $this->db->set('last_time', now());
            $this->db->update($this->table);
            return true;
        }else{
            return false;
        }
    }

}