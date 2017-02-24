<?php

/**
 * Created by IntelliJ IDEA.
 * User: deyi
 * Date: 2016/8/15
 * Time: 16:33
 */
class RecommendController extends Controller
{

    public function filters()
    {
        return array(array(
                         'application.filters.SetNavFilter',
                         'mobile_title' => '精选',
                         'controller'   => $this,
                     ));
    }

    /*精选首页*/
    public function actionIndex()
    {
        $lib  = new RecommendLib();
        $data = array(
            'page'     => 1,
            'page_num' => 10,
        );
        $res  = $lib->choiceList($data);
        $info = $lib->firstAlert();
        $hot = $lib->searchIndex();
        $history = unserialize(CookieUtil::get('history'));
        $res['data']['notice'] = $info['data'];
        $res['data']['history'] = $history;
        $res['data']['hot'] = $hot['data'];
        $this->tpl->assign('res', $res['data']);
        $this->tpl->display('recommend/m/index.html');
    }

    /*选择城市*/
    public function actionSelectCity()
    {
        $this->tpl->display('recommend/m/select-city.html');
    }

    /*搜索*/
    public function actionSearch()
    {
        $hot = RecommendLib::searchIndex();
        $history = unserialize(CookieUtil::get('history'));
        $res['hot'] = $hot['data'];
        $res['history'] = $history;
        $this->tpl->assign('res', $res);
        $this->tpl->assign('history', $history);
        $this->tpl->display('recommend/m/search.html');
    }

    /*搜索详情*/
    public function actionSearchDetail()
    {
        $word = $_GET['word'];
        $service = new RecommendService();
        $service->updateSearchHistory($word);
        $res['word'] = $word;
        $this->tpl->assign('res',$res);
        $this->tpl->display('recommend/m/search-detail.html');
    }

    /* 删除搜索缓存 */
    public function actionDelSearchHistory()
    {
        CookieUtil::del('history');
        $res = array(
            'status' => 1,
            'msg'    => '历史记录清除成功'
        );
        HttpUtil::out($res);
    }

    public function actionGetAllIndex()
    {
        $lib = new RecommendLib();
        HttpUtil::out($lib->choiceList($_POST));
    }

    public function actionGetAllSearch()
    {
        $lib = new RecommendLib();
        HttpUtil::out($lib->searchDetail($_POST));
    }
}