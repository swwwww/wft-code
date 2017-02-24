<?php
require_once('dom/simple_html_dom.php');

class dom {
    public function getHtml($url){
        return file_get_html($url);
    }
}
