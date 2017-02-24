/**
 * Created by xxxian on 2016/9/30 0030.
 */
(function () {
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
    $('#upload-image').localResizeIMG({
        width: 800,
        quality: 0.8,
        success: function (result) {
            var status = true;
            if (result.height > 1600) {
                status = false;
                alert("照片最大高度不超过1600像素");
            }
            if (status) {
                viewImg.html('<img src="' + result.base64 + '"/><input type="hidden" id="file' + '" name="fileup[]" value="' + result.clearBase64 + '">');
            }
        }
    });

    $('body').on(M.click_tap, '#sub', function () {
        var dao = DAO.user;

        var img  = $('input[name="fileup[]"]').val();
        var name = $("#name").val();
        var old  = parseInt(new Date($('#old').val()).getTime() / 1000);
        var sel  = document.getElementById("sex");
        var sex  = sel.options[sel.selectedIndex].getAttribute('name');

        if (!name || !old || !sex) {
            config_tips.msg = "请填齐所有资料";
            M.util.popup_tips(config_tips);
            return false;
        }

        var flagImg  = false;
        var flagInfo = false;

        // 用户头像更新
        var param = {
            'img': img
        };
        dao.uploadImg(param, function (res) {
            if (res.status == 1) {
                flagImg = true;
            }
            // 用户资料更新
            param = {
                'username': name,
                'sex': sex,
                'birth': old
            };
            dao.editMyInfo(param, function (res) {
                if (res.status == 1) {
                    flagInfo = true;
                }
                if (flagImg && flagInfo) {
                    config_tips.msg = "头像和资料更新成功";
                } else if (flagImg) {
                    config_tips.msg = "头像更新成功";
                } else if (flagInfo) {
                    config_tips.msg = "资料更新成功";
                } else {
                    config_tips.msg = "资料更新失败";
                }
                M.util.popup_tips(config_tips);
                window.location.href = '/user/center';
            });
        });
    });
})();

