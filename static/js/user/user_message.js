/**
 * Created by MEX | mixmore@yeah.net on 16/11/10.
 */
(function () {
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };


    //分页
    function dropList() {
        //加载更多
        var counter = 1;
        var message_id = 0;
        var dropload = $('.message-total').dropload({
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
                        'message_id': message_id,
                    };
                    dao.userMessage(param, function (res) {
                        if (res.status == 1) {
                            var res_data = res.data;
                            var messageList = res_data['message_list'];
                            var len = messageList.length;
                            message_id = messageList[len - 1]['id'];

                            var result = [];
                            result['res'] = messageList;
                            setTimeout(function () {
                                template.helper("dateFormat", dateFormat);
                                $('.message-list').append(template('message-list', result));
                                me.resetload();
                            }, 500);
                            if (messageList == 0) {
                                me.lock();
                                me.noData();
                                return;
                            }
                        } else {
                            config_tips.msg = res.msg;
                            M.util.popup_tips(config_tips);
                        }
                    });
                }

                renderMore();
            }
        });
    }

    dropList();

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
}());