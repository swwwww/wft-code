<?php
require_once('smarty3/SmartyBC.class.php');

class tpl extends SmartyBC {
    public function __construct(array $options=array()) {
        parent::__construct($options);

        spl_autoload_unregister('smartyAutoload');
        Yii::registerAutoloader('smartyAutoload');

        $this->setTemplateDir(DIR_BIN . 'view/smarty/');

        $this->setCompileDir(DIR_TEMP . 'view/compile/smarty3/');
        //$this->setConfigDir(DIR_TEMP . 'template/config/smarty3/');
        $this->setCacheDir(DIR_TEMP . 'view/cache/smarty3/');

        $this->caching = false;

        if (YII_DEBUG) {
            $this->force_compile = true;
        } else {
            $this->force_compile = false;
        }

        $this->left_delimiter = '{';
        $this->right_delimiter = '}';

        $this->addPluginsDir(DIR_FW_COMPONENT . '/third/smarty3_extend/');

        if($_SERVER['HTTP_HOST']){
            $base_host = HttpUtil::getBaseHost();

            //网站根路径：带域名
            $base_url = $base_host . '/';

            //重新给 顶层root module赋值 url_module
            $module = G::$param['route']['m'];
            $base_url_module = $module == 'root' ? $base_url : $base_url . $module . '/';

            $this->assign('BASE_HOST', $base_host);
            $this->assign('BASE_URL', $base_url);
            $this->assign('BASE_URL_STATIC', $base_url . 'static/');
            $this->assign('BASE_URL_MODULE', $base_url_module);

            //云端资源
            $cloud_config = Yii::app()->params['UPYUN_CONFIG'];
            $base_url_cloud = $cloud_config['file_domain'];
            $this->assign('BASE_URL_CLOUD', $base_url_cloud);

            //yii_csrf_token
            $yii_csrf_token = Yii::app()->request->getCsrfToken();
            CookieUtil::set(Yii::app()->request->csrfTokenName, $yii_csrf_token);

            $this->assign('YII_CSRF_TOKEN', $yii_csrf_token);
            $this->assign('YII_DEBUG', YII_DEBUG);
        }
    }

    public function init(){

    }

    public function staticFileMap($file, $type, $module = '', $debug = YII_DEBUG) {
        //网站根路径：不带域名
        $base = Yii::app()->getRequest()->getBaseUrl();

        if ($module) {
            $module = $base . '/' . $module . '/static/';
            $module_base = DIR_ROOT . 'bin/modules/' . $module . '/static/';
        } else {
            $module = $base . '/static/';
            $module_base = DIR_ROOT . '/static/';
        }

        if ($debug) {
            $ret = array(
                'realFile' => $module_base . $file,
                'webFile' => $module . $file,
                'type' => $type,
            );
        } else {
            if ($type === 'css' || $type === 'less') {
                $ret = array(
                    'realFile' => $module_base . $file,
                    'webFile' => $module . $file,
                    'type' => $type,
                );
            } elseif ($type === 'js') {
                $ret = array(
                    'realFile' => $module_base . $file,
                    'webFile' => $module . $file,
                    'type' => $type,
                );
            }
        }
        $ret['md5'] = md5_file($ret['realFile']);
        $web_file = $ret['webFile'];
        $md5 = $ret['md5'];
        $file_path = $web_file . '?v=' . $md5;
        switch ($ret['type']) {
            case 'css':
                $ret['html'] = "<link rel=\"stylesheet\" href=\"{$file_path}\">";
                break;
            case 'js':
                $ret['html'] = "<script src=\"{$file_path}\"></script>";
                break;
            default:
                $ret['html'] = '';
        }
        return $ret;
    }
}
