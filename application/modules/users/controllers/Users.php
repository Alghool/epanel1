<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class users extends MX_Controller {

    public function isUsernameNotExist(){
        $username = $this->input->get('username');
        $this->load->model('Mdl_User');
        if($this->Mdl_User->IsUsernameExist($username)){
            http_response_code (500);
            echo 1;
        }else{
            http_response_code (200);
            echo 1;
        }
    }

    public function isEmailNotExist(){
        $email = $this->input->get('email');
        $oldEmail = ($this->input->get('oldemail') == null)? "":$this->input->get('oldemail');

        if($oldEmail == $email) {
            http_response_code (200);
            return;
        }
        $this->load->model('Mdl_User');
        if($this->Mdl_User->IsEmailExist($email)){
            http_response_code (500);
            echo 1;
        }else{
            http_response_code (200);
            echo 1;
        }
    }

    public function isPasswordCorrect(){
        $this->load->model('Mdl_User');

        $password = $this->input->get('oldpassword');
        $username = $this->session->userdata['user']['username'];

        if($this->Mdl_User-> verifyUser($username, $password)){
            echo 'true';
        }else{
            echo 'false';
        }

    }
}