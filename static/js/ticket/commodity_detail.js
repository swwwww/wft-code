/**
 * Created by deyi on 2016/8/31.
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
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    var config = {
        title: '注意事项',
        notice : '',
        height : 800,
        width : 600,
        top : '10%',
        is_border: false
    };

    window.onload = function(){
        var packages = $('.package');
        var place = $('.place');
        var lens_packages = packages.find('.package-list-item').length;
        var lens_place = place.find('.place-list-item').length;
        if(lens_packages == 0){
            packages.remove();
        }

        if(lens_place == 0){
            place.remove();
        }
    };
    //tab切换
    $('.nav').on(M.click_tap,'li',function(){
        var itemIndex =$(this).index(),
            totalHeight = $('.wrapper').height() + $('#edit_talk').height() + $('.tips').height() + $('.package').height() + $('.place').height() + 80;
        $(this).find('a').addClass('active');
        $(this).siblings('li').find('a').removeClass('active');
        $('.adv').eq(itemIndex).show().siblings('.adv').hide();
        $('body').scrollTo({toT:(totalHeight - 140),durTime:200});
    });

    //点赞和取消点赞
    $('.like').on(M.click_tap,function(e){
        e.preventDefault();
        var obj = $(this);
        var mid = obj.attr('data-mid');
        if(obj.hasClass('active')){
            var dao_del = DAO.delPost;
            var param_del = {
                'mid':mid
            };
            dao_del.getAllCommodityDel(param_del, function (res) {
                if(res.status == 1){
                    var data = res['data'];
                    if(data.status == 0){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                    }else if(data.message == '已经取消过了'){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                    }else{
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                        obj.removeClass('active');
                        var num = obj.text();
                        obj.text(parseInt(num)-1);
                    }
                }else{
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                }
            });
        }else{
            var dao_send = DAO.sendPost;
            var param_send = {
                'mid':mid
            };
            dao_send.getAllCommoditySend(param_send, function (res) {
                if(res.status == 1){
                    var data = res['data'];
                    if(data.status == 0){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                    }else{
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                        obj.addClass('active');
                        var num = obj.text();
                        obj.text(parseInt(num)+1);
                    }
                }else{
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                }
            });
        }
    });

    //咨询
    $('#consult_btn').on(M.click_tap,function(e){
        e.preventDefault();
        var obj = $(this);
        var gid = obj.attr('data-id');
        var mes = $('#textarea').val();

        if(mes){
            var dao = DAO.consult;
            var param = {
                'gid':gid,
                'message':mes
            };
            dao.getAllCommodityConsult(param, function (res) {
                if(res.status == 1){
                    var data = res['data'];
                    if(data.status == 0){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                    }else{
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                        window.location.reload();
                    }
                }else{
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                }
            });
        }
        else{
            config_tips.msg = '内容不能为空';
            M.util.popup_tips(config_tips);
        }
    });

    //二次评论链接跳转
    $('.comment').on(M.click_tap,function(e){
        e.preventDefault();
        var obj =$(this);
        var pid = obj.attr('data-id');
        window.location.href='/comment/recomment?type=2&pid='+pid
    });

    //精选套系高亮显示


    //点击收藏
    $('.collect-btn').on(M.click_tap,function(e){
        e.preventDefault();
        var collect = $('#collect'),
            id = $('#commodity_id').val();
        if(collect.hasClass('active')){
            var dao_del = DAO.collect,
                act_del = 'del',
                param_del = {
                    'link_id':id,
                    'type':'good',
                    'act':act_del
                };
            dao_del.getAllCollect(param_del, function (res) {
                if(res.status == 1){
                    var data = res['data'];
                    if(data.status == 0){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                    }else{
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                        collect.removeClass('active');
                        window.location.reload();
                    }
                }else{
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                }

            });
        }else{
            var dao_add = DAO.collect,
                act_add = 'add',
                param_add = {
                    'link_id':id,
                    'type':'good',
                    'act':act_add
                };
            dao_add.getAllCollect(param_add, function (res) {
               if(res.status == 1){
                   var data = res['data'];
                   if(data.status == 0){
                       config_tips.msg = data.message;
                       M.util.popup_tips(config_tips);
                   }else{
                       config_tips.msg = data.message;
                       M.util.popup_tips(config_tips);
                       collect.addClass('active');
                       window.location.reload();
                   }
               }else{
                   config_tips.msg = res.msg;
                   M.util.popup_tips(config_tips);
               }
            });
        }
    });

    //点评以及咨询触发事件
    $('.consult').on(M.click_touchend,function(e){
        e.preventDefault();
        $('.nav').find('li').eq(2).trigger(M.click_tap);
    });

    //引导分享按钮
    $('.share').on(M.click_touchend,function(){
       $('.share-popup').show();
    });

    $('.picture').on(M.click_touchend,function(){
        $('.share-popup').hide();
    });

    //商品套系开始时间大于当前时间
    function getTimeoutHtml(time_end){
        var time_start = new Date().getTime(); //设定当前时间
        // 计算时间差
        var time_distance = parseInt(time_end*1000) - time_start;

        if(time_distance <= 0){
            return false;
        }
        time_distance = parseInt(time_end)*1000 - time_start;

        // 天
        var int_day = Math.floor(time_distance/86400000);
        time_distance -= int_day * 86400000;
        // 时
        var int_hour = Math.floor(time_distance/3600000);
        time_distance -= int_hour * 3600000;
        // 分
        var int_minute = Math.floor(time_distance/60000);
        time_distance -= int_minute * 60000;
        // 秒
        var int_second = Math.floor(time_distance/1000);

        if(int_hour < 10){
            int_hour = "0" + int_hour;
        }
        if(int_minute < 10){
            int_minute = "0" + int_minute;
        }
        if(int_second < 10){
            int_second = "0" + int_second;
        }


        var html_time = '';

        html_time = '<span class="to-day">' + int_day + '天' + '</span>' + '<span class="to-hour">' + int_hour + '时' + '</span>'
        + '<span class="to-min">' + int_minute + '分' + '</span>'
        + '<span class="to-sec">' + int_second + '秒' + '</span>'
        + '<span>'+'后开抢'+'</span>';

        return html_time;
    }
    setInterval(function(){
        $('.js-timeout').each(function(){
            var $this = $(this);
            var time = parseInt($this.attr('data-time'));
            var result = getTimeoutHtml(time);
            if(result){
                $this.html(result);
            }
        });
    },1000);


    //套系弹窗
    $('.package').on(M.click_tap,'.package-list-item',function(){
        var popup_title = $(".package-popup-title"),
            popup = $(".package-popup"),
            btn = $('.goBuy'),
            way = $(this).attr('data-way'),
            back = $(this).attr('data-back'),
            info = $(this).attr('data-info'),
            price = $(this).attr('data-price'),
            score = $(this).find('.item-info-score').text(),
            title = $(this).attr('data-title'),
            num = $(this).attr('data-num'),
            group = $(this).attr('data-group'),
            goBuy = $(this).find('.item-btn').text(),
            scr = $(this).find('.item-btn').attr('href');

        btn.removeClass('active');

        if(!scr){
            btn.addClass('active');
        }

        if(score){
            popup.find(".total").find("mark").text('￥'+price);
            popup.find(".total").find("span").empty();
            popup.find(".total").find("span").text(score);
        }else{
            popup.find(".total").find("mark").text('￥'+price);
            popup.find(".total").find("span").empty();
        }

        popup_title.find("p").text(title);
        popup_title.find("span").text('仅剩'+num+'张');
        $('.exchangeIntro').text(way);
        $(".specialIntro").text(info);
        $(".refundIntro").text(back);
        btn.text(goBuy);
        btn.attr('href',scr);
        popup.show();
        $(".popup-matte").show();
    });

    $(".crossBtn").on(M.click_touchend,function(e){
        e.preventDefault();
        $(".package-popup").hide();
        $(".popup-matte").hide();
    });

    $('.item-btn').on(M.click_tap,function(e){
        e.stopPropagation();
    });

    //注意事项
    $('.attention').on(M.click_tap,function(){
        var notice = $(this).find('span').text();
        config['notice']= notice;
        M.util.popup_notice(config);
    });

    //评论加载
    var counter = 1;
    var object_id = $('#commodity_id').val();

    var dropload = $('#module_rated').dropload({
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
                    'page_num': 10,
                    'object_id':object_id,
                    'type':2
                };

                dao.getCommentList(param, function (res) {
                    console.log(res);
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

    //获得verify_code seller_id参数
    var verify_code = $('#verify_code').val();
    var seller_id = $('#seller_id').val();
    var wx_verify_code = 0;
    if(verify_code != 0){
        wx_verify_code = 'wx_' + verify_code;
    }

    if(localStorage.getItem('verify_code') == undefined && localStorage.getItem('seller_id') == undefined){
        localStorage.setItem('verify_code',wx_verify_code);
        localStorage.setItem('seller_id',seller_id);
    }else{
        if(localStorage.getItem('verify_code') != wx_verify_code){
            localStorage.setItem('verify_code',wx_verify_code);
        }
        if(localStorage.getItem('seller_id') != seller_id){
            localStorage.setItem('seller_id',seller_id);
        }
    }
})();

function highLight(){
    var obj = $('.package'),
        cover = $('.cover'),
        body = $('body'),
        totalHeight = $('.wrapper').height() + $('#edit_talk').height() + $('.tips').height() -20;
    obj.css({
        'width':'746px',
        'border':'2px solid #fa6e51',
        'z-index':'15'
    });
    cover.show();
    body.scrollTo({toT:totalHeight});
    body.css({
        'overflow':'hidden'
    });
    body.bind('touchmove',function(e){
        e.preventDefault();
    });

    cover.on(M.click_touchend,function(e){
        e.preventDefault();
        obj.css({
            'width':'750px',
            'border':'0',
            'z-index':'0'
        });
        cover.hide();
        body.css({
            'overflow':'auto'
        });
        body.off('touchmove');
    });
}











