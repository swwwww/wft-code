(function(){
    var nav = $('.nav'),
        adv = $('.adv'),
        dao = DAO.searchDetail,
        type = '',
        word = $('#get_word').val(),
        info = $('.info'),
        search = $('#search'),
        reset = $('.reset'),
        itemIndex = 0,
        page_num,
        param = {
            'page':1,
            'page_num':10,
            'type':type,
            'word':word
        };

    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    nav.on(M.click_tap,'.nav-item',function(){
        var $this = $(this);
        itemIndex = $this.index();

        if($this.find('i').not('active')){
            $this.find('i').addClass('active');
            $this.siblings('.nav-item').find('i').removeClass('active');
            adv.eq(itemIndex).show().siblings('.adv').hide();
        }
        param['type'] = getType(itemIndex);
        initialData(param,param['type'],upLoadData);
    });

    info.on('input',function(){
        var $this = $(this);
        if($this.val()){
            search.addClass('active');
            search.text('搜索');
            reset.show();

        }else{
            search.removeClass('active');
            search.text('取消');
            reset.hide();
        }
    });

    reset.on(M.click_touchend,function(event){
        event.stopPropagation();
        event.preventDefault();
        info.val('');
        info.trigger('input');
    });

    search.on(M.click_touchend,function(event){
        event.stopPropagation();
        event.preventDefault();
        var $this = $(this);
        var type = $this.text();
        var val = info.val();
        callBack(type,val)
    });

    info.trigger('input');

    function getType(index){
        if(index == 0){
            type = 'play'
        }else if(index == 1){
            type = 'goods'
        }else if(index == 2){
            type = 'place'
        }
        return type
    }

    function getIndex(type){
        if(type == 'play'){
            itemIndex = 0
        }else if(type == 'goods'){
            itemIndex = 1
        }else if(type == 'place'){
            itemIndex = 2
        }
        return itemIndex
    }

    function callBack(type,val){
        if(type == '搜索'){
            window.location.href = '/recommend/searchdetail?word='+val;
        }else if(type == '取消'){
            window.location.href = '/recommend/index';
        }
    }

    function judgeType(type,data){
        if(!type){
            if(data.goods_list.length > 0){
                type = 'goods'
            }else if(data.place_list.length > 0){
                type = 'place'
            }else if(data.play_list.length > 0){
                type = 'play'
            }else if(data.goods_list.length == 0 && data.place_list.length == 0 && data.play_list.length == 0){
                type = 'play'
            }
        }
        return type
    }

    function initialData(param,type,func){
        param['page'] = 1;
        $('.dropload-down').remove();
        if(type){
            $('#adv_'+type).find('.adv-list').empty();
        }

        if(!word){
            config_tips.msg = '搜索内容不能为空！';
            M.util.popup_tips(config_tips);
            return false
        }

        dao.getAllSearch(param,function(res){
            if(res.status == 1){
                var data = res.data;
                var result = [];
                if(!type){
                    type = judgeType(type,data);
                    itemIndex = getIndex(type);
                    $('.nav-item').eq(itemIndex).find('i').addClass('active');
                    adv.eq(itemIndex).show().siblings('.adv').hide();
                }

                result[type+'_list'] = data[type+'_list'];
                page_num = data[type+'_list'].length;

                if(page_num == 0){
                    $('#adv_'+type).find('.adv-list').append(
                        '<div class="no-data">'
                        +'<img src="/static/img/site/mobile/nodata.gif" />'
                        +'</div>')
                }else if(page_num > 0 && page_num < 10){
                    $('#adv_'+type).find('.adv-list').append(template(type+'_list', result));
                }else if(page_num >= 10 ){
                    $('#adv_'+type).find('.adv-list').append(template(type+'_list', result));
                    func(param,type)
                }
            }else{
                config_tips.msg = res.msg;
                M.util.popup_tips(config_tips);
            }
        });
    }

    function upLoadData(param,type){
        var counter = 1;
        var dropload = $('.main').dropload({
            scrollArea : window,
            domDown : {
                domClass: 'dropload-down',
                domRefresh: '<div class="dropload-refresh" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">↑上拉加载更多</div>',
                domLoad: '<div class="dropload-load" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;"><span class="loading"></span>加载中...</div>',
                domNoData: '<div class="dropload-noData" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">已经全部加载完毕</div>'
            },

            loadDownFn : function(me){
                counter++;
                param['page'] = counter;
                dao.getAllSearch(param,function(res){
                    if(res.status == 1){
                        var data = res.data;
                        var result = [];
                        result[type+'_list'] = data[type+'_list'];
                        setTimeout(function(){
                            $('#adv_'+type).find('.adv-list').append(template(type+'_list', result));
                            me.resetload();
                        },500);
                        if(data[type+'_list'].length == 0){
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
        });
    }

    initialData(param,type,upLoadData);
})();