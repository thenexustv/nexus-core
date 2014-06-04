<?php

class Nexus_General_Settings extends Nexus_Setting {

	protected $default_settings = array(
		'redirect-url' => ''
	);

	public $settings_key = 'nexus-core-general-settings';

	public function __construct() {
		parent::__construct();
	}

}