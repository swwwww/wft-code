/**
 * Created by deyi on 2016/9/3.
 */

(function(){
    var state = $('#state').val(),
        ticket_num = $('.ticket-num'),
        total = $('.total-content-price'),
        total_money,
        surplus_num,
        order_id,
        price = parseFloat($('.ticket-price').find('mark').text());

    var config_tips = {
        msg: '',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    var config = {
        title: '',
        notice : '',
        height : 800,
        width : 600,
        top : '10%',
        is_border: false
        
    };

    if(state == 1){
        var calendar = $('#calendar');
        calendar.on(M.click_tap, function() {
            if (!calendar.attr('data-value')) {
                var start_str = show_time() + '-' + '01' + ' 00:00:00',
                    end_str = show_time() + '-' + getDays() + ' 24:00:00';
                start_str=start_str.replace(/-/g,':').replace(' ',':');
                start_str=start_str.split(':');
                end_str=end_str.replace(/-/g,':').replace(' ',':');
                end_str=end_str.split(':');
                var start_time = new Date(start_str[0],(start_str[1]-1),start_str[2],start_str[3],start_str[4],start_str[5]).getTime()/1000;
                var end_time = new Date(end_str[0],(end_str[1]-1),end_str[2],end_str[3],end_str[4],end_str[5]).getTime()/1000;

                console.log(start_time);
                console.log(end_time);
                console.log(order_start);
                if(order_start >= end_time){
                    start_time = order_start;
                    end_time =order_end;
                    AjaxTime(start_time, end_time);
                    pickerEvent.getDetail(start_time);
                }else{
                    AjaxTime(start_time, end_time);
                    pickerEvent.getToday();
                }
            }else{
                var now = calendar.attr('data-value');
                var now_str = now.split('-');
                var daycount = DayNumOfMonth(now_str[0],now_str[1]);
                var start_str = now_str[0] + '-' + now_str[1] + '-01' + ' 00:00:00';
                var end_str = now_str[0] + '-' + now_str[1] + '-' +daycount + ' 24:00:00';
                start_str=start_str.replace(/-/g,':').replace(' ',':');
                start_str=start_str.split(':');
                end_str=end_str.replace(/-/g,':').replace(' ',':');
                end_str=end_str.split(':');
                var start_time = new Date(start_str[0],(start_str[1]-1),start_str[2],start_str[3],start_str[4],start_str[5]).getTime()/1000;
                var end_time = new Date(end_str[0],(end_str[1]-1),end_str[2],end_str[3],end_str[4],end_str[5]).getTime()/1000;
                AjaxTime(start_time,end_time);
            }
        })
    }else{
        var timeList = $('#time_list'),
            placeList = $('#place_list'),
            time,
            min_num,
            max_num,
            has_addr;
        order_id = '';

        timeList.find('input').each(function(){
            var $this = $(this);
            var surplus_num = $this.attr('data-num');
            var surplus_num_arr = surplus_num.split('|');
            $this.next().addClass('active');
            $this.prop('disabled',true);
            for(var i in surplus_num_arr){
                if(surplus_num_arr[i] > 0){
                    $this.next().removeClass('active');
                    $this.prop('disabled',false);
                    return;
                }
            }
        });

        timeList.on('change','input',function(e){
            var $this = $(e.target),
                place = $this.attr('data-place'),
                place_arr = place.split('|');
            time = $this.attr('data-time');
            placeList.find('input').each(function(){
                var obj = $(this);
                obj.next().addClass('active');
                for(var i in place_arr){
                    if(obj.attr('data-place') == place_arr[0]){
                        obj.prop('checked',true).trigger('change');
                    }

                    if(obj.attr('data-place') == place_arr[i]){
                        obj.prop('disabled',false);
                        obj.next().removeClass('active');
                        return;
                    }else{
                        obj.prop('disabled',true);
                    }
                }
            });
        });

        placeList.on('change','input',function(){
            var p_time = $(this).attr('data-time'),
                p_id = $(this).attr('data-id'),
                p_min = $(this).attr('data-min'),
                p_max = $(this).attr('data-max'),
                p_buy = $(this).attr('data-buy'),
                p_total = $(this).attr('data-total'),
                p_price = $(this).attr('data-price'),
                p_addr = $(this).attr('data-addr'),
                p_id_str = p_id.split('|'),
                p_time_str =p_time.split('|'),
                p_min_str = p_min.split('|'),
                p_max_str = p_max.split('|'),
                p_buy_str = p_buy.split('|'),
                p_total_str = p_total.split('|'),
                p_price_str = p_price.split('|'),
                p_addr_str = p_addr.split('|');

            for(var i in p_time_str){
                if(time == p_time_str[i]){
                    order_id = p_id_str[i];
                    has_addr = p_addr_str[i];
                    max_num = p_max_str[i];
                    min_num = p_min_str[i];
                    surplus_num = p_total_str[i] - p_buy_str[i];
                    $('#order_id').val(order_id);
                    $('#ticket_intro').find('p').empty().html('最少购买'+p_min_str[i]+'张，最多购买'+max_num+'张，仅剩'+surplus_num+'张');
                    $('.ticket-price').find('mark').text(p_price_str[i]);

                    if(parseInt(surplus_num) <= parseInt(ticket_num.text())){
                        ticket_num.text(surplus_num);
                        total_money = parseFloat((ticket_num.text() * price).toFixed(2));
                        total.text('￥' + total_money);
                        updateBanner(total_money);
                    }
                }
            }
        });
        timeList.find('label').not('.active').eq(0).prev().prop('checked',true).trigger('change');
    }


    $('.ticket-plus').on(M.click_tap,function(){
        var num = parseInt(ticket_num.text());
        var date_num = $('.num_ticket').find('mark').text();
        order_id = $('#order_id').val();

        if(!order_id){
            config_tips.msg = '请选择出行日期';
            M.util.popup_tips(config_tips);
            return false
        }

        if(surplus_num == 0){
            config_tips.msg = '亲，您所选的商品库存不足！';
            M.util.popup_tips(config_tips);
            return false
        }

        num = num + 1;

        if(state == 1){
            if(num > max_num_b || num > date_num ){
                var normal_temp_num_ = parseInt(num) - 1;
                config_tips.msg = '最多购买'+normal_temp_num_+'张！';
                M.util.popup_tips(config_tips);
                return false
            }
        }else{
            if(num > max_num || num > surplus_num){
                var special_temp_num = parseInt(num) - 1;
                config_tips.msg = '最多购买'+special_temp_num+'张！';
                M.util.popup_tips(config_tips);
                return false
            }
        }

        ticket_num.text(num);
        total.text('￥'+(num*price).toFixed(2));
        calculate();
    });

    $('.ticket-minus').on(M.click_tap,function(){
        var num = parseInt(ticket_num.text());

        if(surplus_num == 0){
            return false
        }

        num = num - 1;
        if(num == 0){
            return false;
        }

        ticket_num.text(num);
        total.text('￥'+(num*price).toFixed(2));
        calculate();
    });

    window.calculate = function() {
        if(surplus_num == 0){
            ticket_num.text('0');
            total_money = 0;
            total.text('￥' + total_money)
        }else{
            price = parseFloat($('.ticket-price').find('mark').text()).toFixed(2);
            total_money = (ticket_num.text() * price).toFixed(2);
            total.text('￥' + total_money);
        }
        updateBanner(total_money);
    };

    calculate();

    //弹窗处理
    $('.tips').on(M.click_tap,'.introduce',function(){
        var title = $(this).find('mark').text(),
            content = $(this).find('span').text();
        config['title'] = title;
        config['notice'] = content;
        M.util.popup_notice(config);
    });

    $('.total-next').on(M.click_tap,function(){
        var is_remark = $('#is_remark').attr('data-remark'),
            remark_content = $('.remark-content').val(),
            num = parseInt($('.ticket-num').text()),
            contacts = $('#contacts'),
            param = $('#param'),
            name = contacts.attr('data-name'),
            phone = contacts.attr('data-phone'),
            city = contacts.attr('data-city'),
            address = contacts.attr('data-addr'),
            g_buy = param.attr('data-group'),
            coupon_id = param.attr('data-coupon'),
            g_buy_id = param.attr('data-group-id'),
            info_addr = $('.information-addr').val();
        order_id = $('#order_id').val();

        if(state == 1){
            if(!order_id){
                var start_str = show_time() + '-' + '01' + ' 00:00:00',
                    end_str = show_time() + '-' + getDays() + ' 24:00:00';
                start_str=start_str.replace(/-/g,':').replace(' ',':');
                start_str=start_str.split(':');
                end_str=end_str.replace(/-/g,':').replace(' ',':');
                end_str=end_str.split(':');
                var start_time = new Date(start_str[0],(start_str[1]-1),start_str[2],start_str[3],start_str[4],start_str[5]).getTime()/1000;
                var end_time = new Date(end_str[0],(end_str[1]-1),end_str[2],end_str[3],end_str[4],end_str[5]).getTime()/1000;


                if(order_start >= end_time){
                    start_time = order_start;
                    end_time =order_end;
                    AjaxTime(start_time, end_time);
                    pickerEvent.getDetail(start_time);
                }else{
                    AjaxTime(start_time, end_time);
                    pickerEvent.getToday();
                }
                config_tips.msg = '请选择出行日期';
                M.util.popup_tips(config_tips);
                return false
            }
        }else{
            if(!order_id){
                config_tips.msg = '请选择时间地点';
                M.util.popup_tips(config_tips);
                return false
            }
        }
        if(num < min_num ){
            config_tips.msg = '该商品最少购买'+min_num+'张';
            M.util.popup_tips(config_tips);
            return false
        }

        if(is_remark == 1){
            if(!remark_content){
                config_tips.msg = '请填写备注!';
                M.util.popup_tips(config_tips);
                return false
            }
        }

        if(has_addr == 1){
            if(!info_addr){
                config_tips.msg = '请填写收货地址!';
                M.util.popup_tips(config_tips);
                return false
            }
        }

        $(this).text('请等候...').css({'background':'#ccc'}).prop('disabled',true);

        var is_use_score = $('#score').val();
        // 商品跳转收银台需要的参数
        var param_ticket = {
            'order_type': 1,
            'coupon_id': coupon_id,
            'info_id': order_id,
            'number': num,
            'group_buy': g_buy,
            'group_buy_id': g_buy_id,
            'message': remark_content,
            'associates_ids': 0,
            'phone': phone,
            'name': name,
            'address': address,
            'use_score': is_use_score
        };
        param_ticket = M.util.getUrlFromJson(param_ticket);
        window.location.href = base_url_module + 'orderWap/orderCheckOut?' + param_ticket;
    });
})();















