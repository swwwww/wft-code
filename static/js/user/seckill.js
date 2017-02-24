/**
 * Created by Administrator on 2016/8/24 0024.
 */
(function () {
    var change = parseInt($('#change').val(), 10); //秒杀资格
    var ex_chance = parseInt($('#ex_chance').val(), 10); //积分兑换次数
    var now_time = parseInt(new Date().getTime()/1000);

    //兑换秒杀机会
    $('body').on(M.click_tap, '#js_exchange', function (e) {
        e.preventDefault();
        var dao = DAO.user;
        var shop_sid = Number($('#shop_sid').val());
        var param = {};
        loading('正在操作...')
        dao.getExchangeScore(param, function (res) {
            if(res.status){
                var res = res['data'];
                if(res.status == 1){
                    loadingSuccess(res.message);
                    setTimeout(function(){
                        if(shop_sid){
                            window.location.href = base_url_module + 'ticket/commodityDetail?id='+shop_sid;
                        }else {
                            window.location.reload();
                        }
                    },1500);
                }else {
                    loadingErr(res.message);
                }
            }
        });

    });

    //秒杀机会不够的提示
    function give_tip() {
        $('body').on(M.click_tap, '#js_go_buy', function () {
            if(change == 0){
                if(ex_chance>0){
                    alert('赶紧兑换积分！！！');
                }else {
                    alert('立刻去赚取积分！！！');
                }
            }
        });
    }
    give_tip();

    function renderData() {
        var drop_list_flag = true;
        var dao = DAO.user;
        var param = {
            'page': 1,
            'page_num': 10
        };
        dao.getSeckillGoods(param, function (res) {
            if(res.status){
               var res = res['data'];
                var data_res = [];
                var result = [];
                for (var i=0; i<res['coupon_list'].length; i++){
                    res['coupon_list'][i]['change'] = change;
                    res['coupon_list'][i]['ex_chance'] = ex_chance;
                    if(res['coupon_list'][i]['end_time']>now_time){
                        data_res.push(res['coupon_list'][i])
                    }
                }
                result['res']= data_res;
                if(!result['res'][0]){
                    result['gif_flag'] = 1;
                    drop_list_flag = false;
                }
                console.log(res);
                template.helper("dateFormat", dateFormat);
                $('.in-list').append(template('list', result));
                if(drop_list_flag){
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

        var dropload = $('#nav-drop').dropload({
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
                    dao.getSeckillGoods(param, function (res) {
                        if(res.status){
                            var res =res['data'];
                            var data_res = [];
                            var result = [];
                            for (var i=0; i<res['coupon_list'].length; i++){
                                res['coupon_list'][i]['change'] = change;
                                res['coupon_list'][i]['ex_chance'] = ex_chance;
                                if(res['coupon_list'][i]['end_time']>now_time){
                                    data_res.push(res['coupon_list'][i])
                                }
                            }
                            result['res']= data_res;
                            setTimeout(function () {
                                template.helper("dateFormat", dateFormat);
                                $('.in-list').append(template('list', result));
                                me.resetload();
                            }, 500);
                            if (result['res'] == 0) {
                                me.lock();
                                me.noData();
                                return;
                            }
                            console.log(res);

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