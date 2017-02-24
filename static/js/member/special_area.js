/**
 * Created by Administrator on 2016/11/14 0014.
 */
(function () {
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

    function renderData() {
        var drop_list_flag = true;
        var dao = DAO.member;
        var param = {
            'page': 2,
            'page_num': 5
        };

        dao.getAreaLists(param, function (res) {
            console.log(res);
            if(res.status == 1){
                var result = [];
                var res = res['data']['choice_list'];
                result['res'] = res;

                console.log(res[0]);
                if (!res[0]) {
                    result['gif_flag'] = 1;
                    drop_list_flag = false;
                }
                template.helper("dateFormat", dateFormat);
                $('.list-item').append(template('list', result));
                if(drop_list_flag){ //有数据就进行分页
                    dropList();
                }
            }

        });
    }
    renderData();

//分页
    function dropList() {
//加载更多
        var counter = 2;

        var dropload = $('.main').dropload({
            scrollArea: window,
            domDown: {
                domClass: 'dropload-down',
                domRefresh: '<div class="dropload-refresh" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">↑上拉加载更多</div>',
                domLoad: '<div class="dropload-load" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;"><span class="loading"></span>加载中...</div>',
                domNoData: '<div class="dropload-noData" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">已经全部加载完毕</div>'
            },
            //上拉加载函数
            loadDownFn: function (me) {
                counter++;
                function renderMore() {
                    var dao = DAO.member
                    var param = {
                        'page': counter,
                        'page_num': 5
                    };
                    dao.getAreaLists(param, function (res) {
                        if(res.status == 1){
                            var result = [];
                            var res = res['data']['choice_list'];
                            result['res'] = res;
                            console.log(result);

                            setTimeout(function () {
                                template.helper("dateFormat", dateFormat);
                                $('.list-item').append(template('list', result));
                                me.resetload();
                            }, 500);
                            if (res.length == 0) {
                                me.lock();
                                me.noData();
                                return;
                            }
                        }
                    });
                }

                renderMore();
            }
        });
    }

//template时间转换函数
    function dateFormat(date, format) {
        date = new Date(date * 1000);
        var map = {
            "M": date.getMonth() + 1, //月份
            "d": date.getDate(), //日
            "h": date.getHours(), //小时
            "m": date.getMinutes(), //分
            "s": date.getSeconds(), //秒
            "q": Math.floor((date.getMonth() + 3) / 3), //季度
            "S": date.getMilliseconds() //毫秒
        };

        format = format.replace(/([yMdhmsqS])+/g, function (all, t) {
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
})();