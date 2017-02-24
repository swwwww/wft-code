/**
 * Created by deyi on 2016/12/9.
 */
(function(){
    var config_tips = {
        msg: '',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    var order_detail = $('#order_detail');
    var order_sn = order_detail.attr('data-sn');
    var type = order_detail.attr('data-type');

    /*
     * 取消订单事件
     * */
      function cancel_order() {
        $('body').on(M.click_tap, '.items-order', function () {
            $('.mark').show();
            $('#js_sure_cancel').show();

            //"确定"按钮
            $('body').on(M.click_tap, '#js_sure', function () {
                var dao = DAO.user;
                var param = {
                    'order_sn': order_sn,
                    'type': type
                };
                $('#js_sure_cancel').hide();
                $('.mark').hide();
                dao.delOrderItem(param, function (res) {
                    if (res.status == 1) {
                        var res = res['data'];
                        if (res.status == 1) {
                            config_tips.msg = res.message;
                            M.util.popup_tips(config_tips);
                            setTimeout(function () {
                                window.location.href ="/user/center";
                            }, 1000);
                        } else if (res.status == 0) {
                            config_tips.msg = res.message;
                            M.util.popup_tips(config_tips);
                        }
                    }

                });
            });
        });
        //"取消"按钮
        $('body').on(M.click_touchend, '#js_cancel', function (e) {
            e.preventDefault();
            $('.mark').hide();
            $('#js_sure_cancel').hide();
        });

    }

    cancel_order();

    /*
     * 重新付款订单事件
     * */
    function pay_order() {
        $('body').on(M.click_tap, '.items-pay', function () {
            var data = JSON.parse($('#order_pay').val());
            var temp_value = 0;
            var param_ticket = {
                'repay': 1,
                'order_type' :data['order_type'],
                'order_sn': order_sn,
                'coupon_id': data['coupon_id'],
                'info_id': data['info_id'],
                'number': data['buy_number'],
                'group_buy_id': data['group_buy_id'],
                'total': data['total_price'],
                'address': data['use_address']
            };

            if(data['order_type'] == 2){
                param_ticket['info_id'] = data['coupon_id'];
                param_ticket['coupon_id'] = data['info_id'];
            }
            param_ticket = M.util.getUrlFromJson(param_ticket);
            window.location.href = base_url_module + 'orderWap/orderCheckOut?' + param_ticket;
        })
    }
    pay_order();

})();
