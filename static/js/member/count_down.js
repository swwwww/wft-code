/**
 * Created by MEX | mixmore@yeah.net on 16/11/19.
 */

//计时函数
var time_distance;
function show_time(){
    // 计算时间差
    var end = $('#data').val();
    console.log(end);

    time_distance = parseInt(end)*1000;

    setInterval(function(){
        time_distance = time_distance - 1000;
        getTimeArr();
    }, 1000);
}

function getTimeArr(){
    if(time_distance){
        var temp_time = time_distance;
        // 天
        var int_day = Math.floor(temp_time/86400000);
        temp_time -= int_day * 86400000;
        // 时
        var int_hour = Math.floor(temp_time/3600000);
        temp_time -= int_hour * 3600000;
        // 分
        var int_minute = Math.floor(temp_time/60000);
        temp_time -= int_minute * 60000;
        // 秒
        var int_second = Math.floor(temp_time/1000);

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
        $(".time_d").text(int_day);
        $(".time_h").text(int_hour);
        $(".time_m").text(int_minute);
        $(".time_s").text(int_second);
    }else{
        window.location.reload();
    }
}

show_time();
