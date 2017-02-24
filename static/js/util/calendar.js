/**
 * Created by deyi on 2016/9/28.
 */
var obj = { date: new Date(), year: -1, month: -1, priceArr: [] };
var htmlObj = { header: "", left: "", right: "" };
var elemId = null;

var pickerEvent = {
    Init: function (elemid) {
        // console.log(elemid);
        elemId=elemid;
        if (obj.year == -1) {
            dateUtil.getCurrent();
        }
        for (var item in pickerHtml) {
            pickerHtml[item]();
        }
        // console.log('eeee',elemid, obj.month);
        var p = document.getElementById("calendar_choose");
        if (p != null) {
            document.getElementById("calendar_box").removeChild(p);
            $('.calendar-matte').remove();
        }
        document.getElementById("calendar_box").innerHTML ="";
        var html = '<div id="calendar_choose_'+obj.month+'"class="calendar" style="display: block;">';
        html += htmlObj.header;
        html += '<div class="basefix" id="bigCalendar" style="display: block;">';
        html += htmlObj.left;
        html += htmlObj.right;
        html += '<div style="clear: both;"></div>';
        html += "</div></div>";
        document.getElementById("calendar_box").innerHTML = html;
        //todo...这里之前放日历切换月份的

        //todo...这里之前放选择日期
    },


    getLast: function () {
        dateUtil.getLastDate();
        pickerEvent.Init(elemId);
    },
    getNext: function () {
        dateUtil.getNextDate();
        pickerEvent.Init(elemId);
    },
    getToday:function(){
        dateUtil.getCurrent();
        pickerEvent.Init(elemId);
    },
    getDetail:function(time){
        dateUtil.getDetailDay(time);
        pickerEvent.Init(elemId);
    },
    setPriceArr: function (arr) {
        obj.priceArr = arr;
    },
    remove: function () {
        var p = document.getElementById("calendar_choose");
        if (p != null) {
            document.body.removeChild(p);
        }
    },
    isShow: function () {
        var p = document.getElementById("calendar_choose");
        if (p != null) {
            return true;
        }
        else {
            return false;
        }
    }
};

//喧染页面
var pickerHtml = {
    //蒙层
    // getMatte:function (){
    //     var matte ='<div class="calendar-matte" style="display: block;position: fixed;top: 0;left: 50%;bottom: 0;transform:translate(-50%,0);-webkit-transform:translate(-50%,0);width:750px;z-index: 100;background: rgba(99,99,99,0.7)">' + '</div>';
    //     htmlObj.matte = matte;
    // },
    //表头
    getHead: function () {
        var head = '<div class="calendar_left pkg_double_month"><p class="date_text">' + obj.year + '年' + (obj.month<10 ? "0" + obj.month : obj.month) + '月</p><a href="javascript:void(0)" title="上一月" id="picker_last" class="pkg_circle_top"><i></i></a><a href="javascript:void(0)" title="下一月" id="picker_next" class="pkg_circle_bottom "><i></i></a><span class="l-matte"></span><span class="r-matte"></span></div><ul class="calendar_num basefix"><li class="bold">六</li><li>五</li><li>四</li><li>三</li><li>二</li><li>一</li><li class="bold">日</li></ul>';
        htmlObj.header = head;
    },
    //表中的具体日期 getRight --> getItemDate
    getItemDate: function () {
        var days = dateUtil.getLastDay();
        var week = dateUtil.getWeek();
        var html = '<table id="calendar_tab" class="calendar_right"><tbody>';
        var index = 0;

        for (var i = 1; i <= 42; i++) {
            if (index == 0) {
                html += "<tr>";
            }
            var c = week > 0 ? week : 0;
            if ((i - 1) >= week && (i - c) <= days) {
                var price = commonUtil.getPrice((i - c));
                var order_id = commonUtil.getOrderId(i - c);
                var ticket = commonUtil.getTicket(i-c);
                var intro = commonUtil.getIntro(i-c);
                var status = commonUtil. getStatus(i-c);
                var priceStr = "";
                var idStr = "";
                var wantNum = "";
                var introStr = "";
                var classStyle = "";
                var statusStr ='';
                var num = 0;
                if (price != -1) {
                    priceStr = price;
                    classStyle = "class='on'";
                    idStr = order_id;
                    wantNum = ticket;
                    introStr = intro;
                    statusStr = status;
                }
                if (status != 0 && status != 1){
                    statusStr = -1;
                }

                if (price != -1&&obj.year==new Date().getFullYear()&&obj.month==new Date().getMonth()+1&&i-c==new Date().getDate()) {
                    classStyle = "class='on today'";
                }
                
                if(obj.year>=new Date().getFullYear()&&obj.month>=new Date().getMonth()+1 && i-c<=new Date().getDate()){
                    html += '<td  ' + classStyle + ' date="' + obj.year + "-"  + (obj.month<10 ? "0" + obj.month : obj.month) + "-" + ((i - c)<10 ? "0" + (i - c) : (i - c)) + '" price="' + price + '" data-want="' + wantNum +'" data-buy="' + introStr +'" data-status="' + statusStr +'">' +
                        '<a href="javascript:;">';
                    if(i-c == new Date().getDate() && obj.month == new Date().getMonth()+1){
                        html +='<span class="date basefix unused">今天</span>';
                    }else {
                        if(obj.month != new Date().getMonth()+1){
                            html +='<span class="date basefix">' + (i - c) + '</span>';
                        }else {
                            html +='<span class="date basefix unused">' + (i - c) + '</span>';

                        }
                    }
                    html += '<span class="team basefix" style=" "></span>';
                    html += '<span class="team basefix" style="display: block"></span>';
                    html += '<span class="calendar_price01"></span>' ;
                    html += '</a></td>';
                }else{
                    html += '<td  ' + classStyle +  ' date="' + obj.year + "-"  + (obj.month<10 ? "0" + obj.month : obj.month) + "-" + ((i - c)<10 ? "0" + (i - c) : (i - c)) + '" price="' + price + '" data-want="' + wantNum +'" data-buy="' + introStr +'" data-status="' + statusStr +'">' +
                        '<a href="javascript:;">';
                    /*
                     * status: 1没票，显示想去人数，可以选 statusStr
                     * status: 0(默认)：有票可卖，显示价格 不可选
                     * */

                    if(statusStr == 1){
                        html += '<span class="date basefix" >' + (i - c) + '</span>';
                        if(wantNum){
                            html += '<span class="team basefix want-go" style="display: block">'+wantNum+"想去"+'</span>';
                        }else{
                            html += '<span class="team basefix" style="display: block">'+'&nbsp'+'</span>';
                        }
                    }else if(statusStr == 0) {
                        html += '<span class="date basefix unused" >' + (i - c) + '</span>';
                        if(introStr){
                            html += '<span class="team basefix unused" style="display: block">'+introStr+"人报名"+'</span>';
                        }else{
                            html += '<span class="team basefix unused" style="display: block">可报名</span>';
                        }
                        if(priceStr){
                            html += '<span class="calendar_price01 unused">' + "¥" +  priceStr + '</span>';
                        }
                    }else if(statusStr == -1){
                        html += '<span class="date basefix" >' + (i - c) + '</span>';
                        html += '<span class="team basefix" style="display: block"></span>';
                    }


                    html += '</a></td>';


                }

                if (index == 6) {
                    html += '</tr>';
                    index = -1;
                }
            }
            else {
                if((i - 1)<days){
                    html += "<td></td>";
                }
                if (index == 6) {
                    html += "</tr>";
                    index = -1;
                }
            }
            index++;
        }
        html += "</tbody></table>";
        htmlObj.right = html;
    }
};
var dateUtil = {
    //根据日期得到星期
    getWeek: function () {
        var d = new Date(obj.year, obj.month - 1, 1);
        return d.getDay();
    },
    //得到一个月的天数
    getLastDay: function () {
        var new_year = obj.year;//取当前的年份
        var new_month = obj.month;//取下一个月的第一天，方便计算（最后一不固定）
        var new_date = new Date(new_year, new_month, 1);                //取当年当月中的第一天
        return (new Date(new_date.getTime() - 1000 * 60 * 60 * 24)).getDate();//获取当月最后一天日期
    },
    //获取当前日期
    getCurrent: function () {
        var dt = obj.date;
        obj.year = dt.getFullYear();
        obj.month = dt.getMonth() + 1;
        obj.day = dt.getDate();
    },

    getLastDate: function () {
        if (obj.year == -1) {
            var dt = new Date(obj.date);
            obj.year = dt.getFullYear();
            obj.month = dt.getMonth() + 1;
        }
        else {
            var newMonth = obj.month - 1;
            if (newMonth <= 0) {
                obj.year -= 1;
                obj.month = 12;
            }
            else {
                obj.month -= 1;
            }
        }

    },
    getNextDate: function () {
        if (obj.year == -1) {
            var dt = new Date(obj.date);
            obj.year = dt.getFullYear();
            obj.month = dt.getMonth() + 1;
        }
        else {
            var newMonth = obj.month + 1;
            if (newMonth > 12) {
                obj.year += 1;
                obj.month = 1;
            }
            else {
                obj.month += 1;
            }
        }

    },
    getDetailDay: function (time) {
        var date = t_exchange(time);
        var dateStr = date.split('-');
        obj.year = parseInt(dateStr[0]);
        obj.month = parseInt(dateStr[1]);
    }
};
var commonUtil = {
    getPrice: function (day) {
        var dt = obj.year + "-";
        if (obj.month < 10)
        {
            dt += "0"+obj.month;
        }
        else
        {
            dt+=obj.month;
        }
        if (day < 10) {
            dt += "-0" + day;
        }
        else {
            dt += "-" + day;
        }

        for (var i = 0; i < obj.priceArr.length; i++) {
            var s1 = $.trim(t_exchange(obj.priceArr[i].time)) + "";
            var s2 = $.trim(dt) + "";
            if (s1 == s2) {
                return obj.priceArr[i].price;
            }
        }
        return -1;
    },

    getOrderId: function (day) {
        var dt = obj.year + "-";
        if (obj.month < 10)
        {
            dt += "0"+obj.month;
        }
        else
        {
            dt+=obj.month;
        }
        if (day < 10) {
            dt += "-0" + day;
        }
        else {
            dt += "-" + day;
        }

        for (var i = 0; i < obj.priceArr.length; i++) {
            var s1 = $.trim(t_exchange(obj.priceArr[i].time)) + "";
            var s2 = $.trim(dt) + "";
            if (s1 == s2){
                return obj.priceArr[i].order_id;
            }
        }
        return -1;
    },

    getTicket: function (day) {
        var dt = obj.year + "-";
        if (obj.month < 10)
        {
            dt += "0"+obj.month;
        }
        else
        {
            dt+=obj.month;
        }
        if (day < 10) {
            dt += "-0" + day;
        }
        else {
            dt += "-" + day;
        }

        for (var i = 0; i < obj.priceArr.length; i++) {
            var s1 = $.trim(t_exchange(obj.priceArr[i].time)) + "";
            var s2 = $.trim(dt) + "";
            if (s1 == s2){
                return obj.priceArr[i].want_number;
            }
        }
        return -1;
    },

    getIntro: function (day) {
        var dt = obj.year + "-";
        if (obj.month < 10)
        {
            dt += "0"+obj.month;
        }
        else
        {
            dt+=obj.month;
        }
        if (day < 10) {
            dt += "-0" + day;
        }
        else {
            dt += "-" + day;
        }

        for (var i = 0; i < obj.priceArr.length; i++) {
            var s1 = $.trim(t_exchange(obj.priceArr[i].time)) + "";
            var s2 = $.trim(dt) + "";
            if (s1 == s2){
                return obj.priceArr[i].buy_number;
            }
        }
        return -1;
    },

    getStatus: function (day) {
        var dt = obj.year + "-";
        if (obj.month < 10) {
            dt += "0"+obj.month;
        }
        else {
            dt+=obj.month;
        }
        if (day < 10) {
            dt += "-0" + day;
        }else {
            dt += "-" + day;
        }

        for (var i = 0; i < obj.priceArr.length; i++) {
            var s1 = $.trim(t_exchange(obj.priceArr[i].time)) + "";
            var s2 = $.trim(dt) + "";
            if (s1 == s2){
                return obj.priceArr[i].status;
            }
        }
        return -1;
    }/*,

    chooseClick: function (sender) {
        var date = sender.getAttribute("date");
        var price = sender.getAttribute("price");
        var order_id = sender.getAttribute("data-id");
        var num = sender.getAttribute("data-num");
        var intro = sender.getAttribute("data-intro");
        var el = document.getElementById(elemId);
        // console.log(el);
        if (el != null) {
            // console.log(sender.firstChild);
            sender.setAttribute("class", "active");
            $('#calendar').val(date).attr('data-value', date);
            $('#order_id').val(order_id);
            // pickerEvent.remove();
            // $('.calendar-matte').remove();
        }
    }*/
};

//计算当前日期
function show_date(){
    var d = new Date();
    var vYear = d.getFullYear();
    var vMon = d.getMonth() + 1;
    var vDay = d.getDate();
    var h = d.getHours();
    var m = d.getMinutes();
    var se = d.getSeconds();
    s=vYear+'-'+vMon+'-'+vDay;
    return s;
}

function show_day(){
    var d = new Date();
    var vYear = d.getFullYear();
    var vMon = d.getMonth() + 1;
    var vDay = d.getDate();
    s=vYear+'-'+(vMon<10 ? "0" + vMon : vMon)+'-'+(vDay<10 ? "0" + vDay : vDay);
    return s;
}

//计算当前月份
function show_month(){
    var d = new Date();
    var vYear = d.getFullYear();
    var vMon = d.getMonth() + 1;
    var vDay = d.getDate();
    var h = d.getHours();
    var m = d.getMinutes();
    var se = d.getSeconds();
    s=vYear+'-'+vMon;
    return s;
}

//标准时间转换
function show_time(){
    var d = new Date();
    var vYear = d.getFullYear();
    var vMon = d.getMonth() + 1;
    var vDay = d.getDate();
    var h = d.getHours();
    var m = d.getMinutes();
    var se = d.getSeconds();
    s=vYear+'-'+(vMon<10 ? "0" + vMon : vMon);
    return s;
}

//取当前时间月份的天数
function getDays(){
    var date = new Date();
    var year = date.getFullYear();
    var mouth = date.getMonth() + 1;
    var days ;
    if(mouth == 2){
        days= year % 4 == 0 ? 29 : 28;
    }
    else if(mouth == 1 || mouth == 3 || mouth == 5 || mouth == 7 || mouth == 8 || mouth == 10 || mouth == 12){
        days= 31;
    }
    else{
        days= 30;

    }
    return days;
}

//取任意月份的天数
function DayNumOfMonth(Year,Month) {
    var d = new Date(Year,Month,0);
    return d.getDate();
}

//时间搓转换成时间
function t_exchange(time){
    var date = new Date(parseInt(time)*1000);
    var Y = date.getFullYear() + '-';
    var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
    var D = (date.getDate() < 10 ? '0'+date.getDate() : date.getDate()) + ' ';
    return Y+M+D;
}

function GetQueryString(name) {
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return decodeURI(r[2]); return null;
}




