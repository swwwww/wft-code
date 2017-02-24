/**
 * Created by MEX on 16/7/5.
 */

/**
 * 入参格式
 * {
    "ticket": {
        "app_id": "wx8e4046c01bf8fff3",
        "timestamp": 1467697228,
        "noncestr": "mvlpugj170dhoabogxoa1uk3vbfhfdrt",
        "signature": "2940efe5cc90e3c2bfbbefefdf3ced428d8154bb"
        },
        "share_app_message": {
            "title": "暑假不再家里蹲！花样遛娃，好玩还能涨知识！",
            "message": "玩翻天遛娃活动火爆来袭！免费游玩门票、海量红包等你抢！",
            "link": "http://localhost:83/share/test?lottery_id=1&share_user_id=",
            "img_url": "http://localhost:83/static/img/market/ad/wechat_share.png"
        },
        "share_time_line": {
            "title": "暑假，要花样遛娃，好玩还能涨知识！玩翻天遛娃活动火爆开抢中!",
            "link": "http://localhost:83/share/test?lottery_id=1&share_user_id=",
            "img_url": "http://localhost:83/static/img/market/ad/wechat_share.png"
        }
    }
 */
(function () {
    // 获取参数
    var wechat_share_json = document.getElementById("wechat_share_config_json").value;
    var wechat_share = JSON.parse(wechat_share_json);

    wx.config({
        debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: wechat_share.ticket.app_id, // 必填，公众号的唯一标识
        timestamp: wechat_share.ticket.timestamp, // 必填，生成签名的时间戳
        nonceStr: wechat_share.ticket.noncestr, // 必填，生成签名的随机串
        signature: wechat_share.ticket.signature,// 必填，签名，见附录1
        jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
    });

    //wx.ready(function () {
    //    wx.onMenuShareAppMessage({
    //        title: wechat_share.share_app_message.title,
    //        desc: wechat_share.share_app_message.message,
    //        link: wechat_share.share_app_message.link,
    //        imgUrl: wechat_share.share_app_message.img_url,
    //        trigger: function (res) {
    //            alert('用户点击发送给朋友');
    //        },
    //        success: function (res) {
    //            alert('已分享');
    //        },
    //        cancel: function (res) {
    //            alert('已取消');
    //        },
    //        fail: function (res) {
    //            alert(JSON.stringify(res));
    //        }
    //    });
    //    wx.onMenuShareTimeline({
    //        title: wechat_share.share_time_line.title,
    //        link: wechat_share.share_time_line.link,
    //        imgUrl: wechat_share.share_time_line.img_url,
    //        trigger: function (res) {
    //            alert('用户点击分享到朋友圈');
    //        },
    //        success: function (res) {
    //            alert('已分享');
    //        },
    //        cancel: function (res) {
    //            alert('已取消');
    //        },
    //        fail: function (res) {
    //            alert(JSON.stringify(res));
    //        }
    //    });
    //});
})();