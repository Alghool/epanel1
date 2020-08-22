<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Epanel extends Epanel_Core {

    public function __construct()
    {
        $this->load->library('session');
        parent::__construct();
    }

    public function test(){
//        $koko = file_get_contents(FCPATH ."src/epanel/locales/ar/notifi.json");
//
//        var_dump(json_decode($koko, 1));
//        $this->notificationengine->addToUser(64, ['text'=>'taskAdded', 'task'=>'koko']);
        echo "hi";


//        $string = "component {{component}} in Manufacturing order number {{order}} completed and waiting for rate ";
//        $replacements = array('component'=>'Marycomponent','order'=> 'Janeorder');
//        echo preg_replace_callback('/{{([^}}]+)}}/', function($matches) use (&$replacements) {
//            var_dump($matches);
//            return $replacements[$matches[1]];
//        }, $string);

    }

    public function migrate(){
        if( Epanel_Core::$enableMigration){
            $this->load->library('migration');

            if ($this->migration->current() === FALSE)
            {
                show_error($this->migration->error_string());
            }else{
                echo " migration done successfaly";
            }
        }else{
            echo "no migration allowed";
        }
    }

    /**
     * default epanel method handle login and homepage actions
     */
    public function index()
    {
        if($this->isLogin()){
            $userData = $this->buildUserData($this->sessionengine->getUserInfo('userID'),$this->sessionengine->getUserInfo('username') , $this->input->ip_address());
            $this->buildEpanel($userData);
        }elseif ($this->input->post('username') && $this->input->post('password')){
            $userData = $this->login($this->input->post('username'), $this->input->post('password'), $this->input->ip_address());
            if($userData){
                $this->buildEpanel($userData);
            }else{
                $this->loginPage();
            }
        }else{
            $this->loginPage();
        }
    }
    /**
     * epanel logout action
     */
    public function logout(){
        $userId = $this->sessionengine->getUserInfo('userID');

        //remove breath data
        $this->load->model('Mdl_Breath');
        $this->Mdl_Breath->deleteByAttr('user_id', $userId);
        //remove session data
        $this->sessionengine->removeSession();
        //route to login
        $this->nanaengine->addMsg('success', 'logoutsuccess');
        $this->loginPage();

    }
    /**
     * epanel action handler this gateway is the main request handler
     */
    public function action(){
        $userID = $this->input->post('userID');
        $type = $this->input->post('type');
        $permission = $this->input->post('permission');
        $area = $this->input->post('area');
        $action = $this->input->post('action');
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
            $run = modules::run($link, $parameters);
            if($run){
                $data = $run;
                //get none displayed permission in this action
                $this->getNoneDisplayedAction($type, $area, $permission);
            }else{
                $this->nanaengine->addMsg('error', 'runFailed');
                $this->nanaengine->setSuccess(false);
                $this->nanaengine->setDebug(['run'=> $run, 'link' => $link, 'paramters'=> $parameters]);
            }
        }else{
            $this->nanaengine->addMsg('error', $actionSecurity['msg']);
            $this->nanaengine->setSuccess(false);
            $this->nanaengine->setDebug($actionSecurity['debug']);
        }


        $response = $this->nanaengine->getParsedArray();
        $response['data'] = $data;
        $response['security'] = [
            'tokenName' => $this->security->get_csrf_token_name(),
            'tokenValue' => $this->security->get_csrf_hash(),
        ];

        echo json_encode($response);
    }

    /// actions methods ///////////////////////////////////////////////////////////////////////////////////////////////////////

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
                $userAuthorization = $this->getUserAuthorization($userData['id']);
                $this->sessionengine->setValue('userAuthorization', $userAuthorization);
                if($this->createNewBreath($userID, $ipAddress)){
                    return $userData;
                }
            }
        }
        $this->nanaengine->addMsg('error', 'cantshowepanel');
        return false;
    }
    /**
     * this is a action method
     * @return array server time and user new notifications
     *can only be accessed by epanel actions
     */
    public function _breath(){
        $lastTime =
            (int)$this->input->post('lastTime') ?? 0;
        $data['notifications'] = $this->notificationengine->getMyLastNotifications($lastTime);
        $data['time'] = date($this->config->item('dateTimeFormat'), now());
        $this->nanaengine->addToData($data);
        return $data;
    }
    /// returning view methods /////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * build Epanel main layout
     * @param array $userData
     * showing a view
     */
    private function buildEpanel($userData){
        $notifications = $this->notificationengine->getMyLastNotifications();

        $userData['epanelSetting'] = [
            'breathInterval' => $this->Mdl_Setting->getSettingIntValue('breathTimer'),
            'tokenName' => $this->security->get_csrf_token_name(),
            'tokenValue' => $this->security->get_csrf_hash(),
        ];

        $data = [
            'notifications' => json_encode($notifications),
            'themeColor' => $userData['setting']['themeColor'] ? $userData['setting']['themeColor'] : 'blue',
            'account' => $this->Mdl_Setting->getSettingOnOff('siteAccount'),
            'siteLink' => $this->Mdl_Setting->getSettingStrValue('siteLink'),
            'siteBrand' => $this->Mdl_Setting->getSettingStrValue('siteBrand'),
            'isMultiArea' => $this->Mdl_Setting->getSettingOnOff('multiarea'),
            'version' => $this->config->item('version'),
            'lang' => $userData['setting']['language'],
            'epanelLink' => $this->config->item('epanel-link'),
            'epanelData' => json_encode($userData),
            'title' => 'sign system',
            'time' =>  date($this->config->item('dateTimeFormat'), now())
        ];

        $this->nanaengine->addToData($data);
        $this->nanaengine->setPage('epanel/layout');
        $this->nanaengine->parseToScreen();
    }
    /**
     * build the login form
     * showing a view
     */
    private function loginPage()
    {
        $this->load->model('Mdl_Setting');

        $data = [
            'formOpen' => form_open('epanel', ['id' => 'login-form']),
            'formclose' => form_close(),
            'version' => $this->config->item('version'),
            'lang' => $this->Mdl_Setting->getSettingStrValue('defaultLanguage')
        ];
        $this->nanaengine->addToData($data);
        $this->nanaengine->setPage('epanel/login');
        $this->nanaengine->parseToScreen();
    }

}