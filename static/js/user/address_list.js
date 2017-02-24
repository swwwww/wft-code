/**
 * Created by deyi on 2016/9/26.
 */
(function(){
    var list = $('.address-list');
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    list.on(M.click_tap,'.item-btn-default',function(e){
        e.preventDefault();
        var id = $(this).prev().val();
        if(id > 0){
            var dao = DAO.address;
            var param = {
                'id': id
            };
            dao.getAllDefaultAddr(param, function (res) {
                if(res.status == 0){
                    config_tips.msg = res.message;
                    M.util.popup_tips(config_tips);
                }else if(res.status == 1){
                    config_tips.msg = res.message;
                    M.util.popup_tips(config_tips);
                    window.location.reload();
                }
            })
        }else{
            $(this).prev().prop('checked',false);
            config_tips.msg = '请完善联系人信息';
            M.util.popup_tips(config_tips);
        }
    });

    list.on(M.click_tap,'.item-btn-del',function(e){
        e.preventDefault();
        var id = $(this).attr('data-id');
        if(id > 0){
            var dao = DAO.address;
            var param = {
                'id': id
            };
            dao.getAllDelAddress(param, function (res) {
                if(res.status ==1){
                    var data = res['data'];
                    if(data.status == 0){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                    }else if(data.status == 1){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                        window.location.reload();
                    }
                }else{
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                }
            })
        }else{
            config_tips.msg = '请完善联系人信息';
            M.util.popup_tips(config_tips);
        }
    });
})();





