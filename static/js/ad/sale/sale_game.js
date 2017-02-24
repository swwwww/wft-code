/**
 * Created by Administrator on 2016/10/26 0026.
 */
(function () {
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '25%',
        font_size: 28,
        time: 1500,
        z_index: 100
    };
    var flag_cut = false;
    var progress = 0;
    var cash_name = ''; //奖品名

    var sale_reward = function () {
        var reward_scrape = {}, //刮奖初始化对象
            lottery_vo = {}, //传参json
            op_total = 0, //刮奖机会
            reward_flag = true,
            reward_status = 0;

        function initData() {
            sale_reward.lottery_vo = JSON.parse($('#hide_lottery_vo').val());
            sale_reward.op_total = parseInt(sale_reward.lottery_vo.op_total, 10);
        }

        /*
         *刮奖的回调函数
         * percent: 刮开的百分比
         * */
        function doingScrape(percent) {
            var progress = parseInt(percent, 10);
            sale_reward.lottery_vo = JSON.parse($('#hide_lottery_vo').val());
            sale_reward.op_total = parseInt(sale_reward.lottery_vo.op_total, 10);

            if (flag_cut && progress) {
                var dao = DAO.lottery;
                var lottery_id = sale_reward.lottery_vo.lottery_id;
                var record_url = base_url_module + 'lottery/record/lottery_id/' + lottery_id;
                sale_reward.op_total--;
                $('#js_scrape_chance').text(sale_reward.op_total);

                flag_cut = false;

                $('.lottery-again-btn').addClass('js-lottery-again');
                $('.lottery-again-btn').removeClass('js-answer-again');
                $('.see-prise').attr('href', record_url);

                var post_data = {
                    lottery_id: sale_reward.lottery_vo.lottery_id
                };
                //刮奖请求
                dao.lucky(post_data, function (res) {
                    if (res['status'] == 1) {
                        sale_reward.reward_status = 1;
                        var cash_vo = res['data'];
                        var lottery_cash_id = cash_vo['id'];
                        cash_name = cash_vo['cash_alias'];
                        $('.smile-prise-name').text(cash_name);
                        $('.lottery-again-btn').text('再刮一次');
                    } else {
                        sale_reward.reward_status = parseInt(res['data'], 10);
                        cash_name = '咻咻咻，再刮一次';
                        $('.lottery-again-btn').text('再刮一次');
                    }
                    sale_reward.reward_scrape.init(cash_name, 'text');
                });
            }

            if (sale_reward.op_total > -1) {
                if (progress > 50) {
                    $('.mask-cover').hide();
                    switch (sale_reward.reward_status) {
                        case 1:// 中奖
                            setTimeout(function () {
                                $('.matte').show();
                                $('.pop-smile').show();
                            }, 800);
                            break;
                        case 100:// 没有中奖
                            setTimeout(function () {
                                $('.matte').show();
                                $('.pop-weep').show();
                                $('.not-get-prise').show();
                            }, 800);
                            break;
                        case 101:// 刮奖次数已用完
                            config_tips.msg = '抽奖次数已用完';
                            M.util.popup_tips(config_tips);
                            setTimeout(function () {
                                $('.matte').show();
                                $('.share-icon').show();
                            }, 800);
                            break;
                        case 102:
                        case 103:// 非法参数
                            window.location = base_url_module + 'lottery/login/lottery_id/' + lottery_id;
                            return false;
                            break;
                        default:
                            setTimeout(function () {
                                $('.matte').show();
                                $('.pop-weep').show();
                                $('.not-get-prise').show();
                            }, 800);
                            break;
                    }
                }
            } else {
                $('#js_scrape_chance').text('0');
                $('.matte').show();
                $('.share-icon').show();
            }
        }

        return {
            reward_scrape: reward_scrape,
            lottery_vo: lottery_vo,
            reward_flag: reward_flag,
            reward_status: reward_status,
            initData: initData,
            doingScrape: doingScrape
        };
    }();
    sale_reward.initData();

    //答题游戏
    function start_answer() {
        var answer_chance = parseInt($('.js-anwser-chance').text(), 10), //lottery_vo.chance 刮奖机会
            flag_time = true,
            lottery_vo = JSON.parse($('#hide_lottery_vo').val());
        if (answer_chance) {
            flag_time = setInterval(function () { //游戏计时器
                if (Number($('.time-end').text()) > 0) {
                    $('.time-end').text(Number($('.time-end').text() - 1));
                } else {
                    return false
                }
            }, 1000);
        }
        if (flag_time) {
            if (answer_chance) { //有答题机会
                //答题选项的处理
                $('#js_clock').removeClass('gray-clock').addClass('red-clock');
                $('.topic-list').find('.topic-choose').removeAttr('disabled');
                $('.topic-list').find('.topic-choose').removeClass('no-ask');

                $('body').on(M.click_tap, '.topic-label', function (e) {
                    e.preventDefault();
                    var lottery_vo = JSON.parse($('#hide_lottery_vo').val());
                    var lottery_id = sale_reward.lottery_vo.lottery_id;
                    var dao = DAO.lottery;
                    var name = $(this).attr('data-title'); //选项标题
                    var title = $('#area-img-title').val(); //获得的图片题目 lottery_vo.title
                    var result = 0;

                    $(this).find('input').attr('checked', true);

                    answer_chance--;
                    if (name == title) {
                        $('.smile-content').text('bingo! 回答正确');
                        $('.smile-prise-name').text('获得两次刮奖机会');
                        $('.lottery-again-btn').text('再答一次');
                        setTimeout(function () {
                            $('.lottery-again-btn').show();
                            $('.matte').show();
                            $('.pop-smile').show();
                        }, 500);
                        result = 1;     //如果答题成功
                        lottery_vo.op_total += 2; //刮奖机会+2
                    } else { //答题不成功
                        $('.lottery-again-btn').text('再答一次');
                        $('.pop-weep').show();
                        $('.lottery-again-btn').show();
                        $('.answer-wrong').show();
                        $('.matte').show();
                        result = 0
                    }

                    var record_url = base_url_module + 'lottery/record/lottery_id/' + lottery_vo.lottery_id;
                    $('.see-prise').attr('href', record_url);
                    $('.lottery-again-btn').addClass('js-answer-again');
                    $('.lottery-again-btn').removeClass('js-lottery-again');
                    var params = {
                        'lottery_id': lottery_vo.lottery_id,
                        'result': result
                    }
                    dao.guessShop(params, function (res) {
                        if (res.status == 1) {

                        } else {

                        }
                    });

                });
            } else { //没有答题机会
                $('.matte').show();
                $('.end-msg').html('您没有答题机会啦，<br/> 明天再来挑战！');
                $('.pop-weep').show();
                $('.white-clock').hide();
                $('#no-time').show();
                setTimeout(function () {
                    window.location.reload();
                }, 2000);
                // alert('您已经没有了答题机会')
            }
        } else { //没时间了
            $('.pop-weep').show();
            $('#no-time').show();
            setTimeout(function () {
                window.location.reload();
            }, 3000);

        }
    }

    window.onload = function () {
        var lottery_id = sale_reward.lottery_vo.lottery_id;

        flag_cut = true;

        sale_reward.reward_scrape = new Lottery('lottery-container',
            '/static/img/ad/sale/3_lottery_bg_1.png', 'image', 690, 300, sale_reward.doingScrape);
        sale_reward.reward_scrape.init(cash_name, 'text');
    }

    //点击更多刮奖机会
    function scroll_answer_area() {
        $('body').on(M.click_tap, '#js_more_chance', function () {
            sale_reward.op_total = parseInt(sale_reward.lottery_vo.op_total, 10);
            var answer_chance = parseInt($('.js-more-chance').attr('data-chance-num'), 10);

            if (answer_chance) {
                var charge_H = $('.game-head').height() + $('.card').height();
                $('body').scrollTo({toT: charge_H, durTime: 200});
            } else {
                if (sale_reward.op_total) {
                    config_tips.msg = '您还有刮奖机会，用完了再获取';
                    M.util.popup_tips(config_tips);
                    setTimeout(function () {
                        window.location.reload();
                    }, 800);
                } else {
                    $('.matte').show();
                    $('.share-icon').show();
                }
            }
        });
    }

    scroll_answer_area();

    //点击我要答题
    $('body').on(M.click_tap, '.answer-btn ', function (e) {
        e.preventDefault();
        start_answer();
    });

    //点击再刮一次的触发
    $('body').on(M.click_touchend, '.js-lottery-again', function (e) {
        e.preventDefault();
        $('.matte').hide();
        $('.pop-smile').hide();
        $('.pop-weep').hide();
        $('.not-get-prise').hide();
        if (sale_reward.op_total) {
            window.location.reload();
        } else {
            config_tips.msg = '刮奖次数已用完';
            M.util.popup_tips(config_tips);
            setTimeout(function () {
                $('.matte').show();
                $('.share-icon').show();
            }, 2500);
        }
    });

    //点击再答一次
    $('body').on(M.click_touchend, '.js-answer-again', function (e) {
        e.preventDefault();
        var answer_chance = parseInt($('.js-anwser-chance').text(), 10);
        $('.matte').hide();
        $('.pop-smile').hide();
        $('.pop-weep').hide();
        if (answer_chance) {
            var vo = JSON.parse($('#hide_lottery_vo').val());
            window.location.reload();
        } else {
            config_tips.msg = '您的答题机会用完';
            M.util.popup_tips(config_tips);
            setTimeout(function () {
                $('.matte').show();
                $('.share-icon').show();
            }, 800);
        }

    });


    //点击蒙层都能关闭
    // $('body').on(M.click_tap, '.matte', function () {
    //     $('.matte').hide();
    //     $('.pop-weep').hide();
    //     $('.pop-smile').hide();
    //     $('.share-icon').hide();
    //     setTimeout(function () {
    //         window.location.reload();
    //     }, 1500);
    // });

    $('body').on(M.click_tap, '.share-icon', function () {
        $('.matte').hide();
        $('.share-icon').hide();
        setTimeout(function () {
            window.location.reload();
        }, 1500);
    });

    //点击分享按钮事件
    $('.share-btn').on('click', function () {
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