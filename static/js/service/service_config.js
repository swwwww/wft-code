/**
 * Created by Administrator on 2016/12/5 0005.
 */

/*差的字段：全局判断用户是否是VIP is_vip*/
(function () {
    //用户信息
    var user_info = JSON.parse($('#user_info').val()),
        user_uid = user_info.uid,
        user_name  = user_info.username,
        user_mobile = user_info.phone,
        user_img = user_info.img,
        vip_id = user_info.is_vip,
        sex_id = user_info.child_sex,
        user_sex = '',
        is_vip = '',

    //商品信息
        good_info = JSON.parse($('#service_info').val()),
        product_id = good_info.id,
        product_title = good_info.share_title,
        product_desc = good_info.share_content,
        product_pic = good_info.share_image,
        product_price = good_info.product_price,
        product_url = good_info.share_url,
        product_type = good_info.product_type,
        group_id = $('#group_id_info').val(),
        good_type = '';

    if(sex_id == 1){
        user_sex = '男';
    }else {
        user_sex = '女';
    }
    if(vip_id){
        is_vip = '是'
    }else{
        is_vip = '否'
    }
    if(product_type == 1){
        good_type = '商品ID';
    }else if(product_type == 2){
        good_type = '活动ID';
    }else if(product_type == 3){
        good_type = '商品订单ID';
    }else if(product_type == 4){
        good_type = '活动订单ID';
    }
    if( (product_type == 3 || product_type == 4) && parseInt(product_price)){
        product_price = '￥' + product_price;
    }else {
        product_price = '￥' + product_price + '起';
    }
    
    //轻量CRM接入
    ysf.on({
        'onload': function(){
            //定义七鱼客服客端用户基本信息
            ysf.config({
                uid: user_uid,
                groupid: group_id,
                data:JSON.stringify([
                    {"key": "real_name", "value": user_name},
                    {"key": "mobile_phone", "value": user_mobile, "hidden":false},
                    {"key":"email", "value":"dev@wanfantian.com", "hidden":true},
                    {"index":0, "key": "user_uid", "label":"用户ID", "value": user_uid},
                    {"index":1, "key": "sex", "label":"性别", "value": user_sex},
                    {"index":2, "key":"avatar", "label":"img", "value":user_img},
                    {"index":5, "key": "is_vip", "label":"VIP会员", "value": is_vip},
                    {"index":6, "key": "product_id", "label": good_type, "value": product_id},
                ])
            });
            //商品信息
            ysf.product({
                show : 1, // 1为打开， 其他参数为隐藏（包括非零元素）
                note : product_price,
                title : product_title,
                desc : product_desc,
                picture : product_pic,
                url : product_url
            });
        },
        //未读取消息设置
        unread: function(msg){
            if(msg.total){
                alert('您有'+msg.total+'条消息未读取');
            }
        }
    });
    // 接口方式
    // ysf.unread();
})();

