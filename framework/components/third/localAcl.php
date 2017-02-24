<?php
/**
 * 权限验证体系
 * @Description:
 * @ClassName: localAcl
 * @author Quenteen || qintao ; hi：qintao870314 ;
 * @date 2014-11-19 下午11:22:52
 */
class localAcl{
    public function check($module, $controller, $action, $user) {

        $route_arr = array(strtolower($module), strtolower($controller), strtolower($action));
        $root_arr = G::$param['LOCAL_ACL_RULES'];
        /**
         * root => array(
         *    'user' => 'register',
         *    'user' => array(
         *        'index' => 'register'
         *    )
         * )
         */

        foreach ($route_arr as $route) {
            if (isset($root_arr[$route])) {
                //判断是否为权限认证的叶子节点
                $is_check_acl_method_flag = $root_arr[$route];

                if (is_string($is_check_acl_method_flag)) {
                    $method_name = $is_check_acl_method_flag;
                    return $this->{$method_name}($user);
                }
                //遍历下一层数组
                $root_arr = $root_arr[$route];
            } else {
                return false;
            }
        }
        return false;
    }

    public function get($module, $controller, $action){
        $route_arr = array(strtolower($module), strtolower($controller), strtolower($action));
        $root_arr = G::$param["LOCAL_ACL_RULES"];

        $module = strtolower($module);
        $controller = strtolower($controller);
        $action = strtolower($action);

        if($root_arr[$module][$controller] == null){
            return false;
        }else if(is_string($root_arr[$module][$controller])){
            return true;
        }else if(isset($root_arr[$module][$controller][$action])){
            return true;
        }

        return false;
    }

    /**
     * array(
     *     0 => '注册用户-register',
     *     1 => '正式付费用户-payer',
     *     2 => '正式核心付费用户-corepayer',
     *     3 => '付费品牌用户-brandpayer',
     *     4 => '正式付费管理员-adminpayer',
     *     5 => '管理员-admin',
     *     6 => '超级管理员-sa',
     *     7 => '经理-manager',
     *     8 => '超级经理-sm',
     * )
     */

    public function allow() {
        return true;
    }

    public function deny() {
        return false;
    }

    public function register($user) {
        $role_type = intval($user["role_type"]);
        $flag = $role_type >= 0;
        return $flag;
    }
    public function payer($user){
        $role_type = intval($user["role_type"]);
        $flag = $role_type >= 1;
        return $flag;
    }
    public function corepayer($user) {
        $role_type = intval($user["role_type"]);
        $flag = $role_type >= 2;
        return $flag;
    }
    public function brandpayer($user) {
        $role_type = intval($user["role_type"]);
        $flag = $role_type >= 3;
        return $flag;
    }
    //zhixiao-employee-付费权限
    public function adminpayer($user) {
        $role_type = intval($user["role_type"]);
        $flag = $role_type >= 4;
        return $flag;
    }
    public function admin($user) {
        $role_type = intval($user["role_type"]);
        $flag = $role_type >= 5;
        return $flag;
    }
    public function sa($user) {
        $role_type = intval($user["role_type"]);
        $flag = $role_type >= 6;
        return $flag;
    }
    public function manager($user) {
        $role_type = intval($user["role_type"]);
        $flag = $role_type >= 7;
        return $flag;
    }
    public function sm($user) {
        $role_type = intval($user["role_type"]);
        $flag = $role_type >= 8;
        return $flag;
    }
}

