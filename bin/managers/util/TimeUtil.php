<?php
/**
 * 时间帮助类
 * @Description:
 * @ClassName: TimeUtil
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-11-13 下午03:54:05
 */
class TimeUtil extends Manager{

    public static function getNowDate(){
        return date('Y-m-d');
    }

    public static function getNowDateTime(){
        return date('Y-m-d H:i:s');
    }

    public static function getDateTimeFromTime($time){
        $target = date('Y-m-d H:i:s', $time);

        return $target;
    }
    /**
     * 根据指定日期：获取本周的起止日期 - 周一凌晨0点 至 周日晚上12点
     * @Description:
     * @Title: getThisWeekStartAndEnd
     * @param unknown_type $source
     * @author Quenteen || qintao ; hi：qintao870314 ;
     * @date 2014-11-13 下午03:57:37
     */
    public static function getThisWeekStartAndEnd($source){
        if($source == null){
            //如果传入空值：默认获取上周一和上周日
            $source = date('Y-m-d', strtotime('-7 days', strtotime(date('Y-m-d'))));
        }
        $source_dt = strtotime($source);

        $week = date('w', $source_dt);

        //周日：week = 0 =>统一转换一次
        $week = $week == 0 ? 7 : $week;

        $monday_gap = $week - 1;
        $sunday_gap = 7 - $week;

        $mon_date = date('Y-m-d', strtotime("-{$monday_gap} days", $source_dt));
        $sun_date = date('Y-m-d', strtotime("+{$sunday_gap} days", $source_dt));

        $result = array(
            'mon_date' => $mon_date,
            'sun_date' => $sun_date,
            'mon_week' => date('w', strtotime($mon_date)),
            'sun_week' => date('w', strtotime($sun_date)),
        );

        return $result;
    }

    /**
     * 根据指定日期：获取上一周的起止日期 - 周一凌晨0点 至 周日晚上12点
     * @Description:
     * @Title: getLastWeekStartAndEnd
     * @param unknown_type $date
     * @author Quenteen || qintao ; hi：qintao870314 ;
     * @date 2014-11-13 下午03:55:01
     */
    public static function getLastWeekStartAndEnd($source){
        if($source == null){
            $source = date('Y-m-d');
        }

        $source_dt = strtotime($source);

        //获取上周的同一天
        $last_week_source = date('Y-m-d', strtotime('-7 days', $source_dt));

        //调用基础函数，获取上周的周一和周日
        $result = self::getThisWeekStartAndEnd($last_week_source);

        return $result;
    }

    //根据source 获取昨天的日期
    public static function getLastDate($source){
        if($source == null){
            $source = date('Y-m-d');
        }

        $source_dt = strtotime($source);

        //获取上周的同一天
        $last_date = date('Y-m-d', strtotime('-1 days', $source_dt));

        $result = $last_date;

        return $result;
    }

    /**
     * 获取N以后的日期
     * @param
     * @return
     * @author 11942518@qq.com | quenteen
     * @date 2015-4-4 下午06:31:25
     */
    public static function getLaterDateByPlusTime($source, $num, $type){
        $type_arr = array(
            'd' => 'days',
            'm' => 'months',
            'y' => 'years',
        );

        if($source){
            $time = strtotime($source);
        }else{
            $now = TimeUtil::getNowDate();
            $time = strtotime($source);
        }

        $plus_time = strtotime("+{$num} " . $type_arr[$type], $time);

        $result = date('Y-m-d', $plus_time);

        return $result;
    }
}