/**
 * Created by MEX | mixmore@yeah.net on 16/11/9.
 */
(function () {
    "use strict";
    var oDiv = document.getElementById("tab"),
        oLi = oDiv.getElementsByTagName("div")[0].getElementsByTagName("li"),
        aCon = oDiv.getElementsByTagName("div")[1].getElementsByTagName("div");
    for (var i = 0; i < oLi.length; i++) {
        oLi[i].index = i;
        oLi[i].ontouchstart = function() {
            show(this.index);
        };
        oLi[i].onclick = function() {
            show(this.index);
        };
    }
    function show(a) {
        var  index = a;
        for (var j = 0; j < oLi.length; j++) {
            oLi[j].className = "";
            aCon[j].className = "";
        }
        oLi[index].className = "cur";
        aCon[index].className = "cur";
    }
}());