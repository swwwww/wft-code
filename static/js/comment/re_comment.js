/**
 * Created by deyi on 2016/9/1.
 */
//二次评论回复
(function () {
    var data = JSON.parse($("#data").val());  // json decode
    var dao = DAO.comment;
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    $('#post').on(M.click_tap, function (e) {
        e.preventDefault();
        var mes = $('#c_input').val(),
            messageJson = [];
        messageJson.push({'t': 1, 'val': mes});
        if (mes) {
            var param = {
                'pid': data.pid,
                'type': data.type,
                'message': JSON.stringify(messageJson)
            };
            console.log(param);
            dao.giveComment(param, function (res) {
                console.log(res);

                if (res.status == 0) {
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                } else {
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                    // type (回复可选)对象类型 2评论商品 3评论游玩地 4评论商家 5评论专题 6团购分享 7活动(只能购买后评论，id为场次)
                    // var url = "";
                    if(data.type == 2){
                        // url +=  "/ticket/recommend?";
                    }else if(data.type == 3){

                    }
                   window.location.reload();
                }
            });
        } else {
            config_tips.msg = '内容不能为空！';
            M.util.popup_tips(config_tips);
        }
    });
})();
