<?php
class Module extends CWebModule{
    public function init(){
        $this->initImport();
    }

    protected function initImport($module = null){
    	$module = $module == null ? G::$param['route']['m'] : $module;
    	ImportUtil::import($module);
    }

}