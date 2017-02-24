// 轮播的图片
(function() {
    var god = function() {
        var islider_dom = {};
        var lottery_vo = {};

        function initData() {
            god.lottery_vo = JSON.parse($('#hide_lottery_vo').val());

            var star_arr = god.lottery_vo.all_superstar;

            var result = [];
            var content = {};

            for ( var item in star_arr) {
                var slide_item_arr = [];

                var star = star_arr[item];
                var sort = parseInt(item, 10) + 1;
                var id = star['id'];
                var name = star['name'];
                var avatar = star['avatar'];
                var total = star['total'];

                slide_item_arr.push('<img class="js-vote-star-' + item + '" data-id="' + id + '" src="' + avatar + '"/>');
                slide_item_arr.push('<p class="b-tit db pr">');
                slide_item_arr.push('<span class="top-num">NO.' + sort + '</span>');
                slide_item_arr.push('<span class="top-select">已有' + total + '人选择他</span>');
                slide_item_arr.push('</p>');

                content = {
                    content : slide_item_arr.join('')
                };

                result.push(content);
            }

            return result;
        }

        function render() {
            var star_list = initData();

            var config = {
                type : 'dom',
                data : star_list,
                dom : document.querySelector('.js-com-slide-media'),
                isLooping : true,
                isAutoplay : 1,
                duration : 2500,
                animateType : 'flow',
                useZoom : true
            };

            god.islider_dom = new iSlider(config);
        }

        return {
            lottery_vo : lottery_vo,
            islider_dom : islider_dom,
            render : render
        };
    }();

    $(document).ready(function() {
        var dao = DAO.lottery;
        god.render();

        var lottery_id = god.lottery_vo.lottery_id;

        $('body').on(M.click_tap, '.js-vote-choice', function() {
            $this = $(this);

            var login_url = base_url_module + 'lottery/login/lottery_id/' + lottery_id;
            if (!M.util.checkLogin($this, login_url)) {
                return false;
            }

            $('.matte').show();
            $('.gif-img').show();
            god.islider_dom.isAutoplay = 0;

            var vote_index = god.islider_dom.slideIndex;

            var star_id = $('.js-vote-star-' + vote_index).attr('data-id');
            var post_data = {
                lottery_id : lottery_id,
                star_id : star_id
            };

            dao.voteSuperstar(post_data, function(res) {
                if (res.status == 0) {
                    alert(res.msg);
                    window.location = login_url;
                } else {
                    setTimeout(function() {
                        window.location = base_url_module + 'lottery/friend/lottery_id/' + lottery_id + '?vote_id=' + res.data;
                    }, 850);
                }
            });
        });

        // 动态及点击轮播
        var swiper = new Swiper('.swiper-container', {
            pagination : '.swiper-pagination',
            nextButton : '.swiper-button-next',
            prevButton : '.swiper-button-prev',
            paginationClickable : true,
            spaceBetween : 30,
            centeredSlides : true,
            loop : true,
            autoplay : 3000,
            autoplayDisableOnInteraction : false
        });


        $('body').on(M.click_tap, '#go-get-prise', function () {
            var vote_id = parseInt($(this).attr('data-vote-id'), 10);
            var loginTip = $('#loginTip');
            if(vote_id == 0){
                loginTip.text("选1个男神，即可抽奖啦！").show();
                setTimeout(function(){
                    loginTip.hide();
                },5000);
            }else{
                var url = base_url_module + 'lottery/friend/lottery_id/2?vote_id=' + vote_id;
                window.location = url;
            }
        });
    });
})();
