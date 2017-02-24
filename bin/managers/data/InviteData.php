<?php
/**
 * 邀请数据层            负责邀请相关的数据操作
 * @classname: IntegralData
 * @author 11942518@qq.com | quenteen
 * @date 2016-7-2
 */
class InviteData extends Manager{

    /**
     * 获取用户邀请注册的记录
     * @param   uid        用户的uid
     * @param   start_time 开始时间
     * @param   end_time   结束时间
     * @return
     */
    public static function getInviteRegisterRecordsByUserId($param){
        LogUtil::info('[InviteData](getInviteRegisterRecordsByUserId)[start] with user uid: ' . $param['uid']);
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
            $sql       = "SELECT * FROM invite_member WHERE uid = :uid";

            if ($param['start_time']) {
                $sql .= " AND register_time >= :start_time ";
            }

            if ($param['end_time']) {
                $sql .= " AND register_time <= :end_time ";
            }

            $sql_param = array();
            $sql_param['uid']        = $param['uid'];
            $sql_param['start_time'] = $param['start_time'];
            $sql_param['end_time']   = $param['end_time'];

            $data_return_invite_member = $pdo->createCommand($sql)->query($sql_param);

            $data_result = array(
                'status' => 1,
                'msg'    => '',
                'data'   => $data_return_invite_member
            );
        }

        if ($data_result['msg'] == '') {
            $data_log_content = '[InviteData](getInviteRegisterRecordsByUserId)[end] with user uid: ' . $param['uid'];
        } else {
            $data_log_content = '[InviteData](getInviteRegisterRecordsByUserId)[end] [' . $data_result['msg'] . '] with user uid: ' . $param['uid'];
        }

        LogUtil::info($data_log_content);

        return $data_result;
    }
}
