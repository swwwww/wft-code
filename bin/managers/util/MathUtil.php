<?php
/**
 * 数学计算的帮助类
 * @Description:
 * @ClassName: MathUtil
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-11-14 下午01:33:53
 */
class MathUtil extends Manager{

    /**
     * 平均值
     * @Description: $source是一维数组
     * @Title: mean
     * @param unknown_type $source
     * @return unknown
     * @author Quenteen || qintao ; hi：qintao870314 ;
     * @date 2014-11-27 下午12:23:00
     */
    public static function mean($source){
        $target = 0;

        $i = 0;
        $total = 0;
        $float_flag = false;
        foreach($source as $key => $val){
            if(is_float($val)){
                $float_flag = true;
            }

            $total += $val;
            $i++;
        }

        if($i > 0){
            $target = $total / $i;
            if($float_flag){
                $target = round($target, 2);
            }else{
                $target = intval($target);
            }
        }

        return $target;
    }

    /**
     * 计算方差
     * @Description: $source是一维数组
     * @Title: variance
     * @param unknown_type $source
     * @author Quenteen || qintao ; hi：qintao870314 ;
     * @date 2014-11-26 下午08:30:26
     */
    public static function variance($source){
        $target = 0;
        $count = count($source);
        if($count > 1){
            $avg = self::mean($source);

            $variance_total = 0;

            foreach($source as $key => $val){
                $variance_total += pow($val - $avg, 2);
            }

            $target = $variance_total / $count;
        }

        return $target;
    }

    /**
     * 峰值（按数值，还是按百分比返回峰值）
     * @Description: $source是一维数组
     * @Title: max
     * @param unknown_type $source
     * @param unknown_type $type
     * @author Quenteen || qintao ; hi：qintao870314 ;
     * @date 2014-11-14 下午01:37:17
     */
    public static function max($source, $count_or_percent){
        //判断$count_or_percent 是 小数还是整数（0.8 || 5）
        //从而返回 对应的top数据（按数值，还是按百分比）

        $count = count($source);

        if($count == 0){
            return array(0);
        }

        if(is_float($count_or_percent)){
            $top_num = intval(floor($count * $count_or_percent));
        }else{
            $top_num = $count_or_percent;
        }

        $top_num = $top_num > $count ? $count : $top_num;

        rsort($source, SORT_NUMERIC);

        $target = array_slice($source, 0, $top_num);

        return $target;
    }

    public static function min($source, $count_or_percent){
        //判断$count_or_percent 是 小数还是整数（0.8 || 5）
        //从而返回 对应的top数据（按数值，还是按百分比）

        $count = count($source);

        if($count == 0){
            return array(0);
        }

        if(is_float($count_or_percent)){
            $top_num = intval(floor($count * $count_or_percent));
        }else{
            $top_num = $count_or_percent;
        }

        $top_num = $top_num > $count ? $count : $top_num;

        sort($source, SORT_NUMERIC);

        $target = array_slice($source, 0, $top_num);

        return $target;
    }

    /**
     * 获取一个向量的相关统计量
     * @param array $vector
     * @return array
     */
    public static function getVectorStaticValues($vector) {
        $output = array(
            'min' => $vector[array_search(min($vector), $vector)],
            'max' => $vector[array_search(max($vector), $vector)],
            'avg' => array_sum($vector) / count($vector)
        );
        return $output;
    }

    //根据范围大小几率的获取成功的返回
    public static function getChance($size){
        $size  = intval($size);
        $size = $size == 0 ? 1 : $size;

        $target = rand(1, $size);

        if(1 === $target){
            return true;
        }else{
            return false;
        }
    }

    //获取随机的整型 数值
    public static function randCode($size = 9){
        // 去掉了 0 1 O l 等
        $str = "123456789";
        $code = '';
        for ($i = 0; $i < $size; $i++) {
            $code .= $str[mt_rand(0, strlen($str)-1)];
        }

        return intval($code);
    }

}