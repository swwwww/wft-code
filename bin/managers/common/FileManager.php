<?php
/**
 * 直接调用类：文件
 * @Description:
 * @ClassName: FileManager
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-12-18 上午01:22:36
 */
class FileManager extends Manager{
    /**
     * 上传图片接口，默认调用upload。 其他类型请调用：uploadFile
     * @Description:
     * @Title: upload
     * @author Quenteen || qintao ; hi：qintao870314 ;
     * @date 2014-12-22 下午07:36:06
     */
    public function upload(){
        $result = array(
            'status' => 0,
            'msg' => '',
            'data' => array(),
        );

        $file_limit = array(
            "type" => array('gif', 'png', 'jpg', 'jpeg'),
            "size" => 1000,//文件大小限制，单位KB
        );

        $file_error = $_FILES["file"]["error"]; //$file_type = $_FILES["file"]["type"];
        $file_name = $_FILES["file"]["name"];
        $file_path = $_FILES["file"]["tmp_name"];
        $file_size = $_FILES["file"]["size"];
        //$mark_name = $_FILES["file"]["mark_name"];

        $last_point_loc = strrpos($file_name, '.') + 1;
        $file_type = substr($file_name, $last_point_loc);

        $limit_flag = true;
        //格式: strtolower(strrchr($file_name, '.'));
        $mime_type_suffix = strtolower($file_type);

        if(!in_array($mime_type_suffix, $file_limit['type'])){
            $limit_flag = false;
            $result['msg'] = '暂不支持该图片类型';
        }

        //大小
        $file_size_limit = 5 * 1024 * $file_limit['size'];
        if($file_size > $file_size_limit ){
            $limit_flag = false;
            $result['msg'] = '图片超出大小';
        }

        //保存图片
        if($limit_flag){
            if($file_error == 0 && is_uploaded_file($file_path)){
                $mark_name = $this->getMarkName($mark_name);

                $upload_result = $this->youpai->upload($file_path, $mime_type_suffix, $mark_name);
                $http_status = $upload_result['code'];
                if($http_status == 200){
                    $file_url = $upload_result['url'];

                    $width = $upload_result['image-width'];
                    $height = $upload_result['image-height'];

                    $now_user = UserLib::getNowUser();
                    $user_id = $now_user['id'];

                    $file_vo = new FileVo();
                    $file_vo->only_id = md5(uniqid('', true));
                    $file_vo->name = $file_name;
                    $file_vo->type = $file_type;
                    $file_vo->url = $file_url;
                    $file_vo->width = intval($width);
                    $file_vo->height = intval($height);
                    $file_vo->size = $file_size;
                    $file_vo->private = 0;
                    $file_vo->valid = 1;
                    $file_vo->create_user_id = $user_id;
                    $file_vo->update_user_id = $user_id;
                    $file_vo->created = TimeUtil::getNowDateTime();
                    $file_vo->updated = TimeUtil::getNowDateTime();

                    if($file_vo->save()){
                        $result['status'] = 1;

                        $cloud_config = Yii::app()->params["UPYUN_CONFIG"];
                        $file_domain = $cloud_config['file_domain'];

                        $result['data'] = array(
                            'id' => $file_vo->id,
                            'url' => $file_domain . $file_vo->url,
                        );
                    }
                }else{
                    $info = $upload_result['code'] . '#!#' . $upload_result['message'];
                    LogUtil::logDb($info, __CLASS__, __FUNCTION__);

                    $result['msg'] = '网络错误';
                }
            }
        }

        return $result;
    }

    public function readImage($source_file, $file_path = null){
        list($width, $height, $image_type) = getimagesize($source_file);

        $image_src = false;
        switch ($image_type){
            case 1:
                header('Content-Type: image/gif');
                break;
            case 2:
                header('Content-Type: image/jpeg');
                break;
            case 3:
                header('Content-Type: image/png');
                break;
            default:
                return "wrong file";
        }
        $interval = 60 * 24 * 3600;
        header("Pragma:max-age");
        header("Cache-Control: max-age=$interval");
        header("Expires: ". gmdate("r", (time() + $interval)));

        if($file_path == null){
            $data = file_get_contents($source_file);
        }else{
            $data = $this->youpai->read($file_path);
        }

        return $data;
    }

    public function delete($file_path){
        $flag = $this->youpai->delete($file_path);
        $flag = true;

        return $flag;
    }

    /**
     * 获取存在的 云存储 mark
     * @param
     * @return
     * @author 11942518@qq.com | quenteen
     * @date 2015-5-11 下午04:38:35
     */
    public function getMarkName($mark_name){
        $mark_name_arr = array('mark', 'view');

        if($mark_name == null || !in_array($mark_name, $mark_name_arr)){
            $mark_name = 'view';
        }

        return $mark_name;
    }
}