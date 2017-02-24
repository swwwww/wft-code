/**
 * Created by Administrator on 2017/2/22 0022.
 */
(function (exports) {
    var POCT, P, mb = POCT = P = P || {
            version : '1.0'
        };

    P.page = function (window, document, undefined) {
        //count_num: 总的条数
        //page_count： 总页数  objec
        //current  当前页数
        //state ： 售卖状态
        //max: 最多显示几条
        /*var page_Obj = {
            count_num : count_num,
            page_count： page_count,
            current : current
        };*/

        function loopStr(start, end, url, current){
            // console.log(end);
            var str = '';
            for(var i = start; i <= end; i++){
                if(i == current){
                    str += '<li><a class="active" href='+url + '&page='+ i + '>' + i +'</a></li>';
                }else{
                    str += '<li><a class="" href='+ url + '&page=' + i + '>' + i +'</a></li>';
                }
            }
            return str;
        }

        function pageHtmlStr(page_Obj, max, url){
            var current = parseInt(page_Obj.current, 10);
            var pageCount = parseInt(page_Obj.page_count, 10);

            if(current==1){
                var headStr = '<li><span>'
                    + page_Obj.count_num + '条记录' + current +'/<mark>'
                    + pageCount +'</mark>页</span></li>';
            }else {
                var headStr = '<li><span>'
                    + page_Obj.count_num + '条记录' + current +'/<mark>'
                    + pageCount +'</mark>页</span></li><li><a href="'+url+'&page='+(current - 1) + '">上一页</a></li>';
            }

            var footStr = '<li> <a href='+ url +'&page='
                + (current + 1) +'>下一页</a> </li><li><a href='+url+'&page='
                + pageCount +'>末页</a></li>';

            var insertStr = '<li> <a href="javascript:;">...</a></li>';

            var mainStr = '';

            if(pageCount <= max && pageCount > 1){
                mainStr = loopStr(1,pageCount, url, current)
            }else if(pageCount == 1){
                headStr = '';
                mainStr = '';
                footStr = '';
            }else if(pageCount == current){
                footStr = '';
                mainStr = loopStr(1,2, url, current) + insertStr + loopStr(current -2, pageCount, url, current);
            }else if (pageCount > max && max - current <= 2 && max - current > 0){
                mainStr = loopStr(1,2, url, current) + insertStr + loopStr(current -2, current +2, url, current) + insertStr;
            }else if (pageCount > max && current >= max && current + 2 < pageCount){
                mainStr = loopStr(1,2, url, current) + insertStr + loopStr(current -2, current +2, url, current) + insertStr;
            }else if (pageCount > max && current >= max && current + 2 == pageCount){
                mainStr = loopStr(1,2, url, current) + insertStr + loopStr(current -2, current +2, url, current);
            }else if (pageCount > max && current >= max && current + 2 > pageCount){
                mainStr = loopStr(1,2, url, current) + insertStr + loopStr(current -2, pageCount, url, current);
            }else {
                mainStr = loopStr(1,max,url, current) + insertStr
            }

            return headStr + mainStr + footStr;
        }

        function setPage(page_Obj, max, url){
            var pageStr = pageHtmlStr(page_Obj, max, url);
            $('.pagination').empty().append(pageStr);
        }

        return {
            setPage: setPage
        }
    }(window, document);

    exports.P = P;
})(window);
