<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_epanel extends Epanel_Core {

    public function __construct()
    {
        parent::__construct();
    }

     public function getUsers($parameters){
         $this->load->model('Mdl_User');

         $data['users'] = $this->Mdl_User->getUsersWithRolesforShow();
         //todo: see if site accounts should be shown here

         $policies = $this->sessionengine->getEpanel('policies');
         $data['policies'] =[
             'switch'  => $policies['switchUser'],
             'add'     => $policies['addUser'],
             'edit'    => $policies['editUser'],
             'password'=> $policies['passwordUser'],
             'show'    => $policies['getUserProfile'],
             'delete'  => $policies['deleteUser'],
         ];

         $this->nanaengine->addToData($data);
         $this->nanaengine->setPage('epanel/panels/users');
         $this->nanaengine->setSuccess();
         RETURN true;
     }

    public function getUserSetting($parameters){
        $this->load->model('Mdl_User_Setting');

        $data = $this->Mdl_User_Setting->getUserSetting($parameters['appliedid']);
        $data['userID'] = $parameters['appliedid'];
        $this->nanaengine->addToData($data);
        $this->nanaengine->setPage('epanel/panels/user-setting');
        $this->nanaengine->setSuccess();
        RETURN true;
    }

    public function setUserSetting($parameters){
        $this->load->model('Mdl_User_Setting');
        $userID = (int)$this->input->post('id');
        $language = $this->input->post('language');
        $themeColor = $this->input->post('themeColor');
        $notificationCount = (int)$this->input->post('notificationCount');

        $this->Mdl_User_Setting->updateUserSetting($userID, 'language', 'strval', $language);
        $this->Mdl_User_Setting->updateUserSetting($userID, 'themeColor', 'strval', $themeColor);
        $this->Mdl_User_Setting->updateUserSetting($userID, 'notificationCount', 'intval', $notificationCount);

        $this->nanaengine->addMsg('success', 'successfullyUpdated');
        $this->nanaengine->setSuccess();
        return true;
    }

    public function getUserProfile($parameters){
        $this->load->Model('Mdl_User');
        $userID = $parameters['appliedid'];
        $data = $this->Mdl_User->getByIDWithRole($userID);

        $data['canChangePassword'] = ($userID == $this->sessionengine->getUserInfo('userID'));
        $data['userID'] = $parameters['appliedid'];
        $this->nanaengine->addToData($data);
        $this->nanaengine->setPage('epanel/panels/user-profile');
        $this->nanaengine->setSuccess();
        RETURN true;
    }

    public function addUser($parameters){
        $this->load->model('Mdl_User');
        $this->load->model('Mdl_User_Setting');
        $this->load->module('epanel')->load->model('Mdl_Role');
        $myType = $this->sessionengine->getUserInfo('roleType');

        $data =[];
        if($this->input->Post('name') != null) { //is submitting
            //image handle
            $image = '';
            if(isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])){
                $image = $this->imageupload->uploadImage('image', 'user');
                $image = ($image['success'])? $image['image'] : '';
            }

            $user = [
                'name' => $this->input->post('name'),
                'username' => trim($this->input->post('username')),
                'email' => ($this->input->post('email') == '')? '' : $this->input->post('email'),
                'phone' =>($this->input->post('phone') == '')? '' : $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
                'active' => '1',
                'created_by' => $this->sessionengine->getUserInfo('userID'),
                'password' => hash('ripemd160',$this->input->post('password') .$this->config->item('pw-salt')),
                'pic'=> $image,
                'create_date' => now(),
                'epanel' => $this->input->post('userRole')
            ];

            $userID = $this->Mdl_User->addByData($user);
            $this->Mdl_User_Setting->createUserSettings($userID);

            foreach ($this->input->post('domainsArr') as $domain){
                $this->notificationengine->registerToDomain('user', $domain, $userID);
            }

            $this->logengine->addLog('new user created with name '.$this->input->post('username'));
            $this->nanaengine->addMsg('success', 'successfullyAdded');
            $this->nanaengine->setSuccess();
        }
        else{
            $data['roles'] = $this->Mdl_Role->getAvailableRoles($myType);
            $data['domains'] = $this->notificationengine->getActiveDomains();

            $this->nanaengine->addToData($data);
            $this->nanaengine->setPage('epanel/panels/add-user');
            $this->nanaengine->setSuccess();
        }

        return $userID ? $userID : true;
    }

    public function updateUser($parameters){
        $this->load->Model('Mdl_User');
        $this->load->Model('Mdl_Role');
        $userID = $parameters['appliedid'];
        $user = $this->Mdl_User->getByIDWithRole($userID);
        $myType = $this->sessionengine->getUserInfo('roleType');

        if($this->input->Post('name') != null) { //is submitting
            $image = $user['pic'];
            if(isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])){
                $image = $this->imageupload->uploadImage('image', 'user');
                if($image['success']){
                    $image = $image['image'];
                    if($user['pic'])  unlink('image/'.$user['pic']);
                }
            }

            $user = [
                'name' => $this->input->post('name'),
                'email' => ($this->input->post('email') == '')? '' : $this->input->post('email'),
                'phone' =>($this->input->post('phone') == '')? '' : $this->input->post('phone'),
                'gender' => $this->input->post('gender'),
                'pic'=> $image,
                'epanel' => $this->input->post('userRole')
            ];

            $this->Mdl_User->editByData($userID, $user);

            $this->notificationengine->removeAllFromDomain('user', $userID);
            foreach ($this->input->post('domainsArr') as $domain){
                $this->notificationengine->registerToDomain('user', $domain, $userID);
            }

            $this->logengine->addLog('user ' .$user['username'] .' edit');
            $this->nanaengine->addMsg('success', 'successfullyUpdated');
            $this->nanaengine->setSuccess();
        }
        else{
            $data['user'] = $user;
            $data['roles'] = $this->Mdl_Role->getAvailableRoles($myType);
            $data['domains'] = $this->notificationengine->getActiveDomainsWithUser($userID);
            $data['edit'] = 'edit';

            $this->nanaengine->addToData($data);
            $this->nanaengine->setPage('epanel/panels/add-user');
            $this->nanaengine->setSuccess();
        }

        return true;
    }

    function switchUser($parameters){
        $userID = $parameters['appliedid'];
        $this->load->model('Mdl_User');

        $currentStatusActive = $this->Mdl_User->switchUser($userID);
        $user = $this->Mdl_User->getByID($userID, 'name');

        if($currentStatusActive) {
            $text = " set to active";
            $this->nanaengine->addMsg('info', 'itemActivated', ['item'=> $user['name']]);
        }
        else {
            $text = " set to inactive";
            $this->nanaengine->addMsg('info', 'itemDeactivated', ['item'=> $user['name']]);
        }

        $this->logengine->addLog('user '.$user['name'] .$text);
        $this->nanaengine->setSuccess();
        return true;
    }

    public function changePassword($parameters){
        $userID = $parameters['appliedid'];

        $this->load->model('Mdl_User');
        $user = $this->Mdl_User->getByID($userID);

        if($this->input->Post('password') != null){ //is submitting
            if(($userID == $this->sessionengine->getUserInfo('userID'))){
                $password = $this->input->post('oldpassword');
                $username = $this->sessionengine->getUserInfo('username');

                if(!$this->Mdl_User->verifyUser($username, $password)){
                    $this->nanaengine->addMsg('error', 'wrongPassword');
                    $this->nanaengine->setSuccess();
                    return true;
                }
            }

            $this->Mdl_User->ChangePassword($userID, $this->input->post('password'));

            if($userID != $this->sessionengine->getUserInfo('userID')){
                $this->logengine->addLog('changed  '.$user['name'] .' password');
                $this->notificationengine->addToUser($userID, 'changePassword');
            }


            $this->nanaengine->addMsg('success', 'passwordUpdated');
            $this->nanaengine->setSuccess();
            return true;

        }else{// request the form
            $data['title'] = $user['name'];
            $data['needOldPassword'] = ($userID == $this->sessionengine->getUserInfo('userID'));
            $data['userID'] = $user['user_id'];

            $this->nanaengine->addToData($data);
            $this->nanaengine->setPage('epanel/panels/changePassword');
            $this->nanaengine->setSuccess();
        }

        return true;
    }

    public function deleteUser($parameters){
        $userID = $parameters['appliedid'];

        $this->load->model('Mdl_User');
        $this->load->model('Mdl_User_Setting');
        $user = $this->Mdl_User->getByID($userID);

        //todo: handle site accounts in better way
        $this->load->module('workers')->load->model('Mdl_Worker');
        $this->Mdl_Worker->deleteWorkerByUserID($userID);

        $this->load->module('clients')->load->model('Mdl_ClientUser');
        $this->Mdl_ClientUser->deleteClientByUserID($userID);

        $this->Mdl_User->deleteByID($userID);
        $this->Mdl_User_Setting->deleteByAttr('user_id', $userID);
        if($user['pic'] != '') unlink('image/'.$user['pic']);

        $this->logengine->addLog('deleted '.$user['name'] .' account');
        $this->nanaengine->addMsg('success', 'userDeleted');
        $this->nanaengine->setSuccess();
        return true;
    }

}