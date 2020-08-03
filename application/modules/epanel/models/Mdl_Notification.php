<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_Notification extends MY_Model
{
    function __construct() {
        $this->table = 'notifications';
        $this->keyAttr = 'id';
    }

    function getDomainID($domainName){
        $this->db->select('domain_id');
        $this->db->where('name', $domainName);

        $query = $this->db->get('notifi_domains');
        $domain = $query->row_array();

        return $domain['domain_id'];
    }

    function getLastNotification($userID, $roleID, $roleType, $since, $limit){
        $this->db->select('notifications.*,users.name as userName');
        $this->db->join('users', 'users.id = notifications.user_id', 'left');
        $this->db->join('notifi_domains', 'notifi_domains.domain_id = notifications.domain', 'left');
        $this->db->join('notifi_register', 'notifi_register.domain = notifi_domains.domain_id', 'left');

        $this->db->group_start();
        $this->db->or_group_start()->where('type', 'user')->where('type_id', $userID)->group_end();
        $this->db->or_group_start()->where('type', 'role')->where('type_id', $roleID)->group_end();
        $this->db->or_group_start()->where('type', 'type')->where('type_id', $roleType)->group_end();
        $this->db->or_group_start()->where('notifi_user', $userID)->where('notifications.domain', '0')->group_end();
        $this->db->group_end();

        $this->db->where('date >', $since);
        $this->db->order_by('date', 'DESC');
        $this->db->group_by('notifications.notification_id');
        $this->db->limit($limit);

        $query = $this->db->get($this->table);
        $result = $query->result_array();
        if($result){
            $this->resetMyNew($userID);
        }

        return $result;

    }

    function getDomainUsers($domainID){
        $this->db->distinct();
        $this->db->select('users.user_id');
        $this->db->join('users',
            "(users.id = notifi_register.type_id  and notifi_register.type = 'user') or (users.epanel = notifi_register.type_id  and notifi_register.type = 'role')"
            , 'left');
        $query = $this->db->get('notifi_register');
        $result = $query->result_array();
        return $result;
    }

    function resetMyNew($userID){
        $this->db->where('notifi_user', $userID);
        $this->db->where('notifications.domain', '0');

        $this->db->set('new', '0');
        $this->db->update($this->table);
    }

    function getActiveDomains(){
        $this->db->where('active', 1);
        $query = $this->db->get('notifi_domains');
        return $query->result_array();
    }

    function getActiveDomainsWithUser($userID){
        $this->db->select('notifi_domains.*, notifi_register.*');
        $this->db->join('notifi_register', "notifi_register.domain = notifi_domains.domain_id and type = 'user' and type_id = '{$userID}'" , 'left');
        $this->db->where('active', 1);
        $query = $this->db->get('notifi_domains');
        return $query->result_array();
    }

    function getActiveDomainsWithRole($roleID){
        $this->db->select('notifi_domains.*, notifi_register.*');
        $this->db->join('notifi_register', "notifi_register.domain = notifi_domains.domain_id and type = 'role' and type_id = '{$roleID}'" , 'left');
        $this->db->where('active', 1);
        $query = $this->db->get('notifi_domains');
        return $query->result_array();
    }

    function removefromRigter($data){
        foreach ($data as $attr => $val){
            $this->db->where($attr, $val);
        }
        return $this->db->delete('notifi_register');
    }

    function addToTable($data, $table){
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }
}