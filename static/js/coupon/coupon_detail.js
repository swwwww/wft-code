/**
 * Created by xxxian on 2016/10/11 0011.
 */

(function () {
    var cid = $('#cid').val(),
        id = $('#id').val();

    function renderData() {
        var drop_list_flag = true;
        var dao = DAO.coupon;
        var params = {
            'cid': cid,
            'id': id,
            'page': 1,
            'page_num': 5
        };
        console.log(params);

        dao.getCouponDetails(params, function (res) {
            if(res.status){
                var res = res['data'];
                var result = [];
                result['res'] = res['list'];
                if (result.length == 0) {
                    result['gif_flag'] = 1;
                    drop_list_flag = false;
                }
                template.helper("dateFormat", dateFormat);
                $('.item-list').append(template('list', result));
                if(drop_list_flag){ //有数据就进行分页
                    dropList();
                }
            }


        });
    }
    renderData();

    //分页
    function dropList() {
        var counter = 1;
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
                counter++;
                function renderMore() {
                    var result = [];
                    var dao = DAO.coupon;
                    var params = {
                        'cid' : cid,
                        'id' : id,
                        'page' : counter,
                        'page_num' : 5
                    };
                    dao.getCouponDetails(params, function (res) {
                        if(res.status){
                            var res = res['res'];
                            result['res'] = res['list'];
                            console.log(result);
                            setTimeout(function () {
                                template.helper("dateFormat", dateFormat);
                                $('.item-list').prepend(template('list', result));
                                me.resetload();
                            }, 500);
                            if (result.length == 0) {
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
