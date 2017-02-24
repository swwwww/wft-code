/**
 * Created by Administrator on 2017/2/17 0017.
 */
(function () {

    //input获取焦点事件
    $('#js-get-info').on('focus', '.item-input', function () {
        var $this = $(this);
        $this.css({
            'border': '1px solid #55cea9'
        }).parent().siblings().find('.item-input').css({
            'border': '1px solid #949494'
        });
    });



    $('#js-application-in').on('click', function () {
        var sellerName    = $('#business_name'),
            sellerAddress = $('#address'),
            sellerMess    = $('#management'),
            sellerLinker    = $('#contact_name'),
            sellerLinkerPhone = $('#contact_phone'),
            config_tips     = {
                msg: '提示信息',
                padding_tb: '4%',
                padding_rl: '8%',
                top : '45%',
                font_size: 28,
                time:1500,
                is_reload: false
            };

        if(sellerName.val() == ''){
            config_tips.msg = '请输入商家名称!';
            M.util.popup_tips(config_tips);
            sellerName.focus();
        }else if(sellerAddress.val() == ''){
            config_tips.msg = '请输入商家地址!';
            M.util.popup_tips(config_tips);
            sellerAddress.focus();
        }else if(sellerMess.val() == ''){
            config_tips.msg = '请输入经营内容!';
            M.util.popup_tips(config_tips);
            sellerMess.focus();
        }else if(sellerLinker.val() == ''){
            config_tips.msg = '请输入联系人姓名!';
            M.util.popup_tips(config_tips);
            sellerLinker.focus();
        }else if(!(sellerLinkerPhone.val() && /^1[34578]\d{9}$/.test(sellerLinkerPhone.val()))){
            config_tips.msg = '请输入正确的联系人电话!';
            M.util.popup_tips(config_tips);
            sellerLinkerPhone.focus();
        }


        var dao = ADMIN_DAO.business;
        var params = {
            'seller_name': sellerName.val(),
            'seller_address': sellerAddress.val(),
            'seller_mess': sellerMess.val(),
            'seller_linker' : sellerLinker.val(),
            'seller_linker_phone' : sellerLinkerPhone.val()
        }
        console.log(params);

        dao.postSellInfo(params, function (res) {
            console.log(res);
            if(res.status == 1){
                config_tips.msg = res.msg;
                M.util.popup_tips(config_tips);
                setTimeout(function () {
                    window.location.href = base_url_module + 'business/index';
                },2500)
            }else {
                config_tips.msg = res.msg;
                M.util.popup_tips(config_tips);
            }
        });
    });

})();