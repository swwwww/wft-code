(function (exports) {
   var app_bridge = {
       open: function (id, type) {
           var $this = $(this);
           var u = navigator.userAgent;

           if(M.browser.wft){
               if(M.browser.ios){
                   if(type == 0){
                       document.location.href = 'game_info$$id=' + id;
                   }else if(type == 1){
                       document.location.href = 'kids_play$$id=' + id;
                   }else if(type == 2){
                       document.location.href = 'tab$$name=selection';
                   }else {// 充值
                       document.location.href = 'recharge$$id=selection' + id;
                   }
               }else{
                   if(type == 0){
                       window.getdata.game_info(id);
                   }else if(type == 1){
                       window.getdata.kids_play(id);
                   }else if(type == 2){
                       window.getdata.tab('selection');
                   }else {// 充值
                       window.getdata.recharge(id);
                   }
               }
           }
       }
   };
    exports.app_bridge = app_bridge;
})(window);

(function () {
    $('body').on('click', '.js-action-redirect', function () {
        var $this = $(this);
        var id = $this.attr('data-id');
        var type = $this.attr('data-type');
         app_bridge.open(id, type);
    });
})();
