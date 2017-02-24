<?php
/**
 * Smarty plugin
 *
 */

function smarty_modifier_imageFix($url, $type = 'main', $mark_name = 'view') {

    if(strstr($url, '/uploads/')){
        $result = 'http://www.wanfantian.com' . $url;
    }else{
        $result = $url;
    }


    return $result;
}
