<?php
class SocialCircleMsgMongo extends EMongoDocument{

    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    public function getCollectionName()
    {
        return 'social_circle_msg';
    }

    public function addInfo() {
        $this->z='1234';
        $this->save();
    }

}
