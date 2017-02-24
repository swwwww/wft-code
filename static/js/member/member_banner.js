/**
 * Created by MEX | mixmore@yeah.net on 16/11/16.
 * 初始化接口控制函数
 */
(function() {
    var type = $('#banner_type').val();
    var banner = $('#go-charge');
    var bannerBlowPic = $('.wrapper-head-cover');
    var vipBottom = $('.contain-in');

    // 控制banner是否显示
    var dao = DAO.member;
    dao.init(null, function(res) {
        if (res.status == 1) {
            res = res['data'];
            //控制banner显示
            if (res[type] == 0 || type == "none") {
                banner.hide();
                bannerBlowPic.removeClass('pdt65');
                vipBottom.removeClass('vip-bottom');
            } else {
                banner.show();
                bannerBlowPic.addClass('pdt65');
                vipBottom.addClass('vip-bottom');
            }
        }
    });
}());

// 在价格产生变化的时候修改内容
function updateBanner(price) {
    var chargePrice = $('#once_charge');
    var sendPrice = $('#send_money');
    var dao = DAO.member;

    dao.getVipSession(null, function(res) {
        var data = res.data;
        for ( var i = 0; i < data.length; i++) {
            if (parseFloat(price) <= parseFloat(data[i]['price'])) {
                sendPrice.text(data[i]['free_money']);
                chargePrice.text(data[i]['price']);
                break;
            }
            sendPrice.text(data[i]['free_money']);
            chargePrice.text(data[i]['price']);
        }
    });
}
updateBanner(0);
