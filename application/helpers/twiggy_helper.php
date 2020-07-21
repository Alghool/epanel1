<?php
//template engine helper functions
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

function varDump($variable = null){
    $CI =& get_instance();
    $twiggy = $CI->twiggy;
    print_r($variable);
    print_r($twiggy->$variable);
//    return $variable? json_encode($twiggy->$variable) :json_encode($twiggy->getAllVariables()) ;
}

function parseDate($date = null, $onlyDate = false){
    $CI =& get_instance();
    if($onlyDate == 1) {
        return ($date) ? date($CI->config->item('dateFormat'), $date) : date($CI->config->item('dateFormat'), now());
    }
    else if($onlyDate == 2){
        return ($date) ? date($CI->config->item('timeFormat'), $date) : date($CI->config->item('timeFormat'), now());
    }
    else{
        return ($date)? date($CI->config->item('dateTimeFormat'), $date): date($CI->config->item('dateTimeFormat'), now());
    }
}

function getDuration($time){
    $CI =& get_instance();
    $time = (float) $time;
    if($time < 120){
        return "<span class='time-duration'> $time  <lang>minutes</lang> </span>";
    }elseif ($time < ($CI->config->item('hoursInDay') * 60)){
        return "<span class='time-duration'> ". round($time / 60, 2)."  <lang>hours</lang> </span>";
    }else{
        return "<span class='time-duration'> ". round($time / (60 * $CI->config->item('hoursInDay')), 2)."   <lang>days</lang> </span>";
    }

}

function myRound($float, $operator = 'common', $precession = 0 ){

    switch($operator){
        case 'ceil':
            return ceil($float);
            break;
        case 'floor':
            return floor($float);
            break;
        default:
            return round($float, $precession);
            break;

    }
}

function getJsonCount($jsonStr){
    $arr = json_decode($jsonStr, true);
    return count((array)$arr);
}