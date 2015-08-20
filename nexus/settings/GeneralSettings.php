<?php

namespace Nexus\Settings;

class GeneralSettings extends AbstractSettings {

	protected $default_settings = array(
		'redirect-url' => ''
	);

	public $settings_key = 'nexus-core-general-settings';

	public function __construct() {
		parent::__construct();
	}

}