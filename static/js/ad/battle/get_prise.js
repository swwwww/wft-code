/**
 * Created by Administrator on 2017/1/15 0015.
 */
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

    //领取奖券的函数
    //领取奖券函数
    function acceptCash(data, callback) {
        //防止重复领取
        var record_id = parseInt(click_target.attr('data-record-id'), 10);
        var id = parseInt(click_target.attr('data-id'), 10);
        var page = parseInt(click_target.attr('data-page'), 10);

        var dao = DAO.lottery;
        var post_data = {
            'lottery_id' : 6,
            'record_id' : record_id
        };

        if(data){
            post_data['phone'] = data['phone'];
            post_data['code'] = data['code'];
        }

        dao.acceptLotteryRecord(post_data, function (res) {
            if(res['status'] == 1){ //领奖结果
                if(page == 1){
                    var record_url = base_url_module + 'lottery/detail/lottery_id/6?record_id='+record_id;
                    window.location.href = record_url;
                }else{
                    config_tips.msg = res['msg'];
                    M.util.popup_tips(config_tips);
                    setTimeout(function () {
                        window.location.href=window.location;
                    }, 2500);
                }

                if(callback){
                    callback();
                }

                $('.ph-get-prise').hide();
                $('.matte').hide();
            }else {
                config_tips.msg = res['msg'];
                M.util.popup_tips(config_tips);
                setTimeout(function () {
                      window.location.href=window.location;
                }, 1500);
            }
        });
    }

    $('.js-get-cash').on(M.click_touchend, function () {
        var user_phone = $('#hide_user_phone').val();
        var $this = $(this);
        click_target = $this;
        var cash_name = click_target.attr('data-name');
        $('.cash-name').text(cash_name);

        if (user_phone != '') {
            acceptCash();
        } else {
            $('.score-prise').hide();
            $('.matte').show();
            $('.ph-get-prise').show();
        }
        
        
    });


    $('.js-accept-gift').on(M.click_touchend, function () {
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

    $('#js_get_phone_code').on(M.click_touchend, function () {
        phone_login.code();
        $('#js_com_phone_code').css({
            border: '1px solid #f64062'
        });
    });
});