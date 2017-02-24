/**
 * Created by deyi on 2016/8/20.
 */
(function(){
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    window.onload = function(){
        var city = M.location.getCustomCity();
        city = M.location.getCityForCn(city);
        $('.area-info').text(city);
    };

    var container = $('.scroll-container');
    var totalHeight = container.height();//滚动总高度
    var height = $('.scroll-message').height();//每次滚动的高度
    var num = 0;//滚动次数

    //文字滚动
    function fontScroll(){
        var temp = num*height;//滚动变量
        if(totalHeight > temp){
            container.css('margin-top',-temp)
        }else{
            var diff = temp % totalHeight;
            container.css('margin-top',-diff)
        }
        num++;
        setTimeout(fontScroll,3000);
    }
    fontScroll();



    var item_num = $('.inner').find('.index-item').length;
    if(item_num >= 5){
        //加载更多
        var counter = 1;
        var dropload = $('.main-s').dropload({
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
                    var dao = DAO.choice;
                    var param = {
                        'page': counter,
                        'page_num': 5
                    };
                    dao.getAllIndex(param, function (res) {
                        if(res.status == 1){
                            var data = res['data'];
                            var result = [];
                            result['res'] = data['choice_list'];
                            setTimeout(function(){
                                $('.lists').append(template('more', result));
                                me.resetload();
                            },500);
                            if(data['choice_list'].length == 0){
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
    }

    //轮播函数
    function imgScroll(){
        var scr = $('.swipe-wrap'),
            swipe = $('.swipe'),
            contain = $('.container'),
            imgWidth = scr.find('img').width(),
            len = scr.find('img').length,
            index = $('.dots').find('li'),
            iNow = 0,
            start =0,
            end = 0,
            timer = null;
        scr.width(len * imgWidth);//整个容器的宽度

        //点击按钮控制轮播滚动
        index.on(M.click_tap,function(e){
            e.preventDefault();
            iNow = $(this).index();
            $(this).addClass('active').siblings().removeClass('active');
            scr.animate({
                left:-imgWidth * iNow + 'px'
            },400)
        });

        timer = setInterval(function(){
            change();
        },3000);

        //鼠标划入
        swipe.mouseenter(function(){
            clearInterval(timer);
        });

        //鼠标滑出
        swipe.mouseleave(function(){
            clearInterval(timer);
            timer = setInterval(function(){
                change();
            },3000)
        }).trigger('mouseleave');

        //自动滚动函数
        function change(){
            iNow++;
            if(iNow == len){
                iNow = 0;
            }
            if(iNow == -1){
                iNow = len -1;
            }
            index.eq(iNow).addClass('active').siblings().removeClass('active');
            scr.animate({
                left:-imgWidth * iNow + 'px'
            },400)
        }


        //按手势滑动
        contain.bind('touchstart',function(e){
            e.stopPropagation();
            clearInterval(timer);
            start = e.changedTouches[0].pageX;
        });

        contain.bind('touchend',function(e){
            e.stopPropagation();
            clearInterval(timer);
            end = e.changedTouches[0].pageX;
            if(end - start > 0){
                iNow = iNow -2;
                change();
                timer = setInterval(function(){
                    change();
                },3000)
            }else if (end - start < 0){
                change();
                timer = setInterval(function(){
                    change();
                },3000)
            }
        });
    }
    imgScroll();

    /***********************************************************************
     * 判断是否有提醒弹框
     * 判断是否本地存储是否有相同提醒Id
     * 显示提醒弹框
     */

    document.onreadystatechange = subSomething;
    function subSomething(){
        if(document.readyState == 'complete'){
            var noticeValue = $('#notice_value'),
                noticeId = noticeValue.attr('data-id'),
                isShow = noticeValue.attr('data-show'),
                notice = $('#notice'),
                crossBtn = $('.link-cross');

            if(isShow == 1){
                if (window.localStorage) {
                    if(localStorage.getItem('noticeId') == undefined){
                        localStorage.setItem('noticeId',noticeId);
                        notice.show();
                    }else{
                        if(localStorage.getItem('noticeId') != noticeId){
                            localStorage.setItem('noticeId',noticeId);
                            notice.show();
                        }
                    }
                }
            }

            crossBtn.on(M.click_touchend,function(event){
                event.stopPropagation();
                event.preventDefault();
                $('.notice-link-wrap').addClass('active');
                $('.notice-matte').css({'opacity':0});
                setTimeout(function(){
                    notice.hide();
                },800)
            });
        }
    }


    $('.head-info').on(M.click_touchend,function(e){
        e.preventDefault();
        e.stopPropagation();
        var searchBox = $('#search_box');
        $('.main-s').hide();
        $('.search-ele').show();
        $('.foot-nav').hide();
        $('.foot-btn').hide();
        window.location.hash ='searchBox';
    });

    function checkLocation(){
        if(location.hash.indexOf("#searchBox")>-1){
            $('.search-ele').show();
            $('.main-s').hide();
            $('.foot-nav').hide();
            $('.foot-btn').hide();
        }else{
            $('.main-s').show();
            $('.foot-nav').show();
            $('.foot-btn').show();
            $('.search-ele').hide();
        }
    }

    window.onpopstate = function() {
        checkLocation();
    };

})();

