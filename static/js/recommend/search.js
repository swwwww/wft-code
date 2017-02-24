/**
 * Created by deyi on 2016/10/10.
 */
(function(){
    var lens = $('.search-list').find('.item').length;
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    var reset = $('.reset');
    var info = $('.info');
    var search = $('#search');
    var back = $('.back-btn');

    info.on('input',function(){
        var $this = $(this);
        if($this.val()){
            reset.show();
            search.addClass('active');
            search.text('搜索')
        }else{
            reset.hide();
            search.removeClass('active');
            search.text('取消')
        }
    });

    reset.on(M.click_tap,function(){
        info.val('');
        search.removeClass('active');
        search.text('取消');
        $(this).hide();
    });

    info.keydown(function(event) {
        if (event.which == "13") {
            var msg = info.val();
            if(!msg){
                config_tips.msg = '搜索内容不能为空！';
                M.util.popup_tips(config_tips);
                return false;
            }
            window.location.href = '/recommend/searchdetail?word=' +msg ;
        }
    });

    search.on(M.click_tap,function(){
        var $this = $(this);
        var msg = info.val();
        if($this.text() == '搜索'){
            if(!msg){
                config_tips.msg = '搜索内容不能为空！';
                M.util.popup_tips(config_tips);
                return false;
            }
            window.location.href = '/recommend/searchdetail?word=' +msg ;
        }else{
            $('.main-s').show();
            $('.foot-nav').show();
            $('.foot-btn').show();
            $('.search-ele').hide();
            history.replaceState(null,'',location.pathname+location.search);
        }
    });

    back.on(M.click_touchend,function(e){
        e.stopPropagation();
        e.preventDefault();
        $('.main-s').show();
        $('.foot-nav').show();
        $('.foot-btn').show();
        $('.search-ele').hide();
        history.replaceState(null,'',location.pathname+location.search);
    });

    if(lens == 0){
        $('.search').remove();
    }

    $('.search-clear').on(M.click_tap,function(){
        var dao = DAO.searchDetail;
        dao.delSearchHistory('', function (res) {
            if(res.status == 1){
                config_tips.msg = res.msg;
                M.util.popup_tips(config_tips);
                setTimeout(function () {
                    window.location.reload();
                }, 100);
            }else{
                config_tips.msg = res.msg;
                M.util.popup_tips(config_tips);
            }
        });
    });

})();





