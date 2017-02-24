/**
 * Created by xxxian on 2016/9/24 0024.
 */

(function () {
    var subBtn= $('#submit'),
        loginTip = $("#tips-dia"),
        name = $("#name"),
        card_id = $("#id_num"),
        id = $('#associates_id').val(),
        sid = $('#sid').val()>=0 ? $('#sid').val() : null,
        config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '34%',
        font_size: 28,
        time: 2500,
        z_index: 100
    };

    $('body').on(M.click_tap, '#submit', function () {
        var flag = Verify_person(name, card_id);

        if (flag){
            var dao = DAO.traveller,
                param={};
            
            M.load.loadTip('loadType', '提交中...', 'delay');
            if(id>0){//编辑出行人
                param = {
                    'associates_id': id,
                    'id_num': card_id.val(),
                    'name': name.val()
                };
                dao.playEditTraveller(param, function (res) {
                    console.log(res);
                    alert_msg(res);
                });
            }else { //添加出行人
                param = {
                    'name': name.val(),
                    'id_num': card_id.val()
                };
                dao.playAddTraveller(param, function (res) {
                    alert_msg(res);
                });
            }
        }
    });


//显示添加或编辑出行人的提示信息
    function alert_msg(res) {
        if(res.status == 1){
            var data = res['data'];

            M.load.loadTip('doType', res.msg, 'delay');
            // config_tips.msg = res.msg;
            // M.util.popup_tips(config_tips);
            if(data.status == 1){
                if(!sid){
                    setTimeout(function(){
                        window.location.href = '/play/playSelectTraveller';
                    },2000);
                }else {
                    M.util.hidePopup();
                }
            }else{//其他提示
                //tod...
            }

        }else{
            M.load.loadTip('doType', res.msg, 'delay');
            // config_tips.msg = res.msg;
            // M.util.popup_tips(config_tips);
        }
    }


//验证输入姓名及身份证信息函数 信息输入有误返回false， 正确返回为true
    function Verify_person(name, card_id) {
        var nameReg = /^[\u4E00-\u9FA5]+$/,
            idReg = /^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/;

        if(name == ''){
            config_tips.msg = "您还未输入姓名";
            M.util.popup_tips(config_tips);
            setTimeout(function(){
                subBtn.attr('disabled', false);
            },3000);
            name.focus();
            return false;
        }

        if(!nameReg.test(name.val())){
            config_tips.msg = "请输入中文名！";
            M.util.popup_tips(config_tips);
            setTimeout(function(){
                subBtn.attr('disable', false);
            }, 3000);
            card_id.focus();
            return false;
        }

        if(card_id ==''){
            config_tips.msg = "您还未输入身份证号";
            M.util.popup_tips(config_tips);
            setTimeout(function(){
                subBtn.attr('disabled', false);
            },3000);
            card_id.focus();
            return false;
        }else if (!idReg.test(card_id.val())) {
            console.log('1212');
            config_tips.msg = "身份证格式不正确！";
            M.util.popup_tips(config_tips);
            // setTimeout(function(){
            //     subBtn.attr('disabled', false);
            // },3000);
            card_id.focus();
            return false;
        }

        return true;
    }
})();


