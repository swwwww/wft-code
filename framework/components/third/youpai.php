<?php
require_once('upyun/upyun.class.php');

class youpai {
    //use form api
    public function upload($upload_file_path, $mime_type_suffix, $mark_name = 'viewnice'){
        $cloud_config = Yii::app()->params["UPYUN_CONFIG"];

        $upload_url = $cloud_config['upload_url'];
        $bucket = $cloud_config['bucket_name'];

        $url = $upload_url . $bucket;

        $post_data = $this->createToken($mime_type_suffix);
        if(YII_DEBUG) {
            $target_file_obj = new CURLFile($upload_file_path);
        } else {
            $target_file_obj = '@' . $upload_file_path;
        }

        $post_data['file'] = $target_file_obj;

        $content_secret = $cloud_config['content_secret'];
        $post_data['Content-Secret'] = $content_secret;

        if($mark_name != 'no'){
            $post_data['x-gmkerl-thumbnail'] = $mark_name;
        }

        $proxy = new ProxyUtil('post_file', $url, $post_data);

        $proxy_result = $proxy->run();

        $result = json_decode($proxy_result, true);

        return $result;
    }

    public function init(){
        $cloud_config = Yii::app()->params["UPYUN_CONFIG"];

        $upload_url = $cloud_config['upload_url'];
        $bucket = $cloud_config['bucket_name'];

        $upyun = new UpYun($bucket, 'qintao', 'sui870314', UpYun::ED_AUTO, 600);

        return $upyun;
    }

    public function write($file_path, $server_path){
        $upyun = $this->init();

        $file_handler = fopen($file_path, 'r');
        $data = $upyun->writeFile($server_path, $file_handler, true);
        fclose($file_handler);

        return $data;
    }

    public function read($file_path){
        $upyun = $this->init();
        $data = $upyun->readFile($file_path);

        return $data;
    }

    public function delete($file_path){
        $upyun = $this->init();
        $flag = $upyun->delete($file_path);

        return $flag;
    }

    public function createToken($mime_type_suffix){
        $cloud_config = Yii::app()->params["UPYUN_CONFIG"];

        $bucket = $cloud_config['bucket_name'];
        $form_api_token = $cloud_config['form_api_token'];
        $time = time() + 15 * 365 * 24 * 3600;

        $policy_arr = array(
            'bucket' => $bucket,
            'expiration' => $time,
            'save-key' => "/{year}/{mon}/{day}/{random32}.{$mime_type_suffix}",
        );

        $policy_json = json_encode($policy_arr);
        $policy = base64_encode($policy_json);

        $signature_source = $policy . '&' . $form_api_token;
        $signature = md5($signature_source);

        $result = array(
            'policy' => $policy,
            'signature' => $signature,
        );

        return $result;
    }
}
