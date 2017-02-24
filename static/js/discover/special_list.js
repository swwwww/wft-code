/**
 * Created by deyi on 2016/8/23.
 */

//加载更多
var config_tips = {
    msg: '提示信息',
    padding_tb: '4%',
    padding_rl: '4%',
    top: '50%',
    font_size: 28,
    time: 2500,
    z_index: 2000
};
var counter = 1;
var dropload = $('.inner').dropload({
    scrollArea : window,
    domDown : {
        domClass: 'dropload-down',
        domRefresh: '<div class="dropload-refresh" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">↑上拉加载更多</div>',
        domLoad: '<div class="dropload-load" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;"><span class="loading"></span>加载中...</div>',
        domNoData: '<div class="dropload-noData" style="font-size: 20px;height:50px;line-height: 50px;text-align: center;">已经全部加载完毕</div>'
    },
    //上拉加载函数
    loadDownFn : function(me){
        counter++;
        function renderMore(){
            var dao = DAO.special;
            var param = {
                'page': counter,
                'page_num': 5
            };
            dao.getAllSpecial(param, function (res) {
                if(res.status ==1){
                    var data =res['data'];
                    var result = [];
                    result['res'] = data;
                    setTimeout(function(){
                        $('.special').append(template('list', result));
                        me.resetload();
                    },500);

                    if(data == 0){
                        me.lock();
                        me.noData();
                        return;
                    }
                }else{
                    config_tips.msg = res.msg;
                    M.util.popup_tips(config_tips);
                }
            });
        }
        renderMore();
    }
});