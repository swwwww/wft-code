/**
 * Created by deyi on 2016/11/3.
 */
(function () {
    var score,
        viewImg = $("#upload-list"),
        submitBtn = $("#w_submit"),
        imgcount = 0;

    var dao = DAO.comment;

    //评分点击
    $('.all-comment').on(M.click_tap, 'a', function (e) {
        e.preventDefault();
        var target = $(e.target);
        var itemIndex = target.index();
        score = target.attr('data-star');
        target.siblings().removeClass('star-cur');
        for (var i = 0; i < itemIndex; i++) {
            $('.comm-star').eq(i).addClass('star-cur');
        }
        $("#w_star_holder").val(score);
    });


    //添加图片
    $('#upload-image').localResizeIMG({
        width: 800,
        quality: 0.8,
        success: function (result) {
            var status = true;
            if (result.height > 1600) {
                status = false;
                alert("照片最大高度不超过1600像素");
                M.util.loadTip('err')
            }
            if (viewImg.find("li").length > 8) {
                alert("最多上传9张照片");
            }
            if (status) {
                var fileParam = {'file': result.clearBase64};
                console.log(fileParam);
                dao.uploadImg(fileParam, function (res) {
                    console.log(res);
                    if (res.status == 0) {
                        M.load.loadTip('errorType', res.msg, 'delay');
                    } else {
                        viewImg.append('<li></li>');
                        viewImg.find("li:last-child").html('<span class="del">×</span><img src="' + result.base64 + '"/><input type="hidden" id="file'
                            + imgcount
                            + '" name="fileup[]" value="'
                            + res.data.url + '">');
                        var del = $(".del");
                        del.on("click", function () {
                            $(this).parent('li').remove();
                        });
                        imgcount++;
                    }
                });
            }
        }
    });

    //点击上传
    submitBtn.on(M.click_tap, function (e) {
        e.preventDefault();
        var info = $('input[name="fileup[]"]');
        var len = info.length;
        var img_list = [];
        for (var i = 0; i < len; i++) {
            var img = $(info[i]).val();
            if (img) {
                img_list.push(res.data.url)
            }
        }
        var mes = $("#w_describe").val(),
            star = $("#w_star_holder").val(),
            describe = $("#w_describe"),
            data = JSON.parse($("#data").val());

        if (describe.val() == "") {
            M.load.loadTip('errorType', '请输入评论', 'delay');
            return false;
        }
        if (star == 0) {
            M.load.loadTip('errorType', '请输入评分', 'delay');
            return false;
        }
        $(this).css("pointer-events", "none");
        var messageJson = [];
        messageJson.push({'t': 1, 'val': mes});
        if (img_list) {
            var img_len = img_list.length;
            for (var i = 0; i < img_len; i++) {
                messageJson.push({'t': 2, 'val': img_list[i]});
            }
        }

        var type = 0;
        if(data.type == 1){
            type = 2;
        }else if(data.type == 3){
            type = 3;
        }else{
            type = 7;
        }
        var param = {
            'type': type,
            'object_id': data.coupon_id,
            'message': JSON.stringify(messageJson),
            'star': parseInt(star) / 10,
            'order_sn': data.order_sn
        };
        dao.giveComment(param, function (res) {
            if (res.status == 0) {
                M.load.loadTip('errorType', res.msg, 'delay');
            } else {
                M.load.loadTip('doType', res.msg, 'delay');
                if(type == 3){
                   window.location.href = '/discover/playDetail?id='+data.coupon_id;
                }else{
                    window.location.href = '/user/order?order_status=3';
                }
            }
        })
    })

})();