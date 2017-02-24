/**
 * Created by MEX | mixmore@yeah.net on 16/11/10.
 */

// 收藏
(function () {
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    $('.banner-store').on(M.click_tap, function (e) {
        e.preventDefault();
        var collect = $('#collect'),
            collect_data = JSON.parse($('#collect_data').val());

        var dao = DAO.collect;

        var collect_act = 'add';
        if (collect.hasClass('has-store')) {
            collect_act = 'del';
        }
        var param = {
            'link_id': collect_data.id,
            'type': collect_data.type,
            'act': collect_act
        };
        console.log(collect_data)

        console.log(param)
        dao.getAllCollect(param, function (res) {
            console.log(res)

            var res_data = res['data'];
            if (res.status == 1) {
                if (res_data.status == 0) {
                    config_tips.msg = res_data.message;
                    M.util.popup_tips(config_tips);
                } else {
                    config_tips.msg = res_data.message;
                    config_tips.is_reload = true;
                    M.util.popup_tips(config_tips);
                }
            } else if (res.status == 0) {
                config_tips.msg = res.msg;
                M.util.popup_tips(config_tips);
            }

        });
    });
}());