
/**
 * Created by Administrator on 2016/10/27 0027.
 */
//领取奖券
$(document).ready(function () {
    var reward_id = 0; //奖券id
    var click_target = {};
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '25%',
        font_size: 28,
        time: 2500,
        z_index: 100
    };
    //领取奖券函数
    function acceptCash(data, callback) {
        //防止重复领取
        console.log(click_target);
        var status = click_target.attr('data-status');
        var record_id = parseInt(click_target.attr('data-id'), 10);
        // var cash_id = parseInt($this.attr('data-cash-id'), 10);

        if(status == 0){
            return false;
        }

        var dao = DAO.lottery;
        var post_data = {
            'lottery_id' : 4,
            'record_id' : record_id
        };

        if(data){
            post_data['phone'] = data['phone'];
            post_data['code'] = data['code'];
        }

        console.log(post_data);
        dao.acceptLotteryRecord(post_data, function (res) {
            console.log(res);
            if(res['status'] == 1){
                config_tips.msg = '您的券领取成功！';
                M.util.popup_tips(config_tips);
                click_target.addClass('disabled');
                click_target.text('已领取');
                click_target.attr('data-status', 0);
                if(callback){
                    callback();
                }
                
                $('.ph-get-prise').hide();
                $('.matte').hide();
            }else {
                click_target.attr('data-status', 1);
                alert(data.msg);
            }
        });
    }

    $('body').on(M.click_tap, '.get-btn', function () {
        var user_phone = $('#hide_user_phone').val();
        var $this = $(this);
        click_target = $this;

        if (user_phone != '') {
            acceptCash();
        } else {
            $('.matte').show();
            $('.ph-get-prise').show();
        }
    });


    $('body').on(M.click_tap, '.js-accept-gift', function () {
        var code_flag = true;
        if(!phone_login.check(code_flag)){
            return false;
        }

        var phone = $('#js_com_phone').val();
        var code = $('#js_com_phone_code').val();

        var data = {
            phone: phone,
            code: code
        };
        acceptCash(data);
    });

    $('body').on(M.click_tap, '#js_get_phone_code', function () {
       phone_login.code();
        $('#js_com_phone_code').css({
            border: '1px solid #f64062'
        });
    });

});
