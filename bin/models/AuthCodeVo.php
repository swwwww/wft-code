<?php
class AuthCodeVo extends ActiveRecord{

    public static function model($className=__CLASS__){
        return parent::model($className);
    }

    public function tableName(){
        return 'play_auth_code';
    }

    //保存或更新操作
    public function commonSave($source_map, $vo = null){
        $column_map = $this->getAttributes();

        $result = $this->common_save($column_map, $source_map, $vo, __CLASS__);
        return $result;
    }

}