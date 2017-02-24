<?php
/**
 * 发送报警的公用方法类
 * @Description:
 * @ClassName: AlarmUtil
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-1-17 下午02:46:37
 */
class AlarmUtil extends Manager{

    /**
     * 发送邮件或短信报警
     * @Description: 对外提供静态方法进行调用，实际上调用一个实例对象的方法
     * @Title: sendAlarm
     * @param unknown_type $config
     * @author Quenteen || qintao ; hi：qintao870314 ;
     * @date 2014-1-17 下午02:57:35
     */
    public static function sendAlarm($config){
        $instance = new AlarmUtilInstance();
        $instance->sendAlarm($config);
    }
}

/**
 * 报警类对内 提供的示例对象 调用
 * @Description: 对外提供静态调用方法，为了能使用 $this 调用框架的方法，特别提供一个此类的示例对象。
 * @ClassName: AlarmUtilInstance
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-1-26 下午10:29:05
 */
class AlarmUtilInstance extends Manager{
    public function sendAlarm($config){
        $base_config = array(
            'title' => '【请添加详细描述】',
            'content' => '报警了，请火速查看',
            'source_mail' => 'crowdtest_alarm@baidu.com',
            'target_mail' => array('qintao02@baidu.com'),
            'is_gsm' => false,
            'target_phone' => array('13888888888'),
            'sim_content' => '众测平台报警了，请火速查看',
        );
        foreach ($config as $key => $val){
            $base_config[$key] = $val;
        }

        $target_mail = implode(',', $base_config['target_mail']);
        $source_mail = $base_config['source_mail'];
        $title = $base_config['title'];
        $content = $base_config['content'];
        $is_gsm = $base_config['is_gsm'];

        //todo ...
        //$this->email->send($source_mail, $target_mail, $title, $content);

        if($is_gsm){
            $target_phone = $base_config['target_phone'];
            $sim_content = $base_config['sim_content'];
            //todo ...
            //$this->gsm->sendMsg($target_phone, $sim_content);
        }
    }
}