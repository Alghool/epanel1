<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');

class Epanel_Core extends MX_Controller{
    private $excludedControllers =['Epanel', 'Api'];
    protected static $key = false;
    public static $APICall = false;
    public static $enableMigration = true;
    //0 = do not learn new actions, 1= learn only from a developer, 2 = learn from any one
    private $learnMode = 2;


    public function __construct()
    {
        parent::__construct();
        $called = get_called_class();
        if(in_array($called,$this->excludedControllers)) return;
        //cant be direct access
        if(!Epanel_Core::$key){
            echo $called .": direct access dined!";
            die();
        }
    }
    /// user methods ///////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * get full epanel all areas and permission Or for role
     * @param int optional user role id
     * @param int optional user role type
     * @return array of Epanel areas and permissions data
     * <p> if no role get all areas and permissions</p>
     */
    protected function getEpanelAreasAndPermissions($roleID = 0, $roleType = 0){
        $this->load->Model('Mdl_Area');
        $this->load->Model('Mdl_Permission');
        $this->load->Model('Mdl_Role');
        $this->load->Model('Mdl_Setting');
        $areas = $this->Mdl_Area->getRoleAreas($roleID);
        //if single area site local admin count as super admin in permissions get
        if(!$this->Mdl_Setting->getSettingOnOff('multiarea') && in_array($roleType, [4, 5])){
            $permissions = $this->Mdl_Permission->getRolePermissions(0);
        }
        elseif($roleID != 0 &&(in_array($roleType, [4, 5]))){
            //check if he is an admin so get all his areas permissions
            $permissions = $this->Mdl_Permission->getAreaPermissions(array_map(function($area){return $area['id'];},$areas));
        }else{
            $permissions = $this->Mdl_Permission->getRolePermissions($roleID);
        }

        $result = [
            'areas' => $areas,
            'permissions' => $permissions
        ];
        return $result;
    }
    /**
     * get user Full data include epanel role and account and user settings
     * included data depending on setting and user status
     * @param int user ID
     * @return array of user data
     * <p> depends on epanel and user setting</p>
     */
    protected function getUserEnvironment($userID){
        $this->load->model('Mdl_Role');
        $this->load->model('Mdl_Setting');
        $this->load->model('Mdl_Policy');
        $this->load->module('users')->load->model('Mdl_User');
        $this->load->module('users')->load->model('Mdl_User_Setting');

        $user = $this->Mdl_User->getByIDWithRole($userID);
        $user['setting'] = $this->Mdl_User_Setting->getUserSetting($userID);

        if($user['epanel'] > 0){
            //epanel data

            switch((int)$user['type']){
                case 6:
                case 7:
                case 10:
                    $user['epanel'] = $this->getEpanelAreasAndPermissions();
                    $user['epanel']['policies'] = $this->Mdl_Policy->getPoliciesStructure($user['type'],$user['user_id']);
                    break;
                case 0:
                    $this->nanaengine->addMsg( 'error', 'userpanned');
                    $user['epanel'] = false;
                    break;
                case 1:
                    $this->nanaengine->addMsg( 'error', 'userhasnorole');
                    $user['epanel'] = false;
                    break;
                default:
                    $user['epanel'] = $this->getEpanelAreasAndPermissions($user['id'], $user['type']);
                    $user['epanel']['policies'] = $this->Mdl_Policy->getPoliciesStructure($user['type'],$user['user_id']);
                    break;
            }
        }else{
            $this->nanaengine->addMsg( 'error', 'userhasnorole');
            $user['epanel'] = false;
        }

        if( $this->Mdl_Setting->getSettingOnOff('siteAccount')){
            //todo: implement site accounts
            $this->load->model('Mdl_Account');
            $user['account'] = $this->Mdl_Account->getUserAccount($userID);
        }

        return $user;
    }
    /// security methods ////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * check if this action pass all security points
     * if valid return success = true
     * @param string action type core, area, permission
     * @param int area id
     * @param int permission id
     * @param int user id
     * @param string action link
     * @param array action parameters
     * @return array with success key true|false and msg, debug keys for false case
     */
    protected function actionSecurity($type, $area, $permission, $userID, $link, $parameters){
        $result = [
            'success' => false
        ];
        //area and permission should be exist for normal actions
        //userID should be set

        if((((!$permission && $type == 'permission') || !$area) && $type != 'core' ) || !$userID ) {
            $result['msg'] = 'noparamter';
            $result['debug'] = ['type' => $type, 'permission' => $permission, 'area' => $area];
        }
        elseif(!$this->isLogin($userID)){
            $result['msg'] = 'notLogin';
            $result['debug'] = ['userid'=> $userID, 'session' => $this->sessionengine->getSessionKey()];
        }
        elseif(!$this->isAuthenticated($type, $area, $permission)){
            $result['msg'] = 'notAuthenticated';
            $result['debug'] = ['type'=> $type, 'permission' => $permission, 'area' => $area];
        }
        elseif(!$this->isActionAllowed($type, $area, $permission, $link, $parameters['appliedid'])){
            $result['msg'] = 'noPolicyNorAction';
            $result['debug'] = ['type'=> $type, 'permission' => $permission, 'area' => $area, 'link' => $link, 'appliedID'=> $parameters['appliedid']];
        }
        else{
            $result['success'] = true;
        }
        return $result;
    }
    /**get none displayed permission in this action
     * some action has a hidden permission that applied on certain actions
     * in that action so here we add those to nana engine data array
     * @param string action type
     * @param int permission id
     * @param int area id
     */
    protected function getNoneDisplayedAction($type, $area, $permission){
        if($type == 'permission'){
            $actionPermissions = [];
            $userPermissions = $this->sessionengine->getEpanel('permissions');
            foreach ($userPermissions as $thisPermission){
                if($thisPermission['display'] == 'none' && $thisPermission['parent'] == $permission){
                    $actionPermissions[$thisPermission['name']] = $thisPermission['id'];
                }
            }
            $this->nanaengine->addToData([
                'actionPermissions' => $actionPermissions
            ]);
        }elseif ($type == 'area'){
            //todo: handle multi area sites
            if(!$this->Mdl_Setting->getSettingOnOff('multiarea')){
                $actionPermissions = [];
                $userPermissions = $this->sessionengine->getEpanel('permissions');
                foreach ($userPermissions as $thisPermission){
                    if($thisPermission['display'] == 'none' && $thisPermission['parent'] == 0){
                        $actionPermissions[$thisPermission['name']] = $thisPermission['id'];
                    }
                }
                $this->nanaengine->addToData([
                    'actionPermissions' => $actionPermissions
                ]);
            }

        }
    }
    /**
     * create New Session from user data array
     * @param array user data array
     * @return true|false if session created successfully
     */
    protected function createNewSession($userData){
        $this->load->model('Mdl_Setting');
        $sessionKey =  hash('ripemd160',$this->input->ip_address() .$this->config->item('ip-salt'));
        $userInfo = array(
            'userID'  => $userData['user_id'],
            'name'  => $userData['name'],
            'username'  => $userData['username'],
            'pic' => $userData['pic'],
            'lang' => (isset($userData['setting']['language']))? $userData['setting']['language']:$this->Mdl_Setting->getSettingStrValue('defaultLanguage') ,
            'role' => $userData['id'],
            'roleType' => $userData['type']
        );
        $sessionData =[
            'userInfo' => $userInfo,
            'sessionKey' => $sessionKey,
            'setting' => $userData['setting'],
            'epanel' => $userData['epanel']
        ];
        return $this->sessionengine->initSession($sessionData);
    }
    /**
     * create New user breath and delete old user breath
     * @param int user id
     * @param string user ip address
     * @return true|false if breath created successfully
     */
    function createNewBreath($userID, $ipAddress){
        $this->load->model('Mdl_Breath');
        $this->Mdl_Breath->deleteByAttr('user_id', $userID);
        $time = now();
        $breath = [
            'session' => $this->sessionengine->getSessionKey(),
            'user_id' => $userID,
            'ipaddress' => $ipAddress,
            'start_time' => $time,
            'last_time' => $time,
        ];
        return ($this->Mdl_Breath->addByData($breath))? true: false;
    }
    /**
     * check if user is allowed to call those area and permission
     * <p>this action is only effective is area or permission calls not core</p>
     * @param string action type
     * @param int area id
     * @param int permission id
     * @return true|false if user allowed to call this action
     */
    protected  function isAuthenticated($type, $area, $permission){
        if($type != 'area' || $type != 'permission') return true;
        if($this->sessionengine->getUserInfo('roleType') == 10) return true; //welcome codeMechanic
        if($this->sessionengine->getUserInfo('roleType') > 5) return true; //super admin and owner can access what ever they wants

        if($type == 'area'){
            $userAreas = $this->sessionengine->getEpanel('areas');
            foreach($userAreas as $area){
                if($area['id'] == $area) return true;
            }
        }
        if($type == 'permission'){
            $userPermissions = $this->sessionengine->getEpanel('permissions');
            foreach($userPermissions as $thisPermission){
                if($thisPermission['id'] == $permission){
                    $this->load->model('Mdl_Permission');
                    return $this->Mdl_Permission->IsPermissionInArea($permission, $area);
                }
            }
        }

        return false;
    }
    /**
     * check if user is allowed to call this action in this area and permission
     * <p>if codemechanic is login system will learn actions from him</p>
     * @param string action type
     * @param int area id
     * @param int permission id
     * @param string action link
     * @param int optional applied id if exist
     * @return true|false if user allowed to call this action
     */
    protected function isActionAllowed($type, $area, $permission, $link, $appliedID = 0){
        if($type == 'core') {
            if ($this->sessionengine->getUserInfo('roleType') == 10) return true; //welcome codeMechanic
            $this->load->model('Mdl_Policy');
            $this->load->module('users')->load->model('Mdl_User');

            $activeUser = [
                'userID' => $this->sessionengine->getUserInfo('userID'),
                'roleType' => $this->sessionengine->getUserInfo('roleType')
            ];
            if ($appliedID == 0) {
                $appliedUser = 0;
            } else {
                $user = $this->Mdl_User->getByIDWithRole($appliedID);
                $appliedUser = [
                    'userID' => $user['user_id'],
                    'roleType' => $user['type']
                ];
            }
            return $this->Mdl_Policy->CheckPolicyByLink($link, $activeUser, $appliedUser);
        }
        else{
            $this->load->model('Mdl_Action');
            $container = ($type == 'area')? $area: $permission;
            if($this->Mdl_Action->isActionExist($type, $container, $link)){
                return true;
            }elseif($this->sessionengine->getUserInfo('roleType') == 10 && $this->learnMode > 0){
                $this->Mdl_Action->learnAction($type, $container, $link);
                return true;
            }elseif($this->learnMode == 2){
                $this->Mdl_Action->learnAction($type, $container, $link);
                return true;
            }
            return false;
        }
    }
    /**
     * try login taking care of max login try and session initialize
     * if valid it starts a new session
     * @param string username
     * @param string user password
     * @param string client ip address
     * @return true|false
     * <p> depends on verification status</p>
     */
    protected function login($userName, $password, $ipAddress)
    {
        $this->load->model('Mdl_Login');
        $this->load->model('Mdl_Setting');
        $this->load->module('users')->load->model('Mdl_User');

        $lastTry = $this->Mdl_Login->getLastTry($userName, $ipAddress);
        $maxTries = $this->Mdl_Setting->getSettingIntValue('maxLoginTry');
        $userID = $this->Mdl_User->verifyActiveUser($userName, $password);

        if($lastTry && $maxTries != 0 && now() - $this->config->item('login-holdup')  <= $lastTry['lasttime'] && $lastTry['count'] >= $maxTries){
            //cant try login he reached maximum try
            $this->nanaengine->addMsg( 'error', 'limitlogintry', ['holdup' => $this->config->item('login-holdup') - (now() - $lastTry['lasttime']) ]);
            return false;
        }elseif($userID) {
            //login successful
            return $this->buildUserData($userID, $userName, $ipAddress);

        }elseif($maxTries == 0){
            //login failed without limit
            $this->nanaengine->addMsg('error', 'wronglogindata');
            return false;
        }elseif($lastTry && now() - $this->config->item('login-holdup')  <= $lastTry['lasttime']){
            //login failed
            $tryCount = $lastTry['count'] + 1 ;
            $this->nanaengine->addMsg('error', 'loginfaild', ['remaining' => $maxTries - $tryCount]);
            $this->Mdl_Login->updateLoginTry($lastTry[$this->Mdl_Login->getKeyAttr()], $tryCount);
            return false;
        }else{
            //login failed
            $this->nanaengine->addMsg('error', 'loginfaild', ['remaining' => $maxTries - 1]);
            $this->Mdl_Login->newLogin($userName, $ipAddress);
            return false;
        }
    }
    /// login handling methods ////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * check if use is login and has a valid session
     * @return int|false if user login return user id or return false
     */
    protected function isLogin($userID = 0)
    {
        $session = $this->sessionengine->getSessionKey();
        if($session){
            if($userID){
                if($userID != $this->sessionengine->getUserInfo('userID')) return false;
            }
            $this->load->model('Mdl_Setting');
            $this->load->model('Mdl_Breath');

            $isStrictIPaddress = $this->Mdl_Setting->getSettingOnOff('strictIPaddress');
            $ipaddress = ($isStrictIPaddress)? $this->input->ip_address(): 0;
            $userID = $this->sessionengine->getUserInfo('userID');
            $minTime = now() - ($this->Mdl_Setting->getSettingIntValue('breathTimer') * 4);
            return $this->Mdl_Breath->isUserBreath($userID, $session, $minTime, $ipaddress);
        }
       return false;
    }

    protected function getUserAuthorization($userID){
        //todo: rebuild this to handle site accounts
        return ['accountType' => 'none'];
    }
}