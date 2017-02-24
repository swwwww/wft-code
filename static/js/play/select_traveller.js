/**
 * Created by xxxian on 2016/9/26 0026.
 */
(function () {
    var id = 0,
        config_tips = {
            msg: '提示信息',
            padding_tb: '4%',
            padding_rl: '4%',
            top: '34%',
            font_size: 28,
            time: 1500,
            z_index: 100
        };

    //删除出行人
    $('body').on(M.click_tap, '.del', function () {
        id = $(this).next().attr('data-ass-id');
        $('.matte').show();
        $('.make-sure').show();
    });
    
    $('body').on(M.click_tap, '.cancel-btn', function () {
        $('.matte').hide();
        $('.make-sure').hide();
    });

    $('body').on(M.click_tap, '.sure-btn', function () {
        var dao = DAO.traveller,
            param={
                'associates_id': id
            };
        dao.delTraveller(param, function (res) {
            if(res.status == 1){
               var res = res['data'];
                if(res.status==1){
                    config_tips.msg = res.message;
                    config_tips.is_reload = true;
                    M.util.popup_tips(config_tips);
                }else if(res.status==0){
                    config_tips.msg = res.message;
                    M.util.popup_tips(config_tips);
                }
            }else {
                $('.matte').hide();
                $('.make-sure').hide();
                config_tips.msg = res.message;
                config_tips.is_reload = true;
            }
        });
    });
})();