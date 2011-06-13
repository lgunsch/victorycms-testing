<?php

use Vcms\View\Html;


class TestView extends Html
{
	public function __construct($params){
	}
	public function render(){
		return "12345";
	}
	public function isCacheable(){
	}

}
?>
