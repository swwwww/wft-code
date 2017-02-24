<?php
/**
 * Smarty plugin
 *
 */

function smarty_function_resource($params, $template) {
    $var_name = $params['var'];

    $resource_class = $params['resource'];

    $method = $params['method'];

    $call_arg = $params['arg'];

    if($call_arg){
        $result = Resource::instance($resource_class . 'Resource')->$method($call_arg);
    }else{
        $result = Resource::instance($resource_class . 'Resource')->$method();
    }

    $template->assign($var_name, $result);

    return '';
}


