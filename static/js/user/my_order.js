(function () {
    var loginTip = $("#tips-dia");
    var order_status = $('#order_status').val();
    $(".sel_" + order_status).addClass('menu-list-cur').siblings().removeClass('menu-list-cur');

    /*
     * 取消订单事件
     * */
    function cancel_order() {
        $('body').on(M.click_tap, '#js_cancel_order', function () {
            var order_sn = $(this).attr('data-sn');
            var type = $(this).attr('data-type');
            $('.mark').show();
            $('#js_sure_cancel').show();

            //"确定"按钮
            $('body').on(M.click_tap, '#js_sure', function () {
                var dao = DAO.user;
                var param = {
                    'order_sn': order_sn,
                    'type': type
                };
                $('#js_sure_cancel').hide();
                $('.mark').hide();
                dao.delOrderItem(param, function (res) {
                    if (res.status == 1) {
                        var res = res['data'];
                        if (res.status == 1) {
                            loginTip.text(res.message);
                            loginTip.show();
                            setTimeout(function () {
                                loginTip.hide();
                                window.location.reload();
                            }, 3000);
                        } else if (res.status == 0) {
                            loginTip.text(res.message);
                            loginTip.show();
                            setTimeout(function () {
                                loginTip.hide();
                            }, 3000);
                        }
                    }

                });


            });
        });
        //"取消"按钮
        $('body').on(M.click_tap, '#js_cancel', function () {
            $('.mark').hide();
            $('#js_sure_cancel').hide();
        });

    }

    cancel_order();

    /*
     * 重新付款订单事件
     * */
    function pay_order() {
        $('body').on(M.click_tap, '#js_to_pay', function () {
            var order_sn = $(this).attr('data-sn');
            var type = $(this).attr('data-type');
            // http://wftgit.greedlab.com/wft/api-document/blob/master/document/nopayIndex.md#L0
            // todo: 下面这个data-data获取可用调用上面这个借口获取
            var data = JSON.parse($(this).attr('data-data'));
            // var dao = DAO.order;
            // dao.noPayInfo({'order_sn':order_sn}, function (res) {
            //"确定"按钮
            var temp_value = 0;
            var param_ticket = {
                'repay': 1,
                'order_type' :data['order_type'],
                'order_sn': order_sn,
                'coupon_id': data['coupon_id'],
                'info_id': data['info_id'],
                'number': data['buy_number'],
                'group_buy_id': data['group_buy_id'],
                'total': data['total_price'],
                'address': data['use_address']
            };

            if(data['order_type'] == 2){
                param_ticket['info_id'] = data['coupon_id'];
                param_ticket['coupon_id'] = data['info_id'];
            }
            param_ticket = M.util.getUrlFromJson(param_ticket);
            window.location.href = base_url_module + 'orderWap/orderCheckOut?' + param_ticket;
            // });
        })
    }
    pay_order();


    function renderData() {
        var drop_list_flag = true;
        var dao = DAO.user;
        var param = {
            'order_status': order_status,
            'page': 2,
            'page_num': 10
        };
        var now_time = Date.parse(new Date());

        dao.getAllorders(param, function (res) {
            for (var i = 0; i < res.length; i++) {
                res[i]['data'] = JSON.stringify(res[i]);
                res[i]['now_time'] = now_time;
            }

            if (res.status) {
                var res = res['data'];
                var result = [];
                result['res'] = res;
                if (!res[0]) {
                    result['gif_flag'] = 1;
                    drop_list_flag = false;
                }
                template.helper("dateFormat", dateFormat);
                $('.order-list').append(template('list', result));
                if (drop_list_flag) { //有数据就进行分页
                    dropList();
                }
            }

        });
    }

    renderData();

    //分页
    function dropList() {
//加载更多
        var counter = 1;

        var dropload = $('.main').dropload({
            scrollArea: window,
            domDown: {
                domClass: 'dropload-down',
                domRefresh: '<div class="dropload-refresh" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">↑上拉加载更多</div>',
                domLoad: '<div class="dropload-load" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;"><span class="loading"></span>加载中...</div>',
                domNoData: '<div class="dropload-noData" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">已经全部加载完毕</div>'
            },
            //上拉加载函数
            loadDownFn: function (me) {
                counter++;
                function renderMore() {
                    var dao = DAO.user;
                    var param = {
                        'order_status': order_status,
                        'page': counter,
                        'page_num': 10
                    };
                    var now_time = Date.parse(new Date());

                    dao.getAllorders(param, function (res) {
                        if (res.status) {
                            var res = res['data'];
                            res['order_status'] = order_status;
                            var result = [];
                            for (var i = 0; i < res.length; i++) {
                                res[i]['data'] = JSON.stringify(res[i]);
                                res[i]['now_time'] = now_time;
                            }
                            result['res'] = res;
                            setTimeout(function () {
                                template.helper("dateFormat", dateFormat);
                                $('.order-list').prepend(template('list', result));
                                me.resetload();
                            }, 500);
                            if (res.length == 0) {
                                me.lock();
                                me.noData();
                                return;
                            }
                        }

                    });
                }

                renderMore();
            }
        });
    }

    //template时间转换函数
    function dateFormat(date, format) {
        date = new Date(date * 1000);
        var map = {
            "M": date.getMonth() + 1, //月份
            "d": date.getDate(), //日
            "h": date.getHours(), //小时
            "m": date.getMinutes(), //分
            "s": date.getSeconds(), //秒
            "q": Math.floor((date.getMonth() + 3) / 3), //季度
            "S": date.getMilliseconds() //毫秒
        };

        format = format.replace(/([yMdhmsqS])+/g, function (all, t) {
            var v = map[t];
            if (v !== undefined) {
                if (all.length > 1) {
                    v = '0' + v;
                    v = v.substr(v.length - 2);
                }
                return v;
            }
            else if (t === 'y') {
                return (date.getFullYear() + '').substr(4 - all.length);
            }
            return all;
        });
        return format;
    }
})();