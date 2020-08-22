<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_User extends MY_Model
{
    function __construct()
    {
        parent::__construct();
        $this->table = 'users';
        $this->keyAttr = 'id';
    }

    public function getByIDWithRole($userID){
        $this->db->select('epanel_roles.*, epanel_roles.id as role, epanel_roles.name AS role_name,
            users.id as id,users.name,users.email,users.pic,users.phone,users.gender,users.username,users.epanel');
        $this->db->where( $this->table.'.'.$this->keyAttr, $userID);
        $this->db->join('epanel_roles', 'epanel_roles.id = users.epanel', 'left');
        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    public function verifyActiveUser($username, $password){
        $hashPW =  hash('ripemd160',$password .$this->config->item('pw-salt'));
        $this->db->select($this->keyAttr.',name');
        //todo: add login by username or email as epanel settings

        $this->db->group_start()->where('username', $username)->or_where('email', $username)->group_end();

        $this->db->where('active', '1');
        $this->db->where('password', $hashPW);
        $query = $this->db->get($this->table);
        $user = $query->row_array();
        if( $user){ 
            return $user[$this->keyAttr];
        }else{
            return false;
        }
    }

    public function verifyUser($username, $password){
        $hashPW =  hash('ripemd160',$password .$this->config->item('pw-salt'));
        $this->db->select($this->keyAttr.',name');
        $this->db->where('username', $username);
        $this->db->where('password', $hashPW);
        $query = $this->db->get($this->table);
        $user = $query->row_array();
        if( $user){
            return $user[$this->keyAttr];
        }else{
            return false;
        }
    }

    public function verifyActiveEpanelUser($username, $password){
        $hashPW =  hash('ripemd160',$password .$this->config->item('pw-salt'));
        $this->db->select($this->keyAttr.',name');
        $this->db->where('username', $username);
        $this->db->where('active', '1');
        $this->db->where('epanel >', '0');
        $this->db->where('password', $hashPW);
        $query = $this->db->get($this->table);
        $user = $query->row_array();
        if( $user){
            return $user[$this->keyAttr];
        }else{
            return false;
        }
    }

    public function getUsersWithRolesforShow(){
        $this->db->select('users.*,epanel_roles.name as roleName, epanel_roles.type');
        $this->db->where('epanel !=', '1');
        $this->db->join('epanel_roles', $this->table.'.epanel = epanel_roles.id', 'left');

        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    public function getUserName($userID){
        $this->db->select('username');
        $this->db->where($this->keyAttr, $userID);
        $query = $this->db->get($this->table);
        $result = $query->row_array();
        return $result['username'] ?? '';
    }

    function IsUsernameExist($username){
        $this->db->select('username');
        $this->db->where('username', $username);
        $query = $this->db->get('users');
        if( $query->row_array()){
            return true;
        }else{
            return false;
        }
    }

    function IsEmailExist($email){
        $this->db->select('email');
        $this->db->where('email', $email);
        $query = $this->db->get('users');

        if($query->row_array()){
            return true;
        }else{
            return false;
        }
    }

    function switchUser($userID){
        $this->db->where($this->keyAttr, $userID);
        $query = $this->db->get($this->table);
        $role = $query->row_array();
        if($role['active'] == '0'){
            $this->db->where($this->keyAttr, $userID);
            $query = $this->db->set('active', '1');
            $this->db->update($this->table);
            return true;
        }else{
            $this->db->where($this->keyAttr, $userID);
            $query = $this->db->set('active', '0');
            $this->db->update($this->table);
            return false;
        }
    }

    function ChangePassword($userID, $newPassword){
        $hashPW =  hash('ripemd160',$newPassword .$this->config->item('pw-salt'));
        $this->db->where($this->keyAttr, $userID);
        $query = $this->db->set('password', $hashPW);
        return $this->db->update($this->table);
    }

}