/**
 * Created by Administrator on 2017/2/21 0021.
 */
(function () {
    var clearBtn  = $('#js-clear-op'),
        searchBtn = $('#js-search-btn'),
        exportBtn = $('#js-export-btn'),
        code      = $('#js-verify-code'),
        orderSn    = $('#js-order-sn'),
        couponName    = $('#js-coupon-name'),
        startTime  = $('#js-start-time'),
        endTime    = $('#js-end-time'),
        selectOpt  = $('#js-sel-option'),
        pageSelect = $('#js-page'),
        view_type= 0 ,

        config_tips     = {
            msg: '提示信息',
            padding_tb: '4%',
            padding_rl: '8%',
            top : '45%',
            font_size: 28,
            time:1500,
            is_reload: false
        };

    $('#js-nav-select').on('click', '.order-item', function () {
        $(this).addClass('active-item').siblings().removeClass('active-item');
        view_type = $(this).attr('data-view-type');
    });

    clearBtn.on('click', function () {
        code.val('');
        orderSn.val('');
        couponName.val('');
        startTime.val('');
        endTime.val('');
        selectOpt.val('');
    });

    searchBtn.on('click', function () {
        var params = {
            'page' : page,
            'page_num' : 10,
            'code': code.val(),
            'order_sn': orderSn.val(),
            'coupon_name' : couponName.val(),
            'time_start' : startTime.val(),
            'time_end' : endTime.val(),
            'sort_type' : selectOpt.val(),
            'view_type' : view_type
        },
            page = 0;
        view_type = $('#js-nav-select').find('.active-item').attr('data-view-type');

        pageSelect.find('.active', function () {
            page = $(this).attr('data-page');
        });
        console.log(params);
        var flag = code.val()|| orderSn.val() || couponName.val()|| startTime.val() || endTime.val()||selectOpt.val();
        if(flag){
            window.location.href = base_url_module + 'business/manageOrder?'+'page='+page+'&code='+code.val()+'&order_sn='+orderSn.val()+'&coupon_name='+couponName.val()+'&time_start='+ startTime.val() + '&time_end=' + endTime.val() + '&sort_type=' + selectOpt.val()+'&view_type='+view_type;
        }else {
            config_tips.msg = "请输入您的筛选项！";
            M.util.popup_tips(config_tips);
        }
    });

    var page_info = JSON.parse($('#js-page-info').val());
    var url = base_url_module + 'business/manageOrder?code='+code.val()+'&order_sn='+orderSn.val()+'&coupon_name='+couponName.val()+'&time_start='+ startTime.val() + '&time_end=' + endTime.val() + '&sort_type=' + selectOpt.val()+'&view_type='+page_info.view_type;
    P.page.setPage(page_info, 7, url);
})();