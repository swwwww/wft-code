(function(){
    var selectedNUm = 0,
        itemIndex,
        nav = $('.item-main'),
        mapData = {},
        tagId = [],
        clickFlag = true,
        submitBtn = $('#submit'),
        dao = DAO.play,
        tagIdString;

    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };

    /***********************************************************************
     * 选中的活动类型不能超过三个
     * 数组tagId 存储选中活动类型的id
    */
    $(document).on(M.click_tap,'.item-main-nav',function(){
        var $this = $(this);
        tagId = [];
        if(selectedNUm <= 3){
            if($this.hasClass('active')){
                $this.removeClass('active');
                itemIndex = $this.index();
                selectedNUm -= 1;
                delete mapData[itemIndex];
                sortedData();
            }else{
                if(selectedNUm < 3){
                    $this.addClass('active');
                    itemIndex = $this.index();
                    selectedNUm += 1;
                    mapData[itemIndex] = nav.find('li').eq(itemIndex).attr('data-id');
                    sortedData();
                }else if(selectedNUm == 3){
                    sortedData();
                    config_tips.msg = '最多勾选3个活动类别哦!';
                    M.util.popup_tips(config_tips);
                    return false
                }
            }

            tagIdString = JSON.stringify(tagId);

        }else{
            return false
        }
    });

    $(document).on(M.click_tap,'.reset-btn',function(){
        var $this = $(this);
        $this.prev().val('');
    });

    /***********************************************************************
     * 正则验证表单信息
     * 提交表单数据param
    */
    submitBtn.on(M.click_tap,function(){
        var $this = $(this),
            phoneReg = /^1\d{10}$/,
            nameValue = $('#name_info').val(),
            phoneValue = $('#phone_info').val(),
            adultNum = $('#adult_num').val(),
            childNum = $('#child_num').val();

        var param = {
            'tag_id':tagIdString,
            'baby_number':childNum,
            'man_number':adultNum,
            'name':nameValue,
            'phone':phoneValue
        };

        if(clickFlag == false){
            config_tips.msg = '您已提交成功，请勿重复提交哦！';
            M.util.popup_tips(config_tips);
            return false
        }

        if(tagId.length == 0){
            config_tips.msg = '请选择定制活动类型哦！';
            M.util.popup_tips(config_tips);
            return false
        }

        if((adultNum + childNum) == 0){
            config_tips.msg = '请请输入成人或儿童的数量！';
            M.util.popup_tips(config_tips);
            return false
        }

        if(!nameValue){
            config_tips.msg = '请输入您的姓名！';
            M.util.popup_tips(config_tips);
            return false
        }

        if(!phoneValue){
            config_tips.msg = '请输入您的手机号！';
            M.util.popup_tips(config_tips);
            return false
        }

        if(!phoneReg.test(phoneValue)){
            M.load.loadTip('errorType', '请输入正确的手机号哦！', 'delay');
            return false
        }

        $this.css({'background':'#b8b8b8'}).text('请稍等...');
        clickFlag = false;
        dao.makeParty(param, function (res) {
            if(res.status == 1){
                var data = res['data'];
                if(data.status == 0){
                    M.load.loadTip('errorType', data.message, 'delay');
                    $this.css({'background':'#fa6e51'}).text('提交');
                    clickFlag = true;
                }

                if(data.status == 1){
                    M.load.loadTip('doType', data.message,'delay');
                    $this.css({'background':'#fa6e51'}).text('提交成功');
                }
            }else{
                M.load.loadTip('errorType', res.msg, 'delay')
            }
        });
    });

    function sortedData(){
        for(i in mapData){
            tagId.push(parseInt(mapData[i],10));
        }
        return tagId;
    }
})();