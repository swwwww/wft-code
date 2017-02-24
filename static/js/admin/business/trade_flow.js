/**
 * Created by Administrator on 2017/2/21 0021.
 */
(function () {
    var searchBtn  = $('#js-search-btn'),
        startTime  = $('#js-start-time'),
        endTime    = $('#js-end-time'),
        selectOpt  = $('#js-sel-option'),
        pageSelect = $('#js-page'),
        config_tips     = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '8%',
        top : '45%',
        font_size: 28,
        time:1500,
        is_reload: false
    };

    searchBtn.on('click', function () {
        var params = {},
            page = 0;

        pageSelect.find('.active', function () {
            page = $(this).attr('data-page');
        });
        params = {
            'page' : page,
            'page_num' : 10,
            'time_start' : startTime.val(),
            'time_end' : endTime.val(),
            'object_type' : selectOpt.val()
        }
        console.log(params);
        if(startTime.val() || endTime.val() || selectOpt.val()){
            window.location.href = base_url_module + 'business/tradeFlow?page='+ page + '&time_start='+ startTime.val() + '&time_end=' + endTime.val() + '&object_type=' + selectOpt.val();
        }else {
            config_tips.msg = "请输入您的筛选项！";
            M.util.popup_tips(config_tips);
        }

    });

    console.log(selectOpt.val());
    var page_info = JSON.parse($('#js-page-info').val());
    var url = base_url_module + 'business/tradeFlow?time_start='+ startTime.val() + '&time_end=' + endTime.val() + '&object_type=' + selectOpt.val();
    P.page.setPage(page_info, 7, url);

})();