<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_Role extends MY_Model
{
    function __construct()
    {
        $this->table = 'epanel_roles';
        $this->keyAttr = 'id';
    }

    function getRole($roleID){
        $this->db->where($this->keyAttr, $roleID);
        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    function getRoleListForUser($userRole){
        //todo: set this as policy
        $this->db->select($this->table.'.*, (select count(id) from users WHERE users.epanel = '.$this->table.'.'.$this->keyAttr.') as users ');
        $this->db->where('type <= ', $userRole);
        $this->db->where('type != ', '10');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    function switchRole($roleID){
        $this->db->where($this->keyAttr, $roleID);
        $query = $this->db->get($this->table);
        $role = $query->row_array();
        if($role['active'] == '0'){
            $this->db->where($this->keyAttr, $roleID);
            $query = $this->db->set('active', '1');
            $this->db->update($this->table);
            return true;
        }else{
            $this->db->where($this->keyAttr, $roleID);
            $query = $this->db->set('active', '0');
            $this->db->update($this->table);
            return false;
        }
    }

    function setDefaultRole($roleID){
        $this->db->where('default', '1');
        $this->db->set('default', '0');
        if($this->db->update($this->table)){
            $this->db->where($this->keyAttr, $roleID);
            $this->db->set('default', '1');
            $this->db->set('active', '1');
            return $this->db->update($this->table);
        }else{
            return false;
        }
    }

    function deleteRole($roleID){
        $this->deleteByID($roleID);

        $this->db->where('role_id', $roleID);
        $this->db->delete('role_area');

        $this->db->where('role_id', $roleID);
        $this->db->delete('role_permission');
    }

    function getAvailableRoles($maxRole){
        $this->db->where('type < ', $maxRole);
        $query = $this->db->get($this->table);
        return $query->result_array();
    }
}