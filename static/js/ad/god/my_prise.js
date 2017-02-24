/**
 * Created by xxxian on 2016/9/10 0010.
 */
// 领取奖券
$(document).ready(function() {
    var reward_id = 0;
    var click_target = {};

    // 领取奖券函数
    function acceptCash(data, callback) {
        //防止重复领取
        var status = click_target.attr('data-status');
        if(status == 0){
            return false;
        }
        click_target.attr('data-status', 0);

        var url = base_url_module + 'lottery/acceptGodReward';
        var post_data = {
            'YII_CSRF_TOKEN' : yii_csrf_token,
            'lottery_id' : 2,
            'reward_id' : reward_id
        };

        if(data){
            post_data['phone'] = data['phone'];
            post_data['code'] = data['code'];
        }

        $.post(url, post_data, function(res) {
            var data = JSON.parse(res);

            if (data['status'] == 1) {
                click_target.hide();
                click_target.siblings('.has-got').show();

                if (callback) {
                    callback();
                }
                $('.js-register').hide();
                $('.matte').hide();
            } else {
                click_target.attr('data-status', 1);
                alert(data.msg);
            }
        });
    }

    $('body').on(M.click_tap, '.to-got', function() {
        var user_phone = $('#hide_user_phone').val();
        var $this = $(this);
        click_target = $this;

        reward_id = parseInt($this.attr('data-id'), 10);
        var cash_id = parseInt($this.attr('data-cash-id'), 10);

        if (user_phone != '') {
            acceptCash();
        } else {
            $('.matte').show();
            $('.js-register').show();
        }
    });

    $('body').on(M.click_tap, '.js-accept-gift', function() {
        var code_flag = true;
        if (!phone_login.check(code_flag)) {
            return false;
        }

        var phone = $('#js_com_phone').val();
        var code = $('#js_com_phone_code').val();

        var data = {
            phone : phone,
            code : code
        };
        acceptCash(data);
    });

    $('body').on(M.click_tap, '.js-get-phone-code', function() {
        phone_login.code();
    });

    $('body').on(M.click_tap, '.close-btn', function () {
        $('.js-register').hide();
        $('.matte').hide();
    })
});
