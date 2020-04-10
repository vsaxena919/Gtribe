<?php

namespace Rtcl\Controllers\Hooks;

class Actions {

    public static function init()
    {
        Filters::init();
        Hooks::init();
	    Comments::init();
        AppliedBothEndHooks::init();
    }

}