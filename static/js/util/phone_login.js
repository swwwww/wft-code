//手机登陆action
(function(exports) {
    var click_flag = false;
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '20%',
        font_size: 28,
        time: 2500,
        z_index: 22222
    };

    var phone_login = {
        /***********************************************************************
         * 输入手机号
         */
        code : function() {
            var phone = $('#js_com_phone').val();
            var type = $('#get_type').val();
            var code_flag = false;
            var dao = DAO.user;
            if(!this.check(code_flag)){
                return false;
            }

            var url = base_url_module + 'sms/getCode';

            var post_data = {
                'YII_CSRF_TOKEN' : yii_csrf_token,
                'phone' : phone,
                'type' : type,
                'wap' : 3
            };

            config_tips.msg = '验证码已发送';
            M.util.popup_tips(config_tips);
            if(type == 0){
                $.post(url, post_data, function(res) {
                    var data = JSON.parse(res);
                    console.log(data);
                    if(data.status == 1){

                    }else{
                        config_tips.msg = data['msg'];
                        M.util.popup_tips(config_tips);
                    }
                });
            }else if(type == 1){
                dao.getCode(post_data, function (res) {
                   var data = res.data;
                    console.log(data);
                    if(data.status == 1){

                    }else{
                        config_tips.msg = data['msg'];
                        M.util.popup_tips(config_tips);
                    }
                })
            }
        },

        /***********************************************************************
         * 提交手机号码及验证码函数 phone： 获取手机号的输入框 phone = $('id/class).val(); Qcode:
         * 输入框获取的验证码
         * 数据提交成功的跳转接口submit_url
         * 回调函数：callback
         */
        submit : function(submit_url, callback) {
            if(click_flag == false){
                click_flag = true;
            }else{
                return false;
            }

            var phone = $('#js_com_phone').val();
            var code = $('#js_com_phone_code').val();
            var login_btn = $('.login-btn');
            var code_flag = true;
            if(!this.check(code_flag)){
                return false;
            }

            var post_data = {
                'YII_CSRF_TOKEN' : yii_csrf_token,
                'phone' : phone,
                'code' : code
            };

            login_btn.css({'background':'#eee'}).text('请稍等...');

            $.post(submit_url, post_data, function(res) {
                click_flag = false;
                var data = JSON.parse(res);
                if (data['status'] == 1) {
                    if(callback){
                        callback();
                    }else{
                        window.location.href = window.location;
                    }
                } else {
                    alert(data['msg']);
                    login_btn.css({'background':'#fb6050'}).text('登录');
                }
            });
        },

        check: function(code_flag){
            var phone = $('#js_com_phone').val();
            var code = $('#js_com_phone_code').val();

            if (!(phone && /^1[34578]\d{9}$/.test(phone))) {
                alert("请输入正确的手机号！");
                return false;
            }

            if (code_flag && !code) {
                alert("请输入验证码！");
                return false;
            }

            return true;
        }
    };

    $('body').on(M.click_tap, '.js-get-phone-code', function() {
        phone_login.code();
    })

    exports.phone_login = phone_login;
})(window);