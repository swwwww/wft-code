/**
 * Created by Administrator on 2016/6/30 0030.
 */
/* 弹窗部分 */
(function() {
    $('#today-prise').on('tap', function() {
        $('#big-gift').show();
        $('#mark').show();
    });

    $('#big-close').on('tap', function() {
        $('#big-gift').hide();
        $('#mark').hide();
    });

    $('.btnShare').on('click', function () {
        var app = 0;
        var title = wechat_share.share_app_message.title;
        var content = wechat_share.share_app_message.message;
        var url = wechat_share.share_app_message.link;
        var img = wechat_share.share_app_message.img_url;
        if(M.browser.ios){
            var share_url = 'webshare$$app='+app+'&title='+title+'&url='+url+'&img='+img+'&content='+content;
            window.location.href = share_url;
        }else{
            window.getdata.webShare(app,url,title,content,img);
        }
    });
    $('#share').on('click', function () {
        $('#share').hide();
        $('#mark_share').hide();
    });
    $('#mark_share').on('click',function () {
        $('#share').hide();
        $('#mark_share').hide();
    });
    $(".try-again").on('click', function() {
        $("#win").hide();
        $("#lose").hide();
        $("#mark").hide();
    });

    $('.good-nav-action').on('click', function() {
        var $this = $(this);
        var href = $this.attr('data-href');

        $this.parent().addClass("cur");
        $this.parent().siblings().removeClass("cur");

        var target = document.getElementById('good_seq_' + href);
        var scroll_height = parseInt(M.util.getElementTop(target), 10);
        scroll_height = scroll_height - 100;
        window.scroll(0, scroll_height);
    });

    $('.js-action-redirect').click(function(){
        var $this = $(this);
        var id = $this.attr('data-id');
        var type = $this.attr('data-type');
        var u = navigator.userAgent;

        if(M.browser.wft){
            if(M.browser.ios){
                if(type == 0){
                    document.location.href = 'game_info$$id=' + id;
                }else{
                    if(M.browser.wft && u.indexOf('/client/3.3') < 0){
                        window.location = '../../../../index.php';
                        return false;
                    }
                    document.location.href = 'kids_play$$id=' + id;
                }
            }else{
                if(type == 0){
                    window.getdata.game_info(id);
                }else{
                    if(M.browser.wft && u.indexOf('/client/3.3') < 0){
                        window.location = '../../../../index.php';
                        return false;
                    }
                    //window.getdata.kids_play(id);
                    window.getdata.integral('activity_' + id);

                }
            }
        }
    });
})();

/* 转盘抽奖 */
var dataObj = [ 0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330 ];
var prise_type_arr = {
  '谢谢': [0, 4, 8],
  '1元券-1' : [9],
  '2元券-1' : [6],
  '5元券-1' : [3],
  '10元券-1' : [1],
  '10元券-2' : [10],
  '20元券-2' : [7],
  '30元券-2' : [5],
  '50元券-2' : [2],
  '特等奖-4' : [11]
};

var cash_vo = {};
var is_award = false;
var $accept_this;
var lottery_cash_id;

$(function() {
    var rotating = false;

    var myTurn = $("#outter").rotateModule({
        beginAngle : 0,
        // time: 3,
        endAngule : null,
        callBack : function() {
            rotating = false;

            if(is_award){
                showPriseNotice('yes');
                var award_total = parseInt($('#js_award_total').text(), 10);
                $('#js_award_total').text(award_total + 1);
            }else{
                showPriseNotice('no');
            }

            is_award = false;
        }
    });

    var left_total = parseInt($('#js_left_total').text(), 10);

    $("#inner").on("click", function() {
        if(rotating){
           return false;
        }
        left_total--;
        if(left_total < 0){
            $("#share").show();
            $("#mark_share").show();
            myTurn.rotateTo(0);
            return false;
        }

        $('#js_left_total').text(left_total);
        var $this = $(this);
        var login = $this.attr('data-login');
        if(login == 'no'){
            if(M.browser.wft){
                if(M.browser.ios){
                    document.location.href = 'user_login$$';
                }else{
                    window.getdata.user_login();
                }
            }else{
                var wft_param = $('#hide_wft_param').val();
                var redirect_url = base_url_module + 'lottery/login';
                if(wft_param){
                    redirect_url += '?p=' + wft_param;
                }
                window.location = redirect_url;
            }
            return false;
        }

        var url = base_url_module + 'lottery/lucky';
        var post_data = {
            'YII_CSRF_TOKEN': yii_csrf_token,
            'lottery_id': 1,
        };

        $.post(url, post_data, function(res){
            var result = eval('(' + res + ')');

            if(result['status'] == 1){
                cash_vo = result['data'];
                lottery_cash_id = cash_vo['id'];
                var cash_name = cash_vo['cash_alias'] + '-' + cash_vo['type'];
                var cash_index_arr = prise_type_arr[cash_name];
                var cash_index = Math.floor((Math.random() * cash_index_arr.length));
                var cash_angle = dataObj[cash_index_arr[cash_index]];

                if (!rotating) {
                    rotating = true;
                    is_award = true;
                    var round = Math.ceil(Math.random() * 5);
                    myTurn.rotateTo(cash_angle + 360 * round);
                }
            }else{
                var flag = parseInt(result['data'], 10);
                switch(flag){
                    case 100:// 没有中奖
                        var thank_index_arr = prise_type_arr['谢谢'];
                        var thank_index = Math.floor((Math.random() * thank_index_arr.length));
                        var thank_angle = dataObj[thank_index_arr[thank_index]];
                        if (!rotating) {
                            rotating = true;
                            is_award = false;
                            myTurn.rotateTo(thank_angle + 1440);
                        }
                        break;
                    case 101:// 抽奖次数已用完
                        $("#share").show();
                        $("#mark_share").show();
                        myTurn.rotateTo(0);
                        break;
                    case 102:
                    case 103:// 非法参数
                        window.location = base_url_module + 'lottery/login';
                        return false;
                        break;
                    default:
                        alert(result['msg']);
                        break;
                }
            }
        });
    });
});

/* 获取普通奖券的弹出出窗 */
function showPriseNotice(type) {
    var priseTitle = $("#win-info-kind");
    var priseName = $("#win-info-money");

    if(type == 'yes'){
        lottery_cash_id = cash_vo['id'];
        var cash_type = parseInt(cash_vo['type'], 10);
        var cash_name = cash_vo['cash_alias'];
        var cash_image = cash_vo['cash_image'];

        switch(cash_type){
        case 1:
            priseTitle.text('玩翻天通用红包');
            priseName.text(cash_name);
            break;
        case 2:
            priseTitle.text('遛娃学院红包');
            priseName.text(cash_name);
            break;
        case 4:
            priseTitle.text('今日特奖');
            priseName.text(cash_name);
            break;
        default:
            break;
        }

        $("#win").show();
        $("#mark").show();
        actionGetPrise();
    }else{
        $('#lose').show();
        $('#mark').show();
    }
}

/**
 * 领奖
 */
function actionGetPrise() {
    var phoneNum = parseInt($('#hide_phone').val(), 10);

    $("#win-get-im").on("click", function() {
        if(phoneNum > 0){
            acceptCash(phoneNum, lottery_cash_id);
        }else {
            $('#give').show();
            $('#mark').show();
            $('#win').hide();
        }
    });
}

$(".to-get").on('click', function() {
    $accept_this = $(this);
    lottery_cash_id = $accept_this.attr('data-lottery-cash-id');
    var phoneNum = parseInt($('#hide_phone').val(), 10);

    if (phoneNum > 0) {

        acceptCash(phoneNum, lottery_cash_id, function(){
            $accept_this.hide();
            $accept_this.next().show();
        });
    } else {
        $("#give").show();
        $("#mark").show();
    }
});


$(".give-sure").on('click', function() {
    var flag = verifyPh();
    if (flag) {
        $("#loginTip").hide();

        phoneNum = $('#give-purse-phone').val();
        acceptCash(phoneNum, lottery_cash_id, function(){
            $accept_this.hide();
            $accept_this.next().show();
        });
    }
});

function acceptCash(phone, lottery_cash_id, callback){
    var url = base_url_module + 'lottery/accept';
    var post_data = {
        'YII_CSRF_TOKEN': yii_csrf_token,
        'lottery_id': 1,
        'phone': phone,
        'lottery_cash_id': lottery_cash_id
    }

    $.post(url, post_data, function(res){
        var data = JSON.parse(res);

        if(data['status'] == 1){
            $('#succ').show();
            $('#mark').show();
            $('#win').hide();
            $('#give').hide();
            if(callback){
                callback();
            }
            $('.succ-btn').on('click', function () {
                $('#succ').hide();
                $('#mark').hide();

                var target = $(this).attr('data-target');

                var u = navigator.userAgent;
                // 直接下载
                if(M.browser.wft && u.indexOf('/client/3.3') < 0){
                    window.location = '../../../../index.php';
                    return false;
                }

                if(target == 'market'){
                    window.location = 'http://play.wanfantian.com/' + 'lottery/market/lottery_id/1#good-nav';
                }else{
                    window.location = 'http://play.wanfantian.com/' + 'lottery/record/lottery_id/1';
                }
            });
        }else{
            alert(data['msg']);
        }
    });
}

/*******************************************************************************
 * 新手输入手机号的表单验证
 */
function verifyPh() {
    var g_ph = $('#give-purse-phone');
    if (!(g_ph.val() && /^1[34578]\d{9}$/.test(g_ph.val()))) {
        $("#loginTip").text("你输入的手机号有误,请重新输入！");
        $("#loginTip").show();
        setTimeout(function() {
            $("#loginTip").hide();
        }, 1000)
        return false;
    } else {
        $("#loginTip").hide();
        return true;
    }
}

$(window).scroll(function() {
    var scroll_top = 0;
    var olott = $('#lott');
    var lottHeight = olott.height();

    scroll_top = M.util.getScrollTop();

    if (scroll_top > lottHeight) {
        $("#good-nav").css({
            position : 'fixed',
            top : '0'
        });
    } else {
        $("#lott").css({
            position : 'static'
        });
        $("#good-nav").css({
            position : 'static'
        });
        $('.good-title').css('margin-top', 0);
        $("#lott").css('display', 'block');
    }
});
