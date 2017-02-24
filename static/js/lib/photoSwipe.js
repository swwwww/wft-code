/**
 * Created by deyi on 2016/11/23.
 */
var img_length = $('.rated-list').find('.gallery-img').length;

if(img_length > 0){
    (function(window, PhotoSwipe){
        document.addEventListener('DOMContentLoaded', function(){

            var options = {
                    captionAndToolbarOpacity: 0.5,
                    captionAndToolbarAutoHideDelay: 0
                    // fadeInSpeed: 100
                },
                instance = PhotoSwipe.attach( window.document.querySelectorAll('#Gallery a'), options );

        }, false);

    })(window, window.Code.PhotoSwipe);
}