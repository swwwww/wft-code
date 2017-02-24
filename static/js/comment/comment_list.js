/**
 * Created by Administrator on 2016/10/14 0014.
 */
//template时间转换函数
function dateFormat(date, format){
    date = new Date(date*1000);
    var map = {
        "M": date.getMonth() + 1, //月份
        "d": date.getDate(), //日
        "h": date.getHours(), //小时
        "m": date.getMinutes(), //分
        "s": date.getSeconds(), //秒
        "q": Math.floor((date.getMonth() + 3) / 3), //季度
        "S": date.getMilliseconds() //毫秒
    };

    format = format.replace(/([yMdhmsqS])+/g, function(all, t){
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

(function(){
    var counter = 1;
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    var type = $('#type').val();
    var object_id = $('#object_id').val();

    var dropload = $('.adv').dropload({
        scrollArea : window,
        domDown : {
            domClass: 'dropload-down',
            domRefresh: '<div class="dropload-refresh" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">↑上拉加载更多</div>',
            domLoad: '<div class="dropload-load" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;"><span class="loading"></span>加载中...</div>',
            domNoData: '<div class="dropload-noData" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">已经全部加载完毕</div>'
        },
        //上拉加载函数
        loadDownFn : function(me){
            counter++;
            function renderMore(){
                var dao = DAO.comment;
                var param = {
                    'page': counter,
                    'object_id':object_id,
                    'type':type
                };
                dao.getCommentList(param, function (res) {
                    if(res.status == 1){
                        var data = res['data'];
                        var result = [];
                        result['res'] = data['post'];
                        setTimeout(function(){
                            template.helper("dateFormat", dateFormat);
                            $('.rated-list').append(template('more', result));
                            me.resetload();
                        },500);
                        if(data['post'].length == 0){
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

})();
