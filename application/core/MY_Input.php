<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Input extends CI_Input
{
    private function checkValidInputArray($_array){
        if(count((array) $_array) == 1){
            if(array_values($_array)[0] == '' && array_keys($_array)[0] != ''){
                return json_decode(array_keys($_array)[0],true);
            }
        }
    }

    public function input_stream($index = NULL, $xss_clean = NULL)
    {
        // Prior to PHP 5.6, the input stream can only be read once,
        // so we'll need to check if we have already done that first.
        if ( ! is_array($this->_input_stream))
        {
            // $this->raw_input_stream will trigger __get().
            $this->_input_stream = file_get_contents('php://input');
            if($this->_input_stream) $this->_input_stream = @json_decode($this->_input_stream, true);
            is_array($this->_input_stream) OR $this->_input_stream = array();

//            $this->_input_stream = $this->checkValidInputArray($this->_input_stream);

        }

        return $this->_fetch_from_array($this->_input_stream, $index, $xss_clean);
    }


}