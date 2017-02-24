/**
 * Created by deyi on 2017/2/21.
 */

(function(window,document){
    var index = {
        init:function(){
            document.addEventListener('DOMContentLoaded',function(){
                index.clickBtn();
                index.tabSwitch();
                index.setPage();
            }.bind(index),false);
        }(),

        clickBtn:function(){
            var checkBtn = document.getElementById('code_btn');
            checkBtn.addEventListener('click',this.checkCode,false);
        },

        checkCode:function(){
            var codeVal = document.getElementById('code').value;
            if(codeVal){
                index.verifyCode(codeVal);
            }else{
                alert('您输入的验证码不能为空');
            }
        },

        popupHtmlStr:function(data){
            var str = '';
            if(data.code_status == 0){
                str = '<div class="popup-verify" ><div class="verify-head"><img class="verify-head-pic" src="'
                + data.coupon_img +'"/><div class="verify-head-info"> <p class="info-title">'
                + data.coupon_title + '</p><p class="info-price">售价：￥'
                + data.coupon_price + '</p></div></div> <div class="verify-main"><span class="verify-main-way"> 参与方式：'
                + data.join_way + '</span><span class="verify-main-oder">订单号：'
                + data.order_sn + '</span><span class="verify-main-code">验证码：<mark>'
                + data.code + '</mark></span><span class="verify-main-phone">购买手机号码：'
                + data.phone + '</span><p class="verify-main-time">下单时间：'
                + data.order_time + '</p><p class="verify-main-addr">使用地点：'
                + data.use_address + '</p></div><div class="verify-btn"> <a class="verify-btn-cancel" href="javascript:;">取消</a> <a class="verify-btn-confirm" href="javascript:;">确认</a></div></div>'
            }else{
                str = '<div class="popup-verify popup-preview" ><div class="verify-head"><img class="verify-head-pic" src="'
                + data.coupon_img +'"/><div class="verify-head-info"> <p class="info-title">'
                + data.coupon_title + '</p><p class="info-price">售价：￥'
                + data.coupon_price + '</p></div></div> <div class="verify-main"><span class="verify-main-way"> 参与方式：'
                + data.join_way + '</span><span class="verify-main-oder">订单号：'
                + data.order_sn + '</span><span class="verify-main-code">验证码：<mark>'
                + data.code + '</mark></span><span class="verify-main-phone">购买手机号码：'
                + data.phone + '</span><p class="verify-main-time">下单时间：'
                + data.order_time + '</p><p class="verify-main-addr">使用地点：'
                + data.use_address + '</p></div><div class="verify-btn"><p class="verify-btn-intro">此商品已验证,'
                + data.use_datetime+ '</p><a class="verify-btn-cancel" href="javascript:;">取消</a></div></div>'
            }
            return str;
        },

        verifyCode:function(code){
            var dao = ADMIN_DAO.business;
            var param = {
                'code': code
            };
            dao.checkCode(param, function (res) {
                var data = res.data;
                if(data.status == 0){
                    alert(res.msg)
                }else{
                    var popup = $('.popup');
                    var matte = $('.popup-matte');
                    res.data.order_time = M.time.year_to_date(res.data.order_time);
                    res.data.use_datetime = M.time.year_to_date(res.data.use_datetime);
                    popup.show().empty().append(index.popupHtmlStr(res.data));
                    matte.show();
                    var cancel = document.getElementsByClassName('verify-btn-cancel')[0];
                    cancel.addEventListener('click',function(){
                        popup.hide();
                        matte.hide();
                    },false);

                    var confirm = document.getElementsByClassName('verify-btn-confirm')[0];
                    confirm.addEventListener('click',function(){
                        index.confirmCode(code);
                    },false)
                }
            });
        },

        confirmCode:function(code){
            var dao = ADMIN_DAO.business;
            var param = {
                'code': code
            };
            dao.confirmCode(param, function (res) {
                var data = res.data;
                if(data.status == 0){
                    alert(res.msg)
                }else{
                    var popup = $('.popup');
                    var matte = $('.popup-matte');
                    popup.hide();
                    matte.hide();
                    alert(res.data.message);
                }
            });
        },

        tabSwitch:function(){
            var tabStatus = parseInt($('#on_sale').val()) - 1;
            var nav = $('.index-nav');
            nav.find('.nav-state').eq(tabStatus).addClass('active').siblings('.nav-state').removeClass('active');
        },

        loopStr:function(start,end,state,current){
            var str = '';
            for(var i = start; i <= end; i++){
                if(i == current){
                    str += '<li><a class="active" href="/admin/business/index?on_sale='
                    + state + '&page='
                    + i + '">'
                    + i +'</a></li>';
                }else{
                    str += '<li><a class="" href="/admin/business/index?on_sale='
                    + state + '&page='
                    + i + '">'
                    + i +'</a></li>';
                }
            }
            return str;
        },

        pageHtmlStr:function(object,max){
            if(object.current == 1){
                var headStr = '<li><span>'
                    + object.count_num + '条记录'
                    + object.current +'/<mark>'
                    + object.page_count +'</mark>页</span></li>';
            }else {
                var headStr = '<li><span>'
                    + object.count_num + '条记录'
                    + object.current +'/<mark>'
                    + object.page_count +'</mark>页</span></li><li><a href="/admin/business/index?on_sale='
                    + object.state +'&page='
                    + (object.current -1) + '">上一页</a></li>';
            }


            var footStr = '<li class="next-page" > <a href="/admin/business/index?on_sale='
                + object.state +'&page='
                + (object.current + 1) +'">下一页</a> </li><li><a href="/admin/business/index?on_sale='
                + object.state +'&page='
                + object.page_count +'">末页</a></li>';

            var insertStr = '<li> <a href="javascript:;">...</a></li>';

            var mainStr = '';
            if(object.page_count <= max && object.page_count > 0){
                mainStr = index.loopStr(1,object.page_count,object.state,object.current)
            }else if(object.page_count == 0){
                headStr = '';
                mainStr = '';
                footStr = '';
            }else if(object.page_count == object.current){
                footStr = '';
                mainStr = index.loopStr(1,2, object.state, object.current) + insertStr + index.loopStr(object.current -2, object.page_count, object.state, object.current);
            }else if (object.page_count > max && max - object.current <= 2 && max - object.current > 0){
                mainStr = index.loopStr(1,2,object.state,object.current) + insertStr + index.loopStr(object.current -2,object.current +2,object.state,object.current) + insertStr;
            }else if (object.page_count > max && object.current >= max && object.current + 2 < object.page_count){
                mainStr = index.loopStr(1,2,object.state,object.current) + insertStr + index.loopStr(object.current -2,object.current +2,object.state,object.current) + insertStr;
            }else if (object.page_count > max && object.current >= max && object.current + 2 == object.page_count){
                mainStr = index.loopStr(1,2,object.state,object.current) + insertStr + index.loopStr(object.current -2,object.current +2,object.state,object.current);
            }else if (object.page_count > max && object.current >= max && object.current + 2 > object.page_count){
                mainStr = index.loopStr(1,2,object.state,object.current) + insertStr + index.loopStr(object.current -2,object.page_count,object.state,object.current);
            }else {
                mainStr = index.loopStr(1,max,object.state,object.current) + insertStr
            }
            return headStr + mainStr + footStr;
        },

        setPage:function(){
            var pageInfo = JSON.parse($('#page_info').val());
            pageInfo.current = parseInt(pageInfo.current,10);
            if(pageInfo.current === parseInt(pageInfo.page_count)){
                $('.next-page').on('click',function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                })

            }
            var pageStr = index.pageHtmlStr(pageInfo,7);
            $('.pagination').empty().append(pageStr);
        }
    }

})(window,document);
