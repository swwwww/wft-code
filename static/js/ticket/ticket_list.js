/**
 * Created by deyi on 2016/8/16.
 */
//选择分类加载商品列表
(function(){
    var nav = $('.nav'),
        cate = $('.menu-cate'),
        area = $('.menu-area'),
        hot = $('.menu-new');

    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };


    M.click_tap = 'click';
    //切换分类选项
    nav.on(M.click_tap,'li',function(){
        var $this =$(this);
        itemIndex = $this.index();
        if($this.find('a').hasClass('active')){
            $this.find('a').removeClass('active');
            $('.adv').eq(itemIndex).hide();
        }else{
            $this.find('a').addClass('active');
            $this.siblings('li').find('a').removeClass('active');
            $('.adv').eq(itemIndex).show().siblings('.adv').hide();
            $('.ticket').bind('touchmove',function(e){
                e.preventDefault();
            })
        }
    });

    //所有分类列表
    cate.on(M.click_tap,'a',function(){
        var val = $(this).text();
        $(this).addClass('active').siblings('a').removeClass('active');
        nav.find('li').eq(0).find('mark').text(val);
        nav.find('li').eq(0).find('a').removeClass('active');
        cate.hide();
    });

    //全部区域列表
    area.on(M.click_tap,'span',function(){
        $(this).addClass('active').siblings('span').removeClass('active');
        $(this).next().show().siblings('.area-list-right').hide();
    });

    area.on(M.click_tap,'.tag-list',function(){
        var val = $(this).find('a').text();
        $(this).addClass('active').siblings('li').removeClass('active');
        nav.find('li').eq(1).find('mark').text(val);
        nav.find('li').eq(1).find('a').removeClass('active');
        area.hide();
    });

    //最新上架列表
    hot.on(M.click_tap,'a',function(){
        var val = $(this).text();
        $(this).addClass('active').siblings('a').removeClass('active');
        nav.find('li').eq(2).find('mark').text(val);
        nav.find('li').eq(2).find('a').removeClass('active');
        hot.hide();
    });

    //template时间转换函数
    function dateFormat(date, format){
        date = new Date(date*1000);
        var map = {
            "M": date.getMonth() + 1, //月份
            "d": date.getDate(), //日
            "h": date.getHours(), //小时
            "m": date.getMinutes(), //分
            "s": date.getSeconds(), //秒
            "q": Math.floor((date.getMonth() + 3) / 3), //季度
            "S": date.getMilliseconds() //毫秒
        };

        format = format.replace(/([yMdhmsqS])+/g, function(all, t){
            var v = map[t];
            if (v !== undefined) {
                if (all.length > 1) {
                    v = '0' + v;
                    v = v.substr(v.length - 2);
                }
                return v;
            }
            else if (t === 'y') {
                return (date.getFullYear() + '').substr(4 - all.length);
            }
            return all;
        });
        return format;
    }



    //加载数据
    function GetQueryString(name) {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        // console.log(window.location);
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)return decodeURI(r[2]); return null;
    }

    var id = GetQueryString('id'), //所有分类 下拉列表
        id_name = GetQueryString('id_name'), //全部区域，左边列表的id
        rid = GetQueryString('rid'), //全部区域，左边列表的id
        rid_name = GetQueryString('rid_name'), //全部区域，左边列表的id
        aid = GetQueryString('aid'), //全部区域，右边列表的id
        aid_name = GetQueryString('aid_name'), //全部区域，右边列表的id
        addr_x=GetQueryString('x'), //区域x坐标、
        addr_y=GetQueryString('y'), //区域y坐标
        order=GetQueryString('status'), //状态
        order_name=GetQueryString('status_name'); //状态


    // 使用cookie记录之前的操作记录
    // 地区类别
    if(id){
        localStorage.ticketId = id;
        localStorage.ticketIdName = id_name;
        $('.item-cate').find('mark').text(id_name);
    }else{
        if(localStorage.ticketId){
            id = localStorage.ticketId;
            id_name = localStorage.ticketIdName;
            $('.item-cate').find('mark').text(id_name);
        }else{
            id = 0
        }
    }

    // rid和aid这个是有2级列表
    if(rid_name){
        // 如果选择的是一级标签
        localStorage.ticketRid = rid;
        localStorage.ticketRidName = rid_name;
        localStorage.ticketAid = -1;
        localStorage.ticketAidName = '';
        $('.item-area').find('mark').text(rid_name);
    }else if(!aid && localStorage.ticketAid != -1) {
        if (localStorage.ticketRidName != 0){
            aid = localStorage.ticketAid;
            aid_name = localStorage.ticketAidName;
            $('.item-area').find('mark').text(aid_name);
        }
    }

    if(aid){
        // 当一级标签不存在的时候判断二级标签是否存在
        localStorage.ticketAid = aid;
        localStorage.ticketAidName = aid_name;
        localStorage.ticketRid = -1;
        localStorage.ticketRidName = '';
        $('.item-area').find('mark').text(aid_name);
    }else if(!rid_name && localStorage.ticketRid != -1){
        if (localStorage.ticketAidName != 0) {
            rid = localStorage.ticketRid;
            rid_name = localStorage.ticketRidName;
            $('.item-area').find('mark').text(rid_name);
        }else{
            rid = 0;
        }
    }

    // 某些规则排序
    if(order){
        M.cookie.set('ticket_order', order);
        M.cookie.set('ticket_order_name', order_name);
        $('.item-new').find('mark').text(order_name);
    }else{
        order = M.cookie.get('ticket_order');
        if(order){
            order_name = M.cookie.get('ticket_order_name');
            $('.item-new').find('mark').text(order_name);
        }
    }

    // 如果调用的是附近
    if(rid_name == '附近'){
        var res = M.location.getMapData();
        addr_x=res.lat; //区域x坐标、
        addr_y=res.lng; //区域y坐标
        M.cookie.set('ticket_rid', rid);
        M.cookie.set('ticket_rid_name', rid_name);
        M.cookie.set('ticket_aid', '');
        $('.item-area').find('mark').text(rid_name);
    }

    if(!id && !rid && !aid && !order){
        order = 'new';
    }

    function renderData(){
        var dao = DAO.ticket;
        var param = {
            'id': id,
            'addr_x': addr_x,
            'addr_y': addr_y,
            'order': order,
            'rid': rid,
            'aid': aid,
            'page': 1,
            'wap': 1,
            'page_num':10
        };
        console.log(param);

        dao.getAllCoupons(param, function (res) {
            console.log(res);

            if(res.status ==1){
                var result = [];
                var data = res['data'];
                result['res'] = data['coupon_list'];
                template.helper("dateFormat", dateFormat);
                $('.ticket').append(template('list', result));
            }else{
                config_tips.msg = res.msg;
                M.util.popup_tips(config_tips);
            }
        });
    }
    renderData();


    //加载更多
    var counter = 1;
    var dropload = $('.main').dropload({
        scrollArea : window,
        domDown : {
            domClass: 'dropload-down',
            domRefresh: '<div class="dropload-refresh" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">↑上拉加载更多</div>',
            domLoad: '<div class="dropload-load" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;"><span class="loading"></span>加载中...</div>',
            domNoData: '<div class="dropload-noData" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">已经全部加载完毕</div>'
        },
        //上拉加载函数
        loadDownFn : function(me){
            counter++;
            function renderMore(){
                var dao = DAO.ticket;
                var param = {
                    'id': id,
                    'addr_x': addr_x,
                    'addr_y': addr_y,
                    'order': order,
                    'rid': rid,
                    'aid': aid,
                    'page': counter,
                    'wap': 1,
                    'page_num':10
                };

                dao.getAllCoupons(param, function (res) {
                    if(res.status == 1){
                        var result = [];
                        var data = res['data'];
                        result['res'] = data['coupon_list'];
                        setTimeout(function(){
                            template.helper("dateFormat", dateFormat);
                            $('.ticket').append(template('more', result));
                            me.resetload();
                        },500);
                        if(data['coupon_list'] == 0){
                            me.lock();
                            me.noData();
                            return;
                        }
                    }else{
                        config_tips.msg = res.msg;
                        M.util.popup_tips(config_tips);
                    }
                });
            }
            renderMore();
        }
    });
})();


