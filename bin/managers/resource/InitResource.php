<?php

class InitResource extends Resource {

    public function set() {
        $tab_arr = array(
            'tab_selection' => array(
                'nav_name' => 'recommend',
                'nav_url' => 'recommend/index',
                'name' => 'tab_selection',
                'title' => '精选',
                'image' => '',
                'image_selected' => '',
                'color' => '000',
                'color_selected' => 'fa6e51',
                'show_type' => 'sprite',
            ),
            'tab_kidsplay' => array(
                'nav_name' => 'play',
                'nav_url' => 'play/playIndex?sort=1',
                'name' => 'tab_kidsplay',
                'title' => '遛娃',
                'image' => '',
                'image_selected' => '',
                'color' => '000',
                'color_selected' => 'fa6e51',
                'show_type' => 'sprite',
            ),
            'tab_ticket' => array(
                'nav_name' => 'ticket',
                'nav_url' => 'ticket/buyTicket?rid=0&rid_name=全部区域',
                'name' => 'tab_ticket',
                'title' => '买票',
                'image' => '',
                'image_selected' => '',
                'color' => '000',
                'color_selected' => 'fa6e51',
                'show_type' => 'sprite',
            ),
            'tab_mine' => array(
                'nav_name' => 'user',
                'nav_url' => 'user/center',
                'name' => 'tab_mine',
                'title' => '我的',
                'image' => '',
                'image_selected' => '',
                'color' => '000',
                'color_selected' => 'fa6e51',
                'show_type' => 'sprite',
            ),
        );

        $theme_result = array(
            'show_theme' => 0,
            'theme' => $tab_arr,
        );

        $init_resource = MemberLib::init();

        if($init_resource['status'] == 1){
            $data = $init_resource['data'];

            if($data['show_theme'] == 1){
                foreach((array)$data['theme'] as $key => $val){
                    $title = $val['name'];
                    $nav_name = $tab_arr[$title]['nav_name'];
                    $nav_url = $tab_arr[$title]['nav_url'];

                    $tab_arr[$title] = $val;
                    $tab_arr[$title]['nav_name'] = $nav_name;
                    $tab_arr[$title]['nav_url'] = $nav_url;
                }

                $theme_result['show_theme'] = 1;
                $theme_result['theme'] = $tab_arr;
            }
        }

        return $theme_result;
    }

}




