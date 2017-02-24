/**
 * Created by Administrator on 2017/2/20 0020.
 */

(function () {
    $('.mer-nav').on('click', '.nav-item', function () {
        var $this = $(this);
        $this.addClass('active-nav').siblings().removeClass('active-nav');
    });
})();