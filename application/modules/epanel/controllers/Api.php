<?php (defined('BASEPATH')) OR exit('No direct script access allowed');


class Api extends Epanel_Core
{
    protected $isLogin = false;

    public function __construct()
    {
        $this->load->helper(array('jwt', 'authorization'));
        $tokenData = [];
        $token = $this->input->get_request_header('token');
        if($token){
            $tokenData = (array)AUTHORIZATION::validateToken($token);

            if(isset($tokenData['sessionID'])){
                session_id($tokenData['sessionID']);

            }
        }
        $this->load->library('session');
        $this->isLogin = $this->isLogin($tokenData);
        parent::__construct();
    }

    public function test(){

        $tokenData = array();
        $tokenData['id'] = 1; //TODO: Replace with data for token
        $output['token'] = AUTHORIZATION::generateToken($tokenData);
//        $this->set_response($output, REST_Controller::HTTP_OK);
        $data = AUTHORIZATION::validateToken($output['token']);
        echo json_encode(['session'=>$this->sessionengine->getEpanel() ,
            'token'=>$output['token'] ,
            'data' => $data,
            'post' => $_POST, 'get' => $_GET, 'request' => $_REQUEST]);
    }

    public function action(){
        $this->load->model('Mdl_Permission');

        $area = 1;
        $type = 'permission';
        $action = $this->input->input_stream('action')? $this->input->input_stream('action') : $this->input->post('action');
        $userID = $this->sessionengine->getUserInfo('userID');
        $permission = $this->Mdl_Permission->getPermissionIDFromAction($action);
        $data = [];


        //get action part and query
        $parts = parse_url($action);
        if(array_key_exists ( 'query' , $parts )){
            parse_str($parts['query'], $parameters);
        }else{
            $parameters = [];
        }
        if(!array_key_exists ( 'appliedid' , $parameters )){
            if(array_key_exists ( 'id' , $parameters )){
                $parameters['appliedid'] = $parameters['id'];
            }elseif ($this->input->post('applieedid')){
                $parameters['appliedid'] = $this->input->post('appliedid');
            }elseif ($this->input->post('id')){
                $parameters['appliedid'] = $this->input->post('id');
            }else{
                $parameters['appliedid'] = 0;
            }
        }
        $parameters['area'] = $area;
        $parameters['permission'] = $permission;
        $parameters['type'] = $type;

        $link = $parts['path'];

        $actionSecurity = $this->actionSecurity($type, $area, $permission, $userID, $link, $parameters);
        if($actionSecurity['success']){
            Epanel_Core::$key = true;
            Epanel_Core::$APICall = true;
            $run = modules::run($link, $parameters);
            if($run){
                $data = $run;
                //get none displayed permission in this action
//                $this->getNoneDisplayedAction($type, $area, $permission);
            }else{
                $this->nanaengine->addMsg('error', 'runFailed');
                $this->nanaengine->setSuccess(false);
                $this->nanaengine->setDebug(['run'=> $run, 'link' => $link, 'paramters'=> $parameters, 'session' => $this->session->userdata]);
            }
        }else{
            $this->nanaengine->addMsg('error', $actionSecurity['msg']);
            $this->nanaengine->setSuccess(false);
            $this->nanaengine->setDebug($actionSecurity['debug']);
        }

        $this->nanaengine->returnAPI();
    }

    public function actionSecurity($type, $area, $permission, $userID, $link, $parameters){
        $result = [
            'success' => false
        ];

        if(!$this->isLogin){
            $result['msg'] = 'notLogin';
            $result['debug'] = ['userid'=> $userID, 'session' => $this->sessionengine->getSessionKey()];
        }
//        elseif(!$this->isAuthenticated($type, $area, $permission)){
//            $result['msg'] = 'notAuthenticated';
//            $result['debug'] = ['type'=> $type, 'permission' => $permission, 'area' => $area];
//        }
        else{
            $result['success'] = true;
        }

        return $result;
    }

     public function index()
     {

         if($this->isLogin){
            $this->action();
        }


        elseif ($this->input->input_stream('username') && $this->input->input_stream('password')) {
            $userData = $this->login($this->input->input_stream('username'), $this->input->input_stream('password'), $this->input->ip_address());
            if ($userData) {
                $this->buildAPI($userData);
            } else {
                $this->notLogin();
            }
        }
        else{
            $this->nanaengine->addmsg('error', 'no login parameters');
            $this->notLogin();
        }
    }

    private function notLogin(){
        $this->nanaengine->returnAPI([], false, 401);
    }

    private function buildAPI($userData){


        $notifications = $this->notificationengine->getMyLastNotifications();
        $security = [
            'sessionKey' => $this->sessionengine->getSessionKey(),
            'sessionID' => $this->sessionengine->getSessionID(),
            'userID' => $this->sessionengine->getUserInfo('userID')
        ];

        $token = AUTHORIZATION::generateToken($security);
        unset($userData['epanel']);
        unset($userData['setting']);

        $userAuthorization = $this->getUserAuthorization($userData['user_id']);
        $this->sessionengine->setValue('userAuthorization', $userAuthorization);

        $data = [
            'notifications' => $notifications,
            'version' => $this->config->item('version'),
            'epanelLink' => $this->config->item('epanel-link'),
            'userData' => $userData,
            'authorization' => $userAuthorization,
            'token' => $token,
            'title' => 'epanel-sys',
            'time' =>  date($this->config->item('dateTimeFormat'), now())
        ];
        $this->nanaengine->addToData($data);
        $this->nanaengine->returnAPI();
    }

    /**
     * after successfully login build user login data
     * delete old login tries
     * build new session data
     * delete other user breath data
     * create new breath data
     * @param int user id
     * @param string user name
     * @param string user ip address
     * @return array|false userdata if all action done successfully
     *
     */
    protected function buildUserData($userID, $userName, $ipAddress){
        $this->load->model('Mdl_Login');

        $this->Mdl_Login->deleteLogin($userName, $ipAddress);
        $userData = $this->getUserEnvironment($userID);
        if(is_array($userData)){
            if($this->createNewSession($userData) && $userData['epanel']){
                return $userData;
            }
        }
        $this->nanaengine->addMsg('error', 'can not build API');
        return false;
    }

    public function isLogin($tokenData = 0)
    {
        $session = $this->sessionengine->getSessionKey();
        if($session && isset($tokenData['sessionKey']) && isset($tokenData['userID'])){
            //todo:validate session is still valid data
            return
                ($tokenData['sessionKey'] == $this->sessionengine->getSessionKey()
                    && $tokenData['userID'] == $this->sessionengine->getuserinfo('userID'))? true: $this->tryLogin($tokenData);
        }
        return false;
    }

    private function tryLogin($tokenData){
        if(isset($tokenData['userID'])){
            $userData = $this->getUserEnvironment($tokenData['userID']);
            if(is_array($userData)){
                if($this->createNewSession($userData) && $userData['epanel']){
                    $userAuthorization = $this->getUserAuthorization($userData['user_id']);
                    $this->sessionengine->setValue('userAuthorization', $userAuthorization);

                    return $userData;
                }
            }
        }
        return false;
    }

//    helper functions ////////////////////////////////////////////////////////////////////////////////////////////////////////////

}