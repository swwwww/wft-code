<?php

class CsrfPassHttpRequest extends CHttpRequest {

    private $_csrfToken;

    public $exceptionRoute = array(
        'root' => array(
            'wechat',
        ),
        'smart',
        'admin' => array(),
    );

    public $csrfHttpHeaderTokenName = 'HTTP_X_YII_CSRF_TOKEN';

    public function init() {
        parent::init();
        parent::getCsrfToken();
    }

    private function isInCsrfException($m, $c, $a) {
        if(in_array($m, $this->exceptionRoute, true)) {
            return true;
        } elseif($this->exceptionRoute[$m] && is_array($this->exceptionRoute[$m])) {
            if (in_array($c, $this->exceptionRoute[$m], true)) {
                return true;
            } elseif ($this->exceptionRoute[$m][$c] && is_array($this->exceptionRoute[$m][$c])) {
                if (in_array($a, $this->exceptionRoute[$m][$c], true)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function validateCsrfToken($event) {
        $module = G::$param['route']['m'];
        $controller = G::$param['route']['c'];
        $action = G::$param['route']['a'];

        //对于app客户端，全部跳过csrf验证
        $platform = HttpUtil::getPlatform();
        if($platform['wft']){
            return true;
        }

        if($this->isInCsrfException(strtolower($module), strtolower($controller), strtolower($action))) {
            return true;
        }

        if(isset($_SERVER[$this->csrfHttpHeaderTokenName]) && ($_SERVER['REQUEST_METHOD'] === 'POST')) {
            $_POST[$this->csrfTokenName] = $_SERVER[$this->csrfHttpHeaderTokenName];
        }

        return parent::validateCsrfToken($event);
    }
}


