<?php

use Vcms\HtmlView;


class TestView extends HtmlView
{
	public function render($params){
	}
	public function getBody(){
		return "12345";
	}
	public function isCacheable(){
	}

}
?>
