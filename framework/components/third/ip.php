<?php
require_once('ip/IPHelp.class.php');

class ip {
	public function find($ip){
	    return IPHelp::find($ip);
	}
}
