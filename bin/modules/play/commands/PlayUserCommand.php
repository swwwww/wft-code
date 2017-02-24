<?php
class PlayUserCommand extends Command{

    public function actionProcessUser(){
        /**
         * 目前unionid的状态
         * 总：142477
         *
         * NULL：28463
         * ''： 4949
         * >= -1：1
         * 正常：109064
         */

        //拿到所有的union_id
        $union_sql = "select unionid from play_user_weixin where (unionid != '' and unionid is not null) group by unionid order by id desc";
        $all_union_id_arr = Yii::app()->db->createCommand($union_sql)->queryAll();

        foreach($all_union_id_arr as $u_key => $u_val){
            $union_id = $u_val['unionid'];

            //根据union_id获取同一个微信账号的open_id
            $open_sql = "select open_id from play_user_weixin where unionid = '{$union_id}'";
            $all_open_wechat_arr = Yii::app()->db->createCommand($open_sql)->queryAll();

            //容错之前没有union_id的用户，根据所有open_id获取同一个微信账号的用户
            $all_open_id_arr = array();
            foreach($all_open_wechat_arr as $key => $val){
                $all_open_id_arr[] = $val['open_id'];
            }

            $all_open_id_str = "'" . implode("', '", $all_open_id_arr) . "'";

            //将同一个微信账户的union_id统一更新为一个
            $up_sql = "update play_user_weixin set unionid = {$union_id} where open_id in ({$all_open_id_str})";
            //$up_rst = Yii::app()->db->createCommand($up_sql)->execute();

            //找到同一个微信账户对应的所有app账户
            $open_user_sql = "select wechat.uid, wechat.open_id, wechat.login_type, user.phone
                            from play_user_weixin as wechat
                            inner join play_user as user
                            on (wechat.uid = user.uid)
                            where wechat.open_id in ({$all_open_id_str})
                            group by user.uid";

            $all_open_user_arr = Yii::app()->db->createCommand($open_user_sql)->queryAll();

            $wechat_result = null;
            $phone_result = array();
            foreach($all_open_user_arr as $key => $val){
                $user_id = $val['uid'];
                $login_type = $val['login_type'];
                $phone = $val['phone'];

                if($phone){
                    $phone_result[] = $val;
                }

                if($login_type == 'weixin_wap'){
                    $wechat_result = $val;
                }
            }

            if(count($phone_result) > 1){
                echo "{$union_id}\t";

                foreach($phone_result as $key => $val){
                    $open_id = $val['open_id'];
                    $phone = $val['phone'];
                    $login_type = $val['login_type'];
                    echo " === {$open_id}\t{$login_type}\t{$phone}\t";
                }
                echo "\n";

                if($wechat_result){
                    $target_user_id = $wechat_result['uid'];
                    $target_phone = $wechat_result['phone'];
                }else{
                    $target_user_id = $phone_result[0]['uid'];
                    $target_phone = $phone_result[0]['phone'];
                }
            }
        }

    }
}
