(function () {
    var config_tips = {
            msg: '提示信息',
            padding_tb: '4%',
            padding_rl: '4%',
            top: '45%',
            font_size: 28,
            time: 2500,
            z_index: 100
        }
    //今日签到
    $('body').on(M.click_tap, '.nav-sign', function () {
        var dao = DAO.user,
            sid = Number($('.shop_id').val());
        param = { };
        dao.getMyScore(param, function (res) {
            if(res.status){
                var res=res['data'];
                if(res.status == 1){
                    config_tips.msg = res.message;
                    config_tips.is_reload = true;
                    M.util.popup_tips(config_tips);
                    if(sid){
                        window.location.href = base_url_module + 'ticket/commodityDetail?id='+sid;
                    }
                }else{
                    config_tips.msg = res.message;
                    M.util.popup_tips(config_tips);
                }
            }
        });
    });
    $('body').on(M.click_tap, '.sign-done', function (e) {
        e.preventDefault();
        config_tips.msg = '今日事，今日毕！明日事，明日再战~~';
        M.util.popup_tips(config_tips);
    });

    function renderData() {
        var drop_list_flag = true;
        var dao = DAO.user;

        var param = {
            'page': 1,
            'page_num': 10
        };
        dao.getPointsGoods(param, function (res) {
            if(res.status){
                var res = res['data'];
                var result = [];
                result['res'] = res['coupon_list'];
                if(!result['res'][0]){
                    result['gif_flag'] = 1;
                    drop_list_flag = false;
                }

                template.helper("dateFormat", dateFormat);
                $('.in-list').append(template('list', result));
                if(drop_list_flag){
                    dropList();
                }
            }

        })
    }
    renderData();

//分页
    function dropList() {
        //加载更多
        var counter = 1;

        var dropload = $('.in-list').dropload({
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
                        'page': counter,
                        'page_num': 10
                    };

                    dao.getPointsGoods(param, function (res) {
                        if(res.status){
                            var res = res['data'];
                            var result = [];
                            result['res'] = res['coupon_list'];
                            setTimeout(function () {
                                template.helper("dateFormat", dateFormat);
                                $('.in-list').prepend(template('list', result));
                                me.resetload();
                            }, 500);
                            if (res['coupon_list'] == 0) {
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