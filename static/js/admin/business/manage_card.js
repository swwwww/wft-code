/**
 * Created by Administrator on 2017/2/17 0017.
 */
(function () {
    var changeBtn = $('#js-change-bank');
    var submitBtn = $('#js-submit-btn');
    var sureBtn = $('#js-sure-btn');
    var popMatte = $('.pop-matte');

    //展现修改银行卡号的弹窗
    changeBtn.on('click',function () {
        $('#js-win-popUp').show();
        $('#js-get-info').show();
        $('#js-sure-win').hide();
    });
    popMatte.on('click', function () {
        $('#js-win-popUp').hide();
    });
    sureBtn.on('click', function () {
        $('#js-win-popUp').hide();
    });
    //input获取焦点事件
    $('#js-get-info').on('focus', '.item-input', function () {
        var $this = $(this);
        $this.css({
            'border': '1px solid #55cea9'
        }).parent().siblings().find('.item-input').css({
            'border': '1px solid #949494'
        });
    });

    //提交事件
    submitBtn.on('click', function () {
        var name = $('#icon_name'),
            phone = $('#icon_phone'),
            accountPerson = $('#account_person'),
            accountBank = $('#account_bank'),
            subBank = $('#sub_bank'),
            cardNum = $('#card_num'),
            config_tips     = {
                msg: '提示信息',
                padding_tb: '4%',
                padding_rl: '8%',
                top : '45%',
                font_size: 28,
                time:1500,
                is_reload: false,
                z_index: 9999
            };


        if(name.val() == ''){
            config_tips.msg = '请输入公司全称!';
            M.util.popup_tips(config_tips);
            name.focus();
        }else if(!(phone.val() && /^1[34578]\d{9}$/.test(phone.val()))){
            config_tips.msg = '请输入汇款通知手机号码!';
            M.util.popup_tips(config_tips);
            phone.focus();
        }else if(accountPerson.val() == ''){
            config_tips.msg = '请输入开户人信息!';
            M.util.popup_tips(config_tips);
            personPerson.focus();
        }else if(accountBank.val() == ''){
            config_tips.msg = '请输入开户行信息!';
            M.util.popup_tips(config_tips);
            accountBank.focus();
        }else if(subBank.val() == ''){
            config_tips.msg = '请输入开户支行信息!';
            M.util.popup_tips(config_tips);
            subBank.focus();
        }else if(!(cardNum.val() && /^(\d{16}|\d{19})$/.test(cardNum.val()))){
            config_tips.msg = '请检查你输入的银行卡号位数是否正确!';
            M.util.popup_tips(config_tips);
            cardNum.focus();
        }


        var dao = ADMIN_DAO.business;
        var params = {
            'company_name': name.val(),
            'notification_phone': phone.val(),
            'bank_user': accountPerson.val(),
            'bank_name' : accountBank.val(),
            'bank_address' : subBank.val(),
            'bank_card' : cardNum.val()
        };
        var flag = name.val() && phone.val() && accountPerson.val() && accountBank.val() && subBank.val() && (cardNum.val() && /^(\d{16}|\d{19})$/.test(cardNum.val()));
        console.log(params);
        console.log(flag);
        if(flag){
            dao.updateBankCard(params, function (res) {
                console.log(res);
                if(res.status == 1){
                    var data = res['data'];
                    $('#js-notice-text').text(data['message']);
                    $('#js-win-popUp').show();
                    $('#js-get-info').hide();
                    $('#js-sure-win').show();
                }else {
                    var data = res['data'];
                    $('#js-notice-text').text(data['message']);
                    $('#js-win-popUp').show();
                    $('#js-get-info').hide();
                    $('#js-sure-win').show();
                }
            });
        }

    });

})();