/**
 * Created by Administrator on 2016/11/17 0017.
 */

(function () {
    var lottery_vo = JSON.parse($('#hide_lottery_vo').val()),
        lottery_total = lottery_vo.op_total,
        lottery_id = lottery_vo.lottery_id,
        reward_status = 0,
        cash_name = '', //奖品名
        config_tips = {
            msg: '提示信息',
            padding_tb: '4%',
            padding_rl: '4%',
            top: '40%',
            font_size: 28,
            time: 2500,
            z_index: 100
        },
        flag_cut = false;  //限制回调中请求奖券数据一次

        //没机会去刮奖区
    $('#js-no-chance').bind('touchmove', function () {
        config_tips.msg = '您已经没有刮奖机会，下次再来吧！';
        M.util.popup_tips(config_tips);
    });
    //再刮一次
    $('body').on(M.click_tap, '.try-again', function () {
        window.location = window.location.href;
    });

    $('body').on(M.click_tap, '.matte', function () {
        $('.matte').hide();
        $('.pop-share').hide();
        $('.share-pic').hide();
    });

    //刮奖回调
    function doingScrape(precent) {
        var progress = parseInt(precent, 10);
        var flag = function () {
            if(progress>4){ //防止用户误操作而损耗刮奖机会
                return true;
            }else{
                return false;
            }
        };
        if(flag_cut && flag){
            flag_cut = false;
            var dao = DAO.member,
                post_data = {
                    lottery_id: lottery_id
                };
            lottery_total --; //次数-1
            dao.lucky(post_data, function (res) {
                    if(res['status'] == 1){
                        reward_status = 1;
                        var cash_vo = res;
                        var lottery_cash_id = cash_vo['id'];
                        cash_name = cash_vo['data'];
                        $('.lottery-value').text(cash_name);
                        setTimeout(function () {
                            $('#js-pop-win').show();
                            $('.matte').show();
                        }, 2500);
                    }else {
                        //没有中奖
                        $('.lottery-value').text(0);
                        setTimeout(function () {
                            $('#js-pop-win').show();
                            $('.matte').show();
                        }, 2500);
                    }
                });
        }

        if(progress > 60){
            $('.mask-cover').hide();
            switch (reward_status){ //中奖的状态，参考sale_game.js判断

            }
        }

    }
    
    window.onload = function () {
        if(lottery_total){
            flag_cut = true;
            var lottery = new Lottery('lottery-container',
                '/static/img/cash/can_win_prize.png',
                'image', 620, 220, doingScrape);
                lottery.init('/static/img/cash/lottery_bg.png', 'image'); /*Math.random() 奖券初始化*/
            // $('.lottery-value').text(parseFloat(Math.random()).toFixed(3)); //test
        }
    }
})();
