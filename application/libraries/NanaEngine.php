<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class NanaEngine
{
    protected $CI;
    private $msg = array();
    private $data = array();
    private $page = '';
    private $hasPage = '';
    private $debug = array();
    private $success = true;
    private $responseCode = 200;

    public function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();
    }

    // MSG handler /////////////////////////////////////////////////////////////////////////////////////

    /**
     * add one more msg to be displayed
     * @param string type
     * @param string msg
     * @param array optional dynamic msg data
     * <p>new msg will not over ride other messages</p>
     */
    function addMsg($type, $msg, $data = array())
    {
        $this->msg[] = array(
            'type' => $type,
            'msg' => $msg,
            'data' => $data
        );
    }

    /**
     * get all messages
     * @return array of messages (type, msg)
     */
    function getMsg()
    {
        return $this->msg;
    }

    // page handler /////////////////////////////////////////////////////////////////////////////////////

    /**
     * set page relative path
     * @param string page relative path from view folder
     */
    function setPage($pagePath)
    {
        $this->hasPage = true;
        $this->page = $pagePath;
    }

    /**
     * get page relative path that will be displayed
     * @return string relative page path
     */
    function getPage()
    {
        return $this->page;
    }

    // data handler /////////////////////////////////////////////////////////////////////////////////////

    /**
     * assign data to dynamic page variable
     * @param array items key and value
     * @throws Exception of input is not array
     * <p>data will be added to exists data not over ride it</p>
     */
    function addToData($items)
    {
        if (is_array($items)) {
            $this->data = array_merge($this->data, $items);
        } else {
            throw new Exception('try to add non array items to NANA data Array');
        }
    }

    /**
     * get all assigned dynamic view variable
     * @return array of variable data key and value
     */
    function getData()
    {
        return $this->data;
    }

    /**
     * remove all assigned dynamic variable data
     */
    function resetData()
    {
        $this->data = array();
    }

    // success flag handler /////////////////////////////////////////////////////////////////////////////////////

    /**
     * assign success flag
     * @param bool optional default to success
     * <p>success flag will be used by client to determine if request handled successfully</p>
     */
    function setSuccess($success = true)
    {
        $this->success = $success;
    }

    // debug handler /////////////////////////////////////////////////////////////////////////////////////

    /**
     * add to debug array
     * @param array| string can add string or key/value array of debugging
     */
    function setDebug($items)
    {
        $debugItem = array(
            'items' => $items,
            'track' => debug_backtrace(2,2)
        );
        $this->debug[] = $debugItem;
    }

    // output handler /////////////////////////////////////////////////////////////////////////////////////

    /**
     * parse view page to viewport
     * @param string optional relative path to view page
     * @param array optional array of dynamic variable key and value
     * @param boolean optional success flag default to true
     * @throws Exception if no page path specified
     * <p>optional data will be felt with class properties can be set by setters </p>
     */
    function parseToScreen($page = '', $data = array(), $success = true)
    {
        if ($page) {
            $this->page = $page;
            $this->hasPage = true;
        } elseif (!$this->hasPage) {
            throw new Exception('no page specificated to parse to screen');
        }
        if ($data) {
            $this->data = $data;
        }

        $twiggyData = $this->data;

        $twiggyData['debugging'] = $this->debug;
        $twiggyData['msgs'] = json_encode($this->msg);
        $twiggyData['success'] = $success;
        $twiggyData['hasPage'] = $this->hasPage;

        $twiggy = $this->CI->twiggy;
        $twiggy->set($twiggyData);
        $twiggy->template($this->page)->display();
    }

    /**
     * parse view page to string
     * @param string optional relative path to view page
     * @param array optional array of dynamic variable key and value
     * @param boolean optional success flag default to true
     * @return array parsed page with all values
     * @throws Exception if no page path specified
     * <p>optional data will be felt with class properties can be set by setters </p>
     */
    function getParsedArray($page = '', $data = array(), $success = null)
    {
        if ($page) {
            $this->page = $page;
            $this->hasPage = true;
        }
        if ($data) {
            $this->data = $data;
        }
        $twiggyData = [
            'debugging' => $this->debug,
            'msgs' => $this->msg,
            'success' => ($success == null)? $this->success: $success,
            'hasPage' => $this->hasPage
        ];
        $twiggy = $this->CI->twiggy;
        $twiggy->set($this->data);
        $twiggyData['pageStr'] = ($this->hasPage)?$twiggy->render($this->page): '';
        return $twiggyData;
    }

    function returnAPI($data = array(), $success = null, $responseCode = 0) {
        if($data) {
            $this->data = $data;
        }
        $success = ($success == null)? $this->success: $success;
        $responseCode = ($responseCode == null)? $this->responseCode: $responseCode;
        $twiggyData = [
            'data' => $this->data,
            'debugging' => $this->debug,
            'msgs' => $this->msg
        ];

        header('Content-Type: application/json');
        if($success){
            http_response_code(200);
            echo json_encode($twiggyData);
        }else{
            $responseCode = ($responseCode == 200)? 400 : $responseCode;
            http_response_code($responseCode);
            echo json_encode($twiggyData);
        }
        die();
    }
}
