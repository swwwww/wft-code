
<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     block.cacheChunk.php
 * Type:     block
 * Name:     cacheChunk
 * Purpose:  缓存指定区域
 * -------------------------------------------------------------
 */
function smarty_block_cacheChunk($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    if(YII_DEBUG){
        return $content;
    }

    $id = $params['id'];
    $expire = intval($params['expire']);
    if ($expire <= 0) {
        $expire = 60;
    }
    $expire = YII_DEBUG ? -1 : $expire;

    if ($repeat) {
        $content = Yii::app()->cache->get($id);
        if ($content) {
            $repeat = false;
            return $content;
        }
    } else {
        Yii::app()->cache->set($id, $content, $expire);
        return $content;
    }
}
