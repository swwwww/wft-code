/**
 * Created by Administrator on 2016/8/26 0026.
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
    // var  prev_page_end = [];
    // var next_page_first = [];

    function renderData() {
        var drop_list_flag = true;
        var dao = DAO.user;
        var param = {
            'page': 1,
            'page_num': 10
        };
        dao.getAccountList(param, function (res) {
            if(res.status == 1){
                var result = [];
                var data = res['data'];
                result['res'] = data['flows'];
                if(!result['res'][0]){
                    result['gif_flag'] = 1;
                    drop_list_flag = false;
                }

                var date_line = [];
                for(var i=0; i<result['res'].length; i++){
                    var j = parseInt(i+1, 10);
                    date_line[i] = result['res'][i]['dateline'];
                }
                if(result['res'].length > 0){
                    result['res'][0]['show_month'] = 1;
                }
                for(var i=0; i+1<date_line.length; i++){
                    if(M.time.year_to_month(date_line[i]) != M.time.year_to_month(date_line[i+1])){
                        result['res'][i+1]['show_month'] = 1;
                    }else {
                        result['res'][result['res'].length-1]['show_month'] = 0;
                    }
                }
                // prev_page_end = result['res'][result['res'].length-1]['dateline']; //

                template.helper("dateFormat", dateFormat);
                $('.expense-list').append(template('list', result));
                if(drop_list_flag){
                    dropList();
                }
            }else{
                config_tips.msg = res.msg;
                M.util.popup_tips(config_tips);
            }
        })
    }
    renderData();

//分页
    function dropList() {
        //加载更多
        var counter = 1;

        var dropload = $('.ac-list').dropload({
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

                    dao.getAccountList(param, function (res) {
                        if(res.status == 1){
                            var result = [];
                            var data = res['data'];
                            result['res'] = data['flows'];

                            var date_line = [];
                            for(var i=0; i<result['res'].length; i++){
                                next_page_first = result['res'][0]['dateline']; //下一页的第一个
                                date_line[i] = result['res'][i]['dateline'];
                            }

                            //todo... 需要在当前加载函数中判断加载当前页的第最后一个和下一页的第一个是否是在同一个月份，否则会出显示两个月份的

                            for(var i=0; i+1<date_line.length; i++){
                                if(M.time.year_to_month(date_line[i]) != M.time.year_to_month(date_line[i+1])){
                                    result['res'][i+1]['show_month'] = 1;
                                }else {
                                    result['res'][i+1]['show_month'] = 0;
                                }
                            }
                            setTimeout(function () {
                                template.helper("dateFormat", dateFormat);
                                $('.expense-list').append(template('list', result));
                                me.resetload();
                            }, 500);
                            if (data['flows'] == 0) {
                                me.lock();
                                me.noData();
                                return;
                            }
                        }else{
                            config_tips.msg = res.msg;
                            M.util.popup_tips(config_tips);
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
