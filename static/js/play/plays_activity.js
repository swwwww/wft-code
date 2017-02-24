/**
 * Created by xxxian on 2016/9/12 0012.
 */

(function () {
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '25%',
        font_size: 28,
        time: 2500,
        z_index: 100
    };
    var config = {
        title: '集合方式',
        notice : '',
        height : 800,
        width : 600,
        top : '10%',
        is_border : false// 是否有边框
    };

    //弹窗
    $('body').on(M.click_tap, '#js_gather_way', function () {
        config.title = '集合方式';
        config.notice = $('#gather_way').val();
        M.util.popup_notice(config);
    });

    $('body').on(M.click_tap, '#js_notice_items', function () {
        config.title = '注意事项';
        config.notice = $('#notice_items').val();
        M.util.popup_notice(config);
    });

//关闭按钮
    $('body').on(M.click_tap, '.close-btn', function () {
        $('.matte').hide();
        $('.gather-popup').hide();
    });

    /*************************************************
     * Tab切换
     * */
    $('#oNav').on(M.click_tap,'li',function(){
        var itemIndex =$(this).index();
        var totalHeight = $('.contain-top').height();
        $(this).find('a').addClass('cur');
        $(this).siblings('li').find('a').removeClass('cur');
        $('.adv').eq(itemIndex).show().siblings('.adv').hide();
        // if(itemIndex == 0){
        //     $('.contain-top').show();
        // }else{
        //     $('.contain-top').hide();
        // }
        $('body').scrollTo({toT:totalHeight,durTime:200});
    });

    /************************************************
     * 游玩地列表点击加载更多
     * */
    function GetQueryString(name) {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)return decodeURI(r[2]); return null;
    }
    var id = GetQueryString('id');
    var counter = 0; /*计数器*/
    var pageStart = 0; /*offset*/
    var pageSize = 2; /*size*/
    /*首次加载*/
    getData(pageStart, pageSize);

    //监听加载更多事件
    $('body').on(M.click_tap, '#js_address_more', function () {
        counter ++;
        pageStart = counter * pageSize;
        getData(pageStart, pageSize);
    });

//从服务器拉取数据的业务逻辑   参照http://www.cnblogs.com/52fhy/p/5405541.html
    function getData(offset,size){
        $.ajax({
            type: 'POST',
            url: base_url_module + 'play/getAllPlaces',
            dataType: 'json',
            data: {
                'id': id,
                'YII_CSRF_TOKEN' :yii_csrf_token
            },
            success: function(reponse){
                var res = reponse['data'];
                var data = res.place_list;
                var sum = res.place_list.length;
                var result = '';

                /****业务逻辑块：实现拼接html内容并append到页面*********/
                /*如果剩下的记录数不够分页，就让分页数取剩下的记录数
                 * 例如分页数是5，只剩2条，则只取2条
                 * 实际MySQL查询时不写这个不会有问题
                 */
                if(sum - offset < size ){
                    size = sum - offset;
                }

                /*使用for循环模拟SQL里的limit(offset,size)*/
                for(var i=offset; i< (offset+size); i++){

                    result +='<a class="list-content list-place db pr" href="../discover/playDetail?id='+data[i].id+'">'+
                        '<i class="icon address"></i>'+
                        '<p class="list-p">' +
                        '<span class="list-span fl">'+data[i].name+
                        '</span><br/>'+
                        '<mark class="list-mark fl">'+ data[i].desc +'</mark>'+
                        '</p>'+
                        '<i class="arrow-right"></i>'+
                        '</a>';
                }
                $('.js-blog-list').append(result);

                /*隐藏more按钮*/
                if ( (offset + size) >= sum){
                    $(".js-load-more").text('已经全部加载完毕');
                }else{
                    $(".js-load-more").show();
                }
            },
            error: function(xhr, type){
                // console.log(xhr);
                // alert('Ajax error!');
            }
        });
    }
    /*************************************************************************/
//点击收藏函数
//点击收藏
    $('.banner-store').on(M.click_tap,function(e){
        e.preventDefault();
        var collect = $('#collect'),
            id = $('#act_id').val();

        if(collect.hasClass('has-store')){
            var dao_del = DAO.collect,
                act_del = 'del', //操作类型，‘del’删除
                param_del = {
                    'link_id':id,
                    'type':'kidsplay',
                    'act':act_del
                };
            dao_del.getAllCollect(param_del, function (res) {
                if(res.status == 1){
                    var data = res['data'];
                    if(data.status == 0){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                    }else{
                        config_tips.msg = data.message;
                        config_tips.is_reload = true;
                        M.util.popup_tips(config_tips);
                    }
                }else if(res.status == 0){
                    console.log(data);
                    var data = res['data'];
                    if(data.error_code == 0){

                        config_tips.msg = data.error_msg;
                        M.util.popup_tips(config_tips);
                    }
                }

            });
        }else{
            var dao_add = DAO.collect,
                act_add = 'add',  //操作类型，‘add’增加
                param_add = {
                    'link_id':id,
                    'type':'kidsplay',
                    'act':act_add
                };
            dao_add.getAllCollect(param_add, function (res) {
                if(res.status == 1){
                    var data = res['data'];
                    if(data.status == 0){
                        config_tips.msg = data.message;
                        M.util.popup_tips(config_tips);
                    }else{
                        config_tips.msg = data.message;
                        config_tips.is_reload = true;
                        M.util.popup_tips(config_tips);
                    }
                }else if(res.status == 0){
                    console.log(data);
                    var data = res['data'];
                    if(data.error_code == 0){
                        config_tips.msg = data.error_msg;
                        M.util.popup_tips(config_tips);
                    }
                }
            });
        }
    });

    /************************************
     * 底部功能区
     * */
//点击分享引导
    $('body').on(M.click_tap, '.go-share', function () {
        $('.matte').show();
        $('.share-pic').show();
        // setTimeout(function () {
        //     $('.matte').hide();
        //     $('.share-pic').hide();
        // }, 1500);
    });
    $('body').on(M.click_tap, '.share-pic', function () {
        $('.matte').hide();
        $('.share-pic').hide();
    });
    //展现
    $('body').on(M.click_tap, '.gallery-link', function () {
        $('body').addClass('ps-active');
        // $('body').addClass('ps-building');
        $('.ps-document-overlay').show();
        $('.ps-carousel').show();
    });
})();

