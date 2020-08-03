<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_Area extends MY_Model
{
    function __construct()
    {
        $this->table = 'epanel_areas';
        $this->keyAttr = 'id';
    }

    function getRoleAreas($roleID){
        if($roleID != 0){
            $this->db->where('role_id', $roleID);
            $this->db->join('role_area', $this->table.'.'.$this->keyAttr.' = role_area.area_id', 'left');
        }
        $this->db->order_by('lvl', 'ASC');

        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    function addAreasToRole($areas, $roleID){
        $data = [];
        foreach ($areas as $area){
            $data[] = [
                'area_id'=> $area,
                'role_id'=> $roleID,
            ];
        }
        if(!empty($data)){
            $this->db->insert_batch('role_area', $data);
        }

    }
}