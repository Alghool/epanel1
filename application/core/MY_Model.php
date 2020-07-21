<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Model extends CI_Model {

    protected $table = "";
    protected $lang = "";
    protected $keyAttr = 'id';
    protected $langAttr = 'lang';
    protected $enableCash = false;
    protected $cash = array();

    public function getTable($select = 0, $limit = 0){
        if($select != 0)
            $this->db->select($select);
        if($this->lang != '')
            $this->db->where($this->langAttr, $this->lang);
        if($limit != 0)
            $this->db->limit($limit);
        $query = $this->db->get($this->table);
        $result = $query->result_array();
        $this->addToCash($result);
        return $result;
    }

    public function getByID($ID, $select = 0){
        if($select != 0)
            $this->db->select($select);

        $this->db->where($this->keyAttr, $ID);
        $query = $this->db->get($this->table);
        $result = $query->row_array();
        $this->addToCash([$result]);
        return $result;
    }

    public function getRowByAttr($attr, $val, $select = 0){
        if($select != 0)
            $this->db->select($select);
        if($this->lang != '')
            $this->db->where($this->langAttr, $this->lang);

        $this->db->where($attr, $val);
        $query = $this->db->get($this->table);
        $result = $query->row_array();
        $this->addToCash([$result]);
        return $result;
    }

    public function getArrByAttr($attr, $val, $select = 0){
        if($select != 0)
            $this->db->select($select);
        if($this->lang != '')
            $this->db->where($this->langAttr, $this->lang);

        $this->db->where($attr, $val);
        $query = $this->db->get($this->table);
        $result = $query->result_array();
        $this->addToCash($result);
        return $result;
    }


    //add//////////////////////////////////////////////////////////////////////////////////
    public function addByData($data){
        $this->db->insert($this->table, $data);
        $newId = $this->db->insert_id();
        $data[$this->keyAttr] = $newId;
        $this->addToCash([$data]);
        return $newId;
    }

    //edit/////////////////////////////////////////////////////////////////////////////////
    public function editByData($ID, $data){
        $this->db->where($this->keyAttr, $ID);
        if($this->db->update($this->table, $data)){
            $data[$this->keyAttr] = $ID;
            $this->addToCash([$data]);
            return true;
        }
        return false;
    }

    public function editByAttr($attr, $val, $data){
        $this->db->where($attr, $val);
        return $this->db->update($this->table, $data);
    }

    public function setAttr($id, $attr, $newvalue){
        $this->db->where($this->keyAttr, $id);
        $this->db->set($attr, $newvalue);
        if($this->db->update($this->table)){
            $data =[
                $this->keyAttr => $id,
                $attr = $newvalue
                ];
            $this->addToCash([$data]);
            return true;
        }
        return false;
    }
    //delete///////////////////////////////////////////////////////////////////////////////
    public function deleteByID($ID){
        $this->db->where($this->keyAttr, $ID);
        if($this->db->delete($this->table)){
            $this->removeFromCash($ID);
        }
        return false;
    }

    public function deleteByAttr($attr, $val){
        if($this->lang != '')
            $this->db->where($this->langAttr, $this->lang);

        $this->db->where($attr, $val);
        return $this->db->delete($this->table);
    }

    public function deleteByArr($arr){
        if(!is_array($arr) && empty($arr) ){
            throw new Exception('parameter '.$arr .' is not array or empty');
        }
        foreach ($arr as $attr => $val){
            $this->db->where($attr, $val);
        }
        return $this->db->delete($this->table);
    }

    //tools////////////////////////////////////////////////////////////////////////////////
    function getTotal(){
        return $this->db->from($this->table)->count_all_results();
    }

    //myStyle//////////////////////////////////////////////////////////////////////////////
    public function getByClass($className, $select = 0){
        if($select != 0)
            $this->db->select($select);
        if($this->lang != '')
            $this->db->where($this->langAttr, $this->lang);

        $this->db->where('class', $className);
        $query = $this->db->get($this->table);
        $result = $query->result_array();
        $this->addToCash($result);
        return $result;
    }

    //lang/////////////////////////////////////////////////////////////////////////////////
    public function setLang($lang){
        $this->lang = $lang;
    }
    public function unsetLang(){
        $this->lang = "";
    }
    //key//////////////////////////////////////////////////////////////////////////////////
    public function getKeyAttr(){
        return $this->keyAttr;
    }
    //cash/////////////////////////////////////////////////////////////////////////////////
    protected function addToCash($queryResult){
        if ($this->enableCash){
            if(!is_array($queryResult)) return;
            foreach ($queryResult as $row)
            {
                $thisKey = $row[$this->keyAttr];
                if(isset($this->cash[$thisKey])){
                    foreach ($row as $key => $value){
                        $this->cash[$thisKey][$key] = $value;
                    }
                }else{
                    $this->cash[$thisKey] = $row;
                }
            }
        }
    }

    protected function removeFromCash($id){
        if ($this->enableCash){
            if(isset($this->cash[$id])){
                unset ($this->cash[$id]);
            }
        }
    }

    protected function getFromCash($value , $key = 0){
        //please be sure you got your data right some functions does not update cash data
        //user model cash at your risk
        if ($this->enableCash){
            $key = ($key === 0)? $this->keyAttr: $key;
            if($key == $this->keyAttr || $key == 0){
                return (isset($this->cash[$value]))? $this->cash[$value]: false;
            }else{
                foreach ($this->cash as $row){
                    if($row[$key] = $value){
                        return $row;
                    }
                }
                return false;
            }
        }
        return false;
    }
}