<?php

use Vcms\HtmlView;


class TestView2 extends HtmlView
{
	public function render($params){
	}
	public function getBody(){
		return "678910";
	}
	public function isCacheable(){
	}

}
?>
