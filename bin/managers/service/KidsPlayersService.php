<?php
/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2017/1/10 0010
 * Time: 上午 10:53
 */
class KidsPlayersService extends Manager{

    public function kidsPlayersPage()
    {
        $Players = array(
            0  => array(
                'name'      => '可乐', /*陈必诚陈必诚*/
                'img'       => 'cbc.jpg'
            ),
            1  => array(
                'name'      => '笑笑 ', /*冯笑慰*/
                'img'       => 'fxw.jpg'
            ),
            2  => array(
                'name'      => '奥利奥', /*王起明*/
                'img'       => 'wqm.png'
            ),
            3  => array(
                'name'      => '媛媛', /*诸媛媛*/
                'img'       => 'zyy.jpg'
            ),
            4  => array(
                'name'      => '阳光', /*郑炜*/
                'img'       => 'zh.jpg'
            ),
            5  => array(
                'name'      => '软糖', /*宋金凤*/
                'img'       => 'sjf.jpg'
            )
        );
        return $Players;
    }
}
