<?php
/*
具体格式是：
‘module’ => array (
      ‘controller’ => array(
            ‘action1’ => ‘allow’, //全部用户可见
            ‘action2’ => ‘register’, //登陆用户可见
            ‘action2’ => ‘payer’, //正式付费用户
            ‘action2’ => ‘corepayer’, //正式内部核心付费用户
            ‘action2’ => ‘brandpayer’, //付费品牌用户
            ‘action2’ => ‘adminpayer’, //正式付费管理员
            ‘action2’ => ‘admin’, //管理员
            ‘action2’ => ‘sa’, //超级管理员
            ‘action2’ => ‘manager’, //经理
            ‘action3’ => ‘sm’, //超级经理
            .......
      ),
      ......
  )
 */
return array(
    'LOCAL_ACL_RULES' => array(
        'root'  => array(
            'site'      => array(
                'index' => 'allow',
                'sorry' => 'allow',
                'wechatlogin' => 'register',
            ),
            'file'      => 'allow',
            'main'      => array(
                'index' => 'allow',
            ),
            // ============= 主模块 ============

            //会员专区
            'member' => array(
                'specialarea' => 'allow',
                'getarealists' => 'allow',
                'kidstravel' => 'allow',
                'guide' => 'allow',
                'goodsmore' => 'allow',
                'lucky' => 'register',
                'init' => 'register',
                'getvipsession' => 'register',
                'rank' => 'allow',
            ),
            'about'      => 'allow',
            'user'      => array(
                'code'                => 'allow',
                'babylist'            => 'register',
                'editbaby'            => 'register',
                'editmyinfo'          => 'register',
                'addbaby'             => 'register',
                'uploadimg'           => 'register',
                'center'              => 'allow',
                'order'               => 'register',
                'getallorders'        => 'register',
                'delorder'            => 'register',
                'scores'              => 'register',
                'getscore'            => 'register',
                'getexchangescore'    => 'register',
                'getpointsgoods'      => 'register',
                'pointsrules'         => 'register',
                'userinstr'           => 'register',
                'selectaddr'          => 'register',
                'remainaccount'       => 'register',
                'getaccountlist'      => 'register',
                'seckill'             => 'register',
                'getseckillcommodity' => 'register',
                'usercollect'         => 'register',
                'distribution'        => 'register',
                'mymessage'           => 'register',
                'passwordtransition'  => 'register',
                'passwordset'         => 'register',
                'passwordback'        => 'register',
                'getcode'             => 'register',
                'verifypassword'      => 'register',
                'verifycode'          => 'register',
                'updatepassword'      => 'register',
                'editpassword'        => 'register',
                'register'            => 'allow',
                'paymentset'          => 'register',
                'addresslist'         => 'register',
                'getallusercollect'   => 'register',
                'scorerules'          => 'register',
                'editinfo'            => 'register',
                'wantgo'              => 'register',
                'remindlist'          => 'register',
                'allwantlist'         => 'register',
                'rechargecard'        => 'register',
                'rechargecardpost'    => 'register',
            ),
            'play'      => array(
                'playindex'           => 'allow',
                'playactivity'        => 'allow',
                'getallplaces'        => 'allow',
                'playmember'          => 'register',
                'playconsult'         => 'allow',
                'giveconsult'         => 'allow',
                'getconsultlists'     => 'allow',
                'privateparty'        => 'allow',
                'makeparty'           => 'allow',
                'playseleapplic'      => 'register',
                'playaddrlists'       => 'register',
                'gettravellers'       => 'register',
                'playchoicefield'     => 'register',
                'playselecttraveller' => 'register',
                'gettravellerlist'    => 'register',
                'playaddtraveller'    => 'register',
                'addtraveller'        => 'register',
                'playedittraveller'   => 'register',
                'edittraveller'       => 'register',
                'deltraveller'        => 'register',
                'getallplay'          => 'allow',
                'postprivateparty'    => 'register',
                'gatherdate'          => 'register',
                'wantgolist' => 'allow',
                'godatesubmit'  => 'register',
                'backpay'    => 'register',
            ),         // 溜娃活动
            'ticket'    => array(
                'buyticket'              => 'allow',
                'commoditydetail'        => 'allow',
                'commodityselect'        => 'register',
                'editinfo'               => 'register',
                'commodityorder'         => 'register',
                'getallcoupons'          => 'allow',
                'getallcommoditydel'     => 'register',
                'getallcommoditysend'    => 'register',
                'getallcommodityconsult' => 'allow',
                'getallrecommend'        => 'register',
                'getallcollect'          => 'register',
                'getalldefaultaddr'      => 'register',
                'getalldeladdress'       => 'register',
                'getallcalendar'         => 'register',
                'getalleditaddress'      => 'register',
            ),         // 商品
            'recommend' => array(
                'index'            => 'allow',
                'selectcity'       => 'allow',
                'search'           => 'allow',
                'searchdetail'     => 'allow',
                'delsearchhistory' => 'allow',
                'getallindex'      => 'allow',
                'getallsearch'     => 'allow',

            ),            // 精选
            'discover'  => array(
                'discoverdetail'   => 'allow',
                'speciallist'      => 'allow',
                'specialinfo'      => 'allow',
                'playlist'         => 'allow',
                'playdetail'       => 'allow',
                'getallspecial'    => 'allow',
                'getallinfo'       => 'allow',
                'getallplayground' => 'allow',
                'getallplaydetail' => 'allow',
                'getallpostlike'   => 'register',
                'getallpostdel'    => 'register',
            ),            // 发现

            // ============= 次模块 ============
            'orderwap'  => array(
                'ordercheckout'      => 'register',
                'orderpay'           => 'register',
                'orderclean'         => 'register',
                'nopayinfo'          => 'register',
                'orderplaydetail'    => 'register',
                'orderplaycompleted' => 'register',
                'getallorderwap'     => 'register',
                'backpay'            => 'register',
                'orderselecttraveller'  => 'register',
                'addassociates'  => 'register',
                'chargemoney' => 'register',
                'couponshare' => 'allow',
            ),         // 订单
            'topic'     => 'allow',            // 专题
            'place'     => array(
                'placeindex'  => 'allow',
                'getallplace' => 'allow',
            ),            // 游玩地
            'cash'      => array(
                'cashindex' => 'allow',                // 刮现金奖
                'countdown' => 'allow',
            ),
            'auth'      => 'allow',            // 登陆

            // ============= 组件 ============
            'coupon'    => array(
                'usercoupon'     => 'register',
                'getcouponlists' => 'register',
                'exchangecoupon' => 'register',
                'coupondetail'   => 'register',
                'couponbuy'      => 'register',
                'couponselect'   => 'register',
            ),          // 现金券
            'suit'      => 'allow',            // 套系
            'stage'     => 'allow',           // 场次
            'score'     => 'allow',           // 积分

            // ============= 动作 ============
            'comment'   => array(
                'giveliketo'       => 'register',
                'removeliketo'     => 'register',
                'review'           => 'register',
                'givecommentto'    => 'register',
                'commentlistall'   => 'allow',
                'getcommentlists'  => 'allow',
                'reviewcomment'    => 'allow',
                'recomment'        => 'register',
                'getcommentdetail' => 'allow',
                'disclaimer'       => 'allow',
                'comment'          => 'register',
                'uploadimg'        => 'allow',
            ),          // 评论
            'collect'   => 'allow',         // 收藏
            'ask'       => 'allow',             // 咨询
            'location'  => array(
                'test'         => 'register',
                'redirect'     => 'allow',
                'redirecthome' => 'allow',
                'openpage'     => 'allow'
            ),             // 定位
            'buy'       => 'allow',             // 购买
            'invite'    => 'allow',          // 邀请

            // ============= 其他 ============
            'lottery'   => array(
                'market'              => 'allow',
                'game'              => 'register',
                'gamescore'              => 'register',
                'lucky'               => 'register',
                'record'              => 'register',
                'acceptlotteryrecord' => 'register',
                'login'               => 'register',
                'friend'              => 'allow',
                'votesuperstar'       => 'register',
                'godrank'             => 'allow',
                'guessshop'           => 'register',
                'bindjiguphone'       => 'register',
                'acceptjigurecord'    => 'register',
                'detail'              => 'register',
                'gamebegin'           =>  'register',
                'gamescore'           => 'register',
            ),
            'stats'     => array(
                'getlotterylist'        => 'allow',
                'getlotterystatsdetail' => 'allow',
                'getlotterystatsusers'  => 'allow',
                'getlotteryusersold'    => 'allow',
                'getlotteryusersnew'    => 'allow',
                'getusersdata'          => 'allow',
                'updatelottery'         => 'allow',
                'updatelotteryproduct'  => 'allow',
                'addlotteryattr'        => 'allow',
                'luckuserlist'          => 'allow',
                'changestatus'          => 'allow',
            ),
            'act'       => array(
                'poem'        => 'register',
                'getcash'     => 'register',
                'cash'        => 'allow',
                'cashresult'  => 'allow',
                'cashforcode' => 'allow',
            ),
            'sms'       => 'allow',
            'wms'       => 'allow',
            'activity'  => 'allow',
            'grasp'     => 'allow',
            'seller'    => array(
                'seller'            => 'register',
                'sellgoods'         => 'register',
                'getallsellinfo'    => 'allow',
                'getallsellcash'    => 'allow',
                'getallsellgoods'   => 'allow',
                'getallsellcollect' => 'allow',
            ),

            'kidsplayers' => array(
                'kidsplayersinstr' => 'allow',
            ),

            // pc站点
            'pcsite' => array(
                'index'    => 'allow',
                'school'    => 'allow',
                'business'    => 'allow',
                'join'    => 'allow',
                'article'    => 'allow',
            ),

    ),

        /*后台模块*/
        'admin' => array(
            'brand' => array(
                'index'           => 'adminpayer',
                'getallbrandinfo' => 'allow',
            ),

            /*商家后台*/
            'business'=> array(
                'manageorder'    => 'allow',
                'goodsdetail'    => 'allow',
                'tradeflow'    => 'allow',
                'applicationin'    => 'allow',
                'setpassword'    => 'allow',
                'managecard'    => 'allow',
                'changepassword'    => 'allow',

                'logout'    => 'allow',
                'business'    => 'allow',
                'postlogin' => 'allow',
                'index'    => 'allow',
                'postsellinfo'     => 'allow',
                'getverifycode'    => 'allow',
                'getpassword'    => 'allow',
                'postpasswordchange'    => 'allow',
                'updatebankcard' => 'allow',
                'getorderexecl'    => 'allow',
                'checkcode'    => 'allow',
                'confirmcode'  => 'allow',
            )
        ),
    ),
);