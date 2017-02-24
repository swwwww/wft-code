/**
 * Created by deyi on 2016/11/2.
 */
(function () {
    var pay = $('.pay'),
        vip = $('.vip-list'),
        way,
        moneyEnter = $('.ticket-limit'),
        total = 100;
    //判断浏览器选择支付方式
    window.onload = function () {
        if (!M.browser.weixin) {
            var weixin = document.getElementById('weixin');
            weixin.remove();
            $('#zhifubao').trigger(M.click_tap);
        }

        if (M.browser.weixin) {
            var zhifubao = $('#zhifubao');
            zhifubao.remove();
            $('#weixin').trigger(M.click_tap);
        }

        if($('#isShowVip').val() == 0){
            $('.real-pay').text(total);
        }
        //$('.ticket-limit').focus();

    };

    //会员充值金额
    vip.on(M.click_tap, '.item-li', function () {
       var $this = $(this),
           money = parseFloat($this.children().find('.money-value').attr('data-value'));
        $this.addClass('tap-on');
        $this.siblings().removeClass('tap-on');
        $('.ticket-limit').val('');
        $('.real-pay').text(money);
    });

    //选择支付方式
    pay.on(M.click_tap, '.pay-item', function () {
        var $this = $(this);
        way = $this.attr('data');
        $this.find('i').addClass('active');
        $this.siblings().find('i').removeClass('active');
    });

    //input监控输入金额
    moneyEnter.on('input', function () {
        total = $(this).val();
        $('.real-pay').text(total);
        $('.item-li').removeClass('tap-on');
    });

    // 去支付按钮
    $('.submit').on(M.click_tap, function () {
        $(this).css({'background':'#b8b8b8'}).text('请稍等...');
        chargeMoney(way);
    });

    function chargeMoney(way) {
        var isVip = $('#isVip').val();
        var payType = getPayType(way);
        var fromUid = $('#from_uid').val();
        var dao = DAO.order;

        total = parseFloat($('.real-pay').text());
        var param = {
            'money': total,
            'paytype': payType,
            'from_uid': fromUid
        };

        dao.chargeMoney(param, function (res) {
            // console.log(res)
            if (res.status == 1) {
                if (way == 'zhifubao') {
                    M.load.loadTip('doType', res.msg, 'delay');
                    window.location.href = res.data.url;
                } else if (way == 'weixin') {
                    M.load.loadTip('loadType', '即将充值');

                    pay_data = res.data;
                    WeixinJSBridge.invoke(
                        'getBrandWCPayRequest', {
                            "appId": "" + pay_data.appId + "",     //公众号名称，由商户传入
                            "timeStamp": "" + pay_data.timeStamp + "",         //时间戳，自1970年以来的秒数
                            "nonceStr": "" + pay_data.nonceStr + "", //随机串
                            "package": "" + pay_data.package + "",
                            "signType": pay_data.signType,         //微信签名方式:
                            "paySign": "" + pay_data.paySign + "" //微信签名
                        },
                        function (data) {
                            if (data.err_msg == "get_brand_wcpay_request:ok") {
                                // 使用以上方式判断前端返回,微信团队郑重提示：res.err_msg将在用户支付成功后返回    ok，但并不保证它绝对可靠。
                                M.load.loadTip('doType', '支付成功', 'delay');
                                window.location.href = "/user/remainAccount?share=1&total="+total;
                                return false;
                            } else if (data.err_msg == "get_brand_wcpay_request:cancel") {
                                var ask = confirm('取消支付');
                                if (ask) {
                                        dao.orderClean(param_clean, function (res) {
                                            if (res.status == 1) {
                                                var data = res['data'];
                                                if (data.status == 0) {
                                                    M.load.loadTip('loadType', data.message, 'delay');
                                                } else {
                                                    window.location.href = '/user/remainAccount';
                                                    return true;
                                                }
                                            } else {
                                                M.load.loadTip('errorType', res.msg, 'delay');
                                            }
                                            // window.location = window.location.href;
                                            window.location.href = '/orderWap/chargeMoney'
                                        });
                                }else{
                                    // window.location = window.location.href;
                                    window.location.href = '/orderWap/chargeMoney'
                                }
                            } else if (data.err_msg == "get_brand_wcpay_request:fail") {
                                M.load.loadTip('errorType', '支付失败', 'delay');
                            } else {
                                M.load.loadTip('errorType', '其他错误:' + data.err_msg, 'delay');
                            }
                        }
                    );
                }
            } else {
                M.load.loadTip('errorType', res.msg, 'delay');
            }
        });

    }

    function getPayType(way) {
        var payType = 2;
        if (way == 'zhifubao') {
            payType = 1;
        } else if (way == 'weixin') {
            payType = 4;
        }
        return payType;
    }

})();