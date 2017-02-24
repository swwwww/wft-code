/**
 * Created by asus on 2017/1/11.
 */
(function () {
    //小屏手机页面适配
    window.onload = function(){
        if(screen.availHeight<500){
            $('.icon-head').css({
                'zoom': '0.85',
                'left': '10%'
            });

            $('.start-ufo').css({
                'height': '502px'
            });

            $('.lose-cry').css({
                'bottom': '-67px'
            });

            $('.new-year').css({
                'margin' : '0px',
                'font-size': '45px'
            });
            $('.hurry-up').css({
                'margin' : '0px'
            })
        }
    }

    //app 分享
    function share_func() {
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
    }

    var lottery_vo = JSON.parse($('#hide_lottery_vo').val()),
    config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '20%',
        font_size: 28,
        time: 2500,
        z_index: 22222
    };

    //游戏规则
    $('body').on(M.click_tap, '#js_rule_show', function () {
        $('.matte').show();
        $('#rule').show();
        if(screen.availHeight<500){
            $('.js-start-game').css({
                'top': '85%',
                'z-index': '10'
            })
        }else {
            $('.js-start-game').css({
                'top': '80%',
                'z-index': '10'
            })
        }

    });

    //判断用户是否登录并跳转相关的页面
    $('body').on(M.click_tap, '.js-login-in', function(e) {
        e.preventDefault();
        var type = parseInt($(this).attr('data-type'), 10);
        var chance = parseInt($(this).attr('data-chance'), 10);
        var is_end = $(this).attr('data-end');
        var login_url = base_url_module + 'lottery/login/lottery_id/' + lottery_vo.lottery_id;
        var wft = $(this).attr('data-wft');
        var record_url = ''
        if (!M.util.checkLogin($(this), login_url)) {
            return false;
        }

        if(type == 1){/*去抽奖*/
            record_url = base_url_module + 'lottery/market/lottery_id/'+lottery_vo.lottery_id+'/luck/1';
        }else if(type == 2){/*去奖品页*/
            record_url = base_url_module + 'lottery/record/lottery_id/'+lottery_vo.lottery_id;//我的奖品页
        }else if(type == 3){ /*去游戏页*/
            if(is_end == "yes"){
                alert('活动已经截止！')
            }else {
                record_url = base_url_module + 'lottery/game/lottery_id/' + lottery_vo.lottery_id;
            }
        }
        if(type != 3){
            window.location.href = record_url;
        }else {
            if(chance){
                window.location.href = record_url;
            }else{
                if(wft){
                    share_func();
                    config_tips.msg = '您的游戏机会已用完，赶快分享获取游戏机会！';
                    M.util.popup_tips(config_tips);
                }else {
                    $('.matte').show();
                    $('.js-share-to').show();
                    $('#rule').hide();
                    $('.js-start-game').css({
                        'top': '42%',
                        'z-index': '2'
                    });
                }
            }
        }

    });

    /*分享按钮*/
    $('body').on(M.click_tap, '.js-share-btn', function () {
        $('.score-prise').hide();
        $('.matte').show();
        $('#resultPanel').hide();
        $('.js-share-to').show();
        $('#container').css({
            'overflow': 'hidden'
        });
        $('.js-share-to').css({
            'top': '0px'
        });
    });

    // 点击分享按钮事件
    $('.icon-share-btn').on('click', function() {
        share_func();
    });

    /*关闭蒙层*/
    $('body').on(M.click_tap, '.js-close-btn', function () {
        $('.matte').hide();
        $('.js-share-to').hide();
        $('#rule').hide();
        $('.js-start-game').css({
            'top': '42%',
            'z-index': '2'
        });
        window.location.href = window.location;

    });

    //再抽奖一次
    $('#js-lottery-again').on(M.click_touchend, function () {
        M.load.loadTip('loadType', '数据请求中...', 'delay');
        var record_url = base_url_module + 'lottery/market/lottery_id/'+lottery_vo.lottery_id+'/luck/1';
        window.location.href = record_url;
    })

})();


