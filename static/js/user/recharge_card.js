/**
 * Created by deyi on 2017/1/9.
 */
(function(){
    var cardNum = $('#card_number'),
        cardCode = $('#card_code'),
        submit = $('#submit'),
        clickFlag = true,
        dao = DAO.user;

    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    submit.on(M.click_tap,function(){
        var cardNumVal = cardNum.val();
        var cardCodeVal = cardCode.val();
        var $this = $(this);

        if(clickFlag == false){
            return false
        }

        if(!cardNumVal){
            config_tips.msg = '您输入的卡号不能为空！';
            M.util.popup_tips(config_tips);
            return false
        }

        if(!cardCodeVal){
            config_tips.msg = '您输入的密码不能为空！';
            M.util.popup_tips(config_tips);
            return false
        }

        var param = {
            'card_code':cardNumVal,
            'card_password':cardCodeVal
        };

        $this.css({background:'#b8b8b8'}).text('提交中...');
        clickFlag = false;
        dao.rechargeCard(param, function (res) {
            if(res.status == 1){
                var data = res.data;
                if(data.status == 1){
                    config_tips.msg = data.message;
                    M.util.popup_tips(config_tips);
                    setTimeout(function(){
                        window.location.href = '/user/remainAccount';
                    },1000);
                }else if(data.status == 0){
                    config_tips.msg = data.message;
                    M.util.popup_tips(config_tips);
                    cardNum.val('');
                    cardCode.val('');
                    $this.css({background:'#fa6e51'}).text('提交');
                    clickFlag = true;
                }
            }else{
                config_tips.msg = res.msg;
                M.util.popup_tips(config_tips);
            }
        })
    })
})();