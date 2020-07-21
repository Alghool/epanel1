<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Role extends Epanel_Core {

    public function __construct()
    {
        parent::__construct();
    }

    public function getRoles(){
        $this->load->model('Mdl_Role');
        $userRole = $this->sessionengine->getUserInfo('roleType');

        $data['roles'] = $this->Mdl_Role->getRoleListForUser($userRole);

        $policies = $this->sessionengine->getEpanel('policies');
        $data['policies'] =[
            'switch'  => $policies['switchRole'],
            'add'     => $policies['addRole'],
            'edit'    => $policies['editRole'],
            'default' => $policies['defaultRole'],
            'delete'  => $policies['deleteRole'],
        ];

        $this->nanaengine->addToData($data);
        $this->nanaengine->setPage('epanel/panels/epanel-roles');
        $this->nanaengine->setSuccess();
        RETURN true;
    }

    public function addRole($parameters){
        $this->load->model('Mdl_Role');
        $this->load->model('Mdl_Setting');
        $isMultiArea = $this->Mdl_Setting->getSettingOnOff('multiarea');
        $data = [];

        if($this->input->Post('name') != null) { //is submitting
            $name = $this->input->Post('name');
            $type = (int)$this->input->Post('myType');
            //todo: handle multi area sites
            $areas = [];
            $permissions = [];
            switch ($type) {
                case 0:
                case 1:
                case 6:
                case 7:
                   //super admin and owner no need to add areas noe permission
                    //panned and no role no need to add areas nor permissions
                    break;
                case 2:
                    //normal user
                    $areas[] = ($this->input->Post('myArea'))? (int)$this->input->Post('myArea') : 1;
                    $permissions = $this->input->Post('permissionArr');
                    break;
                case 4:
                    //local admin
                    $areas[] = ($this->input->Post('myArea'))? (int)$this->input->Post('myArea') : 1;
                    break;
                case 3:
                    //special user
                    $areas = $this->input->Post('areaArr');
                    $permissions = $this->input->Post('permissionArr');
                    break;
                case 4:
                    //multi admin 
                    $areas = $this->input->Post('areaArr');
            }

            $data =[
                'name'=> $name,
                'type'=> $type,
                'default'=> 0,
                'core' => 0,
                'active' => 1,
                'created_date' => now()
            ];

            $roleID= $this->Mdl_Role->addByData($data);
            foreach ($this->input->post('domainsArr') as $domain){
                $this->notificationengine->registerToDomain('role', $domain, $roleID);
            }
            //$result['success'] = false;
            if($roleID) {
                if(!empty($areas)){
                    $this->load->Model('Mdl_Area');
                    $this->Mdl_Area->addAreasToRole($areas, $roleID);
                }
                if(!empty($permissions)){
                    $this->load->Model('Mdl_Permission');
                    $this->Mdl_Permission->addPermissionsToRole($permissions, $roleID);
                }
            }
            $this->logengine->addLog('new role created with name '.$name);
            $this->nanaengine->addMsg('success', 'successfullyAdded');
            $this->nanaengine->setSuccess();
        }
        else{// request the form
            $types = $this->config->item('roleTypes');
            $userType = $this->sessionengine->getUserInfo('roleType');
            $data['types'] = [];
            for($i = 0; $i< count($types); $i++){
                if($types[$i]['id'] < $userType){
                    if(!$isMultiArea && ($types[$i]['id'] == 3 || $types[$i]['id'] == 5)) continue;
                    $data['types'][] = $types[$i];
                }
            }
            $data['domains'] = $this->notificationengine->getActiveDomains();

            $this->nanaengine->addToData($data);
            $this->nanaengine->setPage('epanel/panels/add-role');
            $this->nanaengine->setSuccess();
        }
        RETURN true;
    }

    public function editRole($parameters)
    {
        $this->load->model('Mdl_Role');
        $this->load->model('Mdl_Setting');
        $this->load->model('Mdl_Permission');
        $roleID = $parameters['appliedid'];
        $role = $this->Mdl_Role->getRole($roleID);
        $isMultiArea = $this->Mdl_Setting->getSettingOnOff('multiarea');
        $data = [];
        if($this->input->Post('name') != null) { //is submitting
            $type = (int)$this->input->Post('myType');

            $role = [
                'name'=> $this->input->Post('name'),
                'type'=> $type
            ];

            $this->Mdl_Role->editByData($roleID, $role);

            $this->Mdl_Permission->RemoveRolePermission($roleID);
            switch ($type) {
                case 0:
                case 1:
                case 6:
                case 7:
                    //super admin and owner no need to add areas noe permission
                    //panned and no role no need to add areas nor permissions
                    break;
                case 2:
                    //normal user
                    $areas[] = ($this->input->Post('myArea'))? (int)$this->input->Post('myArea') : 1;
                    $permissions = $this->input->Post('permissionArr');
                    break;
                case 4:
                    //local admin
                    $areas[] = ($this->input->Post('myArea'))? (int)$this->input->Post('myArea') : 1;
                    break;
                case 3:
                    //special user
                    $areas = $this->input->Post('areaArr');
                    $permissions = $this->input->Post('permissionArr');
                    break;
                case 4:
                    //multi admin
                    $areas = $this->input->Post('areaArr');
            }
            if($roleID) {
                if(!empty($areas)){
                    $this->load->Model('Mdl_Area');
                    $this->Mdl_Area->addAreasToRole($areas, $roleID);
                }
                if(!empty($permissions)){
                    $this->load->Model('Mdl_Permission');
                    $this->Mdl_Permission->addPermissionsToRole($permissions, $roleID);
                }
            }

            $this->notificationengine->removeAllFromDomain('role', $roleID);
            foreach ($this->input->post('domainsArr') as $domain){
                $this->notificationengine->registerToDomain('role', $domain, $roleID);
            }

            $this->logengine->addLog('role ' .$role['name'] .' edit');
            $this->nanaengine->addMsg('success', 'successfullyUpdated');
            $this->nanaengine->setSuccess();
        }
        else{

            $data['role'] = $role;
            $roleEpanel = $this->getEpanelAreasAndPermissions($roleID, $role['type']);
            $rolePermission = array_column($roleEpanel['permissions'], 'permission_id');
            $data['rolePermission'] = json_encode($rolePermission);

            $data['domains'] = $this->notificationengine->getActiveDomainsWithRole($roleID);
            $data['edit'] = 'edit';

            $types = $this->config->item('roleTypes');
            $userType = $this->sessionengine->getUserInfo('roleType');
            $data['types'] = [];
            for($i = 0; $i< count($types); $i++){
                if($types[$i]['id'] < $userType){
                    if(!$isMultiArea && ($types[$i]['id'] == 3 || $types[$i]['id'] == 5)) continue;
                    $types[$i]['selected'] =($types[$i]['id'] == $role['type']) ? 1 : 0;

                    $data['types'][] = $types[$i];
                }
            }

            $this->nanaengine->addToData($data);
            $this->nanaengine->setPage('epanel/panels/add-role');
            $this->nanaengine->setSuccess();
        }
        return true;
    }

    public function switchRole($parameters){
        $this->load->model('Mdl_Role');

        $roleID = $parameters['role'];
        $currentStatusActive = $this->Mdl_Role->switchRole($roleID);
        $role = $this->Mdl_Role->getByID($roleID, 'name');

        if($currentStatusActive) {
            $text = " set to active";
            $this->nanaengine->addMsg('info', 'itemActivated', ['item'=> $role['name']]);
        }
        else {
            $text = " set to inactive";
            $this->nanaengine->addMsg('info', 'itemDeactivated', ['item'=> $role['name']]);
        }

        $this->logengine->addLog('role '.$role['name'] .$text);
        $this->nanaengine->setSuccess();
        return true;
    }

    public function defaultRole($parameters){
        $this->load->model('Mdl_Role');

        $roleID = $parameters['role'];
        $result = $this->Mdl_Role->setDefaultRole($roleID);

        $role = $this->Mdl_Role->getByID($roleID, 'name');
        if($result) {
            $this->logengine->addLog('role '.$role['name'] .' set to default');
            $this->nanaengine->addMsg('success', 'successfullyUpdated');
            $this->nanaengine->setSuccess();
        }
        else {
            $this->nanaengine->addMsg('error', 'actionFailed');
        }
        return true;
    }

    public function deleteRole($parameters){
        $this->load->model('Mdl_Role');

        $roleID = $parameters['role'];
        $role = $this->Mdl_Role->getByID($roleID, 'name,default');
        if($role['default'] == 1){
            $this->nanaengine->addMsg('error', 'cantDeleteDefault');
            return true;
        }
        $this->Mdl_Role->deleteRole($roleID);

        $this->load->module('users')->load->model('Mdl_User');
        $defaultRole = $this->Mdl_Role->getRowByAttr('default', '1', 'role_id');
        //todo: add notifications to users of role changes
        $this->Mdl_User->editByAttr('epanel', $roleID, ['epanel'=>$defaultRole['role_id']]);

        $this->logengine->addLog('role '.$role['name'] .' deleted');
        $this->nanaengine->addMsg('success', 'itemDeleted');
        $this->nanaengine->setSuccess();
        return true;
    }
}
