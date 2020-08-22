<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Page extends Epanel_Core {

    public function __construct()
    {
        parent::__construct();
    }

    public function home($parameters){
        $this->nanaengine->setSuccess();
        RETURN true;
    }

    public function getEpanelSetting($parameters){
        $this->load->model('Mdl_Setting');

        $data = $this->Mdl_Setting->getEpanelSetting();
        $this->nanaengine->addToData($data);
        $this->nanaengine->setPage('epanel/panels/epanel-setting');
        $this->nanaengine->setSuccess();
        RETURN true;
    }

    public function setEpanelSetting($parameters){
        $this->load->model('Mdl_Setting');

        $maxLoginTry = (int)$this->input->post('maxLoginTry');
        $defaultLanguage = $this->input->post('defaultLanguage');
        $strictIPaddress = (int)$this->input->post('strictIPaddress');
        $breathTimer = (int)$this->input->post('breathTimer');

        $this->Mdl_Setting->updateEpanelSetting('defaultLanguage', 'strval', $defaultLanguage);
        $this->Mdl_Setting->updateEpanelSetting('maxLoginTry', 'intval', $maxLoginTry);
        $this->Mdl_Setting->updateEpanelSetting('strictIPaddress', 'intval', $strictIPaddress);
        $this->Mdl_Setting->updateEpanelSetting('breathTimer', 'intval', $breathTimer);

        $this->nanaengine->addMsg('success', 'successfullyUpdated');
        $this->nanaengine->setSuccess();
        return true;
    }

    public function showLog($parameters){
        $this->load->model('Mdl_Log');
        $data['logs'] = $this->Mdl_Log->getLatestLogs();

        $this->nanaengine->addToData($data);
        $this->nanaengine->setPage('epanel/panels/epanel-log');
        $this->nanaengine->setSuccess();
        RETURN true;
    }
}
