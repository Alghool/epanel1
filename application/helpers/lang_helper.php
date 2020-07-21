<?php
//Lang library helper functions


function lang($str){
    $CI =& get_instance();
    if($CI->lang->line($str, FALSE))
        return  $CI->lang->line($str, FALSE);
    else{
        if($str != ''){
            $file = APPPATH .'/language/arabic/phrase_lang.php';
            $phrase = '$lang["'.$str.'"] = "'.$str.'";';
            file_put_contents($file, $phrase, FILE_APPEND | LOCK_EX);
            $file = APPPATH .'/language/english/phrase_lang.php';
            $phrase = '$lang["'.$str.'"] = "'.$str.'";';
            file_put_contents($file, $phrase, FILE_APPEND | LOCK_EX);
        }
        return $str;
    }
}