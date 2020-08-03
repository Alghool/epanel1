<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_Setting extends MY_Model
{
    function __construct()
    {
        $this->table = 'epanel_setting';
        $this->keyAttr = 'id';
        $this->enableCash = true;
    }
    /**
     * get the int value of epanel setting
     * @param string|integer if integer its setting id if string its setting name
     * @return  boolean true|false is the setting value
     */
    function getSettingOnOff($settingKey){
        $whereAttr = (is_numeric($settingKey) and $settingKey > 0)? $this->keyAttr: 'name';
        $cashResult =  $this->getFromCash($settingKey, $whereAttr);
        if($cashResult && isset($cashResult['intval'])){
            return ($cashResult['intval'] > 0)? true: false;
        }
        //not in cash
        $this->db->select($this->keyAttr.',name,intval');
        $this->db->where($whereAttr, $settingKey);
        $query = $this->db->get($this->table);
        $result = $query->row_array();
        $this->addToCash([$result]);
        if($result['intval'] > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * get the string value of epanel setting
     * @param string|integer if integer its setting id if string its setting name
     * @return string the setting value
     */
    function getSettingStrValue($settingKey){
        $whereAttr = (is_numeric($settingKey) and $settingKey > 0)? $this->keyAttr: 'name';
        $cashResult =  $this->getFromCash($settingKey, $whereAttr);
        if($cashResult && isset($cashResult['strval'])){
            return $cashResult['strval'];
        }
        //not in cash
        $this->db->select($this->keyAttr.',name,strval');
        $this->db->where($whereAttr, $settingKey);
        $query = $this->db->get($this->table);
        $result = $query->row_array();
        $this->addToCash([$result]);
        return $result['strval'];
    }
    /**
     * get the integer value of epanel setting
     * @param string|integer if integer its setting id if string its setting name
     * @return integer the setting value
     */
    function getSettingIntValue($settingKey){
        $whereAttr = (is_numeric($settingKey) and $settingKey > 0)? $this->keyAttr: 'name';
        $cashResult =  $this->getFromCash($settingKey, $whereAttr);
        if($cashResult && isset($cashResult['intval'])){
            return $cashResult['intval'];
        }
        //not in cash
        $this->db->select($this->keyAttr.',name,intval');
        $this->db->where($whereAttr, $settingKey);

        $query = $this->db->get($this->table);
        $result = $query->row_array();
        $this->addToCash([$result]);

        return $result['intval'];
    }

    function getEpanelSetting(){
        $this->db->where('name', 'defaultLanguage');
        $this->db->or_where('name', 'maxLoginTry');
        $this->db->or_where('name', 'strictIPaddress');
        $this->db->or_where('name', 'breathTimer');
        $query = $this->db->get($this->table);
        $result = array();
        foreach ($query->result_array() as $row)
        {
            $result[$row['name']] = ($row['strval'] == '')? $row['intval'] : $row['strval'];
        }

        return $result;
    }

    function updateEpanelSetting($name, $attr, $value){
        $this->db->where('name', $name);
        $this->db->set($attr, $value);
        $this->db->update($this->table);
    }

}