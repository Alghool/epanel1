<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Page extends Epanel_Core {

    public function __construct()
    {
        parent::__construct();
    }

    public function home($parameters){
        $this->load->module('workers')->load->model('Mdl_Worker');
        $this->load->module('purchases')->load->model('Mdl_Envoy');
        $activeUser = $this->sessionengine->getUserInfo();


        $data =[];
        $isWarker = $this->Mdl_Worker->isUserWarker($activeUser['userID']);
        $isEnvoy = $this->Mdl_Envoy->isUserEnvoy($activeUser['userID']);
        if($isWarker){
            $this->load->module('workers')->load->model('Mdl_Task');
            $this->load->module('workers')->load->model('Mdl_Skill');
            $this->load->module('comments')->load->model('Mdl_Comment');
            $this->load->module('management')->load->model('Mdl_Element');
            $this->load->module('orders')->load->model('Mdl_Order');

            $data = [
                'skills' => $this->Mdl_Skill->getWorkerStat($isWarker),
                'rate' => $this->Mdl_Task->getWorkerRate($isWarker),
                'currentTask' => [],
                'nextTasks' => [],
                'oldTasks' => []
            ];

            $currentTask = $this->Mdl_Task->getCurrentTask($isWarker);
            if($currentTask){
                $currentTask['operation'] = json_decode($currentTask['operation_str'], true);
                $element = $this->Mdl_Element->getByID($currentTask['element'],'name');
                $currentTask['operation']['name'] =$currentTask['operation']['name'] .': '.$element['name'] ;
                $order= $this->Mdl_Order->getOrderLocation($currentTask['order']);
                $currentTask['status'] =  $currentTask['order'] .' - ' .$order['name'];
                $spentTime = $currentTask['duration'] - $currentTask['remaining'];
                $currentTask['running'] = false;
                if ($currentTask['start_time']){
                    $timer = now() - $currentTask['start_time'];
                    $timer = ceil($timer /60);
                    $spentTime += $timer;
                    $currentTask['working'] = 1;
                    $currentTask['running'] = true;
                }

                $timer = [
                    'h' => floor($spentTime /60),
                    'm' => (int) ($spentTime % 60),
                    't' => $spentTime
                ];
                $currentTask['timer'] = $timer;

                $data['currentTask'] = $currentTask;
            }

            $nextTasks = $this->Mdl_Task->getNextTask($isWarker);
            foreach ($nextTasks as $task){
                $task['operation'] = json_decode($task['operation_str'], true);
                $element = $this->Mdl_Element->getByID($task['element'],'name');
                $task['operation']['name'] =$task['operation']['name'].': '. $element['name'] ;
                $order = $this->Mdl_Order->getOrderLocation($task['order']);
                $task['status'] = $task['order'] .' - '. $order['name'];
                $task['dependOnOperation'] = $task['dependOnStr']? json_decode($task['dependOnStr'], true) : [];
                $data['nextTasks'][] = $task;
            }
            $oldTask =  $this->Mdl_Task->getOldTask($isWarker);
            foreach ($oldTask as $task){
                $task['operation'] = json_decode($task['operation_str'], true);
                $data['oldTasks'][] = $task;
            }


            $data['comments'] = $this->Mdl_Comment->getTableComments($currentTask['id'], 'tasks');
            $data['logs'] = $this->logengine->getTableLog('tasks', $currentTask['id']);

            $this->nanaengine->setPage('epanel/pages/worker-home');
        }
        elseif ($isEnvoy){
            $this->load->module('purchases')->load->model('Mdl_Purchase');
            $this->load->module('purchases')->load->model('Mdl_Supplier');

            $data['materials'] = $this->Mdl_Purchase->getEnvoyPurchases($isEnvoy);
            $data['suppliers'] = $this->Mdl_Supplier->getTable();

            $this->nanaengine->setPage('epanel/pages/purchases/envoy-home');
        }
        else if( $activeUser['roleType'] > 3){
            $this->load->module('orders')->load->model('Mdl_Order');
            $this->load->module('workers')->load->model('Mdl_Worker');
            $this->load->module('workers')->load->model('Mdl_Task');
            $this->load->module('storage')->load->model('Mdl_Material');
            $this->load->module('management')->load->model('Mdl_Item');
            $this->load->module('management')->load->model('Mdl_Component');
            $this->load->module('clients')->load->model('Mdl_Client');
            $this->load->module('clients')->load->model('Mdl_Contact');
            $this->load->module('users')->load->model('Mdl_User');

            $data['material'] = $this->Mdl_Material->getState();
            $data['worker'] = $this->Mdl_Worker->getState();
            $data['order'] = $this->Mdl_Order->getState();
            $data['stat'] = [
                'items' => $this->Mdl_Item->getTotal(),
                'components' => $this->Mdl_Component->getTotal(),
                'tasks' => $this->Mdl_Task->getTotal(),
                'users' => $this->Mdl_User->getTotal(),
                'clients' => $this->Mdl_Client->getTotal(),
                'contacts' => $this->Mdl_Contact->getTotal(),
            ];

            $this->nanaengine->setPage('epanel/panels/epanel-home');
        }

        $data['userName'] = $activeUser['name'];
        $this->nanaengine->addToData($data);
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
