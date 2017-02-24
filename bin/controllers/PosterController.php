<?php

/**
 * Created by PhpStorm.
 *
 * 海报活动
 * 用户分享活动海报 有人点击之后获取活动积分
 * 记录积分领奖品
 *
 * 这个里面用的是微信相关操作的回调
 *
 *
 * User: MEX
 * Date: 16/7/8
 * Time: 10:08
 */
class PosterController extends Controller
{
    const bind_add_credits = 10;
    private $response_data;
    private $open_id;
    private $wechat_request_data;

    public function getWechatCallback()
    {
        // 参数
        $wechat_conf_id = !empty($_GET['acid']) ? intval($_GET['acid']) : 1;
        // TODO 做一个微信配置文件 字符串名称 和 int数字 对应的配置
        $this->$wechat_conf_id = $wechat_conf_id;

        //TODO 获取微信配置数据 对现在的框架体系而言好像不需要直接调用
        $wechat_service = new WechatService('poster');

        // 服务器通信验证
        // 然后 我不知道这个echostr是什么
        if ($wechat_service->getSignature()) {
            if (isset($_GET['echostr'])) {
                exit($_GET['echostr']);
            }
        }

        // 获取微信返回的数据
        $this->wechat_request_data = $wechat_service->getRequestObject();
        $this->open_id = $this->wechat_request_data->FromUserName;

        // TODO MsgType是不是我们自己定义的?这个不符合命名规则啊 好像是真的就是这个格式
        switch ($this->wechat_request_data->MsgType) {
            case 'event':
                // 将渠道推广数据写入数据库
                $event = $this->wechat_request_data->Event;
                $event_key = $this->wechat_request_data->EventKey;
                // 查询用户关注情况
                $concern_log = self::_getUserConcernLog($this->open_id);

                // 关注
                if ($event == 'subscribe') {
                    // 获取用户微信数据 比如昵称
                    // TODO 关注事件时才查询用户信息 这个数据在后面会用的到
                    $user_info = $this->weiXin->getOdinaryUserInfo($this);//通过open_id获取用户信息
                    if ($user_info && isset($user_info['nickname'])) {
                        $data['nick_name'] = $user_info['nickname'];//用户微信昵称
                    }

                    // TODO data里面的数据是用来掉接口和补全数据库内容用的

                    $data['concern_time'] = time();//用户最后一次关注时间
                    $data['is_on'] = 1;//当前为关注

                    // 渠道信息
                    $data['scene'] = self::_getChannelInfo($event_key);

                    // 判断用户是不是第一次关注 如果是第一次关注insert 如果不是第一次关注就 update
                    if (!$concern_log) {
                        //如果用户是第一次关注，将数据写入数据库
                        $data['open_id'] = $open_id;
                        $data['concern_num'] = 1;
                        //$this->_getWeixinDituiLogTable()->insert($data);
                    } else {
                        //如果用户不是第一次关注，更新关注次数
                        unset($data['weixin_name']);//不修改微信名称
                        $data['concern_num'] = $concern_log->concern_num + 1;
                        //$this->_getWeixinDituiLogTable()->update($data, $concern_log_where);
                    }
                    // 海报活动
                    // 先获取用户数据
                    $user = $this->_getWeixinUserTable()->get(['unionid' => $user_info['unionid'], 'acid' => $acid]);
                    if (!$user) {
                        // 在用户不存在的情况下 新建用户
//                        $data = [
//                            'acid' => $acid,
//                            'unionid' => $user_info['unionid'],//多平台下用户唯一标识id
//                            'openid' => $open_id,
//                            'nickname' => $user_info['nickname'],//用户微信昵称
//                            'headimgurl' => $user_info['headimgurl'],//用户头像
//                            'sex' => $user_info['sex'],//
//                            'city' => $user_info['city'],//
//                            'province' => $user_info['province'],//
//                            'subscribe' => 1,//关注了
//                            'subscribe_time' => time(),//用户最后一次关注时间
//                        ];
                        //创建用户
                        $uid = $this->_getWeixinUserTable()->insert($data);
                    } else {
                        // 如果用户存在就进行更新
//                        $this->_getWeixinUserTable()->update([
//                            'openid' => $open_id,
//                            'nickname' => mysql_escape_string($user_info['nickname']),
//                            'headimgurl' => $user_info['headimgurl'],//用户头像
//                            'sex' => $user_info['sex'],//
//                            'city' => $user_info['city'],//
//                            'province' => $user_info['province'],//
//                            'subscribe' => 1,
//                            'subscribe_time' => time()
//                        ], ['uid' => $user->uid]);

                        // 暂时不知道pid是干什么用的
                        if ($user->pid > 0) {//通过好友扫码关注
                            if ($user->is_bind == 0) {//首次关注，且未肯定好友
                                $user = $this->_getWeixinUserTable()->get(['unionid' => $user_info['unionid'], 'acid' => $acid]);
                                $p_user = $this->_getWeixinUserTable()->getUserByUid($acid, $user->pid);
                                $p_username = $p_user['nickname'];
                                if ($acid == 1) {
                                    if ($user->pid == $user->first) {
                                        //发提醒
                                        $data_str_1 = "恭喜您成为【" . $p_username . "】的支持者！！！";
                                        $json1 = '{"touser":"' . $user['openid'] . '","msgtype":"text","text":{"content":"' . htmlspecialchars_decode($data_str_1) . '"}}';
                                        $wechat_service->serviceMsg($json1);
                                        $data_str_2 = "恭喜你获得10个积分";
                                        $json2 = '{"touser":"' . $user['openid'] . '","msgtype":"text","text":{"content":"' . htmlspecialchars_decode($data_str_2) . '"}}';
                                        $wechat_service->serviceMsg($json2);

                                        $url = $this->domain . '/weixin/index/getapi?&acid=' . $acid;//接口地址
                                        $data_str_3 = "您的二维码海报已为您准备好啦~请点击<a href='" . $url . "'>【这里】</a>保存海报\n";
                                        $data_str_3 .= "将海报发送给好友加关注，你就会获得积分。凭积分可直接兑换奖品！";
                                        $json3 = '{"touser":"' . $user['openid'] . '","msgtype":"text","text":{"content":"' . htmlspecialchars_decode($data_str_3) . '"}}';
                                        $wechat_service->serviceMsg($json3);

                                        $data_str_p = "您的朋友[" . $user->nickname . "]成为了您的支持者！为您增加10个积分";
                                        $json = '{"touser":"' . $p_user['openid'] . '","msgtype":"text","text":{"content":"' . htmlspecialchars_decode($data_str_p) . '"}}';
                                        $wechat_service->serviceMsg($json);

                                        $pid = pcntl_fork();  //pid -1 失败 >0父  0子
                                        //父进程和子进程都会执行下面代码
                                        if ($pid === 0) {
                                            $this->haibaoClickLog($user);
                                            $this->responsePIC($user, $open_id, $acid);
                                            exit; //必须
                                        }
                                        $addCredit = $this->_getWeixinHaibaoCreditsLogTable()->get(array('uid' => $user->uid, 'source_uid' => $user->pid, 'dateline >= ?' => strtotime("2016-3-11 17:00:00")));
                                        //跟自己加积分
                                        $this->_getWeixinUserTable()->update(array('is_bind' => 1), array('uid' => $user->uid));
                                        if (!$addCredit) {//首次加分
                                            $this->_getWeixinUserTable()->update(array('credits' => new Expression('credits+' . static::BindAddCredits)), array('uid' => $user->uid));
                                            $this->_getWeixinHaibaoCreditsLogTable()->insert([
                                                'acid'       => $acid,
                                                'uid'        => $user->uid,
                                                'source_uid' => $user->pid,
                                                'credits'    => static::BindAddCredits,//增加积分
                                                'type'       => 0,
                                                'dateline'   => time()
                                            ]);
                                        }
                                        //跟别人加积分
                                        $this->_getWeixinUserTable()->update(array('credits' => new Expression('credits+' . static::BindAddCredits)), array('uid' => $user->pid));//奖品数量减1
                                        $this->_getWeixinHaibaoCreditsLogTable()->insert([
                                            'acid'       => $acid,
                                            'uid'        => $user->pid,
                                            'source_uid' => $user->uid,
                                            'credits'    => static::BindAddCredits,//增加积分
                                            'type'       => 0,
                                            'dateline'   => time()
                                        ]);
                                        echo '';

//                                    $data_str = "您已经通过[" . $p_username . "]成功关注，点击下方\n\n";
//                                    $data_str .= "[菜单--给ta加分]\n\n";
//                                    $data_str .= "为好友加分";
                                    } else {
                                        //发提醒
                                        $data_str_1 = "亲，您已经跟别的好友加过分了!\n";
                                        $data_str_1 .= "快点击菜单【生成海报】攒积分、兑好礼吧！";
                                        $json1 = '{"touser":"' . $user['openid'] . '","msgtype":"text","text":{"content":"' . htmlspecialchars_decode($data_str_1) . '"}}';
                                        $this->weiXin->serviceMsg($json1);
                                        echo '';
//                                        $pid = pcntl_fork();
//                                        if ($pid === 0) {
//                                            $this->responsePIC($user, $open_id, $acid);
//                                            exit; //必须
//                                        }
                                        exit;
                                    }
                                } else {
                                    $data_str = "您已经通过[" . $p_username . "]成功关注，点击下方\n\n";
                                    $data_str .= "[菜单--给ta加分]\n\n";
                                    $data_str .= "为好友加分";
                                }
                                $respMsg = [
                                    'type' => 'text',
                                    'data' => $data_str
                                ];
                                $responseData = $requestXml = $this->weiXin->responseMsg($this->weiXinRequestData, $respMsg);
                                echo $responseData;
                                exit;
                            } else {
                                //不存在已肯定好友状态（取消关注时，会取消肯定状态）
                            }
                        } else {
                            //pid==0，用户重新关注（且为自己关注）
                            //正常回复
                        }
                    }

                        //发送欢迎消息
                        $responseData = $this->eventRequest($acid);


                    } elseif ($event == 'unsubscribe') {
                    //取消关注事件
                    if ($concern_log) {
                        //关注后才能取消关注
                        unset($data['weixin_name']);//不修改微信名称
                        $data['is_on'] = 0;
                        $this->_getWeixinDituiLogTable()->update($data, $concern_log_where);
                    }
                    //  海报活动
                    $user_info = $this->weiXin->getOdinaryUserInfo($open_id);//通过open_id获取用户信息
                    $user = $this->_getWeixinUserTable()->get([
                        'unionid' => $user_info['unionid'], 'acid' => $acid]);
                    if ($user->pid > 0) {//有绑定用户
                        if ($user->is_bind == 1 and $user->pid = $user->first) {//肯定好友过//加过分
                            //解除肯定状态和关注状态
                            $this->_getWeixinUserTable()->update(['is_bind' => 0, 'subscribe' => 0], ['uid' => $user->uid]);
                            //给pid减分
                            $this->_getWeixinUserTable()->update(['credits' => new Expression('credits-' . static::BindAddCredits)], ['uid' => $user->pid]);
                            //减分log插入，type=0为好友类型，1为商品
                            $this->_getWeixinHaibaoCreditsLogTable()->insert([
                                'acid' => $acid,
                                'uid' => $user->pid,
                                'source_uid' => $user->uid,
                                'credits' => '-' . static::BindAddCredits,//扣除积分
                                'type' => 0,
                                'dateline' => time()
                            ]);

                            //给pid发消息提醒
                            $p_user = $this->_getWeixinUserTable()->getUserByUid($acid, $user->pid);
                            $p_openid = $p_user['openid'];
                            $data_str = "非常抱歉,您的积分被扣除" . static::BindAddCredits . "分。\n";
                            $data_str .= "原因:您的好友[" . $user->nickname . "]取消关注。\n";
                            $json = '{"touser":"' . $p_openid . '","msgtype":"text","text":{"content":"' . htmlspecialchars_decode($data_str) . '"}}';
                            $res = $this->weiXin->serviceMsg($json);
                            exit;
                        } else {
                            //关注状态
                            $this->_getWeixinUserTable()->update(['is_bind' => 0, 'subscribe' => 0], ['uid' => $user->uid]);
                            exit;
                        }
                    } else {
                        //无绑定用户，不做更新
                        //关注状态
                        $this->_getWeixinUserTable()->update(['is_bind' => 0, 'subscribe' => 0], ['uid' => $user->uid]);
                        exit;
                    }


                    $responseData = '';
                } elseif ($event == 'CLICK') { //todo  点击事件
                    /**
                     * 用户取消关注时需要把其上级以及上上级的关联加分减掉
                     */
                    //通过自定义菜单点击事件
                    //查找$event_key对应的自定义回复内容（type以及content）
                    if ($event_key) {
                        if ($event_key == "生成海报") {
                            //发送海报图
                            //查找该用户对应的图片，并判断是否首次获取
                            $user_info = $this->weiXin->getOdinaryUserInfo($open_id);//通过open_id获取用户信息

                            //判断第几次请求
                            $r = RedCache::get('T:user:' . $open_id);
                            if ($r) {
                                echo '';
                                exit;
                            } else {
                                RedCache::set('T:user:' . $open_id, '1', 7);
                            }


                            if (!$user_info['unionid']) {
                                WriteLog::WriteLog("未获取到unionidid", true);
                            }
                            $user = $this->_getWeixinUserTable()->get([
                                'unionid' => $user_info['unionid'], 'acid' => $acid]);
                            if (!$user) {//游戏上线前已经关注的用户，则创建该海报用户
                                $data = [
                                    'acid' => $acid,
                                    'unionid' => $user_info['unionid'],//多平台下用户唯一标识id
                                    'openid' => $open_id,
                                    'nickname' => $user_info['nickname'],//用户微信昵称
                                    'headimgurl' => $user_info['headimgurl'],//用户头像
                                    'sex' => $user_info['sex'],//
                                    'city' => $user_info['city'],//
                                    'province' => $user_info['province'],//
                                    'subscribe' => 1,//关注了
                                    'subscribe_time' => time(),//用户最后一次关注时间
                                ];
                                //创建用户
                                $uid = $this->_getWeixinUserTable()->insert($data);
                                $user = $this->_getWeixinUserTable()->get([
                                    'unionid' => $user_info['unionid'], 'acid' => $acid]);
                            } elseif (empty($user->openid)) {
                                $this->_getWeixinUserTable()->update([
                                    'openid' => $open_id,
                                    'nickname' => $user_info['nickname'],
                                    'headimgurl' => $user_info['headimgurl'],//用户头像
                                    'sex' => $user_info['sex'],//
                                    'city' => $user_info['city'],//
                                    'province' => $user_info['province'],//
                                    'pid' => 0,
                                    'subscribe' => 1,
                                    'subscribe_time' => time()
                                ], ['uid' => $user->uid]);
                                $user->pid = 0;
                            }

                            if ($user->pid != 0) {//扫码关注进来的用户
                                $this->responseByIP($open_id, $user, $user->ip_check, 1, $acid, 1);
                                exit;
                            } else {//自己进入的用户
                                if ($user_info) {
                                    $this->responseByIP($open_id, $user, $user->ip_check, 1, $acid, 1);
                                    exit;
                                }

                            }

                        } elseif ($event_key == "给ta加分") {
                            $user_info = $this->weiXin->getOdinaryUserInfo($open_id);//通过open_id获取用户信息
                            $user = $this->_getWeixinUserTable()->get([
                                'unionid' => $user_info['unionid'], 'acid' => $acid]);

                            if (!$user) {
                                $data = [
                                    'acid' => $acid,
                                    'unionid' => $user_info['unionid'],//多平台下用户唯一标识id
                                    'openid' => $open_id,
                                    'nickname' => $user_info['nickname'],//用户微信昵称
                                    'headimgurl' => $user_info['headimgurl'],//用户头像
                                    'sex' => $user_info['sex'],//
                                    'city' => $user_info['city'],//
                                    'province' => $user_info['province'],//
                                    'subscribe' => 1,//关注了
                                    'subscribe_time' => time(),//用户最后一次关注时间
                                ];
                                //创建用户
                                $uid = $this->_getWeixinUserTable()->insert($data);
                                $user = $this->_getWeixinUserTable()->get([
                                    'unionid' => $user_info['unionid'], 'acid' => $acid]);
                            } elseif (empty($user->openid)) {
                                $this->_getWeixinUserTable()->update([
                                    'openid' => $open_id,
                                    'nickname' => $user_info['nickname'],
                                    'headimgurl' => $user_info['headimgurl'],//用户头像
                                    'sex' => $user_info['sex'],//
                                    'city' => $user_info['city'],//
                                    'province' => $user_info['province'],//
                                    'pid' => 0,
                                    'subscribe' => 1,
                                    'subscribe_time' => time()
                                ], ['uid' => $user->uid]);
                                $user->pid = 0;
                            }
                            //判断ip
                            $result = $this->responseByIP($open_id, $user, $user->ip_check, 2, $acid, 2);
                            if ($result) {//验证ip成功
                                if ($user->pid > 0) {
                                    //判断用户关联账号是否绑定
                                    if ($user->is_bind == 0) {
                                        $this->_getWeixinUserTable()->update(['is_bind' => 1], ['uid' => $user->uid]);
                                        $this->_getWeixinUserTable()->update(['credits' => new Expression('credits+' . static::BindAddCredits)], ['uid' => $user->pid]);

                                        //加分log插入，type=0为好友类型，1为商品
                                        $this->_getWeixinHaibaoCreditsLogTable()->insert([
                                            'acid' => $acid,
                                            'uid' => $user->pid,
                                            'source_uid' => $user->uid,
                                            'credits' => static::BindAddCredits,//增加积分
                                            'type' => 0,
                                            'dateline' => time()
                                        ]);
                                        $p_user = $this->_getWeixinUserTable()->getUserByUid($acid, $user->pid);
                                        //  提醒分享用户
                                        $data_str_p = "您的朋友[" . $user->nickname . "]成为了您的支持者！您已获得了相应的积分奖励(+" . static::BindAddCredits . "),请注意查收";
                                        $json = '{"touser":"' . $p_user['openid'] . '","msgtype":"text","text":{"content":"' . htmlspecialchars_decode($data_str_p) . '"}}';
                                        $res = $this->weiXin->serviceMsg($json);
                                        //  提醒扫码关注用户
                                        $p_username = $p_user['nickname'];
                                        $data_str = "为好友[" . $p_username . "]加分成功~";
                                        if ($acid == 2) {
                                            $data_str .= "点[生成海报]，将海报发给2个朋友关注，立即获得1元红包！";
                                        }
                                        $respMsg = [
                                            'type' => 'text',
                                            'data' => $data_str
                                        ];
                                        $requestResult = $requestXml = $this->weiXin->responseMsg($this->weiXinRequestData, $respMsg);
                                        echo $requestResult;
                                        exit;
                                    } else {//已经肯定
                                        $data_str = "您已经为好友加分过~";
                                        $respMsg = [
                                            'type' => 'text',
                                            'data' => $data_str
                                        ];
                                        $requestResult = $requestXml = $this->weiXin->responseMsg($this->weiXinRequestData, $respMsg);
                                        echo $requestResult;
                                        exit;
                                    }
                                } else {
                                    $data_str = "您不是通过好友关注的，不需要给ta加分噢~\n\n";
                                    $data_str .= "点击下方\n\n";
                                    $data_str .= "[菜单--生成海报]\n\n";
                                    $data_str .= "马上参加本次活动，万元好礼等你领！~";
                                    $respMsg = [
                                        'type' => 'text',
                                        'data' => $data_str
                                    ];
                                    $requestResult = $requestXml = $this->weiXin->responseMsg($this->weiXinRequestData, $respMsg);
                                    echo $requestResult;
                                    exit;
                                }
                            }
                        } else {
                            //正常流程
                            $open_id = $this->weiXinRequestData->FromUserName;//用户的open_id，是加密后的用户微信信息
                            $responseData = $this->MenuMessageRequest($acid, $open_id);
                        }

                    } else {
                        $responseData = '';
                    }
                    //点击菜单按钮推送给用户的消息
                    //$responseData = $this->MenuMessageRequest();

                } else {
                    $responseData = '';
                }

                break;
            case 'text'://接收普通消息
                $open_id = $this->weiXinRequestData->FromUserName;//用户的open_id，是加密后的用户微信信息
                $responseData = $this->messageRequest($acid, $open_id);
                break;
            case 'image'://接收图片消息
                $responseData = $this->messageRequest($acid);
                break;
            default:
                $responseData = '';
                break;
        }

        echo $responseData;
        exit;
    }

    private function _getUserConcernLog($open_id)
    {
        $data = array();
        $data['wechat_name'] = '玩翻天';
        $concern_log_where = array(
            'open_id'     => $open_id,
            'wechat_name' => $data['wechat_name']
        );
        // TODO 获取用户关注情况数据 这里需要service层提供接口
    }

    private function _getChannelInfo($event_key)
    {
        $res = null;
        $scene_tmp = explode('_', $event_key);
        if (count($scene_tmp) >= 2) {
            if ($scene_tmp[1] >= 1 && $scene_tmp[1] <= 40) {//渠道编号1-40
                $scene_id = $scene_tmp[1];
                $res = 'WF-' . $scene_id;//渠道编号
            }
        }
        return $res;
    }

    /**
     * 记录海报生成log
     * type = 0
     * edit by kylin
     * 2016-01-18
     **/
    private function haibaoClickLog($user)
    {
        if ($user) {
            $result = $this->_getWeixinHaibaoClickLogTable()->insert(['uid' => $user->uid, 'type' => 0, 'dateline' => time()]);
            return true;
        }
        return false;
    }


    //todo 生成海报
    private function haibao($uid = 121266, $avatarUrl, $acid = 1)
    {
        $avatarImg = null;
        $backgroundImg = null;
        $avatarUrl = preg_replace('/(.*)\/{1}([^\/]*)/i', '$1', $avatarUrl) . '/64';
        $code = $acid . "_" . $uid;

        $url = $this->domain . '/weixin/index/Link?code=' . $code;


        $avatarPath = __DIR__ . '/../../../public/demo/upload/avatar/' . sprintf('%02d', substr($uid, -2));

        if (!is_dir($avatarPath)) {
            mkdir($avatarPath, 0777, true);
        }

        $qrcodeUrl = $avatarPath . '/code.png';

        QrCode::png($url, 9.5, 0, $qrcodeUrl); //320

        $res[] = Request::get($avatarUrl);  //获取用户头像 资源
        $res[] = file_get_contents($qrcodeUrl);  //获取二维码资源


        if ($res) {
            foreach ($res as $key => $val) {

                $avatarImg[$key] = imagecreatefromstring($val);
                imagesavealpha($avatarImg[$key], true);
                $avatar_w[$key] = imagesx($avatarImg[$key]);
                $avatar_h[$key] = imagesy($avatarImg[$key]);
                if ($key == 0) {
                    $avatarFile[$key] = $avatarPath . sprintf('/%d.png', $uid);
                } else {//二维码
                    $avatarFile[$key] = $avatarPath . sprintf('/qrcode%d.png', $uid);
                }
                imagepng($avatarImg[$key], $avatarFile[$key], 10);
            }

            if ($acid == 1) {//武汉
                $backgroundFile = __DIR__ . '/../../../public/demo/img/wh.png';//背景图
            } elseif ($acid == 2) {//南京
                $backgroundFile = __DIR__ . '/../../../public/demo/img/nj4.png';//背景图
            }

            $backgroundImg = imagecreatefrompng($backgroundFile);
            imagesavealpha($backgroundImg, true);

            foreach ($avatarImg as $key => $val) {
                if ($key == 0) {
                    if ($acid == 1) {
                        $x = 68;  //ok
                        $y = 519;
                    } elseif ($acid == 2) {
//                        $src_info = getimagesize($avatarUrl);
//                        $height = $src_info[1];
                        $x = 111;
                        $y = 717;
                    } else {
                        $x = 107;
                        $y = 492;
                    }

                } else {
                    if ($acid == 1) {
                        $x = 182;
                        $y = 638;
                    } elseif ($acid == 2) {
                        $x = 212;
                        $y = 710;
                    } else {
                        $x = 222;
                        $y = 604;
                    }

                }

                imagecopyresampled(
                    $backgroundImg,
                    $avatarImg[$key],
                    $x,//x轴坐标
                    $y,//y轴坐标
                    0,
                    0,
                    $avatar_w[$key],
                    $avatar_h[$key],
                    $avatar_w[$key],
                    $avatar_h[$key]
                );
            }

            $haibaoFile = $avatarPath . sprintf('/%d_haibao.jpg', $uid);

            // 生成.jpg格式海报图
            imagejpeg($backgroundImg, $haibaoFile, 100);
            $sum = count($img);
            //防止内存泄露
            for ($i = 0; $i < $sum; $i++) {
                //删除下载头像以及二维码图片
                unlink($avatarFile[$i]);
                if (is_resource($avatarImg[$i])) {
                    imagedestroy($avatarImg[$i]);
                }
                if (is_resource($backgroundImg[$i])) {
                    imagedestroy($backgroundImg[$i]);
                }
            }


            //海报相对路径
            $haibao_file = '/demo/upload/avatar/' . sprintf('%02d', substr($uid, -2)) . '/' . $uid . '_haibao.jpg';
            //更新数据库
//            $this->_getWeixinUserTable()->update(['media_file' => $haibao_file, 'subscribe' => 1], ['uid' => $uid]);
            $this->_getAdapter()->getDriver()->getConnection()->disconnect();
            $db = $this->_getAdapter();
            $db->query('update weixin_user set media_file=?,subscribe=? WHERE  uid=?;', array($haibao_file, 1, $uid))->count();

            return $haibao_file;
        }
        return false;

    }


    public function RedEnvelopeAction()
    {
        $price = $this->getQuery('price');
        $open_id = $this->getQuery('open_id');
        $token = $this->getQuery('token');
        if (!$price || !$open_id || !$token) {
            exit(json_encode(['status' => -1, 'message' => '参数缺失']));
        }
        if ($token != '6BUvDMwIU72M') {
            exit(json_encode(['status' => -2, 'message' => '参数错误']));
        }
        $hb = new WXHongBao();
        $rmb = bcmul($price, 100, 0);
//      $rmb = 100;// 测试
        $hb->newhb($open_id, $rmb); //新建一个10元的红包，第二参数单位是 分，注意取值范围 1-200元
        //以下若干项可选操作，不指定则使用class脚本顶部的预设值
        $hb->setNickName("玩翻天");
        $hb->setSendName("玩翻天App");
        $hb->setWishing("新的一年，祝您与孩子一起快乐玩翻天！");
        $hb->setActName("玩翻天新年红包活动");
        $hb->setRemark("玩翻天，武汉最大的亲子App！遛娃资讯、票券一网打尽！");

        //发送红包
        if (!$hb->send()) { //发送错误
//            setcookie('err',$hb->err(),time()+500);
//            return $this->_Goto('兑换失败'.$hb->err());
            exit(json_encode(['status' => -3, 'message' => $hb->err()]));
        } else {
            exit(json_encode(['status' => 1, 'message' => '兑换成功']));
        }
    }

    public function wftbabyAction()
    {
        $openid = $_GET['openid'];
        $this->weiXinConfig = $this->getwxConfig()[1];
        $this->weiXin = new WeiXinFun($this->weiXinConfig);
        $user_info = $this->weiXin->getOdinaryUserInfo($openid);//通过open_id获取用户信息
        if ($user_info) {
            $goUrl = "http://weixin.deyi.com/authodata?p=" . bin2hex(base64_encode(json_encode($user_info)));
            header('Location:' . $goUrl);
        }
        exit;
    }

    // 参数解释
    // $string： 明文 或 密文
    // $operation：DECODE表示解密,其它表示加密
    // $key： 密匙
    // $expiry：密文有效期
    private function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
        $ckey_length = 4;

        // 密匙
        $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);

        // 密匙a会参与加解密
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
            substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
            sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            // substr($result, 0, 10) == 0 验证数据有效性
            // substr($result, 0, 10) - time() > 0 验证数据有效性
            // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
            // 验证数据有效性，请看未加密明文的格式
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
                substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)
            ) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    //自定义菜单事件
    private function MenuMessageRequest($acid, $openid)
    {
        $acid = $this->acid;
        // 此处之后添加一个key关联自定义回复keyword的表，前台click事件需添加一个关联自定义回复的字段
        //从微信服务器传过来的消息ID
        $content = $this->weiXinRequestData->EventKey;
        //完全匹配
        $keylog = $this->_getWeiXinReplyKeyword()->get(['acid' => $acid, 'keyword' => $content]);

        //如果完全匹配没有结果则进行模糊匹配
        if (!$keylog) {
            $kWhere = ['acid' => $acid, 'match_all' => 0];//匹配条件
            $kOrder = ['id' => 'desc'];//搜索排序

            //消息内容分割成数组，分隔符(回车、空格、换行、+、-、_、|、*)
            $msgArr = preg_split('/[\n|\r|\s|\-|+|_|\\||\\*]+/i', $content);
            $msgArrNew = [];
            foreach ($msgArr as $mg) {
                if ($mg) {
                    $msgArrNew[] = $mg;//过滤空字符串
                }
            }

            $msgCount = count($msgArrNew);
            if ($msgCount > 1) {
                $sqlVal = [];
                foreach ($msgArrNew as $m) {
                    $sqlVal[] = '%' . $m . '%';
                }
            } elseif ($msgCount == 1) {
                $sqlVal = '%' . $msgArrNew[0] . '%';
            } else {
                $sqlVal = '';
            }

            $sqlKey = 'keyword like ?';
            for ($i = 1; $i < $msgCount; $i++) {
                $sqlKey .= ' or keyword like ?';
            }
            if ($msgCount > 1) {
                $sqlKey = '(' . $sqlKey . ')';
            }

            $kWhere[$sqlKey] = $sqlVal;
            $keylog = $this->_getWeiXinReplyKeyword()->fetchLimit(0, 1, ['id', 'content_id'], $kWhere, $kOrder)->current();
        }

        if ($keylog) {
            //根据关键字对应的回复id查找回复
            $requestData = $this->_getWeiXinReplyContent()->fetchLimit(0, 1, [], ['id' => $keylog->content_id, 'acid' => $acid])->toArray();
            if (count($requestData)) {
                if ($requestData[0]['type'] == 'news') {
                    // news可以添加多条数据，二维数组
                    if ($requestData[0]['to_url'] == 'http://wx.wanfantian.com/weixin/index/wftbaby') {
                        $requestData[0]['to_url'] .= "?openid=" . $openid;
                    }
                    // news可以添加多条数据，二维数组
                    $respMsg = [
                        'type' => 'news',
                        'data' => $requestData
                    ];
                } else {
                    $respMsg = [
                        'type' => 'text',
                        'data' => $requestData[0]['message']
                    ];

                }
            } else {
                if ($acid == 1) {
                    $respMsg = [
                        'type' => 'text',
                        'data' => '如未及时回复，请添加玩翻天小花微信（wanfantian2）咨询。'
                    ];
                } elseif ($acid == 2) {
                    $respMsg = [
                        'type' => 'text',
                        'data' => '亲，你的信息已收到~ 活动具体请添加玩翻天-福利君微信（Wanfantiannanjing)咨询。'
                    ];
                } else {
                    $respMsg = [
                        'type' => 'text',
                        'data' => '如未及时回复，请添加玩翻天小花微信（wanfantian2）咨询。'
                    ];
                }
            }

        } else {
            if ($acid == 1) {
                $respMsg = [
                    'type' => 'text',
                    'data' => '如未及时回复，请添加玩翻天小花微信（wanfantian2）咨询。'
                ];
            } elseif ($acid == 2) {
                $respMsg = [
                    'type' => 'text',
                    'data' => '亲，你的信息已收到~ 活动具体请添加玩翻天-福利君微信（Wanfantiannanjing)咨询。'
                ];
            } else {
                $respMsg = [
                    'type' => 'text',
                    'data' => '如未及时回复，请添加玩翻天小花微信（wanfantian2）咨询。'
                ];
            }
        }
        $requestXml = $this->weiXin->responseMsg($this->weiXinRequestData, $respMsg);
        return $requestXml;

    }


    //消息请求
    private function messageRequest($acid, $openid)
    {
        $acid = $this->acid;
        //从微信服务器传过来的消息
        $content = $this->weiXinRequestData->Content;

        //完全匹配
        $keylog = $this->_getWeiXinReplyKeyword()->get(['acid' => $acid, 'keyword' => $content]);

        //如果完全匹配没有结果则进行模糊匹配
        if (!$keylog) {
            $kWhere = ['acid' => $acid, 'match_all' => 0];//匹配条件
            $keylog = false;
            $keylogs = $this->_getWeiXinReplyKeyword()->fetchLimit(0, 2000, ['id', 'content_id', 'keyword'], $kWhere, ['id' => 'desc']);


            //输入 我要领取   关键字 领取

            foreach ($keylogs as $v) {
                if (stripos($content, $v->keyword) !== false) {
                    $keylog = $v;
                    break;
                }
            }

        }

        if ($keylog) {
            //根据关键字对应的回复id查找回复
            $requestData = $this->_getWeiXinReplyContent()->fetchLimit(0, 1, [], ['id' => $keylog->content_id, 'acid' => $acid])->toArray();
            if (count($requestData)) {
                if ($requestData[0]['type'] == 'news') {
                    // news可以添加多条数据，二维数组
                    if ($requestData[0]['to_url'] == 'http://wx.wanfantian.com/weixin/index/wftbaby') {
                        $requestData[0]['to_url'] .= "?openid=" . $openid;
                    }
                    $respMsg = [
                        'type' => 'news',
                        'data' => $requestData
                    ];
                } else {
                    $respMsg = [
                        'type' => 'text',
                        'data' => $requestData[0]['message']
                    ];
                }
            } else {
                if ($acid == 1) {
                    $respMsg = [
                        'type' => 'text',
                        'data' => '如未及时回复，请添加玩翻天小花微信（wanfantian2）咨询。'
                    ];
                } elseif ($acid == 2) {
                    $respMsg = [
                        'type' => 'text',
                        'data' => '亲，你的信息已收到~ 活动具体请添加玩翻天-福利君微信（Wanfantiannanjing)咨询。'
                    ];
                } else {
                    $respMsg = [
                        'type' => 'text',
                        'data' => '如未及时回复，请添加玩翻天小花微信（wanfantian2）咨询。'
                    ];
                }
            }

        } else {
            if ($acid == 1) {
                $respMsg = [
                    'type' => 'text',
                    'data' => '如未及时回复，请添加玩翻天小花微信（wanfantian2）咨询。'
                ];
            } elseif ($acid == 2) {
                $respMsg = [
                    'type' => 'text',
                    'data' => '亲，你的信息已收到~ 活动具体请添加玩翻天-福利君微信（Wanfantiannanjing)咨询。'
                ];
            } else {
                $respMsg = [
                    'type' => 'text',
                    'data' => '如未及时回复，请添加玩翻天小花微信（wanfantian2）咨询。'
                ];
            }
        }
        $requestXml = $this->weiXin->responseMsg($this->weiXinRequestData, $respMsg);
        return $requestXml;
    }

    //活动请求
    private function eventRequest($acid = 1)
    {
        if ($acid == 1) {
//            $data_str = $data = $this->_getWeixinWelcomeTable()->get(array("acid" => $this->acid));
//            $data_str .= "50万遛娃大奖正在派送！参加就有奖！\n";
//            $data_str .= "快点链接参加吧 http://weixin.deyi.com/appvote?m=wftbaby&site=2";
            $data = $this->_getWeixinWelcomeTable()->get(array("acid" => 1));
            $data_str = '恭喜你找到遛娃大本营了！
点击菜单[生成海报] ，发给好友赚积分，即可兑换。';
        } elseif ($acid == 2) {
//            $data_str = "宝妈节日嗨皮！么么哒~\n";
//            $data_str .= "感谢关注玩翻天，回复【红包】欢迎参加最新活动哟~";
            $data = $this->_getWeixinWelcomeTable()->get(array("acid" => 2));
            $data_str = $data['content'];
        } else {
//            $data_str = "欢迎关注本微信号，点击生成海报....";
            $data = $this->_getWeixinWelcomeTable()->get(array("acid" => 3));
            $data_str = $data['content'];
        }
        $respMsg = [
            'type' => 'text',
            'data' => $data_str
        ];
        $requestXml = $requestXml = $this->weiXin->responseMsg($this->weiXinRequestData, $respMsg);
        return $requestXml;
    }

}