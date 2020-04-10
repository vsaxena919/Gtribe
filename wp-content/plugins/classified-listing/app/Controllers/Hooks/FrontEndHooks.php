<?php
namespace Rtcl\Controllers\Hooks;


class FrontEndHooks {
	
	public static function init(){
	    TemplateHooks::init();
	    AppliedHooks::init();
	}

}