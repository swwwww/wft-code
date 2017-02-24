/**
 * Created by deyi on 2016/9/29.
 */
(function(){
    var area1 = new LArea();
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '50%',
        font_size: 28,
        time: 2500,
        z_index: 2000
    };
    area1.init({
        'trigger': '#addr_info', //触发选择控件的文本框，同时选择完毕后name属性输出到该位置
        'valueTo': '#value1', //选择完毕后id属性输出到该位置
        'keys': {
            id: 'id',
            name: 'name'
        }, //绑定数据源相关字段 id对应valueTo的value属性输出 name对应trigger的value属性输出
        'type': 1, //数据源类型
        'data': LAreaData //数据源
    });
    area1.value=[17,16,10];//控制初始位置，注意：该方法并不会影响到input的value

    function GetQueryString(name) {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)return decodeURI(r[2]); return null;
    }

    $('#save').on(M.click_tap,function(e){
        var name_val = $('#name_info').val(),
            phone_val = $('#tel_info').val(),
            area_val = $('#addr_info').val(),
            area_arr = area_val.split(','),
            province = area_arr[0],
            city = area_arr[1],
            region = area_arr[2],
            detail_val =$('#detail_info').val(),
            nameReg = /^[\u4E00-\u9FA5]+$/,
            phoneReg = /^1\d{10}$/,
            id = $('#addr_id').val();

        if(!name_val){
            config_tips.msg = '姓名不能为空';
            M.util.popup_tips(config_tips);
            return false;
        }

        if(!nameReg.test(name_val)){
            config_tips.msg = '请输入中文名字';
            M.util.popup_tips(config_tips);
        }

        if(!phone_val){
            config_tips.msg = '手机号码不能为空';
            M.util.popup_tips(config_tips);
            return false;
        }

        if(!phoneReg.test(phone_val)){
            config_tips.msg = '手机号码格式错误';
            M.util.popup_tips(config_tips);
            return false;
        }

        if(!area_val){
            config_tips.msg = '地区信息不能为空';
            M.util.popup_tips(config_tips);
            return false;
        }

        if(!detail_val){
            config_tips.msg = '详细地址不能为空';
            M.util.popup_tips(config_tips);
            return false
        }

        $(this).text('提交中...');
        $(this).prop('disabled',true);
        $(this).addClass('disabled');

        var info_id = $('#sid').val(),
            coupon_id = $('#coupon_id').val(),
            dao = DAO.address,
            param = {
                'name':name_val,
                'phone':phone_val,
                'address':detail_val,
                'id':id,
                'province':province,
                'city':city,
                'region':region
            };
        dao.getAllEditAddress(param, function (res) {
            if(res.status == 1){
                var data = res['data'];
                if(data.status == 0){
                    config_tips.msg = data.message;
                    M.util.popup_tips(config_tips);
                    $(this).prop('disabled',false).text('保存').removeClass('disabled');
                }else{
                    config_tips.msg = data.message;
                    M.util.popup_tips(config_tips);
                    $(this).prop('disabled',false).text('保存').removeClass('disabled');
                    if(info_id || coupon_id){
                        window.location.href='/play/playSeleApplic?sid='+info_id+'&id='+coupon_id;
                    }else {
                        window.location.href='/user/addressList';
                    }
                }
            }else{
                config_tips.msg = res.msg;
                M.util.popup_tips(config_tips);
            }
        });
    })
})();

