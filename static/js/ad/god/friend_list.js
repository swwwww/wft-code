/**
 * Created by xxxian on 2016/9/8 0008.
 */

(function() {
    var god_reward = function() {
        var reward_scrape = {};
        var lottery_vo = {};
        var op_total = 0;
        var reward_flag = true;
        var reward_status = 0;

        var dao = DAO.lottery;

        function initData() {
            god_reward.lottery_vo = JSON.parse($('#hide_lottery_vo').val());
            god_reward.op_total = parseInt(god_reward.lottery_vo.op_total, 10);
        }

        // 刮奖函数
        function scrapeCard() {
            if (god_reward.op_total > 0) {
                god_reward.reward_flag = true;
                god_reward.reward_scrape.init('', 'text');

                setTimeout(function() { // 界面提示
                    $('.pop-info').hide();
                }, 1600);
            } else {
                $('.js-lottery-left-total').text('0');
                $('#lottery-container').hide();
                $('.pop-info').hide();
                $('.card-value').html("想增加刮奖机会？<br/>点击右上角，分享给好友吧~").show();
                $('.get-btn').hide();
            }
        }

        function doingScrape(percent) { // 刮奖的回调函数
            var progress = parseInt(percent, 10);

            if (god_reward.reward_flag) {
                god_reward.reward_flag = false;

                god_reward.op_total--;// 抽奖次数-1
                var op_total = god_reward.op_total;
                op_total = op_total < 0 ? 0 : op_total;
                $('.js-lottery-left-total').text(op_total);

                var post_data = {
                    lottery_id : god_reward.lottery_vo.lottery_id
                };
                // 抽奖
                var cash_name = '';
                dao.lucky(post_data, function(res) {
                    if (res['status'] == 1) {// 中奖
                        god_reward.reward_status = 1;

                        var cash_vo = res['data'];
                        var lottery_cash_id = cash_vo['id'];
                        cash_name = cash_vo['cash_alias'];
                    } else {
                        god_reward.reward_status = parseInt(res['data'], 10);

                        cash_name = '咻咻咻，再刮一次';
                    }
                    god_reward.reward_scrape.init(cash_name, 'text');
                });
            }

            if (progress > 30) {
                switch (god_reward.reward_status) {
                case 1:// 中奖
                    $('.see-btn').text('领取奖品').show();
                    break;
                case 100:// 没有中奖
                    break;
                case 101:// 抽奖次数已用完
                    alert('抽奖次数已用完');
                    break;
                case 102:
                case 103:// 非法参数
                    window.location = base_url_module + 'lottery/login/lottery_id/' + god_reward.lottery_vo.lottery_id;
                    return false;
                    break;
                default:
                    break;
                }
                $('.get-btn').text('再刮一次').show();

                setTimeout(function() {
                    $('.mask-cover').hide(); // 清除遮罩层
                }, 100);
            }
        }

        return {
            reward_scrape : reward_scrape,
            lottery_vo : lottery_vo,
            op_total : op_total,
            reward_flag : reward_flag,
            reward_status : reward_status,
            initData : initData,
            scrapeCard : scrapeCard,
            doingScrape : doingScrape
        };
    }();

    god_reward.initData();

    $('body').on('tap', '.js-purse', function(e) {
        e.preventDefault();
        var $this = $(this);
        var lottery_id = god_reward.lottery_vo.lottery_id;
        var login_url = base_url_module + 'lottery/login/lottery_id/' + lottery_id;
        if (!M.util.checkLogin($this, login_url)) {
            return false;
        }

        god_reward.reward_scrape = new Lottery('lottery-container', '#ccc', 'color', 490, 240, god_reward.doingScrape);
        $('.matte').show();
        $('.pop-purse').show();
        $('.result').css({
            position: 'fixed',
            overflow: 'hidden'
        });

        setTimeout(function(){
            var record_url = base_url_module + 'lottery/record/lottery_id/2';
            $('.see-btn').attr('href', record_url);
        }, 700);

        god_reward.scrapeCard();
    });

    /***************************************************************************
     * 页面滚动时停止touchend事件冒泡，可以防止触发touchend事件
     */
    function stopTouchendPropagationAfterScroll(){
        var locked = false;

        window.addEventListener('touchmove', function(ev){
            locked || (locked = true, window.addEventListener('touchend', stopTouchendPropagation, true));
        }, true);
        function stopTouchendPropagation(ev){
            ev.stopPropagation();
            window.removeEventListener('touchend', stopTouchendPropagation, true);
            locked = false;
        }
    }


    $('body').on('touchend', '.get-btn', function(e) {
        e.preventDefault();
        $('.mask-cover').show();
        god_reward.scrapeCard();
    });

    $('.close-btn').on('click', function() {
        var vo = JSON.parse($('#hide_lottery_vo').val());
        var vote_id = vo.vote_vo.id;
        var url = base_url_module + 'lottery/friend/lottery_id/2?vote_id=' + vote_id;
        window.location.href = url;
    });

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
