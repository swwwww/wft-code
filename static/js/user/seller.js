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

function show_date(){
    var d = new Date();
    var vYear = d.getFullYear();
    var vMon = d.getMonth() + 1;
    var vDay = d.getDate();
    var h = d.getHours();
    var m = d.getMinutes();
    var se = d.getSeconds();
    s=vYear+'-'+(vMon<10 ? "0" + vMon : vMon)+'-'+(vDay<10 ? "0"+ vDay : vDay);
    return s;
}

//获取当前日期
function show_hours(){
    var d = new Date();
    var vYear = d.getFullYear();
    var vMon = d.getMonth() + 1;
    var vDay = d.getDate();
    var h = d.getHours();
    var m = d.getMinutes();
    var se = d.getSeconds();
    t=(h<10 ? "0"+ h : h)+':'+(m<10 ? "0" + m : m)+':'+(se<10 ? "0" +se : se);
    return t;
}

var dao = DAO.seller,
    main_list = $('.main-list'),
    page_num = main_list.children().length,
    balance = $('.balance').attr('data-item'),
    minBalance = $('.popup-spread-title').attr('data-low'),
    matte = $('.matte'),
    state = $('#state').val();

var config_tips = {
    msg: '提示信息',
    padding_tb: '4%',
    padding_rl: '4%',
    top: '50%',
    font_size: 28,
    time: 2500,
    z_index: 2000
};

main_list.find('.item-info-time').each(function(){
    if($(this).text() == show_date()) {
        $(this).text($(this).attr('data-time'));
    }
});

$('.header-btn-cash').on(M.click_tap,function(){
    if(state == 1){
        $('.popup-warn').show();
        matte.show();
        $('.popup-warn-btn').on(M.click_tap,function(){
            $('.popup-warn').hide();
            matte.hide();
            window.location.href = '/seller/sellgoods';
        });

        matte.on(M.click_tap,function(){
            $('.popup-warn').hide();
            matte.hide();
        })
    }else{
        if(parseFloat(balance) < parseFloat(minBalance)){
            $('.popup-spread').show();
            matte.show();
            matte.on(M.click_tap,function(){
                $('.popup-spread').hide();
                matte.hide();
            });

            $('.popup-spread-btn').on(M.click_tap,function(){
                $('.popup-spread').hide();
                matte.hide();
                window.location.href = '/seller/sellgoods';
            })
        }else{
            var total = balance - balance % 10;
            $('.popup-withdraw-title').text('本次提现金额为'+total+'元，确定提现'+total+'元？');
            $('.popup-withdraw').show();
            matte.show();

            $('.cancel').on(M.click_tap,function(){
                $('.popup-withdraw').hide();
                matte.hide();
            });

            matte.on(M.click_tap,function(){
                matte.hide();
                $('.popup-withdraw').hide();
            });

            $('.confirm').on(M.click_touchend,function(e){
                    e.preventDefault();
                    $('.popup-withdraw').hide();
                    matte.hide();
                var param_cash ={
                    'money':total
                };

                dao.getAllSellCash(param_cash, function (res) {
                    if(res.status ==1){
                        var data = res['data'];
                        if(data.status == 1){
                            $('.popup-tip').show();
                            matte.show();
                            matte.on(M.click_tap,function(){
                                $('.popup-tip').hide();
                                matte.hide();
                                window.location.reload();
                            });
                            $('.popup-tip-btn').on(M.click_tap,function(){
                                $('.popup-tip').hide();
                                matte.hide();
                                window.location.reload();
                            });
                        }else{
                            config_tips.msg = data.message;
                            M.util.popup_tips(config_tips);
                        }
                    }else{
                        config_tips.msg = res.msg;
                        M.util.popup_tips(config_tips);
                    }
                });
            })
        }
    }
});

if(page_num >= 10){
    var counter = 1;
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
            var param_info = {
                'page':counter
            };
            dao.getAllSellInfo(param_info, function (res) {
                if(res.status ==1){
                    var data =res['data'];
                    var result = [];
                    result['income'] = data;
                    setTimeout(function () {
                        template.helper("dateFormat", dateFormat);
                        main_list.append(template('income_more', result));
                        me.resetload();
                    }, 500);
                    if (result['income']) {
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