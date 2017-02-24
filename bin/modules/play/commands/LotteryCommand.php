<?php
class LotteryCommand extends Command{

    /*录入几古家的中奖码*/
    public function actionProcessCode(){
        $file = '/home/work/jigu_code.txt';

        $content = file_get_contents($file);

        $content_arr = explode("\n", $content);

        $lottery_id = 3;
        $time = TimeUtil::getNowDateTime();
        foreach($content_arr as $key => $val){
            $vo = LotteryCodeVo::model()->find('code = :code', array(
                ':code' => $val,
            ));

            if($vo){
                $vo->updated = $time;
            }else{
                $vo = new LotteryCodeVo();

                $vo->lottery_id = $lottery_id;
                $vo->code = $val;
                $vo->created = $time;
                $vo->updated = $time;
            }

            $vo->save();
        }
    }

}
