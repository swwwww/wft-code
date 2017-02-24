(function() {
    var bagscore = $('.takebag').find('p').find('i'),
        baglistEle = $('#baglist'),
        startbtnEle = $('.startbtn'),
        popupEle = $('.popup'),
        infoDiaEle = $('#infoDia'),
        tipDiaEle = $('.tipDia'),
        closeDiaEle = $('.closeDia'),
        submitbtnEle = $('#submitbtn'),
        shareEle = $('#share'),
        endDiaEle = $('.endDia'),
        againbtnEle = $('#againbtn'),
        shareDiaEle = $('.shareDia'),
        nonetworkEle = $('.nonetwork'),
        playagainEle = $('#playagain');

    window.addEventListener('unload', function () {});
    window.addEventListener('pageshow', function () {});

    baglistEle.on('tap', 'div', function() {
        var self = $(this),
            level = self.attr('level'),
            score = self.attr('score'),
            isopen = self.attr('isopen'),
            res;

        if(Number(baglistEle.attr('isend'))) {
            popupEle.show();
            endDiaEle.show();
        }else {
            if(!Number(isopen)) {
                self.attr('isopen', '1');
                res = Number(bagscore.text() - score);
                if(res > 0) {
                    bagscore.text(res);
                    $.post(baglistEle.data('url'), {"level": level}, function(data) {
                        var text;

                        self.addClass('shake');
                        if(Number(data.code) == 1) {/*中奖*/
                            var random = Math.random();
                            if(random < 0.4) {
                                text = '“猴”运到！恭喜获得由' + data.sponsor + '提供的' + data.gift + '一份！';
                            }else if(random > 0.3 && random < 0.7) {
                                text = '天降财神！开年大吉！恭喜获得开门红礼包！';
                            }else {
                                text = '泛海国际桂海园祝您猴年合家欢乐！';
                            }
                        }else if(Number(data.code) == 2) {/*异常信息*/
                            setTimeout(function() {
                                popupEle.show();
                                tipDiaEle.show().find('p').text(data.msg).end().find('a').hide();
                            }, 900);
                        }else {/*没有中奖*/
                            if(Math.random() > 0.5) {
                                text = '哎呀，礼品还堵在路上，别灰心，再抽一次！';
                            }else {
                                text = '最好的奖品是最真心的祝福！泛海国际桂海园祝您猴年合家欢乐！';
                            }
                        }

                        setTimeout(function() {
                            self.removeClass('shake').find('a').show().find('h3').text(text);
                            if(data.isfirst) {
                                popupEle.show();
                                infoDiaEle.show();
                            }
                        }, 900);
                    });
                }else {/*积分不够*/
                    popupEle.show();
                    tipDiaEle.show().find('a').show();
                }
            }
        }

    });

    closeDiaEle.on('touchstart', function() {
        popupEle.hide();
        $(this).parent().hide();
    });

    submitbtnEle.on('tap', function() {
        var nameEle = $('#name'),
            phone = $('#tel'),
            errorTip = $('.errortip');
        if(!(nameEle.val() && /^[\u4e00-\u9fa5]+$/.test(nameEle.val()))){
            errorTip.text('请填写您的姓名(汉字)');
            return;
        }

        if(!(phone.val() && /^1\d{10}$/.test(phone.val()))) {
            errorTip.text('请输入正确的手机号');
            return;
        }

        $.post(submitbtnEle.attr('url'), {"num": nameEle.val(), "phone": phone.val()},function(data) {
            if(data.message) {
                errorTip.text(data.message);
            }else {
                popupEle.hide();
                infoDiaEle.hide();
            }
        });
    });

    shareEle.on('tap', function() {
        if(Number($(this).attr('isweixin'))) {
            popupEle.show();
            shareDiaEle.show();
        }
    });

    popupEle.on('tap', function() {
        if(shareDiaEle.css('display') == 'block') {
            popupEle.hide();
            shareDiaEle.hide();
        }else if(endDiaEle.css('display') == 'block') {
            popupEle.hide();
            endDiaEle.hide();
        }
    });

    startbtnEle.on('tap', function() {
        if(Number($(this).attr('isend'))) {
            popupEle.show();
            endDiaEle.show();
        }else {
            chanceover($(this).attr('chance'), $(this).attr('url'));
        }
    });

    againbtnEle.on('tap', function() {
        chanceover($(this).attr('chance'), $(this).attr('url'));
    });

    playagainEle.on('tap', function() {
        chanceover($(this).attr('chance'), $(this).attr('url'), 1);
    });

    function chanceover(chance, url, isplay) {
        if(Number(chance) > 0) {
            window.location.href = url;
        }else {
            if(isplay) {/*play页面*/
                nonetworkEle.hide();
            }else {
                popupEle.show();
            }
            tipDiaEle.show();
        }
    }
})();