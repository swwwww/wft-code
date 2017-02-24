/**
 * Created by Administrator on 2016/12/23 0023.
 */

(function () {
    var lock_people_num = 0;  //还差多少人没有填
    var add_num = 0;
    var join_len = 0;
    var data_ass_id =0;
    var associates_id = [];
    var associates_name = [];
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '24%',
        font_size: 28,
        time: 2500,
        z_index: 100
    };

    //add check style 
    function check_style(associates_id) {
        for (var i = 0; i < associates_id.length; i++) {
            $(".c_" + associates_id[i]).attr("data-select", 1);
            $(".c_"+associates_id[i]).addClass("active");
        }
    }

    //拼接并过滤数组
    function tab(arr1,arr2){
        var arr = arr1.concat(arr2);
        var lastArr = [];
        for(var i = 0;i<arr.length;i++){
            if(!unique(arr[i],lastArr)){
                lastArr.push(arr[i]);
            }
        }
        return lastArr;
    }

    function unique(n,arr){
        for(var i=0;i<arr.length;i++){
            if(n==arr[i]){
                return true;
            }
        }
        return false;
    }

    //加载出行人的数据
    function loadTraveller() {
        var dao = DAO.traveller,
            params = {};

        M.load.loadTip('loadType', '数据请求中...', 'delay');
        dao.getTraveller(params, function (res) {
            if (res.status == 1) {
                var result = [];
                var length = 0;
                var checked_ids=[];
                result['res'] = res['data'];
                length = result.length;
                var html = template('list-item', result);
                $('.list').html(html);

                var checked_travellers = JSON.parse($('#hide_checked_traveller').val());
                for(var i=0; i<checked_travellers.length; i++){
                    checked_ids.push(checked_travellers[i]['associates_id']);
                }
                var associates_id_sub = $('#js_choice_sub').attr('data-ids');

                //把两次选择到的数组合到一起并过滤
                //化成数组并类型统一
                if(associates_id_sub.indexOf(',') != -1){
                    associates_id_sub = associates_id_sub.split(',');
                }

                for(var i = 0; i<associates_id_sub.length; i++){
                    associates_id_sub[i] = parseInt(associates_id_sub[i], 10)
                }

                if(checked_ids.length != 0){
                    if(associates_id_sub.length){
                        associates_id = tab(checked_ids, associates_id_sub);
                    }else {
                        associates_id = checked_ids;
                    }
                }else{
                    //不用考虑是否有checked_ids 已选择的
                }


                check_style(associates_id);
                add_num = associates_id.length;
            }
        });
    }

    function remove_data(date_map, data) {
        //通过删除数字的下标来删除
        Array.prototype.remove=function(dx){
            if(isNaN(dx)||dx>this.length){return false;}
            for(var i=0,n=0;i<this.length;i++){
                if(this[i]!=this[dx]){
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

    $('body').on(M.click_tap, '.js-add-traveller', function () {
        var checked_ids = [];
        lock_people_num = $(this).attr('data-lock-num');
        join_len = $(this).attr('data-join-num');

        loadTraveller();
        if(lock_people_num){
            $('.detail').hide();
            $('.matte1').hide();
            $('.travel-detail').hide();
            $('#js_select_traveller').show();
        }
    });

    //选出行人
    $('body').on(M.click_tap, '.js-select-person', function () {
        var flag = true;
        var traveller_id = 0;
        var traveller_name = '';

        traveller_id = $(this).attr('data-id');
        traveller_name = $(this).attr('data-name');

        if($(this).hasClass('active')){
            $(this).removeClass('active');
            associates_id =remove_data(associates_id, traveller_id);
            add_num=associates_id.length;
        }else {
            if(add_num < join_len){
                $(this).addClass('active');
                for(var i=0; i<associates_id.length; i++){
                    if(traveller_id == associates_id[i]){
                        flag = false;
                    }
                }

                if(flag){
                    associates_id.push(traveller_id);
                }
            }else {
                alert('您的出行人填完毕')
            }

        }
        add_num=associates_id.length;
        if(associates_id.length != 0){
            $('#js_choice_sub').removeClass('back');
            // $('#js_choice_sub').text('确定');
        }
    });

    $('body').on(M.click_touchend, '#js_choice_sub', function () {
        var dao = DAO.order;
        var order_sn = $('#order_detail').attr('data-sn');
        var params = {
            associates_ids: JSON.stringify(associates_id),
            order_sn: order_sn
        }
        add_num=associates_id.length;
        lock_people_num = join_len - add_num;

        if(lock_people_num){
            config_tips.msg = '您还有'+lock_people_num+'出行人未填';
            M.util.popup_tips(config_tips);
            window.location = window.location.href;
        }

        dao.addAssociates(params, function (res) {
            if(res.status == 1){
                var data = res['data'];
                M.load.loadTip('loadType', data.message, 'delay');
            }
        });

        if(lock_people_num){
            $('.no-info').text('您还有'+lock_people_num+'出行人未填');
        }else {
            $('.no-info').text('添加完成');
        }
        $('#js_choice_sub').attr('data-ids', associates_id);
        $('.detail').show();
        $('#js_select_traveller').hide();
    });

    //添加出行人
    $('body').on(M.click_tap, '#js_add_travel', function () {
        $('.detail').hide();
        $('.matte1').hide();
        $('#js_select_traveller').hide();
        $('#js_add_traveller').show();
    });

    //添加及编辑出行人提交
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

    //添加出行人
    $('body').on(M.click_tap, '#per-submit', function () {
        var name = $("#pname"),
            card_id = $("#pid"),
            id = data_ass_id;
        var flag = M.verify.nameFormat(name) && M.verify.idCardNumFormat(card_id);
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
                        loadTraveller();
                        //返回到出人列表页
                        setTimeout(function () {
                            $('#detail').hide();
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
                        loadTraveller();
                        setTimeout(function () {
                            $('#detail').hide();
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
        $('#detail').show();
        $('#js_select_traveller').hide();
        $('#js_add_traveller').hide();
    });
})();