/**
 * Created by xxxian on 2016/8/26 0026.
 */

(function () {
    var meeting_num = parseInt($('#js_meeting_num').val(), 10), //集合地点的数量
        priceItem = $('.price_item'),//出行人收费项
        otherPriceItem = $('.other-item'), //其他收费项
        people_num = 0,//出行人数量
        people_check_num = 0, // 勾选出行人数量
        data = JSON.parse($('#data').val()),
        car_addr_id = 0,  //集合地点
        meeting_addr = {},
        data_ass_id = 0,
        ids = [], //存储出行id
        free_price = 0,
        config_tips = {
            msg: '提示信息',
            padding_tb: '4%',
            padding_rl: '4%',
            top: '24%',
            font_size: 28,
            time: 2500,
            z_index: 100
        },
        config_select = {
            title: '选择集合地点',
            notice: '',
            height: 545,
            width: 600,
            top: '15%',
            is_bg_close: false,// 是否点击浮层后关闭弹出框：true-关闭 false-不关闭
            is_border: false,
            is_bg: false //没有背景
        };

    /********************************************
     * "提交订单"按钮事件
     * */
    $('body').on(M.click_tap, '#js_submit_btn', function (e) {
        e.preventDefault();
        var people_num = getsum(priceItem),
            phone = $('.address-ph').text(),
            associates_ids = "[" + $("#associates_ids").val() + "]", //填写的出行人的id集合
            sid = $("#sid").val(), //订单号
            id = $("#coupon_id").val(), //订单号
            name = $(".address-name").text(), //收货人
            total_price = $("#js_total").text(), //总价
            car_addr_id = $('#car_addr').val(),//集合地点
            link_address = $(".address-position").text(),
            charges_people = getCharges(priceItem), //出行人数据
            charges_other = getCharges(otherPriceItem), //出行人数据
            charges = charges_people.concat(charges_other);

        if (people_num <= 0) {
            give_notcie();
        }

        if (people_check_num > people_num){
            config_tips.msg = '出行人数选择多于票数！';
            M.util.popup_tips(config_tips);
        }else{
            //弹出集合地选择弹框
            if (people_num > 0 && meeting_num != 1 && car_addr_id == 0) {
                select_meeting_place(meeting_num);
            } else if (people_num > 0 && car_addr_id > 0) {
                $('#js_submit_btn').text('请稍候..').css({
                    'background-color': '#ccc',
                    'border-top': '1px solid #ccc'
                }).attr({"disabled": "disabled"});

                var param_play = {
                    'order_type': 2,
                    'coupon_id': id,
                    'info_id': sid,
                    'total': total_price,
                    'meet_id': car_addr_id,
                    'associates_ids': associates_ids,
                    'charges': JSON.stringify(charges),
                    'name': name,
                    'phone': phone,
                    'address': link_address
                };

                param_play = M.util.getUrlFromJson(param_play);
                window.location.href = base_url_module + 'orderWap/orderCheckOut?' + param_play;
            }
        }
    });


    /**************************************************************************************************************
     * 功能函数
     * */
    //加减按钮事件
    function add_sub(minVal, maxVal) {
        var join_num = parseInt($('#js_join_num').val(), 10);
        var pref_num = parseInt($('#js_perf_num').val(), 10);

        if (join_num >= pref_num) {
            config_tips.msg = '票已售罄哦！';
            M.util.popup_tips(config_tips);
        } else {
            $('body').on(M.click_tap, '.plus-btn', function () {
                var maxVal = parseInt($(this).parent().find('#max_num').val(), 10);
                var minVal = parseInt($(this).parent().find('#min_num').val(), 10);
                var numPlus = $(this).prev();
                var plusVal = parseInt(numPlus.text(), 10); //页面展示的数据
                var type = $(this).attr('data-type');

                colse_notice();
                $('.popup-mask').hide();
                if (minVal == 0 && maxVal == 0) {//不限购
                    numPlus.prev().removeClass("disable");
                    plusVal++;
                    numPlus.text(plusVal);
                } else { //限购
                    if (type == 'other') {
                        maxVal = 2000;
                    }
                    if (plusVal < maxVal) {
                        numPlus.prev().removeClass("disable");
                        plusVal++;
                        numPlus.text(plusVal);
                    } else if (plusVal >= maxVal) {
                        config_tips.msg = "最多购买" + maxVal + "张哦！";
                        M.util.popup_tips(config_tips);
                        $('.popup-mask').hide();
                        $(this).attr("disabled", true);
                    }
                }
                calculate_total();
                limit_num(minVal, maxVal);
            });

            $('body').on(M.click_tap, '.minus-btn', function () {
                var maxVal = parseInt($(this).parent().find('#max_num').val(), 10);
                var minVal = parseInt($(this).parent().find('#min_num').val(), 10);
                var numMinus = $(this).next();
                // var minusVal = parseInt(numMinus.text(), 10); //页面展示的数据
                var numVal = parseInt(numMinus.text(), 10);
                var type = $(this).attr('data-type');

                if (minVal == 0 && minVal == 0) {
                    if (numVal > 1) {
                        numVal--;
                        numMinus.text(numVal);
                        $(this).next().next().removeClass('disable_add');
                    } else if (numVal == 1) {
                        numVal--;
                        numMinus.text(numVal);
                        $(this).attr("disabled", true);
                        $(this).addClass("disable");
                    }
                } else {
                    if (type == 'other') {
                        minVal = 0;
                    }
                    if (numVal > minVal) {
                        numVal--;
                        numMinus.text(numVal);
                        $(this).next().next().removeClass('disable_add');
                    } else if (numVal == minVal) {
                        config_tips.msg = "最少购买" + minVal + "张哦！";
                        M.util.popup_tips(config_tips);

                        numMinus.text(numVal);
                        $(this).attr("disabled", true);
                        $(this).addClass("disable");
                    }
                }
                calculate_total();
                limit_num(minVal, maxVal);
            });
        }
    }

    add_sub();

    //计算总价
    function calculate_total() {
        var num = 0,
            price = 0,
            total = 0,
            other_num = 0,
            other_price = 0,
            other_total = 0,
            discount = 0,
            final_total = 0;
        //求优惠项目（“满。。。减。。。”）
        var full = $('#js_full_price').text(),
            less = $('#js_less_price').text();

        //正常收费项
        var priceItem = $(".price_item"),
            Len = priceItem.length;
        for (var i = 0; i < Len; i++) {
            num = $(priceItem[i]).find(".num-total").text();
            price = $(priceItem[i]).find('.ticket-price').find("mark").text();

            var vip_area = $(priceItem[i]).next('.vip-area').find('.radio-vip-choice');
            var vip_check = vip_area.attr('checked');
            if(vip_check != undefined && vip_check != 'false'){
                if(num > 0){
                    var vip_free_num = $(priceItem[i]).next('.vip-area').data('free-num');
                    $(priceItem[i]).data('free-num', vip_free_num);
                }else{
                    $(priceItem[i]).data('free-num', 0);
                }
                num = num - 1;
                num = num > 0 ? num : 0;
            }else {
                //todo 更新data-free-num的状态
                $(priceItem[i]).data('free-num', 0);
            }

            total += parseInt(num) * parseFloat(price);
        }

        //其他收费项
        var input = $("input[type=checkbox][name='cost']");
        var len = input.length;
        for (var i = 0; i < len; i++) {
            other_num = parseInt($(input[i]).parent().next().find(".num-total").text());
            other_price = parseFloat($(input[i]).parent().next().next().find("mark").text());
            other_total += other_num * other_price;
        }

        final_total = (total + other_total).toFixed(2); //没有优惠的总金额

        //求优惠金额
        if (parseFloat(full) > 0 && parseFloat(less) > 0) {
            discount = Math.floor(final_total / parseInt(full)) * parseInt(less);
        }
        final_total = (final_total - discount - free_price).toFixed(2);
        updateBanner(final_total);
        $("#js_total").text(final_total);
    }

    // function updateBanner(price) {
    //     var chargePrice = $('#once_charge');
    //     var sendPrice = $('#send_money');
    //     var dao = DAO.member;
    //     dao.getVipSession(null, function (res) {
    //         var data = res.data;
    //         for(var i=0; i<data.length; i++){
    //             if(price <= data[i]['price']){
    //                 sendPrice.text(data[i]['free_money']);
    //                 chargePrice.text(data[i]['price']);
    //                 break;
    //             }
    //         }
    //     });
    // }
    // updateBanner(0);

    calculate_total();

    //显示按钮状态函数
    function limit_num(minVal, maxVal) {
        //遍历加减按钮
        var lists = $('.activity-num');
        lists.find(".num-total").each(function () {
            if ($(this).text() == minVal || $(this).text() == 0) {
                $(this).prev().addClass("disable");
                $(this).next().removeClass('disable_add');
            }
            if ($(this).text() == maxVal) {
                $(this).next().addClass("disable_add");
            }
        });
    }

    //求和，用于计算出行人数量及选择的购买保险费用的人数
    function getsum(arr) {
        //用于装对应值的数组
        var len = arr.length,
            count = [];
        for (var i = 0; i < len; i++) {
            var type = $(arr[i]).attr('data-type');
            var num = $(arr[i]).attr('data-num');
            count.push(num * (parseInt($(arr[i]).find(".num-total").text())));
        }
        //数组求和函数
        Array.prototype.sum = function () {
            var sum = 0;
            for (var i = 0; i < this.length; i++) {
                sum += parseInt(this[i]);
            }
            return sum;
        };
        //计算出行人数，或买的其他票数
        return count.sum();
    }

    //求和，用于计算出行人数量及选择的购买保险费用的人数
    function getCharges(arr) {
        var len = arr.length,
            charges = [],
            i = 0;
        for (; i < len; i++) {
            var type = $(arr[i]).attr('data-type');

            var buy_num = parseInt($(arr[i]).find(".num-total").text());
            var id = $(arr[i]).attr('data-id');
            var temp = {};
            temp.id = id;
            temp.buy_number = buy_num;
            temp.is_other = type;
            temp.free_buy_number = $(arr[i]).attr('data-free-num') > 0 ?1:0;
            charges.push(temp);
        }
        return charges;
    }

    //提示购买出行人票函数
    function give_notcie() {
        config_tips.msg = "请选择购买出行人票券！";
        config_tips.is_matte = true;
        M.util.popup_tips(config_tips);
        $('#js_ask_choice').css({
            'position': 'relative',
            'z-index': 3,
            'border': '4px solid rgb(250, 110, 81)',
            'margin': ' auto 20px'
        });
        $('body').scrollTo({toT: 0});
        $('body').css({
            'overflow': 'hidden'
        });
        setTimeout(function () {
            colse_notice();
            $('.popup-mask').hide();
        }, 2500);
    }

    // //提示关闭函数
    function colse_notice() {
        $('#js_ask_choice').css({
            'position': 'static',
            'z-index': 0,
            'border': 'none',
            'margin': 0
        });
        $('body').css({
            'overflow': 'scroll'
        });
    }
    

    //选择资格券点击可取消
    $('body').on(M.click_tap, '.vip-area', function () {
        var radio_item = $(this).find('.radio-vip-choice');
        var radio_val = radio_item.attr("checked");

        if(radio_val == 'false'){
            radio_item.attr("checked", 'true');
            $(this).addClass('choice-in');
            var item_data_free = radio_item.parent().attr('data-free-num');
            $(this).prev().data('free-num', item_data_free);
        }else {
            radio_item.attr("checked", 'false');
            $(this).prev().data('free-num', 0);
            $(this).removeClass('choice-in');
        }
        calculate_total();
    });

    /********************************************
     * "选择集合地点"区域按钮事件
     * */
    $('body').on(M.click_touchend, '.addr_sel', function () {
        select_meeting_place(meeting_num);
    });

    /*************************************
     *选择"出行人人数"
     * */
    $('body').on(M.click_tap, '#js_choice_people', function () {
        people_num = getsum(priceItem);
        if (people_num <= 0) {
            give_notcie();
        } else {
            $('#applic').hide();
            $('#js_select_traveller').show();
            loadTraveller(people_num);
            //选择出行人
        }
    });


    /*
     * 编辑和添加出行人
     * */
    //添加出行人
    $('body').on(M.click_tap, '#js_add_travel', function () {
        $('#applic').hide();
        $('#js_select_traveller').hide();
        $('#js_add_traveller').show();
    });

    //编辑出行人
    $('body').on(M.click_tap, '#js-edit-elem', function () {
        var pid = $(this).attr('data-pid');
        var pname = $(this).attr('data-name');
        data_ass_id = $(this).attr('data-id');

        $('#pname').val(pname);
        $('#pid').val(pid);
        $('#applic').hide();
        $('#js_select_traveller').hide();
        $('#js_add_traveller').show();
    });

    //添加及编辑出行人提交
    $('body').on(M.click_tap, '#per-submit', function () {
        var name = $("#pname"),
            card_id = $("#pid"),
            people_num = getsum(priceItem),
            id = data_ass_id;

        var flag = Verify_person(name, card_id);
        if (flag) {
            var dao = DAO.traveller,
                param = {};
            M.load.loadTip('loadType', '提交中...', 'delay');

            if (id > 0) {//编辑出行人
                param = {
                    'associates_id': id,
                    'id_num': card_id.val(),
                    'name': name.val()
                };
                dao.playEditTraveller(param, function (res) {
                    if (res.status == 1) {
                        res = res['data'];

                        M.load.loadTip('doType', res.message, 'delay');
                        // config_tips.msg = res.message;
                        // M.util.popup_tips(config_tips);
                        $('#js_person_info').removeClass('back');
                        loadTraveller(people_num);
                        //返回到出人列表页
                        setTimeout(function () {
                            $('#applic').hide();
                            $('#js_select_traveller').show();
                            $('#js_add_traveller').hide();
                        },2500);
                    }
                });
            } else { //添加出行人
                param = {
                    'name': name.val(),
                    'id_num': card_id.val()
                };
                dao.playAddTraveller(param, function (res) {
                    if (res.status == 1) {
                        res = res['data'];

                        M.load.loadTip('doType', res.message, 'delay');
                        // config_tips.msg = res.message;
                        // M.util.popup_tips(config_tips);
                        loadTraveller(people_num);
                        setTimeout(function () {
                            $('#applic').hide();
                            $('#js_select_traveller').show();
                            $('#js_add_traveller').hide();
                        },2500);
                    } else if (res.status == 0) {
                        config_tips.msg = res.message;
                        M.util.popup_tips(config_tips);
                    }
                });
            }
        }
    });

    //添加完出行人返回
    $('body').on(M.click_tap, '#js_choice_sub', function () {
        $('#applic').show();
        $('#js_select_traveller').hide();
        $('#js_add_traveller').hide();
    });

    //喧染编辑出行人数据的函数
    function loadTraveller(people_num) {
        var dao = DAO.traveller,
            params = {};

        dao.getTraveller(params, function (res) {
            if (res.status == 1) {
                var result = [];
                var length = 0;
                result['res'] = res['data'];
                length = result.length;
                var html = template('list-item', result);
                $('.list').html(html);

                var idsObj = $("#associates_ids").val().split(","),
                    len = idsObj.length;
                for (var i = 0; i < len; i++) {
                    $(".r_" + idsObj[i]).attr("checked", true);
                    $(".r_" + idsObj[i]).val("1");
                    // $(".r_"+idsObj[i]).addClass("active");
                }
                select_people(people_num);
            }
        });
    }
    loadTraveller();

    /*********************************
     * 选择"收货地址"
     ***/
    $('body').on(M.click_tap, '.take-good', function () {
        $('#applic').hide();
        $('#js_select_addr').show();
        select_rec_addr();
    });

    //选择出行人
    function select_people(people_num) {
        var people_num = people_num;
        $('.form-radio').on('change', function (e) {
            e.preventDefault();
            var chk_num = $('input[type=checkbox][name="radio"]:checked').length;

            if (chk_num > 0) {
                $('#js_choice_sub').text('确认').removeClass('back');
            } else {
                $("#js_choice_sub").text("返回").addClass("back");
            }
            people_check_num = chk_num;
            if (chk_num > people_num) {
                config_tips.msg = "抱歉，保险最多人数为" + people_num;
                M.util.popup_tips(config_tips);
                $(this).prop("checked", false);
                return false;
            } else {
                if ($(this).val() == 0) {
                    $(this).attr("checked", true);
                    $(this).val("1");
                } else {
                    $(this).val("0");
                    $(this).attr("checked", false);
                }
            }
        });

        //"点击确定"的事件
        $("#js_choice_sub").on("tap", function (e) {
            ids = [];
            e.preventDefault();
            var chk = $('input[type=checkbox][name="radio"]:checked'),
                chk_length = chk.length,
                names = '';
            for (var i = 0; i < chk_length; i++) {
                if (i == chk_length - 1) {
                    names += $(chk[i]).attr('data-name');
                } else {
                    names += $(chk[i]).attr('data-name') + ',';
                }

                ids.push($(chk[i]).attr("data-id"));
            }

            // 在完成订单之后补充数据的情况
            if (data.order_sn){
                var temp_people_num = data.un_people_num;
                $('#associates_ids').val(ids);
                var num = ids.length;
                if (num > temp_people_num) {
                    var diff = parseInt(num - temp_people_num);
                    config_tips.msg = '出行人选择过多, 您多选择了' + temp_people_num + '个出行人';
                    M.util.popup_tips(config_tips);
                    window.location.reload();
                }else{
                    var daoOrder = DAO.order;
                    var param = {
                        'order_sn':data.order_sn,
                        'associates_ids':JSON.stringify(ids)
                    };
                    daoOrder.addAssociates(param, function (res) {
                        if(res.status == 1){
                            M.load.loadTip('doType', res.msg, 'delay');
                            window.location.href = "/orderWap/orderPlayDetail?order_sn="+data.order_sn;
                        }else{
                            M.load.loadTip('errorType', res.msg, 'delay');
                        }
                    })
                }
            }

            if (chk_length && !$(this).hasClass("back")) {
                $('.people-name').text(names);
                $('#associates_ids').val(ids);
                var num = ids.length;
                if (num < people_num) {
                    var diff = parseInt(people_num - num);

                    config_tips.msg = '您还有' + diff + '个出行人信息未填写，这些信息将被用于购买保险';
                    if(diff){
                        $('.notice-num').html('需要添加' +diff +'位出行人');
                    }else{
                        $('.notice-num').html('你的出行人已填完');
                    }
                    M.util.popup_tips(config_tips);
                }
                $('#applic').show();
                $('#js_select_traveller').hide();


            } else {
                // $('#applic').show();
                // $('#js_select_traveller').hide();
            }
        });

    }

    // 选择收货地址
    function select_rec_addr() {
        $('.radio-input').on('change', function () {
            var dao = DAO.ticket;
            var id = $(this).val();
            var param = {
                'id': id
            };

            dao.setDefaultAddr(param, function (res) {
                if (res.status == 1) {
                    res = res['data'];
                    config_tips.msg = res.message;
                    M.util.popup_tips(config_tips);
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                }
            });
        });
    }

    //选择集合地点
    function select_meeting_place(meeting_num) {
        var dao = DAO.play;
        var sid = $('#sid').val();
        var time = null;
        var ty = '';
        var params = {
            'session_id': sid
        };
        $('.matte').show();
        M.load.loadTip('loadType', '提交中...', 'delay');
        dao.getAddrLists(params, function (res) {
            if (res.status == 1) {
                var res = res['data'];
                meeting_addr = res['meeting'];
                time = meeting_addr[0].meeting_time;
                car_addr_id = $('#car_addr').val();//集合地点

                for (var i = 0; i < meeting_addr.length; i++) {
                    ty = ty + '<div class="popup-item">' +
                        '<span class="item-span dib">' +
                        meeting_addr[i].meeting_place +
                        '</span>' +
                        '<span class="item-time dib">' +
                        M.time.month_to_minute(meeting_addr[i].meeting_time) +
                        '</span>';
                    if (car_addr_id == meeting_addr[i].id) {
                        ty += '<input class="popup-input pa" checked type="radio" name="radio" id="address' +
                            meeting_addr[i].id + '" data-id="' + meeting_addr[i].id + '">';
                    } else {
                        ty += '<input class="popup-input pa" type="radio" name="radio" id="address' +
                            meeting_addr[i].id + '" data-id="' + meeting_addr[i].id + '">';
                    }
                    ty += '<label class="popup-label" for="address' + meeting_addr[i].id + '"></label>' +
                        '</div>';
                }

                config_select.title = "选择集合地点";
                config_select.notice = ty;
                M.util.popup_choice(config_select);
            }

        });

        //点击“是”事件
        $('body').on(M.click_touchend, '.right-btn', function (e) {
            var count = 0;
            e.preventDefault();
            $('#popup_notice').find('input').each(function () {
                var sel = $(this).is(':checked');
                if (sel == false) {
                    ++count;
                }
                if (count == meeting_num) { //没有选择集合地点
                    config_tips.msg = '请选择集合地点！';
                    M.util.popup_tips(config_tips);
                } else {
                    if (sel == true) {
                        $("#car_addr").val($(this).attr("data-id"));
                        var input_flag = '#address' + $(this).attr("data-id");
                        var addr = $(this).prev().prev().text();
                        $(".addr_sel").text(addr);
                        M.util.hidePopup();
                        $('.matte').hide();
                    }
                }
            });
        });
        //点击否
        $('body').on(M.click_touchend, '.left-btn', function (e) {/*cancel-btn*/
            e.preventDefault();
            $('.matte').hide();
            M.util.hidePopup();
        });
    }

    //验证输入姓名及身份证信息函数 信输入有误返回false， 正确返回为true
    function Verify_person(name, card_id) {
        var nameReg = /^[\u4E00-\u9FA5]+$/,
            idReg = /^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/;

        if (name == '') {
            config_tips.msg = "您还未输入姓名";
            M.util.popup_tips(config_tips);
            setTimeout(function () {
                $('.per-button').attr('disabled', false);
            }, 3000);
            name.focus();
            return false;
        }

        if (!nameReg.test(name.val())) {
            config_tips.msg = "请输入中文名！";
            M.util.popup_tips(config_tips);
            setTimeout(function () {
                $('.per-button').attr('disable', false);
            }, 3000);
            card_id.focus();
            return false;
        }

        if (card_id == '') {
            config_tips.msg = "您还未输入身份证号";
            M.util.popup_tips(config_tips);
            setTimeout(function () {
                $('.per-button').attr('disabled', false);
            }, 3000);
            card_id.focus();
            return false;
        } else if (!idReg.test(card_id.val())) {
            config_tips.msg = "身份证格式不正确！";
            M.util.popup_tips(config_tips);
            setTimeout(function () {
                $('.per-button').attr('disabled', true);
            }, 3000);
            card_id.focus();
            return false;
        }

        return true;
    }

})();

