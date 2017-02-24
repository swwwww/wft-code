/**
 * Created by deyi on 2016/8/24.
 */
//分类切换加载数据
var nav = $('.nav'),
    cate = $('.menu-cate'),
    age = $('.menu-age'),
    hot = $('.menu-new');

var config_tips = {
    msg: '提示信息',
    padding_tb: '4%',
    padding_rl: '4%',
    top: '50%',
    font_size: 28,
    time: 2500,
    z_index: 2000
};

nav.on('click','li',function(){
    var $this =$(this);
    itemIndex = $this.index();
    if($this.find('a').hasClass('active')){
        $this.find('a').removeClass('active');
        $('.adv').eq(itemIndex).hide();
    }else{
        $this.find('a').addClass('active');
        $this.siblings('li').find('a').removeClass('active');
        $('.adv').eq(itemIndex).show().siblings('.adv').hide();
    }
});

//所有分类列表
cate.on('click','a',function(){
    var val = $(this).text();
    $(this).addClass('active').siblings('a').removeClass('active');
    nav.find('li').eq(0).find('mark').text(val);
    nav.find('li').eq(0).find('a').removeClass('active');
    cate.hide();
});

//年龄分类列表
age.on('click','li',function(){
    var val = $(this).find('a').text();
    $(this).addClass('active').siblings('li').removeClass('active');
    nav.find('li').eq(1).find('mark').text(val);
    nav.find('li').eq(1).find('a').removeClass('active');
    age.hide();
});

//最热门分类列表
hot.on('click','a',function(){
    var val = $(this).text();
    $(this).addClass('active').siblings('a').removeClass('active');
    nav.find('li').eq(2).find('mark').text(val);
    nav.find('li').eq(2).find('a').removeClass('active');
    hot.hide();
});

//加载数据
function GetQueryString(name) {
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return decodeURI(r[2]); return null;
}

var id = GetQueryString('id');
var age_min = GetQueryString('age_min');
var age_max = GetQueryString('age_max');
var order = GetQueryString('order');
var addr_x;
var addr_y;
var map_data = M.location.getMapData();

//页面初始化
if(order==null){
    order='hot';
}
if(age_max==null){
    age_max=100;
}
if(age_min==null){
    age_min=0
}

if(map_data){
    addr_x = map_data['lng'];
    addr_y = map_data['lat'];
}

function renderData(){
    var dao = DAO.playList;
    var param = {
        'id':id,
        'order':order,
        'age_min':age_min,
        'age_max':age_max,
        'addr_x':addr_x,
        'addr_y':addr_y,
        'page':1,
        'page_num':10
    };
    dao.getAllPlayground(param, function (res) {
        if(res.status == 1){
            var data = res['data'];
            var result = [];
            result['res'] = data['place_list'];
            $('.play-list').append(template('list', result));
        }else{
            config_tips.msg = res.msg;
            M.util.popup_tips(config_tips);
        }
    });
}
renderData();

//加载更多
var counter = 1;
var dropload = $('.play').dropload({
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
            var dao = DAO.playList;
            var param = {
                'id':id,
                'order':order,
                'age_min':age_min,
                'age_max':age_max,
                'addr_x':addr_x,
                'addr_y':addr_y,
                'page':counter,
                'page_num':10
            };

            dao.getAllPlayground(param, function (res) {
                if(res.status == 1){
                    var data = res['data'];
                    var result = [];
                    result['res'] = data['place_list'];
                    setTimeout(function(){
                        $('.play-list').append(template('more', result));
                        me.resetload();
                    },500);

                    if(data['place_list']== 0){
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