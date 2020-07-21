<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class LogEngine
{
    //todo: add documentation to this engine
    //todo: add priority to logs
    protected $CI;

    public function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();
    }

    public function addLog($text, $link = '', $linkType = 0, $linkID = 0){
        //todo: add text data to use with localization
        $this->CI->load->model('Mdl_Log');
        $data = [
            'user_id' => $this->CI->sessionengine->getUserInfo('userID'),
            'text' => $text,
            'date' => now(),
            'link' => $link,
            'link_type' => $linkType,
            'link_id' => $linkID
        ];
        $this->CI->Mdl_Log->AddByData($data);
    }

    public function getTableLog($table, $itemID, $limit = 50){
        $this->CI->load->model('Mdl_Log');
        return $this->CI->Mdl_Log->getTableLog($table, $itemID, $limit);
    }

    public function removeTableLog($table, $itemID){
        $this->CI->load->model('Mdl_Log');
        return $this->CI->Mdl_Log->removeTable($table, $itemID);
    }

    public function changeTableLog($oldTable, $oldItemID, $newTable, $newItemID){
        $this->CI->load->model('Mdl_Log');
        return $this->CI->Mdl_Log->changeTable($oldTable, $oldItemID, $newTable, $newItemID);
    }
}