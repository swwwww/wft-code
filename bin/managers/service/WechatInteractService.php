<?php

/**
 * Class WechatInteractService
 * 微信交互相关功能
 * author: MEX | mixmore@yeah.net
 */
class WechatInteractService extends Manager
{
    public $app_id;
    public $secret;
    public $token;
    public $access_token;

    public function __construct($wechat = 'wechat')
    {
        $config = Yii::app()->params[$wechat];

        $this->app_id = $config['appid'];
        $this->secret = $config['secret'];
        $this->token = $config['token'];

        $service = new WechatConfigService($wechat);
        $this->access_token = $service->getAccessToken();
    }

    //根据用户open_id获取用户信息
    public function getUserInfoByUserOpenId($open_id)
    {
        LogUtil::info('[WechatInteractService](getUserInfoByUserToken)[start] $open_id:' . $open_id);

        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$this->access_token}&openid={$open_id}&lang=zh_CN";
        $proxy = new ProxyUtil('get', $url);
        $user_info = $proxy->run();

        LogUtil::info('[WechatInteractService](getUserInfoByUserToken)[end] $open_id:' . $open_id);

        return json_decode($user_info, true);
    }

    // 设置菜单
    public function setMenu($menuJson)
    {
        LogUtil::info('[WechatInteractService](setMenu)[start] menuJson:'.$menuJson);

        $proxy_util = new ProxyUtil('post', 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->access_token, $menuJson);
        $resp = $proxy_util->run();

        LogUtil::info('[WechatInteractService](setMenu)[end]');

        return json_decode($resp, true);
    }

    // 消息发送
    public function serviceMsg($respMsg)
    {
        LogUtil::info('[WechatInteractService](serviceMsg)[start] respMsg:' . $respMsg);

        $proxy_util = new ProxyUtil('post', 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $this->access_token, $respMsg);
        $resp = $proxy_util->run();

        LogUtil::info('[WechatInteractService](serviceMsg)[end]');

        return json_decode($resp, true);
    }

    /*************************** 用户管理 开始 *****************************/
    /**
     * 新建用户分组
     * 入参传入分组名称就好了
     *
     * @param $name
     *
     * @return mixed
     * author: MEX | mixmore@yeah.net
     */
    public function postUserGroup($name)
    {
        LogUtil::info('[WechatInteractService](serviceMsg)[start]');

        $data = array(
            'group' => array(
                'name' => $name
            )
        );
        $proxy_util = new ProxyUtil('post', "https://api.weixin.qq.com/cgi-bin/groups/create?access_token=" . $this->access_token, json_encode($data));

        LogUtil::info('[WechatInteractService](serviceMsg)[start]');

        return $proxy_util->run();
    }

    /**
     * 查询所有用户分组
     *
     * @return mixed
     * author: MEX | mixmore@yeah.net
     */
    public function getUserGroups()
    {
        LogUtil::info('[WechatInteractService](serviceMsg)[start]');

        $proxy_util = new ProxyUtil('post', "https://api.weixin.qq.com/cgi-bin/groups/get?access_token=" . $this->access_token);

        LogUtil::info('[WechatInteractService](serviceMsg)[start]');

        return $proxy_util->run();
    }


    /**
     * 传入用户open id
     * 获取用户所在分组
     *
     * @param $open_id
     *
     * @return mixed
     * author: MEX | mixmore@yeah.net
     */
    public function getUserGroup($open_id)
    {
        LogUtil::info('[WechatInteractService](getUserGroup)[start]');

        $data = array('open_id' => $open_id);
        $proxy_util = new ProxyUtil('post', "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=" . $this->access_token, json_encode($data));

        LogUtil::info('[WechatInteractService](getUserGroup)[end]');

        return $proxy_util->run();
    }

    /**
     * 更新分组名称
     *
     * @param $id
     * @param $name
     *
     * @return mixed
     * author: MEX | mixmore@yeah.net
     */
    public function updateGroupName($id, $name)
    {
        LogUtil::info('[WechatInteractService](updateGroupName)[start]');

        $data = array(
            'group' => array(
                'id'   => $id,
                'name' => $name
            ));
        $proxy_util = new ProxyUtil('post', "https://api.weixin.qq.com/cgi-bin/groups/update?access_token=" . $this->access_token, json_encode($data));

        LogUtil::info('[WechatInteractService](updateGroupName)[end]');

        return $proxy_util->run();
    }

    /**
     * @param $id
     *
     * 分组id
     *
     * @return mixed
     * author: MEX | mixmore@yeah.net
     */
    public function deleteGroup($id)
    {
        LogUtil::info('[WechatInteractService](deleteGroup)[start]');

        $data = array(
            'group' => array(
                'id' => $id
            ));
        $proxy_util = new ProxyUtil('post', "https://api.weixin.qq.com/cgi-bin/groups/delete?access_token=" . $this->access_token, json_encode($data));

        LogUtil::info('[WechatInteractService](deleteGroup)[end]');

        return $proxy_util->run();
    }
    /*************************** 用户管理 结束 *****************************/

    /*************************** 素材管理 开始 *****************************/

    /**
     * @param $type   素材类型 image/video/voice/news
     * @param $offset 素材偏移位置 0 就表示从第一个开始
     * @param $count  返回数量 1-20
     *
     * @return mixed {"errcode":40007,"errmsg":"invalid media_id"}
     * author: MEX | mixmore@yeah.net
     */
    public function getMaterials($type, $offset, $count)
    {
        LogUtil::info('[WechatInteractService](getMaterials)[start]');

        $data = array(
            'type'   => $type,
            'offset' => $offset,
            'count'  => $count,
        );
        $proxy_util = new ProxyUtil('post', "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=" . $this->access_token, $data);

        LogUtil::info('[WechatInteractService](getMaterials)[end]');

        return $proxy_util->run();
    }

    /**
     * @return mixed
     * {
     * "voice_count":COUNT,
     * "video_count":COUNT,
     * "image_count":COUNT,
     * "news_count":COUNT
     * }
     *
     * author: MEX | mixmore@yeah.net
     */
    public function getMaterislsTotalNumber()
    {
        LogUtil::info('[WechatInteractService](getMaterislsTotalNumber)[start]');

        $proxy_util = new ProxyUtil('post', "https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=" . $this->access_token);

        LogUtil::info('[WechatInteractService](getMaterislsTotalNumber)[end]');

        return $proxy_util->run();
    }
    /*************************** 素材管理 结束 *****************************/

    /*************************** 消息推送管理 开始 *****************************/


    /**
     * @param $data
     *
     *
     * {
     * "template_id_short":"TM00015"
     * }
     *
     * @return mixed
     * {
     * "errcode":0,
     * "errmsg":"ok",
     * "template_id":"Doclyl5uP7Aciu-qZ7mJNPtWkbkYnWBWVja26EGbNyk"
     * }
     * author: MEX | mixmore@yeah.net
     */
    public function getTemplate($data)
    {
        LogUtil::info('[WechatInteractService](getTemplate)[start] data:' . $data);

        $proxy_util = new ProxyUtil('post', "https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token=" . $this->access_token, $data);

        LogUtil::info('[WechatInteractService](getTemplate)[end]');

        return $proxy_util->run();
    }


    /**
     * @param $data
     *
     * @return mixed
     * author: MEX | mixmore@yeah.net
     */
    public function postTemplateMessage($data)
    {
        LogUtil::info('[WechatInteractService](postTemplateMessage)[start] data:' . $data);

        $proxy_util = new ProxyUtil('post', "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->access_token, $data);

        LogUtil::info('[WechatInteractService](postTemplateMessage)[end]');

        return $proxy_util->run();
    }

    /**
     * @param $data
    {
     * "touser":[
     * "OPENID1",
     * "OPENID2"
     * ],
     * "msgtype": "text",
     * "text": { "content": "hello from boxer."}
     * }
     *
     * @return mixed
     *
     * 正确返回
     * {
     * "errcode":0,
     * "errmsg":"send job submission success",
     * "msg_id":34182,
     * "msg_data_id": 206227730
     * }
     * author: MEX | mixmore@yeah.net
     */
    public function sendUsersMessage($data)
    {
        LogUtil::info('[WechatInteractService](sendUsersMessage)[start] data:' . $data);

        $proxy_util = new ProxyUtil('post', "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=" . $this->access_token, $data);

        LogUtil::info('[WechatInteractService](sendUsersMessage)[end]');

        return $proxy_util->run();
    }
    /*************************** 消息推送管理 开始 *****************************/


    /***************************玩翻天微信地推部分开始*****************************/

    /**
     * 生成带参数的永久二维码的ticket
     *
     * @param int $sceneId 渠道参数，如1 2 3 ...
     *
     * @return bool|string
     */
    public function getDituiApiTicket($sceneId)
    {
        LogUtil::info('[WechatInteractService](getDituiApiTicket)[start] sceneId:' . $sceneId);

        $ticket = CacheUtil::getX("weixin_ditui_ticket{$sceneId}");//从缓存中取ticket
        if (!$ticket) {//如果缓存中没有则生成
            $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$this->access_token";
            $qrcode = array(
                'action_name' => 'QR_LIMIT_SCENE',
                'action_info' => array(
                    'scene' => array(
                        'scene_id' => $sceneId
                    )
                )
            );
            $qrcode = json_encode($qrcode);
            $proxy_util = new ProxyUtil('post', $url, $qrcode);
            $res = $proxy_util->run();

            $ticket = json_decode($res)->ticket;
            CacheUtil::setX("weixin_ditui_ticket{$sceneId}", $ticket, 1);
        }

        LogUtil::info('[WechatInteractService](getDituiApiTicket)[end]');

        return $ticket;
    }

    /***************************玩翻天微信地推部分结束*****************************/
}