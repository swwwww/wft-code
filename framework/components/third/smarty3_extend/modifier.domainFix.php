<?php
/**
 * Smarty plugin
 *
 */

function smarty_modifier_domainFix($name, $type = 'brand') {
    $h5 = $_SESSION['h5'];

    if(YII_DEBUG){
        $base_domain = 'localhost:9527/';
        $result = 'http://' . $base_domain;

        if($name != ''){
            if($type == 'brand'){
                //$result .= 'brand/' . $name . '/';
            }else if($type == 'user'){
                //$result .= 'talent/' . $name . '/';
            }
        }
    }else{
        $name = $name == '' ? 'www' : $name;
        $base_domain = '.wanfantian.com/';

        if($h5){
            if($name === 'www'){
                $result = 'http://www.m' . $base_domain;
            }else{
                $result = 'http://m.' . $name . $base_domain;
            }
        }else{
            $result = 'http://' . $name . $base_domain;
        }
    }

    return $result;
}
