<?php
/**
 * Smarty plugin
 *
 */

function smarty_modifier_csrfToken($source) {
    $result = Yii::app()->request->getCsrfToken();

    return $result;
}
