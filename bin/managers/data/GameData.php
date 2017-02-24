<?php

/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/26
 * Time: 09:39
 */
class GameData extends Manager {
    public function getGameInfoById ($id) {
        $data_result = array(
            'status' => 1,
            'msg'    => '',
            'data'   => array()
        );

        if (empty($id)) {
            $data_result = array(
                'status' => 0,
                'msg'    => '套系详情id出错'
            );
        } else {
            $data_result = GameInfoGameVo::model()->find('id = :id', array(':id' => $id));
            if (empty($data_result)) {
                $data_result = array(
                    'status' => 0,
                    'msg'    => '套系详情信息丢失'
                );
            } else {
                $data_result = array(
                    'status' => 1,
                    'msg'    => '',
                    'data'   => $data_result
                );
            }
        }
        return $data_result;
    }

    public function updateGameInfoBuyNumber ($id, $buy_number) {
        $action_result = array(
            "status" => 1,
            "msg"    => ""
        );

        if (empty($id)) {
            LogUtil::info("[GameData](updateGameInfoBuyNumber) 在更新套系已售数量时，发现套系详情的id丢失");
            $action_result = array(
                "status" => 0,
                "msg"    => "套系详情的id丢失"
            );
        } else {
            $pdo     = Yii::app()->db;
            $sql     = "UPDATE play_game_info SET buy = buy + :buy_numbe WHERE id = :id AND buy + :buy_number =< total_num";
            $command = $pdo->createCommand($sql);

            // 绑定参数
            $command->bindParam(":id",         $id);
            $command->bindParam(":buy_number", $buy_number);

            $data_return = $command->execute();
            if (empty($data_return)) {
                LogUtil::info("[GameData](updateGameInfoBuyNumber) 场次 " . $id . " 目前剩余名额不足 " . $buy_number . " 人");
                $action_result = array(
                    "status" => 0,
                    "msg"    => "该场次目前剩余名额不足"
                );
            } else {
                $action_result = array(
                    "status" => 1,
                    "msg"    => "套系详情下的已售数量已经更新成功"
                );
            }
        }

        return $action_result;
    }
}
