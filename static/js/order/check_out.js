/**
 * Created by deyi on 2016/10/14.
 */
(function () {
    var lists = $('.lists'),
        balance = parseFloat($('.account-money').text()),
        way,
        use_account_money = 0,
        box = $('#password'),
        close_btn = $('.close-btn'),
        password_gold = $('#password-gold'),
        pswBox = $('.password-input').find('span'),
        order_data_json = $('#data').val(),
        order_data = JSON.parse(order_data_json),
        type = order_data.type,
        order_sn = order_data.order_sn,
        total = order_data.total,
        psw,
        realPay = total,
        dao = DAO.order,
        url = '',
        len = '',
        pay_type = '',
        matte = $('.matte'),
        pay = $('#pay'),
        cash_coupon_id = $('.ticket-limit').attr('data-cid'),
        cash_coupon_price = $('.ticket-limit').attr('data-price'),
        coupon_id = 0,
        is_password = $('#is_password').val(),
        seller_id = localStorage.getItem('seller_id'),
        verify_code = localStorage.getItem('verify_code');

    //判断是否是微信内置浏览器
    window.onload = function () {
        if (!M.browser.weixin) {
            var weixin = document.getElementById('weixin');
            weixin.remove();
            if (parseFloat(balance) < parseFloat(total)) {
                $('#zhifubao').trigger(M.click_tap);
                lists.find('.pay-item').eq('0').on(M.click_tap, function () {
                    return false
                });
            } else {
                lists.find('.pay-item').eq('0').trigger(M.click_tap);
            }
        }
        if (M.browser.weixin) {
            var zhifubao = $('#zhifubao');
            zhifubao.remove();
            if (parseFloat(balance) < parseFloat(total)) {
                $('#weixin').trigger(M.click_tap);
                lists.find('.pay-item').eq('0').on(M.click_tap, function () {
                    return false
                });
            } else {
                lists.find('.pay-item').eq('0').trigger(M.click_tap);
            }
        }
        cash_coupon_price = cash_coupon_price == undefined ? 0 : cash_coupon_price;
        realPay = parseFloat(total) - parseFloat(cash_coupon_price);
        updateBanner(0);
        $('.real-pay').text(realPay);
    };

    //选择支付方式
    lists.on(M.click_tap, '.pay-item', function () {
        var $this = $(this);
        way = $(this).attr('data');

        if (total == 0) {
            if (way == 'weixin' || way == 'zhifubao') {
                alert('抱歉，0元商品只能使用余额支付');
                return false
            } else {
                $this.addClass('active').siblings().removeClass('active');
                $this.find('input').prop('checked', true);
                $this.siblings().find('input').prop('checked', false);
            }
        } else {
            $this.addClass('active').siblings().removeClass('active');
            $this.find('input').prop('checked', true);
            $this.siblings().find('input').prop('checked', false);
        }
    });

    // 商品支付
    var param_ticket = {
        'phone': order_data.phone,
        'name': order_data.name,
        'address': order_data.address,
        'number': order_data.number,
        'coupon_id': order_data.coupon_id,
        'order_id': order_data.order_id,
        'group_buy': order_data.group_buy,
        'group_buy_id': order_data.group_buy_id,
        'client_id': order_data.client_id,
        'cash_coupon_id': cash_coupon_id,
        'use_score': order_data.use_score,
        'message': order_data.message,
        'type': 0,
        'associates_ids': order_data.associates_ids,
        'seller_id': seller_id,
        'verify_code': verify_code
    };

    // 活动支付
    var param_play = {
        'coupon_id': order_data.coupon_id,                           
        'session_id': order_data.session_id,
        'address': order_data.address,
        'charges': order_data.charges,
        'associates_ids': order_data.associates_ids,
        'name': order_data.name,
        'cash_coupon_id': cash_coupon_id,
        'phone': order_data.phone,
        'message': order_data.message,
        'type': 1,
        'use_account_money': use_account_money,
        'meeting_id': order_data.meeting_id
    };

    if (order_sn) {
        param_ticket['order_sn'] = order_sn;
        param_play['order_sn'] = order_sn;
    }

    $('body').on(M.click_tap, '.shop-coupon', function () {
        $('.shop').hide();
        $('#js_add_coupon').show();
        coupon_id = select_coupon();
    });

    pay.on(M.click_tap, function () {
        if (way == 'account') {
            $('#real-pay').text(realPay);
            use_account_money = 1;

            if(is_password == 1){
                box.show();
                matte.show();
                password_gold.val('');
                password_gold.focus();
                password_gold.on('input', function () {
                    len = password_gold.val().length;
                    pswBox.text('');
                    pswBox.each(function (i) {
                        if (i < len) {
                            pswBox.eq(i).text('*')
                        }
                    });

                    if (type == 0) {
                        if (len == 6) {
                            M.load.loadTip('loadType', '正在下单');
                            psw = password_gold.val();
                            param_ticket['pay_password'] = psw;
                            param_ticket['use_account_money'] = use_account_money;
                            url = '/ticket/commodityOrder?share=1&order_sn=';
                            account_ajax(param_ticket, url);
                        }
                    } else {
                        if (len == 6) {
                            psw = password_gold.val();
                            param_play['pay_password'] = psw;
                            param_play['use_account_money'] = use_account_money;
                            url = '/orderWap/orderPlayDetail?share=1&order_sn=';
                            account_ajax(param_play, url)
                        }
                    }
                });

                close_btn.on(M.click_tap, function () {
                    box.hide();
                    matte.hide();
                    password_gold.val('').blur();
                });
            }else{
                window.location.href='/user/passwordBack?type=1&flag=2';
            }
        }

        if (way == 'weixin') {
            M.load.loadTip('loadType', '正在下单');
            use_account_money = 0;
            pay_type = 4;
            if (type == 0) {
                param_ticket['use_account_money'] = use_account_money;
                param_ticket['pay_type'] = pay_type;
                url = '/ticket/commodityOrder?share=1&order_sn=';
                select_way_ajax(param_ticket, url);
            } else {
                param_play['use_account_money'] = use_account_money;
                param_play['pay_type'] = pay_type;
                url = '/orderWap/orderPlayDetail?share=1&order_sn=';
                select_way_ajax(param_play, url);
            }
        }

        if (way == 'zhifubao') {
            M.load.loadTip('loadType', '正在下单');
            use_account_money = 0;
            pay_type = 1;
            if (type == 0) {
                param_ticket['use_account_money'] = use_account_money;
                param_ticket['pay_type'] = pay_type;
                select_way_ajax(param_ticket);
            } else {
                param_play['use_account_money'] = use_account_money;
                param_play['pay_type'] = pay_type;
                select_way_ajax(param_play);
            }
        }
    });

    function account_ajax(param, url) {
        dao.orderPay(param, function (res) {
            if (res.status == 1) {
                var data = res['data'];
                if (data.status == -1) {
                    M.load.loadTip('errorType', data.message, 'delay');
                    setTimeout(function () {
                        password_gold.val('');
                        pswBox.each(function (i) {
                            if (i < len) {
                                pswBox.eq(i).text('')
                            }
                        });
                    }, 1000);

                } else if (data.status == 0) {
                    M.load.loadTip('errorType', data.message, 'delay');
                } else if (data.status == 2) {
                    M.load.loadTip('doType', '账户支付成功', 'delay');
                    setTimeout(function () {
                        order_sn = data.order_sn;
                        window.location.href = url + order_sn
                    }, 500)
                }
            } else {
                alert(res.msg);
            }
        });
    }

    function select_way_ajax(param) {
        if (arguments[1] != undefined) {
            var url = arguments[1];
        }
        dao.orderPay(param, function (res) {
            if (res.status == 1) {
                var data = res['data'];
                if (data.status == 1) {
                    var pay_data = data,
                        order_sn = data.order_sn;
                    if (param.pay_type == 4) {
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
                                    window.location.href = url + order_sn;

                                    // todo group_buy_id 作用?
                                    // if (param['group_buy_id']) {
                                    //     window.location.href = '/web/wappay/guideattention';
                                    // } else {
                                    //     window.location.href = '/web/wappay/guideattention';
                                    // }

                                    return false;
                                } else if (data.err_msg == "get_brand_wcpay_request:cancel") {
                                    var ask = confirm('取消支付');
                                    if (ask) {
                                        if (param['group_buy_id']) {
                                            var param_clean = {
                                                'order_sn': order_sn
                                            };
                                            dao.orderClean(param_clean, function (res) {
                                                if (res.status == 1) {
                                                    var data = res['data'];
                                                    if (data.status == 0) {
                                                        M.load.loadTip('loadType', data.message, 'delay');
                                                        if (total > 0) {
                                                            pay.text("去支付").css({"background-color": "#fa6e51"}).removeAttr("disabled")
                                                        }
                                                        else {
                                                            pay.text("去报名").css({"background-color": "#fa6e51"}).removeAttr("disabled")
                                                        }
                                                    } else {
                                                        window.location.href = '/recommend/index';
                                                    }
                                                } else {
                                                    alert(res.msg);
                                                }
                                            });
                                        } else {
                                            window.location.href = '/user/order';
                                        }
                                    }
                                } else if (data.err_msg == "get_brand_wcpay_request:fail") {
                                    M.load.loadTip('errorType', '支付失败', 'delay');
                                    if (total > 0) {
                                        pay.text("去支付").css({"background-color": "#fa6e51"}).removeAttr("disabled");
                                    } else {
                                        pay.text("去报名").css({"background-color": "#fa6e51"}).removeAttr("disabled");
                                    }
                                } else {
                                    M.load.loadTip('errorType', '其他错误:' + data.err_msg, 'delay');
                                    if (total > 0) {
                                        pay.text("去支付").css({"background-color": "#fa6e51"}).removeAttr("disabled");
                                    } else {
                                        pay.text("去报名").css({"background-color": "#fa6e51"}).removeAttr("disabled");
                                    }
                                }
                            }
                        );
                    } else if (param.pay_type == 1) {
                        window.location.href = res.data.url;
                    }

                } else if (data.status == 0) {
                    M.load.loadTip('errorType', res.msg, 'delay');
                }
            } else {
                M.load.loadTip('errorType', res.msg, 'delay');
            }
        });
    }

    function select_coupon() {
        var coupon_id = 0;
        $('.form-radio').on('change', function () {
            console.log($(this));
            var coupon_id = $(this).attr('data-id'),
                coupon_title = $(this).attr('data-title'),
                coupon_choice_price = $(this).attr('data-price');
            cash_coupon_id = coupon_id;
            realPay = parseFloat(total) - parseFloat(coupon_choice_price);
            $('.real-pay').text(realPay);
            $('#sub_addr').text('确认').removeClass('back');
            $('.ticket-limit').text(coupon_title);
        });
    }

    $('body').on(M.click_tap, '#sub_addr', function () {
        $('.shop').show();
        $('#js_add_coupon').hide();
    });

    return coupon_id;
})();