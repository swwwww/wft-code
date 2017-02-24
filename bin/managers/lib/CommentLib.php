<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/10/8 0008
 * Time: 下午 2:43
 */

class CommentLib extends Manager
{
    //点赞
    public static function giveLike($in)
    {
        $url = '/post/index/postlike';
        return HttpUtil::setP($in, $url);
    }
    //取消点赞接口
    public static function removeLike($in)
    {
        $url = '/post/index/removelike';
        return HttpUtil::setP($in, $url);
    }

    //评论接口
    public static function commentReview($in)
    {
        $url = '/post/index/index';
        return HttpUtil::setP($in, $url);
    }

    //评论列表
    public static function commentLists($in)
    {
        $url = '/post/index/postlist';
        return HttpUtil::setP($in, $url);
    }

    //点评详情
    public static function commentDetail($in)
    {
        $url = '/post/index/info';
        return HttpUtil::setP($in, $url);
    }

    public static function uploadImg($in)
    {
        $url = '/social/sendpost/upimg';
        return HttpUtil::setP($in, $url, 'post', '10', '', true);
    }
}