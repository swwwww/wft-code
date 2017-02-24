/**
 * Created by Administrator on 2017/2/21 0021.
 */

(function () {
    //nav切换
    $('#js-nav-switch').on('click', '.nav-item', function () {
        $(this).addClass('active-item').siblings().removeClass('active-item');
    });

    var page_info = JSON.parse($('#js-page-info').val());
    console.log(page_info);
    var url = base_url_module + 'business/goodsDetail?type='+page_info.type+'&coupon_id='+page_info.coupon_id;
    P.page.setPage(page_info, 7, url);
})();