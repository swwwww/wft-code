/**
 * Created by MEX | mixmore@yeah.net on 16/10/27.
 */

(function () {
    // 调用这个文件使用full_refund为id的按钮,并提供
    var order_sn = $('#data').val();
    var code = 0;
    var config = {
        title: '注意事项',
        notice : '',
        height : 800,
        width : 600,
        top : '10%',
        is_border: false
    },
    config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '40%',
        font_size: 28,
        time: 2500,
        z_index: 100
    };

    // 整单退款
    $('#full_refund').on(M.click_tap, function () {
        var param = {
            'order_sn' : order_sn
        };
        // console.log(param);

        var dao = DAO.order;
        dao.backpay(param, function (res) {
            // console.log(res);
            alert(res.msg);
            if(res.status==1){
                window.location = "/user/order?order_status=2";
            }
        })
    });

    // 单一退款
    $('.single_refund').on(M.click_tap, function () {
        var title = $(this).attr('data-title');
            code = $(this).attr('data-code');
        $('#js-refund-title').text(title);
        $('.matte1').show();
        $('#js-refund-confirm').show();
    });

    $('body').on(M.click_tap, '.cancel-btn', function () {
        $('.matte1').hide();
        $('#js-refund-confirm').hide();
    });

    $('body').on(M.click_tap, '#js-sure-refund', function () {
        var param = {
            'order_sn' : order_sn,
            'code' : code
        };
        var dao = DAO.play;
        dao.backPay(param, function (res) {
            if(res.status==1){
                $('.matte1').hide();
                $('#js-refund-confirm').hide();
                config_tips.msg = '退款将于3~5个工作日退回至支付账户';
                M.util.popup_tips(config_tips);
                // window.location = "/user/order?order_status=2";
                setTimeout(function () {
                    window.location.reload();
                }, 2500);
            }
        })
    });

    $('body').on(M.click_tap, '#js-sure-commodity-refund', function () {
        var param = {
            'order_sn' : order_sn,
            'password' : code
        };
        var dao = DAO.order;
        dao.backPay(param, function (res) {
            console.log(res);
            if(res.status==1){
                $('.matte1').hide();
                $('#js-refund-confirm').hide();
                config_tips.msg = '退款将于3~5个工作日退回至支付账户';
                M.util.popup_tips(config_tips);
                // window.location = "/user/order?order_status=2";
                setTimeout(function () {
                    window.location.reload();
                }, 2500);
            }
        })
    });

    //弹窗显示
    $('.order').on(M.click_tap,'.introduce',function(){
        var title = $(this).find('mark').text(),
            content = $(this).find('span').text();
        if(title == '使用时间：' || title == '特别说明：' || title == '出行地点：'){
            config['title'] = title;
            config['notice'] = content;
            M.util.popup_notice(config);
        }
    })
})();