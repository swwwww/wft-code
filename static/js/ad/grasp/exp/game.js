/*
* 缓动方式：https://ihatetomatoes.net/greensock-cheat-sheet/#more-6001
* */

(function() {

    var isIOS = navigator.userAgent.match(/iPad/i) || navigator.userAgent.match(/iphone os/i) || navigator.userAgent.match(/ipod/i),
        timeEle = $('.time').find('i'),  //时间显示栏
        scoreEle = $('.score').find('i'), //分数显示
        score = 0, //分数
        gamecontentEle = $('.game-content'), // 绳子滚动的游戏元素
        ropeEle = $('.rope'), //绳子
        bagEle = $('#bag'),  //福袋

        popupEle = $('.popup'), //弹出窗
        gamepopEle = $('.gamepop'), // 遮罩层
        gametipEle = $('.gametip'),  //游戏指引
        nonetworkEle = $('.nonetwork'), //没有网络的弹框
        playagainEle = $('#playagain'), //再玩一次

        tipDiaEle = $('.tipDia'),  //提示
        arrClass = ['xd', 'xy', 'yb', 'ybkl', 'ybfh'],//xd, xy都减10，yb加40
        width,
        tl,
        tlbag,
        progress,
        catchObj,
        speed = 4, //速度
        num = 0,
        isfinish = 1,
        bagComplete = 1;

    if(gamecontentEle.length == 0) {
        return;
    }

	//添加默认阻止事件
    document.addEventListener('touchmove', function(e) {
        e.preventDefault();
    });

    /* 相关说明：http://greensock.com/docs/#/HTML5/GSAP/TimelineMax/
    *  TimelineMax.add()：在时间轴上添加相关的标签（渐变、时间表、回调或标签）
    *  TweenMax.set():为目标元素设置相应的属性；
    *  TweenMax.fromTo（）：基于当前值为开始及结束的位置创建一个值
    *  TweenMax.to（）： 基于当前值为目标位置的特性创建一个过渡过程
    * */
    function addAnima(lastobj) {
        tl = new TimelineMax({
            repeat: 0   //循环次数，-1重复无限次，
        });
        tl.addLabel('label' + num); //添加时间线，容易标记重要的位置或时间
        //一系列的从绳子划过的动作
        //一系列的从绳子划过的动作
        tl.add(TweenMax.set(lastobj, {
            x: width,
            transformOrigin: "50% 5px"
        })).add(TweenMax.fromTo(lastobj, .5, {
            rotation: 6
        }, {
            rotation: 0,
            ease: Power1.easeIn
        })).add(TweenMax.to(lastobj, .5, {
            rotation: -6,
            ease: Power1.easeOut
        })).add(TweenMax.to(lastobj, .5, {
            rotation: 0,
            ease: Power1.easeIn
        })).add(TweenMax.to(lastobj, .5, {
            rotation: 6,
            ease: Power1.easeOut
        }));
        /*
        * onStart:func 开始函数, 触发条件，在TweenMax Object的值改变后才触发。
        * onStartParams：开始函数参数
        * ease：缓动方式
        * onUpdate： 更新函数
        * onComplete: 完成函数，当TweenMax Object完成缓动后触发。
        * onCompleteParams： 完成函数参数
        * */
        TweenMax.to(lastobj, speed, {
            x: -100,
            onStart: starthandle,
            onStartParams: ['{self}'],
            ease: Power0.easeNone,
            onUpdate: updateHandle,
            onUpdateParams: ['{self}', lastobj],
            onComplete: finishHandle,
            onCompleteParams: ['{self}', lastobj]
        });

        lastobj.islast = true;
    }

    //开始函数, 触发条件，在TweenMax Object的值改变后才触发。
    function starthandle(target) {
        catchObj = target;
    }

    //更新函数
    function updateHandle(target, obj) {
        progress = target.progress();

        if(((width * progress) > width / 4) || ((width * progress) == width / 4)) {
            if(!obj.islast) {
                return;
            }
            if(Number(timeEle.text()) < 20 && Number(timeEle.text()) > 10) {
                if(speed > 3) {
                    speed -= 0.1;
                }
            }else if(Number(timeEle.text()) < 10) {
                if(speed > 2.5) {
                    speed -= 0.2;
                }
            }
            createObj();
            obj.islast = false;
        }
    }
    //完成函数，当TweenMax Object完成缓动后触发。
    function finishHandle(target, obj) {
        target.kill(); //休眠目标
        obj.remove();
    }


    bagEle.on('tap', function() { //钩绳的状态
        if(bagComplete) {
            tlbag = new TimelineMax();
            tlbag.add(TweenMax.to(ropeEle, 0.2, {
                y: '-40%',
                onStart: bagstart,
                onComplete: catchHandle,
                onCompleteParams: ['{self}', ropeEle]
            }), "ropeout").add(TweenMax.to(ropeEle, 0.2, {
                y: '0%',
                onComplete: bagend
            }));
        }
    });
    //onStart:func 开始函数, 触发条件，在TweenMax Object的值改变后才触发。
    function bagstart() {
        bagComplete = 0;
    }

    function bagend() {
        bagComplete = 1;
    }

    //抓娃娃
    function catchHandle() {
        if(progress.toFixed(2) > 0.18 && progress.toFixed(2) < 0.22) {
            var getobj = $(catchObj._firstPT.t._target).prev().find('span');
            TweenMax.to(getobj, 0.2, {
                x: 0,
                y: '120%',
                scaleX: 0.5,
                scaleY: 0.5,
                onComplete: catchRemove,  //抓到娃娃后的动作，娃娃消失
                onCompleteParams: [getobj]
            });

            switch (getobj.attr('class')) {
                case arrClass[0]:
                    score = -10;
                    break;
                case arrClass[1]:
                    score = -10;
                    break;
                case arrClass[2]:
                    score = 40;
                    break;
                case arrClass[3]:
                    score = 40;
                    break;
                case arrClass[4]:
                    score = 40;
                    break;
            }
            scoreEle.text(Number(scoreEle.text()) + score);
        }
    }

    //抓到物体后自动消失
    function catchRemove(obj) {
        obj.remove();
    }
    //产生相关的抓取元素
    function createObj() {
        //var str = arrClass[Math.floor(Math.random() * arrClass.length)];
        var str = getrandom(arrClass),
            ele = $('<div class="' + str + 'box"><i></i><span class="' + str + '"></span></div>');

        if(isfinish) { //娃娃在绳子上滑动
            gamecontentEle.append(ele);
            num++;
            addAnima(ele);
        }
    }

    function getrandom(arr) { //概率计算
        var random = Math.random();
        if(random < 0.5) {/*泛海*/
            return arr[4];
        }else if(random < 0.7 && random > 0.4) {/*得意*/
            return arr[3];
        }else if(random < 0.8 && random > 0.6) {/*考拉*/
            return arr[2];
        }else if(random < 0.9 && random > 0.7) {
            return arr[1];
        }else {
            return arr[0];
        }
    }

    //游戏初始化
    function init() {
        if(isIOS) {
            start();
        }else {
            setTimeout(function() {
                start();
            }, 120);
        }
    }

    function start() {
        width = window.innerWidth;
        //游戏计时
        var interval = setInterval(function() {
            if(Number(timeEle.text()) > 0) {
                // timeEle.text(Number(timeEle.text()) - 1);
            }else {
                if(isfinish) {
                    isfinish = 0;
                    //游戏分数
                    if(Number(scoreEle.text()) < 0 || Number(scoreEle.text()) == 0) {
                        scoreEle.text(0);
                    }
                    $.post(bagEle.data('url'), {'md':hex_md5(scoreEle.text() + bagEle.data('token')), 'score':scoreEle.text()}, function() {
                        window.location.href = bagEle.data('href');
                        console.log(bagEle.data('href'));
                    });
                }else {
                    clearInterval(interval);
                    setTimeout(function() {
                        popupEle.show();
                        nonetworkEle.show();
                    }, 10000);
                }
            }
        }, 1000);
        createObj();
    }

    /*游戏入口*/
    if(gamepopEle.length == 0) {
        if(Number(playagainEle.attr('chance')) > 0){
            init();
        }else {
            //popupEle.show();
            //tipDiaEle.show();
        }
    }

    gamepopEle.on('tap', function() {
        if(gametipEle.css('display') == 'block') {
            alert('111');
            init();
            // 原始注释
            /*if(Number(playagainEle.attr('chance')) > 0){
                init();
            }else {
                popupEle.show();
                tipDiaEle.show();
            }*/
            gamepopEle.hide();
            gametipEle.hide();
        }
    });
})();