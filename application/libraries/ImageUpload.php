<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ImageUpload {
    protected $CI;

    public function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();
    }

    function multiUpload($imageArr = 'image', $title = 'def'){
        $images = array();
        for($i = 0; $i <count($_FILES['image']['name']); $i++) {
            if(!$_FILES['image']['name'][$i])
                continue;

            $_FILES['thisimage']=[
                "name" => $_FILES['image']['name'][$i],
                "type" => $_FILES['image']['type'][$i],
                "tmp_name" => $_FILES['image']['tmp_name'][$i],
                "error" => $_FILES['image']['error'][$i],
                "size"=> $_FILES['image']['size'][$i]
            ];
            $result = $this->uploadImage('thisimage', $title);
            //todo: handle errors, and notes that multi upload;
            if($result['success']){
                $images[] = $result['image'];
            }else{
                break;
            }
        }
        return $images;
    }

    function uploadImage($imageName,$title = 'def'){
        if(isset($_FILES[$imageName]) && is_uploaded_file($_FILES[$imageName]['tmp_name'])){
            $config['upload_path'] = './image/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $fileName = $title . '_' .date('YmdHis') .'_'.rand(0,99);
            $config['file_name'] = $fileName;
            $this->CI->load->library('upload', $config);

            if ( ! $this->CI->upload->do_upload($imageName)) {

                $result['success'] = false;
                $result['error'] = $this->CI->upload->display_errors();

                $result['msg']['type'] = 'error';
                $result['msg']['text'] = "تعذر رفع الصورة";
            }
            else
            {
                $result['success'] = true;
                $result['image'] = $this->CI->upload->data('file_name');
            }

        }else{
            $result['success'] = false;
            $result['error'] = 'nouploaded image';

            $result['msg']['type'] = 'error';
            $result['msg']['text'] = "نوع الوسائط لاتتناسب مع الوسائط المضافة";
        }
        return $result;
    }

    function uploadFile($_fileName, $title = 'def', $allowed_types = '*' ){
        if(isset($_FILES[$_fileName]) && is_uploaded_file($_FILES[$_fileName]['tmp_name'])){
            $config['upload_path'] = './image/';
            $config['allowed_types'] = $allowed_types;
            $fileName = $title . '_' .date('YmdHis') .'_'.rand(0,99);
            $config['file_name'] = $fileName;
            $this->CI->load->library('upload', $config);

            if ( ! $this->CI->upload->do_upload($_fileName)) {

                $result['success'] = false;
                $result['error'] = $this->CI->upload->display_errors();

                $result['msg']['type'] = 'error';
                $result['msg']['text'] = "تعذر رفع الملف";
            }
            else
            {
                $result['success'] = true;
                $result['image'] = $this->CI->upload->data('file_name');
            }
        }else{
            $result['success'] = false;
            $result['error'] = 'nouploaded image';

            $result['msg']['type'] = 'error';
            $result['msg']['text'] = "نوع الوسائط لاتتناسب مع الوسائط المضافة";
        }
        return $result;
    }
}