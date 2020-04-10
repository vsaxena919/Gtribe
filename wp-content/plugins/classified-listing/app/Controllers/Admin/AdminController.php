<?php

namespace Rtcl\Controllers\Admin;


use Rtcl\Controllers\Admin\Meta\MetaController;

class AdminController {

	public function __construct() {
		new AddConfig();
		new PaymentStatus();
		new TemplateLoader();
		new RegisterPostType();
		new MetaController();
		new ScriptLoader();
		new AdminSettings();
		new Cron();
		new EmailSettings();
		new RestApi();
	}

}