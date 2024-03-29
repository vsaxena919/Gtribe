<?php


namespace Rtcl\Traits;


trait SingletonTrait {
	/**
	 * Store the singleton object.
	 */
	private static $singleton = false;

	/**
	 * Create an inaccessible contructor.
	 */
	private function __construct() {
		$this->__instance();
	}

	private function __instance() {
	}

	/**
	 * Fetch an instance of the class.
	 */
	public static function getInstance() {
		if ( self::$singleton === false ) {
			self::$singleton = new self();
		}

		return self::$singleton;
	}
}