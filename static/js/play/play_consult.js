/**
 * Created by xxxian on 2016/9/29 0029.
 */
(function () {
    var id = $('#js_play_id').val(),
        loginTip = $("#tips-dia"),
        msg = $('#js_submit_msg'),
        config_tips = {
            msg: '提示信息',
            padding_tb: '4%',
            padding_rl: '4%',
            top: '34%',
            font_size: 28,
            time: 2500,
            z_index: 100
        };

    $('body').on(M.click_tap, '#js_submit', function (e) {
        var str = msg.val();
        e.preventDefault();
        if(!str){
            config_tips.msg = "请输入咨询内容";
            M.util.popup_tips(config_tips);
            setTimeout(function(){
                $("#btn").attr('disabled', false);
            },1500);
            // msg.focus(); //
            return false;
        }else {
            var message = msg.val();
            giveConsultTo(message);
        }

    });

    //咨询动作
    function giveConsultTo(message) {
        var dao = DAO.play;
        var param = {
            'play_id': id,
            'message': message
        };
        dao.giveConsult(param, function (res) {
            if(res.status == 1) {
                var data = res['data'];
                if (data.status == 1) {
                    config_tips.msg = data.message;
                    M.util.popup_tips(config_tips);
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                }else {
                    config_tips.msg = data.message;
                    M.util.popup_tips(config_tips);
                }
            }else if(res.status == 0) {
                var data = res['data'];
                if(data.error_code == 0){
                    config_tips.msg = data.error_msg;
                    M.util.popup_tips(config_tips);
                }
            }
        });
    }

    //咨询列表分页
    function renderData() {
        var drop_list_flag = true;
        var dao = DAO.play;

        var param = {
            'play_id': id,
            'page': 1,
            'page_num': 10
        };
        dao.getConsultLists(param, function (res) {
            if(res.status == 1){
                var result = [];
                var data = res['data'];
                var mid = data.length == 0?0 :data[data.length-1].mid ;

                result['res'] = res['data'];
                if (!data[0]) {
                    result['gif_flag'] = 1;
                    drop_list_flag = false;
                }

                template.helper("dateFormat", dateFormat);
                $('.data-list').append(template('list-item', result));
                if(drop_list_flag){ //有数据就进行分页
                    dropList(mid);
                }
            }
        });
    }
    renderData();

//分页
    function dropList(mid) {
//加载更多
        var last_id = mid;
        var counter=1;

        var dropload = $('.cons').dropload({
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
                    var dao = DAO.play;
                    var param = {
                        'play_id': id,
                        'page_num': 10,
                        'last_id': last_id
                    };

                    dao.getConsultLists(param, function (res) {
                        if(res.status == 1){

                            var result = [];
                            var data = res['data'];
                                result['res'] = data;
                            last_id = data.length == 0?0 :data[data.length-1].mid ;
                            setTimeout(function () {
                                template.helper("dateFormat", dateFormat);
                                $('.data-list').append(template('list-item', result));
                                me.resetload();
                            }, 500);

                            if (data.length == 0) {
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
