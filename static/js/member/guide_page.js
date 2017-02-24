/**
 * Created by deyi on 2016/11/18.
 */
(function(){
    var scr = $('.swipe-wrap'),
        swipe = $('.swipe'),
        contain = $('.container'),
        imgWidth = scr.find('img').width(),
        len = scr.find('img').length,
        iNow = 0,
        start =0,
        end = 0,
        timer = null,
        temp,
        total = len * (imgWidth + 17 );
    scr.width(total);//整个容器的宽度

    if(total <= 733){
        return false
    }

    timer = setInterval(function(){
        change();
    },3000);

    swipe.mouseenter(function(){
        clearInterval(timer);
    });

    swipe.mouseleave(function(){
        clearInterval(timer);
        timer = setInterval(function(){
            change();
        },3000)
    }).trigger('mouseleave');


    function change(){
        iNow++;

        if(iNow + 2 == len){
            iNow = 0;
        }
        if(iNow == -1){
            iNow = len -1;
        }
        if(iNow == 0){
            //scr.animate({
            //    left:-((imgWidth + 17 )* iNow) + 'px'
            //},300)

            scr.css({
                "transform":"translateX("+(-((imgWidth + 17 )* iNow))+"px)",
                "-webkit-transform":"translateX("+(-((imgWidth + 17 )* iNow))+"px)",
                "-moz-transform":"translateX("+(-((imgWidth + 17 )* iNow))+"px)",
                "-o-transform":"translateX("+(-((imgWidth + 17 )* iNow))+"px)",
                "-ms-transform":"translateX("+(-((imgWidth + 17 )* iNow))+"px)"
            })
        }else{
            //scr.animate({
            //    left:-((imgWidth + 17 )* iNow + 80) + 'px'
            //},300)

            scr.css({
                "transform":"translateX("+(-((imgWidth + 17 )* iNow + 80))+"px)",
                "-webkit-transform":"translateX("+(-((imgWidth + 17 )* iNow + 80))+"px)",
                "-moz-transform":"translateX("+(-((imgWidth + 17 )* iNow + 80))+"px)",
                "-o-transform":"translateX("+(-((imgWidth + 17 )* iNow + 80))+"px)",
                "-ms-transform":"translateX("+(-((imgWidth + 17 )* iNow + 80))+"px)"
            })
        }
    }

    contain.bind('touchstart',function(e){
        e.stopPropagation();
        e.preventDefault();
        clearInterval(timer);
        start = e.changedTouches[0].pageX;
    });

    //手势滑动
    contain.bind('touchend',function(e){
        e.stopPropagation();
        e.preventDefault();
        clearInterval(timer);
        end = e.changedTouches[0].pageX;
        temp = end - start;

        console.log(temp);
        if(temp > 0){
            if(iNow == 0){
                return false
            }else{
                iNow = iNow -2;
                change();
                timer = setInterval(function(){
                    change();
                },3000)
            }
        }else if (temp < 0){
            change();
            timer = setInterval(function(){
                change();
            },3000)
        }
    });

    //弹框显示
    $('.link-rule').on(M.click_tap,function(){
        $('.normal-popup').show();
        $('.s-matte').show();
    });

    $('.cross-btn').on(M.click_touchend,function(){
        $('.normal-popup').hide();
        $('.free-popup').hide();
        $('.s-matte').hide();
    });

    $('.free-btn').on(M.click_tap,function(){
        $('.free-popup').show();
        $('.s-matte').show();
    })
})();