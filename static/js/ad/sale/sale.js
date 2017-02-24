/**
 * Created by Administrator on 2016/10/20 0020.
 */
//定位城市
window.onload = function(){
    var city = M.location.getCustomCity();
    $('.select-city').val(city);
};

//选择设置城市
$('.select-city').change(function () {
    var select_city = $('option').not(function(){ return !this.selected }).text();
    M.location.setCustomCity(select_city)
    $('.select-city').val(select_city);
    window.location.reload();
});


(function () {
    var lottery_vo = JSON.parse($('#hide_lottery_vo').val()),
        taobao_info = lottery_vo.taobao_sale,
        taobao_date = taobao_info.is_taobao_date,
        during_hour = taobao_info.taobao_during;

    var charge_H = $('#header-section').height(),
        seckill_H = 0;
    if(taobao_date == true){ //没有充值
        seckill_H = charge_H + $('#seckill').height()-70;
    }else {
        seckill_H = charge_H + $('#charge').height()-50;
    }
    var play_H = seckill_H  + $('#rob').height()+35,
        fresh_H = play_H + $('#play').height() + 20;

    //头部nav切换及滑动效果
    $('body').on('click', '.a-item' ,function () {
        var $this = $(this),
            href = $this.attr('data-href');

        $this.addClass("cur-line");
        $this.siblings().removeClass("cur-line");
        if(href == 0){
            $('body').scrollTo({toT:charge_H, durTime:200});
        }else if(href == 1){
            $('body').scrollTo({toT:seckill_H, durTime:200});
            // $('body').scrollTo({toT:rob_H, durTime:200});
        }else if(href == 2){
            $('body').scrollTo({toT:play_H, durTime:200});
        }else if(href == 3){
            $('body').scrollTo({toT:fresh_H, durTime:200});
        }
    });

// 随着滑动nav导航固定在头部
    $(window).scroll(function() {
        var scroll_top = 0;
        var olott = $('#header-section');
        var lottHeight = olott.height();
        scroll_top = M.util.getScrollTop(); //实时滑动的高度
        // console.log('scroll_top: '+scroll_top);
        // console.log('seckill_H: '+seckill_H);
        // console.log('play_H: '+play_H);
        // console.log('fresh_H: '+fresh_H);


        // if(scroll_top < seckill_H){
        //     $('.item-one').addClass("cur-line");
        //     $('.item-one').siblings().removeClass("cur-line");
        // }else if(scroll_top+20 > seckill_H && scroll_top < play_H){
        //     $('.item-two').addClass("cur-line");
        //     $('.item-two').siblings().removeClass("cur-line");
        // }else if(scroll_top > play_H && scroll_top <  fresh_H){
        //     $('.item-three').addClass("cur-line");
        //     $('.item-three').siblings().removeClass("cur-line");
        // }else if(scroll_top > fresh_H){
        //     $('.item-four').addClass("cur-line");
        //     $('.item-four').siblings().removeClass("cur-line");
        // }

        if (scroll_top > lottHeight) {
            $("#sale-nav").css({
                position : 'fixed',
                top : '0',
                zIndex: '1'
            });
        } else {
            $("#header-section").css({
                position : 'static'
            });
            $("#sale-nav").css({
                position : 'static'
            });
            $("#header-section").css('display', 'block');
        }
    });

    function seckil_show(item_value) {
        if(item_value == 10){
            $('.ten-clock').show();
            $('.ten-clock').siblings().hide();
        }else if(item_value == 16) {
            $('.four-clock').show();
            $('.four-clock').siblings().hide();
        }else if(item_value == 21){
            $('.twenty-clock').show();
            $('.twenty-clock').siblings().hide();
        }
    }

    //秒杀开始显示的
    seckil_show(during_hour);

//秒杀nav切换效果
    $('body').on(M.click_tap, '.clock-item', function () {
        var during_hour = taobao_info.taobao_during;
        var click_data = $(this).attr('click-data');
        seckil_show(click_data);

        $(this).addClass("during-rob");
        $(this).siblings().removeClass("during-rob");
    });

    //点击分享按钮事件
    $('.share-btn').on('click', function() {
        var app = 0;
        var title = wechat_share.share_app_message.title;
        var content = wechat_share.share_app_message.message;
        var url = wechat_share.share_app_message.link;
        var img = wechat_share.share_app_message.img_url;

        if (M.browser.ios) {
            var share_url = 'webshare$$app=' + app + '&title=' + title + '&url=' + url + '&img=' + img + '&content=' + content;
            window.location.href = share_url;
        } else {
            window.getdata.webShare(app, url, title, content, img);
        }
    });

})();
