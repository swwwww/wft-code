<?php
class GetAction extends CAction {

    public function run(){
        $result = array(
            'status' => 0,
            'data' => array(),
            'msg' => 'api',
        );

        echo json_encode($result);
    }
}
