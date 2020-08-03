<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class SessionEngine
{
    protected $CI;
    // session parameters///////
    private $sessionData = [
        'isSession' => true,
        'userInfo' => [
            'userID'  => 0,
            'name'  => '',
            'username'  => '',
            'pic' => '',
            'lang' => '',
            'role' => 0,
            'roleType' => 0
        ],
        'sessionKey' => '',
        'setting' => [],
        'epanel' => [],
        'extra' => []
    ];


    public function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();
    }
    /**
     * check for existing session or create new one
     * @return true|false
     * <p> if session not exist create new empty session</p>
     */
    private function IsSessionOrInit(){
        if(key_exists('isSession', $this->CI->session->userdata))
            return true;
        else
            $this->CI->session->set_userdata($this->sessionData);
    }
    /**
     * create a session with data
     * @param array session data with user info and session key
     * @return true|false
     * <p> if any old session will be removed</p>
     */
    public function initSession($data){
        if(isset($data['userInfo']) && is_array($data['userInfo']) && isset($data['sessionKey'])){

            foreach  ($data['userInfo'] as $key => $value){
                if(array_key_exists($key, $this->sessionData['userInfo']))
                    $this->sessionData['userInfo'][$key] = $value;
            }
            unset($data['userInfo']);
            $this->sessionData['sessionKey'] = $data['sessionKey'];
            unset($data['sessionKey']);
            foreach  ($data as $key => $value){
                if(array_key_exists($key, $this->sessionData)){
                    $this->sessionData[$key] = $value;
                }else{
                    $this->sessionData['extra'][$key] = $value;
                }
            }
            $this->CI->session->set_userdata($this->sessionData);
            return true;
        }else{
            return false;
        }
    }
    /**
     * get session Key
     * @return string session key
     * <p>if session not exist return null </p>
     */
    public function getSessionKey(){
        if (!isset($this->CI->session->userdata['sessionKey']))
            return null;
        return $this->CI->session->userdata['sessionKey'];
    }
    /**
     * get session ID
     * @return string PHP session ID
     * <p>if session not exist return null </p>
     */
    public function getSessionID(){
        return session_id();
    }

    /**
     * create a flash data that will be hold for only the next request
     * @param string data key
     * @param string data value
     * <p>if session not exist create new empty session</p>
     */
    public function addFlashData($key, $value){
        $this->IsSessionOrInit();
        $this->CI->session->set_userdata($key, $value);
        $this->CI->session->mark_as_flash($key);
    }
    /**
     * delete existing session
     */
    public function removeSession()
    {
        $this->CI->session->sess_destroy();
    }

    // extra data handler /////////////////////////////////////////////////////////////////////////////////////
    /**
     * get custom data from session data
     * @param string data key
     * @return null if not exist|data if exist
     * <p> if session not exist create new empty session</p>
     */
    public function getValue($valName){
        $this->IsSessionOrInit();
        if(key_exists($valName, $this->CI->session->userdata['extra']))
            return $this->CI->session->userdata['extra'][$valName];
        else
            return null;
    }
    /**
     * set custom data from session data
     * @param string data key
     * @param string data
     * <p> if session not exist create new empty session</p>
     */
    public function setValue($valName, $data){
        $this->IsSessionOrInit();
        $extra = $this->CI->session->userdata['extra'];
        $extra[$valName] = $data;
        $this->CI->session->set_userdata('extra', $extra);
    }

    /**
     * remove existing data key and value
     * @param string data key
     * <p> if session not exist create new empty session</p>
     */
    public function unsetValue($key){
        if (!isset($this->CI->session->userdata['isSession']))
            return;
        if(isset($this->CI->session->userdata['extra'][$key])){
            unset($this->CI->session->userdata['extra'][$key]);
            $this->CI->session->set_userdata('extra', $this->CI->session->userdata['extra']);
        }
    }
    // epanel handler /////////////////////////////////////////////////////////////////////////////////////
    /**
     * get epanel data array
     * @param string optional get specific epanel attribute
     * @return array epanel data array or empty array
     * <p> if session not exist returns null</p>
     */
    public function getEpanel($key = null)
    {
        if (!isset($this->CI->session->userdata['isSession']))
            return  null;

        return ($key && array_key_exists($key ,$this->CI->session->userdata['epanel']))?
            $this->CI->session->userdata['epanel'][$key] : $this->CI->session->userdata['epanel'];
    }
    /**
     * set epanel data array
     * @param array Epanel total array
     * @return true if success or false if no session intialized
     * <p>epanel array cant only be set as total not key by key</p>
     */
    public function setEpanel($epanelData)
    {
        if (!isset($this->CI->session->userdata['sessionKey']) && $this->CI->session->userdata['sessionKey'] != '')
            return false;
        if(isset($epanelData['permissions'])){
            $permissions = $epanelData['permissions'];
            $epanelData['permissions'] = [];
            foreach ($permissions as $permission){
                $epanelData['permissions'][$permission['id']] = $permission;
            }
            unset($permissions);
        }
        if(isset($epanelData['areas'])){
            $areas = $epanelData['areas'];
            $epanelData['areas'] = [];
            foreach ($areas as $area){
                $epanelData['areas'][$area['id']] = $area;
            }
        }
        $this->CI->session->set_userdata('epanel', $epanelData);
        return true;
    }
    // user info handler /////////////////////////////////////////////////////////////////////////////////////
    /**
     * get user info all data or single value
     * @param string optional user info key
     * @return array user info data array or user info value with input key
     * <p> if session not exist returns null</p>
     */
    public function getUserInfo ($key = null)
    {
        if (!isset($this->CI->session->userdata['isSession']))
            return  null;
        return ($key)?$this->CI->session->userdata['userInfo'][$key]:$this->CI->session->userdata['userInfo'] ;
    }
    /**
     * set user data array or single value
     * @param array of user info or key for single data
     * @param string in single data case tis is the data value
     * @return true if success or false if no session initialized
     * <p>user info cant only be set if session initialized</p>
     */
    public function setUserInfo($_userInfo, $value = null)
    {
        if (!isset($this->CI->session->userdata['sessionKey']) && $this->CI->session->userdata['sessionKey'] != '')
            return false;
        if(is_array($_userInfo)){
            $userInfo = $this->CI->session->userdata['userInfo'];
            foreach ($userInfo as $key => $value){
                if(isset($_userInfo[$key]))
                    $userInfo[$key] = $_userInfo[$key];

                $this->CI->session->set_userdata('userInfo', $userInfo);
                return true;
            }
        }else if ($value && isset($this->CI->session->userdata['userInfo'][$_userInfo])){
            $userInfo = $this->CI->session->userdata['userInfo'];
            $userInfo[$_userInfo] = $value;
            $this->CI->session->set_userdata('userInfo', $userInfo);
            return true;
        }else{
            return false;
        }
    }
    // setting handler /////////////////////////////////////////////////////////////////////////////////////
    /**
     * get setting data array
     * @param string optional setting key name
     * @return array setting data array or empty array
     * <p> if session not exist returns null</p>
     */
    public function getSetting($name = null)
    {
        if (!isset($this->CI->session->userdata['isSession']))
            return  null;
        return ($name)? $this->CI->session->userdata['setting'][$name]: $this->CI->session->userdata['setting'];
    }
    /**
     * set setting data array
     * @param array setting total array
     * @return true if success or false if no session intialized
     * <p>setting array cant only be set as total not key by key
     *  and can be used without login </p>
     */
    public function setSetting($settingData)
    {
        $this->IsSessionOrInit();
        $this->CI->session->set_userdata('setting', $settingData);
        return true;
    }
}