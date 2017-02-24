/**
 * Created by deyi on 2017/1/17.
 */

(function ($) {
    $.fn.touchMove = function(){
        var $this = $(this),
            width = $this.width(),
            len = $this.find('li').length,
            startTime,
            endTime,
            startX ,
            startY,
            XX,
            YY,
            startLeft,
            moveX,
            swipeX,
            swipeY,
            bj,
            signX,
            translateX,
            left,
            index = 0;

        $this.css('width', width * (len + 1));

        $this[0].style.transform = 'translate3d('+ -width +'px,0px,0px)';
        $this[0].style.webkitTransform = 'translate3d('+ -width +'px,0px,0px)';

        $this.live('touchstart' ,function(event){
            $this.removeClass('swipe-moving');
            var date = new Date();
            startTime = date.getTime();
            startLeft = parseFloat($this.css('-webkit-transform').match(/\-?[0-9]+/g)[1]);
            startX = event.changedTouches[0].pageX;
            startY = event.changedTouches[0].pageY;
            swipeX = true;
            swipeY = true;
        });

        $this.live('touchmove' ,function(event){
            translateX = parseFloat($this.css('-webkit-transform').match(/\-?[0-9]+/g)[1]);
            console.log(event);
            XX = event.changedTouches[0].pageX;
            YY = event.changedTouches[0].pageY;

            if(swipeX && Math.abs(XX - startX) - Math.abs(YY - startY) > 0 ){
                event.preventDefault();
                swipeY = false;
                moveX = parseFloat($this.css('-webkit-transform').match(/\-?[0-9]+/g)[1]) + event.targetTouches[0].pageX - startX;
                console.log(moveX);
                startX = event.changedTouches[0].pageX;
                $this.css({
                    'transform' :'translate3d('+ moveX +'px, 0px,0px)',
                    '-webkit-transform' :'translate3d('+ moveX +'px, 0px,0px)'
                });
            }else if(swipeY && Math.abs(XX - startX) - Math.abs(YY - startY) < 0){
                swipeX = false;
            }
        });

        $this.live('touchend', function(){
            var date = new Date();
            endTime = date.getTime();
            translateX = parseFloat($this.css('-webkit-transform').match(/\-?[0-9]+/g)[1]);
            bj = width + translateX % width;

            if(endTime - startTime >= 300){
                signX = width / 2;

                if(bj <= signX){
                    left = translateX - bj
                } else{
                    left = translateX + (width -bj)
                }
            } else{
                bj = Math.abs(translateX % width);
                signX = width / 4;
                if(translateX >= startLeft){
                    if(bj > signX){
                        left = translateX + bj
                    } else{
                        left = translateX - (width - bj)
                    }
                } else{
                    if(bj >= signX * 3){
                        left = translateX + bj
                    } else{
                        left = translateX - (width -bj)
                    }
                }
            }

            if(startLeft < left){
                $this.animate({
                    'transform':'translate3d(0px,0px,0px)',
                    '-webkit-transform' :'translate3d(0px,0px,0px)'
                },300,'ease-out',function(){
                    index--;
                    if(index === -1){
                        index = len - 1;
                    }

                    $this.parent().next().find('li').eq(index).addClass('active').siblings('li').removeClass('active');
                    $this.css({
                        'transform' : 'translate3d('+ width / (-1)+'px,0px,0px)',
                        '-webkit-transform' : 'transform('+ width / (-1)+'px,0px,0px)'
                    });
                    $this.children('li').last().remove().clone(true).insertBefore($this.children('li').first());

                });
            } else if(startLeft > left){
                $this.animate({
                    'transform' : 'translate3d('+ width * (-2) +'px,0px,0px)',
                    '-webkit-transform' : 'translate3d('+ width * (-2) +'px,0px,0px)'

                },300,'ease-out',function(){
                    index++;
                    if(index === len){
                        index = 0;
                    }

                    $this.parent().next().find('li').eq(index).addClass('active').siblings('li').removeClass('active');
                    $this.css({
                        'transform' : 'translate3d('+ width / (-1) +'px,0px,0px)',
                        '-webkit-transform' : 'translate3d('+ width / (-1) +'px,0px,0px)'
                    });
                    $this.children('li').first().remove().clone(true).appendTo($this);
                });
            } else {
                $this.addClass('swipe-moving');
                $this.css('transform' , 'translate3d('+left+'px,0px,0px)');
            }
        });
    };
})($);

(function ($) {
    $.autoPlay = function(selector) {
        var width = selector.find('li').width(),
            len = selector.find('li').length,
            index = 0;

        function showImg() {
            selector.animate({
                'transform' : 'translate3d('+ width * (-2) +'px,0px,0px)',
                '-webkit-transform' : 'translate3d('+ width * (-2) +'px,0px,0px)'

            },300,'ease-out',function(){
                index++;
                if(index === len){
                    index = 0;
                }

                selector.parent().next().find('li').eq(index).addClass('active').siblings('li').removeClass('active');
                selector.css({
                    'transform' : 'translate3d('+ width / (-1) +'px,0px,0px)',
                    '-webkit-transform' : 'translate3d('+ width / (-1) +'px,0px,0px)'
                });
                selector.children('li').first().remove().clone(true).appendTo(selector);
            });
        }

        timer = setInterval(function(){
            showImg()
        },4000);
    }
})($);

$('.swipe-wrap').touchMove();
//$.autoPlay($('.swipe-wrap'));
//$('.swipe-wrap').on('touchstart' , function() {
//    clearInterval(timer);
//}).on('touchend' , function() {
//    $.autoPlay($('.swipe-wrap'));
//});
