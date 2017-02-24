
var Tips = $('#tips-dia');

/*****
* 弹窗 处理部分
* */
//集合地
$('body').on(M.click_tap, '.addr_sel', function () {
    // $('.matte').show();
    // $('#js_meeting').show();
    var meeting_num = parseInt($('#js_meeting_num').val(), 10); //集合地点的数量
    console.log(meeting_num);
    select_meeting_place(meeting_num);
});

//所有弹框的否
$('body').on(M.click_tap, '.cancel-btn', function () {
    $('.matte').hide();
    $('#js_meeting').hide();
});

//计时函数
function show_time(){
    var end = $('.end').val();
    var time_start = new Date().getTime(); //设定当前时间
    var time_end =  new Date(parseInt(end)*1000).getTime(); //设定目标时间
    // 计算时间差
    var time_distance = time_end - time_start;
    // 天
    var int_day = Math.floor(time_distance/86400000);
    time_distance -= int_day * 86400000;
    // 时
    var int_hour = Math.floor(time_distance/3600000);
    time_distance -= int_hour * 3600000;
    // 分
    var int_minute = Math.floor(time_distance/60000);
    time_distance -= int_minute * 60000;
    // 秒
    var int_second = Math.floor(time_distance/1000);

    if(int_hour < 10){
        int_hour = "0" + int_hour;
    }
    if(int_minute < 10){
        int_minute = "0" + int_minute;
    }
    if(int_second < 10){
        int_second = "0" + int_second;
    }
    // 显示时间
    $("#time_d").text(int_day);
    $("#time_h").text(int_hour);
    $("#time_m").text(int_minute);
    $("#time_s").text(int_second);
    // 设置定时器

    setTimeout("show_time()",1000);
}
show_time();

//选择集合地函数
function select_meeting_place(meeting_num) {
    $('.matte').show();
    $('#js_meeting').show();

    //点击“是”事件
    $('body').on(M.click_tap, '#js_meeting_sure', function (e) {
        var count = 0;
        e.preventDefault();

        $('.lists').find('input').each(function () {
            var sel = $(this).is(':checked');
            console.log(sel);
            if(sel == false){
                ++count;
            }
            if(count == meeting_num){ //没有选择集合地点
                Tips.text("请选择集合地点！").show();
                setTimeout(function(){
                    Tips.hide();
                },3000);
            }else {
                if(sel == true){
                    $("#car_addr").val($(this).attr("data-id"));
                    var  addr = $(this).prev().prev().text();
                    $(".addr_sel").text(addr);
                    $(".matte").hide();
                    $(".popup-address").hide();
                }
            }

        });

    });
}

$('body').on(M.click_touchend, '.close-btn', function () {
    $('.matte').hide();
    $('.js-succ-pop').hide();
    $('.share-pic').hide();
    var un_select_travellers = parseInt($('#un_select_people').val(), 10);
    console.log(un_select_travellers);
    if (un_select_travellers) {
        $('.matte1').show();
        $('#js-select-traveller').show();
        setTimeout(function () {
            $('.matte1').hide();
            $('#js-select-traveller').hide();
        }, 3500);
    }
});

/*没有购买分享提示时，直接弹出出行人不够的弹框*/
window.onload = function () {
    var un_select_travellers = parseInt($('#un_select_people').val(), 10);
    var wechat_flag = $('#vip-area').val();
    if (un_select_travellers && !wechat_flag) {
        $('.matte1').show();
        $('#js-select-traveller').show();
        setTimeout(function () {
            $('.matte1').hide();
            $('#js-select-traveller').hide();
        }, 3500);
    }
}
