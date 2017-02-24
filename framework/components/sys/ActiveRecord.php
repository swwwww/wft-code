<?php
/**
 * 通用AR父类
 * @Description:
 * @ClassName: ActiveRecord
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-11-19 下午11:08:28
 */
class ActiveRecord extends CActiveRecord{

    //判断 列名是否正确
    public function checkColumnValid($map, $column){
        $flag = false;

        if($column !== 'id'){//不能操作主键id
            foreach($map as $col => $val){
                if($col == $column){
                    $flag = true;
                    break;
                }
            }
        }

        return $flag;
    }

    public function common_save($column_map, $source_map, $vo, $class_name){
        $new_flag = $vo == null ? true : false;

        $flag = false;
        if(count($source_map) > 0){
            unset($column_map['created']);
            unset($column_map['updated']);

            if($vo == null){
                $vo = new $class_name();
                $vo->created = TimeUtil::getNowDateTime();
                $vo->updated = TimeUtil::getNowDateTime();

                foreach($source_map as $key => $val){
                    //正确的字段信息才做更新操作
                    if($this->checkColumnValid($column_map, $key) == true){
                        $flag = true;
                        $vo->$key = $val;
                    }
                }

                if($flag){
                    $flag = $vo->save();
                }
            }else{
                $update_arr = array();

                foreach($source_map as $key => $val){
                    //正确的字段信息才做更新操作
                    if($this->checkColumnValid($column_map, $key) == true){
                        $flag = true;
                        $update_arr[$key] = $val;
                    }
                }

                if($flag){
                    $update_arr['updated'] = TimeUtil::getNowDateTime();
                    $flag = $vo->updateByPk($vo->id, $update_arr);
                }
            }
        }

        $result = $flag ? $vo : ($new_flag ? array() : $vo);

        return $result;
    }
}