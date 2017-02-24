/**
 * Created by deyi on 2017/1/16.
 */
(function () {
    var dao = DAO.user;

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
})();
