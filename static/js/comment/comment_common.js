/**
 * Created by deyi on 2016/11/1.
 */
(function(){
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    //点赞和取消点赞
    $(document).on(M.click_tap,'.like',function(e){
        e.preventDefault();
        var obj = $(this);
        var mid = obj.attr('data-mid');
        if(obj.hasClass('active')){
            var dao_del = DAO.comment;
            var param_del = {
                'pid':mid
            };
            dao_del.removeLike(param_del, function (res) {
                if(res.status == 1){
                    var data = res['data'];
                    if(data.status == 0){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                    }else if(data.message == '已经取消过了'){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                    }else{
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                        obj.removeClass('active');
                        var num = obj.text();
                        obj.text(parseInt(num)-1);
                    }
                }else{
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                }
            });
        }else{
            var dao_send = DAO.comment;
            var param_send = {
                'pid':mid
            };
            dao_send.giveLike(param_send, function (res) {
                if(res.status == 1){
                    var data = res['data'];
                    if(data.status == 0){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                    }else{
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                        obj.addClass('active');
                        var num = obj.text();
                        obj.text(parseInt(num)+1);
                    }
                }else{
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                }
            });
        }
    });
})();


