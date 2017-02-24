<?php
/**
 * 活动积分记录
 * @classname: LotteryScoreRecordVo
 * @author 11942518@qq.com | quenteen
 * @date 2016-10-24
 */
class LotteryScoreRecordVo extends ActiveRecord{

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName(){
        return 'ps_lottery_score_record';
    }

    //保存或更新操作
    public function commonSave($source_map, $vo = null){
        $column_map = $this->getAttributes();

        $result = $this->common_save($column_map, $source_map, $vo, __CLASS__);
        return $result;
    }

}