<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class NotificationEngine
{
    protected $CI;

    public function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();
    }

    public function __get($class)
    {
        return $this->CI->$class;
    }

//todo: remove domain registers on role / user delete
    public function addToDomain($domainID, $notificationArr, $linktype = 'none', $link_id = 0, $link = 0){
        $this->load->model('Mdl_Notification');
        $domainID = (is_numeric($domainID))? $domainID : $this->Mdl_Notification->getDomainID($domainID);
        if(!$domainID) return false;
        $notificationArr = (is_array($notificationArr))? json_encode($notificationArr): json_encode(['text'=> $notificationArr]);

        $data = [
            'user_id' => $this->sessionengine->getUserInfo('userID'),
            'user_role' => $this->sessionengine->getUserInfo('role'),
            'text' => $notificationArr,
            'date' => now(),
            'new' => 1,
            'link' => $link,
            'link_type' => $linktype,
            'link_id' => $link_id,
            'domain' => $domainID,
            'notifi_user' => 0
        ];
        if($this->config->item('push_notification_Enable')){
            $users = $this->Mdl_Notification->getDomainUsers($domainID);
            $phrases = file_get_contents(FCPATH ."src/epanel/locales/ar/notifi.json");
            $phrases = json_decode($phrases, 1);
            $notificationArr = json_decode($notificationArr,true);
            $textKey = $notificationArr['text'];
            $notificationText = $phrases[$textKey];
            $notificationText = preg_replace_callback('/{{([^}}]+)}}/', function($matches) use (&$notificationArr) {
                return $notificationArr['data'][$matches[1]];
            }, $notificationText);
            foreach ($users as $user){
                $this->pushNotification($this->getUserTopic($user['user_id']), $notificationText);
            }
        }
        return $this->Mdl_Notification->addByData($data);
    }

    public function addToUser($userID, $notificationArr, $linktype = 'none', $link_id = 0, $link = 0){
        $this->load->model('Mdl_Notification');
        $notificationArr = (is_array($notificationArr))? json_encode($notificationArr): json_encode(['text'=> $notificationArr]);

        $data = [
            'user_id' => $this->sessionengine->getUserInfo('userID'),
            'user_role' => $this->sessionengine->getUserInfo('role'),
            'text' => $notificationArr,
            'date' => now(),
            'new' => 1,
            'link' => $link,
            'link_type' => $linktype,
            'link_id' => $link_id,
            'domain' => 0,
            'notifi_user' => $userID
        ];
        if($this->config->item('push_notification_Enable')){
            $phrases = file_get_contents(FCPATH ."src/epanel/locales/ar/notifi.json");
            $phrases = json_decode($phrases, 1);
            $notificationArr = json_decode($notificationArr,true);
            $textKey = $notificationArr['text'];
            $notificationText = $phrases[$textKey];
            $notificationText = preg_replace_callback('/{{([^}}]+)}}/', function($matches) use (&$notificationArr) {
                return $notificationArr['data'][$matches[1]];
            }, $notificationText);
            $this->pushNotification($this->getUserTopic($userID), $notificationText);
        }
        return $this->Mdl_Notification->addByData($data);
    }

    public function removeAllFromDomain($type, $typeID){
        $this->load->model('Mdl_Notification');
        $data = [
          'type' => $type,
          'type_id' => $typeID
        ];
        $this->Mdl_Notification->removefromRigter($data);
    }

    public function registerToDomain($type, $domain, $typeID){
        $this->load->model('Mdl_Notification');
        $data = [
          'type'=> $type,
          'domain' => $domain,
          'type_id' => $typeID
        ];
        return $this->Mdl_Notification->addToTable($data,'notifi_register');
    }

    public function getMyLastNotifications($lastCheck = 0){
        $this->load->model('Mdl_Notification');
        $userInfo = $this->sessionengine->getUserInfo();
        $this->nanaengine->setDebug(['user' => $userInfo, 'last' => $lastCheck]);
        return $this->Mdl_Notification->getLastNotification(
            $userInfo['userID'],
            $userInfo['role'],
            $userInfo['roleType'],
            $lastCheck,
            $this->sessionengine->getSetting('notificationCount')
        );
    }

    public function getActiveDomains(){
        $this->load->model('Mdl_Notification');
        return $this->Mdl_Notification->getActiveDomains();
    }

    public function getActiveDomainsWithUser($userID){
        $this->load->model('Mdl_Notification');
        return $this->Mdl_Notification->getActiveDomainsWithUser($userID);
    }

    public function getActiveDomainsWithRole($roleID){
        $this->load->model('Mdl_Notification');
        return $this->Mdl_Notification->getActiveDomainsWithRole($roleID);
    }

    private function pushNotification($user,$message){

        //API URL of FCM
        $url = $this->config->item('push_notification_url');
        $api_key = $this->config->item('push_notification_Key');

        $msg = array
        (
            'body'  => $message,
            'title'     => "Sign Systems",
            'vibrate'   => 1,
            'sound'     => 1,
        );

        $fields = array (
            'to'           => '/topics/'.$user,
            'notification' => $msg
        );

        //header includes Content type and api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$api_key
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    private function getUserTopic($userID){
        $this->load->module('users')->load->model('Mdl_User');
        $user = $this->Mdl_User->getUserName($userID);
        return  $user.'-'.$userID;
    }
}