<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/26
 * Time: 11:06
 */
class UserCollectVo extends ActiveRecord{

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName(){
        return 'play_user_collect';
    }

    //保存或更新操作
    public function commonSave($source_map, $vo = null){
        $column_map = $this->getAttributes();

        $result = $this->common_save($column_map, $source_map, $vo, __CLASS__);
        return $result;
    }

}