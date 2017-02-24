/**
 * Created by deyi on 2016/10/28.
 */
(function () {
    var dao = DAO.user;
    var prev_psw;
    var next_psw;

    $('#submit').on(M.click_tap, function () {
        var code = $('#code').val();
        var phone = $('#js_com_phone').val();
        var val = $('#get_flag').val();
        var type = $('#get_type').val();
        var param = {
            'code': code,
            'phone': phone,
            'type': type
        };
        dao.verifyCode(param, function (res) {
            if (res.status) {
                var data = res.data;
                M.load.loadTip('doType', '验证通过', 'delay');
                window.location.href = "/user/passwordSet?code="+code+"&flag="+val;
            } else {
                M.load.loadTip('errorType', res.msg, 'delay');
            }
        })
    });

    function verify_password() {
        var res = JSON.parse($('#data').val());
        var psdPwd = $('#old_pwd');
        var pswBox = $('.password-input').find('span');
        var flag = true;
        if(res['flag'] == 1){
            // flag为2表示设置初始密码，flag为1表示找回密码，flag为0表示修改密码
            $('.main').append("<button value='完成' id='finish-btn'>完成</button>");
        }
        psdPwd.val('');
        psdPwd.focus();
        psdPwd.on('input', function () {
            len = psdPwd.val().length;
            pswBox.text('');
            pswBox.each(function (i) {
                if (i < len) {
                    pswBox.eq(i).text('*')
                }
            });
            if (len == 6 && flag) {
                if(res['flag'] == 1){
                    var btn = $('#finish-btn');
                    btn.addClass('active');
                    btn.on(M.click_tap, function () {
                        prev_psw = psdPwd.val();
                        var param = {
                            'code':res['code'],
                            'phone':res['phone'],
                            'password':prev_psw
                        };
                        dao.editPassword(param, function (res) {
                            console.log(res);
                            M.load.loadTip('loadType', "正在更新密码");
                            if (res.status) {
                                if (res.data.status) {
                                    M.load.loadTip('doType', res.msg, 'delay');
                                    window.location.href = '/user/center';
                                } else {
                                    M.load.loadTip('errorType', res.msg, 'delay');
                                }
                            }
                        })
                    })
                }else if(res['flag'] == 0){
                    prev_psw = psdPwd.val();
                    var param = {
                        'password': prev_psw
                    };
                    dao.verifyPassword(param, function (res) {
                        console.log(res);
                        if (res.status) {
                            if (res.data.status == 1) {
                                // 更新密码
                                psdPwd.val('');
                                pswBox.each(function (i) {
                                    if (i < len) {
                                        pswBox.eq(i).text('')
                                    }
                                });
                                // 更新密码
                                var htmlButton = "<button value='完成' id='finish-btn'>完成</button>";
                                $('#title').text('请再次输入您的玩翻天支付密码');
                                $('.main').append(htmlButton);
                                M.load.loadTip('doType', res.msg, 'delay');
                                flag = false;
                                update_password();
                            }else{
                                psdPwd.val('');
                                pswBox.each(function (i) {
                                    if (i < len) {
                                        pswBox.eq(i).text('')
                                    }
                                });
                                M.load.loadTip('errorType', res.data.message, 'delay');
                            }
                        }
                        // 如果密码正确 继续输入新密码
                        // 如果密码错误 跳出
                    }) 
                }else if(res['flag'] == 2){
                    prev_psw =  psdPwd.val();
                    psdPwd.val('');
                    pswBox.each(function (i) {
                        if (i < len) {
                            pswBox.eq(i).text('')
                        }
                    });
                    // 判断第一次与第二次输入的密码是否相同
                    var htmlButton = "<button value='完成' id='finish-btn'>完成</button>";
                    $('#title').text('请再次输入您的玩翻天支付密码');
                    $('.main').append(htmlButton);
                    M.load.loadTip('doType','请再次输入密码', 'delay');
                    flag = false;

                    psdPwd.on('input', function () {
                        var len = psdPwd.val().length;
                        pswBox.text('');
                        pswBox.each(function (i) {
                            if (i < len) {
                                pswBox.eq(i).text('*')
                            }
                        });
                        if (len == 6) {
                            var btn = $('#finish-btn');
                            btn.addClass('active');
                            btn.on(M.click_tap, function () {
                                var param = {
                                    'code': res['code'],
                                    'phone': res['phone'],
                                    'password': psdPwd.val()
                                };
                                next_psw = psdPwd.val();
                                if(prev_psw == next_psw){
                                    dao.editPassword(param, function (res) {
                                        M.load.loadTip('loadType', "正在更新密码");
                                        if (res.status) {
                                            if (res.data.status) {
                                                M.load.loadTip('doType', res.msg, 'delay');
                                                window.location.href = '/user/center';
                                            } else {
                                                M.load.loadTip('errorType', res.msg, 'delay');
                                            }
                                        }
                                    })
                                }else{
                                    M.load.loadTip('errorType','输入密码不一致', 'delay');
                                    setTimeout(function(){
                                        window.location.reload();
                                    },500)
                                }
                            })
                        }
                    });

                } else {
                    M.load.loadTip('errorType', res.msg, 'delay');
                }
            }
        });
    }

    verify_password();

    function update_password() {
        var psdPwd = $('#old_pwd');
        var pswBox = $('.password-input').find('span');
        psdPwd.val('');
        psdPwd.focus();
        psdPwd.on('input', function () {
            len = psdPwd.val().length;
            pswBox.text('');
            pswBox.each(function (i) {
                if (i < len) {
                    pswBox.eq(i).text('*')
                }
            });
            if (len == 6) {
                var btn = $('#finish-btn');
                btn.addClass('active');
                btn.on(M.click_tap, function () {
                    var param = {
                        'origin_password':prev_psw,
                        'new_password': psdPwd.val()
                    };
                    dao.updatePassword(param, function (res) {
                        M.load.loadTip('loadType', "正在更新密码");
                        if (res.status) {
                            if (res.data.status) {
                                M.load.loadTip('doType', res.msg, 'delay');
                                window.location.href = '/user/center';
                            } else {
                                M.load.loadTip('errorType', res.msg, 'delay');
                            }
                        }
                    })
                })
            }
        });
    }

})();