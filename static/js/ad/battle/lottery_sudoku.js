/**
 * Created by Administrator on 2017/1/14 0014.
 */
(function () {
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '70%',
        font_size: 28,
        time: 2500,
        z_index: 22222
    };
    var cash_type = 0; //奖品类型
    //中奖处理函数
    function lottery_res(res) {
        console.log(res);
        if(res.status == 1){ //中奖
            var res = res['data'];
            cash_type = res['type'];
            var cash_name = res['cash_name'];
            var cash_alias = res['cash_alias'];
            var record_id = res['record_id'];
            var id = res['id'];

            console.log(id);
            $('.score-prise').show();
            $('.succ-prise').show();
            $('.matte').show();
            $('.cash-name').text(cash_name);
            $('.cash-desc').text(cash_alias);
            $('.js-get-cash').attr('data-name', cash_name);
            $('.js-get-cash').attr('data-type', cash_type );
            $('.js-get-cash').attr('data-record-id', record_id);
            $('.js-get-cash').attr('data-id', id);
        }else if(res.data == 100){ //没中奖
            $('.matte').show();
            $('.lose-prise').show();
        }else if(res.data == 101){ //中奖机会已用完
            config_tips.msg = res['msg'];
            M.util.popup_tips(config_tips);
        }else{
            config_tips.msg = res['msg'];
            M.util.popup_tips(config_tips);
        }
    }
    var tigerGame = {
        params: null,
        lottery_vo: JSON.parse($('#hide_lottery_vo').val()),
        prise: {},
        init: function() {
            tigerGame.params = {
                num: {
                    circle: 4,
                    lap: 8,
                    prize: 8
                },
                step: {
                    total: 0,
                    fast: 0,
                    slow: 8
                },
                speed: {
                    fast: 100,
                    slow: 200
                },
                index: 1
            }
        },
        monitor: function() {
            $('body').bind(M.click_tap, '.js-lottery-btn',tigerGame.startAction);
        },
        getPrize: function() {
            prize = Math.floor(Math.random() * 7 + 1);
        },
        startAction: function() {
            if(tigerGame.lottery_vo.game_score_total >= tigerGame.lottery_vo.spend_score){
                $('.js-game-total').text(tigerGame.lottery_vo.game_score_total - tigerGame.lottery_vo.spend_score);
                tigerGame.init();
                var dao = DAO.lottery,
                    params = {
                        'lottery_id' : 6
                    }
                dao.lucky(params, function (res) {
                    tigerGame.prise = res;
                });
                $('body').unbind(M.click_tap,'.js-lottery-btn');
                tigerGame.params.step.total = tigerGame.params.num.circle * tigerGame.params.num.lap + tigerGame.params.num.prize;
                tigerGame.goAction();
            }else{
            // 积分不足
                $('.js-game-total').text(tigerGame.lottery_vo.game_score_total);
                $('.matte').show();
                $('.score-prise').show();
                $('.fail-prise').show();
                if(tigerGame.lottery_vo.op_total){ //有游戏机会，再玩一次
                    $('.js-start-game').show();
                }else {//没有游戏机会，分享获得机会
                    $('.js-share-btn').show();
                    $('.icon-share-btn').show();
                }
            }

        },
        goAction: function() {
            if(tigerGame.params.step.total <= Math.floor(Math.random() * (9 - 1) + 1)) {
                tigerGame.overAction();
                return false;
            }
            $(".prize").removeClass("get-active");
            $(".prize").each(function() {
                if($(this).attr("index") == tigerGame.params.index) {
                    $(this).addClass("get-active");
                }
            });
            if(tigerGame.params.step.total > tigerGame.params.step.slow) {
                setTimeout(tigerGame.goAction, tigerGame.params.speed.fast);
            } else {
                setTimeout(tigerGame.goAction, tigerGame.params.speed.slow);
                tigerGame.params.speed.slow += 80;
            }
            tigerGame.params.step.total--;
            tigerGame.params.index++;
            if(tigerGame.params.index == 10) {
                tigerGame.params.index = 1;
            }
        },
        overAction: function() {
            $('body').bind(M.click_tap,'.js-lottery-btn', tigerGame.startAction);
            tigerGame.init();
            setTimeout(function () {
                lottery_res(tigerGame.prise);
            }, 1500);
        }
    };
    $('body').bind(M.click_tap,'.js-lottery-btn', tigerGame.startAction);

    //单行跑马灯轮播
    var container = $('.news_li');
    var totalHeight = parseInt(container.height());//滚动总高度
    var height = parseInt($('.prise-user').height());//每次滚动的高度
    var num = 0;//滚动次数
    var num_total = $('#hidden_horse_total').val(); //每列的行数
    //文字滚动
    function fontScroll(){
        var temp = num*height;//滚动变量
        var content_one = 0;
        var content_two = 0;
        var str = '用户'+$('.js-news-one').find('.li-item-'+num).text()+'抽中'+$('.js-news-two').find('.li-item-'+num).text();
        if(totalHeight > temp){
            container.css('margin-top',-temp)
        }else{
            var diff = temp % totalHeight;
            container.css('margin-top',-diff)
        }
        if(num == num_total){
            num = 0;
        }

        $('.js-news-one').find('.li-item-'+num).show().siblings().hide();
        $('.js-news-two').find('.li-item-'+num).show().siblings().hide();

        content_one = parseInt($('.js-news-one .li-item-'+num).width(),10);
        content_two = parseInt($('.js-news-two .li-item-'+num).width(), 10);
        //超出文字处理
        if((content_one+content_two+128) > 540){
            var content_two_new = 520-(content_one+128);
            $('.js-news-two .li-item-'+num).css({
                'width': content_two_new,
                'text-overflow': 'ellipsis',
                '-webkit-text-overflow': 'ellipsis',
                'white-space': 'nowrap',
                'overflow': 'hidden',
                'display': 'inline-block',
                'z-index': '33333'
            });
        }
        num++;
        setTimeout(fontScroll,2000);
    }
    fontScroll();

})();
