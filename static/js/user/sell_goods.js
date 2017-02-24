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

function GetQueryString(name) {
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return decodeURI(r[2]); return null;
}




var sell_type = "activity",
    itemIndex = 0,
    counter = 1,
    dao = DAO.seller,
    activity_num = $('#activity_num').val(),
    goods_num = $('#goods_num').val(),
    is_seller  = $('#is_seller').val(),
    nav = $('.header-nav'),
    uid = GetQueryString('seller_id');

var config_tips = {
    msg: '提示信息',
    padding_tb: '4%',
    padding_rl: '4%',
    top: '50%',
    font_size: 28,
    time: 2500,
    z_index: 2000
};

nav.on(M.click_tap,function(){
    if($(this).hasClass('active')){
        $('.header-content').hide();
        $(this).removeClass('active');
    }else{
        $('.header-content').show();
        $(this).addClass('active');
    }
});

if(activity_num == 0){
    $('.category').find('.item').eq(0).prop('disabled',true).addClass('disabled');
    $('.matte-l').show();
}

if(goods_num == 0){
    $('.category').find('.item').eq(1).prop('disabled',true).addClass('disabled');
    $('.matte-r').show();
}

$('.header-main').on(M.click_touchend,'.item',function(e){
    e.preventDefault();
    if($(this).text() == '遛娃活动'){
        sell_type = 'activity';
        itemIndex = 0;
        nav.text($(this).text());
        $('.adv').eq(0).empty().show().siblings().hide();
    }else{
        sell_type = 'goods';
        itemIndex = 1;
        nav.text($(this).text());
        $('.adv').eq(1).empty().show().siblings().hide();
    }

    $(this).addClass('active').siblings().removeClass('active');
    $('.header-content').hide();
    nav.removeClass('active');
    dataRender(itemIndex,sell_type);
});

function dataRender(itemIndex,sell_type){
    var param_info = {
        'page':1,
        'sell_type':sell_type,
        'is_seller': is_seller,
        'seller_id':uid
    };

    dao.sellGoods(param_info, function (res) {
        if(res.status ==1){
            var result = [];
            var data = res['data'];
            result[sell_type] = data;
            template.helper("dateFormat", dateFormat);
            $('.adv').eq(itemIndex).append(template(sell_type+'_list', result));
        }else{
            config_tips.msg = res.msg;
            M.util.popup_tips(config_tips);
        }
    });

    var dropload = $('.main').dropload({
        scrollArea: window,
        domDown: {
            domClass: 'dropload-down',
            domRefresh: '<div class="dropload-refresh" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;margin-bottom: 100px;">↑上拉加载更多</div>',
            domLoad: '<div class="dropload-load" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;margin-bottom: 100px;"><span class="loading"></span>加载中...</div>',
            domNoData: '<div class="dropload-noData" style="font-size: 20px;height:50px;line-height:50px;text-align: center;margin-bottom: 100px;">全部加载完毕</div>'
        },
        loadDownFn: function (me) {
            counter++;
            param_info['page'] = counter;
            dao.getAllSellGoods(param_info, function (res) {
                if(res.status ==1){
                    var result = [];
                    var data = res['data'];
                    result['sell_type'] = data;

                    setTimeout(function () {
                        template.helper("dateFormat", dateFormat);
                        $('.adv').eq(itemIndex).append(template(sell_type+'_more', result));
                        me.resetload();
                    }, 500);

                    if (result['sell_type']) {
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
    });
}
dataRender(itemIndex,sell_type);
