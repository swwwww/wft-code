/**
 * @data access object *
 * @author quenteenfix@gmail.com *
 * @date 2016-06-27
 */
/* global baidu,base_url,yii_csrf_token */
(function (exports, undefined) {
    var emptyFun = function () {
    };
    var mock_flag = false;

    var dao = {};
    dao.request = dao.request || {};
    // ============= 主模块 ============
    dao.user = dao.user || {};
    dao.recommend = dao.recommend || {};    // 精选
    dao.discover = dao.discover || {};      // 发现
    dao.play = dao.play || {};              // 溜娃活动
    dao.ticket = dao.ticket || {};          // 商品

    //==============会员专区==============
    dao.member = dao.member || {};

    // ============= 次模块 ============
    dao.topic = dao.topic || {};      // 专题
    dao.place = dao.place || {};      // 游玩地
    dao.cash = dao.cash || {};        // 账户余额
    dao.auth = dao.auth || {};        // 登陆
    dao.order = dao.order || {};      // 订单

    // ============= 组件 ============
    dao.suit = dao.suit || {};        // 套系
    dao.stage = dao.stage || {};      // 场次
    dao.score = dao.score || {};      // 积分
    dao.coupon = dao.coupon || {};    // 现金券

    // ============= 动作 ============
    dao.comment = dao.comment || {};   // 评论
    dao.collect = dao.collect || {};   // 收藏
    dao.ask = dao.ask || {};           // 咨询
    dao.buy = dao.buy || {};           // 购买
    dao.location = dao.location || {}; // 定位
    dao.invite = dao.invite || {};     // 邀请

    // ============= 其他 ============
    dao.question = dao.question || {};
    dao.page = dao.page || {};
    dao.traveller = dao.traveller || {};
    dao.choice = dao.choice || {};
    dao.special = dao.special || {};
    dao.playList = dao.playList || {};
    dao.playDetail = dao.playDetail || {};
    dao.searchDetail = dao.searchDetail || {};
    dao.delPost = dao.delPost || {};
    dao.sendPost = dao.sendPost || {};
    dao.consult = dao.consult || {};
    dao.lottery = dao.lottery || {};
    dao.stats = dao.stats || {};
    dao.address = dao.address || {};
    dao.calendar = dao.calendar || {};
    dao.like = dao.like || {};
    dao.seller = dao.seller || {};
                                           
    // 个人中心
    dao.user = {
        // 获取所有商品
        getAllorders: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/getAllOrders', params, success, fail);
        },
        // 删除订单
        delOrderItem: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/delOrder', params, success, fail);
        },
        // 获取有秒杀资格的商品
        getSeckillGoods: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/getSeckillCommodity', params, success, fail);
        },
        // 积分兑换秒杀机会
        getExchangeScore: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/getExchangeScore', params, success, fail);
        },
        // 获取我的积分可兑换的商品
        getPointsGoods: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/getPointsGoods', params, success, fail);
        },
        // 签到活动积分
        getMyScore: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            console.log(base_url + controller + '/getScore');
            return dao.request.post(base_url + controller + '/getScore', params, success, fail);
        },
        // 获取账户花销详情
        getAccountList: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/getAccountList', params, success, fail);
        },
        // 添加baby信息
        addMyBabyInfo: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/addBaby', params, success, fail);
        },
        // 上传baby图像
        upLoadBabyImg: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/uploadImg', params, success, fail);
        },
        // 我的收藏列表
        getAllUserCollect: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/getAllUserCollect', params, success, fail);
        },
        // 验证密码
        verifyPassword: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/verifyPassword', params, success, fail);
        },
        // 验证验证码
        verifyCode: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/verifyCode', params, success, fail);
        },
        // 更新密码 前提是经过验证的
        updatePassword: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/updatePassword', params, success, fail);
        },
        // 找回密码
        editPassword: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/editPassword', params, success, fail);
        },
        // 获取验证码
        getCode: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/getCode', params, success, fail);
        },
        userMessage: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/myMessage', params, success, fail);
        },
        editMyInfo: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/editMyInfo', params, success, fail);
        },
        uploadImg: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/uploadImg', params, success, fail);
        },
        //想去场次提醒列表
        remindList: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/remindList', params, success, fail);
        },
        allWantList: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/allWantList', params, success, fail);
        },
        rechargeCard: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'user';
            return dao.request.post(base_url + controller + '/rechargeCardPost', params, success, fail);
        }
    };

    dao.coupon = {
        // 获取现金券列表
        getCouponLists: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'coupon';
            return dao.request.post(base_url + controller + '/getCouponLists', params, success, fail);
        },
        // 兑换现金券
        exchangeCoupon: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'coupon';
            return dao.request.post(base_url + controller + '/exchangeCoupon', params, success, fail);
        },
        getCouponDetails: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'coupon';
            return dao.request.post(base_url + controller + '/couponBuy', params, success, fail);
        }
    };

    // 活动
    dao.play = {
        // 活动首页列表
        getAllPlay: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/getAllPlay', params, success, fail);
        },
        // 获取集合地址
        getAddrLists: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/playAddrLists', params, success, fail);
        },
        // 游玩地
        getAllPlaces: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/getAllPlaces', params, success, fail);
        },
        // 获取咨询列表
        getConsultLists: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/getConsultLists', params, success, fail);
        },
        // 咨询
        giveConsult: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/giveConsult', params, success, fail);
        },
        // 获取出行人列表
        getTrallver: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/giveConsult', params, success, fail);
        },
        // 定制私人活动
        postPrivateParty: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/postPrivateParty', params, success, fail);
        },
        backPay: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/backPay', params, success, fail);
        },
        //我想去
        wantGoList: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/wantGoList', params, success, fail);
        },
        goDateSubmit: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/goDateSubmit', params, success, fail);
        },
        makeParty: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/makeParty', params, success, fail);
        }
    };
    // 点赞及点评
    dao.comment = {
        // 点赞
        giveLike: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'comment';
            return dao.request.post(base_url + controller + '/giveLikeTo', params, success, fail);
        },
        // 取消点赞
        removeLike: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'comment';
            return dao.request.post(base_url + controller + '/removeLikeTo', params, success, fail);
        },
        // 点评
        giveComment: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'comment';
            return dao.request.post(base_url + controller + '/giveCommentTo', params, success, fail);
        },
        getCommentList: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'comment';
            return dao.request.post(base_url + controller + '/getCommentLists', params, success, fail);
        },
        uploadImg: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'comment';
            return dao.request.post(base_url + controller + '/uploadImg', params, success, fail);
        }
    };

    // 出行人
    dao.traveller = {
        // 添加出行人
        playAddTraveller: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/addTraveller', params, success, fail);
        },
        // 编辑出行人
        playEditTraveller: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/editTraveller', params, success, fail);
        },
        // 获取出行人列表
        getTraveller: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/getTravellerList', params, success, fail);
        },
        // 删除出行人
        delTraveller: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'play';
            return dao.request.post(base_url + controller + '/delTraveller', params, success, fail);
        }
    };

    //会员专区
    dao.member = {
        getAreaLists: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'member';
            return dao.request.post(base_url + controller + '/getAreaLists', params, success, fail);
        },
        init: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'member';
            return dao.request.post(base_url + controller + '/init', params, success, fail);
        },
        lucky: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'member';
            return dao.request.post(base_url + controller + '/lucky', params, success, fail);
        },
        getVipSession: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'member';
            return dao.request.post(base_url + controller + '/getVipSession', params, success, fail);
        }
    };

    dao.cash = {

    };

    // 商品
    dao.ticket = {
        getAllCoupons: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'ticket';
            return dao.request.post(base_url + controller + '/getAllCoupons', params, success, fail);
        },
        setDefaultAddr: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'ticket';
            return dao.request.post(base_url + controller + '/getAllDefaultAddr', params, success, fail);
        }
    };

    //
    dao.choice = {
        getAllIndex: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'recommend';
            return dao.request.post(base_url + controller + '/getAllIndex', params, success, fail);
        }
    };

    dao.special = {
        getAllSpecial: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'discover';
            return dao.request.post(base_url + controller + '/getAllSpecial', params, success, fail);
        }
    };

    dao.topic = {
        getAllInfo: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'discover';
            return dao.request.post(base_url + controller + '/getAllInfo', params, success, fail);
        }
    };

    dao.playList = {
        getAllPlayground: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'discover';
            return dao.request.post(base_url + controller + '/getAllPlayground', params, success, fail);
        }
    };

    dao.discover = {
        getAllPlayDetail: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'discover';
            return dao.request.post(base_url + controller + '/getAllPlayDetail', params, success, fail);
        }
    };

    dao.searchDetail = {
        getAllSearch: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'recommend';
            return dao.request.post(base_url + controller + '/getAllSearch', params, success, fail);
        },
        delSearchHistory: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'recommend';
            return dao.request.post(base_url + controller + '/delSearchHistory', params, success, fail);
        }
    };

    dao.delPost = {
        getAllCommodityDel: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'ticket';
            return dao.request.post(base_url + controller + '/getAllCommodityDel', params, success, fail);
        }
    };

    dao.sendPost = {
        getAllCommoditySend: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'ticket';
            return dao.request.post(base_url + controller + '/getAllCommoditySend', params, success, fail);
        }
    };

    dao.consult = {
        getAllCommodityConsult: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'ticket';
            return dao.request.post(base_url + controller + '/getAllCommodityConsult', params, success, fail);
        }
    };

    dao.recommend = {
        getAllRecommend: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'ticket';
            return dao.request.post(base_url + controller + '/getAllRecommend', params, success, fail);
        }
    };

    dao.collect = {
        getAllCollect: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'ticket';
            return dao.request.post(base_url + controller + '/getAllCollect', params, success, fail);
        }
    };

    dao.lottery = {
        voteSuperstar: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'lottery';
            return dao.request.post(base_url + controller + '/voteSuperstar', params, success, fail);
        },
        lucky: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'lottery';
            return dao.request.post(base_url + controller + '/lucky', params, success, fail);
        },
        gameScore: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'lottery';
            return dao.request.post(base_url + controller + '/gameScore', params, success, fail);
        },
        acceptLotteryRecord: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'lottery';
            return dao.request.post(base_url + controller + '/acceptLotteryRecord', params, success, fail);
        },
        // 传猜图标题
        guessShop: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'lottery';
            return dao.request.post(base_url + controller + '/guessShop', params, success, fail);
        },
        //几古家绑定手机
        bindJiguPhone: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'lottery';
            return dao.request.post(base_url + controller + '/bindJiguPhone', params, success, fail);
        },
        //几古家领奖
        acceptJiguRecord: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'lottery';
            return dao.request.post(base_url + controller + '/acceptJiguRecord', params, success, fail);
        },
        //
        gameBegin: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'lottery';
            return dao.request.post(base_url + controller + '/gameBegin', params, success, fail);
        },
        gameScore: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'lottery';
            return dao.request.post(base_url + controller + '/gameScore', params, success, fail);
        }
    };

    dao.stats = {
        addLotteryAttr: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'stats';
            return dao.request.post(base_url + controller + '/addLotteryAttr', params, success, fail);
        },

        changeStatus: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'stats';
            return dao.request.post(base_url + controller + '/changeStatus', params, success, fail);
        }
    };

    dao.address = {
        getAllDefaultAddr: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'ticket';
            return dao.request.post(base_url + controller + '/getAllDefaultAddr', params, success, fail);
        },
        getAllDelAddress: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'ticket';
            return dao.request.post(base_url + controller + '/getAllDelAddress', params, success, fail);
        },
        getAllEditAddress: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'ticket';
            return dao.request.post(base_url + controller + '/getAllEditAddress', params, success, fail);
        }
    };

    dao.calendar = {
        getAllCalendar: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'ticket';
            return dao.request.post(base_url + controller + '/getAllCalendar', params, success, fail);
        }
    };

    dao.like = {
        getAllPostLike: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'discover';
            return dao.request.post(base_url + controller + '/getAllPostLike', params, success, fail);
        },

        getAllPostDel: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'discover';
            return dao.request.post(base_url + controller + '/getAllPostDel', params, success, fail);
        }
    };

    dao.order = {
        orderCheckout: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'orderWap';
            return dao.request.post(base_url + controller + '/orderCheckout', params, success, fail);
        },
        orderPay: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'orderWap';
            return dao.request.post(base_url + controller + '/orderPay', params, success, fail);
        },
        orderClean: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'orderWap';
            return dao.request.post(base_url + controller + '/orderClean', params, success, fail);
        },
        noPayInfo: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'orderWap';
            return dao.request.post(base_url + controller + '/noPayInfo', params, success, fail);
        },
        backPay: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'orderWap';
            return dao.request.post(base_url + controller + '/backPay', params, success, fail);
        },
        //补录出行人
        addAssociates: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'orderWap';
            return dao.request.post(base_url + controller + '/addAssociates', params, success, fail);
        },
        chargeMoney: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'orderWap';
            return dao.request.post(base_url + controller + '/chargeMoney', params, success, fail);
        }
    };

    dao.seller = {
        getAllSellInfo: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'seller';
            return dao.request.post(base_url + controller + '/getAllSellInfo', params, success, fail);
        },
        getAllSellCash: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'seller';
            return dao.request.post(base_url + controller + '/getAllSellCash', params, success, fail);
        },
        getAllSellGoods: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'seller';
            return dao.request.post(base_url + controller + '/getAllSellGoods', params, success, fail);
        },
        sellGoods: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'seller';
            return dao.request.post(base_url + controller + '/sellGoods', params, success, fail);
        }
    };

    /**
     * 题目相关请求配置
     */
    dao.question = {
        // 获取页信息
        getMatchWord: function (params, success, fail) {
            var controller = mock_flag ? 'mock' : 'question';
            return dao.request.get(base_url + controller + '/page', params, success, fail);
        },

        // 获取题目信息
        getQuestion: function (params, success, fail) {
            return dao.request.get(base_url + 'mock/question', params);
        }
    };

    dao.page = {
        // 获取项目列表
        getProjectList: function (params, success, fail) {
            var controller = mockFlag ? 'mock' : 'mark';
            return dao.request.get(base_url + controller + '/projectList', params, success);
        },

        receiveProject: function (params, success, fail) {
            return dao.request.post(base_url + controller + '/receiveProject', params, success, fail);
        }
    };

    dao.request = {
        get: function (url, params, success, fail) {
            params = params || '';

            return $.ajax({
                type: 'GET',
                url: url,
                data: params,
                cache: false
            }).then(function (data) {
                var jsonData = data;

                if (typeof data === 'string') {
                    if (JSON && JSON.parse) {
                        jsonData = JSON.parse(data);
                    } else {
                        jsonData = eval('(' + data + ')');
                    }
                }

                success = success || emptyFun;
                success(jsonData);
            }, fail || emptyFun);
        },

        post: function (url, params, success, fail, options) {
            if (typeof params === 'string') {
                params = params || '';
                params += '&' + M.util.getUrlFromJson(M.util.getPublicParam());
            } else {
                params = params || {};
                params = M.util.lightCopy(params, M.util.getPublicParam());
            }
            var settings = {
                type: 'POST',
                url: url,
                data: params || {},
                cache: false
            };
            // 若有额外的参数，添加到settings中去，目前没有判定传入的参数的有效性，需调用者保证
            if (typeof options === 'object' && !(options instanceof Array)) {
                options = options || {};
                $.each(options, function (key, value) {
                    settings[key] = value;
                });
            }

            return $.ajax(settings).then(function (data) {
                var jsonData = data;
                if (typeof data === 'string' && data.length) {
                    if (JSON && JSON.parse) {
                        jsonData = JSON.parse(data);
                    } else {
                        jsonData = eval('(' + data + ')');
                    }
                }

                if (data === '') {
                    jsonData = {
                        status: 1
                    };
                }
                success = success || emptyFun;
                success(jsonData);
            }, fail || emptyFun);
        }
    };

    exports.DAO = dao;
})(window, undefined);
