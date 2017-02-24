<?php

/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2016/8/18 0018
 * Time: 下午 7:06
 */
class CommentController extends Controller
{

    //点赞
    public function actionGiveLikeTo()
    {
        HttpUtil::out(CommentLib::giveLike($_POST)); //咨询
    }

    //取消点赞
    public function actionRemoveLikeTo()
    {
        HttpUtil::out(CommentLib::removeLike($_POST)); //咨询
    }

    public function actionGiveCommentTo()
    {
        HttpUtil::out(CommentLib::commentReview($_POST));
    }

    //评论列表
    public function actionCommentListAll()
    {
        $data  = $_GET;
        $in  = array(
            'object_id' => $data['coupon_id'],
//            'eid'       => $data['info_id'],
            'type'      => $data['type'],
            'buy_log'   => $data['buy_log']
        );
        $res_temp = CommentLib::commentLists($in);
        $res = $res_temp['data'];
        $res['param'] = $in;
        $res['id'] = $data['coupon_id'];
        $this->tpl->assign('res', $res);
        $this->tpl->display('comment/m/comment_list.html');
    }

    //评论列表
    public function actionGetCommentLists()
    {
        $data = $_POST;
        $res = CommentLib::commentLists($data);
        $res['type'] = $data['type'];
        HttpUtil::out($res);
    }

    //回复评论
    public function actionReComment()
    {
        //pid 回复id(必选)
        //uid 用户id(可选)
        //last_repid (可选)每页最后一条数据repid
        //pagenum 每页条数 默认10
        // 访问实例 ?id=183&type=3&pid=580ddf1c7f8b9aff498b472f
        $data  = $_GET;
        $param = array(
            'pid'        => $data['pid'],
            'type'       => $data['type'],
            'last_repid' => $data['id'],
        );

        $res_temp = CommentLib::commentDetail($param);
        $res_comment = $res_temp['data'];
        $data['uid'] = $res_comment['uid'];

        $res_comment['data']  = json_encode($data);  // add hidden data json
        $res_comment['score'] = round(($res_comment['score'] / 5) * 100) . '%'; // count score
        $res_comment['id'] =   $data['pid'];
        $this->tpl->assign('res', $res_comment);
        $this->tpl->display('comment/m/re_comment.html');
    }

    public function actionGetCommentDetail()
    {
        HttpUtil::out(CommentLib::commentDetail($_POST)); //咨询
    }

    //免责声明
    public function actionDisclaimer()
    {
        $this->tpl->display('comment/m/disclaimer.html');
    }

    //购买商品或活动才有资格评论
    public function actionComment()
    {
        $data = $_GET;
        $res = array(
            'type' => $data['type'],
            'coupon_id' => $data['coupon_id'],
            'order_sn' => $data['order_sn']
        );
        $this->tpl->assign('res', json_encode($res));
        $this->tpl->display('comment/m/comment.html');
    }

    // 评论上传图片
    public function actionUploadImg(){
        HttpUtil::out(CommentLib::uploadImg($_POST));
    }
}