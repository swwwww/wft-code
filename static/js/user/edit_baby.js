/**
 * Created by xxxian on 2016/9/30 0030.
 */
(function () {
    var tipsDia=$('#tips-dia');
    var config_tips = {
        msg: '提示信息',
        padding_tb: '4%',
        padding_rl: '4%',
        top: '34%',
        font_size: 28,
        time: 2500,
        z_index: 100
    };
    //上传图片
    var viewImg = $("#upload-pic");
    $('#upload-image').localResizeIMG ({
        width: 800,
        quality: 0.8,
        success: function (result) {
            var status = true;
            if (result.height > 1600) {
                status = false;
                alert("照片最大高度不超过1600像素");
            }
            if (status) {
                viewImg.html('<img src="' + result.base64 + '"/><input type="hidden" id="file' + '" name="fileup[]" value="'+ result.clearBase64 + '">');
            }
        }
    });

    $('body').on(M.click_tap, '#sub', function () {
        var img = $('input[name="fileup[]"]').val();
        var name = $("#name").val();
        var old = $('#old').val();
        old = parseInt(new Date(old).getTime()/1000);
        var sel = document.getElementById("sex");
        var sex = sel.options[sel.selectedIndex].getAttribute('name');
        var id = $("#id").val();
        var act = $('#act').val();

        if (!name || !old || !sex) {
            tipsDia.text("请填齐所有资料");
            tipsDia.show();
            setTimeout(function(){
                tipsDia.hide();
                tipsDia.text("");
            },2000);
            return false;
        }

        if(act=='add') {
            // tipsDia.text("正在处理...");
            // tipsDia.show();
            setTimeout(function () {
                tipsDia.hide();
                tipsDia.text("");
            }, 3000);
            var child_id= 0;
            var dao=DAO.user;
            var params={
                'act': act,
                'username': name,
                'sex': sex,
                'old': old
            };
            dao.addMyBabyInfo(params, function (res) {
                console.log(res);
                if(res.status == 1){
                    tipsDia.text(res.message);
                    tipsDia.show();
                    setTimeout(function(){
                        tipsDia.hide();
                        // tipsDia.text("");
                        // window.location.href = base_url_module + 'user/babylist';
                    },2000);
                    child_id = res.baby_id;
                }else if(res.status == 0){
                    tipsDia.text(res.message);
                    tipsDia.show();
                    setTimeout(function(){
                        tipsDia.hide();
                        // tipsDia.text("");
                    },2000);
                }
            });

            var paramsImg={
                'img':img
            };
            dao.upLoadBabyImg(paramsImg, function (res) {
                console.log(res);
            });
        }else if (act=='fix'){
            // tipsDia.text("正在处理...");
            // tipsDia.show();
            setTimeout(function () {
                tipsDia.hide();
                tipsDia.text("");
            }, 3000);
            var dao=DAO.user;
            var params={
                'act': act,
                'username': name,
                'sex': sex,
                'old': old,
                'link_id' : id
            };
            console.log(id);
            dao.addMyBabyInfo(params, function (res) {
                console.log(res);
                if(res.status){
                    var res = res['data'];
                    if(res.status == 1){
                        // tipsDia.text(res.message);
                        tipsDia.text('baby资料修改成功！');
                        tipsDia.show();
                        setTimeout(function(){
                            tipsDia.hide();
                            // tipsDia.text("");
                            window.location.href = base_url_module + 'user/babylist';
                        },2000);
                    }else if(res.status == 0){
                        // // tipsDia.text(res.message);
                        // // tipsDia.show();
                        // setTimeout(function(){
                        //     tipsDia.hide();
                        //     // tipsDia.text("");
                        // },2000);
                    }
                }else {
                   /*// 其它原因报错, todo...*/
                }

            });

            var paramsImg={
                'img':img
            };
            dao.upLoadBabyImg(paramsImg, function (res) {
                console.log(res);
                if(res.status){
                    var res = res['data'];
                    console.log(res);
                    if(res.status == 1){
                        // tipsDia.text(res.message);
                        tipsDia.text(res.msg);
                        tipsDia.show();
                        setTimeout(function(){
                            tipsDia.hide();
                            // tipsDia.text("");
                            // window.location.href = base_url_module + 'user/babylist';
                        },2000);
                    }else {
                        // tipsDia.text(res.msg);
                        // tipsDia.show();
                        // setTimeout(function(){
                        //     tipsDia.hide();
                        //     // tipsDia.text("");
                        // },2000);
                    }
                }else {
                    /*// 其它原因报错, todo...*/
                    // tipsDia.text(res.error_msg);
                    // tipsDia.show();
                    // setTimeout(function(){
                    //     tipsDia.hide();
                    //     // tipsDia.text("");
                    // },2000);
                }
            });
        }

    });
})();

