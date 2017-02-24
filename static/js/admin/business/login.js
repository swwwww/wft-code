/**
 * Created by Administrator on 2017/2/18 0018.
 */
(function () {
    $('#js-login').on('click', function () {
       var name = $('#js_com_phone'),
           password = $('#js_com_phone_code'),
           expTime = $('#js_check_box').val(),
           config_tips     = {
               msg: '提示信息',
               padding_tb: '4%',
               padding_rl: '8%',
               top : '45%',
               font_size: 28,
               time:1500,
               is_reload: false
           };

        //武商众圆真冰场
        if(name.val() == ''){
            config_tips.msg = '请输入用户名或用户OD!';
            M.util.popup_tips(config_tips);
            name.focus();
        }else if(password.val() == ''){
            config_tips.msg = '请输入密码!';
            M.util.popup_tips(config_tips);
            password.focus();
        }

        if(name.val()&&password.val()){
            var dao = ADMIN_DAO.business;
            var params = {
                'name': name.val(),
                'password': password.val(),
                'exp_time': expTime
            }
            console.log(params);
            
            dao.postLogin(params, function (res) {
                console.log(res);
                if(res.status == 1){
                    var data = res['data'];
                    // M.cookie.set('exp_time', data['exp_time']);
                    // M.cookie.set('organizer_id', data['organizer_id']);
                    // M.cookie.set('organizer_name', data['organizer_name']);
                    // M.cookie.set('rc_auth', data['rc_auth']);
                    config_tips.msg = res['msg'];
                    M.util.popup_tips(config_tips);
                    setTimeout(function () {
                        window.location.href = base_url_module + 'business/index';
                    }, 2500)
                }else {
                    config_tips.msg = res['msg'];
                    M.util.popup_tips(config_tips);
                }
            });
        }

    });
})();


