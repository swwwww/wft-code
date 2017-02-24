<?php
/**
 * 积分数据层            负责积分相关的数据操作
 * @classname: IntegralData
 * @author 11942518@qq.com | quenteen
 * @date 2016-7-2
 */
class IntegralData extends Manager{

    /**
     * 根据用户id获取用户积分信息
     * @param   uid 用户的uid
     * @return
     */
    public static function getIntegralByUserId($param){
        LogUtil::info('[IntegralData](getIntegralByUserId)[start] with user uid: ' . $param['uid']);
        $data_result = array(
            'status' => 1,
            'msg'    => '',
            'data'   => array()
        );

        if (empty($param['uid'])) {
            $data_result = array(
                'status' => 0,
                'msg'    => '缺少用户uid'
            );
        } else {
            $pdo       = Yii::app()->db;
            $sql       = "SELECT * FROM play_integral_user WHERE uid = :uid";
            $sql_param = array(
                'uid' => $param['uid']
            );

            $data_return_integral_user = $pdo->createCommand($sql)->query($sql_param);

            $data_result = array(
                'status' => 1,
                'msg'    => '',
                'data'   => $data_return_integral_user
            );
        }

        if ($data_result['msg'] == '') {
            $data_log_content = '[IntegralData](getIntegralByUserId)[end] with user uid: ' . $param['uid'];
        } else {
            $data_log_content = '[IntegralData](getIntegralByUserId)[end] [' . $data_result['msg'] . '] with user uid: ' . $param['uid'];
        }

        LogUtil::info($data_log_content);

        return $data_result;
    }

    /**
     * 根据用户某一段时间内的积分变更记录
     * @param   uid        用户的uid
     * @param   start_time 开始时间
     * @param   end_time   结束时间
     * @return
     */
    public function getIntegralRecordByUserIdAndTime ($param) {
        LogUtil::info('[IntegralData](getIntegralRecordByUserIdAndTime)[start] with user uid: ' . $param['uid']);

        $data_result = array(
            'status' => 1,
            'msg'    => '',
            'data'   => array()
        );

        if (empty($param['uid'])) {
            $data_result = array(
                'status' => 0,
                'msg'    => '缺少必要参数'
            );
        } else {
            $pdo = Yii::app()->db;
            $sql = "SELECT * FROM play_integral WHERE uid = :uid";

            if ($param['start_time']) {
                $sql .= " AND create_time >= :start_time ";
            }

            if ($param['end_time']) {
                $sql .= " AND create_time <= :end_time ";
            }

            $sql_param = array();
            $sql_param['uid']        = $param['uid'];
            $sql_param['start_time'] = $param['start_time'];
            $sql_param['end_time']   = $param['end_time'];

            $data_return_integral_records = $pdo->createCommand($sql)->query($sql_param);

            $data_result = array(
                'status' => 1,
                'msg'    => '',
                'data'   => $data_return_integral_records
            );
        }

        if ($data_result['msg'] == '') {
            $data_log_content = '[IntegralData](getIntegralRecordByUserIdAndTime)[end] with user uid: ' . $param['uid'];
        } else {
            $data_log_content = '[IntegralData](getIntegralRecordByUserIdAndTime)[end] [' . $data_result['msg'] . '] with user uid: ' . $param['uid'];
        }

        LogUtil::info($data_log_content);

        return $data_result;
    }

    /**
     * 获取某城市每日任务积分规则
     * @param   city  所在城市
     * @return
     */
    public function getTaskIntegralByCity ($param) {
        LogUtil::info('[IntegralData](getTaskIntegralByCity)[start]');

        $data_result = array(
            'status' => 1,
            'msg'    => '',
            'data'   => array()
        );

        // 默认城市为武汉
        if (empty($param['city'])) {
            $param['city'] = "WH";
        }

        $pdo       = Yii::app()->db;
        $sql       = "SELECT * FROM play_task_integral WHERE `city` = :city LIMIT 1";
        $sql_param = array(
            'city' => $param['city']
        );

        $data_return_task_integral = $pdo->createCommand($sql)->query($sql_param);

        $data_result = array(
            'status' => 1,
            'msg'    => '',
            'data'   => $data_return_task_integral
        );

        if ($data_result['msg'] == '') {
            $data_log_content = '[IntegralData](getTaskIntegralByCity)[end]';
        } else {
            $data_log_content = '[IntegralData](getTaskIntegralByCity)[end] [' . $data_result['msg'] . ']';
        }

        LogUtil::info($data_log_content);
        return $data_result;
    }
}
