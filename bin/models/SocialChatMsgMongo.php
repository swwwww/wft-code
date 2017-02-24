<?php
class SocialChatMsgMongo extends EMongoDocument{

    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    public function getCollectionName()
    {
        return 'social_chat_msg';
    }

    public function addInfo() {
        $this->z='1234';
        $this->save();
    }

}
