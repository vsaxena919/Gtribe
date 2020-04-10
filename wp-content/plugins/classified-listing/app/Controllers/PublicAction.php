<?php

namespace Rtcl\Controllers;


class PublicAction {

    public function __construct()
    {
        FormHandler::init();
        new Shortcodes();
        new RtclPublic();
        ListingHook::init();
    }

}