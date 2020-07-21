<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mdl_Policy extends MY_Model
{
    function __construct()
    {
        $this->table = 'epanel_policies';
        $this->keyAttr = 'policy_id';
    }

    function getPoliciesStructure($roleType, $userID){
        $result = array();

        $actions = $this->getPoliciesActions();
        if($roleType == 10){
            //codemechanic has green card for all policies
            foreach ($actions as $action){
                $result[$action['name']] = 1;
            }
        }else{
            foreach ($actions as $action){
                $policies = $this->getActiveActionPolicies($action['name']);
                $result[$action['name']] = 1;
                foreach ($policies as $policy){
                    $policyRun = $this->policyInitial($policy, ['userID'=>$userID, 'roleType'=>$roleType]);
                    if($policyRun !== -1){
                        $result[$action['name']] = $policyRun;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    function getPoliciesActions(){
        $this->db->select($this->keyAttr .',name,link');
        $this->db->where('active', 1);
        $this->db->group_by('name');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    function getActiveActionPolicies($actionName, $selector = 'name'){
        $this->db->where($selector, $actionName);
        $this->db->where('active', 1);
        $this->db->order_by('priority', 'ASC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    function CheckPolicyByName($name, $user, $applied = 0){
       $polcies = $this->getActiveActionPolicies($name);
       foreach ($polcies as $policy){
           $policyRun = $this->PolicyRun($policy, $user, $applied);
           if($policyRun !== -1){
               return $policyRun;
               break;
           }
       }
       return true;
    }

    function CheckPolicyByLink($link, $user, $applied = 0){
        $polcies = $this->getActiveActionPolicies($link, 'link');
        foreach ($polcies as $policy){
            $policyRun = $this->PolicyRun($policy, $user, $applied);
            if($policyRun !== -1){
                return (bool)$policyRun;
                break;
            }
        }
        return true;
    }
    //inner function ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private function policyInitial($policy, $activeUser){
        $first = 0;
        $second = 0;
        $action = 0;

        switch ($policy['type']){
            case 'request role':
                $first = $activeUser['roleType'];
                break;
            case 'request user':
                $first = $activeUser['userID'];
                break;
            case 'applied role':
            case 'applied user':
                return -1;
        }

        switch ($policy['policy']){
            case 'higher':
            case 'lower':
            case 'equal':
                $second = $policy['intval'];
                $action = $policy['policy'];
                break;
            case 'self':
                return $policy['result'];
                break;
        }

        return ($this->getActionResult($first, $second, $action))? $policy['result'] : -1;
    }

    function PolicyRun($policy, $activeUser, $appliedUser){
        $first = 0;
        $second = 0;
        $action = 0;

        switch ($policy['type']){
            case 'request role':
                $first = $activeUser['roleType'];
                break;
            case 'request user':
                $first = $activeUser['userID'];
                break;
            case 'applied role':
                if(!isset($appliedUser['roleType'])) return -1;
                $first = $appliedUser['roleType'];
                break;
            case 'applied user':
                if(!isset($appliedUser['userID'])) return -1;
                $first = $appliedUser['userID'];
                break;
        }

        switch ($policy['policy']){
            case 'higher':
            case 'lower':
            case 'equal':
                $second = $policy['intval'];
                $action = $policy['policy'];
                break;
            case 'self':
                if(!is_array($appliedUser)) return $policy['result'];
                $second = (in_array($policy['type'], ['request role', 'applied role']))? $appliedUser['roleType'] : $appliedUser['userID'];
                $action = 'equal';
                break;
        }

        return ($this->getActionResult($first, $second, $action))? $policy['result'] : -1;
    }

    private function getActionResult($firstParam, $secondParam, $action){
        switch ($action){
            case 'equal':
                return ($firstParam == $secondParam);
            case 'lower':
                return ($firstParam < $secondParam);
            case 'higher':
                return ($firstParam > $secondParam);
        }
    }
}