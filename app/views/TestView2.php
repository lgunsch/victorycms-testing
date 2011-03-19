<?php

use Vcms\HtmlView;


class TestView2 extends HtmlView
{
	public function __construct($params){	
	}
	public function render(){
		return "678910";
	}
	public function isCacheable(){
	}

}
?>
