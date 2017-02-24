/**
 * Created by deyi on 2016/10/18.
 */
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

var itemIndex = 0,
    dao = DAO.user,
    type = 'good',
    align = $('.align'),
    item = $(".item");

item.on(M.click_tap, function () {
    var $this = $(this);
    itemIndex = $this.index();
    $('.dropload-down').remove();
    align.eq(itemIndex).empty();
    $this.addClass('active').siblings('.item').removeClass('active');
    align.eq(itemIndex).show().siblings('.align').hide();

    if(itemIndex == 0){
        type = 'good'
    }else if(itemIndex == 1){
        type = 'kidsplay'
    }else if(itemIndex == 2){
        type = 'shop'
    }
    dataRender(itemIndex,type);
});

function dataRender(itemIndex,type){
    var counter = 1;
    var param_info = {
        'p':1,
        'type':type
    };
    dao.getAllUserCollect(param_info, function (res) {
        var result = [];
        var data =res['data'];
        result[type] = data;
        template.helper("dateFormat", dateFormat);
        if(result[type].length == 0){
            var html = '<div class="no-data">' + '<img src="/static/img/site/mobile/nodata.gif" />' + '</div>';
            align.eq(itemIndex).append(html);
        }else{
            align.eq(itemIndex).append(template(type+'_list', result));
            drop_more();
        }
    });

    function drop_more(){
        var dropload = $('.inner').dropload({
            scrollArea: window,
            domDown: {
                domClass: 'dropload-down',
                domRefresh: '<div class="dropload-refresh" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;margin-bottom: 100px;">↑上拉加载更多</div>',
                domLoad: '<div class="dropload-load" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;margin-bottom: 100px;"><span class="loading"></span>加载中...</div>',
                domNoData: '<div class="dropload-noData" style="font-size: 20px;height:50px;line-height:50px;text-align: center;margin-bottom: 100px;">已经全部加载完毕</div>'
            },
            loadDownFn: function (me) {
                counter++;
                var param_info_more ={
                    'p':counter,
                    'type':type
                };

                dao.getAllUserCollect(param_info_more, function (res) {
                    var result = [];
                    var data =res['data'];
                    result['type'] = data;
                    var lens = result['type'].length;

                    setTimeout(function () {
                        template.helper("dateFormat", dateFormat);
                        align.append(template(type+'_more', result));
                        me.resetload();
                    }, 200);

                    if (lens == 0) {
                        me.lock();
                        me.noData();
                        return;
                    }
                });
            }
        });
    }
}
dataRender(itemIndex,type);