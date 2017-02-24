/**
 * Created by Administrator on 2017/2/18 0018.
 */
/* *
* 处理用户密码：
*   1. 忘记密码，用户处于为登陆状态
*   2. 修改密码，用户处于登陆状态
* */
(function () {
    var getCodeBtn    = $('#js-get-code'),
        submitBtn     = $('#js-submit-btn'),
        changeBtn     = $('#js-change-btn'),
        organizerName = $('#mer_name'),
        phone         = $('#mer_phone'),
        code          = $('#mer_code'),
        newPwd       = $('#mer_password_new'),
        pwd           = $('#mer_password_again'),
        tips     = {
            msg: '提示信息',
            padding_tb: '4%',
            padding_rl: '8%',
            top : '45%',
            font_size: 28,
            time:1500,
            is_reload: false
        };
    //input获取焦点事件
    $('#js-get-info').on('focus', '.item-input', function () {
        var $this = $(this);
        $this.css({
            'border': '1px solid #55cea9'
        }).siblings().css({
            'border': '1px solid #949494'
        });
    });

    //发送验证码
    getCodeBtn.on('click', function () {
        if(organizerName.val() == ''){
            tips.msg = '请输入商家名称或ID!';
            M.util.popup_tips(tips);
            organizerName.focus();
        }else if(!(phone.val() && /^1[34578]\d{9}$/.test(phone.val()))){
            tips.msg = '请输入正确的联系人手机号';
            M.util.popup_tips(tips);
            phone.focus();
        }
        if(organizerName.val() && phone.val()){
            var dao = ADMIN_DAO.business;
            var params = {
                'organizer_name': organizerName.val(),
                'phone': phone.val()
            };

            dao.getVerifyCode(params, function (res) {
                console.log(res);
                if(res.status == 1){
                    var data = res['data'];
                    tips.msg = data['message'];
                    M.util.popup_tips(tips);
                }else {
                    tips.msg = res['msg'];
                    M.util.popup_tips(tips);
                }
            })
        }
    });

    //确认提交,获取忘记的密码
    submitBtn.on('click', function () {
        if(organizerName.val() == ''){
            tips.msg = '请输入商家名称或ID!';
            M.util.popup_tips(tips);
            organizerName.focus();
        }else if(!(phone.val() && /^1[34578]\d{9}$/.test(phone.val()))){
            tips.msg = '请输入正确的联系人手机号';
            M.util.popup_tips(tips);
            phone.focus();
        }else if(code.val() == ''){
            tips.msg = '请输入验证码！';
            M.util.popup_tips(tips);
            code.focus();
        }else if(newPwd.val() == ''){
            tips.msg = '请输入新密码！';
            M.util.popup_tips(tips);
            newPwd.focus();
        }
        if(organizerName.val()&&(phone.val() && /^1[34578]\d{9}$/.test(phone.val()))&&code.val()&&newPwd.val()){
            var dao = ADMIN_DAO.business;
            var params = {
                'organizer_name': organizerName.val(),
                'phone': phone.val(),
                'new_pwd': newPwd.val(),
                'code': code.val()
            };
            dao.getPassword(params, function (res) {
                console.log(res);
                if(res.status == 1){
                    var data = res['data'];
                    tips.msg = res['message'];
                    M.util.popup_tips(tips);
                }else {
                    tips.msg = res['msg'];
                    M.util.popup_tips(tips);
                }
            })
        }
    });

    //修改密码
    changeBtn.on('click', function () {
        if(organizerName.val() == ''){
            tips.msg = '请输入商家名称或ID!';
            M.util.popup_tips(tips);
            organizerName.focus();
        }else if(code.val() == ''){
            tips.msg = '请输入原始密码';
            M.util.popup_tips(tips);
            code.focus();
        }else if(newPwd.val() == ''){
            tips.msg = '请输入新密码';
            M.util.popup_tips(tips);
            newPwd.focus();
        }else if(pwd.val() == ''){
            tips.msg = '请重新输入新密码！';
            M.util.popup_tips(tips);
            pwd.focus();
        }

        if(organizerName.val() && code.val() && newPwd.val() && pwd.val()){
            var dao = ADMIN_DAO.business;
            var params = {
                'new_pwd': newPwd.val(),
                'pwd': code.val()
            };
            console.log(params);
            if(newPwd.val() != pwd.val()){
                tips.msg = '您重新输入的新密码有误，请确认后重新输入!';
                M.util.popup_tips(tips);
                pwd.focus();
            }else {
                dao.postPasswordChange(params, function (res) {
                    console.log(res);
                })
            }

        }
    });
})();