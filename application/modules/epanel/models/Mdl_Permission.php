<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_Permission extends MY_Model
{
    function __construct()
    {
        $this->table = 'epanel_permissions';
        $this->keyAttr = 'id';
    }

    function getRolePermissions($roleID){
        if($roleID != 0){
            $this->db->where('role_id', $roleID);
            $this->db->join('role_permission', $this->table.'.'.$this->keyAttr.' = role_permission.permission_id', 'left');
        }
        $this->db->order_by('lvl', 'ASC');
        $this->db->order_by('sort', 'ASC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    function getAreaPermissions($areas){
        $this->db->where_in('area_id', $areas);
        $this->db->join('permission_area', $this->table.'.'.$this->keyAttr.' = permission_area.permission_id', 'left');

        $this->db->order_by('lvl', 'ASC');
        $this->db->order_by('sort', 'ASC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    function IsPermissionInArea($permission, $area){
        $this->db->where('permission_id', $permission);
        $this->db->where('area_id', $area);
        $query = $this->db->get('permission_area');
        return ($query->row_array())? true: false;
    }

    function addPermissionsToRole($permissions, $roleID){
        $data = [];
        foreach ($permissions as $permission){
            $data[] = [
                'permission_id'=> $permission,
                'role_id'=> $roleID,
            ];
        }
        if(!empty($data)){
            $this->db->insert_batch('role_permission', $data);
        }

    }

    function RemoveRolePermission($roleID){
        $this->db->where('role_id', $roleID);
        return $this->db->delete('role_permission');
    }

    function getPermissionIDFromAction($action){
        $this->db->select($this->keyAttr);
        $this->db->where('home_page', $action);
        $query = $this->db->get($this->table);
        $result = $query->row_array();

        return (isset($result[$this->keyAttr]))? $result[$this->keyAttr] : 0;
    }
}