<?php
require_once('alisms/TopSdk.php');
require_once('alisms/top/TopClient.php');
require_once('alisms/top/ResultSet.php');
require_once('alisms/top/RequestCheckUtil.php');
require_once('alisms/top/TopLogger.php');
require_once('alisms/top/request/AlibabaAliqinFcSmsNumSendRequest.php');

class alisms {
    public function send($phone, $param){
        $c = new TopClient();

        $appkey = '23422745';
        $secret = 'efaf3a56f292c65500d7419c8b588934';

        $c->appkey = $appkey ;
        $c->secretKey = $secret ;

        $req = new AlibabaAliqinFcSmsNumSendRequest;

        $req->setExtend("123");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName("玩翻天测试");
        $req->setSmsParam(json_encode($param));
        $req->setRecNum($phone);
        $req->setSmsTemplateCode("SMS_12862038");
        $resp = $c->execute($req);

        return $resp;
    }
}
