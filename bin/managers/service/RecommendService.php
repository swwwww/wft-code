<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/10/25
 * Time: 15:39
 */
class RecommendService extends Manager
{
    // 更新cookie中的搜索历史
    public function updateSearchHistory($data)
    {
        if (isset($_COOKIE['history'])){
            $source = unserialize(CookieUtil::get('history'));
            array_unshift($source, $data);
            $res = array();
            $i   = 0;
            foreach ($source as $v) {
                if (in_array($v, $res)) {
                    continue;
                }
                $res[] = $v;
                $i++;
                // 限制搜索历史记录为十条
                if ($i == 10) {
                    break;
                }
            }
            $res = serialize($res);
        }else{
            $res = serialize(array($data));
        }

        CookieUtil::set('history', $res);
    }
}