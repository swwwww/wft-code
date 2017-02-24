/**
 * Created by MEX | mixmore@yeah.net on 16/11/15.
 */
(function () {
    var closeBtn = $('.close-btn');
    var windowMatte = $('.matte');
    var windowSucc = $('.js-succ-pop'); /*.succ-pop*/
    var shareGuide = $('.share-pic');
    var shareBtn = $('.js-share-btn');

    shareBtn.on(M.click_touchend, function () {
        windowSucc.hide();
        windowMatte.show();
        shareGuide.show();
    });
    closeBtn.on(M.click_touchend, function () {
        windowMatte.hide();
        windowSucc.hide();
        shareGuide.hide();
    });

    //点击分享按钮事件
    $('.icon-share-btn').on('click', function() {
        var app = 0;
        var title = wechat_share.share_app_message.title;
        var content = wechat_share.share_app_message.message;
        var url = wechat_share.share_app_message.link;
        var img = wechat_share.share_app_message.img_url;

        if (M.browser.ios) {
            var share_url = 'webshare$$app=' + app + '&title=' + title + '&url=' + url + '&img=' + img + '&content=' + content;
            window.location.href = share_url;
        } else {
            window.getdata.webShare(app, url, title, content, img);
        }
    });
}());