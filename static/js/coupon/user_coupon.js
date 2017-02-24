/**
 * Created by xxxian on 2016/10/9 0009.
 */
(function () {
    var type = $('#type').val(),
        pay_price = $('#pay_price').val(),
        sid = $('#sid').val(),
        coupon_id = $('#coupon_id').val(),
        loginTip = $('#tips-dia');
    var result=[];
    var res_use= [];
    var res_not= [];
    var pagenum = 2;
    var coupon_data = JSON.parse($('#data').val());

    function renderData() {
        var drop_list_flag = true;
        var dao = DAO.coupon;
        var param = [];
        if (coupon_data.f) {
            param = {
                'pagenum': 200,
                'page': 1,
                'coupon_id': coupon_data.coupon_id,
                'info_id': coupon_data.info_id,
                'pay_price': coupon_data.pay_price,
                'type': coupon_data.type
            };
        } else {
            param = {
                'pagenum': 200,
                'page': 1
            };
        }

        dao.getCouponLists(param, function (res) {
            if(res.status){
                var res = res['data'];

                var len = res.length;
                for(var i=0,j=0, n=0; i<len; i++){
                    if(res[i].isvalid == 1){
                        res_use[j++] = res[i];
                    }else {
                        res_not[n++] = res[i];
                    }
                }

                result['can_use_len']= res_use.length;
                var flag = (res_use.length /pagenum)-1;
                result['no_use_len']=res_not.length;
                result['res'] = res;

                if (!res[0]) {
                    result['gif_flag'] = 1;
                    drop_list_flag = false;
                }

                template.helper("dateFormat", dateFormat);
                $('.coupon-item').prepend(template('list', result));
                if(drop_list_flag){ //有数据就进行分页
                    // dropList(flag);
                }
            }

        });
    }
    renderData();

//分页
    function dropList(flag) {
        var counter = 1;
        flag--;
//加载更多
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
                // counter++;
                function renderMore() {
                    var dao = DAO.coupon;
                    var param = {
                        'page': counter++,
                        'coupon_id': coupon_id,
                        'pay_price': pay_price,
                        'type': type
                    };
                    dao.getCouponLists(param, function (res) {
                     if(res.status){
                         var res = res['data'];
                         var res_use= [];
                         var res_not= [];
                         var len = res.length;
                         for(var i=0,j=0, n=0; i<len; i++){
                             if(res[i].isvalid == 1){
                                 res_use[j++] = res[i];
                             }else {
                                 res_not[n++] = res[i];
                             }
                         }

                         result['can_use_len']= res_use.length;
                         result['no_use_len']=res_not.length;
                         result['res'] = res;

                         setTimeout(function () {
                             template.helper("dateFormat", dateFormat);
                             $('.coupon-item').prepend(template('list', result));
                             // $('.coupon-item-not').prepend(template('list', result_not));
                             me.resetload();
                         }, 500);
                         console.log("res");
                         console.log(res);
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


//兑换现金券
    $('body').on(M.click_tap, '.js-exchange', function (e) {
        e.preventDefault();
        var code = $("#code").val(),
            config = {
                msg: '提示信息',
                padding_tb: '4%',
                padding_rl: '8%',
                top : '45%',
                font_size: 28,
                time:1500,
                is_reload: false
            },
            dao = DAO.coupon,
            param = {
                'code': code
            };
        if(code){
            dao.exchangeCoupon(param, function (res) {
                if(res.status){
                    var res = res['data'];
                    console.log(res);
                    if(res.status == 1){
                        loginTip.text(res.message);
                        loginTip.show();
                        setTimeout(function(){
                            loginTip.hide();
                            window.location.reload();
                        },3000);
                    }else {
                        alert(res.message);
                    }
                }
            });
        }else {
            config.msg = '请输入邀请码!';
            M.util.popup_tips(config);
            // loginTip.text('请输入邀请码!');
            // loginTip.show();
            // setTimeout(function(){
            //     loginTip.hide();
            // },3000);
        }
    });


})();

