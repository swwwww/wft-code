<?php
/**
 * Smarty plugin
 *
 */

function smarty_modifier_text($string) {
    $result = htmlspecialchars($string, ENT_QUOTES);
    //$result = preg_replace('/\\n\\s*\\n/', '</p><p>', $string);
    $result = preg_replace('/\\n/', '</p><p>', $string);
    return $result;
}



