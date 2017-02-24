/**
 * Created by deyi on 2016/8/23.
 */
//选项状态切换
function showType(show,order){
    if(show=='good'){
        $("#good").addClass('active');
        $(".good").show();
        $(".place").hide();
        $(".excercise").hide();
        $("#place").removeClass('active');
        $("#excercise").removeClass('active');
        $("."+order).addClass('active').siblings().removeClass('active');
        $('.good-list').show();
        $('.place-list').hide();
        $('.excercise-list').hide()
    }else if(show=='excercise'){
        $("#excercise").addClass('active');
        $(".excercise").show();
        $(".good").hide();
        $(".place").hide();
        $("#good").removeClass('active');
        $("#place").removeClass('active');
        $("."+order).addClass('active').siblings().removeClass('active');
        $('.good-list').hide();
        $('.place-list').hide();
        $('.excercise-list').show()
    } else if(show =='place'){
        $("#place").addClass('active');
        $(".place").show();
        $(".good").hide();
        $(".excercise").hide();
        $("#good").removeClass('active');
        $("#excercise").removeClass('active');
        $("."+order).addClass('active').siblings().removeClass('active');
        $('.good-list').hide();
        $('.place-list').show();
        $('.excercise-list').hide()
    }
}

function GetQueryString(name){
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return decodeURI(r[2]); return null;
}

var id = GetQueryString('id');
var show = GetQueryString('show');
var order = GetQueryString('order');
var view_type = $('#view_type').val();
var addr_x;
var addr_y;
var config_tips = {
    msg: '提示信息',
    padding_tb: '4%',
    padding_rl: '4%',
    top: '50%',
    font_size: 28,
    time: 2500,
    z_index: 2000
};
if(!show){
    if(view_type == 1){
        show = 'place'
    }else if(view_type == 2){
        show = 'good'
    }else if(view_type == 3){
        show = 'place'
    }else if(view_type == 4){
        show = 'good'
    }else if(view_type == 5){
        show = 'excercise'
    }else if(view_type == 6){
        show = 'excercise'
    }
}

if(!order){
    order='hot'
}

//加载数据
function renderData(){
    var dao = DAO.topic;
    var map_data = M.location.getMapData();
    if(map_data){
        addr_x = map_data['lng'];
        addr_y = map_data['lat'];
    }
    var param = {
        'id':id,
        'show':show,
        'order':order,
        'page':1,
        'addr_x':addr_x,
        'addr_y':addr_y
    };

    dao.getAllInfo(param, function (res){
        if(res.status ==1){
            var data =res['data'];
            var place = $('.place-list');
            var excer = $('.excercise-list');
            var good = $('.good-list');
            var result_place = [];
            var result_excercise =[];
            var result_good=[];
            result_place['place_list'] = data['place_list'];
            result_excercise['excercise_list'] =data['excercise_list'];
            result_good['good_list'] =data['good_list'];

            if(!result_place['place_list'] || result_place['place_list'].length==0){
                var html_place= '<div class="no-data">' + '<img src="/static/img/site/mobile/nodata.gif" />' + '</div>';
                place.append(html_place);

            }

            if(!result_excercise['excercise_list'] || result_excercise['excercise_list'].length==0){
                var html_excer = '<div class="no-data">' + '<img src="/static/img/site/mobile/nodata.gif" />' + '</div>';
                excer.append(html_excer);
            }

            if(!result_good['good_list'] || result_good['good_list'].length==0){
                var html_good = '<div class="no-data">' + '<img src="/static/img/site/mobile/nodata.gif" />' + '</div>';
                good.append(html_good);
            }
            place.append(template('list_place', result_place));
            excer.append(template('list_excercise', result_excercise));
            good.append(template('list_good', result_good));
            showType(show,order);
        }else{
            config_tips.msg = res.msg;
            M.util.popup_tips(config_tips);
        }
    });
}
renderData();

//加载更多

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
            var dao = DAO.topic;
            var param = {
                'id':id,
                'show':show,
                'order':order,
                'page':counter,
                'addr_x':addr_x,
                'addr_y':addr_y
            };
            dao.getAllInfo(param, function (res){
                console.log(res);

                if(res.status == 1){
                    var data = res['data'];
                    var result = [];
                    result['res'] = data;

                    if(show=='good'){
                        setTimeout(function () {
                            $('.good-list').append(template('list_good', result['res']));
                            me.resetload();
                        }, 500);
                        if(!data['good_list'] || data['good_list'].length == 0){
                            me.lock();
                            me.noData();
                            return;
                        }

                    }else if(show=='excercise'){
                        setTimeout(function () {
                            $('.excercise-list').append(template('list_excercise', result['res']));
                            me.resetload();
                        }, 500);
                        if(!data['excercise_list'] || data['excercise_list'].length ==0){
                            me.lock();
                            me.noData();
                            return;
                        }
                    }else if(show=='place'){
                        setTimeout(function () {
                            console.log(result);
                            $('.place-list').append(template('list_place', result['res']));
                            me.resetload();
                        }, 500);
                        if(!data['place_list'] || data['place_list'].length == 0){
                            me.lock();
                            me.noData();
                            return;
                        }
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
