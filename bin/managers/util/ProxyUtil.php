<?php
/**
 * 通过php curl 代理接口
 * @Description:
 * @ClassName: ProxyUtil
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-11-11 下午02:52:11
 */
class ProxyUtil{
    public $method = 'get';
    public $url = '';
    public $json_data = '';
    public $post_data = '';
    public $data = '';
    public $header = '';

    public function __construct($method, $url, $data = array(), $header = array()){
        $this->method = $method;
        $this->url = $url;
        $this->json_data = json_encode($data);
        $this->post_data = $data;
        $this->header = $header;
        if($method == 'get' && $data != null){
            $this->data = http_build_query($data, '&');
            $this->url .= '?' . http_build_query($data, '&');

        }
    }

    public function run(){
        if($this->method == 'get'){
            return $this->get();
        }else if($this->method == 'post'){
            return $this->post();
        }else if($this->method == 'post_file'){
            return $this->postFile();
        }
    }

    public function get(){
        $curl = curl_init();

        // 设置你需要抓取的URL
        curl_setopt($curl, CURLOPT_URL, $this->url);

        // 设置是否输出header 信息
        curl_setopt($curl, CURLOPT_HEADER, 0);

        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // 设置user-agent 参数
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

        // 运行cURL，请求网页
        $result = curl_exec($curl);

        // 关闭URL请求
        curl_close($curl);

        // 显示获得的数据
        return $result;
    }

    public function post(){
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->url);

        curl_setopt($curl, CURLOPT_POST, 1);

        // 设置是否输出header 信息
        curl_setopt($curl, CURLOPT_HEADER, 0);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->post_data);

        // pretend request as ajax to avoid debug log
        $header = array();

        if($this->header){
            if($this->header['ver']){
                $header[] = 'VER:' . $this->header['ver'];
            }
            if($this->header['city']){
                $header[] = 'CITY:' . $this->header['city'];
            }
        }else{
            $header = array(
            	"X-Requested-With: XMLHttpRequest",
            	"Content-Type: application/json",
            );
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        //curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));

        // 设置user-agent 参数
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

        $result = curl_exec($curl);

        return $result;
    }

    public function postFile(){
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->url);

        curl_setopt($curl, CURLOPT_POST, 1);

        // 设置是否输出header 信息
        curl_setopt($curl, CURLOPT_HEADER, 0);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

        $result = curl_exec($curl);

        return $result;
    }
}