<?php
/**
 * Smarty plugin
 *
 */

function smarty_modifier_stringFix($source, $length = 30) {
    $result = StringUtil::getSubText($source, $length);

    return $result;
}
