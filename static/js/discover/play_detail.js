/**
 * Created by deyi on 2016/8/24.
 */
function GetQueryString(name) {
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return decodeURI(r[2]); return null;
}

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
    var nav = $('.nav');
    var id = GetQueryString('id');
    var itemIndex = 0;
    var totalHeight = $('.head').height() + 20;
    var a_location = $('#location');
    var a_x = a_location.attr('data-x');
    var a_y = a_location.attr('data-y');
    var a_addr = a_location.attr('data-addr');
    var a_title = a_location.attr('data-title');
    var rate = $('.rated-list');
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    function renderData(){
        var dao = DAO.discover;
        var param = {                                                                   
            'id':id,
            'page': 1
        };
        dao.getAllPlayDetail(param, function (res) {
            if(res.status == 1){
                var data =res['data'];
                var result_good = [];
                var result_place=[];
                var result_post=[];
                result_good['good_list'] = data['good_list'];
                result_place['place_list'] =data['place_list'];
                result_post['post_list'] =data['post_list'];
                template.helper("dateFormat", dateFormat);
                $('.ticket-list').append(template('good_list', result_good));
                $('.like-list').append(template('place_list', result_place));
                $('.rated-list').append(template('post_list', result_post));
            }else{
                config_tips.msg = res.msg;
                M.util.popup_tips(config_tips);
            }
        });
    }

    //初始化
    createMap(a_x,a_y,a_addr,a_title);
    renderData();

    nav.on(M.click_tap,'li',function(){
        var $this =$(this),
            itemIndex = $this.index();
        $this.find('a').addClass('active');
        $this.siblings('li').find('a').removeClass('active');
        $('.adv').eq(itemIndex).show().siblings('.adv').hide();
        $('body').scrollTo({toT:totalHeight,durTime:200});
    });

    $('.enroll-fixed').on(M.click_touchend,function(e){
        e.preventDefault();
        $('.nav').find('li').eq(1).trigger(M.click_tap);
    });

    $('.map-nav').on(M.click_tap,'.item',function(){
        var $this =$(this),
            itemIndex = $this.index();
        $this.addClass('active').siblings('.item').removeClass('active');
        $('.location-list').eq(itemIndex).show().siblings('.location-list').hide();
    });

    $('.location-list-item').on(M.click_tap,function(){
        var $this = $(this),
            addr_x = $this.attr('data-x'),
            addr_y = $this.attr('data-y'),
            shop_address = $this.attr('data-addr'),
            shop_name = $this.attr('data-name');
        $this.addClass('active').siblings().removeClass('active');
        nav.find('li').eq(3).trigger(M.click_tap);
        createMap(addr_x,addr_y,shop_address,shop_name);
    });

    //点赞和取消点赞
    rate.on(M.click_tap,'.like',function(e){
        e.preventDefault();
        var obj = $(this);
        var pid = obj.attr('data-id');
        if(obj.hasClass('active')){
            var dao_del = DAO.like;
            var param_del = {
                'pid':pid
            };
            dao_del.getAllPostDel(param_del, function (res) {
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
            var dao_send = DAO.like;
            var param_send = {
                'pid':pid
            };
            dao_send.getAllPostLike(param_send, function (res) {
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

    //二次评论链接跳转
    rate.on(M.click_tap,'.comment',function(e){
        e.preventDefault();
        var obj =$(this);
        var pid = obj.attr('data-id');
        window.location.href='/comment/recomment?type=3&pid='+pid
    });

   

})();






