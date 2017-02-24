/**
 * Created by xxxian on 2016/10/18 0018.
 */
(function(){
    var mask_cover = $('.matte'),
        rule_msg = $('#js_rule_msg'),
        tipDiaEle = $('.game_rule'),
        prise_in = $('#prise_in').val(), //是否获奖
        code_no = null, //复制的
        score_use = parseInt($('#js_my_score').text(), 10),//获取可用的积分
        frist_tips = $('#js_frist_in'), //首次进入就展示
        lottery_vo = JSON.parse($('#hide_lottery_vo').val()),
        is_frist_in = lottery_vo['is_first_game'],
        lottery_id = lottery_vo.lottery_id,
        resultEnd = $('.result-show'),
        config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '34%',
        font_size: 28,
        time: 2500,
        z_index: 100
    };

    prise_in = true;

    //判断用户登录
    $('body').on(M.click_tap, '#js_start', function(e) {
        e.preventDefault();
        var chance = parseInt($(this).attr('data-chance'), 10);
        var login_url = base_url_module + 'lottery/login/lottery_id/' + lottery_id;

        if (!M.util.checkLogin($(this), login_url)) {
            return false;
        }
        has_chance(chance);
    });

    //活动规则
    $('body').on(M.click_tap, '#js_rule', function () {
        mask_cover.show();
        rule_msg.show();
    });
    //我的奖品
    $('body').on(M.click_tap, '#js_go_prise', function () {
        if(is_frist_in == 'yes'){
            mask_cover.show();
            $('#js_frist_in').show();
            setTimeout(function () {
                mask_cover.hide();
                $('#js_frist_in').hide();
            }, 2000);
        }else {
            setTimeout(function(){ //我的奖品页面
                var record_url = base_url_module + 'lottery/record/lottery_id/' + lottery_id + '/luck/1';
                window.location.href = record_url;
            }, 700);
        }
    });
    //关闭按钮
    $('body').on(M.click_tap, '.js_btn_close', function () {
        mask_cover.hide();
        rule_msg.hide();
        $('#js_focous').hide();
        $('#js_score_not').hide();
        $('.good-luck').hide();
        $('#js_try_once').hide();
        $('#js-phone-in').hide();
        window.location.reload();
    });

    $('body').on(M.click_tap, '.close_focous', function () {
        $('#js_focous').hide();
        mask_cover.hide();
        setTimeout(function(){
            var record_url = base_url_module + 'lottery/market/lottery_id/' + lottery_id; //首页
            window.location.href = record_url;
        }, 400);
    });

    //关注我们
    $('body').on(M.click_tap, '.focous-us', function () {
        resultEnd.hide();
        $('#js_focous').show();
    });
    //关闭引导分享的
    $('body').on(M.click_tap, '.share-to', function () {
        $('.matte').hide();
        $('.share-to').hide();
    });

    //我的奖品页获取验证码  核销俄统一码：1118    //几古家实物输入验证码  待协商
    $('body').on(M.click_tap, '#js-get-code', function () {
       // bindPhone
       var code = $('.code-in').val(),
           dao = DAO.lottery,
           record_id =  $('.code-in').attr('data-id');

        if(!code){
            config_tips.msg = '请输入验证码';
            M.util.popup_tips(config_tips);
        }else{
            var params={
                'lottery_id': 3,
                'record_id' : record_id,
                'check_code': code
            }
            dao.acceptJiguRecord(params, function (res) {
                console.log(res);
                if(res.status == 1){
                    res = res['data'];
                   if(res.status == 1){
                       config_tips.msg = res.msg;
                       M.util.popup_tips(config_tips);
                       $('#js-get-code').text('已经领取');
                       $('#js-get-code').addClass('.bak');
                   }
                }else {
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                }
                setTimeout(function () {
                    window.location.reload();
                }, 2000);
            });
        }
    });

    //抽奖
    $('.prise-item').find('a').on(M.click_tap, function () {
        var phone = $('#hide_phone').val();

        if(phone){
            var dao = DAO.lottery;
            var params = {
                'lottery_id': 3
            }

            if(score_use >= 1){
                if($(this).hasClass('shaking')){
                    alert('您已经抽过一次啦！');
                    return false;
                }

                $(this).removeClass('prise-box');
                $(this).addClass('shake-box');
                $('.prise-item a').addClass('shaking');
                dao.lucky(params, function (prise_in) {
                    score_use = score_use - 1; //抽一次积分减一次 /*200*/
                    if(prise_in.status == 1){//中奖 true
                       var res = prise_in['data'];
                        $('#js_code_1').text(res['code']);
                        if(res.type == 1){ //开业体验券
                            setTimeout(function () {
                                mask_cover.show();
                                $('#js-code').show();
                            }, 800)
                        }else if(res.type == 2){//文具套装
                            setTimeout(function () {
                                mask_cover.show();
                                $('#js-tool').show();
                            }, 800)
                        }
                    }else { //没中奖
                        setTimeout(function () {
                            mask_cover.show();
                            $('#js_try_once').show(); //再来一次
                        }, 800);
                    }
                    $(this).removeClass('shake-box');
                });
            }else {
                mask_cover.show();
                $('#js_score_not').show();//积分不够
            }
        }else {
            $('#js-phone-in').show();
            $('.matte').show();
        }
    });

    //第一次进入我的奖品页面出行输入手机号码验证
    //校验并绑定号码
    $('body').on(M.click_tap, '#js-ph-login', function () {
        var flag =  phone_login.check(false);
        if(flag){
            var phone = $('#js_com_phone').val();
            var dao = DAO.lottery;
            var params = {
                'lottery_id': 3,
                'phone': phone
            };

            dao.bindJiguPhone(params, function (res) {
                console.log(res);
                if(res.status==1){
                    $('#js-phone-in').hide();
                    $('.matte').hide();
                    window.location.reload();
                }
            });
        }


    });

    //有游戏机会
    function has_chance(chance) {
        //判断用户玩游戏的机会
        if(chance){
            setTimeout(function(){
                var record_url = base_url_module + 'lottery/game/lottery_id/' + lottery_id;
                window.location.href = record_url;
            }, 300);
        }else {
            $('.matte').show();
            $('.share-to').show();
            setTimeout(function () {
                $('.matte').hide();
                $('.share-to').hide();
            }, 2500);
        }
    }

})();