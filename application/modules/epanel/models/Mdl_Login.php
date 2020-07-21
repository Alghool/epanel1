<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_Login extends MY_Model
{
    function __construct()
    {
        $this->table = 'epanel_logins';
        $this->keyAttr = 'login_id';
    }
    /**
     * get the last login try for this username or ip address
     * @param string username who is try login
     * @param string ip address try to login
     * @return array last login data
     */
    function getLastTry($username, $ipaddress){

        $this->db->where('username', $username);
        $this->db->or_where('ip', $ipaddress);
        $this->db->order_by('lasttime', 'DESC');
        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    /**
     * set login counter of user by id
     * @param integer id of entry to be updated
     * @param integer new counter value
     */
    function updateLoginTry($id, $count){
        $data = [
            'count'  => $count,
            'lasttime' => now(),
        ];
        $this->editByData($id, $data);
    }
    /**
     * set new login try counter
     * @param string username
     * @param string client ip address
     */
    function newLogin($username, $ipAddress){
        //delete last order
        $this->deleteLogin($username, $ipAddress);
        //create new record
        $data = [
            'username' => $username,
            'ip' => $ipAddress,
            'count'  => 1,
            'lasttime' => now(),
        ];
        $this->addByData($data);
    }
    /**
     * delete try record
     * @param string username
     * @param string client ip address
     */
    function deleteLogin($username, $ipAddress)
    {
        $this->db->where('username', $username);
        $this->db->or_where('ip', $ipAddress);
        $this->db->order_by('lasttime', 'DESC');
        $this->db->delete($this->table);
    }

}