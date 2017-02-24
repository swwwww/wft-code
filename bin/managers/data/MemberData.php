<?php
class MemberData extends Manager {
    public static function getMember($user_id) {
        $sql = "select * from play_member
                where member_user_id = {$user_id}
                and member_level = 1";

        $vo = Yii::app()->db->createCommand($sql)->queryRow();

        return $vo;
    }
}
