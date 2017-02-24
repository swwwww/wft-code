<?php
/**
 * Created by IntelliJ IDEA.
 * User: deyi
 * Date: 2016/8/23
 * Time: 10:06
 */

class DiscoverLib extends Manager{
    //发现首页列表
    public function discoverList($in=null)
    {
        $url = '/tag/index/list';
        return HttpUtil::setP($in, $url);
    }

    //专题列表
    public function topicList($in=null)
    {
        $url = '/topic/index';
        return HttpUtil::setP($in, $url);
    }

    //专题详情
    public function topicInfo($in=null)
    {
        $url = '/topic/index/info';
        return HttpUtil::setP($in, $url);
    }

    //游玩地列表
    public function playList($in=null)
    {
        $url = '/tag/index';
        return HttpUtil::setP($in,$url);
    }

    //游玩地详情
    public static function playDetail($in=null)
    {
        $url = '/place/index/newindex';
        return HttpUtil::setP($in,$url);
    }

    //评论点赞
    public function postLike($in=null)
    {
        $url = '/post/index/postlike';
        return HttpUtil::setP($in,$url);
    }

    //评论取消点赞
    public function postDel($in=null)
    {
        $url = '/post/index/removelike';
        return HttpUtil::setP($in,$url);
    }
}