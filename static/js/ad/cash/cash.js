/**
 * Created by xxxian on 2016/9/9 0009. 提交手机号领券
 */

(function() {
    $('.js-submit').on(M.click_tap, function() {
        var phone = $('.js-ph').val();

        if (!(phone && /^1\d{10}$/.test(phone))) {
            alert('你输入的手机号有误,请重新输入！');
            return false;
        }

        var post_data = {
            YII_CSRF_TOKEN : yii_csrf_token,
            phone : phone,
            act_id : 1
        };

        var url = base_url_module + 'act/cashForCode';

        $.post(url, post_data, function(res) {
            var data = JSON.parse(res);
            if (data['status'] == 1) {
                window.location = base_url_module + 'act/cashResult';
            } else {
                alert(data['msg']);
                $('.js-ph').val('');
            }
        });
    });
})();
