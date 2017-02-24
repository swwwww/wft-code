/**
 * Created by deyi on 2016/8/25.
 */
//创建和初始化地图函数：
function initMap(){
    createMap();//创建地图
    setMapEvent();//设置地图事件
    addMapControl();//向地图添加控件
    //addMarker();//向地图中添加marker
}

//创建地图函数：
function createMap(addr_x,addr_y,shop_address,shop_name){
    var map = new BMap.Map("map_Content");//在百度地图容器中创建一个地图
    var point = new BMap.Point(addr_x,addr_y);//定义一个中心点坐标
    var marker = new BMap.Marker(point);//创建标注
    var opts ={
        width: 10,
        height:5,
        title:shop_name
    };
    var infoWindow = new BMap.InfoWindow(shop_address,opts);//创建窗口信息
    map.addOverlay(marker);//将标注添加到地图
    map.centerAndZoom(point,18);//设定地图的中心点和坐标并将地图显示在地图容器中

    map.openInfoWindow(infoWindow,point); //开启信息窗口
    marker.addEventListener("click", function(){
        map.openInfoWindow(infoWindow,point); //开启信息窗口
    });
    window.map = map;//将map变量存储在全局
}

//地图事件设置函数：
function setMapEvent(){
    map.enableDragging();//启用地图拖拽事件，默认启用(可不写)
    map.enableScrollWheelZoom();//启用地图滚轮放大缩小
    map.enableDoubleClickZoom();//启用鼠标双击放大，默认启用(可不写)
    map.enableKeyboard();//启用键盘上下左右键移动地图
}

//地图控件添加函数：
function addMapControl(){
    //向地图中添加缩放控件
    var ctrl_nav = new BMap.NavigationControl({anchor:BMAP_ANCHOR_TOP_LEFT,type:BMAP_NAVIGATION_CONTROL_LARGE});
    map.addControl(ctrl_nav);
}
initMap();//创建和初始化地图
