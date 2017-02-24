/**
 * Created by Administrator on 2016/12/12 0012.
 */

(function() {
    // //初始化加载
    var date_map = [];
    var choice_num = 0;
    var curr_month =0;
    var add_month = 0;
    var min_child = 0; //最少选择的小孩数
    var min_person = 0; //最少选择的大人数
    var play_id = $('#play_id').val();
    // var plus_val = 0;
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    window.onload = function() {
        var calendar = $('#calendar');
        var now = new Date();
        var now_year = parseInt(now.getFullYear());
        var now_month = parseInt(now.getMonth() + 1); //获取当前月份
        var start_str = show_time() + '-' + '01' + ' 00:00:00',
            end_str = show_time() + '-' + getDays() + ' 24:00:00';
        start_str = start_str.replace(/-/g, ':').replace(' ', ':');
        start_str = start_str.split(':');
        end_str = end_str.replace(/-/g, ':').replace(' ', ':');
        end_str = end_str.split(':');

        var start_time = new Date(start_str[0], (start_str[1] - 1), start_str[2], start_str[3], start_str[4], start_str[5]).getTime() / 1000;
        var end_time = new Date(end_str[0], (end_str[1] - 1), end_str[2], end_str[3], end_str[4], end_str[5]).getTime() / 1000;

        AjaxTime(start_time, end_time);
    }

    $('body').on(M.click_tap, '.pkg_circle_bottom', function(e) {
        e.preventDefault();
        var now = new Date();
        var now_year = parseInt(now.getFullYear());
        var now_month = parseInt(now.getMonth() + 1); //获取当前月份
        //add_month 需要走接口
        var month_end = now_month + add_month;
        var year_end = now_year;

        var cur_val = $('body').find('.date_text').text();
        var date = cur_val.replace(/[\u4e00-\u9fa5]/g, '-');
        var date_str = date.split('-');

        if (month_end > 12) {
            month_end = month_end - 12;
            year_end = now_year + 1;
        }

        if (parseInt(date_str[0], 10) < parseInt(year_end, 10) || (parseInt(date_str[0], 10) == parseInt(year_end, 10)) && parseInt(date_str[1], 10) < parseInt(month_end, 10)) {
            pickerEvent.getNext();
            var cur_val = $('body').find('.date_text').text(); //基准时间
            var date = cur_val.replace(/[\u4e00-\u9fa5]/g, '-');
            var date_str = date.split('-');
            var daycount = DayNumOfMonth(date_str[0], date_str[1]); //当前月份最多天数

            if (cur_val) {
                var start_str = cur_val + '01' + ' 00:00';
                var end_str = cur_val + daycount + ' 00:00';
                var start_time = new Date(start_str.replace(/[\u4e00-\u9fa5]/g, '/')).getTime() / 1000;
                var end_time = new Date(end_str.replace(/[\u4e00-\u9fa5]/g, '/')).getTime() / 1000;
                AjaxTime(start_time, end_time);
            }
        } else {
            config_tips.msg = '只能选择3个月以内的时间哦！';
            M.util.popup_tips(config_tips);
        }
    });

    $('body').on(M.click_tap, '.pkg_circle_top', function(e) {
        e.preventDefault();
        var now = new Date();
        var now_year = parseInt(now.getFullYear());
        var now_month = parseInt(now.getMonth() + 1); //获取当前月份

        var cur_val = $('body').find('.date_text').text();
        var date = cur_val.replace(/[\u4e00-\u9fa5]/g, '-');
        var date_str = date.split('-');

        if (now_year< parseInt(date_str[0]) || (now_year==parseInt(date_str[0]) && now_month < parseInt(date_str[1]))) {
            pickerEvent.getLast();
            var cur_val = $('body').find('.date_text').text(); //基准时间
            var date = cur_val.replace(/[\u4e00-\u9fa5]/g, '-');
            var date_str = date.split('-');
            var daycount = DayNumOfMonth(date_str[0], date_str[1]); //当前月份最多时间

            if (cur_val) {
                var start_str = cur_val + '01' + ' 00:00';
                var end_str = cur_val + daycount + ' 00:00';
                var start_time = new Date(start_str.replace(/[\u4e00-\u9fa5]/g, '/')).getTime() / 1000;
                var end_time = new Date(end_str.replace(/[\u4e00-\u9fa5]/g, '/')).getTime() / 1000;

                AjaxTime(start_time, end_time);
            }
        } else {
            config_tips.msg = '不能选择过去的时间哦！';
            M.util.popup_tips(config_tips);
        }
    });

    $('body').on('click', '.calendar' + ' td', function(e) {
        e.preventDefault();
        var s =new Date(show_day()).getTime()/1000;  //今天的时间
        var now_time =new Date($(this).attr("date")).getTime()/1000;
        var time_item = 0;

        var status = $(this).attr('data-status');
        if(status != 0){
            if(now_time >s){
                if (choice_num <= 3) {
                    if($(this).hasClass('active')){
                        choice_num--;
                        $(this).removeClass('active');
                        time_item = $(this).attr('date');
                        var date_del = time_item;/*M.time.format_to_stamp(time_item)*/
                        date_map =remove_data(date_map, date_del);
                    }else {
                        if(choice_num < 3){
                            $(this).addClass('active');
                            $(this).find('a').addClass('white');
                            $(this).find('span').removeClass('want-go').addClass('white');
                            time_item = $(this).attr('date');
                            date_map[choice_num++] = time_item; /*M.time.format_to_stamp(time_item)*/
                        }else if(choice_num == 3){
                            config_tips.msg = '最多选择3个想去的日期哦！';
                            M.util.popup_tips(config_tips);
                        }
                    }
                }

            }else if(now_time == s){

            }else {
                config_tips.msg = '不能选择过去的时间哦!';
                M.util.popup_tips(config_tips);
            }
        }else {
            config_tips.msg = '本活动可在当天报名!';
            M.util.popup_tips(config_tips);
        }

    });

    function add_style(date_time, current_month) {
        current_month = parseInt(current_month, 10);
        var cur_val = $('#calendar_choose_'+current_month).find('.date_text').text();
        var cur_date = cur_val.replace(/[\u4e00-\u9fa5]/g, '-');
        var cur_str = cur_date.split('-');

        var date_str = date_time.split('-');
        if(date_str[0]==cur_str[0] && date_str[1] == cur_str[1]){
            var td_arr = $('#calendar_choose_'+current_month).find('td');
            for(var i=0; i<td_arr.length; i++){
                if(date_time == td_arr[i].getAttribute("date") ){
                    td_arr[i].setAttribute("class", "active");
                }
            }
        }
    }
    
    function has_choice(time_arry, current_month) {
       for(var i=0; i<time_arry.length; i++){
           var date_str = time_arry[i]; //传过来的日期"xx年xx月xx日"
           add_style(date_str, current_month);
       }
    }

    //删除数据
    function remove_data(date_map, data) {
        //通过删除数字的下标来删除
        Array.prototype.remove=function(dx)
        {
            if(isNaN(dx)||dx>this.length){return false;}
            for(var i=0,n=0;i<this.length;i++)
            {
                if(this[i]!=this[dx])
                {
                    this[n++]=this[i]
                }
            }
            this.length-=1
        }

        for(var i=0; i<date_map.length; i++){
            if(data == date_map[i]) {
                // delete date_map[i];
                date_map.remove(i);
            }
        }
        return date_map;
    }

    /*============================================
    *  选择成人及儿童的人数
    * */
    //加
    $('body').on(M.click_tap, '.add-person', function () {
        var plus_val = parseInt($(this).prev().text(), 10);
        plus_val++;
        $('#js-submit-btn').removeClass('disabled');
        $(this).siblings().removeClass('disabled-btn');
        $(this).prev().text(plus_val);
    });

    //减
    $('body').on(M.click_tap, '.sub-person', function () {
        var min_val = parseInt($(this).parent().find('.person-num').attr('data-min'), 10);
        var plus_val = parseInt($(this).next().text(), 10);

        if(min_val < plus_val){
            --plus_val
        }else{
            config_tips.msg = '一个家庭不能少于'+min_val+'人加该活动!';
            M.util.popup_tips(config_tips);
        }
        if(min_val == plus_val){
            $(this).addClass('disabled-btn');
        }
        $(this).next().text(plus_val);
    });

    //提交数据
    $('body').on(M.click_tap,'#js-submit-btn' ,function() {
        var baby_number = parseInt($('#js_child_num').text(), 10);
        var man_number  = parseInt($('#js_person_num').text(), 10);
        var times=[];
        for(var i=0; i<date_map.length; i++){
            times[i] = M.time.format_to_stamp(date_map[i]);
        }

        if(times.length == 0){
            config_tips.msg = '请选择想去的时间后在提交';
            M.util.popup_tips(config_tips);
            setTimeout(function () {
                $('.matte').hide();
            }, 2000);
        }
        times = JSON.stringify(times);
        var dao = DAO.play;
        var param = {
            'play_id': play_id,
            'man_number': man_number,
            'baby_number': baby_number,
            'times': times
        };

        if(man_number||man_number){
            $('.matte').show();
            dao.goDateSubmit(param, function (res) {
                if(res.status==1){
                    var data = res['data'];
                    var msg = data['message'];
                    $('#js_succ_msg').text(msg)
                    $('#js_succ_submit').show();
                }
            })
        }else {
            config_tips.msg = '请选择想去的人数！';
            M.util.popup_tips(config_tips);
            // window.location = window.location.href;
        }
    });

    //请求数据
    function AjaxTime(start_time, end_time) {
        if (start_time && end_time) {
            var dao = DAO.play;
            var param = {
                'begin_time': start_time,
                'end_time': end_time,
                'play_id': play_id
            };

            $('.matte').show();
            M.load.loadTip('loadType', '数据请求中...', 'delay');
            dao.wantGoList(param, function(res) {
                if (res.status == 1) {
                    var data = res['data'];
                    var day_item = data['days'];
                    add_month = data['max_select_number']-1;
                    min_child = data['child_min'];
                    min_person = data['man_min'];
                    $('#js_person_num').text(min_person);
                    $('#js_person_num').attr('data-min', min_person)
                    $('#js_child_num').text(min_child);
                    $('#js_child_num').attr('data-min', min_child)
                    pickerEvent.setPriceArr(day_item);
                    pickerEvent.Init(" ");
                    $('.append-html').show();
                    $('.matte').hide();

                    var cur_val = $('body').find('.date_text').text();
                    var date = cur_val.replace(/[\u4e00-\u9fa5]/g, '-');
                    var date_str = date.split('-');
                    curr_month = date_str[1];
                    //todo... 把喧染样式的函数加在此
                    has_choice(date_map, curr_month);
                } else {
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                }
            });
        }
    }
})();